<x-mail::message>
# Pendaftaran diterima

Yth. {{ $registration->guardian_name }},

Pendaftaran **{{ $student->name }}** di MySchool telah **diterima**.

<x-mail::table>
| | |
|:--|:--|
| NIS | {{ $student->nis }} |
| Kelas | {{ $student->kelas }} |
</x-mail::table>

@if ($newAccount)
Akun MySchool Anda sudah dibuat dengan email **{{ $registration->email }}**. Klik tombol berikut untuk membuat password dan masuk:

<x-mail::button :url="$resetUrl">
Buat password
</x-mail::button>

Link ini berlaku selama {{ intdiv(config('auth.passwords.users.expire'), 1440) }} hari. Jika sudah kedaluwarsa, buka halaman masuk, pilih **Lupa password**, lalu masukkan email ini.
@else
Siswa sudah ditambahkan ke akun Anda yang sudah ada. Silakan masuk dengan email **{{ $registration->email }}** untuk melihat data dan tagihannya.

<x-mail::button :url="route('login')">
Masuk
</x-mail::button>
@endif
</x-mail::message>