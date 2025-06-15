@props([
    'disabled' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => '--Select--',
    'showPlaceholder' => true,
])

<select {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full']) }}>
    @if ($showPlaceholder)
        <option value="" {{ $selected === null ? 'selected' : '' }}>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $key => $value)
        @if (is_array($options) && array_keys($options) !== range(0, count($options) - 1))
            {{-- Associative array: use key as value, value as display text --}}
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : '' }}>
                {{ $value }}
            </option>
        @else
            {{-- Indexed array: use value as both value and display text --}}
            <option value="{{ $value }}" {{ $selected === $value ? 'selected' : '' }}>
                {{ $value }}
            </option>
        @endif
    @endforeach
</select>
