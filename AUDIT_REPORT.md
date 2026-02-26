# AUDIT REPORT - Sobol Najah

## 1) نطاق المراجعة
- تاريخ المراجعة: 2026-02-25
- مسار الكود الأساسي: `backend-soubel-alnajah/`
- تم فحص: routes, controllers, models, requests, migrations, views, config, tests, roles/auth, upload flows.
- لم يتم تنفيذ refactor كبير في هذه المرحلة (Audit فقط).

## 2) Architecture Map (كيف مبني المشروع)
- النواة: Laravel 10 + PHP 8.1.
- الترجمة متعددة اللغات: `mcamara/laravel-localization` + `spatie/laravel-translatable`.
- الصلاحيات: `Laratrust` (roles/permissions).
- واجهة حالية: Blade + قالب Admin قديم (Bootstrap/Admin template) + Livewire v2 جزئي.
- طبقة المجال:
  - `School`: مدارس/مستويات/أقسام/صفوف.
  - `Inscription`: طلبات تسجيل + تلاميذ + أولياء + أساتذة.
  - `AgendaScolaire`: أجندة/درجات/منشورات/معرض/اختبارات/نقاط/غياب.
  - `Promotion`: ترقية وتخرّج.
  - `Application/Chat`: دردشة داخلية جديدة (chat_rooms/chat_messages).
  - `Application/Message`: نظام دردشة قديم موازي (messages table) غير موحد.
- أسلوب Multi-tenant الحالي: ربط المستخدم بـ `school_id` + Scopes (`forSchool`) في بعض الـ Models، لكن التطبيق غير كامل على كل الاستعلامات.
- التخزين والملفات:
  - جزء يستخدم `Storage::disk('public')`.
  - جزء كبير يرفع مباشرة إلى `public/` عبر `move()`.

## 3) Feature Inventory (الميزات الحالية)

### 3.1 الحسابات والأدوار
- تسجيل/دخول/استرجاع كلمة المرور عبر `Auth::routes()`.
- أدوار موجودة فعليًا: admin, student, guardian, teacher (Laratrust).
- تغيير كلمة المرور للمستخدم الحالي عبر endpoint مخصص.

### 3.2 إدارة المدرسة والهيكل الأكاديمي
- CRUD مدارس (`Schools`).
- CRUD مستويات/أطوار (`Schoolgrades`).
- CRUD أقسام دراسية (`Classes`).
- CRUD أفواج/Sections (`Sections`) + ربط الأساتذة بالفوج (pivot `teacher_section`).

### 3.3 التسجيل والطلاب والأولياء والأساتذة
- استقبال طلب تسجيل (`Inscriptions`) من الواجهة.
- قبول طلب التسجيل وتحويله تلقائيًا إلى طالب + ولي + حسابات مستخدمين.
- إدارة التلاميذ (`Students`) + استيراد Excel.
- إدارة الأساتذة (`Teachers`).

### 3.4 الترقية والتخرج
- ترقية جماعية من section إلى section (`Promotions`).
- إرجاع ترقية أو حذف سجل ترقية.
- التخرج يعتمد SoftDeletes للتلاميذ (`graduated`).

### 3.5 الأجندة المدرسية والمحتوى
- CRUD أجندة (`Agendas`) ودرجات (`Grades`).
- منشورات (`Publications`) مع صور (`Gallery`) مع ترجمة OpenAI.
- اختبارات/فروض (`Exames`) مع ملفات مرفوعة.
- نقاط الطلاب (`NoteStudent`) بملفات PDF.
- غيابات يومية حسب الساعات (`Absences`).

### 3.6 التواصل والإشعارات
- إشعارات قاعدة بيانات لشهادة مدرسية.
- Chat داخلي حديث (غرف، مجموعات، رسائل، قراءة).
- Chat AI (OpenAI chat completions).

### 3.7 الواجهة العامة
- صفحات عامة: home/about/contact/publications/exam/gallery/inscription.
- صفحات ملف الطالب/الولي بعد تسجيل الدخول.

### 3.8 الاختبارات والـ CI
- اختبارات افتراضية فقط (ExampleTest).
- لا يوجد CI pipeline فعّال.

## 4) أهم المشاكل (مرتبة بالأولوية)

### High Risk
1. رفع الملفات غير آمن في عدة نقاط.
- أمثلة: `ExamesController`, `NoteStudentController`.
- المشكلة: غياب تحقق صارم `mimes/max`, التخزين مباشرة في `public/`, تنزيل ملفات عبر اسم ملف من URL.
- الأثر: رفع ملفات ضارة/تسريب/تجاوزات.

2. كلمات مرور افتراضية ثابتة للحسابات الجديدة.
- `StudentEnrollmentService`, `TeacherController`.
- الأثر: اختراق سهل إذا لم يتم تغييرها.

3. فجوات صلاحيات/تفويض (Authorization) وعدم وجود Policies.
- الاعتماد غالبًا على middleware role عام.
- كثير من العمليات الحساسة دون `Policy` على مستوى السجل.
- الأثر: وصول غير مقصود بين مدارس/سجلات.

4. عزل المدارس (school scoping) غير مكتمل.
- Controllers عديدة تستخدم `all()` بدون `forSchool`.
- الأثر: تسرب بيانات بين المدارس.

5. عمليات تغيير بيانات عبر GET.
- مثل `notify/{id}` (GET) و`store/{id}`.
- الأثر: مخالف لأمن الويب (CSRF/Idempotency) وسلوك غير متوقع.

6. تخزين ملفات حساسة/تشغيلية داخل `public/` ومرفوعة على Git.
- أمثلة موجودة في `public/exames` و`public/agenda` بامتدادات متعددة (حتى html/txt).
- الأثر: تضخم الريبو + مخاطر أمان + صعوبة إدارة وسائط.

