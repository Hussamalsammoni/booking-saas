<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * يحوّل رابط مشاركة خرائط جوجل (حتى الرابط القصير maps.app.goo.gl)
 * إلى إحداثيات دقيقة [lat, lng] لدبوس المحل.
 */
class MapCoords
{
    private const HOST = '#^https://([a-z0-9-]+\.)*(goo\.gl|google\.[a-z.]+)/#i';

    public static function fromUrl(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '' || !preg_match(self::HOST, $url)) {
            return null;
        }

        try {
            $current = $url;

            for ($i = 0; $i < 6; $i++) {
                if ($coords = self::extract($current)) {
                    return $coords;
                }

                $res = Http::withOptions(['allow_redirects' => false])
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->timeout(8)
                    ->get($current);

                $next = $res->header('Location');
                if (!$next) {
                    return null;
                }

                if (str_starts_with($next, '/')) {
                    $p = parse_url($current);
                    $next = $p['scheme'] . '://' . $p['host'] . $next;
                }

                // ما نتبع إلا روابط جوجل (حماية من SSRF)
                if (!preg_match(self::HOST, $next)) {
                    return self::extract($next);
                }

                $current = $next;
            }
        } catch (\Throwable $e) {
            Log::warning('MapCoords: ' . $e->getMessage());
        }

        return null;
    }

    private static function extract(string $s): ?array
    {
        $s = urldecode($s);

        // !3d..!4d.. = موقع المكان نفسه (الأدق)، و @lat,lng = مركز الخريطة
        $patterns = [
            '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
            '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
            '/[?&](?:q|ll|query|destination)=(-?\d+\.\d+),(-?\d+\.\d+)/',
        ];

        foreach ($patterns as $re) {
            if (preg_match($re, $s, $m)) {
                return [(float) $m[1], (float) $m[2]];
            }
        }

        return null;
    }
}