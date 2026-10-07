<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExtracurricularController extends Controller
{
    public function index()
    {
        $extracurriculars = Extracurricular::withCount('students')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $unassigned = Student::whereDoesntHave('extracurriculars')->count();

        return view('admin.extracurriculars.index', compact('extracurriculars', 'unassigned'));
    }

    public function create()
    {
        return view('admin.extracurriculars.create');
    }

    public function store(Request $request)
    {
        Extracurricular::create($this->validated($request));

        return redirect()->route('admin.extracurriculars.index')
            ->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function show(Request $request, Extracurricular $extracurricular)
    {
        $cariAnggota = is_string($request->query('m')) ? trim($request->query('m')) : '';
        $likeAnggota = '%'.addcslashes($cariAnggota, '%_\\').'%';
        $totalAnggota = $extracurricular->students()->count();
        $anggota = $extracurricular->students()
            ->with('parent')
            ->when($cariAnggota !== '', fn ($q) => $q->where(fn ($w) => $w->where('students.name', 'like', $likeAnggota)->orWhere('students.nis', 'like', $likeAnggota)))
            ->orderBy('students.kelas')
            ->orderBy('students.name')
            ->paginate(15, ['*'], 'anggota')
            ->withQueryString();

        $others = Extracurricular::active()
            ->whereKeyNot($extracurricular->id)
            ->orderBy('name')
            ->get();

        // Pencarian siswa untuk ditambahkan (dikerjakan di server, dibatasi per halaman).
        $cari = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $kelasDipilih = is_string($request->query('kelas')) ? trim($request->query('kelas')) : '';
        $kelasList = Student::query()->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $mencari = $cari !== '' || $kelasDipilih !== '';

        $results = null;
        if ($mencari) {
            $like = '%'.addcslashes($cari, '%_\\').'%';

            // Siswa yang belum menjadi anggota eskul ini. Eskul lain miliknya ikut ditampilkan.
            $results = Student::with('extracurriculars')
                ->whereDoesntHave('extracurriculars', fn ($q) => $q->where('extracurriculars.id', $extracurricular->id))
                ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('nis', 'like', $like)))
                ->when($kelasDipilih !== '', fn ($q) => $q->where('kelas', $kelasDipilih))
                ->orderBy('name')
                ->paginate(10, ['*'], 'cari')
                ->withQueryString();
        }

        return view('admin.extracurriculars.show', compact(
            'extracurricular', 'others', 'results', 'cari', 'kelasDipilih', 'kelasList', 'mencari', 'anggota', 'totalAnggota', 'cariAnggota'
        ));
    }

    public function edit(Extracurricular $extracurricular)
    {
        return view('admin.extracurriculars.edit', compact('extracurricular'));
    }

    public function update(Request $request, Extracurricular $extracurricular)
    {
        $extracurricular->update($this->validated($request, $extracurricular));

        return redirect()->route('admin.extracurriculars.index')
            ->with('success', 'Ekstrakurikuler berhasil diubah.');
    }

    public function destroy(Extracurricular $extracurricular)
    {
        if ($extracurricular->students()->exists()) {
            return back()->withErrors([
                'extracurricular' => 'Eskul ini masih punya anggota. Keluarkan anggotanya dulu, atau nonaktifkan saja.',
            ]);
        }

        $extracurricular->delete();

        return redirect()->route('admin.extracurriculars.index')
            ->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    /**
     * Tambah siswa ke eskul ini. Eskul lain milik siswa tidak berubah.
     */
    public function addMember(Request $request, Extracurricular $extracurricular)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ], [], ['student_id' => 'siswa']);

        if (! $extracurricular->is_active) {
            return back()->withErrors(['student_id' => 'Eskul ini nonaktif. Aktifkan dulu sebelum menambah anggota.']);
        }

        $student = Student::findOrFail($data['student_id']);
        $hasil = $extracurricular->students()->syncWithoutDetaching([$student->id]);

        if ($hasil['attached'] === []) {
            return back()->with('success', "{$student->name} sudah menjadi anggota {$extracurricular->name}.");
        }

        return back()->with('success', "{$student->name} ditambahkan ke {$extracurricular->name}.");
    }

    /**
     * Keluarkan siswa dari eskul "from". Kalau ada eskul tujuan, siswa sekaligus dimasukkan ke sana.
     * Eskul lain milik siswa tidak disentuh.
     */
    public function updateStudent(Request $request, Student $student)
    {
        $data = $request->validate([
            'from' => ['required', 'exists:extracurriculars,id'],
            'extracurricular_id' => ['nullable', Rule::exists('extracurriculars', 'id')->where('is_active', true)],
        ], [
            'extracurricular_id.exists' => 'Pilih ekstrakurikuler yang aktif.',
        ], [
            'from' => 'eskul asal',
            'extracurricular_id' => 'ekstrakurikuler',
        ]);

        $from = Extracurricular::findOrFail($data['from']);
        $target = filled($data['extracurricular_id'] ?? null)
            ? Extracurricular::find($data['extracurricular_id'])
            : null;

        DB::transaction(function () use ($student, $from, $target) {
            $student->extracurriculars()->detach($from->id);

            if ($target) {
                $student->extracurriculars()->syncWithoutDetaching([$target->id]);
            }
        });

        $message = $target
            ? "{$student->name} dipindahkan dari {$from->name} ke {$target->name}."
            : "{$student->name} dikeluarkan dari {$from->name}.";

        return back()->with('success', $message);
    }

    private function validated(Request $request, ?Extracurricular $extracurricular = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('extracurriculars', 'name')->ignore($extracurricular?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'schedule' => ['nullable', 'string', 'max:150'],
        ], [
            'name.unique' => 'Nama eskul ini sudah ada.',
        ], [
            'name' => 'nama eskul',
            'description' => 'deskripsi',
            'schedule' => 'jadwal',
        ]);

        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        return $data;
    }
}