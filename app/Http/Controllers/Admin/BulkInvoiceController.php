<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BulkInvoiceController extends Controller
{
    public function create()
    {
        $total = Student::count();
        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $countByKelas = Student::selectRaw('kelas, count(*) as total')
            ->groupBy('kelas')
            ->pluck('total', 'kelas');

        return view('admin.invoices.bulk', compact('total', 'kelasList', 'countByKelas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target' => ['required', 'in:semua,kelas'],
            'kelas' => ['required_if:target,kelas', 'nullable', 'string', Rule::exists('students', 'kelas')],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date'],
            'semester' => ['nullable', 'in:ganjil,genap', 'required_with:academic_year'],
            'academic_year' => ['nullable', new \App\Rules\AcademicYear, 'required_with:semester'],
        ], [
            'target.required' => 'Pilih siswa yang akan ditagih.',
            'target.in' => 'Target tagihan tidak valid.',
            'kelas.required_if' => 'Pilih kelas yang akan ditagih.',
            'semester.in' => 'Pilih semester Ganjil atau Genap.',
            'semester.required_with' => 'Pilih semester untuk tahun ajaran ini.',
            'academic_year.required_with' => 'Isi tahun ajaran untuk semester ini.',
        ], [
            'title' => 'nama tagihan',
            'amount' => 'jumlah',
            'due_date' => 'jatuh tempo',
            'kelas' => 'kelas',
            'semester' => 'semester',
            'academic_year' => 'tahun ajaran',
        ]);

        $students = Student::query()
            ->when($data['target'] === 'kelas', fn ($q) => $q->where('kelas', $data['kelas']))
            ->get();

        if ($students->isEmpty()) {
            return back()
                ->withErrors(['target' => 'Belum ada siswa yang bisa ditagih.'])
                ->withInput();
        }

        $existing = Invoice::where('title', $data['title'])
            ->where('semester', $data['semester'] ?? null)
            ->where('academic_year', $data['academic_year'] ?? null)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('student_id')
            ->flip();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($students, $existing, $data, &$created, &$skipped) {
            foreach ($students as $student) {
                if ($existing->has($student->id)) {
                    $skipped++;

                    continue;
                }

                Invoice::create([
                    'student_id' => $student->id,
                    'title' => $data['title'],
                    'amount' => $data['amount'],
                    'due_date' => $data['due_date'],
                    'semester' => $data['semester'] ?? null,
                    'academic_year' => $data['academic_year'] ?? null,
                ]);

                $created++;
            }
        });

        $message = $created > 0
            ? "{$created} tagihan berhasil dibuat."
            : 'Tidak ada tagihan baru yang dibuat.';

        if ($skipped > 0) {
            $message .= " {$skipped} siswa dilewati karena sudah punya tagihan \"{$data['title']}\".";
        }

        return redirect()->route('admin.invoices.index')->with('success', $message);
    }
}