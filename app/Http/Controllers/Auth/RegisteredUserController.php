<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Log all request data to see what's happening
        Log::info('Registration request data:', $request->except(['password', 'password_confirmation']));


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone_country_code' => ['required', 'string', 'max:3'],
            'phone_number' => ['required', 'string', 'max:16'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Combine phone country code and phone number
        $phone = '+' . $request->phone_country_code . $request->phone_number;

        // Explicitly create the data array to ensure all fields are included
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
        ];

        // Log the user data before creation (password will be hashed)
        Log::info('User creation data:', $userData);

        try {
            // Try creating user with the data array
            $user = User::create($userData);

            Log::info('User created successfully with ID: ' . $user->id);

            event(new Registered($user));
            Auth::login($user);

            return redirect(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            // Log any exceptions that occur
            Log::error('Error creating user: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Rethrow the exception to maintain normal error flow
            throw $e;
        }
    }
}