7. ملف README يحتوي merge conflict markers.
- الملف الحالي فيه `<<<<<<<`, `=======`, `>>>>>>>`.
- الأثر: مؤشر على جودة دمج منخفضة وتوثيق غير موثوق.

### Medium Risk
1. Controllers ضخمة جدًا (God Controllers).
- أمثلة: InscriptionController (352 سطر)، ChatController (345)، StudentController (283).
- الأثر: صعوبة صيانة واختبار.

2. Validation غير متناسق.
- بعض الوحدات تستخدم FormRequests، وبعضها يستخدم Request مباشر بحد أدنى.
- الأثر: أخطاء إدخال وسلوك غير متوقع.

3. تكرار منطق الأعمال.
- قبول التسجيل موجود في أكثر من Controller.
- الأثر: تضارب سلوك وتكاليف تعديل أعلى.

4. مخطط قاعدة البيانات قديم/غير مثالي.
- أعمدة JSON مخزنة كسلاسل `string` في migrations الأصلية.
- نقص فهارس مركبة مهمة لبعض الاستعلامات.

5. واجهة قديمة ومتشعبة.
- مزج كبير بين UI business logic وBlade تقليدي.
- الأثر: UX متذبذب وصعوبة تحديث التصميم.

6. وجود نظامي دردشة متوازيين (`messages` و`chat_*`).
- الأثر: تعقيد تقني وعدم وضوح المصدر الرسمي للرسائل.

7. Notifications table: عمود `data` نصي محدود + unique.
- الأثر: قابلية فشل إدراج إشعارات صحيحة.

### Low Risk
1. غياب اختبارات تغطي المسارات الحساسة.
2. غياب CI/CD بسيط للتحقق الآلي.
3. Naming غير موحّد وأخطاء تهجئة في أسماء methods/routes (`Displqy...`, `UpdateScoolgrade`).

## 5) ملخص أمني مركز
- CSRF: متوفر في web middleware، لكن استخدام GET لعمليات state-changing يحتاج إصلاح.
- XSS: Blade escaping غالبًا موجود، لكن يلزم تدقيق حقول rich text.
- File Uploads: أهم نقطة حرجة الآن.
- Authorization: مطلوب Policies + Gates تفصيلية لكل domain model.
- Secrets: `.env` غير متتبع حاليًا (جيد)، لكن يلزم مراجعة تاريخ git لاحتمال تسرب سابق.

## 6) ملخص أداء وقابلية التوسع
- كثير من الشاشات تعمل `get()` بدون pagination.
- نقص caching لبيانات مرجعية (schools/grades/sections).
- `notifications()` يجلب كل الإشعارات بلا scope حسب المدرسة.
- يلزم فهارس إضافية للجداول ذات الاستعلامات المتكررة: `studentinfos(section_id)`, `absences(student_id,date)`, `inscriptions(school_id,statu)` وغيرها.

## 7) توافق المتطلبات الجديدة (Gap Analysis)

### (1) التوظيف + CV
- غير موجود كـ Module واضح حاليًا.
- يلزم domain جديد (job_posts, job_applications) + storage آمن + anti-spam + workflow حالات.

### (2) الجداول الزمنية الاحترافية
- توجد “agenda” ولكن ليست Timetable grid معيارية (أيام/حصص/طباعة احترافية).
- يلزم تصميم بيانات مستقل `timetables + timetable_entries`.

### (3) العقود والمدفوعات
- غير موجود حاليًا.
- ملف Excel الخاص بالمقتصد غير ظاهر ضمن المسار المفحوص بشكل صريح كملف مرجعي جاهز لهذا الموديول.
- يلزم جلسة mapping لحقول Excel الحقيقي قبل التنفيذ النهائي.

## 8) توصيات حلول واضحة (بدون تنفيذ دفعة واحدة)
1. توحيد نمط التطبيق إلى طبقات: Controller thin + FormRequest + Action/Service + Policy + Resource.
2. تطبيق Authorization مركزي عبر Policies لكل: School, Section, Student, Teacher, Inscription, Publication, Exame, Payment.
3. اعتماد multi-tenancy على مستوى الاستعلامات بشكل إجباري (Global scope/tenant resolver حيث يلزم).
4. إعادة هندسة رفع الملفات:
- استخدام `Storage` فقط خارج `public`.
- أسماء عشوائية آمنة + path by year/month.
- تنزيل عبر signed routes + فحص ownership.
5. إزالة كلمات المرور الافتراضية الثابتة:
- invite/reset flow أو توليد آمن + فرض تغيير أول تسجيل دخول.
6. توحيد نظام الدردشة واختيار واحد (chat_* الحديث).
7. تحسين قاعدة البيانات:
- Migration cleanup تدريجي.
- تحويل أعمدة مناسبة إلى JSON type.
- فهارس مركبة + قيود unique صحيحة.
8. تحديث UI تدريجيًا:
- Design System موحد + RTL كامل + صفحات CRUD حديثة (بحث/فلترة/pagination).
9. إدخال اختبارات حرجة أولًا:
- auth/roles, school scoping, uploads, inscriptions, payments.
10. تفعيل CI بسيط (GitHub Actions) للتشغيل الآلي للـ tests + pint.

## 9) خلاصة تنفيذية
- المشروع يحتوي أساس وظيفي غني ومفيد، لكنه يحتاج إعادة تنظيم قوية في الأمان، الصلاحيات، عزل البيانات، وهيكلة الكود.
- الأولوية القصوى قبل أي توسعة كبيرة: hardening الأمان + governance للصلاحيات + تنظيف البنية.
- بعد ذلك نبدأ تنفيذ الميزات الجديدة الثلاث على بنية أكثر ثباتًا.
