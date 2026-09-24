<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>رمز إعادة تعيين كلمة المرور</title>
</head>
<body style="margin:0;padding:24px;background:#eef2f9;font-family:Tahoma,Arial,sans-serif;color:#0a1a3f;">
    <div style="max-width:440px;margin:0 auto;background:#ffffff;border:1px solid #d3dcec;border-radius:20px;padding:28px;">
        <p style="margin:0 0 4px;font-size:22px;font-weight:bold;">وقتي</p>
        <p style="margin:0 0 20px;color:#3f5079;font-size:14px;">
            @if($name) أهلاً {{ $name }}، @endif
            وصلنا طلب لإعادة تعيين كلمة المرور. استخدم الرمز التالي:
        </p>

        <div style="text-align:center;margin:24px 0;padding:18px;background:#eef3ff;border:1px solid #d6e0ff;border-radius:14px;">
            <span style="display:inline-block;direction:ltr;font-size:34px;font-weight:bold;letter-spacing:10px;color:#2b59ff;">{{ $code }}</span>
        </div>

        <p style="margin:0 0 8px;color:#3f5079;font-size:14px;">
            الرمز صالح لمدة {{ $minutes }} دقائق ويُستخدم مرة وحدة.
        </p>
        <p style="margin:0;color:#7d8aa8;font-size:13px;">
            إذا ما طلبت إعادة تعيين كلمة المرور، تجاهل هذه الرسالة ولا تشارك الرمز مع أحد.
        </p>
    </div>
</body>
</html>