<?php

namespace App\Console\Commands;

use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders {--days=3 : Ingatkan tagihan yang jatuh tempo dalam sekian hari ke depan}';

    protected $description = 'Kirim email pengingat tagihan yang akan jatuh tempo ke orang tua';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));

        $invoices = Invoice::query()
            ->with('student.parent')
            ->where('status', '!=', 'lunas')
            ->whereNull('reminded_at')
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', today()->addDays($days))
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'menunggu'))
            ->whereHas('student', fn ($q) => $q->whereNotNull('user_id'))
            ->orderBy('due_date')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices->groupBy(fn ($invoice) => $invoice->student->user_id) as $items) {
            $parent = $items->first()->student->parent;

            if (! $parent) {
                continue;
            }

            try {
                Mail::to($parent)->send(new InvoiceReminder($parent, $items));
                Invoice::whereKey($items->pluck('id'))->update(['reminded_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        $this->info("Selesai: {$sent} email terkirim, {$failed} gagal, {$invoices->count()} tagihan diperiksa.");

        return self::SUCCESS;
    }
}