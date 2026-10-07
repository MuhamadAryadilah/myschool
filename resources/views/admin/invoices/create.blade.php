<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tambah Tagihan</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <form action="{{ route('admin.invoices.store') }}" method="POST">
                    @include('admin.invoices._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>