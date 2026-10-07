<x-public-layout title="Pendaftaran Siswa Baru">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Pendaftaran Siswa Baru</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
            Pendaftaran untuk kelas 7. Isi data dengan lengkap dan benar. Kolom bertanda <span class="text-red-500">*</span> wajib diisi.
        </p>

        @if (\App\Support\RegistrationAge::description())
            <div class="mt-4 p-3 rounded-md border border-amber-300 bg-amber-50 text-amber-900 text-sm dark:border-amber-400/60 dark:bg-amber-400/10 dark:text-amber-50">
                {{ \App\Support\RegistrationAge::description() }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 p-3 bg-red-100 text-red-800 rounded-md text-sm">
                Ada isian yang perlu diperbaiki. Periksa kolom yang ditandai di bawah.
            </div>
        @endif

        @include('registration._steps', ['compact' => true])

        <form method="POST" action="{{ route('registration.store') }}" x-data="{ busy: false }" @submit="busy = true" class="mt-6 space-y-6 sm:space-y-8">
            @csrf

            {{-- Jebakan bot: jangan diisi --}}
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Jangan diisi</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <section class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Data calon siswa</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-form-field class="sm:col-span-2" name="student_name" label="Nama lengkap" :required="true" />

                    <x-form-field name="gender" label="Jenis kelamin" :required="true">
                        <select id="gender" name="gender"
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">- Pilih -</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                    </x-form-field>

                    <x-form-field name="religion" label="Agama" :required="true">
                        <select id="religion" name="religion"
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">- Pilih -</option>
                            @foreach (\App\Models\Registration::RELIGIONS as $religion)
                                <option value="{{ $religion }}" @selected(old('religion') === $religion)>{{ $religion }}</option>
                            @endforeach
                        </select>
                    </x-form-field>

                    <x-form-field name="birth_place" label="Tempat lahir" :required="true" />
                    <x-form-field name="birth_date" label="Tanggal lahir" type="date" :required="true" />

                    <x-form-field name="nisn" label="NISN (jika ada)" hint="10 digit angka. Boleh dikosongkan." />
                    <x-form-field name="previous_school" label="Asal sekolah (SD/MI)" :required="true" />
                    @if (($extracurriculars ?? collect())->isNotEmpty())
                        @php
                            $maxEskul = \App\Models\Registration::MAX_EXTRACURRICULARS;
                            $pilihanLama = collect(old('extracurricular_ids', []))->map(fn ($id) => (string) $id)->values()->all();
                        @endphp
                        <div class="sm:col-span-2" x-data="{ chosen: {{ \Illuminate\Support\Js::from($pilihanLama) }}, max: {{ $maxEskul }} }">
                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ekstrakurikuler pilihan</span>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach ($extracurriculars as $eskul)
                                    <label class="flex items-start gap-2 rounded-md border border-gray-200 dark:border-gray-700 p-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="extracurricular_ids[]" value="{{ $eskul->id }}" x-model="chosen"
                                               :disabled="chosen.length >= max && !chosen.includes('{{ $eskul->id }}')"
                                               class="mt-0.5 rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500 disabled:opacity-50">
                                        <span>{{ $eskul->name }}{{ $eskul->schedule ? ' ('.$eskul->schedule.')' : '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Pilih paling banyak {{ $maxEskul }}. Boleh dikosongkan. Sekolah masih bisa mengubahnya nanti.
                                <span x-text="chosen.length + ' dari ' + max + ' dipilih'"></span>
                            </p>
                            @error('extracurricular_ids') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            @error('extracurricular_ids.*') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <x-form-field class="sm:col-span-2" name="address" label="Alamat tempat tinggal" :required="true">
                        <textarea id="address" name="address" rows="3"
                                  class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('address') }}</textarea>
                    </x-form-field>
                </div>
            </section>

            <section class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Data orang tua / wali</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-form-field name="father_name" label="Nama ayah" :required="true" />
                    <x-form-field name="mother_name" label="Nama ibu" :required="true" />

                    <x-form-field name="guardian_name" label="Nama orang tua/wali pemilik akun" :required="true"
                                  hint="Nama ini dipakai untuk akun MySchool." />

                    <x-form-field name="guardian_relation" label="Hubungan dengan siswa" :required="true">
                        <select id="guardian_relation" name="guardian_relation"
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">- Pilih -</option>
                            <option value="ayah" @selected(old('guardian_relation') === 'ayah')>Ayah</option>
                            <option value="ibu" @selected(old('guardian_relation') === 'ibu')>Ibu</option>
                            <option value="wali" @selected(old('guardian_relation') === 'wali')>Wali</option>
                        </select>
                    </x-form-field>

                    <x-form-field name="guardian_job" label="Pekerjaan" />
                    <x-form-field name="phone" label="Nomor HP / WhatsApp" type="tel" :required="true" />

                    <x-form-field class="sm:col-span-2" name="email" label="Email" type="email" :required="true"
                                  hint="Email ini menjadi akun untuk masuk ke MySchool. Setelah pendaftaran diterima, link untuk membuat password dikirim ke email ini." />
                </div>
            </section>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <button type="submit" :disabled="busy" class="w-full sm:w-auto px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-md disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-text="busy ? 'Memproses...' : 'Kirim Pendaftaran'">Kirim Pendaftaran</span>
                </button>
                <a href="{{ route('home') }}" class="text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</x-public-layout>