<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'kelas', 'semester', 'academic_year']);
        $base = Invoice::filter($filters);

        $invoices = (clone $base)
            ->with('student')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'semua' => $this->summarize(clone $base),
            'lunas' => $this->summarize((clone $base)->where('status', 'lunas')),
            'belum' => $this->summarize((clone $base)->where('status', '!=', 'lunas')),
            'terlambat' => $this->summarize(
                (clone $base)->where('status', '!=', 'lunas')->whereDate('due_date', '<', today())
            ),
        ];

        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

                $years = Invoice::whereNotNull('academic_year')->distinct()->orderByDesc('academic_year')->pluck('academic_year');

        return view('admin.invoices.index', compact('invoices', 'kelasList', 'summary', 'years'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['q', 'status', 'kelas', 'semester', 'academic_year']);
        $filename = 'tagihan-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');

            // Penanda UTF-8 supaya Excel membaca karakter dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['NIS', 'Siswa', 'Kelas', 'Tagihan', 'Jumlah', 'Jatuh Tempo', 'Status', 'Tanggal Lunas'], ';', '"', '');

            Invoice::filter($filters)->with('student')->chunkById(500, function ($invoices) use ($out) {
                foreach ($invoices as $invoice) {
                    fputcsv($out, [
                        $this->cell($invoice->student->nis),
                        $this->cell($invoice->student->name),
                        $this->cell($invoice->student->kelas),
                        $this->cell($invoice->title),
                        $invoice->amount,
                        $invoice->due_date->toDateString(),
                        $this->statusLabel($invoice),
                        $invoice->paid_at?->toDateString() ?? '',
                    ], ';', '"', '');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $students = Student::orderBy('name')->get();

        return view('admin.invoices.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date'],
            'semester' => ['nullable', 'in:ganjil,genap', 'required_with:academic_year'],
            'academic_year' => ['nullable', new \App\Rules\AcademicYear, 'required_with:semester'],
        ], \App\Support\SemesterInput::messages(), \App\Support\SemesterInput::attributes());

        Invoice::create($data);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Tagihan berhasil dibuat.');
    }

    public function edit(Invoice $invoice)
    {
        $students = Student::orderBy('name')->get();

        return view('admin.invoices.edit', compact('invoice', 'students'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date'],
            'semester' => ['nullable', 'in:ganjil,genap', 'required_with:academic_year'],
            'academic_year' => ['nullable', new \App\Rules\AcademicYear, 'required_with:semester'],
        ], \App\Support\SemesterInput::messages(), \App\Support\SemesterInput::attributes());

        $invoice->update($data);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Tagihan berhasil diubah.');
    }

    public function pay(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'lunas',
            'paid_at' => now(),
        ]);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Tagihan ditandai lunas.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    private function summarize(Builder $query): array
    {
        return [
            'count' => (clone $query)->count(),
            'sum' => (int) (clone $query)->sum('amount'),
        ];
    }

    private function statusLabel(Invoice $invoice): string
    {
        if ($invoice->isPaid()) {
            return 'Lunas';
        }

        return $invoice->due_date->lt(today()) ? 'Terlambat' : 'Belum lunas';
    }

    /**
     * Cegah teks yang diawali karakter rumus dijalankan sebagai rumus oleh Excel.
     */
    private function cell(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}