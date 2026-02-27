# Runbook: Backup & Restore

## Purpose
تشغيل نسخ احتياطي واستعادة لقاعدة البيانات وملفات التخزين بشكل آمن وسريع.

## Scope
- MySQL database
- Application files in `storage/app`

## Prerequisites
- صلاحية وصول لقاعدة البيانات.
- مساحة كافية لحفظ النسخ.
- تنفيذ الأوامر من جذر المشروع.

## Backup Procedure

### 1) Database dump
```bash
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > backup_$(date +%F_%H%M).sql
```

### 2) Storage archive
```bash
tar -czf storage_backup_$(date +%F_%H%M).tar.gz storage/app
```

### 3) Integrity quick-check
```bash
ls -lh backup_*.sql storage_backup_*.tar.gz
```

## Restore Procedure

### 1) Enable maintenance mode
```bash
php artisan down --render="errors::503"
```

### 2) Restore database
```bash
mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < backup_YYYY-MM-DD_HHMM.sql
```

### 3) Restore storage files
```bash
tar -xzf storage_backup_YYYY-MM-DD_HHMM.tar.gz
```

### 4) Rebuild caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5) Bring app up
```bash
php artisan up
```

## Validation Checklist
- الصفحة الرئيسية تفتح بدون أخطاء.
- تسجيل الدخول يعمل.
- عرض مرفقات خاصة (exam/note/cv/publication media) يعمل.
- لا توجد أخطاء حرجة في `storage/logs/laravel.log`.

## Rollback Note
إذا فشلت الاستعادة، أعد نفس الخطوات بآخر backup صالح ثم تحقق من السجلات قبل إعادة الخدمة.
