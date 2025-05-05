@props([
    'countryName' => 'phone_country_code',
    'numberName' => 'phone_number',
    'countryId' => 'phone_country_code',
    'numberId' => 'phone_number',
    'countryValue' => '',
    'numberValue' => '',
    'disabled' => false,
])

<div class="flex items-center mt-1">
    <div class="flex mr-1">
        <!-- + symbol -->
        <div class="flex items-center justify-center w-7 h-10 border dark:bg-gray-900 dark:border-gray-700 rounded-l-md">
            <span class="text-sm text-gray-900 dark:text-gray-300">+</span>
        </div>

        <!-- Country code -->
        <div class="flex items-center">
            <input @disabled($disabled) type="text" inputmode="numeric" pattern="[0-9]*"
                name="{{ $countryName }}" id="{{ $countryId }}" maxlength="3" placeholder="62"
                value="{{ old($countryName, $countryValue) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="pl-1 w-12 h-10 border dark:bg-gray-900 dark:border-gray-700 text-sm text-gray-900 dark:text-gray-300 rounded-r-md text-center">
        </div>
    </div>

    <!-- Phone number -->
    <input @disabled($disabled) type="text" inputmode="numeric" pattern="[0-9]*" name="{{ $numberName }}"
        id="{{ $numberId }}" maxlength="12" placeholder="8123456789" value="{{ old($numberName, $numberValue) }}"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        class="flex-1 h-10 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
</div>
