@props(['disabled' => false, 'options' => [], 'selected' => null, 'placeholder' => '--Select--'])

<select {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full']) }}>
    <option value="" {{ $selected === null ? 'selected' : '' }}>{{ $placeholder }}</option>
    @foreach ($options as $option)
        <option value="{{ $option }}" {{ $selected === $option ? 'selected' : '' }}>
            {{ $option }}
        </option>
    @endforeach
</select>
