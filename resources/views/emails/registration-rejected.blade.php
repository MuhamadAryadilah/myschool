<x-mail::message>
# Pendaftaran belum dapat diterima

Yth. {{ $registration->guardian_name }},

Mohon maaf, pendaftaran **{{ $registration->student_name }}** belum dapat kami terima.

**Alasan:** {{ $registration->admin_note }}

Jika ada pertanyaan, silakan hubungi sekretariat sekolah.
</x-mail::message>