<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'pending'       => Payment::where('status', 'menunggu')->count(),
            'registrations' => Registration::where('status', 'menunggu')->count(),
            'overdue'       => Invoice::where('status', '!=', 'lunas')
                                      ->whereDate('due_date', '<', today())
                                      ->count(),
            'unpaid'        => Invoice::where('status', '!=', 'lunas')->sum('amount'),
            'collected'     => Invoice::where('status', 'lunas')->sum('amount'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}