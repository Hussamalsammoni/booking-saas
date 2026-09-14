<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
   public function create()
{
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    session(['captcha_answer' => $num1 + $num2]);

    return Inertia::render('Auth/Register', [
        'captchaQuestion' => "What is {$num1} + {$num2}?"
    ]);
}

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $baseSlug = Str::slug($request->shop_name);
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::find($slug)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $tenant = Tenant::create([
            'id' => $slug,
            'shop_name' => $request->shop_name,
        ]);

        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);

        $tenant->domains()->create([
            'domain' => $slug.'.'.$centralDomain,
        ]);

        tenancy()->initialize($tenant);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'owner',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return Inertia::location('http://'.$slug.'.'.$centralDomain.':8000'.RouteServiceProvider::HOME);
    }
}