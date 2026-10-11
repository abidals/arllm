<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطأ مؤقت — {{ site_name() }}</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: #f9fafb; color: #111827; font-family: 'IBM Plex Sans Arabic', sans-serif; }
        .card { text-align: center; padding: 3rem; }
        .code { font-size: 4rem; font-weight: 900; color: #d1d5db; }
        a { display: inline-block; margin-top: 1.5rem; background: #111827; color: #fff;
            text-decoration: none; padding: 0.75rem 1.75rem; border-radius: 0.6rem; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <h1 style="font-size: 1.6rem; margin: 0.5rem 0;">حدث خطأ تقني مؤقت</h1>
        <p style="color: #6b7280; max-width: 26rem; margin: 0 auto; line-height: 1.9;">
            نعتذر عن هذا الخلل. قد يكون مؤقتًا — حاول تحديث الصفحة أو العودة لاحقًا دقائق.
        </p>
        <a href="{{ url('/') }}">← العودة إلى الرئيسية</a>
    </div>
</body>
</html>