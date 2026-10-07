<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');
        $filters = [
            'q' => is_string($q) ? trim($q) : '',
            'status' => is_string($status) ? $status : '',
        ];

        $registrations = Registration::filter($filters)
            ->orderByRaw("status = 'menunggu' desc")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Registration::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.registrations.index', compact('registrations', 'counts'));
    }

    public function show(Registration $registration)
    {
        $registration->load('student', 'user', 'extracurriculars');

        $existingUser = User::where('email', $registration->email)->first();
        $emailIsAdmin = $existingUser && $existingUser->role !== 'orang_tua';
        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.registrations.show', compact('registration', 'existingUser', 'emailIsAdmin', 'kelasList'));
    }

    public function approve(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
            'kelas' => ['required', 'string', 'max:50'],
        ], [], [
            'nis' => 'NIS',
            'kelas' => 'kelas',
        ]);

        [$user, $student, $newAccount] = DB::transaction(function () use ($registration, $data) {
            $reg = Registration::lockForUpdate()->findOrFail($registration->id);

            if (! $reg->isPending()) {
                throw ValidationException::withMessages(['registration' => 'Pendaftaran ini sudah diproses.']);
            }

            $user = User::where('email', $reg->email)->first();
            $newAccount = false;

            if ($user && $user->role !== 'orang_tua') {
                throw ValidationException::withMessages([
                    'email' => 'Email ini dipakai akun admin, tidak bisa dijadikan akun orang tua.',
                ]);
            }

            if (! $user) {
                $user = new User([
                    'name' => $reg->guardian_name,
                    'email' => $reg->email,
                    'password' => Str::random(40),
                    'phone' => $reg->phone,
                ]);
                $user->role = 'orang_tua';
                $user->email_verified_at = now();
                $user->save();
                $newAccount = true;
            }

            if (! $newAccount && blank($user->phone) && filled($reg->phone)) {
                $user->forceFill(['phone' => $reg->phone])->save();
            }

            $student = Student::create([
                'user_id' => $user->id,
                'name' => $reg->student_name,
                'nis' => $data['nis'],
                'kelas' => $data['kelas'],
            ]);

            $reg->update([
                'status' => 'diterima',
                'student_id' => $student->id,
                'user_id' => $user->id,
                'processed_at' => now(),
                'admin_note' => null,
            ]);

            $eskulAktif = $reg->extracurriculars()->where('extracurriculars.is_active', true)->pluck('extracurriculars.id');

            if ($eskulAktif->isNotEmpty()) {
                $student->extracurriculars()->syncWithoutDetaching($eskulAktif->all());
            }

            return [$user, $student, $newAccount];
        });

        $registration->refresh();
        $mailFailed = false;

        try {
            $resetUrl = null;

            if ($newAccount) {
                $token = Password::broker()->createToken($user);
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            }

            Mail::to($user->email)->send(new RegistrationApproved($registration, $student, $newAccount, $resetUrl));
        } catch (\Throwable $e) {
            report($e);
            $mailFailed = true;
        }

        $message = $newAccount
            ? 'Pendaftaran diterima. Siswa dan akun orang tua sudah dibuat.'
            : 'Pendaftaran diterima. Siswa ditambahkan ke akun orang tua yang sudah ada.';

        $redirect = redirect()->route('admin.registrations.show', $registration)->with('success', $message);

        if ($mailFailed) {
            $redirect->with('warning', 'Email ke orang tua gagal terkirim. Minta orang tua memakai "Lupa password" di halaman masuk dengan email '.$registration->email.'.');
        }

        return $redirect;
    }

    public function reject(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:255'],
        ], [], [
            'admin_note' => 'alasan penolakan',
        ]);

        DB::transaction(function () use ($registration, $data) {
            $reg = Registration::lockForUpdate()->findOrFail($registration->id);

            if (! $reg->isPending()) {
                throw ValidationException::withMessages(['registration' => 'Pendaftaran ini sudah diproses.']);
            }

            $reg->update([
                'status' => 'ditolak',
                'admin_note' => $data['admin_note'],
                'processed_at' => now(),
            ]);
        });

        $registration->refresh();
        $mailFailed = false;

        try {
            Mail::to($registration->email)->send(new RegistrationRejected($registration));
        } catch (\Throwable $e) {
            report($e);
            $mailFailed = true;
        }

        $redirect = redirect()->route('admin.registrations.show', $registration)
            ->with('success', 'Pendaftaran ditolak.');

        if ($mailFailed) {
            $redirect->with('warning', 'Email pemberitahuan ke orang tua gagal terkirim.');
        }

        return $redirect;
    }
}