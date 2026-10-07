<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tambah Langkah</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <form method="POST" action="{{ route('admin.registration-steps.store') }}">
                    @include('admin.registration-steps._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>