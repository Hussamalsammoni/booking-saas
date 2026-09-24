<?php

// المسار: app/Http/Controllers/ShopLookupController.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class ShopLookupController extends Controller
{
    /**
     * يتحقق إن كان في محل بهالمعرّف، ويرجّع عنوانه الحقيقي.
     * بتستخدمه نافذة "تسجيل الدخول" بصفحة الهبوط قبل ما تحوّل الزائر.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $slug = Str::of((string) $request->query('shop'))
            ->lower()
            ->trim()
            ->replaceMatches('#^https?://#', '')
            ->before('.')
            ->replaceMatches('/[^a-z0-9-]/', '')
            ->toString();

        if ($slug === '') {
            return response()->json([
                'message' => 'اكتب معرّف محلك بالأحرف الإنكليزية، مثل: royal-look',
            ], 422);
        }

        $domain = Domain::where('tenant_id', $slug)->value('domain')
            ?? Domain::where('domain', 'like', $slug . '.%')->value('domain');

        if (! $domain) {
            return response()->json([
                'message' => 'ما لقينا محلاً بهذا المعرّف. تأكد من كتابته بشكل صحيح وحاول مرة ثانية.',
            ], 404);
        }

        return response()->json(['domain' => $domain]);
    }
}