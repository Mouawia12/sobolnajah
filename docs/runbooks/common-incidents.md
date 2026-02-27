# Runbook: Common Incidents

## Purpose
التعامل السريع مع الأعطال الشائعة في التشغيل.

## Incident 1: `migrations` table errors in tests
### Symptoms
- `Table '...migrations' doesn't exist`
- `Table 'migrations' already exists`

### Actions
1. أوقف أي تشغيل اختبارات متوازي.
2. نفّذ:
```bash
php artisan migrate:fresh --force
```
3. أعد تشغيل الاختبارات بشكل تسلسلي.

## Incident 2: Queue jobs not processing
### Symptoms
- Jobs متراكمة في `jobs` table.

### Actions
1. تحقق من `QUEUE_CONNECTION` في `.env`.
2. شغّل worker:
```bash
php artisan queue:work --tries=3 --timeout=90
```
3. راجع logs للأخطاء المتكررة.

## Incident 3: Scheduler not running
### Symptoms
- مهام مجدولة لا تنفذ.

### Actions
1. تحقق من cron entry:
```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```
2. اختبار يدوي:
```bash
php artisan schedule:run
```

## Incident 4: 500 errors after deploy
### Symptoms
- صفحات الإدارة/العامة ترجع 500.

### Actions
1. تحقق من `.env` و `APP_KEY`.
2. نظف وأعد بناء الكاش:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
3. راجع `storage/logs/laravel.log`.

## Incident 5: Uploaded files inaccessible
### Symptoms
- تنزيل مرفقات يرجع 404/403.

### Actions
1. تحقق من وجود الملفات في `storage/app/private/*`.
2. تحقق من صلاحيات الملفات/المجلدات.
3. تحقق من policy/signed route حسب نوع الملف.

## Escalation
صعّد للحالة الحرجة إذا:
- توقف تسجيل الدخول.
- تعذر الوصول لقواعد البيانات.
- فقدان بيانات بعد restore.
