# Features DB Blueprint (B3)

هذا الملف يوثق تصميم قاعدة البيانات للميزات الجديدة قبل التنفيذ التطبيقي.

## 1) Recruitment Domain

### Tables
- `job_posts`
  - مفتاح الربط: `school_id`
  - حالات: `draft | published | closed`
  - فهارس: `(school_id, status, published_at)` + unique `(school_id, slug)`
- `job_applications`
  - ربط مع: `job_post_id`, `school_id`
  - حقول CV: `cv_path`, `cv_original_name`, `cv_mime`, `cv_size`
  - حالة الطلب: `new | in_review | accepted | rejected` (تخزن كسلسلة)
  - فهارس: `(school_id, status, created_at)` و `(job_post_id, created_at)`

### Flow
- الأدمن ينشر `job_post`.
- الزائر يرسل `job_application` مع CV.
- الطلب يظهر في لوحة الإدارة مع حالة + فلترة.

## 2) Timetable Domain

### Tables
- `timetables`
  - ربط مع: `school_id`, `section_id`
  - حقل سنة: `academic_year`
  - unique: `(school_id, section_id, academic_year)` لمنع ازدواج جدول نفس السنة.
  - فهارس: `(school_id, is_published, academic_year)`
- `timetable_entries`
  - ربط مع: `timetable_id`, (اختياري) `teacher_id`
  - slot: `day_of_week` + `period_index`
  - unique: `(timetable_id, day_of_week, period_index)` لمنع تعارض حصتين بنفس الخانة.

### Flow
- الأدمن يبني جدول مستوى/قسم.
- نشر الجدول يفعّل عرضه في الصفحة العامة والطباعة.

## 3) Accounting Domain

### Tables
- `payment_plans`
  - خطط الدفع لكل مدرسة (سنوي/أقساط/مخصص).
  - unique: `(school_id, name)`.
- `student_contracts`
  - ربط مع: `student_id`, `school_id`, (اختياري) `payment_plan_id`.
  - unique: `(school_id, student_id, academic_year)` (عقد واحد لكل سنة).
  - يتضمن `total_amount`, `plan_type`, `status`.
- `contract_installments`
  - تفاصيل الدفعات المجدولة للعقد.
  - unique: `(contract_id, installment_no)`.
- `payments`
  - كل دفعة فعلية مع `receipt_number`.
  - unique: `(school_id, receipt_number)`.
  - ربط اختياري مع `installment_id` للدفع الجزئي.
- `payment_receipts`
  - وثيقة وصل منفصلة للطباعة/الأرشفة.
  - unique على `payment_id` و `(school_id, receipt_code)`.

### Flow
- إنشاء عقد للتلميذ.
- توليد/إدخال أقساط (إن وجدت).
- تسجيل دفعات وربطها بوصل.
- حساب حالة العقد (draft/active/partial/paid/overdue) في طبقة الخدمة.

## Multi-School Isolation
- كل الجداول الجديدة تحمل `school_id` (عدا الجداول التابعة مباشرة لعقد/جدول يمكن استنتاج المدرسة منه).
- كل الاستعلامات التشغيلية يجب أن تمر عبر scoping حسب مدرسة المستخدم.

## Index Strategy
- تم إضافة فهارس مركبة للسيناريوهات الإدارية المتوقعة:
  - البحث بالحالة + التاريخ.
  - التقارير حسب المدرسة/الفترة.
  - منع التكرار في السجلات الحساسة (slug, receipt_number, contract per year).

## Notes
- هذا التصميم مقصود كـ **Pre-Implementation Schema** ضمن `B3`.
- التنفيذ الوظيفي (Controllers/Services/UI/Policies/Reports) يبدأ في Sprintات الميزات.
