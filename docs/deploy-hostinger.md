# دليل النشر إلى Hostinger (خطة Business)

## 1) إعداد Hostinger

1. من hPanel: **الاستضافة ← الإعدادات المتقدمة ← SSH** — فعّل SSH.
2. أنشئ قاعدة بيانات من: hPanel ← **قواعد بيانات MySQL** وسجّل: اسم القاعدة، المستخدم، كلمة المرور، ومستضيف القاعدة (مثل `srv-pmysql_xxx`).
3. اربط مجالك وفعّل **SSL المجاني** من: hPanel ← الأمان ← SSL.

## 2) الهيكل على السيرفر

يجب ألا تكون `public_html` جذر المشروع مباشرة؛ استخدم هذا الترتيب:

```
/home/USER/domains/domain.tld/
├── app_code/        ← كود Laravel كامل (من مستودع GitHub)
│   ├── public/      ← الجذر الوثيقي للموقع (DocumentRoot)
│   └── ...
└── Backups/         ← نسخ احتياطية
```

من hPanel: **الإعدادات ← مجلد النشر** اجعل مجلد النطاق يشير إلى `app_code/public`.

## 3) أول إعداد على السيرفر

```bash
ssh -p 65002 USER@HOST
mkdir app_code && cd app_code
git clone https://github.com/abidals/arllm.git .
cp .env.example .env
php artisan key:generate
nano .env   # عدّل القيم أدناه ثم احفظ
```

قيم `.env` الإنتاجية:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.tld
APP_LOCALE=ar
DB_CONNECTION=mysql
DB_HOST=<مستضيف القاعدة من hPanel>
DB_PORT=3306
DB_DATABASE=<اسم القاعدة>
DB_USERNAME=<المستخدم>
DB_PASSWORD=<كلمة المرور>
```

ثم:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4) مجدول النشر اليومي (Cron)

من hPanel ← **Cron Jobs** أضف مهمة كل دقيقة:

```
* * * * * cd /home/USER/domains/yourdomain.tld/app_code && php artisan schedule:run >> /dev/null 2>&1
```

هذا الأمر يشغّل `posts:publish-scheduled` تلقائيًا — ينشر المقالات المجدولة (بقاعدة أقل من مقال يوميًا).

## 5) خيارات النشر المستمر

### Option A: GitHub Actions (موصى به)

الملف `.github/workflows/deploy-hostinger.yml` ينقل الكود ويشغّل الترحيلات والكاش تلقائيًا عند كل دفعة على `main`.

أضحق في **GitHub ← Settings ← Secrets ← Actions**:

| الاسم | القيمة |
|---|---|
| HOSTINGER_HOST | عنوان سيرفر SSH |
| HOSTINGER_USER | مستخدم SSH |
| HOSTINGER_PORT | عادة `65002` |
| HOSTINGER_SSH_KEY | مفتاح SSH الخاص |
| HOSTINGER_TARGET | مثل `/home/USER/domains/yourdomain.tld` |

### Option B: Git في hPanel

من hPanel ← **Advanced ← Git**: مستودع `abidals/arllm` فرع `main` تلقائيًا، ثم شغّل الخطوات (3) في أول مرة فقط.

## 6) الأمان والتشغيل

- `APP_DEBUG=false` حتمًا في الإنتاج.
- SSL المجاني في hPanel يُجدد تلقائيًا.
- فعّل **النسخ الاحتياطي التلقائي** من hPanel (قاعدة البيانات + الملفات).
- **غيّر حساب المدير الافتراضي** (admin@example.com) بعد أول دخول.
- خطوط Google تُحمّل من نطاق Google مباشرة (مستقرة عالميًا).