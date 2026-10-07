<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationSubmitted;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function create()
    {
        $extracurriculars = Extracurricular::active()->orderBy('name')->get();

        return view('registration.create', compact('extracurriculars'));
    }

    public function store(Request $request)
    {
        // Jebakan bot: kolom ini tersembunyi dan tidak akan diisi manusia
        if ($request->filled('website')) {
            return redirect()->route('registration.thanks')->with('registration_ref', '-');
        }

        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'extracurricular_ids' => is_array($request->input('extracurricular_ids'))
                ? array_values(array_filter($request->input('extracurricular_ids'), fn ($id) => filled($id)))
                : $request->input('extracurricular_ids', []),
        ]);

        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today', 'after:2000-01-01'],
            'nisn' => ['nullable', 'digits:10'],
            'religion' => ['required', Rule::in(Registration::RELIGIONS)],
            'previous_school' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_relation' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
            'guardian_job' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email' => ['required', 'email', 'max:255'],
            'extracurricular_ids' => ['nullable', 'array', 'max:'.Registration::MAX_EXTRACURRICULARS],
            'extracurricular_ids.*' => ['integer', 'distinct', Rule::exists('extracurriculars', 'id')->where('is_active', true)],
        ], [
            'gender.in' => 'Pilih jenis kelamin yang tersedia.',
            'religion.in' => 'Pilih agama yang tersedia.',
            'guardian_relation.in' => 'Pilih hubungan yang tersedia.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'birth_date.after' => 'Tanggal lahir tidak valid.',
            'nisn.digits' => 'NISN harus 10 digit angka.',
            'phone.regex' => 'Nomor HP tidak valid.',
            'extracurricular_ids.array' => 'Pilihan ekstrakurikuler tidak valid.',
            'extracurricular_ids.max' => 'Pilih paling banyak :max ekstrakurikuler.',
            'extracurricular_ids.*.integer' => 'Pilih ekstrakurikuler yang tersedia.',
            'extracurricular_ids.*.distinct' => 'Ekstrakurikuler yang sama tidak boleh dipilih dua kali.',
            'extracurricular_ids.*.exists' => 'Pilih ekstrakurikuler yang tersedia.',
        ], [
            'student_name' => 'nama siswa',
            'gender' => 'jenis kelamin',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'nisn' => 'NISN',
            'religion' => 'agama',
            'previous_school' => 'asal sekolah',
            'address' => 'alamat',
            'father_name' => 'nama ayah',
            'mother_name' => 'nama ibu',
            'guardian_name' => 'nama orang tua/wali',
            'guardian_relation' => 'hubungan',
            'guardian_job' => 'pekerjaan',
            'phone' => 'nomor HP',
            'email' => 'email',
            'extracurricular_ids' => 'ekstrakurikuler',
            'extracurricular_ids.*' => 'ekstrakurikuler',
        ]);

        $ageError = \App\Support\RegistrationAge::check(\Illuminate\Support\Carbon::parse($data['birth_date']));

        if ($ageError) {
            return back()->withErrors(['birth_date' => $ageError])->withInput();
        }

        $duplicate = Registration::where('status', 'menunggu')
            ->where('email', $data['email'])
            ->where('student_name', $data['student_name'])
            ->whereDate('birth_date', $data['birth_date'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['email' => 'Pendaftaran untuk siswa ini sudah masuk dan sedang diproses.'])
                ->withInput();
        }

        $eskulIds = collect($data['extracurricular_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $registration = DB::transaction(function () use ($data, $eskulIds) {
            $registration = Registration::create(Arr::except($data, ['extracurricular_ids']));
            $registration->extracurriculars()->sync($eskulIds);

            return $registration;
        });

        $this->notifyAdmins($registration);

        return redirect()->route('registration.thanks')
            ->with('registration_ref', $registration->reference());
    }

    public function thanks()
    {
        if (! session()->has('registration_ref')) {
            return redirect()->route('registration.create');
        }

        return view('registration.thanks', ['ref' => session('registration_ref')]);
    }

    /**
     * Beri tahu semua admin. Kegagalan kirim email tidak boleh membatalkan pendaftaran.
     */
    private function notifyAdmins(Registration $registration): void
    {
        foreach (User::where('role', 'admin')->get() as $admin) {
            try {
                Mail::to($admin)->send(new RegistrationSubmitted($registration));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}