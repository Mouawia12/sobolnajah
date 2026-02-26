# Sobol Najah (Laravel)

منصة مدرسية مبنية بـ Laravel لإدارة التسجيلات، المستخدمين، الأقسام، المنشورات، الامتحانات، الغياب، والترقيات.

## المتطلبات

- PHP 8.1+
- Composer 2+
- MySQL 8+
- Node.js 18+ و npm

## التشغيل المحلي (Local Setup)

1. تثبيت الحزم الخلفية:

```bash
composer install
```

2. تثبيت حزم الواجهة:

```bash
npm install
```

3. إعداد ملف البيئة:

```bash
cp .env.example .env
```

4. توليد مفتاح التطبيق:

```bash
php artisan key:generate
```

5. ضبط اتصال قاعدة البيانات داخل `.env`:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

6. تشغيل migrations:

```bash
php artisan migrate
```

7. إنشاء رابط التخزين العام:

```bash
php artisan storage:link
```

8. تشغيل Vite:

```bash
npm run dev
```

9. تشغيل التطبيق:

```bash
php artisan serve
```

## الحسابات والتهيئة الأمنية

- الحسابات الجديدة تُنشأ بدون كلمات مرور ثابتة.
- النظام يفعّل `must_change_password` ويعتمد تدفق إعادة تعيين كلمة المرور (Password Setup Link).

## الاختبارات

تشغيل اختبارات الأمان الأساسية:

```bash
php artisan test --filter=SprintZeroSecurityTest
php artisan test --filter=OnboardingFlowTest
```

ملاحظة: في البيئة الحالية، يفضّل تشغيل اختبارات Feature بشكل تسلسلي (ليس بالتوازي) لتجنب تعارض `RefreshDatabase` على نفس قاعدة البيانات.

## مهام تشغيلية (Production Basics)

### Queue Worker

```bash
php artisan queue:work --tries=3
```

### Scheduler (Cron)

أضف المهمة التالية في cron:

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

## مراجع المشروع

- خطة التحسين المرحلية: `../IMPROVEMENT_PLAN_CHECKLIST.md`
- سجل التنفيذ المرحلي: `../IMPLEMENTATION_NOTES.md`
- تقرير التدقيق: `../AUDIT_REPORT.md`
