# IMPROVEMENT PLAN CHECKLIST - Sobol Najah

> مبدأ التنفيذ: مرحلي (Sprint-by-Sprint) مع تحديث هذه القائمة وتعليم البنود المنجزة ✅ + ملاحظة قصيرة تحت كل بند.

## Architectural Directives (إلزامية)
- [ ] 1) يسمح بإعادة بناء قاعدة البيانات بالكامل من الصفر
الهدف: الوصول لمخطط بيانات قوي قابل للتوسع 5–7 سنوات.
التوجيه: يجوز حذف migrations القديمة وإعادة تصميم الجداول والعلاقات بالكامل؛ نحافظ على المنطق الوظيفي لا شكل الجداول الحالية.

- [ ] 2) يسمح بإعادة تنظيم المشروع جذريًا
الهدف: هندسة منظمة بدل الترقيع.
التوجيه: يجوز تفكيك Controllers، إنشاء Services/Actions/Repositories، تطبيق Clean Layered Structure، حذف الكود المكرر أو غير المستخدم.

- [ ] 3) Multi-School Isolation إلزامي
الهدف: منع أي تسرب بيانات بين المدارس.
التوجيه: scoping إلزامي على مستوى الاستعلامات (global scope أو tenant resolver)، ولا يسمح بأي استعلام بدون scoping واضح.

- [ ] 4) Security First Policy
الهدف: فرض خط أمان ثابت لكل عمليات الكتابة.
التوجيه: أي عملية كتابة تمر عبر FormRequest + Policy Authorization + Validation واضح؛ يمنع state-changing عبر GET؛ إعادة تصميم رفع الملفات إلزامي.

- [ ] 5) Domain Separation
الهدف: فصل واضح بين المجالات.
التوجيه: تنظيم المجالات بحدود واضحة:
`Core Academic`، `Content`، `Communication`، `Recruitment`، `Accounting`.

- [ ] 6) UI Modernization تدريجي
الهدف: تحديث مستقر ومنظم للواجهات.
التوجيه: لا تحديث شامل دفعة واحدة؛ كل وحدة تُحدّث بعد إنهاء refactor الخاص بها؛ الالتزام بـ Design System موحد.

- [ ] 7) حذف الأنظمة المكررة
الهدف: منع Duplicate Systems.
التوجيه: اختيار نظام دردشة واحد حديث وحذف القديم، وعدم السماح ببقاء نظامين متوازيين لنفس الوظيفة.

- [ ] 8) Performance Baseline
الهدف: حد أدنى موحد للأداء في شاشات الإدارة.
التوجيه: كل شاشة إدارة تدعم Pagination + Search + Filters؛ يمنع `get()` على جداول كبيرة دون مبرر.

- [ ] 9) Code Quality Rules
الهدف: قابلية صيانة عالية.
التوجيه: Naming موحد، لا business logic داخل Blade، Controllers نحيفة، العمليات المعقدة في Action/Service، وتوثيق كل تغيير هيكلي في `IMPLEMENTATION_NOTES.md`.

- [ ] 10) No Half Refactor Rule
الهدف: منع التداخل بين القديم والجديد.
التوجيه: أي وحدة يتم لمسها تنظف بالكامل ضمن نطاقها، بدون ترك نصف refactor.

- [ ] 11) Test-First During Refactor
الهدف: منع الانكسارات الصامتة أثناء العمل المرحلي.
التوجيه: أي تعديل هيكلي/أمني أو إضافة ميزة يجب أن يصاحبه اختبار آلي جديد أو تحديث اختبار قائم ضمن نفس المرحلة، مع تسجيل نتيجة التشغيل في `IMPLEMENTATION_NOTES.md`.

## Sprint 0 - Stabilization Baseline
- [x] إصلاح الأساس قبل التوسع
الهدف: إزالة المخاطر الحرجة التي تعطل التطوير الآمن.
الملفات/المجالات: `README.md`, routes الحساسة, upload endpoints, auth defaults.
معايير القبول: لا يوجد merge conflicts، لا توجد عمليات كتابة عبر GET، تدفق رفع ملفات أساسي آمن ومثبت.
ملاحظات تنفيذ (2026-02-25):
- ✅ تحويل `notify/{id}` و`store/{id}` إلى `POST` بدل `GET`.
- ✅ إضافة `FormRequests` جديدة لعمليات حساسة: `StoreExameRequest`, `UpdateExameRequest`, `StoreNoteStudentRequest`, `NotifySchoolCertificateRequest`.
- ✅ إضافة `Policies` وربطها في `AuthServiceProvider` لـ `Exames` و`NoteStudent`.
- ✅ نقل رفع ملفات الامتحانات/النقاط إلى `local storage` بدل الحفظ المباشر في `public`.
- ✅ إزالة كلمات المرور الافتراضية الثابتة من تدفقات إنشاء حسابات الطالب/الولي/الأستاذ.
- ✅ إضافة `must_change_password` + middleware `force.password.change` لفرض تغيير كلمة المرور قبل الوصول للمسارات المحمية.
- ✅ تقوية حماية `Publications` (إنشاء/تعديل/حذف) عبر `auth + role:admin`.
- ✅ إضافة `PublicationPolicy` وربطها مع عزل المدرسة في التعديل/الحذف.
- ✅ تقييد عرض منشورات الإدارة على مدرسة المستخدم الإداري (`school_id scoping`).
- ✅ إضافة `StudentInfoPolicy` و`TeacherPolicy` وربطهما في `AuthServiceProvider`.
- ✅ تفعيل authorize checks داخل `StudentController` و`TeacherController`.
- ✅ إضافة `InscriptionPolicy` و`PromotionPolicy` وربطهما في `AuthServiceProvider`.
- ✅ تفعيل authorize checks داخل `InscriptionController` و`PromotionController`.
- ✅ إضافة اختبارات آلية أمنية `SprintZeroSecurityTest` تغطي: forced password change + upload validation + publication protection.
- ✅ توسيع الاختبارات لتغطية منع حذف أستاذ من مدرسة أخرى.
- ✅ توسيع الاختبارات لتغطية منع قبول تسجيل من مدرسة أخرى.
- ✅ إضافة onboarding service لإرسال reset/setup links للحسابات الجديدة (طالب/ولي/أستاذ).
- ✅ إضافة اختبارات `OnboardingFlowTest` للتحقق من إنشاء reset tokens وتفعيل `must_change_password`.
- ✅ تحويل `markasread/{id}` و`delete_all` إلى `POST` فقط لمنع state-changing عبر GET.
- ✅ تحصين `markasread` ليعمل فقط على إشعارات المستخدم الحالي (`notifiable_id = auth user`).
- ✅ تحصين `delete_all` عبر validation + scoping حسب `school_id` قبل الحذف الجماعي.
- ✅ توسيع `SprintZeroSecurityTest` لاختبار منع الوصول عبر GET لمسارات الكتابة الإدارية.
- ✅ إضافة `StorePromotionRequest` و`DestroyPromotionRequest` لعمليات الترقية الحساسة.
- ✅ تحصين `PromotionController`:
  - منع ترقية/تراجع خارج مدرسة الأدمن (school scoping).
  - إلغاء `truncate()` العام واستبداله بحذف مقيّد حسب المدرسة.
  - إصلاح تدفق الأخطاء في الترقية (إزالة مرجع `$e` غير المعرّف).
- ✅ توسيع اختبارات الأمان للتحقق من رفض ترقية بنفس القسم المصدر/الهدف.
- ✅ إضافة `ImportStudentsRequest` لرفع ملفات استيراد الطلاب (mimes + max size) بدل validation مباشر داخل Controller.
- ✅ تحصين تنزيل/عرض ملفات النقاط:
  - منع أسماء ملفات غير آمنة (path traversal / invalid patterns).
  - إضافة `nosniff` في استجابات تنزيل الملفات.
- ✅ فرض عزل المدرسة على `NoteStudentPolicy` (view/update/delete) بدل الاكتفاء بدور admin فقط.
- ✅ تقييد شاشة إدارة ملفات النقاط (`NoteStudentController@show`) بـ scoping حسب مدرسة المستخدم.
- ✅ توسيع اختبارات الأمان:
  - رفض استيراد ملف غير Excel.
  - رفض اسم ملف نقاط غير آمن.
  - منع أدمن مدرسة من تحميل ملف نقاط تابع لمدرسة أخرى.
- ✅ تقييد `notifications()` على مستوى المستخدم الحالي بدل جلب كل إشعارات النظام.
- ✅ اختبار أمني جديد: منع الأدمن من تعليم إشعار مستخدم آخر كمقروء.
- ✅ تحصين وحدة الغياب بعزل المدرسة:
  - `AbsenceController@index` لم يعد يعرض كل السجلات عالميًا، بل scoped حسب مدرسة الأدمن.
  - `storeOrUpdate` يتحقق أن الطالب تابع لنفس المدرسة قبل إنشاء/تحديث الغياب.
  - `getToday` يتحقق من ملكية الطالب قبل إرجاع بيانات اليوم.
- ✅ توسيع `AbsencePolicy` بإضافة `view` موحد بنفس قواعد العزل.
- ✅ اختبار أمني جديد: منع أدمن مدرسة من تحديث غياب طالب في مدرسة أخرى.
- ✅ تحصين `Schoolgrade` بالكامل:
  - إضافة `SchoolgradePolicy` وربطها في `AuthServiceProvider`.
  - تفعيل `authorize + scoping` داخل `SchoolgradeController` (index/store/update/destroy).
  - منع أي CRUD على مستوى دراسي خارج مدرسة الأدمن.
- ✅ إضافة `scopeForSchool` في `Schoolgrade` model لتوحيد عزل الاستعلامات.
- ✅ اختبار أمني جديد: منع حذف `Schoolgrade` تابع لمدرسة أخرى.
- ✅ تحصين وحدة `School`:
  - إضافة `SchoolPolicy` وربطها في `AuthServiceProvider`.
  - إضافة `scopeForSchool` في `School` model.
  - تفعيل `authorize + scoping` داخل `SchoolController` (index/store/update/destroy/test).
  - منع School-bound admin من إنشاء مدارس جديدة.
- ✅ اختبار أمني جديد: منع حذف مدرسة أخرى من قبل أدمن مقيّد بمدرسة.
- ✅ تحصين وحدة `Section`:
  - إضافة `SectionPolicy` وربطها في `AuthServiceProvider`.
  - تفعيل `authorize` داخل `SectionController` (index/store/edit/update/destroy).
  - إضافة middleware `auth + role:admin` على CRUD مع إبقاء endpoints العامة اللازمة.
- ✅ اختبار أمني جديد: منع حذف قسم تابع لمدرسة أخرى.
- ✅ تحصين وحدة `Classroom`:
  - إضافة `ClassroomPolicy` وربطها في `AuthServiceProvider`.
  - تفعيل `authorize` داخل `ClassroomController` (index/store/update/destroy).
  - إضافة middleware `auth + role:admin` على CRUD مع استثناء endpoint الجلب اللازم.
- ✅ اختبار أمني جديد: منع حذف قسم دراسي (Classroom) تابع لمدرسة أخرى.
- ✅ تحصين وحدتي `Agenda` و`Grade`:
  - إضافة `AgendaPolicy` و`GradePolicy` وربطهما في `AuthServiceProvider`.
  - تفعيل middleware `auth + role:admin` داخل `AgendaController` و`GradeController`.
  - تفعيل `authorize` داخل `index/store/update/destroy`.
- ✅ اختبار أمني جديد: منع غير الأدمن من إنشاء مستوى (`Grade`).
- ✅ تحصين وحدة `Graduated` (الطلاب المتخرجون/المحذوفون):
  - إضافة middleware `auth + role:admin`.
  - فرض `authorize + school scoping` في `index/store/update/destroy`.
  - إصلاح منطق الحذف النهائي: حذف المستخدم/الولي بناءً على العلاقات الفعلية بدل افتراض `user_id + 1`.
  - إصلاح مسار التخرج الجماعي (إزالة مرجع `$e` غير المعرّف + حذف promotions بالربط الصحيح `student_id`).
- ✅ اختبار أمني جديد: منع استرجاع طالب محذوف تابع لمدرسة أخرى.
- ✅ تطبيق `FormRequest` صريح لعمليات الكتابة في `GraduatedController`:
  - `StoreGraduatedRequest`
  - `RestoreGraduatedStudentRequest`
  - `DestroyGraduatedRequest`
- ✅ إيقاف أي كتابة عبر مسارات `edit` (GET) في وحدتي التسجيل/الأقسام:
  - `InscriptionController@edit` و`SectionController@edit` أصبحا `405` (لا كتابة عبر GET).
  - إنشاء مسارات `POST` صريحة:
    - `Inscriptions.status`
    - `Inscriptions.approve`
    - `Sections.status`
    - `Sections.teachers`
  - تحديث واجهات الإدارة لاستخدام المسارات الجديدة بدل `method_field('GET')`.
- ✅ إضافة FormRequests جديدة لتحديث الحالة/ربط المعلمين:
  - `ApproveInscriptionRequest`
  - `UpdateInscriptionStatusRequest`
  - `UpdateSectionStatusRequest`
  - `SyncSectionTeachersRequest`
- ✅ اختبار أمني جديد: التحقق أن `GET .../edit` لا يغير حالة التسجيل أو القسم.
- ✅ تشديد `InscriptionController` بميدلوير إداري صريح على العمليات الحساسة:
  - `show`, `approve`, `edit`, `update`, `destroy`, `updateStatus`.
- ✅ اختبار أمني جديد: منع الضيف من الوصول إلى endpoint قبول التسجيل `Inscriptions.approve`.
- ✅ تقوية الحذف الجماعي للتسجيلات (`delete_all`):
  - تحويل validation إلى `DeleteBulkInscriptionsRequest`.
  - تأكيد العزل المدرسي حتى عند تمرير IDs مختلطة من مدارس مختلفة.
- ✅ اختبار أمني جديد: التحقق أن `delete_all` يحذف فقط تسجيلات مدرسة الأدمن.
- ✅ تقوية عزل وحدة الامتحانات (`Exames`):
  - تحديث `ExamePolicy` لفرض عزل المدرسة في `view/update/delete`.
  - تقييد `ExamesController@index` لإظهار امتحانات مدرسة الأدمن فقط.
  - تقييد `store/update` للتحقق من أن `classroom_id` تابع لنفس مدرسة الأدمن، وضبط `grade_id` من نفس القسم.
- ✅ اختبار أمني جديد: منع حذف امتحان تابع لمدرسة أخرى.
- ✅ إغلاق وصول الضيوف إلى صفحات النماذج الإدارية (`create/edit`) في:
  - `Publications`
  - `Exames`
- ✅ اختبارات أمنية جديدة: منع الضيف من فتح صفحات `create` لوحدتي `Exames` و`Publications`.
- ✅ تقوية عزل `InscriptionController` في `update/destroy`:
  - جلب السجل أصبح scoped حسب `school_id` قبل أي تحديث/حذف.
  - استخدام `$inscription->update()` و`$inscription->delete()` على السجل المقيّد.
- ✅ اختبار أمني جديد: منع حذف تسجيل تابع لمدرسة أخرى.
- ✅ تنظيف تواقيع `destroy` التي كانت تستقبل `Request` بلا استخدام:
  - `NoteStudentController`
  - `SchoolgradeController`
  - `PublicationController`
  - `InscriptionController`
  - `GradeController`
  - `AgendaController`
  - `SchoolController`
- ✅ إغلاق مسار القبول القديم `/store/{id}`:
  - إضافة `school scoping` على جلب `Inscription`.
  - إضافة `authorize('approve', $inscription)` قبل تنفيذ القبول.
- ✅ اختبار أمني جديد: منع قبول تسجيل مدرسة أخرى عبر المسار القديم `/store/{id}`.
- ✅ تم إغلاق بنود الاستقرار الحرجة في Sprint 0 (Security/Permissions/State-changing/Scoping) مع تغطية اختبارات أمان موسعة.

- [x] توحيد بيئة التطوير والتشغيل
الهدف: ضمان قابلية تشغيل الفريق للمشروع بسهولة.
الملفات/المجالات: `.env.example`, `README`, scripts.
معايير القبول: مطور جديد يشغل المشروع محليًا خلال ≤ 20 دقيقة باتباع README.
ملاحظات تنفيذ (2026-02-25):
- ✅ إعادة بناء `backend-soubel-alnajah/README.md` بالكامل بخطوات تشغيل محلية واضحة.
- ✅ إزالة merge-conflict markers من README (`<<<<<<<`, `=======`, `>>>>>>>`).
- ✅ توثيق تشغيل الاختبارات وتسلسلها، وتشغيل queue/scheduler للإنتاج.

## A) Refactor & Cleanup
- [ ] A1. تفكيك Controllers الكبيرة إلى Actions/Services
الهدف: تقليل التعقيد ورفع قابلية الاختبار.
الملفات/المجالات: `InscriptionController`, `StudentController`, `FunctionController`, `PublicationController`.
معايير القبول: كل Controller يحتوي منطق تنسيق HTTP فقط، ومنطق الأعمال في Classes مستقلة.
ملاحظة حالية (2026-02-25): بدء التنفيذ باعتماد `ApproveInscriptionAction` داخل `InscriptionController` و`FunctionController` بدل منطق أعمال مكرر.
ملاحظة حالية (2026-02-25): إضافة `BuildInscriptionPayloadAction` لاستخراج بناء بيانات `Inscription` من `store/update` وتقليل تضخم الـ Controller.
ملاحظة حالية (2026-02-25): في `StudentController@store` تم استخراج بناء payload (الطالب/الولي) إلى `BuildStudentEnrollmentPayloadAction` لتقليل المنطق داخل الـ Controller.
ملاحظة حالية (2026-02-25): في `StudentController@update` تم استخراج منطق التحديث المترابط (student/user/parent/parent-user) إلى `UpdateStudentEnrollmentAction`.
ملاحظة حالية (2026-02-25): في `StudentController@destroy` تم استخراج منطق الحذف المترابط (student/user/parent/notifications cleanup) إلى `DeleteStudentEnrollmentAction` مع تغطية Feature tests لسيناريو وجود/عدم وجود أبناء آخرين.
ملاحظة حالية (2026-02-25): في `TeacherController` تم استخراج منطق `store/update/destroy` إلى Actions مستقلة (`CreateTeacherEnrollmentAction`, `UpdateTeacherEnrollmentAction`, `DeleteTeacherEnrollmentAction`) مع إبقاء الـ Controller طبقة HTTP فقط.
ملاحظة حالية (2026-02-25): في `InscriptionController` تم استخراج `updateStatus` و`destroy` إلى Actions (`UpdateInscriptionStatusAction`, `DeleteInscriptionAction`) مع اختبارات Feature لسير الحياة (status + delete).
ملاحظة حالية (2026-02-25): في `InscriptionController` تم استخراج `store/update` أيضًا إلى Actions (`CreateInscriptionAction`, `UpdateInscriptionAction`) مع إبقاء بناء payload في `BuildInscriptionPayloadAction`.
ملاحظة حالية (2026-02-25): في `FunctionController` تم استخراج منطق الإشعارات (`notify`, `markAsRead`) إلى Actions مخصصة (`SendSchoolCertificateNotificationAction`, `MarkUserNotificationAsReadAction`) مع اختبارات Feature للتأكد من السلوك.
ملاحظة حالية (2026-02-25): تم استكمال تفكيك `FunctionController` باستخراج بناء بيانات العرض (`BuildAgendaPageDataAction`, `BuildGalleryPageDataAction`) واستخراج قبول التسجيل legacy إلى `ApproveInscriptionByClassroomAction`.

- [x] A2. توحيد naming conventions
الهدف: توحيد أسماء methods/routes/classes وإزالة الأخطاء الإملائية.
الملفات/المجالات: controllers, requests, routes, views.
معايير القبول: لا توجد أسماء مبهمة/خاطئة مثل `Displqy*`، ووجود اصطلاح تسمية موحّد موثق.
ملاحظة حالية (2026-02-25): بدء التنفيذ بإضافة أسماء canonical لـ `displayNoteFromAdmin` و`markAsRead` مع إبقاء المسارات/الأسماء القديمة كـ backward-compat aliases.
ملاحظة حالية (2026-02-25): إضافة endpoints canonical جديدة لسلاسل الـ AJAX: `lookup/schools/{id}/grades` و`lookup/grades/{id}/classes` و`lookup/classes/{id}/sections` و`lookup/sections/{id}` مع إبقاء `getgrade/getclasse/getsection/getsection2` للتوافق.
ملاحظة حالية (2026-02-25): إضافة resource canonical `NoteStudents` وتحديث الواجهات إليه، مع إبقاء `Addnotestudents` legacy route للتوافق الخلفي.
ملاحظة حالية (2026-02-25): توحيد تسمية مسارات `FunctionController` عبر canonical routes: `public.agenda.show`, `public.gallery.index`, `admin.password.change.page` مع إبقاء `agenda/album/changepass` القديمة.
ملاحظة حالية (2026-02-25): بدء migration هيكلي لـ namespace `Functionn` إلى `Function` عبر canonical controller جديد وwrapper legacy لضمان عدم كسر أي استدعاء قديم.
ملاحظة حالية (2026-02-25): إغلاق legacy `getgrades` بإضافة method/route canonical (`listAgendaGrades` + `public.agenda.grades`) وربط الواجهة العامة إلى `public.gallery.index`.
ملاحظة حالية (2026-02-25): إضافة تغطية اختبارات لمسارات canonical العامة: `public.agenda.show`, `public.gallery.index`, `public.agenda.grades`.
ملاحظة حالية (2026-02-25): توحيد استدعاءات JavaScript إلى `route('lookup.*')` بدل بناء المسارات نصيًا، مع اختبار معماري يثبت وجود route names canonical.
ملاحظة حالية (2026-02-25): تنظيم أسماء المسارات legacy نفسها تحت `legacy.*` حيث أمكن، مع الإبقاء على أسماء التوافق التاريخية (`changepass`, `DisplqyNoteFromAdmin`) وتغطية Unit tests للتحقق من التسجيل والـ URI.
ملاحظة إغلاق (2026-02-25): تم اعتماد naming convention موحد (canonical + legacy aliases) على مستوى controllers/routes/views/translations مع تغطية اختبارات Unit + Feature.

- [ ] A3. إزالة الكود المكرر
الهدف: منع تضارب السلوك بين وحدات متشابهة.
الملفات/المجالات: flows قبول التسجيل، إنشاء حسابات المستخدمين.
معايير القبول: مصدر وحيد لكل use-case حساس.
ملاحظة حالية (2026-02-25): تم إزالة تكرار منطق قبول التسجيل/إنشاء الطالب والولي من مسارين مختلفين وربطه بمصدر واحد `ApproveInscriptionAction`.
ملاحظة حالية (2026-02-25): تم توحيد mapping حقول التسجيل في مصدر واحد `BuildInscriptionPayloadAction` بدل تكرار حقول كثيرة داخل `store/update`.
ملاحظة حالية (2026-02-25): تم توحيد mapping payload إنشاء الطالب/الولي في `BuildStudentEnrollmentPayloadAction` بدل بناء يدوي مباشر داخل `StudentController`.
ملاحظة حالية (2026-02-25): تم توحيد منطق update enrollment في `UpdateStudentEnrollmentAction` بدل closure كبيرة داخل `StudentController@update`.
ملاحظة حالية (2026-02-25): تم توحيد منطق destroy enrollment في `DeleteStudentEnrollmentAction` بدل منطق حذف مكرر داخل `StudentController@destroy`.
ملاحظة حالية (2026-02-25): تم توحيد منطق enrollment الخاص بالمعلمين (إنشاء/تحديث/حذف) داخل Actions بدل تكرار منطق DB transaction مباشرة داخل `TeacherController`.
ملاحظة حالية (2026-02-25): تم توحيد منطق تغيير حالة التسجيل وحذف التسجيل داخل Actions مخصصة في `InscriptionController` بدل التنفيذ المباشر داخل الـ Controller.
ملاحظة حالية (2026-02-25): تم توحيد منطق إنشاء/تحديث `Inscription` في Actions مخصصة مع اختبار Unit مباشر لطبقة الحفظ.
ملاحظة حالية (2026-02-25): تم نقل منطق الإشعارات الإدارية في `FunctionController` إلى Actions مستقلة للحفاظ على نحافة الـ Controller وتسهيل الاختبار.
ملاحظة حالية (2026-02-25): تم نقل منطق قبول التسجيل عبر المسار legacy (`/store/{id}`) إلى Action مخصصة مع اختبار نجاح end-to-end لضمان التوافق الخلفي.

## B) Database & Migrations
- [ ] B1. تنظيف مخطط البيانات الأساسي
الهدف: تحسين سلامة البيانات والعلاقات.
الملفات/المجالات: migrations الأساسية (schools/sections/studentinfos/inscriptions/notifications).
معايير القبول: كل FK/unique/index يخدم استعلامًا فعليًا، بدون تعارضات schema.

- [ ] B2. فهارس أداء
الهدف: تقليل وقت الاستعلام للشاشات الثقيلة.
الملفات/المجالات: جداول `studentinfos`, `inscriptions`, `absences`, `publications`, `chat_*`.
معايير القبول: تقليل زمن الاستعلامات الأساسية (قوائم الإدارة) بشكل ملموس (target ≥ 40%).
ملاحظة حالية (2026-02-25): تمت إضافة migration فهارس أداء أساسية على الجداول الثقيلة في `backend-soubel-alnajah/database/migrations/2026_02_25_100300_add_performance_indexes_to_core_tables.php`، ويتبقى قياس الأداء الفعلي قبل إغلاق البند نهائيًا.

- [x] B3. تصميم ميزات جديدة في DB (Pre-Implementation)
الهدف: وضع مخطط مستقر للميزات الثلاث.
الملفات/المجالات: migrations جديدة للـ recruitment + timetables + contracts/payments.
معايير القبول: ERD واضح + migrations قابلة للتنفيذ والrollback بدون أخطاء.
ملاحظة إغلاق (2026-02-25): تمت إضافة migrations جديدة للميزات الثلاث + فهارس وعلاقات أساسية في:
- `backend-soubel-alnajah/database/migrations/2026_02_25_100000_create_recruitment_tables.php`
- `backend-soubel-alnajah/database/migrations/2026_02_25_100100_create_timetables_tables.php`
- `backend-soubel-alnajah/database/migrations/2026_02_25_100200_create_accounting_tables.php`
وتم توثيق المخطط في `FEATURES_DB_BLUEPRINT.md` مع نجاح `migrate:fresh`.

## C) UI/UX Overhaul
- [ ] C1. اعتماد UI Stack حديث متدرج
الهدف: واجهة موحدة وقابلة للتوسع مع RTL.
الملفات/المجالات: `resources/views`, `resources/css/js`, Vite assets.
معايير القبول: Design system موحد (colors/typography/components) + دعم RTL كامل.
ملاحظة حالية (2026-02-25): تم بدء تصميم System موحد للإدارة بإضافة طبقة CSS مركزية:
- ملف جديد: `public/cssadmin/admin-modern.css`
- يحتوي Design Tokens (`:root`) للألوان/الحواف/الظلال + قواعد موحدة لـ:
  - page header + breadcrumbs
  - alerts
  - boxes
  - forms
  - tables
  - pagination
- تم ربطه داخل `layoutsadmin/head.blade.php` ليُطبق تدريجيًا على صفحات الإدارة المحدثة.
ملاحظة حالية (2026-02-25): تم توسيع التطبيق الفعلي للـ Design System على وحدات:
- Recruitment (إعلانات + طلبات): status badges + empty state موحد + إزالة error blocks المكررة.
- Timetables: status badge موحد + empty state موحد + إزالة error blocks المكررة.
- Accounting (contracts/payments): status badges + empty state موحد + إزالة error blocks المكررة.
- إضافة classes موحدة جديدة في `admin-modern.css`: `admin-status*`, `admin-empty-state`.
ملاحظة حالية (2026-02-25): تم تحسين اتساق واجهات الإدارة المتقدمة (Recruitment/Timetables/Accounting) بشكل إضافي:
- تطبيق status badges على الحالات الديناميكية (publish/review/accept/reject/paid/overdue...).
- توحيد empty states داخل الجداول والقوائم الثانوية.
- حذف عرض الأخطاء المكرر من صفحات الإدارة التي أصبحت تعتمد alerts الموحد من layout.
ملاحظة حالية (2026-02-25): تم تمديد التوحيد البصري إلى صفحات `create/edit` في Recruitment وTimetables:
- إضافة `breadcrumbs` كاملة لمسارات (index/create/edit) في Controllers.
- تطبيق classes نماذج موحدة: `admin-form-panel`, `admin-form-grid`, `admin-form-actions`, `admin-section-title`, `admin-entry-table`.
- تحسين ترتيب أزرار الإجراءات وتماسك أقسام الإدخال بدون تغيير منطق الأعمال.
ملاحظة حالية (2026-02-25): تم تحسين صفحات الطباعة لتكون متسقة بصريًا:
- تحديث `accounting/payments/receipt` إلى بطاقة إيصال منظمة مع جدول تفاصيل وتهيئة print-friendly.
- تحديث `timetables/print` بتنسيق حديث (header/metadata/table) مع دعم empty state قبل الطباعة.

- [x] C2. تحديث Layout & Navigation للإدارة
الهدف: تقليل التشتت وتحسين الوصول للعمليات اليومية.
الملفات/المجالات: `layoutsadmin/*`, sidebar/header/navigation.
معايير القبول: بنية تنقل واضحة حسب الدور، مع breadcrumbs ورسائل حالات موحدة.
ملاحظة حالية (2026-02-25): تم تطبيق تحسين role-based navigation للمحاسب (`accountant`) في `layoutsadmin/main_sidebar.blade.php`:
- عرض قائمة مبسطة خاصة بالمحاسبة فقط (العقود + الدفعات).
- إخفاء قوائم الإدارة الأكاديمية غير المصرح بها للمحاسب.
- إضافة اختبار `AccountantSidebarTest` للتحقق من ظهور تنقل المحاسبة فقط.
ملاحظة حالية (2026-02-25): تم توحيد `Page Chrome` للإدارة عبر:
- Partial موحد للـ breadcrumb: `layoutsadmin/partials/page_header.blade.php`.
- Partial موحد لرسائل الحالة والأخطاء: `layoutsadmin/partials/status_alerts.blade.php`.
- دمج الجزأين داخل `layoutsadmin/masteradmin.blade.php` ليطبّقا على جميع صفحات الإدارة.
- تمرير `breadcrumbs` من Controllers الأساسية (Students/Teachers/Inscriptions/Absences/Publications/Accounting).
- إزالة كتل عرض الأخطاء المكررة من الصفحات الإدارية التي تم تحديثها.
- إضافة اختبار `AdminLayoutChromeTest` للتحقق من ظهور breadcrumb والـ success alert الموحد.
ملاحظة إغلاق (2026-02-25): تم إكمال توحيد التنقل/الـ layout للأدوار الأساسية (admin/accountant) مع breadcrumbs ورسائل حالة مركزية على الصفحات الحرجة.

- [x] C3. تحديث صفحات CRUD الحرجة
الهدف: تجربة إدارة حديثة (بحث/فلاتر/pagination).
الملفات/المجالات: students, teachers, inscriptions, absences, publications.
معايير القبول: كل صفحة إدارة حرجة تدعم: Search + Filters + Pagination + Empty states + Success/Error alerts.
ملاحظة حالية (2026-02-25): تم تحديث شاشة إدارة التلاميذ `Students.index` إلى server-side Search/Filters/Pagination:
- بحث (`q`) بالاسم/البريد/الهاتف.
- فلترة حسب المستوى/القسم الدراسي/القسم.
- Pagination بحجم 20 مع `withQueryString`.
- إزالة الاعتماد على DataTable المحلي في هذه الشاشة.
- إضافة اختبار آلي `StudentIndexFiltersTest` لتأكيد school scoping + pagination + section filter.
ملاحظة حالية (2026-02-25): تم تحديث شاشة إدارة المعلمين `Teachers.index` إلى server-side Search/Filters/Pagination:
- بحث (`q`) بالاسم/البريد.
- فلترة حسب التخصص والجنس.
- Pagination بحجم 20 مع `withQueryString`.
- إزالة الاعتماد على DataTable المحلي في هذه الشاشة.
- إضافة اختبار آلي `TeacherIndexFiltersTest` لتأكيد school scoping + pagination + filters.
ملاحظة حالية (2026-02-25): تم تحديث شاشة إدارة التسجيلات `Inscriptions.index` إلى server-side Search/Filters/Pagination:
- بحث (`q`) بالاسم/البريد/الهاتف.
- فلترة حسب الحالة (`procec/accept/noaccept`) والقسم الدراسي.
- Pagination بحجم 20 مع `withQueryString`.
- إزالة DataTables المحلي الثقيل من الصفحة.
- إضافة اختبار آلي `InscriptionIndexFiltersTest` لتأكيد school scoping + pagination + status/classroom filters.
ملاحظة حالية (2026-02-25): تم تحديث شاشة الغيابات `Absences.index` إلى server-side Search/Filters/Pagination:
- بحث (`q`) بالاسم/البريد/الهاتف.
- فلترة حسب الفترة الزمنية والقسم.
- Pagination بحجم 20 مع `withQueryString`.
- إزالة DataTables المحلي من الصفحة.
- إضافة اختبار آلي ضمن `AdminIndexFiltersTest` لتأكيد school scoping + pagination.
ملاحظة حالية (2026-02-25): تم تحديث شاشة المنشورات `Publications.index` (لوحة الإدارة) إلى server-side Search/Filters/Pagination:
- بحث (`q`) على العنوان/المحتوى.
- فلترة حسب المستوى/الأجندة/الفترة الزمنية.
- Pagination بحجم 20 مع `withQueryString`.
- إزالة DataTables المحلي من الصفحة.
- إضافة اختبار آلي ضمن `AdminIndexFiltersTest` لتأكيد school scoping + pagination + query filter.
ملاحظة إغلاق (2026-02-25): تم تغطية جميع صفحات CRUD الحرجة المطلوبة (students/teachers/inscriptions/absences/publications) بمعايير Search + Filters + Pagination + Alerts + Empty states.

## D) Security & Permissions
- [ ] D1. تطبيق Policies + Gates
الهدف: منع الوصول غير المصرح على مستوى السجل.
الملفات/المجالات: `app/Policies`, `AuthServiceProvider`, Controllers.
معايير القبول: كل عملية حساسة تمر عبر policy check وتملك اختبارات وصول/منع.
ملاحظة حالية: تم البدء (Policy coverage مفعلة لـ `Exames` و`NoteStudent` و`Publication` و`StudentInfo` و`Teacher` و`Inscription` و`Promotion` مع اختبارات).

- [ ] D2. تقوية Validation وFormRequests
الهدف: توحيد التحقق لكل مدخلات النظام.
الملفات/المجالات: `app/Http/Requests` + endpoints التي تستخدم Request مباشر.
معايير القبول: لا توجد endpoints كتابة بدون validation صريح ومقنن.
ملاحظة حالية: تم البدء وتغطية `notify`, `exams`, `notes`.

- [ ] D3. Secure File Upload Pipeline
الهدف: إغلاق مخاطر رفع/تنزيل الملفات.
الملفات/المجالات: exams, notes, publications, recruitment CV.
معايير القبول: mimes+size enforced، التخزين خارج public، تنزيل عبر signed URLs/authorized controller.
ملاحظة حالية: تم تطبيق المرحلة الأولى على `Exames` و`NoteStudent` (storage local + mime/size + authorization checks).

- [ ] D4. إزالة كلمات المرور الافتراضية الثابتة
الهدف: رفع أمان الحسابات الجديدة.
الملفات/المجالات: enrollment service, teacher creation flow, auth flows.
معايير القبول: لا توجد hardcoded passwords، مع forced password reset/invite flow.
ملاحظة حالية: تمت إزالة hardcoded defaults + تفعيل `must_change_password` + إرسال reset/setup link تلقائيًا؛ يتبقى تحسين قنوات التسليم (بريد/رسائل) حسب بيئة الإنتاج.

## E) Performance
- [ ] E1. Eager Loading + Query Optimization
الهدف: إنهاء N+1 وتقليل عدد الاستعلامات.
الملفات/المجالات: dashboards, students list, inscriptions, chat rooms.
معايير القبول: تقارير debugbar تبين انخفاض query count في الصفحات المستهدفة.
ملاحظة حالية (2026-02-25): تحسين أولي على شاشة محاسبة الدفعات `PaymentController@index` عبر تقييد قائمة `overdue` إلى آخر 100 عقد بدل جلب كامل السجلات، لتقليل الحمل على الصفحة الكبيرة.
ملاحظة حالية (2026-02-25): في شاشة `Classes.index` تم تحسين eager loading إلى `schoolgrade.school` لتفادي N+1 عند عرض المدرسة/المستوى داخل الجدول.
ملاحظة حالية (2026-02-25): تحسين `HomeController@index` عبر تجميع إحصاءات الطلاب (total/male/female) في استعلام واحد، وعزل `messages_today` حسب مدرسة المستخدم الإداري.

- [ ] E2. Caching للبيانات المرجعية
الهدف: تسريع القوائم المتكررة.
الملفات/المجالات: schools/grades/sections/navigation shared data.
معايير القبول: cache policy واضحة + invalidation صحيح عند CRUD.
ملاحظة حالية (2026-02-25): تم بدء التطبيق على lookup endpoints:
- `lookup.schoolGrades`, `lookup.gradeClasses`, `lookup.classSections`, `lookup.sectionById`.
- إضافة `Cache::remember(..., 15 min)` على القراءة + `Cache::forget(...)` تلقائي عند `store/update/destroy` في وحدات `Schoolgrade/Classroom/Section`.
- إضافة اختبار `LookupCacheInvalidationTest` للتحقق من invalidation بعد إنشاء مستوى دراسي جديد.
ملاحظة حالية (2026-02-25): إضافة caching قصير (5 دقائق) لبيانات dashboard الثقيلة في `HomeController@index`:
- `studentsByGrade`
- `studentsMonthly`
مع مفاتيح كاش معزولة حسب المدرسة + اللغة.
ملاحظة حالية (2026-02-25): تم إضافة invalidation مركزي لكاش dashboard عبر `HomeDashboardCacheService`:
- تفريغ تلقائي عند أحداث `StudentInfo` (`saved/deleted/restored/forceDeleted`) من `AppServiceProvider`.
- تغطية مسار raw update في `PromotionController@destroy` باستدعاء invalidate صريح بعد transaction.
- اختبار آلي: `HomeDashboardStatsTest::test_dashboard_chart_cache_is_invalidated_when_student_changes`.
ملاحظة حالية (2026-02-25): تم إضافة caching لقوائم `Exames` الإدارية (`Schoolgrade`, `Classrooms`, `Specializations`) بمفاتيح مستقلة، مع invalidation عند CRUD في `SchoolgradeController` و`ClassroomController` واختبار `LookupCacheInvalidationTest::test_exam_admin_grade_cache_is_invalidated_after_grade_creation`.

- [ ] E3. Pagination افتراضي لكل القوائم الكبيرة
الهدف: تقليل استهلاك الذاكرة وزمن الاستجابة.
الملفات/المجالات: أغلب index actions.
معايير القبول: لا توجد list ثقيلة تعتمد `get()` بدون سبب.
ملاحظة حالية (2026-02-25): تم تحويل شاشتي `Schoolgrades.index` و`Classes.index` إلى Server-side Pagination + Search/Filters مع `withQueryString` وإزالة DataTables المحلية من الصفحتين.
ملاحظة حالية (2026-02-25): تمت إضافة اختبار آلي `SchoolAdminIndexFiltersTest` للتحقق من الفلترة والترقيم في الشاشتين.
ملاحظة حالية (2026-02-25): تم تحديث `Sections.index` إلى فلترة Server-side (`q`, `grade_id`, `classroom_id`, `status`) مع pagination على مجموعات المستويات المحمّلة، وإزالة DataTables المحلية، وإضافة اختبار يغطي الفلترة والعزل المدرسي.
ملاحظة حالية (2026-02-25): تم تحديث `Schools.index` إلى Search + Pagination (20) مع إزالة DataTables المحلية، وتوسعة `SchoolAdminIndexFiltersTest` لتغطية هذا السلوك.
ملاحظة حالية (2026-02-25): تم تحديث `Promotions.index` إلى Search + Pagination + eager loading للعلاقات (student/from/to) مع إزالة DataTables المحلية، وإضافة اختبار `PromotionIndexFiltersTest`.
ملاحظة حالية (2026-02-25): تم تحديث `graduated.index` و`Exames.index` (مسار الأدمن) إلى Search/Filters + Pagination + eager loading مع إزالة DataTables المحلية وإضافة اختبار `AdminLegacyIndexFiltersTest`.
ملاحظة حالية (2026-02-25): تم تحديث `Grades.index` و`Agendas.index` إلى Search + Pagination + Empty states مع إزالة DataTables المحلية وتوسيع `AdminLegacyIndexFiltersTest`.
ملاحظة حالية (2026-02-25): تم إعادة بناء شاشة `NoteStudents.show` (`addnotestudent`) على `StudentInfo + noteStudent` مع Search/Filter (`has_notes`) + Pagination وإزالة الحلقة المزدوجة وDataTables، وتوسيع `AdminLegacyIndexFiltersTest`.
ملاحظة حالية (2026-02-25): تحسين تكميلي على `studentsinscription`: إزالة اعتماد selector القديم المرتبط بـ `example5`، وإضافة empty-state واضح للفلترة الفارغة مع اختبار `InscriptionIndexFiltersTest`.
ملاحظة حالية (2026-02-25): تحسين E1 إضافي في `Sections.index` عبر إزالة استعلام `Section` الزائد غير المستخدم في الواجهة، مع تحديث الاختبار ليعتمد فقط على بيانات المجموعات الفعلية المعروضة.
ملاحظة حالية (2026-02-25): تخفيف حمل DOM في `sections.blade` عبر إزالة مودالات `status/delete` لكل صف وتحويلها إلى نماذج مباشرة (toggle status + delete confirm) مع الحفاظ على التغطية الاختبارية الأمنية.
ملاحظة حالية (2026-02-25): في `InscriptionController@index` تم إيقاف تحميل dataset الإداري (inscriptions/classrooms) بالكامل في الواجهة العامة `front-end.inscription` لأن الصفحة لا تستخدمه، مع الإبقاء على pagination للأدمن فقط وإضافة اختبار `test_public_inscription_page_does_not_load_admin_inscriptions_dataset`.
ملاحظة حالية (2026-02-25): في `PublicationController@index` تم فصل المسار العام تمامًا (guest/student/guardian) ليعرض `front-end.publications` بدون تحميل `Publications/Grade/Agenda/School`، مع إبقاء pagination/filters للأدمن فقط وإضافة اختبار `test_public_publications_page_does_not_load_admin_datasets`.
ملاحظة حالية (2026-02-25): في `ExamesController@index` تم استبدال `get()` في المسار العام بـ `paginate(20)`، مع تحديث `front-end.exam` لدعم ترقيم الخادم + empty state وإضافة اختبار `test_exames_index_is_paginated_for_public_view`.
ملاحظة حالية (2026-02-25): إضافة caching (15 دقيقة) لقوائم إدارة الامتحانات في `ExamesController@index`:
- `exam:school:{id}:grades`
- `exam:school:{id}:classrooms`
- `exam:lookups:specializations`
مع invalidation عند CRUD في `SchoolgradeController` و`ClassroomController`، وتغطية اختبار `LookupCacheInvalidationTest`.
ملاحظة حالية (2026-02-25): تحسين صفحة الجداول العامة `PublicTimetableController@index` ليتم تحميل `sections` فقط من الجداول المنشورة فعليًا (بدل شرط وجود طلاب بالقسم)، مع اختبار `TimetableFlowTest::test_public_timetables_index_lists_only_sections_with_published_timetables`.
ملاحظة حالية (2026-02-25): إضافة كاش لقائمة أقسام الجداول العامة (`public:timetables:sections`) في `PublicTimetableController@index` مع invalidation صريح بعد `timetables.store/update/destroy` داخل `TimetableController` واختبار `test_public_sections_cache_is_invalidated_after_timetable_create`.
ملاحظة حالية (2026-02-25): تحسين `ChatController@index` بوضع حد أقصى لقائمة `availableUsers` (60 مستخدم) مع استمرار عزل المدرسة، وإضافة اختبار `ChatIndexPerformanceTest::test_chat_index_limits_available_users_and_scopes_to_school`.
ملاحظة حالية (2026-02-25): تحسين إضافي في `PublicTimetableController@index` بإزالة eager loading لعلاقة `entries` من صفحة الفهرس (غير مستخدمة في الواجهة)، مع اختبار `test_public_timetables_index_does_not_eager_load_entries_relation`.
ملاحظة حالية (2026-02-25): تحسين `PublicationController@show`:
- eager loading لعلاقة `galleries` لتفادي N+1 في `singlepublication`.
- caching لبيانات `Grade` و`Agenda` (15 دقيقة) بمفاتيح `public:publication:grades` و`public:publication:agendas`.
- إضافة اختبار `PublicPublicationPerformanceTest::test_publication_show_eager_loads_galleries_and_warms_reference_cache`.
ملاحظة حالية (2026-02-25): تم ربط invalidation لكاش `public:publication:grades/agendas` داخل `GradeController` و`AgendaController` بعد `store/update/destroy`، مع اختبار `test_publication_reference_cache_is_invalidated_after_grade_and_agenda_updates`.

## F) Automated Tests + CI
- [x] F1. تأسيس طبقة اختبارات حرجة
الهدف: تثبيت الجودة قبل الميزات الجديدة.
الملفات/المجالات: tests/Feature + tests/Unit.
معايير القبول: تغطية سيناريوهات auth/roles/scoping/uploads الأساسية.
ملاحظة إغلاق (2026-02-25): تمت تغطية الطبقة الحرجة باختبارات Feature أمنية وتدفقية فعالة (`SprintZeroSecurityTest`, `OnboardingFlowTest`) مع عزل المدارس والصلاحيات ورفع الملفات.

- [x] F2. اختبارات الميزات الجديدة الثلاث
الهدف: ضمان عدم انكسار الوظائف المحاسبية/الجداول/التوظيف.
الملفات/المجالات: recruitment, timetable, contracts/payments tests.
معايير القبول: happy path + authorization + validation + edge cases لكل ميزة.
ملاحظة إغلاق (2026-02-25): تمت إضافة وتشغيل:
- `RecruitmentFlowTest`
- `TimetableFlowTest`
- `AccountingFlowTest`
وتغطي المسارات الأساسية + الأمان + صلاحيات الوصول + عزل المدرسة.

- [x] F3. CI بسيط
الهدف: تشغيل فحوصات تلقائية على كل push/PR.
الملفات/المجالات: `.github/workflows/ci.yml`.
معايير القبول: pipeline يمر على lint + tests + artisan config/cache checks.
ملاحظة إغلاق (2026-02-25): تمت إضافة workflow `Laravel CI` في `.github/workflows/ci.yml` لتشغيل:
- `composer install`
- فحص syntax لملفات PHP (`php -l`)
- `php artisan migrate --force` (SQLite testing DB)
- `php artisan test`
على كل `push` و`pull_request`.

## G) Documentation
- [ ] G1. README احترافي للتشغيل والنشر
الهدف: توثيق واضح للفريق والبيئة.
الملفات/المجالات: `README.md`.
معايير القبول: خطوات setup/local/prod/backups/queues/cron موثقة ومختبرة.

- [ ] G2. Architecture + Module Docs
الهدف: تسهيل onboarding والصيانة.
الملفات/المجالات: `docs/architecture.md`, `docs/modules/*.md`.
معايير القبول: كل module رئيسي موثق (domain model + flows + permissions).

- [ ] G3. Runbooks تشغيلية
الهدف: الاستجابة السريعة للأعطال.
الملفات/المجالات: `docs/runbooks/*.md`.
معايير القبول: runbook واضح لـ backup restore + incidents شائعة.

## خطة تنفيذ الميزات الجديدة (بعد Sprint 0)

### Feature 1 - إعلان توظيف + استمارة + CV
- [x] F1-DB: إنشاء `job_posts`, `job_applications`
الهدف: فصل بيانات التوظيف عن بقية النطاقات.
الملفات/المجالات: migrations + models.
معايير القبول: علاقات سليمة + حالة الطلب (`new/review/accepted/rejected`) + فهارس بحث.
ملاحظة إغلاق (2026-02-25): تم إنشاء الجداول مع العلاقات والفهارس ضمن migration `2026_02_25_100000_create_recruitment_tables.php`. طبقة Models/UI ستأتي في مراحل التنفيذ التالية.

- [x] F1-Admin/Public UI
الهدف: نشر إعلان والتقديم العام السريع.
الملفات/المجالات: routes/controllers/views (admin + public).
معايير القبول: نموذج بسيط بالحقول المطلوبة، عرض طلبات للأدمن مع فلترة/بحث/تحميل CV.
ملاحظة إغلاق (2026-02-25): تم تنفيذ واجهات ومُتحكمات كاملة للإعلانات والطلبات:
- Admin: `JobPosts` CRUD + صفحة `recruitment.applications.index` مع بحث/فلترة/pagination وتحديث حالة الطلب.
- Public: صفحات `public.jobs.index` و`public.jobs.show` مع نموذج ترشح مباشر.
- تم ربط روابط الواجهة في `layouts/main_header.blade.php` و`layouts/footer.blade.php` و`layoutsadmin/main_sidebar.blade.php`.

- [x] F1-Security
الهدف: حماية upload ومنع السبام.
الملفات/المجالات: FormRequests, middleware (throttle/honeypot), storage.
معايير القبول: قبول PDF/DOC/DOCX فقط وبحجم محدد، منع abuse واضح في logs.
ملاحظة إغلاق (2026-02-25): تم تطبيق الحماية عبر:
- `StoreJobApplicationRequest`: mimes/size + honeypot (`website`).
- `throttle:6,1` على endpoint التقديم العام.
- تخزين CV في `storage/app/private/recruitment/...` بدل public.
- تنزيل CV عبر controller authorized فقط + `nosniff`.
- اختبارات `RecruitmentFlowTest` تؤكد القبول الآمن ورفض spam وعزل الوصول.

### Feature 2 - Timetable Module احترافي
- [x] F2-DB: `timetables` + `timetable_entries`
الهدف: تمثيل شبكة أيام/حصص قابلة للتوسعة.
الملفات/المجالات: migrations/models.
معايير القبول: إدخال/تعديل/حذف entry بدون تضارب slots.
ملاحظة إغلاق (2026-02-25): تم إنشاء الجداول مع unique slot constraints وفهارس الإدارة ضمن migration `2026_02_25_100100_create_timetables_tables.php`.

- [x] F2-Admin Builder + Print
الهدف: بناء جدول احترافي مع طباعة.
الملفات/المجالات: admin UI + print CSS.
معايير القبول: صفحة طباعة مرتبة (شعار/عنوان/سنة) وتعمل A4.
ملاحظة إغلاق (2026-02-25): تم تنفيذ إدارة الجداول كاملة (`timetables` CRUD) مع إدخال حصص ديناميكي + صفحة طباعة مخصصة `admin/timetables/print.blade.php` عبر المسار `timetables.print`.

- [x] F2-Public Viewer
الهدف: وصول الطالب/الأستاذ لجدوله بسهولة.
الملفات/المجالات: public routes/views.
معايير القبول: اختيار مستوى/قسم وعرض فوري للجدول مع tabs/cards واضحة.
ملاحظة إغلاق (2026-02-25): تم إضافة صفحات عامة:
- `public.timetables.index` (فلترة حسب القسم)
- `public.timetables.show` (عرض تفاصيل الجدول)
مع ربط روابط الواجهة في header/footer.

### Feature 3 - Contracts & Payments (Accountant)
- [ ] F3-Analysis: Mapping ملف Excel
الهدف: تحويل منطق الملف إلى قواعد نظام دقيقة.
الملفات/المجالات: `docs/accounting-mapping.md`.
معايير القبول: كل أعمدة Excel mapped إلى حقول/قواعد أعمال مع حالات واضحة.
ملاحظة حالية (2026-02-25): تم إنشاء `docs/accounting-mapping.md` كنسخة Draft عملية مبنية على متطلبات الإدارة والجداول الجديدة، بانتظار ملف Excel الرسمي لإغلاق mapping النهائي سطرًا بسطر.

- [x] F3-DB: contracts/payment_plans/payments/receipts
الهدف: بناء نموذج محاسبي سليم.
الملفات/المجالات: migrations/models/indexes.
معايير القبول: حساب المتبقي والحالة (`paid/partial/overdue`) آلي وصحيح.
ملاحظة إغلاق (2026-02-25): تم إنشاء جداول `payment_plans`, `student_contracts`, `contract_installments`, `payments`, `payment_receipts` مع القيود والفهارس ضمن migration `2026_02_25_100200_create_accounting_tables.php`. منطق الحساب الآلي سيتم تنفيذه في طبقة الخدمات والـworkflows.

- [x] F3-Role Accountant
الهدف: صلاحيات خاصة ومحددة للمقتصد.
الملفات/المجالات: roles/permissions/policies/navigation.
معايير القبول: المقتصد يصل فقط للشاشات المسموح بها.
ملاحظة إغلاق (2026-02-25): تم تفعيل صلاحيات المحاسبة عبر `StudentContractPolicy` و`PaymentPolicy` + فحص role داخل Controllers، مع روابط مخصصة للمحاسبة في `main_sidebar` للمستخدمين (`admin/accountant`) فقط.

- [x] F3-Workflows + Reports
الهدف: إدخال دفعة في ثوانٍ وتقارير جاهزة.
الملفات/المجالات: controllers/views/reports.
معايير القبول: بحث سريع عن الطالب + تسجيل دفعة + تقرير متأخرين + تقرير فترة/مستوى/قسم.
ملاحظة إغلاق (2026-02-25): تم تنفيذ:
- إدارة العقود: إنشاء + تحديث + حساب المدفوع/المتبقي + حالات العقد.
- إدارة الدفعات: تسجيل دفعة + إنشاء وصل تلقائي + تحديث حالة العقد آليًا.
- تقارير عملية: فلترة الدفعات حسب الفترة والقسم + قائمة العقود المتأخرة.

- [x] F3-Optional Receipt Print
الهدف: طباعة وصل بسيط.
الملفات/المجالات: receipt blade + print style.
معايير القبول: وصل قابل للطباعة يتضمن رقم وصل/تاريخ/مبلغ/رصيد.
ملاحظة إغلاق (2026-02-25): تمت إضافة صفحة وصل دفع قابلة للطباعة عبر المسار `accounting.payments.receipt` والواجهة `admin/accounting/payments/receipt.blade.php`.

## قواعد الإنجاز لكل عنصر Checklist
- [ ] عند إنهاء أي عنصر: تعليمه ✅ وإضافة ملاحظة قصيرة تحته: "ماذا تغير + أين".
- [ ] منع التعديلات الضخمة غير المرحلية خارج ترتيب الـ Sprints.
- [ ] أي كسر توافق (BC) يجب توثيقه في `IMPLEMENTATION_NOTES.md`.
- [ ] ملاحظة إلزامية أثناء العمل: لكل تعديل وظيفي/أمني مهم يجب إضافة/تحديث اختبار آلي مناسب (Feature/Unit) في نفس المرحلة، ولا يؤجل ذلك لنهاية المشروع.
- [ ] مسموح أثناء التنفيذ استخدام الصلاحية الكاملة التقنية (تثبيت مكتبات/إزالة غير الضروري/إعادة تنظيم) طالما التغييرات موثقة في `IMPLEMENTATION_NOTES.md` ومتوافقة مع المعمارية المعتمدة.
