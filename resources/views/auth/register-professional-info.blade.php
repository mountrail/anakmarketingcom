{{-- resources\views\auth\register-professional-info.blade.php --}}
{{-- Industry Field --}}
<div class="mt-4">
    <x-input-label for="register_industry" :value="__('Industry')" />
    <x-select-input id="register_industry" name="industry" :options="[
        'Beauty',
        'Consumer',
        'Education',
        'Financial or Banking',
        'Health',
        'Media',
        'Products',
        'Property',
        'Services',
        'Tech',
        'Others',
    ]" :selected="old('industry')" :placeholder="__('--Select Industry--')"
        class="mt-1 block w-full" />
    <div id="industry-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error class="mt-2" :messages="$errors->get('industry')" />
</div>

{{-- Seniority Field --}}
<div class="mt-4">
    <x-input-label for="register_seniority" :value="__('Seniority')" />
    <x-select-input id="register_seniority" name="seniority" :options="[
        'Junior Staff',
        'Senior Staff',
        'Assistant Manager',
        'Manager',
        'Vice President',
        'Director (C-Level)',
        'Owner',
        'Others',
    ]" :selected="old('seniority')" :placeholder="__('--Select Seniority--')"
        class="mt-1 block w-full" />
    <div id="seniority-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error class="mt-2" :messages="$errors->get('seniority')" />
</div>

{{-- Company Size Field --}}
<div class="mt-4">
    <x-input-label for="register_company_size" :value="__('Company Size')" />
    <x-select-input id="register_company_size" name="company_size" :options="['0-10', '11-50', '51-100', '101-500', '501++']" :selected="old('company_size')"
        :placeholder="__('--Select Company Size--')" class="mt-1 block w-full" />
    <div id="company_size-error" class="text-red-500 mt-1 text-sm hidden">
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
</div>

{{-- City Field --}}
<div class="mt-4">
    <x-input-label for="register_city" :value="__('City')" />
    <x-select-input id="register_city" name="city" :options="['Bandung', 'Jabodetabek', 'Jogjakarta', 'Makassar', 'Medan', 'Surabaya', 'Others']" :selected="old('city')" :placeholder="__('--Select City--')"
        class="mt-1 block w-full" />
    <div id="city-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error class="mt-2" :messages="$errors->get('city')" />
</div>
