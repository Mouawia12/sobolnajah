# Sobol Najah (Laravel)

منصة مدرسية مبنية بـ Laravel لإدارة التسجيلات، المستخدمين، الأقسام، المنشورات، الامتحانات، الغياب، الدردشة، التوظيف، المحاسبة، والجداول.

## 1) المتطلبات

- PHP 8.1+
- Composer 2+
- MySQL 8+
- Node.js 18+ و npm
- امتدادات PHP الشائعة لـ Laravel (`mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)

## 2) التشغيل المحلي (Local Setup)

### 2.1 تثبيت الحزم

```bash
composer install
npm install
```

### 2.2 إعداد البيئة

```bash
cp .env.example .env
php artisan key:generate
```

اضبط متغيرات قاعدة البيانات في `.env`:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### 2.3 تجهيز قاعدة البيانات والملفات

```bash
php artisan migrate
php artisan storage:link
```

### 2.4 تشغيل التطبيق

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# (اختياري عند الحاجة)
php artisan queue:work --tries=3
```

## 3) التهيئة الأمنية

- الحسابات الجديدة لا تستخدم كلمات مرور ثابتة.
- النظام يفرض `must_change_password` ويعتمد رابط إعداد/إعادة تعيين كلمة المرور.
- مرفقات حساسة (مثل الامتحانات/النقاط/CV) تُحفظ خارج `public` مع ضوابط وصول.

## 4) الاختبارات

### 4.1 اختبارات أمنية أساسية

```bash
php artisan test --filter=SprintZeroSecurityTest
php artisan test --filter=OnboardingFlowTest
```

### 4.2 اختبارات الوحدات/التدفق الرئيسية

```bash
php artisan test tests/Feature/Accounting/AccountingFlowTest.php
php artisan test tests/Feature/Recruitment/RecruitmentFlowTest.php
php artisan test tests/Feature/Timetable/TimetableFlowTest.php
```

ملاحظة مهمة: شغّل اختبارات Feature بشكل تسلسلي في نفس البيئة لتجنب تعارضات `RefreshDatabase` على قاعدة بيانات واحدة.

## 5) الإعداد الإنتاجي (Production)

### 5.1 إعدادات `.env` الموصى بها

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain`
- `LOG_CHANNEL=stack`
- `CACHE_DRIVER=file` (أو Redis عند توفره)
- `QUEUE_CONNECTION=database` (أو Redis)
- `SESSION_DRIVER=file` (أو Redis)

### 5.2 أوامر ما بعد النشر

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6) Queue و Scheduler

### 6.1 Queue Worker

```bash
php artisan queue:work --tries=3 --timeout=90
```

يفضل تشغيله تحت Supervisor أو systemd في الإنتاج.

### 6.2 Scheduler (Cron)

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

## 7) النسخ الاحتياطي والاستعادة

### 7.1 نسخ احتياطي لقاعدة البيانات

```bash
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > backup_$(date +%F_%H%M).sql
```

### 7.2 نسخ احتياطي للملفات المرفوعة

```bash
tar -czf storage_backup_$(date +%F_%H%M).tar.gz storage/app
```

### 7.3 الاستعادة

```bash
mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < backup_YYYY-MM-DD_HHMM.sql
tar -xzf storage_backup_YYYY-MM-DD_HHMM.tar.gz
```

## 8) أعطال شائعة (تشخيص سريع)

- `Table ... migrations doesn't exist` أثناء الاختبارات:
  - شغّل الاختبارات تسلسليًا.
  - نفّذ `php artisan migrate:fresh --force` عند فساد بيئة الاختبار.
- مشاكل أصول الواجهة:
  - تأكد من `npm install` ثم `npm run dev` (محليًا) أو `npm run build` (إنتاجيًا).
- مشاكل صلاحيات ملفات:
  - تأكد من صلاحيات الكتابة على `storage` و`bootstrap/cache`.

## 9) مراجع المشروع

- خطة التحسين المرحلية: `../IMPROVEMENT_PLAN_CHECKLIST.md`
- سجل التنفيذ المرحلي: `../IMPLEMENTATION_NOTES.md`
- تقرير التدقيق: `../AUDIT_REPORT.md`
