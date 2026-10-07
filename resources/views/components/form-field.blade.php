@props(['name', 'label', 'type' => 'text', 'required' => false, 'hint' => null, 'value' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}@if ($required) <span class="text-red-500">*</span>@endif
    </label>

    @if ($slot->isEmpty())
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
    @else
        {{ $slot }}
    @endif

    @if ($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>