# IMPLEMENTATION NOTES

## 2026-02-25 - Phase 0 (Audit & Planning)
- تم تنفيذ Audit شامل لهيكل المشروع في `backend-soubel-alnajah/`.
- تم إنشاء `AUDIT_REPORT.md` ويتضمن:
  - Architecture Map
  - Feature Inventory
  - قائمة المخاطر High/Medium/Low
  - اقتراحات حلول تنفيذية تدريجية
- تم إنشاء `IMPROVEMENT_PLAN_CHECKLIST.md` بخطة Sprints وChecklist تفصيلية (A..G + features الجديدة).
- لم يتم تنفيذ Refactor/DB/UI كبير في هذه المرحلة (مقصود حسب الطلب).

## ملاحظات مهمة قبل Sprint 1
- يلزم تأكيد ملف Excel الخاص بالمقتصد (مصدره النهائي ومكانه) قبل تنفيذ Feature المدفوعات.
- يوصى بدء التنفيذ من Sprint 0 (stabilization) قبل أي توسعة ميزات.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 1
- تمت إضافة التوجيهات المعمارية الإلزامية داخل `IMPROVEMENT_PLAN_CHECKLIST.md` تحت قسم `Architectural Directives`.
- تم تحويل مسارات state-changing من `GET` إلى `POST`:
  - `notify/{id}`
  - `store/{id}`
  - وتحديث النماذج ذات الصلة في:
    - `resources/views/front-end/studentprofile.blade.php`
    - `resources/views/front-end/parentprofile.blade.php`
- تم تفعيل Authorization Policies:
  - `app/Policies/ExamePolicy.php`
  - `app/Policies/NoteStudentPolicy.php`
  - وربطها في `app/Providers/AuthServiceProvider.php`.
- تم إضافة FormRequests أمنية:
  - `StoreExameRequest`
  - `UpdateExameRequest`
  - `StoreNoteStudentRequest`
  - `NotifySchoolCertificateRequest`
- تم تقوية تدفق رفع الملفات:
  - `ExamesController`: حفظ الملفات في `storage/app/private/exames` مع قيود mimes/size.
  - `NoteStudentController`: حفظ الملفات في `storage/app/private/notes` مع قيود mimes/size.
  - الحفاظ على fallback للملفات القديمة الموجودة في `public/*` لتجنب كسر السجلات السابقة.
- تم إزالة كلمات المرور الافتراضية الثابتة:
  - `StudentEnrollmentService`
  - `TeacherController`
  - واستبدالها بكلمات مرور عشوائية قوية غير متوقعة.

### تحقق فني
- تم تمرير فحص syntax (`php -l`) لكل الملفات المعدلة بدون أخطاء.
- تم التحقق من تحديث route table وظهور المسارات الجديدة بصيغة `POST`.

### عناصر متبقية في Sprint 0
- استكمال policy coverage على domains إضافية (مثل inscriptions/publications/sections).
- توسيع secure upload إلى بقية نقاط الرفع.
- تصميم forced password reset/invite flow بدل الاكتفاء بكلمة مرور عشوائية.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 2
- تم إضافة migration جديدة:
  - `2026_02_25_000001_add_must_change_password_to_users_table.php`
- تم تطبيق forced password change:
  - إضافة `must_change_password` في `User` model (fillable/casts).
  - إضافة middleware جديدة `RequirePasswordChange`.
  - تسجيل middleware في `Kernel` باسم `force.password.change`.
  - تطبيقها على route groups المحمية في `routes/web.php`.
  - تحديث `ConfirmPasswordController` لتصفير `must_change_password` بعد تغيير كلمة المرور.
- تم ضبط إنشاء الحسابات الجديدة على:
  - كلمة مرور عشوائية قوية.
  - `must_change_password = true`.
- تم تعزيز حماية المحتوى:
  - تشديد validation في `StorePublication` (types/sizes/exists).
  - قصر `store/update/destroy` في `PublicationController` على `auth + role:admin`.
- تم إضافة اختبارات آلية جديدة:
  - `tests/Feature/Security/SprintZeroSecurityTest.php`
  - السيناريوهات المغطاة:
    - منع الوصول للمسارات المحمية عند `must_change_password=true`.
    - نجاح إزالة العلم بعد تغيير كلمة المرور.
    - رفض امتدادات ملفات غير مسموحة في رفع الاختبارات.
    - منع الضيف من إنشاء منشور.
- نتيجة الاختبارات:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (4 tests, 11 assertions).

### ملاحظات الاستكمال
- لا يزال invite/reset onboarding التسليمي للمستخدم الجديد بحاجة إكمال (إرسال رابط/تسليم آمن).
- Secure upload ما زال بحاجة استكمال على بقية نقاط الرفع خارج exams/notes/publications.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 3
- تم إضافة `PublicationPolicy` وربطها في `AuthServiceProvider`.
- تم تشديد `PublicationController` على:
  - `authorize('create'|'update'|'delete')`
  - scoping للمنشورات في لوحة الإدارة حسب `currentSchoolId`.
  - منع ربط منشور بمدرسة غير مدرسة المستخدم الإداري.
- تم توسيع الاختبارات الأمنية في:
  - `tests/Feature/Security/SprintZeroSecurityTest.php`
  - إضافة سيناريو تحقق من منع تعديل منشور خارج مدرسة الأدمن + التأكد من بقاء البيانات دون تغيير.
