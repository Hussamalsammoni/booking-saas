<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PasswordResetCodeController extends Controller
{
    /** صلاحية الرمز بالدقائق */
    private const CODE_TTL_MINUTES = 10;

    /** أقل مدة بين إرسالين (بالثواني) */
    private const RESEND_AFTER_SECONDS = 60;

    /** عدد المحاولات الغلط المسموحة لكل رمز */
    private const MAX_ATTEMPTS = 5;

    /** المدة المسموحة لتغيير كلمة المرور بعد تأكيد الرمز */
    private const VERIFIED_TTL_MINUTES = 15;

    /** الخطوة 1: صفحة إدخال البريد */
    public function create()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /** الخطوة 1: إرسال الرمز */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'اكتب بريدك الإلكتروني.',
            'email.email' => 'اكتب بريد إلكتروني صحيح.',
        ]);

        $email = Str::lower(trim($request->email));

        $this->sendCode($email);

        // نفس الرد سواء الحساب موجود أو لا، حتى ما حدا يقدر يعرف مين مسجّل عندنا
        $request->session()->put('reset_email', $email);
        $request->session()->forget('reset_verified');

        return redirect()->route('password.verify');
    }

    /** الخطوة 2: صفحة إدخال الرمز */
    public function verifyForm(Request $request)
    {
        $email = $request->session()->get('reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return Inertia::render('Auth/VerifyCode', [
            'email' => $this->maskEmail($email),
            'status' => session('status'),
            'resendAfter' => self::RESEND_AFTER_SECONDS,
        ]);
    }

    /** الخطوة 2: التحقق من الرمز */
    public function verify(Request $request): RedirectResponse
    {
        $email = $request->session()->get('reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'اكتب الرمز.',
            'code.digits' => 'الرمز مكوّن من 6 أرقام.',
        ]);

        $row = DB::table('password_reset_codes')->where('email', $email)->first();

        if (! $row) {
            throw ValidationException::withMessages([
                'code' => 'الرمز غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        if (Carbon::parse($row->expires_at)->isPast() || $row->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_codes')->where('email', $email)->delete();

            throw ValidationException::withMessages([
                'code' => 'انتهت صلاحية الرمز، اطلب رمزاً جديداً.',
            ]);
        }

        if (! Hash::check($request->code, $row->code)) {
            DB::table('password_reset_codes')->where('email', $email)->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'الرمز غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        // الرمز صحيح: منحذفه (يُستخدم مرة وحدة) ومنسمح بتغيير كلمة المرور لفترة قصيرة
        DB::table('password_reset_codes')->where('email', $email)->delete();

        $request->session()->put('reset_verified', [
            'email' => $email,
            'expires' => now()->addMinutes(self::VERIFIED_TTL_MINUTES)->timestamp,
        ]);

        return redirect()->route('password.reset');
    }

    /** إعادة إرسال الرمز */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $row = DB::table('password_reset_codes')->where('email', $email)->first();

        if ($row) {
            $readyAt = Carbon::parse($row->last_sent_at)->addSeconds(self::RESEND_AFTER_SECONDS);

            if ($readyAt->isFuture()) {
                $left = max(1, $readyAt->timestamp - now()->timestamp);

                throw ValidationException::withMessages([
                    'resend' => "انتظر {$left} ثانية قبل طلب رمز جديد.",
                ]);
            }
        }

        $this->sendCode($email);

        return back()->with('status', 'أرسلنا لك رمزاً جديداً.');
    }

    /** الخطوة 3: صفحة كلمة المرور الجديدة */
    public function resetForm(Request $request)
    {
        if (! $this->verifiedEmail($request)) {
            return $this->expired();
        }

        return Inertia::render('Auth/ResetPassword');
    }

    /** الخطوة 3: حفظ كلمة المرور الجديدة */
    public function reset(Request $request): RedirectResponse
    {
        $email = $this->verifiedEmail($request);

        if (! $email) {
            return $this->expired();
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->expired();
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget(['reset_email', 'reset_verified']);

        return redirect()->route('login')->with('status', 'تم تغيير كلمة المرور، سجّل الدخول بكلمة المرور الجديدة.');
    }

    /* ------------------------------------------------------------------ */

    /**
     * بيولّد رمز جديد وبيرسله. لو الحساب مش موجود أو لسا ما مرّت مدة الانتظار، ما بيعمل شي.
     */
    private function sendCode(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $existing = DB::table('password_reset_codes')->where('email', $email)->first();

        if ($existing && Carbon::parse($existing->last_sent_at)->addSeconds(self::RESEND_AFTER_SECONDS)->isFuture()) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            [
                'code' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                'last_sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordResetCodeMail($code, self::CODE_TTL_MINUTES, $user->name));
    }

    private function verifiedEmail(Request $request): ?string
    {
        $verified = $request->session()->get('reset_verified');

        if (! $verified || $verified['expires'] < now()->timestamp) {
            $request->session()->forget('reset_verified');

            return null;
        }

        return $verified['email'];
    }

    private function expired(): RedirectResponse
    {
        return redirect()->route('password.request')->withErrors([
            'email' => 'انتهت الجلسة، ابدأ من جديد.',
        ]);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return Str::substr($name, 0, 2).str_repeat('*', max(strlen($name) - 2, 3)).'@'.$domain;
    }
}