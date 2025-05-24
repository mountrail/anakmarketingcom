{{-- resources\views\auth\register-personal-info.blade.php --}}
<!-- Name -->
<div>
    <x-input-label for="register_name" :value="__('Name')" />
    <x-text-input id="register_name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
        autofocus autocomplete="name" />
    <div id="name-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<!-- Email Address -->
<div class="mt-4">
    <x-input-label for="register_email" :value="__('Email')" />
    <x-text-input id="register_email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
        autocomplete="username" />
    <div id="email-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<!-- Phone Number -->
<div class="mt-4">
    <x-input-label for="register_phone_number" :value="__('Phone')" />
    <x-phone-input countryName="phone_country_code" numberName="phone_number" countryId="register_phone_country_code"
        numberId="register_phone_number" countryValue="{{ old('phone_country_code', '62') }}"
        numberValue="{{ old('phone_number') }}" />
    <div id="phone_country_code-error" class="text-red-500 mt-1 text-sm hidden">
    </div>
    <div id="phone_number-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2" />
    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
</div>

<!-- Password -->
<div class="mt-4">
    <x-input-label for="register_password" :value="__('Password')" />
    <x-text-input id="register_password" class="block mt-1 w-full" type="password" name="password" required
        autocomplete="new-password" />
    <div id="password-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<!-- Confirm Password -->
<div class="mt-4">
    <x-input-label for="register_password_confirmation" :value="__('Confirm Password')" />
    <x-text-input id="register_password_confirmation" class="block mt-1 w-full" type="password"
        name="password_confirmation" required autocomplete="new-password" />
    <div id="password_confirmation-error" class="text-red-500 mt-1 text-sm hidden"></div>
    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
</div>
