<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\Student;
use App\Models\User;
use App\Support\CsvCell;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = $this->filtered($request)
            ->with('parent', 'extracurriculars')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $extracurriculars = Extracurricular::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'kelasList', 'extracurriculars'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('parent', 'extracurriculars');
        $filename = 'siswa-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // Penanda UTF-8 supaya Excel membaca karakter dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['NIS', 'Nama', 'Kelas', 'Eskul', 'Orang Tua', 'Email Orang Tua'], ';', '"', '');

            $query->chunkById(500, function ($students) use ($out) {
                foreach ($students as $student) {
                    fputcsv($out, [
                        CsvCell::safe($student->nis),
                        CsvCell::safe($student->name),
                        CsvCell::safe($student->kelas),
                        CsvCell::safe($student->extracurriculars->pluck('name')->join(', ')),
                        CsvCell::safe($student->parent?->name),
                        CsvCell::safe($student->parent?->email),
                    ], ';', '"', '');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $parents = User::where('role', 'orang_tua')->orderBy('name')->get();
        $extracurriculars = Extracurricular::active()->orderBy('name')->get();

        return view('admin.students.create', compact('parents', 'extracurriculars'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), [], $this->attributes());

        $student = Student::create(Arr::except($data, ['extracurricular_ids']));
        $student->extracurriculars()->sync($data['extracurricular_ids'] ?? []);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        $parents = User::where('role', 'orang_tua')->orderBy('name')->get();
        $sudahIkut = $student->extracurriculars->pluck('id')->all();
        $extracurriculars = Extracurricular::where('is_active', true)
            ->orWhereIn('id', $sudahIkut)
            ->orderBy('name')
            ->get();

        return view('admin.students.edit', compact('student', 'parents', 'extracurriculars'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate($this->rules($student), [], $this->attributes());

        $student->update(Arr::except($data, ['extracurricular_ids']));
        $student->extracurriculars()->sync($data['extracurricular_ids'] ?? []);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diubah.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    /**
     * Filter cari, kelas, dan eskul. Dipakai bersama oleh daftar dan ekspor.
     */
    private function filtered(Request $request): Builder
    {
        $q = $request->query('q');
        $kelas = $request->query('kelas');
        $eskul = $request->query('eskul');

        return Student::query()
            ->when(is_string($q) && $q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhereHas('parent', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(is_string($kelas) && $kelas !== '', fn ($query) => $query->where('kelas', $kelas))
            ->when(is_string($eskul) && $eskul !== '', function ($query) use ($eskul) {
                $eskul === 'none'
                    ? $query->whereDoesntHave('extracurriculars')
                    : $query->whereHas('extracurriculars', fn ($e) => $e->where('extracurriculars.id', $eskul));
            });
    }

    private function rules(?Student $student = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => [
                'required', 'string', 'max:50',
                $student ? Rule::unique('students', 'nis')->ignore($student->id) : 'unique:students,nis',
            ],
            'kelas' => ['required', 'string', 'max:50'],
            'user_id' => ['nullable', 'exists:users,id'],
            'extracurricular_ids' => ['nullable', 'array'],
            'extracurricular_ids.*' => ['integer', $this->eskulRule($student)],
        ];
    }

    private function attributes(): array
    {
        return [
            'extracurricular_ids' => 'ekstrakurikuler',
            'extracurricular_ids.*' => 'ekstrakurikuler',
        ];
    }

    /**
     * Eskul harus aktif. Eskul yang sudah diikuti siswa tetap boleh disimpan walau sudah nonaktif.
     */
    private function eskulRule(?Student $student = null)
    {
        $sudahIkut = $student ? $student->extracurriculars()->get()->pluck('id')->all() : [];

        return Rule::exists('extracurriculars', 'id')->where(function ($q) use ($sudahIkut) {
            $q->where(function ($w) use ($sudahIkut) {
                $w->where('is_active', true);

                if ($sudahIkut !== []) {
                    $w->orWhereIn('id', $sudahIkut);
                }
            });
        });
    }
}