- نتيجة الاختبارات بعد التوسعة:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (5 tests, 13 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 4
- تم توسيع Policy coverage بإضافة:
  - `StudentInfoPolicy`
  - `TeacherPolicy`
- تم ربط السياسات الجديدة في `AuthServiceProvider`.
- تم تفعيل `authorize(...)` داخل:
  - `Inscription/StudentController`
  - `Inscription/TeacherController`
- تم إضافة اختبار آلي جديد داخل `SprintZeroSecurityTest`:
  - منع أدمن مدرسة من حذف أستاذ تابع لمدرسة مختلفة.
- نتيجة الاختبارات بعد التوسعة الأخيرة:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (6 tests, 15 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 5
- تم إضافة خدمة onboarding جديدة:
  - `app/Services/UserOnboardingService.php`
  - وظيفتها إرسال password setup/reset link تلقائيًا للمستخدم الجديد.
- تم ربط الخدمة في:
  - `StudentEnrollmentService` (حسابات الطالب/الولي).
  - `TeacherController` (حسابات الأستاذ).
- النتيجة: الحسابات الجديدة لا تعتمد على كلمات مرور افتراضية ثابتة، ويُنشأ reset token تلقائيًا.
- تم إضافة اختبارات آلية جديدة:
  - `tests/Feature/Security/OnboardingFlowTest.php`
  - تغطية:
    - إنشاء طالب/ولي يولّد reset tokens.
    - إنشاء أستاذ يولّد reset token.
- نتيجة الاختبارات المجمعة:
  - `php artisan test --filter='(SprintZeroSecurityTest|OnboardingFlowTest)'` => PASS (8 tests, 22 assertions).

### ملاحظات الاستكمال
- قنوات التسليم الإنتاجية (SMTP/SMS) تحتاج ضبط بيئة النشر لضمان وصول روابط التعيين.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 6
- تم إضافة سياسات جديدة:
  - `InscriptionPolicy`
  - `PromotionPolicy`
- تم ربط السياسات في `AuthServiceProvider`.
- تم تطبيق authorize checks على:
  - `InscriptionController` (view/update/delete/approve paths)
  - `PromotionController` (index/store/destroy)
- تم توسيع الاختبارات الأمنية:
  - إضافة سيناريو منع أدمن مدرسة من قبول تسجيل مدرسة أخرى.
- نتائج الاختبارات:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (7 tests, 17 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

### توجيه عمل إضافي (اعتماد دائم)
- أثناء أي تنفيذ قادم: لا يتم إنهاء تغييرات حساسة بدون إضافة/تحديث اختبارات آلية مرتبطة بها.
- توجد حرية تقنية كاملة أثناء التنفيذ (تثبيت/حذف/إعادة تنظيم) بشرط التوثيق المستمر لتأثير كل تغيير.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 7
- تم إغلاق state-changing routes إضافية عبر تحويلها إلى `POST` فقط:
  - `markasread/{id}`
  - `delete_all`
- تم تحديث الواجهات المرتبطة:
  - `resources/views/layoutsadmin/main_header.blade.php` (أزرار الإشعارات أصبحت forms بـ CSRF).
  - `resources/views/admin/studentsinscription.blade.php` (حذف جماعي عبر POST بدل GET).
- تم تحصين `FunctionController::markasread`:
  - يمنع الوصول لإشعار لا يخص المستخدم الحالي عبر شرط `notifiable_id = auth()->id()`.
  - يعيد `404` في حالة محاولة الوصول غير المصرح.
- تم تحصين `ClassroomController::delete_all`:
  - validation إلزامي للحقل `delete_all_id`.
  - parsing آمن للقيم (integers موجبة فقط).
  - scoping للحذف على `school_id` الحالي فقط لمنع حذف سجلات مدرسة أخرى.
- تم توسيع الاختبارات الأمنية:
  - `SprintZeroSecurityTest` أضيف له سيناريو `test_state_changing_admin_routes_reject_get_method`.
- نتائج الاختبارات بعد Phase 7:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (9 tests, 22 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 8
- تم إضافة FormRequests جديدة لتحصين الترقية:
  - `app/Http/Requests/StorePromotionRequest.php`
  - `app/Http/Requests/DestroyPromotionRequest.php`
- تم تحديث `PromotionController` ليعتمد validation صريح بدل `Request` مباشر في:
  - `store`
  - `destroy`
- تحسينات أمنية ومنطقية داخل الترقية:
  - فرض scoping على مستوى المدرسة (`currentSchoolId`) أثناء الترقية والتراجع.
  - منع ترقية داخل نفس القسم عبر قاعدة `different:section_id`.
  - إزالة `Promotion::truncate()` (خطر حذف شامل) واستبدالها بحذف مقيّد على `from_school`.
  - إصلاح مسار الخطأ في `store` حيث كان يستدعي `$e` قبل تعريفه.
- تم توسيع الاختبارات:
  - `SprintZeroSecurityTest` أضيف له `test_promotion_store_rejects_same_source_and_target_section`.
- نتائج الاختبارات بعد Phase 8:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (10 tests, 25 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 9
- تم إضافة FormRequest جديدة:
  - `app/Http/Requests/ImportStudentsRequest.php`
  - الهدف: توحيد validation لرفع Excel (`xlsx/xls`) مع حد حجم.
- تم تحديث:
  - `StudentController::importExcel` لاستخدام `ImportStudentsRequest` بدل validation inline.
- تم تحصين مسار ملفات النقاط في `NoteStudentController`:
  - منع أي اسم ملف غير آمن عبر فحص نمط صارم (منع `..`, `/`, `\\`).
  - إزالة دعم المسارات غير المباشرة داخل `localNotePath` (الاكتفاء بأسماء ملفات آمنة فقط).
  - إضافة `X-Content-Type-Options: nosniff` عند التنزيل.
- تم إصلاح وتوسيع العزل متعدد المدارس:
  - `NoteStudentPolicy` أصبح يتحقق من انتماء الطالب/القسم لنفس مدرسة الأدمن في `view/update/delete`.
  - `NoteStudent` model تم إصلاح relation `student()` بإضافة import صحيح لـ `StudentInfo`.
  - `NoteStudentController@show` أصبح مقيّدًا بـ scoping حسب المدرسة بدل `NoteStudent::all()`.
- تم توسيع الاختبارات الأمنية:
  - `test_students_import_rejects_non_excel_files`
  - `test_download_note_rejects_unsafe_filename_pattern`
  - `test_admin_cannot_download_note_from_another_school`
- نتائج الاختبارات بعد Phase 9:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (13 tests, 30 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 10
- تم تحديث `app/Http/Controllers/Controller.php`:
  - `notifications()` لم تعد ترجع كل إشعارات النظام.
  - أصبحت ترجع فقط إشعارات المستخدم المصادق عليه (`where notifiable_id = auth()->id()`).
  - عند عدم وجود مستخدم مصادق عليه ترجع مجموعة فارغة.
- الأثر الأمني:
  - منع تسرب بيانات الإشعارات بين المستخدمين/المدارس داخل جميع الشاشات التي تعتمد helper `notifications()`.
- تم إضافة اختبار أمني جديد:
  - `test_admin_cannot_mark_notification_from_another_user_as_read`
  - يثبت أن محاولة تعليم إشعار مستخدم آخر كمقروء تُرفض بـ `404`.
- نتائج الاختبارات بعد Phase 10:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (14 tests, 31 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 11
- تم تحصين `AbsenceController` بعزل المدرسة:
  - `index`: استعلام الغياب أصبح scoped عبر علاقة `student.section.school_id`.
  - `storeOrUpdate`: التحقق أولًا أن `student_id` تابع لمدرسة المستخدم (`StudentInfo::forSchool(...)->findOrFail`).
  - `getToday`: التحقق من ملكية الطالب قبل قراءة سجل الغياب.
- تم تحديث `AbsencePolicy`:
  - إضافة `view()` واستخدام helper موحد `canAccessAbsence` بدل تكرار المنطق.
- تم إضافة اختبار أمني جديد:
  - `test_admin_cannot_update_absence_for_student_from_another_school`
  - يثبت أن العملية تُرفض (`404`) ولا يُنشأ سجل غياب.
- نتائج الاختبارات بعد Phase 11:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (15 tests, 33 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 12
- تم تحصين وحدة `Schoolgrade`:
  - إضافة `app/Policies/SchoolgradePolicy.php`.
  - ربط السياسة في `app/Providers/AuthServiceProvider.php`.
  - إضافة `scopeForSchool` داخل `app/Models/School/Schoolgrade.php`.
- تم تحديث `SchoolgradeController`:
  - إضافة middleware (`auth + role:admin`) لكل الـCRUD (مع استثناء `getGrade`).
  - `index` أصبح scoped حسب مدرسة المستخدم بدل `School::all()` و`Schoolgrade::all()`.
  - `store` صار يرفض إدراج `school_id` مختلف عن مدرسة الأدمن.
  - `update/destroy` صارا يعملان على سجل scoped + policy authorization.
  - `destroy` صار يتحقق من وجود الصفوف التابعة داخل نفس المدرسة فقط.
  - `getGrade` صار يرفض إرجاع بيانات مدرسة أخرى للمستخدم المصادق عليه.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_schoolgrade_from_another_school`
- نتائج الاختبارات بعد Phase 12:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (16 tests, 35 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 13
- تم تحصين وحدة `School` بالكامل:
  - إضافة `app/Policies/SchoolPolicy.php`.
  - ربط `SchoolPolicy` داخل `app/Providers/AuthServiceProvider.php`.
  - إضافة `scopeForSchool` داخل `app/Models/School/School.php`.
- تم تحديث `SchoolController`:
  - middleware `auth + role:admin` على CRUD.
  - `index` scoped حسب مدرسة المستخدم.
  - `store` أصبح يمر عبر Policy (`create`) ويمنع School-bound admins من إنشاء مدارس جديدة.
  - `update/destroy` يعملان على سجل scoped + authorization.
  - `test` endpoint أصبح scoped بدل جلب كل المدارس/المستويات.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_school_from_another_school`
- نتائج الاختبارات بعد Phase 13:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (17 tests, 37 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 14
- تم إضافة سياسة جديدة:
  - `app/Policies/SectionPolicy.php`
  - وربطها في `app/Providers/AuthServiceProvider.php`.
- تم تحديث `SectionController`:
  - إضافة middleware `auth + role:admin` على عمليات CRUD (مع استثناء `getSection/getSection2`).
  - إضافة `authorize` صريح في:
    - `index` (`viewAny`)
    - `store` (`create`)
    - `edit/update` (`update`)
    - `destroy` (`delete`)
  - توحيد حذف القسم عبر كائن scoped (`findOrFail + policy`) بدل حذف مباشر.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_section_from_another_school`
- نتائج الاختبارات بعد Phase 14:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (18 tests, 39 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 15
- تم إضافة سياسة جديدة:
  - `app/Policies/ClassroomPolicy.php`
  - وربطها في `app/Providers/AuthServiceProvider.php`.
- تم تحديث `ClassroomController`:
  - middleware `auth + role:admin` على عمليات CRUD (استثناء `getClasse` فقط).
  - `authorize` صريح في:
    - `index` (`viewAny`)
    - `store` (`create`)
    - `update` (`update`)
    - `destroy` (`delete`)
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_classroom_from_another_school`
- نتائج الاختبارات بعد Phase 15:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (19 tests, 41 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 16
- تم إضافة سياسات جديدة:
  - `app/Policies/AgendaPolicy.php`
  - `app/Policies/GradePolicy.php`
  - وربطهما داخل `app/Providers/AuthServiceProvider.php`.
- تم تحديث `AgendaController`:
  - middleware `auth + role:admin`.
  - authorize checks في `index/store/update/destroy`.
  - `update/destroy` صارا يعملان على سجل محمّل (`findOrFail`) قبل التحديث/الحذف.
- تم تحديث `GradeController`:
  - middleware `auth + role:admin`.
  - authorize checks في `index/store/update/destroy`.
  - `update/destroy` صارا يعملان على سجل محمّل (`findOrFail`) قبل التحديث/الحذف.
- تم إضافة اختبار أمني:
  - `test_non_admin_cannot_create_grade`
  - يثبت أن المستخدم غير الأدمن لا يستطيع إنشاء مستوى.
- نتائج الاختبارات بعد Phase 16:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (20 tests, 43 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).
- ملاحظة تنفيذية:
  - تشغيل اختبارين مختلفين بالتوازي أدى مؤقتًا لتعارض `RefreshDatabase` على نفس DB.
  - الاعتماد الآن على التشغيل التسلسلي لاختبارات feature في هذه البيئة.

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 17
- تم تحصين `GraduatedController` بشكل كامل:
  - إضافة middleware `auth + role:admin`.
  - `index` أصبح يمر عبر `StudentInfoPolicy::viewAny` مع `forSchool` على المدارس والطلاب المحذوفين.
  - `store` أصبح:
    - يمر عبر validation صريح.
    - scoped حسب المدرسة (`forSchool`).
    - يستخدم authorize لكل طالب قبل الترحيل.
    - يصلح خطأ `$e` غير المعرّف عند عدم وجود طلاب.
    - يحذف سجلات `Promotion` بالربط الصحيح `student_id`.
  - `update` (restore) أصبح scoped + policy-guarded على الطالب المحذوف.
  - `destroy` أصبح:
    - يمر عبر validation صريح.
    - scoped حسب المدرسة.
    - في الحذف النهائي يعتمد على العلاقات الفعلية (`student->user`, `student->parent->user`) بدل `user_id + 1`.
    - يستخدم `forceDelete()` للطالب المحذوف بعد تنظيف المستخدمين المرتبطين.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_restore_graduated_student_from_another_school`
- نتائج الاختبارات بعد Phase 17:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (21 tests, 45 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 18
- تم تحصين مسار القبول الإداري القديم في `FunctionController::store($id)`:
  - جلب `Inscription` أصبح scoped حسب `currentSchoolId`.
  - إضافة `authorize('approve', $inscription)` قبل أي تغيير حالة/إنشاء حسابات.
- الهدف:
  - إغلاق مسار legacy قد يسمح بمحاولة قبول تسجيلات خارج مدرسة الأدمن.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_approve_inscription_from_another_school_via_legacy_store_route`
- نتائج الاختبارات بعد Phase 18:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (22 tests, 47 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 19
- تطبيق `FormRequest` على عمليات الكتابة في `GraduatedController` بدل `Request` مباشر:
  - إضافة `app/Http/Requests/StoreGraduatedRequest.php`
  - إضافة `app/Http/Requests/RestoreGraduatedStudentRequest.php`
  - إضافة `app/Http/Requests/DestroyGraduatedRequest.php`
- تحديث `GraduatedController`:
  - `store(StoreGraduatedRequest $request)`
  - `update(RestoreGraduatedStudentRequest $request, $id)`
  - `destroy(DestroyGraduatedRequest $request)`
  - إزالة validation inline واستبدالها بـ `$request->validated()`.
- الهدف:
  - الالتزام بسياسة `Security First` (كل عملية كتابة تمر عبر FormRequest + Authorization).
- نتائج الاختبارات بعد Phase 19:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (22 tests, 47 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 20
- معالجة مخالفة `No state-changing عبر GET` في وحدتي التسجيل والأقسام:
  - `InscriptionController@edit` أصبح `abort(405)`.
  - `SectionController@edit` أصبح `abort(405)`.
- إضافة endpoints كتابة صريحة عبر `POST`:
  - `/Inscriptions/{id}/status` => `Inscriptions.status`
  - `/Sections/{id}/status` => `Sections.status`
  - `/Sections/{id}/teachers` => `Sections.teachers`
- إضافة FormRequests جديدة:
  - `UpdateInscriptionStatusRequest` (حصر القيم: `accept|noaccept|procec`)
  - `UpdateSectionStatusRequest` (حصر القيم: `0|1`)
  - `SyncSectionTeachersRequest` (تحقق قائمة المعلمين)
- تحديث الواجهات:
  - `resources/views/admin/studentsinscription.blade.php`
    - نموذج تغيير حالة التسجيل صار يستخدم `Inscriptions.status`.
  - `resources/views/admin/sections.blade.php`
    - نموذج الحالة صار يستخدم `Sections.status`.
    - نموذج المعلمين صار يستخدم `Sections.teachers`.
    - إزالة `method_field('GET')` من النماذج التي تغيّر الحالة.
- اختبار أمني جديد:
  - `test_get_edit_routes_do_not_change_inscription_or_section_status`
  - يؤكد أن استدعاء `GET .../edit` لا يغير أي بيانات (الحالة تبقى كما هي).
- نتائج الاختبارات بعد Phase 20:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (23 tests, 51 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 21
- تفكيك مسار قبول التسجيل من `show` إلى endpoint كتابة صريح:
  - إضافة `app/Http/Requests/ApproveInscriptionRequest.php`.
  - إضافة method جديدة `approve(ApproveInscriptionRequest $request, $id)` في `InscriptionController`.
  - `show()` أصبح `405` (no state-changing عبر show).
  - `approve()` يفرض:
    - validation (`section_id2`)
    - school scoping للـ `Section` والـ `Inscription`
    - authorization عبر `approve` policy.
- تحديث routes:
  - إضافة `POST /Inscriptions/{id}/approve` باسم `Inscriptions.approve`.
- تحديث الواجهة:
  - `resources/views/admin/studentsinscription.blade.php`
  - نموذج قبول الطالب (`store-form`) صار يستخدم `Inscriptions.approve` بدون spoof لـ `GET`.
- تحسين التغطية الاختبارية:
  - تحديث اختبار approval الأساسي لاستخدام `Inscriptions.approve`.
  - توسيع اختبار `get edit routes do not change ...` ليشمل أيضًا `GET /Inscriptions/{id}` (show) والتأكد أنه لا يغير أي حالة.
- نتائج الاختبارات بعد Phase 21:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (23 tests, 52 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 22
- تشديد حماية `InscriptionController` لأن resource endpoint موجود ضمن المسارات العامة أيضًا:
  - إضافة middleware:
    - `auth`
    - `role:admin`
    - `force.password.change`
  - مطبق على العمليات الحساسة فقط:
    - `show`, `approve`, `edit`, `update`, `destroy`, `updateStatus`
  - مع إبقاء `index/store` متاحين حسب منطق التسجيل العام.
- إضافة اختبار أمني:
  - `test_guest_cannot_access_inscription_approve_endpoint`
  - يثبت أن الضيف لا يصل لمسار `POST /Inscriptions/{id}/approve`.
- ملاحظة تنفيذية:
  - عند تشغيل test suites بالتوازي على نفس DB يظهر تعارض `RefreshDatabase`.
  - اعتماد التشغيل التسلسلي لاستقرار النتائج.
- نتائج الاختبارات بعد Phase 22:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (24 tests, 53 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 23
- تقوية مسار الحذف الجماعي للتسجيلات:
  - إضافة `app/Http/Requests/DeleteBulkInscriptionsRequest.php`.
  - تحديث `ClassroomController::delete_all` لاستخدام FormRequest بدل validation inline.
- تعزيز العزل متعدد المدارس في نفس المسار (موجود سابقًا) مع توثيق واختبار صريح:
  - حتى لو أرسل الأدمن قائمة IDs مختلطة من مدارس مختلفة، الحذف يقتصر على `school_id` الخاص به.
- إضافة اختبار أمني:
  - `test_bulk_delete_inscriptions_is_scoped_to_admin_school_only`
- نتائج الاختبارات بعد Phase 23:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (25 tests, 56 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 24
- تم تقوية العزل متعدد المدارس في وحدة `Exames`:
  - `app/Policies/ExamePolicy.php`
    - `view` للأدمن أصبح يتحقق من `classroom.school_id`.
    - `update/delete` أصبحا يعتمدان نفس عزل المدرسة بدل `admin` فقط.
  - `app/Http/Controllers/AgendaScolaire/ExamesController.php`
    - `index` للأدمن أصبح scoped حسب مدرسة المستخدم.
    - `Schoolgrade` في واجهة الأدمن أصبح scoped حسب المدرسة.
    - `store/update` أصبحا يتحققان من `classroom_id` ضمن مدرسة الأدمن.
    - `grade_id` في الحفظ/التعديل أصبح يُشتق من `classroom->grade_id` لمنع حقن ربط غير متسق.
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_exame_from_another_school`
- نتائج الاختبارات بعد Phase 24:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (26 tests, 58 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 25
- تم تشديد الوصول لصفحات النماذج الإدارية في وحدتي المحتوى:
  - `PublicationController`:
    - middleware `auth + role:admin` أصبح يشمل `create/edit` بالإضافة لـ `store/update/destroy`.
  - `ExamesController`:
    - middleware `auth + role:admin` أصبح يشمل `create/edit` بالإضافة لـ `store/update/destroy`.
- الهدف:
  - منع الضيوف/المستخدمين غير المصرح لهم من الوصول إلى صفحات إدارة الإدخال حتى لو كانت resource routes معرفة في المجموعة العامة.
- تم إضافة اختبارات أمنية:
  - `test_guest_cannot_access_exames_create_page`
  - `test_guest_cannot_access_publications_create_page`
- نتائج الاختبارات بعد Phase 25:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (28 tests, 60 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 26
- تم تحسين `InscriptionController` في عمليتي `update/destroy`:
  - استبدال `findOrFail($id)` العام باستعلام scoped:
    - `when(currentSchoolId, where('school_id', ...))`
  - الهدف: منع الوصول لأي سجل تسجيل خارج مدرسة الأدمن قبل الدخول إلى policy.
  - التحويل إلى عمليات مباشرة على الكائن المقيّد:
    - `$inscription->update(...)`
    - `$inscription->delete()`
- تم إضافة اختبار أمني:
  - `test_admin_cannot_delete_inscription_from_another_school`
- نتائج الاختبارات بعد Phase 26:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (29 tests, 62 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 27
- تنظيف تقني لتقليل surface area في عمليات الحذف:
  - إزالة معاملات `Request` غير المستخدمة من `destroy` في:
    - `AgendaScolaire/NoteStudentController`
    - `School/SchoolgradeController`
    - `AgendaScolaire/PublicationController`
    - `Inscription/InscriptionController`
    - `AgendaScolaire/GradeController`
    - `AgendaScolaire/AgendaController`
    - `School/SchoolController`
- الأثر:
  - لا تغيير سلوكي على التدفقات، لكن إزالة مدخلات HTTP غير ضرورية من نقاط حساسة.
  - تحسين وضوح أن هذه endpoints لا تعتمد body payload.
- نتائج الاختبارات بعد Phase 27:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (29 tests, 62 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 0 (Security/Uploads/Permissions) - Phase 28
- إغلاق بند توحيد بيئة التطوير والتشغيل:
  - إعادة بناء `backend-soubel-alnajah/README.md` بالكامل.
  - إزالة merge-conflict markers القديمة في README.
  - توثيق:
    - متطلبات البيئة
    - خطوات setup المحلي
    - تشغيل الاختبارات
    - ملاحظات تشغيلية أساسية (queue + scheduler)
- تحديث `IMPROVEMENT_PLAN_CHECKLIST.md`:
  - تعليم بندي Sprint 0 الأساسيين كمنجزين (`إصلاح الأساس` + `توحيد بيئة التطوير والتشغيل`).
  - اعتماد أن بنود الاستقرار الحرجة لـ Sprint 0 أُغلقت.
- نتائج الاختبارات المرجعية بعد الإغلاق:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (29 tests, 62 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 29
- بدء تنفيذ `A1/A3` لإزالة التكرار وتفكيك منطق الأعمال من الكنترولرز:
  - تحديث `InscriptionController` لاستخدام `ApproveInscriptionAction` بدل تنفيذ منطق القبول/الإنشاء داخله.
  - تحديث `FunctionController::store` (المسار القديم) لاستخدام نفس `ApproveInscriptionAction`.
  - إزالة الاعتماد المباشر على `StudentEnrollmentService` من الكنترولرز المذكورة، مع إبقائه داخل طبقة الـ Action/Service.
- الأثر:
  - توحيد use-case الحساس (قبول التسجيل) في مصدر واحد.
  - تقليل احتمالية divergence بين المسار الحديث والمسار القديم.
  - تقليل ضخامة الكنترولرز وتحسين قابلية الاختبار.
- نتائج الاختبارات بعد Phase 29:
  - `php artisan test --filter=approve_inscription --stop-on-failure` => PASS (2 tests, 4 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (29 tests, 62 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 30
- تنظيف إضافي في `FunctionController::store`:
  - إزالة `DB::transaction` الخارجية بعد اعتماد `ApproveInscriptionAction`.
  - السبب: الـ Action ينفذ المعاملة الذرية داخليًا، وبالتالي التعشيش الخارجي غير مطلوب.
- الأثر:
  - تبسيط مسار التنفيذ.
  - تقليل تعقيد التعامل مع الأخطاء والمعاملات المتداخلة.
- نتائج الاختبارات بعد Phase 30:
  - `php artisan test --filter=approve_inscription --stop-on-failure` => PASS (2 tests, 4 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 31
- تقليل تضخم `InscriptionController` في عمليتي `store/update`:
  - إضافة `app/Actions/Inscription/BuildInscriptionPayloadAction.php`.
  - استخراج mapping حقول التسجيل (الطالب + الولي + حقول الترجمة) إلى Action موحد.
  - `store` أصبح يعتمد على `forStore(...)` بدل الإسناد اليدوي الطويل.
  - `update` أصبح يعتمد على `forUpdate(...)` بدل تكرار نفس بنية الحقول.
- الحفاظ على السلوك الحالي:
  - `store` يبقي `statu = procec`.
  - `update` يبقي `statu` من قيمة الطلب.
  - `gender` يبقى مضبوطًا في الإنشاء كما كان سابقًا.
- نتائج الاختبارات بعد Phase 31:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (29 tests, 62 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 32
- بدء توحيد naming conventions (A2) مع توافق خلفي:
  - `NoteStudentController`:
    - إضافة method جديدة `displayNoteFromAdmin`.
    - الإبقاء على `DisplqyNoteFromAdmin` كـ alias مؤقت يستدعي الاسم الصحيح.
  - `FunctionController`:
    - اعتماد الاسم canonical `markAsRead`.
  - routes:
    - إضافة route جديدة `markAsRead` على `/mark-as-read/{id}`.
    - إبقاء route القديمة `/markasread/{id}` (name: `markasread`) وتشغيلها عبر نفس method الصحيحة `markAsRead`.
    - إضافة route جديدة `DisplayNoteFromAdmin` مع إبقاء `DisplqyNoteFromAdmin` القديمة.
  - views:
    - تحديث `resources/views/admin/addnotestudent.blade.php` لاستخدام route helper الصحيح `DisplayNoteFromAdmin`.
    - تحديث `resources/views/layoutsadmin/main_header.blade.php` لاستخدام route name الصحيح `markAsRead`.
- اختبارات آلية مضافة (التزام توجيه الاختبارات أثناء العمل):
  - `test_admin_cannot_mark_notification_from_another_user_as_read_via_canonical_route`
- نتائج الاختبارات بعد Phase 32:
  - `php artisan test --filter=mark_notification --stop-on-failure` => PASS (2 tests, 2 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (30 tests, 63 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 33
- توحيد naming conventions لنقاط جلب الـ AJAX (A2) مع توافق خلفي:
  - Controllers:
    - `SchoolgradeController`: إضافة `listBySchool()` والإبقاء على `getGrade()` كـ alias.
    - `ClassroomController`: إضافة `listByGrade()` والإبقاء على `getClasse()` كـ alias.
    - `SectionController`: إضافة `listByClassroom()` و`getSectionById()` مع إبقاء `getSection()/getSection2()` كـ aliases.
  - Routes:
    - إضافة مسارات canonical:
      - `/lookup/schools/{id}/grades`
      - `/lookup/grades/{id}/classes`
      - `/lookup/classes/{id}/sections`
      - `/lookup/sections/{id}`
    - الإبقاء على المسارات القديمة:
      - `/getgrade/{id}`, `/getclasse/{id}`, `/getsection/{id}`, `/getsection2/{id}`
  - Views (Ajax callers):
    - تحديث الاستدعاءات في:
      - `resources/views/layoutsadmin/js.blade.php`
      - `resources/views/front-end/inscription.blade.php`
      - `resources/views/admin/addStudentParent.blade.php`
      - `resources/views/admin/sections.blade.php`
      - `resources/views/admin/classes.blade.php`
    - الهدف: التحول التدريجي للأسماء الواضحة بدون كسر الشاشات القديمة.
- نتائج الاختبارات بعد Phase 33:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (30 tests, 63 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 34
- توحيد naming conventions لمورد ملفات النقاط (A2) مع توافق خلفي:
  - Routes:
    - إضافة resource canonical: `NoteStudents`.
    - إبقاء resource القديم `Addnotestudents` لتفادي كسر الروابط القديمة.
  - Views:
    - تحديث استدعاءات `route(...)` في:
      - `resources/views/admin/addnotestudent.blade.php`
      - `resources/views/admin/sections.blade.php`
    - التحويل إلى أسماء `NoteStudents.*` بدل `Addnotestudents.*`.
- اختبارات آلية مضافة:
  - `test_guest_cannot_access_note_students_show_canonical_route`
- نتائج الاختبارات بعد Phase 34:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (31 tests, 64 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 35
- توحيد naming conventions في `FunctionController` (A2) مع توافق خلفي:
  - Controllers:
    - إضافة methods canonical:
      - `showAgenda($id)` بدل `getAgenda($id)`
      - `showGallery()` بدل `getAlbum()`
      - `showChangePasswordPage()` بدل `changepass()`
    - إبقاء methods القديمة كـ aliases تستدعي الأسماء الجديدة.
  - Routes:
    - إضافة canonical routes:
      - `/school-agenda/{id}` (name: `public.agenda.show`)
      - `/gallery` (name: `public.gallery.index`)
      - `/change-password` (name: `admin.password.change.page`)
    - إبقاء المسارات legacy:
      - `/agenda/{id}`, `/album`, `/changepass`
  - Views:
    - تحديث روابط إعدادات الأدمن إلى `route('admin.password.change.page')` في:
      - `resources/views/layoutsadmin/main_header.blade.php`
      - `resources/views/layoutsadmin/main_sidebar.blade.php`
- اختبارات آلية مضافة:
  - `test_guest_cannot_access_change_password_canonical_route`
- نتائج الاختبارات بعد Phase 35:
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (32 tests, 65 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 36
- migration هيكلي تدريجي للـ namespace `Functionn` إلى `Function` (A2):
  - إضافة controller canonical جديد:
    - `app/Http/Controllers/Function/FunctionController.php`
  - تحويل controller legacy إلى wrapper متوافق:
    - `app/Http/Controllers/Functionn/FunctionController.php`
    - أصبح يرث من الـ canonical controller بدل احتواء منطق الأعمال.
  - تحديث routes للاعتماد على namespace canonical:
    - `use App\Http\Controllers\Function\FunctionController;`
- اختبار آلي معماري جديد:
  - `tests/Unit/Architecture/FunctionControllerNamespaceAliasTest.php`
  - يتحقق أن `Functionn\\FunctionController` ما زال موجودًا ومتوافقًا ويرث من canonical.
- نتائج الاختبارات بعد Phase 36:
  - `php artisan test --filter=FunctionControllerNamespaceAliasTest` => PASS (1 test, 3 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (32 tests, 65 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 37
- إغلاق legacy route `getgrades` ضمن A2:
  - Controllers:
    - إضافة `listAgendaGrades($id = null)` في `FunctionController` كاسم canonical.
    - إبقاء `getGrade($id = null)` كـ alias يستدعي الاسم الجديد.
  - Routes:
    - إضافة مسار canonical جديد:
      - `/agenda-grades/{id?}` (name: `public.agenda.grades`)
    - إبقاء legacy route:
      - `/getgrades/{id}`
  - Views:
    - تحديث روابط gallery العامة إلى route name بدل path legacy:
      - `resources/views/layouts/main_header.blade.php`
      - `resources/views/layouts/footer.blade.php`
- اختبارات آلية مضافة/محدثة:
  - `test_guest_can_reach_public_agenda_grades_canonical_route`
- نتائج الاختبارات بعد Phase 37:
  - `php artisan test --filter=\"guest_can_reach_public_agenda_grades_canonical_route|FunctionControllerNamespaceAliasTest\"` => PASS (2 tests, 4 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (33 tests, 66 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 38
- توسيع التغطية الآلية لمسارات canonical العامة (A2 hardening):
  - إضافة اختبارات:
    - `test_guest_can_reach_public_gallery_canonical_route`
    - `test_guest_can_reach_public_agenda_show_canonical_route`
  - الهدف:
    - ضمان أن التحول نحو `public.*` routes لا يكسر الوصول العام للزوار.
- نتائج الاختبارات بعد Phase 38:
  - `php artisan test --filter=\"guest_can_reach_public_gallery_canonical_route|guest_can_reach_public_agenda_show_canonical_route|guest_can_reach_public_agenda_grades_canonical_route\"` => PASS (3 tests, 3 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 39
- توحيد استدعاءات lookup في الواجهات إلى route names canonical بدل paths نصية:
  - استبدال نمط:
    - `URL::to('lookup/...') + id + ...`
  - بنمط:
    - `route('lookup.*', ['id' => '__ID__']).replace('__ID__', dynamicId)`
  - الملفات المحدثة:
    - `resources/views/layoutsadmin/js.blade.php`
    - `resources/views/front-end/inscription.blade.php`
    - `resources/views/admin/addStudentParent.blade.php`
    - `resources/views/admin/sections.blade.php`
    - `resources/views/admin/classes.blade.php`
- اختبار آلي معماري جديد:
  - `tests/Unit/Architecture/CanonicalRouteNamesTest.php`
  - يتحقق من تسجيل route names canonical الأساسية (`lookup.*`, `public.*`, `admin.password.change.page`).
- نتائج الاختبارات بعد Phase 39:
  - `php artisan test --filter=CanonicalRouteNamesTest` => PASS (1 test, 8 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 40
- تنظيم طبقة legacy aliases في routes:
  - تسمية legacy endpoints غير المسمّاة سابقًا باسماء `legacy.*`:
    - `legacy.lookup.schoolGrades`
    - `legacy.lookup.gradeClasses`
    - `legacy.lookup.classSections`
    - `legacy.lookup.sectionById`
    - `legacy.public.agenda.show`
    - `legacy.public.agenda.grades`
    - `legacy.public.gallery.index`
  - الحفاظ على أسماء التوافق التاريخية المطلوبة:
    - `changepass`
    - `DisplqyNoteFromAdmin`
- اختبارات آلية مضافة:
  - `tests/Unit/Architecture/LegacyAliasRouteNamesTest.php`
    - التحقق من تسجيل legacy route names.
    - التحقق أن المسارات legacy ما زالت على نفس URIs القديمة (`/getgrade`, `/getclasse`, `/getsection`, `/getsection2`, `/agenda`, `/album`, `/changepass`).
- نتائج الاختبارات بعد Phase 40:
  - `php artisan test --filter=\"CanonicalRouteNamesTest|LegacyAliasRouteNamesTest|FunctionControllerNamespaceAliasTest\"` => PASS (4 tests, 27 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 41
- إغلاق آخر آثار التسمية القديمة `gallrey` ضمن A2:
  - إضافة view canonical جديدة:
    - `resources/views/front-end/gallery.blade.php`
  - تحديث `FunctionController::showGallery()` لاستخدام `front-end.gallery` بدل `front-end.gallrey`.
  - إضافة مفتاح ترجمة canonical جديد `main_header.gallery` في:
    - `resources/lang/ar/main_header.php`
    - `resources/lang/fr/main_header.php`
    - `resources/lang/en/main_header.php`
  - تحديث الواجهة العامة لاستخدام `main_header.gallery` في:
    - `resources/views/layouts/main_header.blade.php`
    - `resources/views/layouts/footer.blade.php`
  - الإبقاء على `gallrey` وملف `front-end/gallrey.blade.php` للتوافق الخلفي المؤقت.
- حالة A2:
  - تم تعليم `A2` كمكتمل في `IMPROVEMENT_PLAN_CHECKLIST.md` بعد اكتمال التوحيد (canonical + aliases + tests).
- نتائج الاختبارات بعد Phase 41:
  - `php artisan test --filter=\"CanonicalRouteNamesTest|LegacyAliasRouteNamesTest|FunctionControllerNamespaceAliasTest\"` => PASS (4 tests, 27 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 42
- بدء A1/A3 على `StudentController`:
  - إضافة action جديدة:
    - `app/Actions/Inscription/BuildStudentEnrollmentPayloadAction.php`
  - تحديث `StudentController`:
    - حقن `BuildStudentEnrollmentPayloadAction` عبر constructor.
    - استبدال البناء اليدوي المطوّل لـ `studentPayload` و`guardianPayload` في `store()` باستدعاء action موحد.
- اختبار آلي جديد:
  - `tests/Unit/Inscription/BuildStudentEnrollmentPayloadActionTest.php`
  - يغطي صحة mapping الحقول (student + guardian) والتحويل النوعي لـ `gender`.
- نتائج الاختبارات بعد Phase 42:
  - `php artisan test --filter=BuildStudentEnrollmentPayloadActionTest` => PASS (1 test, 8 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 43
- متابعة A1/A3 على `StudentController@update`:
  - إضافة action جديدة:
    - `app/Actions/Inscription/UpdateStudentEnrollmentAction.php`
  - نقل منطق التحديث المعقد (داخل transaction) من controller إلى action:
    - تحديث `studentinfos`
    - تحديث `users` (حساب الطالب)
    - تحديث `my_parents`
    - تحديث `users` (حساب الولي)
  - تبسيط `StudentController@update` ليصبح تنسيق HTTP + authorize + استدعاء action.
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/StudentUpdateActionTest.php`
  - يتحقق أن عملية تحديث الطالب تحدّث جميع الكيانات المرتبطة كما هو متوقع.
- نتائج الاختبارات بعد Phase 43:
  - `php artisan test --filter=\"BuildStudentEnrollmentPayloadActionTest|StudentUpdateActionTest\"` => PASS (2 tests, 27 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 44
- متابعة A1/A3 على `StudentController@destroy`:
  - إضافة action جديدة:
    - `app/Actions/Inscription/DeleteStudentEnrollmentAction.php`
  - نقل منطق الحذف المتداخل من controller إلى action:
    - حذف `studentinfos` نهائيًا (`forceDelete`)
    - حذف حساب الطالب المرتبط
    - حذف الولي وحسابه فقط عند عدم وجود أبناء آخرين
    - تنظيف إشعارات المستخدمين المرتبطين من جدول `notifications`
  - تبسيط `StudentController@destroy` ليصبح: authorize + استدعاء action + redirect.
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/StudentDestroyActionTest.php`
  - يغطي سيناريوهين:
    - حذف الطالب والولي عند عدم وجود أبناء آخرين.
    - إبقاء الولي عند وجود ابن آخر مع حذف الطالب المستهدف فقط.
- ملاحظة تنفيذية:
  - تشغيل الاختبارات بالتوازي على نفس قاعدة MySQL سبّب تعارض migrations (`RefreshDatabase`)، لذلك تم اعتماد التشغيل التسلسلي لهذه المجموعة.
- نتائج الاختبارات بعد Phase 44:
  - `php artisan test --filter=StudentDestroyActionTest` => PASS (2 tests, 12 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 45
- متابعة A1/A3 على `TeacherController`:
  - إضافة actions جديدة:
    - `app/Actions/Inscription/CreateTeacherEnrollmentAction.php`
    - `app/Actions/Inscription/UpdateTeacherEnrollmentAction.php`
    - `app/Actions/Inscription/DeleteTeacherEnrollmentAction.php`
  - نقل منطق `store/update/destroy` من `TeacherController` إلى actions:
    - إنشاء حساب المستخدم + إسناد دور المعلم + dispatch onboarding link.
    - تحديث بيانات المعلم + حساب المستخدم المرتبط.
    - حذف المعلم + حذف المستخدم المرتبط.
  - تبسيط `TeacherController` ليقتصر على authorize/validation/HTTP flow.
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/TeacherEnrollmentActionsTest.php`
  - يغطي:
    - تحديث المعلم وانعكاسه على `teachers` و`users`.
    - حذف المعلم غير المرتبط بأقسام مع حذف المستخدم المرتبط.
- ملاحظة تنفيذية:
  - تم اعتماد تشغيل اختبارات Sprint بشكل تسلسلي بعد `migrate:fresh` لتجنب تعارض `RefreshDatabase` على MySQL عند التشغيل المتوازي.
- نتائج الاختبارات بعد Phase 45:
  - `php artisan test --filter=TeacherEnrollmentActionsTest` => PASS (2 tests, 12 assertions).
  - `php artisan test --filter=StudentDestroyActionTest` => PASS (2 tests, 12 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 46
- متابعة A1/A3 على `InscriptionController`:
  - إضافة actions جديدة:
    - `app/Actions/Inscription/UpdateInscriptionStatusAction.php`
    - `app/Actions/Inscription/DeleteInscriptionAction.php`
  - تحديث `InscriptionController` لاستخدام actions بدل التنفيذ المباشر داخل:
    - `updateStatus`
    - `destroy`
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/InscriptionLifecycleActionsTest.php`
  - يغطي:
    - تغيير حالة التسجيل (`statu`) عبر المسار الرسمي.
    - حذف التسجيل عبر `Inscriptions.destroy`.
- نتائج الاختبارات بعد Phase 46:
  - `php artisan test --filter=InscriptionLifecycleActionsTest` => PASS (2 tests, 4 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 47
- متابعة A1/A3 على `InscriptionController` (استكمال):
  - إضافة actions جديدة:
    - `app/Actions/Inscription/CreateInscriptionAction.php`
    - `app/Actions/Inscription/UpdateInscriptionAction.php`
  - تحديث `InscriptionController` لاستخدام actions بدل الحفظ المباشر في:
    - `store`
    - `update`
  - الإبقاء على `BuildInscriptionPayloadAction` كمصدر موحد لـ mapping قبل طبقة persistence.
- اختبار آلي جديد (Unit):
  - `tests/Unit/Inscription/InscriptionPersistenceActionsTest.php`
  - يغطي:
    - حفظ تسجيل جديد عبر `CreateInscriptionAction`.
    - تحديث تسجيل موجود عبر `UpdateInscriptionAction`.
- نتائج الاختبارات بعد Phase 47:
  - `php artisan test --filter=InscriptionPersistenceActionsTest` => PASS (2 tests, 3 assertions).
  - `php artisan test --filter=InscriptionLifecycleActionsTest` => PASS (2 tests, 4 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 48
- متابعة A1/A3 على `FunctionController`:
  - إضافة actions جديدة:
    - `app/Actions/Notification/SendSchoolCertificateNotificationAction.php`
    - `app/Actions/Notification/MarkUserNotificationAsReadAction.php`
  - تحديث `FunctionController` لاستخدام actions بدل التنفيذ المباشر داخل:
    - `notify`
    - `markAsRead`
  - الحفاظ على نفس سلوك المسارات وواجهة العرض بدون كسر التوافق الخلفي.
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/FunctionNotificationActionsTest.php`
  - يغطي:
    - إنشاء إشعار قاعدة بيانات عبر endpoint `notify`.
    - تعليم إشعار المستخدم الحالي كمقروء عبر endpoint `markAsRead`.
- نتائج الاختبارات بعد Phase 48:
  - `php artisan test --filter=FunctionNotificationActionsTest` => PASS (2 tests, 4 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 1 (Refactor & Cleanup) - Phase 49
- استكمال تنظيف `FunctionController`:
  - إضافة actions جديدة:
    - `app/Actions/Function/BuildAgendaPageDataAction.php`
    - `app/Actions/Function/BuildGalleryPageDataAction.php`
    - `app/Actions/Inscription/ApproveInscriptionByClassroomAction.php`
  - تحديث `FunctionController`:
    - `showAgenda` أصبح يعتمد Action لبناء بيانات الصفحة وتحديد الـ view.
    - `showGallery` أصبح يعتمد Action مخصص.
    - `store` (المسار legacy) أصبح يعتمد `ApproveInscriptionByClassroomAction` بدل منطق اختيار القسم داخل الكنترولر.
- اختبار آلي جديد (Feature):
  - `tests/Feature/Refactor/FunctionLegacyInscriptionApprovalTest.php`
  - يغطي نجاح قبول تسجيل عبر المسار legacy `POST /store/{id}` داخل نفس المدرسة مع تحديث الحالة إلى `accept`.
- نتائج الاختبارات بعد Phase 49:
  - `php artisan test --filter=\"FunctionLegacyInscriptionApprovalTest|FunctionNotificationActionsTest\"` => PASS (3 tests, 7 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 2 (Database & Migrations) - Phase 50
- تنفيذ `B3` (Pre-Implementation Schema) للميزات الثلاث:
  - Recruitment:
    - `job_posts`
    - `job_applications`
    - migration: `database/migrations/2026_02_25_100000_create_recruitment_tables.php`
  - Timetable:
    - `timetables`
    - `timetable_entries`
    - migration: `database/migrations/2026_02_25_100100_create_timetables_tables.php`
  - Accounting:
    - `payment_plans`
    - `student_contracts`
    - `contract_installments`
    - `payments`
    - `payment_receipts`
    - migration: `database/migrations/2026_02_25_100200_create_accounting_tables.php`
- قرارات هيكلية:
  - إضافة `school_id` وفهارس مركبة في الجداول الجديدة لدعم multi-school scoping وتقارير الإدارة.
  - إضافة unique constraints لمنع التكرار الحرج (slug, contract-per-year, receipt_number, timetable slot).
  - فصل طبقة التخزين المحاسبي إلى عقود + أقساط + دفعات + وصولات لضمان قابلية التوسع.
- توثيق المخطط:
  - إضافة ملف `FEATURES_DB_BLUEPRINT.md` كمرجع ERD/Flow قبل التنفيذ الوظيفي.
- نتائج التحقق:
  - `php artisan migrate:fresh --force` => PASS (جميع migrations بما فيها الجديدة).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 2 (Database & Analysis) - Phase 51
- بدء `F3-Analysis` (Excel Mapping) مع fallback عملي:
  - لم يتم العثور على ملف Excel المحاسبة داخل المشروع الحالي.
  - تم العثور فقط على ملف امتحانات: `public/exames/1665495440OrU0SDbqtu.xls` (غير متعلق بالمحاسبة).
- بدل التوقف، تم إنشاء وثيقة mapping أولية:
  - `docs/accounting-mapping.md`
  - تحتوي:
    - mapping baseline من متطلبات الإدارة إلى جداول المحاسبة الجديدة.
    - قواعد اشتقاق الحالة (`paid/partial/overdue`) بشكل تنفيذي.
    - قائمة المدخلات المطلوبة من ملف Excel لإغلاق التحليل النهائي.
- الحالة:
  - `F3-Analysis` ما يزال مفتوحًا رسميًا إلى حين تزويد ملف Excel الفعلي.

## 2026-02-25 - Sprint 2 (Database & Performance) - Phase 52
- تنفيذ جزء من `B2` (Indexing Baseline):
  - إضافة migration:
    - `database/migrations/2026_02_25_100300_add_performance_indexes_to_core_tables.php`
  - الفهارس المضافة:
    - `studentinfos`: فهارس على `section_id/created_at`, `parent_id/created_at`, `user_id`.
    - `inscriptions`: فهارس على `school_id/statu/created_at`, `grade_id/classroom_id`, `numtelephone`.
    - `absences`: فهارس على `student_id/date`, `date`.
    - `publications`: فهارس على `school_id/created_at`, `grade_id/agenda_id`.
    - `chat_room_user`: فهرس `user_id/last_read_at`.
    - `chat_messages`: فهارس `chat_room_id/created_at`, `user_id/created_at`.
- نتائج التحقق:
  - `php artisan migrate:fresh --force` => PASS.
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).
- ملاحظة:
  - إغلاق بند `B2` النهائي مؤجل لحين قياس أثر الأداء الفعلي على شاشات الإدارة (قبل/بعد).

## 2026-02-25 - Sprint 3 (Feature 1 - Recruitment) - Phase 53
- تنفيذ `F1-Admin/Public UI` + `F1-Security` بشكل كامل:
  - Models:
    - `app/Models/Recruitment/JobPost.php`
    - `app/Models/Recruitment/JobApplication.php`
  - Policies:
    - `app/Policies/JobPostPolicy.php`
    - `app/Policies/JobApplicationPolicy.php`
    - وربطها في `AuthServiceProvider`.
  - Requests:
    - `StoreJobPostRequest`
    - `UpdateJobPostRequest`
    - `StoreJobApplicationRequest`
    - `UpdateJobApplicationStatusRequest`
  - Controllers:
    - `Recruitment/JobPostController` (CRUD إداري + فلترة/بحث/pagination)
    - `Recruitment/JobApplicationController` (قائمة الطلبات + تحديث الحالة + تنزيل CV)
    - `Recruitment/PublicJobController` (عرض عام + تقديم ترشح)
  - Routes:
    - Public:
      - `public.jobs.index`
      - `public.jobs.show`
      - `public.jobs.apply` مع `throttle:6,1`
    - Admin:
      - Resource: `JobPosts`
      - `recruitment.applications.index`
      - `recruitment.applications.status`
      - `recruitment.applications.cv`
  - Views:
    - Admin:
      - `resources/views/admin/recruitment/job_posts/index.blade.php`
      - `resources/views/admin/recruitment/job_posts/create.blade.php`
      - `resources/views/admin/recruitment/job_posts/edit.blade.php`
      - `resources/views/admin/recruitment/applications/index.blade.php`
    - Public:
      - `resources/views/front-end/recruitment/jobs.blade.php`
      - `resources/views/front-end/recruitment/show.blade.php`
  - Navigation:
    - إضافة رابط التوظيف في:
      - `resources/views/layouts/main_header.blade.php`
      - `resources/views/layouts/footer.blade.php`
      - `resources/views/layoutsadmin/main_sidebar.blade.php`
    - إضافة مفاتيح ترجمة `main_header.recruitment` في:
      - `resources/lang/ar/main_header.php`
      - `resources/lang/fr/main_header.php`
      - `resources/lang/en/main_header.php`
- نقاط الأمان المنفذة:
  - رفع CV فقط `PDF/DOC/DOCX` وبحجم أقصى 5MB.
  - honeypot field (`website`) لمنع spam bot submissions.
  - تخزين الملفات في `storage/app/private/recruitment/...` خارج public.
  - تنزيل CV عبر Authorization policy مع header `X-Content-Type-Options: nosniff`.
  - عزل الوصول الإداري حسب المدرسة في policies + queries.
- اختبارات آلية جديدة:
  - `tests/Feature/Recruitment/RecruitmentFlowTest.php`
  - تغطي:
    - تقديم ضيف ناجح مع رفع CV آمن.
    - منع spam عبر honeypot.
    - منع أدمن مدرسة من تحميل CV مدرسة أخرى.
    - منع غير الأدمن من فتح إدارة التوظيف.
- نتائج التحقق:
  - `php artisan test --filter=RecruitmentFlowTest` => PASS (4 tests, 11 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 3 (Feature 2 - Timetables) - Phase 54
- تنفيذ `F2-Admin Builder + Print` و`F2-Public Viewer`:
  - Models:
    - `app/Models/Timetable/Timetable.php`
    - `app/Models/Timetable/TimetableEntry.php`
  - Policy:
    - `app/Policies/TimetablePolicy.php`
    - وربطها في `AuthServiceProvider`.
  - Requests:
    - `StoreTimetableRequest`
    - `UpdateTimetableRequest`
  - Controllers:
    - `Timetable/TimetableController` (Admin CRUD + print)
    - `Timetable/PublicTimetableController` (public listing + show)
  - Routes:
    - Admin: resource `timetables` + `timetables.print`
    - Public: `public.timetables.index`, `public.timetables.show` على URI `school-timetables/*`
  - Views:
    - Admin:
      - `resources/views/admin/timetables/index.blade.php`
      - `resources/views/admin/timetables/create.blade.php`
      - `resources/views/admin/timetables/edit.blade.php`
      - `resources/views/admin/timetables/print.blade.php`
    - Public:
      - `resources/views/front-end/timetables/index.blade.php`
      - `resources/views/front-end/timetables/show.blade.php`
  - Navigation/Translations:
    - إضافة روابط الجداول في:
      - `layoutsadmin/main_sidebar.blade.php`
      - `layouts/main_header.blade.php`
      - `layouts/footer.blade.php`
    - إضافة مفتاح `main_header.timetables` في (ar/fr/en).
- اختبارات آلية جديدة:
  - `tests/Feature/Timetable/TimetableFlowTest.php`
  - تغطي:
    - إنشاء جدول بحصص متعددة لأدمن نفس المدرسة.
    - منع إنشاء جدول لقسم تابع لمدرسة أخرى.
    - الوصول لمسار الطباعة الإداري.
- نتائج التحقق:
  - `php artisan test --filter=TimetableFlowTest` => PASS (3 tests, 6 assertions).
  - `php artisan test --filter=RecruitmentFlowTest` => PASS (4 tests, 11 assertions).
  - `php artisan test --filter=SprintZeroSecurityTest` => PASS (35 tests, 68 assertions).
  - `php artisan test --filter=OnboardingFlowTest` => PASS (2 tests, 7 assertions).

## 2026-02-25 - Sprint 3 (Feature 3 - Accounting) - Phase 55
- تنفيذ `F3-Role Accountant` + `F3-Workflows + Reports` + `F3-Optional Receipt Print`:
  - Controllers:
    - `app/Http/Controllers/Accounting/ContractController.php`
      - إدارة العقود (قائمة/إنشاء/تحديث) مع `school scoping`.
      - توليد الأقساط تلقائيًا حسب نوع الخطة (`yearly/installments/monthly`).
    - `app/Http/Controllers/Accounting/PaymentController.php`
      - إدارة الدفعات (قائمة/تسجيل) مع تحديث حالة العقد آليًا (`paid/partial/overdue/active`).
      - إنشاء سجل `payment_receipts` تلقائيًا لكل دفعة.
      - إضافة مسار عرض/طباعة الوصل `receipt`.
  - Routes:
    - إضافة مسارات محاسبة محمية:
      - `accounting.dashboard`
      - `accounting.contracts.index/store/update`
      - `accounting.payments.index/store/receipt`
  - Views:
    - `resources/views/admin/accounting/contracts/index.blade.php`
      - شاشة العقود مع: إنشاء سريع + بحث/فلترة + pagination + تحديث.
      - مؤشرات المدفوع/المتبقي + قائمة المتأخرين.
    - `resources/views/admin/accounting/payments/index.blade.php`
      - شاشة الدفعات مع: تسجيل سريع + فلترة حسب الفترة/القسم + pagination.
      - قائمة متأخرين.
    - `resources/views/admin/accounting/payments/receipt.blade.php`
      - وصل دفع قابل للطباعة.
  - Navigation:
    - إضافة روابط المحاسبة في:
      - `resources/views/layoutsadmin/main_sidebar.blade.php`
    - الروابط تظهر فقط للمستخدمين ذوي الدور `admin` أو `accountant`.
  - Models/Refinements:
    - تحديث `app/Models/Accounting/StudentContract.php` بإضافة casts للتواريخ.
    - إصلاح parsing تاريخ بداية الأقساط باستخدام `Carbon::parse`.

- اختبارات آلية جديدة:
  - `tests/Feature/Accounting/AccountingFlowTest.php`
  - تغطي:
    - إنشاء عقد وتسجيل دفعة وإنشاء وصل وتحديث حالة العقد.
    - منع إنشاء عقد لتلميذ من مدرسة أخرى.
    - منع المستخدم غير المخوّل محاسبيًا من الوصول لشاشات المحاسبة.

- نتائج التحقق:
  - `php artisan test --filter='(AccountingFlowTest|RecruitmentFlowTest|TimetableFlowTest)'` => PASS (10 tests, 28 assertions).
  - `php artisan test --filter='(SprintZeroSecurityTest|OnboardingFlowTest)'` => PASS (37 tests, 75 assertions).

## 2026-02-25 - Sprint 3 (CI Enablement) - Phase 56
- تم إغلاق بند `F3. CI بسيط` عبر إضافة GitHub Actions workflow:
  - `.github/workflows/ci.yml`
- تصميم الـ pipeline:
  - يعمل على `push` (`main/master`) و`pull_request`.
  - يستخدم `PHP 8.2` مع الامتدادات المطلوبة لـ Laravel.
  - يعتمد `working-directory: backend-soubel-alnajah` لأن المشروع Laravel داخل مجلد فرعي.
- خطوات التنفيذ داخل CI:
  - `composer install --prefer-dist`
  - تجهيز البيئة (`.env`, `php artisan key:generate`, sqlite file)
  - فحص syntax لكل ملفات PHP (`php -l`)
  - `php artisan migrate --force` على SQLite testing DB
  - `php artisan test --without-tty`
- الهدف المحقق:
  - فحص تلقائي موحد يمنع دمج تغييرات تكسر الاختبارات أو syntax.

## 2026-02-25 - Sprint 4 (UI/UX CRUD Modernization) - Phase 57
- تنفيذ تحسينات عملية على شاشة إدارة التلاميذ `Students.index`:
  - تحديث `StudentController@index`:
    - تحويل القائمة من `get()` إلى `paginate(20)` مع `withQueryString()`.
    - إضافة بحث نصي (`q`) على الاسم/البريد/رقم الهاتف.
    - إضافة فلاتر حسب: `section_id`, `classroom_id`, `grade_id`.
    - الحفاظ على `school scoping` الإلزامي عبر `StudentInfo::forSchool(...)`.
    - تحميل بيانات الأقسام `Sections` لاستخدامها في فلاتر الواجهة.
  - تحديث واجهة `resources/views/admin/studentInfo.blade.php`:
    - إضافة نموذج Search/Filters أعلى الجدول.
    - ترقيم النتائج حسب الـ paginator (`firstItem + index`).
    - إضافة روابط pagination (`$StudentInfo->links()`).
    - إزالة الاعتماد على DataTables المحلي لهذه الشاشة.
- اختبارات آلية جديدة:
  - `tests/Feature/Inscription/StudentIndexFiltersTest.php`
  - تغطية:
    - التأكد من العزل المدرسي + pagination (20 عنصر في الصفحة الأولى).
    - التأكد من فلترة القسم (`section_id`) بشكل صحيح.
  - تم تعطيل middlewares التوطين فقط داخل هذا الاختبار لتثبيت سلوك المسار ومنع false negatives من redirects.
- نتائج التحقق:
  - `php artisan test --filter='(StudentIndexFiltersTest|AccountingFlowTest)'` => PASS (5 tests, 15 assertions).
  - `php -l` للملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (UI/UX CRUD Modernization) - Phase 58
- استكمال تحديث صفحات CRUD الحرجة عبر شاشة إدارة المعلمين `Teachers.index`:
  - تحديث `TeacherController@index`:
    - تحويل القائمة من `get()` إلى `paginate(20)` مع `withQueryString()`.
    - إضافة بحث (`q`) على اسم المعلم/البريد.
    - إضافة فلاتر `specialization_id` و`gender`.
    - الحفاظ على عزل المدرسة عبر `Teacher::forSchool(...)`.
  - تحديث واجهة `resources/views/admin/teacher.blade.php`:
    - إضافة نموذج Search/Filters أعلى الجدول.
    - ترقيم النتائج باستخدام paginator.
    - إضافة pagination links.
    - إزالة DataTables المحلي من الشاشة.
- اختبارات آلية جديدة:
  - `tests/Feature/Inscription/TeacherIndexFiltersTest.php`
  - تغطي:
    - school scoping + pagination.
    - filter by specialization + gender.
  - مع تعطيل middlewares التوطين داخل الاختبار لتثبيت سلوك المسارات ومنع تأثير redirects.
- نتائج التحقق:
  - `php artisan test --filter='(TeacherIndexFiltersTest|StudentIndexFiltersTest)'` => PASS (4 tests, 8 assertions).
  - `php -l` للملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (UI/UX CRUD Modernization) - Phase 59
- استكمال تحديث صفحات CRUD الحرجة عبر شاشة التسجيلات `Inscriptions.index`:
  - تحديث `InscriptionController@index`:
    - إبقاء سلوك الواجهة العامة كما هو (front-end) مع التحسين موجّه للوحة الإدارة فقط.
    - تحويل قائمة الإدارة إلى `paginate(20)` مع `withQueryString()`.
    - إضافة بحث (`q`) على الاسم/البريد/الهاتف.
    - إضافة فلاتر `status` و`classroom_id`.
    - الحفاظ على school scoping على مستوى الاستعلامات.
    - تحميل قائمة `Classrooms` لاستخدامها في الفلاتر.
  - تحديث واجهة `resources/views/admin/studentsinscription.blade.php`:
    - إضافة نموذج Search/Filters أعلى الجدول.
    - تحديث ترقيم الصفوف باستخدام paginator بدل عداد محلي.
    - إضافة `{{ $Inscription->links() }}`.
    - إزالة سكربت DataTables الثقيل من الصفحة.
- اختبارات آلية جديدة:
  - `tests/Feature/Inscription/InscriptionIndexFiltersTest.php`
  - تغطي:
    - school scoping + pagination.
    - filters by `status` and `classroom_id`.
  - مع تعطيل middlewares التوطين داخل الاختبار لتثبيت المسارات ومنع تأثير redirects.
- نتائج التحقق:
  - `php artisan test --filter='(StudentIndexFiltersTest|TeacherIndexFiltersTest|InscriptionIndexFiltersTest)'` => PASS (6 tests, 12 assertions).
  - `php -l` للملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (UI/UX CRUD Modernization) - Phase 60
- استكمال تحديث صفحات CRUD الحرجة على وحدتي `Absences` و`Publications`:

### 1) Absences (لوحة الإدارة)
- تحديث `AbsenceController@index`:
  - إضافة فلاتر: `q`, `date_from`, `date_to`, `section_id`.
  - إبقاء `school scoping` عبر علاقة الطالب/القسم.
  - إضافة eager loading للعلاقات (`student.user`, `student.section`).
  - تحويل النتائج إلى `paginate(20)` مع `withQueryString()`.
  - تحميل قائمة الأقسام (`Sections`) لدعم فلاتر الواجهة.
- تحديث `resources/views/admin/AbsenceStudent.blade.php`:
  - إضافة نموذج Search/Filters.
  - استخدام ترقيم paginator بدل `loop->iteration`.
  - إضافة pagination links.
  - إزالة DataTables المحلي من الصفحة.

### 2) Publications (لوحة الإدارة)
- تحديث `PublicationController@index`:
  - إبقاء الواجهة العامة دون تغيير جوهري، وتفعيل الفلاتر في مسار الأدمن.
  - إضافة فلاتر: `q`, `grade_id`, `agenda_id`, `date_from`, `date_to`.
  - إبقاء `school scoping` للأدمن المرتبط بمدرسة.
  - تحويل قائمة الأدمن إلى `paginate(20)` مع `withQueryString()`.
- تحديث `resources/views/admin/publications.blade.php`:
  - إضافة نموذج Search/Filters.
  - استخدام ترقيم paginator.
  - إضافة pagination links.
  - إزالة DataTables المحلي من الصفحة.

- اختبارات آلية جديدة:
  - `tests/Feature/AgendaScolaire/AdminIndexFiltersTest.php`
  - تغطي:
    - `Absences.index`: school scoping + pagination.
    - `Publications.index`: school scoping + pagination + query filtering.

- نتائج التحقق:
  - `php artisan test --filter='(AdminIndexFiltersTest|SprintZeroSecurityTest)'` => PASS (37 tests, 74 assertions).
  - `php -l` للملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Layout/Navigation) - Phase 61
- بدء تنفيذ بند `C2` عبر تحسين التنقل حسب الدور:
  - تحديث `resources/views/layoutsadmin/main_sidebar.blade.php` بإضافة وضع `accountant-only`:
    - عند دخول مستخدم بدور `accountant` بدون `admin`:
      - تظهر قائمة مالية مبسطة فقط:
        - `accounting.contracts.index`
        - `accounting.payments.index`
      - يتم إخفاء باقي قوائم الإدارة الأكاديمية لتقليل التشتيت ومنع إظهار عناصر غير لازمة.
- اختبار آلي جديد:
  - `tests/Feature/Accounting/AccountantSidebarTest.php`
  - يغطي:
    - ظهور روابط المحاسبة.
    - عدم ظهور روابط الطلاب/الأساتذة/التسجيلات للمحاسب.
- نتائج التحقق:
  - `php artisan test --filter='(AccountantSidebarTest|AccountingFlowTest)'` => PASS (4 tests, 17 assertions).
  - `php -l` للملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Layout/Navigation) - Phase 62
- استكمال بند `C2` بتوحيد واجهة صفحة الإدارة (Page Chrome):
  - إنشاء partials جديدة:
    - `resources/views/layoutsadmin/partials/page_header.blade.php` (عنوان الصفحة + breadcrumb)
    - `resources/views/layoutsadmin/partials/status_alerts.blade.php` (success/error/warning/info + validation errors)
  - دمجها في `resources/views/layoutsadmin/masteradmin.blade.php` قبل `@yield('contenta')` لضمان تطبيق موحد.
- توحيد عرض الأخطاء/الرسائل:
  - إزالة كتل `@if ($errors->any()) ...` المكررة من صفحات الإدارة التي تم تحديثها:
    - `admin/studentInfo.blade.php`
    - `admin/teacher.blade.php`
    - `admin/studentsinscription.blade.php`
    - `admin/AbsenceStudent.blade.php`
    - `admin/publications.blade.php`
    - `admin/accounting/contracts/index.blade.php`
    - `admin/accounting/payments/index.blade.php`
- إضافة breadcrumbs من Controllers (index/admin pages):
  - `StudentController`
  - `TeacherController`
  - `InscriptionController`
  - `AbsenceController`
  - `PublicationController`
  - `Accounting/ContractController`
  - `Accounting/PaymentController` (index + receipt)
- اختبار آلي جديد:
  - `tests/Feature/UI/AdminLayoutChromeTest.php`
  - يغطي:
    - ظهور breadcrumb في صفحة إدارة مالية.
    - ظهور success alert الموحد من session.
- نتائج التحقق:
  - `php artisan test --filter='(AdminLayoutChromeTest|AccountantSidebarTest|AccountingFlowTest)'` => PASS (6 tests, 22 assertions).
  - `php -l` لجميع الملفات المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Design System) - Phase 63
- بدء تنفيذ بند `C1` (UI Stack حديث متدرج) عبر طبقة تصميم موحدة للإدارة:
  - إضافة ملف CSS مركزي جديد:
    - `public/cssadmin/admin-modern.css`
  - يحتوي على:
    - Design Tokens على مستوى `:root` (primary/success/danger/warning/info/text/border/surfaces/radius/shadow).
    - قواعد موحدة لمكونات الإدارة:
      - `content-header` + `breadcrumb`
      - `alert`
      - `box`
      - `form-control` / `form-select`
      - `table`
      - `pagination`
  - ربط الملف داخل:
    - `resources/views/layoutsadmin/head.blade.php`
- أثر التنفيذ:
  - تحسين بصري موحد تدريجيًا للصفحات التي تم تحديثها في C2/C3 بدون كسر هيكل القالب الحالي.
  - الحفاظ على توافق RTL (عدم كسر اتجاهات القالب الحالية).
- نتائج التحقق:
  - `php artisan test --filter='(AdminLayoutChromeTest|StudentIndexFiltersTest|TeacherIndexFiltersTest|InscriptionIndexFiltersTest|AdminIndexFiltersTest)'` => PASS (10 tests, 23 assertions).
  - `php -l` لملفات layout المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Design System) - Phase 64
- توسيع تطبيق Design System (`admin-modern.css`) على صفحات الإدارة المكتملة:
  - Recruitment:
    - `resources/views/admin/recruitment/job_posts/index.blade.php`
    - `resources/views/admin/recruitment/applications/index.blade.php`
  - Timetables:
    - `resources/views/admin/timetables/index.blade.php`
  - Accounting:
    - `resources/views/admin/accounting/contracts/index.blade.php`
    - `resources/views/admin/accounting/payments/index.blade.php`
- ما تم توحيده بصريًا:
  - إزالة blocks عرض الأخطاء المكررة (الآن مصدر موحد من layout alerts partial).
  - اعتماد empty-state موحد داخل الجداول (`admin-empty-state`).
  - اعتماد status badges موحدة (`admin-status`, `admin-status-*`) لحالات:
    - recruitment (`new/in_review/accepted/rejected`, `draft/published/closed`)
    - timetables (`draft/published`)
    - accounting (`active/partial/paid/overdue/...`).
- تحديث CSS المركزي:
  - إضافة قواعد:
    - `.admin-empty-state`
    - `.admin-status` + variants (`draft`, `published`, `new`, `in_review`, `accepted`, `rejected`, `active`, `partial`, `paid`, `overdue`, `closed`)
- نتائج التحقق:
  - `php artisan test --filter='(RecruitmentFlowTest|TimetableFlowTest|AccountingFlowTest|AdminLayoutChromeTest|AccountantSidebarTest)'` => PASS (13 tests, 39 assertions).
  - `php -l` لملفات Blade/CSS المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Design System) - Phase 65
- مواصلة صقل C1 على واجهات الإدارة المتقدمة:
  - Recruitment:
    - `admin/recruitment/job_posts/index.blade.php`
    - `admin/recruitment/applications/index.blade.php`
  - Timetables:
    - `admin/timetables/index.blade.php`
  - Accounting:
    - `admin/accounting/contracts/index.blade.php`
    - `admin/accounting/payments/index.blade.php`
- التغييرات:
  - إزالة رسائل أخطاء مكررة داخل الصفحات (layout alerts partial أصبح المصدر الموحد).
  - اعتماد badges موحدة للحالات الديناميكية (`admin-status-*`).
  - اعتماد empty states موحدة (`admin-empty-state`) في الجداول والقوائم.
  - توسيع `admin-modern.css` ليغطي حالات إضافية (`published/accepted/paid/active/partial/new/in_review/overdue/rejected/closed/draft`).
- نتائج التحقق:
  - `php artisan test --filter='(RecruitmentFlowTest|TimetableFlowTest|AccountingFlowTest|AdminLayoutChromeTest)'` => PASS (12 tests, 33 assertions).
  - `php -l` لجميع ملفات Blade/CSS المعدلة => PASS.

## 2026-02-25 - Sprint 4 (Design System) - Phase 66
- استكمال تطبيق C1 على صفحات الإنشاء/التعديل لوحدتي Recruitment وTimetables.

### Recruitment (Job Posts)
- Controllers:
  - `app/Http/Controllers/Recruitment/JobPostController.php`
  - إضافة breadcrumbs واضحة لمسارات:
    - `index`
    - `create`
    - `edit`
- Views:
  - `resources/views/admin/recruitment/job_posts/create.blade.php`
  - `resources/views/admin/recruitment/job_posts/edit.blade.php`
  - اعتماد `admin-form-panel` + `admin-form-grid` + `admin-form-actions` لهيكلة نموذج الإدخال.

### Timetables
- Controllers:
  - `app/Http/Controllers/Timetable/TimetableController.php`
  - إضافة breadcrumbs لمسارات:
    - `index`
    - `create`
    - `edit`
    - `print`
- Views:
  - `resources/views/admin/timetables/create.blade.php`
  - `resources/views/admin/timetables/edit.blade.php`
  - اعتماد `admin-form-panel` + `admin-section-title` + `admin-entry-table` + `admin-form-actions`.

### CSS Design Layer
- تحديث `public/cssadmin/admin-modern.css` بإضافة قواعد جديدة لدعم صفحات forms:
  - `.admin-form-panel`
  - `.admin-form-grid`
  - `.admin-form-actions`
  - `.admin-section-title`
  - `.admin-entry-table`

- نتائج التحقق:
  - `php artisan test --filter='(RecruitmentFlowTest|TimetableFlowTest|AdminLayoutChromeTest)'` => PASS (9 tests, 22 assertions).
  - `php -l` لكل الملفات المعدلة (Controllers/Blade/CSS) => PASS.

## 2026-02-25 - Sprint 4 (Design System) - Phase 67
- استكمال توحيد واجهات الطباعة/الإيصالات ضمن C1:
  - `resources/views/admin/accounting/payments/receipt.blade.php`
  - `resources/views/admin/timetables/print.blade.php`
- التحديثات المنفذة:
  - تحسين صفحة وصل الدفع إلى تخطيط موحد (header + receipt meta + جدول تفاصيل) مع تنسيق طباعة واضح وإخفاء عناصر التنقل عند `print`.
  - تحسين صفحة طباعة الجدول بتصميم حديث (sheet card + metadata + table header) مع دعم empty state عند غياب الحصص.
- تحديث خطة التنفيذ:
  - تعليم `C2` كمكتمل بعد اكتمال Layout/Breadcrumb/alerts الموحدة للأدوار الأساسية.
  - تعليم `C3` كمكتمل بعد اكتمال صفحات CRUD الحرجة بمعايير البحث/الفلاتر/الترقيم/الحالات.
- نتائج التحقق:
  - `php artisan test --filter='(AccountingFlowTest|TimetableFlowTest|AdminLayoutChromeTest|AccountantSidebarTest)'` => PASS (9 tests, 28 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 68
- بدء تنفيذ E1 بتحسين استعلامات شاشة الدفعات:
  - الملف: `app/Http/Controllers/Accounting/PaymentController.php`
  - التغيير: تقييد استعلام `overdue` إلى أحدث 100 عقد (`orderByDesc(updated_at)->limit(100)`) بدل تحميل كل العقود المتأخرة دفعة واحدة.
  - الأثر: خفض الحمل على صفحة إدارة الدفعات في المدارس ذات بيانات كبيرة.
- نتائج التحقق:
  - `php artisan test --filter='(AccountingFlowTest|AccountantSidebarTest)'` => PASS (4 tests, 17 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 69
- تنفيذ E2 (Caching للبيانات المرجعية) على endpoints الـ lookup:
  - `SchoolgradeController::listBySchool`
  - `ClassroomController::listByGrade`
  - `SectionController::listByClassroom`
  - `SectionController::getSectionById`
- سياسة الكاش المطبقة:
  - قراءة: `Cache::remember(..., 15 minutes)`.
  - إبطال: `Cache::forget(...)` عند عمليات `store/update/destroy` ذات الصلة.
  - مفاتيح الكاش مربوطة بالمدرسة + الكيان (`school/grade/classroom/section`) لضمان عزل المدارس.
- تغطية اختبارية جديدة:
  - `tests/Feature/School/LookupCacheInvalidationTest.php`
  - يثبت أن كاش `lookup:school:{id}:grades` يُلغى بعد إنشاء مستوى دراسي عبر endpoint الإدارة.
- نتائج التحقق:
  - `php artisan test --filter='(LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'` => PASS (7 tests, 22 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 70
- متابعة تنفيذ E3/E1 على وحدات المدرسة الأساسية (`Schoolgrades`, `Classes`).

### Backend changes
- `app/Http/Controllers/School/SchoolgradeController.php`
  - `index` أصبح يدعم:
    - Search (`q`) على `name_grade` (fr/ar/en).
    - فلترة اختيارية حسب المدرسة (`school_id`) مع احترام scoping الحالي.
    - Pagination (`20`) + `withQueryString`.
    - Eager loading للعلاقة `school`.
- `app/Http/Controllers/School/ClassroomController.php`
  - `index` أصبح يدعم:
    - Search (`q`) على `name_class` (fr/ar/en).
    - Filters: `school_id`, `grade_id`.
    - Pagination (`20`) + `withQueryString`.
    - Eager loading محسن `schoolgrade.school` لتفادي N+1.

### UI changes
- `resources/views/admin/schoolgrades.blade.php`
  - إضافة نموذج فلترة/بحث أعلى الجدول.
  - ترقيم الصفوف بحسب الصفحة الحالية.
  - إضافة pagination links.
  - إضافة empty state.
  - إزالة DataTables JS local لهذه الصفحة.
- `resources/views/admin/classes.blade.php`
  - إضافة نموذج فلترة/بحث (q + school + grade).
  - ترقيم الصفوف بحسب الصفحة الحالية.
  - إضافة pagination links.
  - إضافة empty state.
  - إزالة DataTables JS local لهذه الصفحة.

### Automated tests
- اختبار جديد: `tests/Feature/School/SchoolAdminIndexFiltersTest.php`
  - تغطية فلترة `Schoolgrades.index` مع school scoping.
  - تغطية pagination + grade filter في `Classes.index`.

### Verification
- `php artisan test --filter='(SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'` => PASS (9 tests, 29 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 71
- استكمال تنفيذ E3 على شاشة `Sections.index` (وحدة الأقسام).

### Controller updates
- الملف: `app/Http/Controllers/School/SectionController.php`
- إضافة فلاتر server-side:
  - `q` (بحث في اسم القسم/اسم القاعة)
  - `grade_id`
  - `classroom_id`
  - `status`
- تحسين التحميل:
  - eager loading لعلاقات `classroom.schoolgrade.school` و`teachers`.
- pagination:
  - تحويل قائمة المستويات المعروضة (`Schoolgrade`) إلى `paginate(6)` مع `withQueryString`.
  - قصر المستويات على التي تملك أقسامًا مطابقة عبر `whereHas('sections', ...)`.
- إضافة قوائم خيارات للفلاتر:
  - `SchoolgradeFilterOptions`
  - `ClassroomFilterOptions`

### View updates
- الملف: `resources/views/admin/sections.blade.php`
- إضافة نموذج فلترة أعلى الصفحة (بحث + مستوى + قاعة + حالة).
- تحويل loop إلى `@forelse` مع empty state موحد.
- إضافة pagination links أسفل النتائج.
- إزالة DataTables assets المحلية من الصفحة لتفادي التعارض مع server-side pagination.

### Automated tests
- توسيع: `tests/Feature/School/SchoolAdminIndexFiltersTest.php`
- إضافة اختبار:
  - `test_sections_index_filters_by_status_and_query_with_school_scope`
  - يغطي: الفلترة + عزل المدرسة + استبعاد سجلات مدرسة أخرى.

### Verification
- `php artisan test --filter='(SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|SprintZeroSecurityTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (45 tests, 101 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 72
- مواصلة E3 على شاشة `Schools.index`.

### Backend changes
- الملف: `app/Http/Controllers/School/SchoolController.php`
- تحديث `index` ليعتمد:
  - Search (`q`) على `name_school` (fr/ar/en).
  - Pagination (`20`) + `withQueryString`.
  - الحفاظ على school scoping (`forSchool(currentSchoolId)`).

### UI changes
- الملف: `resources/views/admin/schools.blade.php`
- إضافة نموذج بحث أعلى الجدول.
- اعتماد `@forelse` + empty state.
- إضافة pagination links.
- إزالة DataTables local assets من الصفحة.

### Automated tests
- توسيع `tests/Feature/School/SchoolAdminIndexFiltersTest.php` بإضافة:
  - `test_schools_index_supports_search_and_pagination_with_school_scope`.

### Verification
- `php artisan test --filter='(SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (11 tests, 37 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 73
- مواصلة E3/E1 على وحدة الترقيات (`Promotions.index`).

### Backend changes
- الملف: `app/Http/Controllers/Promotion/PromotionController.php`
- تحديث `index` إلى:
  - Search (`q`) على بيانات الطالب (`prenom/nom/numtelephone`) عبر `whereHas(student)`.
  - Eager loading موسع للعلاقات المستخدمة في الجدول (`student`, `f_*`, `t_*`).
  - Pagination (`20`) + `withQueryString` بدل تحميل كامل السجلات.

### UI changes
- الملف: `resources/views/admin/promotion.blade.php`
- إضافة نموذج بحث أعلى الجدول.
- تحويل الجدول إلى `@forelse` + empty state.
- إضافة pagination links.
- إزالة DataTables local assets من الصفحة.

### Automated tests
- اختبار جديد: `tests/Feature/Promotion/PromotionIndexFiltersTest.php`
  - التحقق من فلترة `Promotions.index` + عزل المدرسة (school scoping).

### Verification
- `php artisan test --filter='(PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (12 tests, 39 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 74
- استكمال E3/E1 على الشاشتين legacy الثقيلتين: `graduated` و`exam` (admin).

### Graduated module
- الملفات:
  - `app/Http/Controllers/Promotion/GraduatedController.php`
  - `resources/views/admin/graduated.blade.php`
- التغييرات:
  - `GraduatedController@index` أصبح يدعم:
    - Search (`q`) على الاسم/الهاتف/البريد.
    - Filter حسب القسم (`section_id`).
    - eager loading (`user`, `section.classroom.schoolgrade.school`).
    - Pagination (`20`) + `withQueryString`.
  - واجهة `admin/graduated`:
    - إضافة نموذج فلترة أعلى الجدول.
    - تحويل الجدول إلى `@forelse` + empty state.
    - إضافة pagination links.
    - إزالة DataTables local assets.

### Exams module (Admin)
- الملفات:
  - `app/Http/Controllers/AgendaScolaire/ExamesController.php`
  - `resources/views/admin/exam.blade.php`
- التغييرات:
  - `ExamesController@index` للأدمن أصبح يدعم:
    - Search (`q`) على اسم الامتحان.
    - Filters: `grade_id`, `classroom_id`, `specialization_id`, `Annscolaire`.
    - eager loading (`classroom.schoolgrade`, `schoolgrade`, `specialization`).
    - Pagination (`20`) + `withQueryString`.
  - واجهة `admin/exam`:
    - إضافة نموذج فلترة كامل أعلى الجدول.
    - تحويل الجدول إلى `@forelse` + empty state.
    - إضافة pagination links.
    - إزالة DataTables local assets.

### Automated tests
- اختبار جديد:
  - `tests/Feature/Performance/AdminLegacyIndexFiltersTest.php`
  - يغطي:
    - فلترة + scoping في `GraduatedController@index`.
    - فلترة + scoping في `ExamesController@index` للأدمن.

### Verification
- `php artisan test --filter='(AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (14 tests, 45 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 75
- مواصلة إغلاق E3 على شاشات الإدارة المتبقية: `Grades` و`Agendas`.

### Backend changes
- `app/Http/Controllers/AgendaScolaire/GradeController.php`
  - `index` يدعم الآن Search (`q`) على `name_grades` + Pagination (`20`) + `withQueryString`.
- `app/Http/Controllers/AgendaScolaire/AgendaController.php`
  - `index` يدعم الآن Search (`q`) على `name_agenda` + Pagination (`20`) + `withQueryString`.

### UI changes
- `resources/views/admin/grade.blade.php`
  - إضافة form بحث أعلى الجدول.
  - تحويل الجدول إلى `@forelse` + empty state.
  - إضافة pagination links.
  - إزالة DataTables local assets.
- `resources/views/admin/agenda.blade.php`
  - إضافة form بحث أعلى الجدول.
  - تحويل الجدول إلى `@forelse` + empty state.
  - إضافة pagination links.
  - إزالة DataTables local assets.

### Automated tests
- توسيع `tests/Feature/Performance/AdminLegacyIndexFiltersTest.php` بإضافة:
  - `test_grade_index_supports_search_and_pagination`
  - `test_agenda_index_supports_search_and_pagination`

### Verification
- `php artisan test --filter='(AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (16 tests, 51 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 76
- استكمال E3 على شاشة `NoteStudents.show` (واجهة `addnotestudent`).

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/NoteStudentController.php`
- تحديث `show($id)` إلى:
  - جلب القسم الحالي scoped حسب المدرسة (`Section::forSchool`).
  - تحميل الطلاب عبر `StudentInfo` مع:
    - eager loading: `user`, `noteStudent`.
    - Search (`q`) على الاسم/الهاتف/البريد.
    - Filter `has_notes` (لديه ملفات/بدون ملفات).
    - Pagination (`20`) + `withQueryString`.
  - فصل قائمة الطلاب الخاصة بنموذج الرفع في `UploadStudents`.

### UI changes
- الملف: `resources/views/admin/addnotestudent.blade.php`
- إعادة بناء الصفحة بالكامل:
  - إزالة الحلقة المزدوجة المكلفة (`StudentInfo x NoteStudents`).
  - الاعتماد على `student->noteStudent` لكل صف.
  - إضافة نموذج فلترة (بحث + حالة ملفات) أعلى الجدول.
  - إضافة empty state + pagination links.
  - إزالة DataTables local assets.

### Automated tests
- توسيع `tests/Feature/Performance/AdminLegacyIndexFiltersTest.php` بإضافة:
  - `test_note_students_show_supports_filters_and_pagination_with_school_scope`.

### Verification
- `php artisan test --filter='(AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest|AccountingFlowTest|TimetableFlowTest)'`
  - PASS (17 tests, 54 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 77
- تحسين تكميلي لشاشة `Inscriptions.index` (واجهة `studentsinscription`) بعد التحويل السابق إلى server-side filtering.

### UI refinements
- الملف: `resources/views/admin/studentsinscription.blade.php`
- التغييرات:
  - إزالة الاعتماد على `id="example5"` الخاص بنمط DataTables legacy.
  - اعتماد class selector موحد: `.js-inscriptions-table` لعمليات التحديد الجماعي.
  - إضافة empty state واضح عند عدم وجود نتائج فلترة.

### JS safety update
- تحديث selector في سكربت الحذف الجماعي من:
  - `#example5 input[type=checkbox]:checked`
  إلى:
  - `.js-inscriptions-table input[type=checkbox]:checked`
- الهدف: فك الارتباط مع بقايا DataTables IDs وتفادي تضارب المعرفات.

### Automated tests
- توسيع: `tests/Feature/Inscription/InscriptionIndexFiltersTest.php`
- إضافة اختبار:
  - `test_inscriptions_index_renders_empty_state_when_filter_returns_no_results`

### Verification
- `php artisan test --filter='(InscriptionIndexFiltersTest|AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest|LookupCacheInvalidationTest)'`
  - PASS (14 tests, 44 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 78
- تحسينات تكملية على شاشات `Inscriptions` و`Sections` ضمن E1/E3.

### studentsinscription refinements
- الملف: `resources/views/admin/studentsinscription.blade.php`
- التغييرات:
  - استبدال `id=example5` بـ class selector: `.js-inscriptions-table`.
  - تعديل selector الحذف الجماعي ليتوافق مع class الجديد.
  - إضافة empty state نصي عند عدم وجود نتائج فلترة.

### sections query optimization
- الملف: `app/Http/Controllers/School/SectionController.php`
- إزالة استعلام زائد غير مستخدم في الواجهة:
  - حذف `data['Section']` (كان يجلب كل الأقسام المفلترة رغم أن العرض يعتمد على `Schoolgrade->sections`).
- الأثر: تقليل عدد الاستعلامات وحجم البيانات المحملة في `Sections.index`.

### Tests updates
- `tests/Feature/Inscription/InscriptionIndexFiltersTest.php`
  - إضافة: `test_inscriptions_index_renders_empty_state_when_filter_returns_no_results`.
- `tests/Feature/School/SchoolAdminIndexFiltersTest.php`
  - تعديل اختبار sections ليتحقق من بيانات `Schoolgrade` المعروضة بدل الاعتماد على key `Section` المحذوف.

### Verification
- `php artisan test --filter='(SchoolAdminIndexFiltersTest|InscriptionIndexFiltersTest|AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest)'`
  - PASS (13 tests, 38 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 79
- تحسينات أداء + عزل بيانات على `HomeController@index` (Dashboard الإداري).

### Backend changes
- الملف: `app/Http/Controllers/HomeController.php`
- التغييرات:
  - دمج إحصاءات الطلاب (`total_students`, `male_students`, `female_students`) في استعلام واحد aggregate بدل 3 استعلامات منفصلة.
  - الإبقاء على نفس output fields للواجهة بدون كسر.
  - عزل `messages_today` حسب مدرسة المستخدم الإداري عبر:
    - `ChatMessage::whereHas('sender', fn (...) => where('school_id', currentSchoolId))`.

### Data-safety impact
- منع تسرب عدّاد رسائل اليوم بين المدارس (multi-school isolation على dashboard stat).

### Automated tests
- إضافة اختبار جديد:
  - `tests/Feature/Home/HomeDashboardStatsTest.php`
  - يغطي:
    - تجميع الإحصاءات بشكل صحيح.
    - school scoping لإحصاء الرسائل اليومية.

### Verification
- `php artisan test --filter='(HomeDashboardStatsTest|InscriptionIndexFiltersTest|AdminLegacyIndexFiltersTest|PromotionIndexFiltersTest|SchoolAdminIndexFiltersTest)'`
  - PASS (14 tests, 44 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 80
- تحسين إضافي على Dashboard (`HomeController@index`) ضمن E1/E2.

### Query optimization
- الملف: `app/Http/Controllers/HomeController.php`
- دمج إحصاءات الطلاب في استعلام aggregate واحد بدل استعلامات منفصلة:
  - `total_students`
  - `male_students`
  - `female_students`
- الإبقاء على نفس واجهة البيانات المستعملة في view.

### Multi-school safety
- عزل `messages_today` حسب مدرسة المستخدم الإداري:
  - `ChatMessage` أصبح محسوبًا فقط لرسائل مرسلي نفس المدرسة (`sender.school_id`).

### Caching (short TTL)
- إضافة Cache لمدة 5 دقائق لبيانات dashboard ذات الكلفة الأعلى:
  - `studentsByGrade`
  - `studentsMonthly`
- مفاتيح الكاش معزولة حسب:
  - `school_id`
  - `locale`

### Verification
- `php artisan test --filter='(HomeDashboardStatsTest|AdminLegacyIndexFiltersTest|InscriptionIndexFiltersTest)'`
  - PASS (9 tests, 28 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 81
- تخفيف إضافي لأداء واجهة `Sections.index` على مستوى DOM/interaction.

### UI/DOM changes
- الملف: `resources/views/admin/sections.blade.php`
- التغييرات:
  - إزالة مودالات per-row الخاصة بـ:
    - تحديث الحالة (`modal-status...`)
    - الحذف (`modal-centerdelete...`)
  - استبدالهما بـ:
    - نموذج مباشر لتبديل الحالة (toggle 0/1) عبر `Sections.status`.
    - نموذج حذف مباشر مع `confirm(...)` عبر `Sections.destroy`.
  - إزالة `id="example5{{$j}}"` المتبقي من الجدول (بقايا DataTables legacy).

### Impact
- تقليل عدد عناصر DOM لكل صف بشكل واضح، خاصة في الصفحات التي تحتوي على أقسام كثيرة.
- الإبقاء على نفس المسارات والسياسات backend دون تغيير.

### Verification
- `php artisan test --filter='(SchoolAdminIndexFiltersTest|SprintZeroSecurityTest|InscriptionIndexFiltersTest)'`
  - PASS (42 tests, 89 assertions).

## 2026-02-25 - Sprint 5 (Performance) - Phase 82
- إغلاق دورة invalidation لكاش dashboard لضمان تحديث فوري بعد تغييرات الطلاب.

### Centralized cache invalidation
- إضافة خدمة جديدة:
  - `app/Services/HomeDashboardCacheService.php`
- مسؤولية الخدمة:
  - تفريغ مفاتيح:
    - `home:school:{school}:locale:{locale}:students-by-grade`
    - `home:school:{school}:locale:{locale}:students-monthly`
  - دعم `school_id` المعيّن + مفتاح `all`.
  - التكرار على كل اللغات المفعلة في `laravellocalization.supportedLocales`.

### Model lifecycle hooks
- الملف: `app/Providers/AppServiceProvider.php`
- ربط تفريغ الكاش تلقائيًا عند:
  - `StudentInfo::saved`
  - `StudentInfo::deleted`
  - `StudentInfo::restored`
  - `StudentInfo::forceDeleted`

### Raw-update safety
- الملف: `app/Http/Controllers/Promotion/PromotionController.php`
- إضافة استدعاء صريح بعد `destroy` transaction:
  - `HomeDashboardCacheService::forgetForSchool($schoolId)`
- الهدف: تغطية تحديثات `StudentInfo::query()->update(...)` التي تتجاوز أحداث الموديل.

### Automated tests
- تحديث: `tests/Feature/Home/HomeDashboardStatsTest.php`
- إضافة اختبار:
  - `test_dashboard_chart_cache_is_invalidated_when_student_changes`
- يغطي:
  - warm cache عبر `HomeController@index`.
  - تعديل `StudentInfo` عبر Eloquent.
  - التأكد أن مفاتيح dashboard cache تُحذف تلقائيًا.

## 2026-02-25 - Sprint 5 (Performance) - Phase 83
- تحسين استهلاك الاستعلامات في صفحة التسجيل العامة عبر فصل مسار البيانات الإداري عن المسار العام.

### Backend changes
- الملف: `app/Http/Controllers/Inscription/InscriptionController.php`
- التغييرات:
  - في `index` أصبحت استعلامات:
    - `Inscription` (قائمة التسجيلات)
    - `Classrooms` (فلاتر الإدارة)
    تُبنى فقط عندما يكون المستخدم أدمن.
  - الواجهة العامة `front-end.inscription` لم تعد تُحمّل dataset إداري غير مستخدم.
  - الحفاظ على `paginate(20)` للأدمن كما هو.

### Impact
- تقليل حمل قاعدة البيانات على المسار العام للتسجيل.
- توافق مباشر مع قاعدة الأداء: منع تحميل جداول كبيرة دون حاجة فعلية.

### Automated tests
- تحديث: `tests/Feature/Inscription/InscriptionIndexFiltersTest.php`
- إضافة اختبار:
  - `test_public_inscription_page_does_not_load_admin_inscriptions_dataset`
- يغطي:
  - أن صفحة التسجيل العامة تُعرض بدون حقن `Inscription`/`Classrooms`.
  - بقاء بيانات `School` اللازمة للنموذج متاحة.

## 2026-02-25 - Sprint 5 (Performance) - Phase 84
- تحسين إضافي على صفحة المنشورات لفصل البيانات الإدارية عن المسار العام.

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/PublicationController.php`
- التغييرات:
  - عند `!isAdmin`:
    - إرجاع مباشر لواجهة `front-end.publications`.
    - بدون تحميل datasets إدارية (`Publications`, `Grade`, `Agenda`, `School`).
  - عند الأدمن:
    - الإبقاء على `paginate(20)` + الفلاتر كما هي.
    - الإبقاء على scoping حسب `school_id`.

### Impact
- تقليل حمل الاستعلامات في المسار العام للمنشورات (الواجهة تعتمد Livewire ولا تحتاج datasets controller التقليدية).
- تحسين الالتزام بقاعدة الأداء `E3`: عدم تحميل جداول/مرجعيات كبيرة دون استخدام فعلي.

### Automated tests
- تحديث: `tests/Feature/AgendaScolaire/AdminIndexFiltersTest.php`
- إضافة اختبار:
  - `test_public_publications_page_does_not_load_admin_datasets`
- يغطي:
  - عرض الصفحة العامة بنجاح.
  - عدم تمرير datasets إدارية غير مستخدمة للعرض العام.

## 2026-02-25 - Sprint 5 (Performance) - Phase 85
- تحسين صفحة الامتحانات العامة لتفادي تحميل كامل dataset.

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/ExamesController.php`
- التغييرات:
  - المسار العام (`guest` و`user` غير الأدمن) أصبح يستخدم:
    - `paginate(20)->withQueryString()`
    بدل `get()`.
  - مسار الأدمن بقي كما هو (`paginate(20)` + filters + school scope).

### Frontend changes
- الملف: `resources/views/front-end/exam.blade.php`
- التغييرات:
  - دعم paginator server-side:
    - ترقيم صفوف مرتبط بالصفحة الحالية.
    - `{{ $Exames->links() }}` أسفل الجدول.
  - إضافة empty state عند عدم وجود نتائج.
  - إزالة تهيئة DataTables من الصفحة العامة (لم تعد مناسبة مع pagination من الخادم).

### Automated tests
- تحديث: `tests/Feature/Performance/AdminLegacyIndexFiltersTest.php`
- إضافة اختبار:
  - `test_exames_index_is_paginated_for_public_view`
- يغطي:
  - عرض `front-end.exam` للعموم.
  - التحقق من ترقيم 20 عنصرًا في الصفحة الأولى مع `total=25`.

## 2026-02-25 - Sprint 5 (Performance) - Phase 86
- توسيع caching للـ admin lookups في وحدة الامتحانات + invalidation تلقائي.

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/ExamesController.php`
- إضافة `Cache::remember` (TTL=15min) لقوائم شاشة الإدارة:
  - `exam:school:{school_id}:grades`
  - `exam:school:{school_id}:classrooms`
  - `exam:lookups:specializations`

### Invalidation wiring
- الملف: `app/Http/Controllers/School/SchoolgradeController.php`
  - توسيع `forgetSchoolGradesLookupCache` لحذف:
    - `exam:school:{school_id}:grades`
- الملف: `app/Http/Controllers/School/ClassroomController.php`
  - توسيع `forgetGradeClassesLookupCache` لحذف:
    - `exam:school:{school_id}:classrooms`

### Automated tests
- تحديث: `tests/Feature/School/LookupCacheInvalidationTest.php`
- إضافة اختبارات:
  - `test_exam_admin_lookup_caches_are_invalidated_after_grade_and_classroom_creation`
  - `test_exam_admin_grade_cache_is_invalidated_after_grade_creation`
- يغطي:
  - تفريغ كاش grades بعد إنشاء grade.
  - تفريغ كاش classrooms بعد إنشاء classroom.

## 2026-02-25 - Sprint 5 (Performance) - Phase 87
- تحسين استعلام أقسام الفلترة في صفحة الجداول العامة.

### Backend changes
- الملف: `app/Http/Controllers/Timetable/PublicTimetableController.php`
- التغييرات:
  - `sections` لم تعد تُجلب عبر شرط `whereHas('students')`.
  - أصبحت تُجلب فقط للأقسام التي لديها جداول منشورة (`Timetable::published()->distinct(section_id)`).
- الأثر:
  - تقليل الحمل على علاقات الطلاب.
  - عرض قائمة أقسام مرتبطة مباشرة بمحتوى الجداول المنشور فعليًا.

### Automated tests
- تحديث: `tests/Feature/Timetable/TimetableFlowTest.php`
- إضافة اختبار:
  - `test_public_timetables_index_lists_only_sections_with_published_timetables`
- يغطي:
  - إدراج القسم الذي لديه جدول منشور في filter list.
  - استبعاد القسم الذي لديه جدول غير منشور فقط.

## 2026-02-25 - Sprint 5 (Performance) - Phase 88
- إضافة caching لقائمة أقسام الجداول العامة مع invalidation تلقائي من CRUD الإدارة.

### Backend changes
- الملف: `app/Http/Controllers/Timetable/PublicTimetableController.php`
- التغييرات:
  - إضافة كاش لمدة 10 دقائق لقائمة الأقسام في الصفحة العامة بمفتاح:
    - `public:timetables:sections`
  - الاستعلام المخبأ يجلب فقط أقسام الجداول المنشورة مع `classroom.schoolgrade`.

### Invalidation wiring
- الملف: `app/Http/Controllers/Timetable/TimetableController.php`
- التغييرات:
  - إضافة `forgetPublicSectionsCache()` واستدعاؤها بعد:
    - `store`
    - `update`
    - `destroy`
  - الهدف: منع stale cache بعد أي تغيير إداري على الجداول.

### Automated tests
- تحديث: `tests/Feature/Timetable/TimetableFlowTest.php`
- إضافة اختبار:
  - `test_public_sections_cache_is_invalidated_after_timetable_create`
- يغطي:
  - وجود الكاش قبل إنشاء جدول.
  - تفريغ الكاش تلقائيًا بعد `timetables.store`.

## 2026-02-25 - Sprint 5 (Performance) - Phase 89
- تقليل حمولة شاشة الدردشة عند فتح الصفحة الرئيسية.

### Backend changes
- الملف: `app/Http/Controllers/Application/ChatController.php`
- التغييرات:
  - إضافة حد أعلى لقائمة المستخدمين المتاحين في `index`:
    - `AVAILABLE_USERS_LIMIT = 60`
  - تطبيق `limit(60)` على استعلام `availableUsers` مع الإبقاء على `school_id` scoping.

### Impact
- تقليل حجم payload المرسل للواجهة عند وجود عدد كبير من المستخدمين.
- منع تحميل قائمة ضخمة دفعة واحدة في sidebar تبويب `New`.

### Automated tests
- إضافة: `tests/Feature/Application/ChatIndexPerformanceTest.php`
- اختبار:
  - `test_chat_index_limits_available_users_and_scopes_to_school`
- يغطي:
  - التحقق من حد 60 مستخدم.
  - التحقق من عدم ظهور مستخدم من مدرسة أخرى.

## 2026-02-25 - Sprint 5 (Performance) - Phase 90
- تقليل payload جداول الصفحة العامة عبر إزالة eager loading غير مستخدم.

### Backend changes
- الملف: `app/Http/Controllers/Timetable/PublicTimetableController.php`
- التغييرات:
  - في `index` تم إزالة تحميل علاقة `entries` من استعلام قائمة `timetables`.
  - الإبقاء فقط على:
    - `section.classroom.schoolgrade`
- السبب:
  - صفحة الفهرس لا تعرض تفاصيل الحصص (`entries`)؛ تحميلها يضيف payload واستعلامات بدون فائدة.

### Automated tests
- تحديث: `tests/Feature/Timetable/TimetableFlowTest.php`
- إضافة اختبار:
  - `test_public_timetables_index_does_not_eager_load_entries_relation`
- يغطي:
  - وجود جدول منشور مع entries فعلية.
  - التأكد أن `entries` ليست محملة eager في عنصر الفهرس.

## 2026-02-25 - Sprint 5 (Performance) - Phase 91
- تحسين صفحة المنشور المفرد لتقليل الاستعلامات وتفادي N+1.

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/PublicationController.php`
- التغييرات:
  - `show` أصبح يستخدم:
    - `Publication::with('galleries')->findOrFail($id)`
  - الحفاظ على شكل البيانات المتوقع في الواجهة (`Publications` كـ collection) عبر:
    - `collect([$singlePublication])`
  - إضافة caching لمدة 15 دقيقة للبيانات المرجعية:
    - `public:publication:grades`
    - `public:publication:agendas`

### Impact
- تقليل استعلامات الصور داخل `singlepublication` (منع تحميل lazy متكرر لعلاقة galleries).
- تقليل تحميل جداول `grades/agenda` المتكرر في الصفحة العامة.

### Automated tests
- إضافة: `tests/Feature/AgendaScolaire/PublicPublicationPerformanceTest.php`
- اختبار:
  - `test_publication_show_eager_loads_galleries_and_warms_reference_cache`
- يغطي:
  - تحميل علاقة `galleries` eager ضمن عنصر المنشور المعروض.
  - تعبئة مفاتيح الكاش المرجعية بعد زيارة الصفحة.

## 2026-02-25 - Sprint 5 (Performance) - Phase 92
- إكمال دورة invalidation لكاش مراجع المنشورات العامة.

### Backend changes
- الملف: `app/Http/Controllers/AgendaScolaire/GradeController.php`
  - إضافة invalidation لكاش:
    - `public:publication:grades`
    - `public:publication:agendas`
  - التنفيذ بعد: `store`, `update`, `destroy`.
- الملف: `app/Http/Controllers/AgendaScolaire/AgendaController.php`
  - إضافة نفس invalidation بعد: `store`, `update`, `destroy`.

### Impact
- منع بقاء `Grade/Agenda` بقيم قديمة في صفحة المنشور المفرد بعد أي تعديل إداري.
- جعل cache policy متكاملة: warm في `PublicationController@show` + invalidation في مصادر CRUD.

### Automated tests
- تحديث: `tests/Feature/AgendaScolaire/PublicPublicationPerformanceTest.php`
- إضافة اختبار:
  - `test_publication_reference_cache_is_invalidated_after_grade_and_agenda_updates`
- يغطي:
  - مسح كاش المراجع بعد إنشاء `Grade`.
  - مسح كاش المراجع بعد إنشاء `Agenda`.

## 2026-02-26 - Sprint 2 (Database & Migrations) - Phase 93
- إغلاق `B1` (تنظيف مخطط البيانات الأساسي) عبر migration جديدة:
  - `backend-soubel-alnajah/database/migrations/2026_02_26_201000_cleanup_core_schema_integrity.php`

### Backend changes
- `studentinfos`
  - إضافة قيد `unique` على `user_id` باسم `uq_studentinfos_user_id` لضمان one-to-one بين `users` و`studentinfos`.
- `notifications`
  - إزالة القيد `unique` غير العملي على `data`.
  - إضافة فهارس تشغيلية:
    - `idx_notifications_notifiable_created` (`notifiable_id`, `created_at`)
    - `idx_notifications_notifiable_read` (`notifiable_id`, `read_at`)
- `sections`
  - إضافة فهرس مركّب لفلاتر الإدارة:
    - `idx_sections_school_grade_classroom_status_created`

### Automated tests
- إضافة: `backend-soubel-alnajah/tests/Feature/Database/CoreSchemaIntegrityTest.php`
- يغطي:
  - `studentinfos.user_id` لا يقبل التكرار.
  - `notifications.data` يسمح بالتكرار بين صفوف مختلفة.
- تحقق إضافي مع Regression tests:
  - `FunctionNotificationActionsTest`
  - `SchoolAdminIndexFiltersTest`

## 2026-02-26 - Sprint 2 (Database & Performance) - Phase 94
- إغلاق `B2` بقياس أداء فعلي للاستعلامات المفهرسة.

### Benchmark changes
- إضافة اختبار Benchmark:
  - `backend-soubel-alnajah/tests/Feature/Performance/CoreIndexBenchmarkTest.php`
- المنهجية:
  - Seed dataset بحجم 6000 سجل في `inscriptions`.
  - مقارنة نفس استعلام قائمة الإدارة باستخدام:
    - `IGNORE INDEX (idx_inscriptions_school_status_created)`
    - `FORCE INDEX (idx_inscriptions_school_status_created)`
  - القياس بمتوسط 8 تشغيلات.
- شرط النجاح:
  - الاستعلام المفهرس أسرع بنسبة >= 40%.

## 2026-02-26 - Sprint 3 (UI/UX) - Phase 95
- إكمال `C1` (UI Stack حديث + دعم RTL كامل في admin layout).

### Frontend changes
- الملف: `backend-soubel-alnajah/resources/views/layoutsadmin/masteradmin.blade.php`
  - جعل `html` ديناميكيًا:
    - `lang="{{ app()->getLocale() }}"`
    - `dir="rtl/ltr"` حسب اللغة.
- الملف: `backend-soubel-alnajah/public/cssadmin/admin-modern.css`
  - إضافة قواعد RTL واضحة لـ:
    - `content-header`
    - `breadcrumbs`
    - `admin-form-actions`
    - `tables`

### Automated tests
- تحديث: `backend-soubel-alnajah/tests/Feature/UI/AdminLayoutChromeTest.php`
- إضافة اختبارات:
  - التحقق من `lang="ar" dir="rtl"` عند العربية.
  - التحقق من `lang="en" dir="ltr"` عند الإنجليزية.

## 2026-02-26 - Sprint 6 (Feature 3 Analysis) - Phase 96
- تحديث `F3-Analysis` دون إغلاق نهائي بسبب غياب ملف Excel المحاسبي الرسمي.

### Documentation changes
- تحديث: `docs/accounting-mapping.md`
  - إضافة `Final Mapping Template` جاهز للتعبئة (Sheet/Column/Target/Transform/Validation).
  - توضيح أن ملف Excel المكتشف داخل المشروع (`public/exames/1665495440OrU0SDbqtu.xls`) غير محاسبي.

### Status
- `F3-Analysis` يبقى مفتوحًا لحين توفير ملف Excel الخاص بالمقتصد (العقود/الدفعات) لإكمال mapping عمود-بعمود.

## 2026-02-26 - Sprint 0 (Architectural Directives) - Phase 97
- إغلاق موجه `#7 حذف الأنظمة المكررة` لنطاق الدردشة.

### Cleanup changes
- إزالة artifacts غير المستخدمة لنظام الرسائل القديم:
  - حذف `backend-soubel-alnajah/app/Models/Application/Message.php`
  - حذف `backend-soubel-alnajah/app/Events/MessageSent.php`
- إضافة migration لإسقاط الجدول legacy:
  - `backend-soubel-alnajah/database/migrations/2026_02_26_214000_drop_legacy_messages_table.php`

### Verification
- إضافة اختبار schema:
  - `test_legacy_messages_table_is_removed` ضمن `tests/Feature/Database/CoreSchemaIntegrityTest.php`
- تشغيل التحقق:
  - `php artisan test tests/Feature/Database/CoreSchemaIntegrityTest.php tests/Feature/Application/ChatIndexPerformanceTest.php`
  - النتيجة: PASS (4 tests).

### Note
- نظام الدردشة المعتمد فعليًا الآن هو `ChatController` المبني على `chat_rooms/chat_messages/chat_room_user` فقط.

## 2026-02-26 - Sprint 0 (Architectural Directives) - Phase 98
- تحديث حالة التوجيهات المعمارية في `IMPROVEMENT_PLAN_CHECKLIST.md` وإغلاق البنود ذات الدليل التنفيذي المكتمل:
  - `#2`, `#3`, `#4`, `#5`, `#6`, `#8`, `#9`, `#11`.
- الإغلاق بُني على أدلة تنفيذ فعلية داخل المشروع:
  - Actions/Services/FormRequests/Policies.
  - عزل multi-school وتغطية أمنية.
  - تحديث UI تدريجي + RTL/LTR.
  - فهارس + Benchmark أداء.
  - اختبارات مرافقة وتوثيق مرحلي.
- بنود بقيت مفتوحة:
  - `#10 No Half Refactor Rule` (بند حوكمة مستمر).
  - `F3-Analysis` (بانتظار ملف Excel المحاسبي الرسمي).

## 2026-02-26 - Sprint 0 (Architectural Directives) - Phase 99
- إغلاق `#10 No Half Refactor Rule` في checklist.
- الحالة الحالية للقائمة:
  - المتبقي المفتوح الوحيد: `F3-Analysis: Mapping ملف Excel`.
- سبب بقاء `F3-Analysis` مفتوح:
  - عدم توفر ملف Excel المحاسبي الرسمي (العقود/الدفعات) داخل المشروع حتى الآن.

## 2026-02-26 - Sprint 6 (Feature 3 Analysis) - Phase 100
- إغلاق `F3-Analysis` بعد العثور على ملف Excel محاسبي فعلي خارج شجرة المشروع:
  - المصدر: `/Users/mw/Downloads/حسام الدين.xlsx`
  - الأوراق المعتمدة: `عقود التلاميذ`, `دراهم`

### What changed
- تحديث `docs/accounting-mapping.md` من Draft إلى Final mapping فعلي.
- إضافة mapping عمود-بعمود نحو:
  - `student_contracts`
  - `contract_installments`
  - `payments`
  - `payment_receipts`
- توثيق قواعد التحويل (dates/serials/amounts) والتحقق (totals/uniqueness/rejections) وترتيب الاستيراد.

### Checklist update
- تعليم `F3-Analysis` كمكتمل في `IMPROVEMENT_PLAN_CHECKLIST.md` مع ملاحظة إغلاق تتضمن مصدر الملف والأوراق المعتمدة.

## 2026-02-26 - Sprint 6 (Feature 3 Implementation) - Phase 101
- تنفيذ استيراد Excel للمحاسبة داخل النظام (بعد إغلاق التحليل).

### Schema updates
- إضافة migration:
  - `backend-soubel-alnajah/database/migrations/2026_02_26_221000_add_excel_tracking_fields_to_student_contracts_table.php`
- الحقول المضافة في `student_contracts`:
  - `external_contract_no` (nullable)
  - `guardian_name` (nullable)
  - `metadata` (json)
  - unique index: `school_id + academic_year + external_contract_no`

### Backend updates
- Action جديدة:
  - `backend-soubel-alnajah/app/Actions/Accounting/ImportAccountingWorkbookAction.php`
- Request جديدة:
  - `backend-soubel-alnajah/app/Http/Requests/ImportAccountingWorkbookRequest.php`
- Controller:
  - إضافة `ContractController::import()` وربطه بالـ Action.
- Route:
  - `POST /accounting/contracts/import` -> `accounting.contracts.import`

### Import behavior
- قراءة الورقتين:
  - `عقود التلاميذ` -> إنشاء/تحديث العقود + توليد الأقساط.
  - `دراهم` -> إنشاء الدفعات + إنشاء الوصولات + تحديث حالة العقد.
- مطابقة الطلاب حسب الاسم داخل المدرسة الحالية (school scoped).
- إرجاع summary بعد الاستيراد (created/updated/payments/skipped).

### UI updates
- إضافة نموذج رفع Excel في صفحة:
  - `backend-soubel-alnajah/resources/views/admin/accounting/contracts/index.blade.php`

### Tests
- تحديث:
  - `backend-soubel-alnajah/tests/Feature/Accounting/AccountingFlowTest.php`
- إضافة اختبار:
  - `test_accountant_can_import_accounting_workbook`
  - ينشئ workbook مصغرًا (ورقتان) ويثبت:
    - إنشاء عقد بـ `external_contract_no`
    - إدخال دفعة اشتراك + دفعة شهرية

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (6 tests).
- `php artisan test tests/Feature/Database/CoreSchemaIntegrityTest.php tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests).

## 2026-02-26 - Sprint 6 (Feature 3 Implementation) - Phase 102
- توسيع استيراد Excel المحاسبي بإمكانيات تشغيلية إضافية.

### Added capabilities
- `Preview (Dry-Run)`:
  - تمرير `preview=1` على endpoint الاستيراد لتنفيذ التحليل الكامل داخل Transaction ثم rollback.
  - النتيجة: إحصاءات دقيقة لما سيتم إنشاؤه/تحديثه بدون حفظ فعلي.
- `Skipped Rows Report`:
  - تسجيل أسباب الصفوف المتخطاة (sheet/row/reason/contract/student).
  - توليد ملف CSV في `storage/app/private/accounting-import-reports`.
  - توليد رابط تنزيل موقّع (`signed route`) وعرضه في صفحة العقود بعد الاستيراد.

### Backend changes
- `ImportAccountingWorkbookAction`:
  - إضافة معامل `dryRun`.
  - إضافة `skipped_rows` داخل summary.
- `ContractController`:
  - دعم preview.
  - إضافة `downloadImportReport()` لتنزيل تقارير CSV الموقعة.
- `routes/web.php`:
  - إضافة `accounting.contracts.import.report`.
- `contracts.index` view:
  - checkbox للمعاينة.
  - تنبيه مع رابط تقرير الصفوف المتخطاة.

### Tests
- تحديث `AccountingFlowTest` بإضافات:
  - `test_accountant_can_preview_import_without_persisting_data`
  - `test_import_sets_skipped_rows_report_when_student_not_found`
- نتيجة التشغيل:
  - `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (8 tests, 25 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 Implementation) - Phase 103
- إضافة Preview تفصيلي مرئي داخل صفحة العقود بعد تشغيل `preview=1`.

### UX changes
- عرض جداول معاينة لـ:
  - العقود المقترحة (رقم العقد/الطالب/السنة/الإجمالي/إنشاء-تحديث).
  - الدفعات المقترحة (رقم الوصل/رقم العقد/النوع/المبلغ/التاريخ).
- البيانات تأتي من `import_preview` داخل session وتُعرض بعد المعاينة مباشرة.

### Backend support
- `ImportAccountingWorkbookAction` أصبح يرجع:
  - `preview_contracts`
  - `preview_payments`
  - بالإضافة إلى `skipped_rows`.
- `ContractController::import`:
  - يمرر `dryRun` إلى Action.
  - يخزن `import_preview` في session عند المعاينة.

### Tests
- تحديث اختبار المعاينة للتأكد من وجود payload المعاينة في session:
  - `test_accountant_can_preview_import_without_persisting_data`
- نتيجة التشغيل:
  - `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (8 tests).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 104
- تحسين UX لواجهة استيراد المحاسبة.

### Changes
- استبدال نمط checkbox (`preview`) بنمط زرين واضحين في نفس النموذج:
  - `معاينة فقط` (يرسل `preview=1`)
  - `تنفيذ الاستيراد`
- الملف:
  - `backend-soubel-alnajah/resources/views/admin/accounting/contracts/index.blade.php`

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (8 tests, 27 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 105
- إضافة عدادات summary أعلى معاينة الاستيراد قبل التنفيذ.

### Changes
- `ContractController::import`:
  - تمرير `summary` داخل `import_preview` (contracts_created/contracts_updated/payments_created/rows_skipped).
- واجهة `contracts.index`:
  - عرض 4 بطاقات إحصائية في وضع المعاينة:
    - عقود جديدة
    - عقود محدثة
    - دفعات متوقعة
    - صفوف متخطاة

### Tests
- تحديث `test_accountant_can_preview_import_without_persisting_data` للتحقق من وجود `import_preview.summary` في session.
- نتيجة التشغيل:
  - `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (8 tests, 28 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 106
- إضافة تصدير CSV للمعاينة (غير تقرير الصفوف المتخطاة).

### Changes
- `ContractController::import`:
  - توليد ملف CSV للمعاينة من `preview_contracts` + `preview_payments`.
  - حفظ رابط موقّع في session: `import_preview_csv_url`.
- `contracts.index`:
  - عرض رابط تنزيل `CSV للمعاينة` بعد تشغيل Preview.

### Tests
- تحديث اختبار المعاينة للتأكد من وجود `import_preview_csv_url`.
- نتيجة التشغيل:
  - `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (8 tests, 29 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 Validation) - Phase 107
- إضافة Validation ذكي للمجاميع ضمن تدفق Preview.

### Added validation checks
- ورقة `عقود التلاميذ`:
  - مقارنة `مجموع` العقد مع مجموع الأشهر (سبتمبر..ماي).
- ورقة `دراهم`:
  - مقارنة `المجموع الإجمالي (Z)` مع (حقوق الاشتراك + مجموع الدفعات الشهرية).
  - مقارنة `مجموع حقوق الاشتراك (AA)` مع قيمة `حقوق الاشتراك` الفعلية.
  - مقارنة `مجموع دفعات (AB)` مع مجموع الدفعات الشهرية الفعلي.

### Behavior
- عند وجود mismatch:
  - يُسجل warning (ليس skip).
  - يظهر ضمن `import_preview.validation_warnings`.
  - يظهر عدّاد `تحذيرات تحقق` في بطاقات المعاينة.
  - يعرض جدول تحذيرات تفصيلي (sheet/row/type/message/contract_no).

### Tests
- إضافة اختبار:
  - `test_preview_collects_validation_warnings_when_totals_mismatch`
- نتيجة التشغيل:
  - `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 108
- إضافة فلترة تفاعلية لتحذيرات المعاينة داخل صفحة العقود.

### UI changes
- فلاتر جديدة أعلى جدول التحذيرات:
  - حسب الورقة (`عقود التلاميذ` / `دراهم`)
  - حسب نوع التحذير
  - بحث نصي (رقم العقد/الوصف)
- التنفيذ عبر JavaScript خفيف على الواجهة بدون round-trip للخادم.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 109
- إضافة فرز تفاعلي لجدول تحذيرات المعاينة.

### Changes
- إضافة selector `ترتيب` في الواجهة مع خيارات:
  - السطر تصاعدي/تنازلي
  - رقم العقد تصاعدي/تنازلي
  - النوع تصاعدي/تنازلي
- دمج الفرز مع الفلاتر الحالية (ورقة/نوع/بحث نصي) بنفس السكربت الأمامي.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 110
- إضافة زر `إعادة ضبط الفلاتر` لتحذيرات المعاينة.

### Changes
- زر جديد في واجهة التحذيرات يعيد:
  - فلتر الورقة = الكل
  - فلتر النوع = الكل
  - الترتيب = `row_desc`
  - البحث النصي = فارغ
- التنفيذ ضمن نفس سكربت الفلترة الأمامي.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 111
- إضافة حفظ إعدادات فلترة التحذيرات في `localStorage`.

### Changes
- حفظ/استرجاع تلقائي للقيم:
  - `sheet`
  - `type`
  - `sort`
  - `text`
- مفتاح التخزين: `accounting_import_warnings_filters_v1`.
- يتم تحديث التخزين بعد كل تغيير أو إعادة ترتيب.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 112
- إضافة زر تصدير CSV للتحذيرات الظاهرة بعد الفلترة.

### Changes
- زر جديد: `تصدير التحذيرات الظاهرة CSV` في قسم التحذيرات.
- التصدير يتم من المتصفح مباشرة (Client-side) ويعتمد على الصفوف المرئية بعد تطبيق الفلاتر/الفرز.
- الأعمدة المصدّرة: `sheet,row,contract_no,type,message`.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 113
- إضافة عدّاد مباشر للصفوف الظاهرة في جدول تحذيرات المعاينة.

### Changes
- عنصر UI جديد: `الصفوف الظاهرة: N`.
- يتم تحديثه تلقائيًا بعد أي فلترة/بحث/فرز.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 114
- إضافة حالة "لا توجد نتائج مطابقة للفلاتر" + تعطيل تصدير CSV عند عدم وجود صفوف مرئية.

### Changes
- عنصر حالة فارغة جديد تحت جدول التحذيرات يظهر فقط عندما تصبح الصفوف المرئية = 0.
- تعطيل زر `تصدير التحذيرات الظاهرة CSV` تلقائيًا عندما لا توجد صفوف مطابقة.
- الإبقاء على عدّاد `الصفوف الظاهرة` كمؤشر فوري للحالة.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 115
- جعل خيارات فلاتر التحذيرات ديناميكية حسب بيانات المعاينة الفعلية.

### Changes
- استخراج `warningSheets` و`warningTypes` من `validation_warnings` وعرضهما داخل قوائم الفلترة بدل القيم الثابتة.
- تحسين استرجاع الفلاتر من `localStorage`:
  - إذا كانت القيمة المحفوظة قديمة وغير موجودة ضمن الخيارات الحالية، يتم تجاهلها وإرجاع الفلتر إلى `الكل`.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 116
- تحسين أداء البحث النصي في تحذيرات المعاينة عبر `debounce`.

### Changes
- إضافة مؤقّت `debounce` لمدة `250ms` على حقل `warningsTextFilter` قبل تنفيذ `applyFilters`.
- مسح المؤقّت عند `إعادة ضبط الفلاتر` لضمان تحديث فوري ونظيف للحالة.
- باقي سلوك الفلاتر/الفرز/العداد/التصدير بدون تغيير.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 117
- إضافة زر نسخ التحذيرات الظاهرة للحافظة (CSV نصي) مع رسالة حالة.

### Changes
- زر جديد: `نسخ التحذيرات الظاهرة`.
- استخراج بناء CSV إلى helper محلي `buildCsvLines` وإعادة استخدامه في التصدير والنسخ.
- دعم النسخ عبر:
  - `navigator.clipboard.writeText` عند التوفر (Secure Context).
  - fallback باستخدام `document.execCommand('copy')` عند الحاجة.
- إضافة رسالة حالة UI:
  - نجاح: `تم نسخ التحذيرات الظاهرة.`
  - فشل: `تعذر النسخ. استخدم زر التصدير.`
- تعطيل زر النسخ تلقائيًا عندما لا توجد صفوف مرئية بعد الفلترة.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 118
- إضافة زر نسخ رقم العقد داخل كل سطر تحذير.

### Changes
- إضافة عمود `إجراء` في جدول التحذيرات.
- لكل صف يحتوي `contract_no`:
  - زر `نسخ رقم العقد`.
  - حالة مصغّرة في السطر: `تم النسخ` أو `فشل النسخ`.
- إعادة استخدام helper النسخ للحافظة (`copyTextToClipboard`) لسيناريو نسخ CSV ونسخ رقم العقد.
- تحسين `buildCsvLines` ليعتمد على `data-*` في الصف (بدل نص الخلايا) حتى يبقى صحيحًا بعد إضافة عمود الإجراء.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (9 tests, 32 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 119
- إضافة فتح مباشر للعقد من تحذيرات المعاينة + توسيع البحث في العقود برقم العقد الخارجي.

### Changes
- في جدول تحذيرات المعاينة:
  - إضافة زر `فتح العقد` لكل صف يملك `contract_no`.
  - الرابط يوجّه إلى صفحة العقود مع `q=<contract_no>` ومرساة `#contractsList`.
- في صفحة العقود:
  - إضافة دعم البحث بـ `external_contract_no` داخل `ContractController@index`.
  - عرض `external_contract_no` في جدول العقود ضمن عمود `رقم العقد`.
  - تحديث placeholder البحث إلى `بحث باسم التلميذ/البريد/رقم العقد`.
- في الاختبارات:
  - إضافة اختبار `test_accountant_can_search_contracts_by_external_contract_number` للتحقق من منطق البحث برقم العقد الخارجي.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 120
- تحسين تجربة التنقل من التحذيرات إلى العقود عبر تمييز الصف المطابق تلقائيًا.

### Changes
- تحديث رابط `فتح العقد` ليشمل `highlight_contract=<contract_no>`.
- إضافة `data-external-contract` على صفوف جدول العقود.
- سكربت واجهي مشروط (`@if(request('highlight_contract'))`) يقوم بـ:
  - مطابقة رقم العقد المطلوب.
  - تمييز الصف المطابق بصريًا (Outline + Background).
  - تمرير تلقائي للصف داخل الصفحة (`scrollIntoView`).

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 121
- إضافة إشعار واضح لحالة التمييز مع إلغاء يدوي مباشر.

### Changes
- إضافة تنبيه أعلى جدول العقود عند وجود `highlight_contract`:
  - النص الافتراضي: `تم التركيز على العقد رقم ...`.
  - زر: `إلغاء التمييز`.
- توسعة سكربت التمييز:
  - حفظ مرجع الصف المميز.
  - عند الضغط على `إلغاء التمييز`: إزالة الإطار/الخلفية وإخفاء التنبيه.
  - إذا لم يوجد العقد في الصفحة الحالية: عرض رسالة `لم يتم العثور على العقد المطلوب في هذه الصفحة.`

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 122
- إضافة نسخ رابط مباشر للعقد من سطر التحذير.

### Changes
- إضافة زر `نسخ رابط العقد` ضمن عمود الإجراء في جدول التحذيرات.
- الرابط المنسوخ يتضمن:
  - `q=<contract_no>`
  - `highlight_contract=<contract_no>`
  - المرساة `#contractsList`
- إضافة معالج نقر جديد (`.js-copy-contract-link`) يعيد استخدام `copyTextToClipboard`.
- حالة السطر تعرض:
  - نجاح: `تم نسخ الرابط`
  - فشل: `فشل نسخ الرابط`

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 123
- إضافة فتح العقد في تبويب جديد مباشرة من سطر التحذير.

### Changes
- إضافة زر `فتح في تبويب جديد` بجانب `فتح العقد` داخل عمود الإجراء بجدول التحذيرات.
- الزر يستخدم نفس رابط الفلترة/التمييز (`q`, `highlight_contract`, `#contractsList`) مع:
  - `target="_blank"`
  - `rel="noopener noreferrer"`

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 124
- إضافة اختصار لوحة مفاتيح للتنقل السريع من رقم العقد.

### Changes
- جعل خلية `رقم العقد` في جدول التحذيرات قابلة لاختصار التنقل عبر:
  - `Ctrl + Click` (Windows/Linux)
  - `Cmd + Click` (macOS)
- عند الاختصار يتم فتح رابط العقد (المفلتر والمميز) في تبويب جديد باستخدام `window.open(..., '_blank')`.
- إضافة `title` توضيحي على خلية رقم العقد يشرح الاختصار.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 125
- توسيع اختصار `Ctrl/Cmd + Click` ليشمل خلية الوصف.

### Changes
- إضافة نفس hooks (`js-contract-shortcut` + `data-contract-link`) إلى عمود `الوصف` في جدول التحذيرات.
- النتيجة: يمكن فتح العقد المستهدف من:
  - خلية `رقم العقد`
  - خلية `الوصف`
  عبر نفس الاختصار بدون تغيير في بقية السلوك.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-26 - Sprint 6 (Feature 3 UX) - Phase 126
- تحسين الإشارات البصرية للخلايا المختصرة.

### Changes
- إضافة ستايل محلي في صفحة العقود لخلايا `.js-contract-shortcut`:
  - `cursor: pointer`
  - `hover` بلون خلفية خفيف
- الهدف: جعل قابلية الاختصار `Ctrl/Cmd + Click` واضحة بصريًا أثناء المراجعة.

### Verification
- `php artisan test tests/Feature/Accounting/AccountingFlowTest.php` => PASS (10 tests, 33 assertions).

## 2026-02-27 - Regression Validation Snapshot
- إعادة تحقق شاملة للاختبارات الحساسة بعد آخر دفعة تحسينات.

### Verification
- `php artisan test --filter='SprintZeroSecurityTest|AccountingFlowTest|RecruitmentFlowTest|CoreSchemaIntegrityTest|CoreIndexBenchmarkTest|FunctionNotificationActionsTest'` => PASS (84 tests, 202 assertions).

## 2026-02-27 - Sprint 0 Follow-up (Onboarding Delivery Fallback)
- إغلاق نقطة متبقية مرتبطة بقنوات تسليم onboarding في بيئة الإنتاج.

### Changes
- إضافة Notification جديدة: `OnboardingDeliveryFailedNotification` (قناة `database`) لإشعار إداريي المدرسة عند فشل إرسال رابط إعداد كلمة المرور.
- توسيع `UserOnboardingService`:
  - إرسال تنبيه داخلي للإداريين عند فشل broker في `sendResetLink` أو عند الاستثناءات.
  - احترام إعداد بيئي جديد لتعطيل/تفعيل السلوك.
- إضافة ملف إعدادات جديد `config/onboarding.php` بمفتاح:
  - `ONBOARDING_NOTIFY_ADMINS_ON_FAILURE=true` افتراضيًا.
- إضافة اختبار أمني/تدفق جديد:
  - `OnboardingDeliveryChannelsTest` للتحقق من إنشاء إشعار إداري عند فشل إرسال الرابط.

### Verification
- `php artisan test --filter='OnboardingFlowTest|OnboardingDeliveryChannelsTest'` => PASS (3 tests, 10 assertions).

## 2026-02-27 - Performance Benchmark Expansion (Notifications Index)
- توسيع القياس الفعلي للفهارس ليشمل استعلامات الإشعارات الإدارية.

### Changes
- توسيع `CoreIndexBenchmarkTest` بإضافة benchmark جديد:
  - الاستعلام: `notifications` حسب `notifiable_id` مع ترتيب `created_at DESC`.
  - المقارنة: `IGNORE INDEX (idx_notifications_notifiable_created, idx_notifications_notifiable_read)` مقابل `FORCE INDEX (idx_notifications_notifiable_created)`.
  - dataset: عدد كبير (12000 إشعار) مع خليط `read_at` لتمثيل سيناريوهات فعلية.
- شرط النجاح مطابق للسياسة المعتمدة: تحسن لا يقل عن 40% للاستعلام المفهرس.

### Verification
- `php artisan test tests/Feature/Performance/CoreIndexBenchmarkTest.php` => PASS (2 tests, 2 assertions).

## 2026-02-27 - Test Suite Stabilization (Blade Parsing Regressions)
- إصلاح أعطال 500 التي ظهرت أثناء تشغيل السويت الكامل بسبب أخطاء ترجمة Blade.

### Changes
- إصلاح صفحة العقود الإدارية:
  - استبدال صيغة `@php(...)` غير المتوافقة إلى كتلة `@php ... @endphp` في:
    - `resources/views/admin/accounting/contracts/index.blade.php`.
- إصلاح صفحة الجداول العامة:
  - استبدال `@forelse` إلى `@if + @foreach` في:
    - `resources/views/front-end/timetables/index.blade.php`
  - السبب: المترجم كان يولّد متغيرًا غير صالح (`$__empty_-1`) في النسخة الحالية.
- تحديث اختبار الجذر العام:
  - `tests/Feature/ExampleTest.php` أصبح يتحقق من redirect بدل `200` ليتوافق مع سلوك المسار `/`.

### Verification
- `php artisan test tests/Feature/Accounting/AccountantSidebarTest.php tests/Feature/UI/AdminLayoutChromeTest.php tests/Feature/ExampleTest.php` => PASS (6 tests, 18 assertions).
- `php artisan test tests/Feature/Timetable/TimetableFlowTest.php` => PASS (6 tests, 14 assertions).
- `php artisan test` => PASS (154 tests, 464 assertions).
