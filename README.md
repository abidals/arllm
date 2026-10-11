# موقع إخباري عربي (Laravel)

موقع مدونة/صحفة إخبارية بالعربية (RTL) بأسلوب المجلات مثل The Atlantic، مع لوحة تحكم كاملة، صلاحيات مستخدمين، ونشر يومي مجدول.

## المزايا

- **واجهة إخبارية RTL** بخطوط Google العربية (IBM Plex Sans Arabic + Noto Naskh Arabic)
- **مقالات Markdown** مع دعم الجداول والاقتباس وقوائم المهام (GFM) — لا يُسمح بـ HTML خام لأسباب أمنية
- **استيراد ملفات `.md`** وتحويلها تلقائيًا إلى مقال
- **لوحة تحكم**: مقالات، أقسام، مستخدمون، تعليقات، إعدادات
- **صلاحيات**: مدير / محرر / كاتب (Spatie Permission)
- **النشر اليومي**: جدولة مقال واحد يوميًا يُنشر تلقائيًا (`posts:publish-scheduled`)
- **أمان**: CSP، Rate Limiting، حماية CSRF، تشفير bcrypt، تحقق صارم من أنواع الرفع، تنظيف HTML

## المستخدمون التجريبيون (بعد `db:seed`)

البريد: `admin@example.com` / `editor@example.com` / `author@example.com`

كلمة المرور تُولّد **عشوائيًا** وتظهر مرة واحدة في مخرجات السيدر (أو حدد عبر `SEED_DEMO_PASSWORD` بملف .env) — **غيّرها فورًا بعد أول دخول**.

## التشغيل محليًا

```bash
composer install
cp .env.example .env && php artisan key:generate

# MySQL عبر Docker (سنقابل 3307)
docker run -d --name arllm-mysql -e MYSQL_ROOT_PASSWORD=arllm_root_db \
  -e MYSQL_DATABASE=arllm_news -p 3307:3306 mysql:8

# عدّل .env: DB_PORT=3307 ثم
php artisan migrate --seed
php artisan storage:link
npm ci && npm run build
php artisan serve
```

## النشر على Hostinger (خطة Business)

راجع [docs/deploy-hostinger.md](docs/deploy-hostinger.md) للخطوات الكاملة (Git + SSH + Cron + SSL).

## الاختبارات

```bash
php artisan test
```