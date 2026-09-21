<?php

// المسار: app/Http/Controllers/ProfileAvatarController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    /**
     * عرض صورة المستخدم. تُقدَّم عبر الكنترولر (وليس عبر storage:link)
     * حتى تعمل مع فصل ملفات كل محل (tenant) بدون أي إعداد إضافي.
     */
    public function show(int $id)
    {
        $user = User::findOrFail($id);

        abort_unless(
            $user->avatar_path && Storage::disk('local')->exists($user->avatar_path),
            404
        );

        return Storage::disk('local')->response($user->avatar_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'اختر صورة أولاً.',
            'avatar.image'    => 'الملف المختار ليس صورة.',
            'avatar.mimes'    => 'الصيغ المسموحة: JPG أو PNG أو WEBP.',
            'avatar.max'      => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('local')->delete($user->avatar_path);
        }

        $user->avatar_path = $request->file('avatar')->store('avatars', 'local');
        $user->save();

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('local')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return back();
    }
}