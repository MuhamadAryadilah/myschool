<x-mail::message>
# Pendaftaran siswa baru

Ada pendaftaran baru yang menunggu pemeriksaan.

<x-mail::table>
| | |
|:--|:--|
| Nomor | {{ $registration->reference() }} |
| Calon siswa | {{ $registration->student_name }} |
| Orang tua/wali | {{ $registration->guardian_name }} ({{ $registration->guardian_relation }}) |
| Nomor HP | {{ $registration->phone }} |
| Email | {{ $registration->email }} |
</x-mail::table>

<x-mail::button :url="route('admin.registrations.show', $registration)">
Periksa pendaftaran
</x-mail::button>
</x-mail::message>