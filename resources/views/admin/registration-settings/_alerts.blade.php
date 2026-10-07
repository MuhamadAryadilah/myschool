@if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status"
         class="p-3 bg-brand-100 text-brand-800 rounded-md">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="p-3 bg-red-100 text-red-800 rounded-md text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif