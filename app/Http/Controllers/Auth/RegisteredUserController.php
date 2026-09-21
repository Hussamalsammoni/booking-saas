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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /** مدة التجربة المجانية لكل محل جديد (بالأيام) */
    private const TRIAL_DAYS = 14;

    /** أسماء ممنوعة كـ subdomain */
    private const RESERVED_SLUGS = ['www', 'admin', 'api', 'app', 'mail', 'central', 'static', 'assets'];

    /**
     * Display the registration view.
     */
    public function create()
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_answer' => $num1 + $num2]);

        return Inertia::render('Auth/Register', [
            'captchaQuestion' => "كم ناتج {$num1} + {$num2} ؟",
            'trialDays' => self::TRIAL_DAYS,
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
            'captcha' => 'required|numeric',
        ], [
            'captcha.required' => 'اكتب جواب السؤال الأمني.',
            'captcha.numeric' => 'جواب السؤال الأمني لازم يكون رقم.',
        ]);

        // التحقق من الكابتشا: الجواب بينحذف من الجلسة بعد أول محاولة (سؤال جديد لكل محاولة)
        $expected = $request->session()->pull('captcha_answer');

        if ($expected === null || (int) $request->captcha !== (int) $expected) {
            throw ValidationException::withMessages([
                'captcha' => 'جواب السؤال الأمني غير صحيح، جرّب السؤال الجديد.',
            ]);
        }

        $slug = $this->uniqueSlug($request->shop_name);
        $tenant = null;

        try {
            $tenant = Tenant::create([
                'id' => $slug,
                'shop_name' => $request->shop_name,
                'is_active' => true,
                // فترة تجربة تلقائية، وبعدها المحل بيتعطل لحد ما تمدد اشتراكو من لوحة الأدمن
                'subscription_ends_at' => now()->addDays(self::TRIAL_DAYS)->toDateTimeString(),
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
        } catch (\Throwable $e) {
            report($e);

            tenancy()->end();

            // ما منخلي محل نص مبني (سجل بدون قاعدة بيانات أو بدون مالك)
            if ($tenant) {
                try {
                    $tenant->delete();
                } catch (\Throwable $ignored) {
                    report($ignored);
                }
            }

            throw ValidationException::withMessages([
                'shop_name' => 'تعذّر إنشاء المحل حالياً، حاول مرة أخرى بعد قليل.',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        // نفس البروتوكول والمنفذ يلي جاي منهم الطلب (بالإنتاج https بدون منفذ)
        $port = (int) $request->getPort();
        $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;

        return Inertia::location(
            $request->getScheme().'://'.$slug.'.'.$centralDomain.$portPart.RouteServiceProvider::HOME
        );
    }

    /**
     * اسم فريد للمحل بالدومين: بيتعامل مع الأسماء الفاضية (مثلاً إيموجي بس)،
     * الطويلة، والمحجوزة (www, admin...)، وبيضيف رقم لو الاسم مأخوذ.
     */
    private function uniqueSlug(string $shopName): string
    {
        $base = trim(Str::limit(Str::slug($shopName), 40, ''), '-');

        if ($base === '' || in_array($base, self::RESERVED_SLUGS, true)) {
            $base = 'shop';
        }

        $slug = $base;
        $counter = 1;

        while (Tenant::find($slug)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}