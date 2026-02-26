# Accounting Mapping (Draft Until Excel Is Provided)

> الحالة: Draft قابل للتحديث مباشرة بعد استلام ملف Excel الرسمي الخاص بالمقتصد.

## Source Status
- لم يتم العثور على ملف Excel المحاسبة داخل الشجرة الحالية للمشروع.
- الملف الوحيد المكتشف: `backend-soubel-alnajah/public/exames/1665495440OrU0SDbqtu.xls` (يخص الامتحانات، ليس العقود/الدفعات).

## Business Mapping (Current Working Baseline)

## 1) Contract Header -> `student_contracts`
- رقم/مرجع العقد (إن وجد في Excel) -> `student_contracts.id` أو حقل مرجعي إضافي لاحقًا.
- التلميذ -> `student_contracts.student_id`
- السنة الدراسية -> `student_contracts.academic_year`
- المبلغ الإجمالي -> `student_contracts.total_amount`
- نوع الدفع (سنوي/دفعات/أشهر) -> `student_contracts.plan_type`
- عدد الدفعات -> `student_contracts.installments_count`
- تاريخ بداية الخطة -> `student_contracts.starts_on`
- حالة العقد -> `student_contracts.status`

## 2) Plan/Installments -> `contract_installments`
- رقم الدفعة (1..N) -> `contract_installments.installment_no`
- تاريخ الاستحقاق -> `contract_installments.due_date`
- مبلغ الدفعة -> `contract_installments.amount`
- المدفوع من الدفعة -> `contract_installments.paid_amount`
- حالة الدفعة -> `contract_installments.status`
- ملاحظة الفترة (مثال: Jan/Trimester1) -> `contract_installments.label`

## 3) Payments/Receipts -> `payments` + `payment_receipts`
- رقم الوصل -> `payments.receipt_number`
- تاريخ الدفع -> `payments.paid_on`
- مبلغ الدفع -> `payments.amount`
- طريقة الدفع (نقدي/تحويل/...) -> `payments.payment_method`
- ملاحظات -> `payments.notes`
- ربط الدفعة بالعقد -> `payments.contract_id`
- ربط الدفعة بالقسط (اختياري) -> `payments.installment_id`
- نسخة وصل قابلة للطباعة -> `payment_receipts` (`receipt_code`, `payload`)

## 4) Derived Rules (Service Layer)
- المتبقي للعقد = `total_amount - SUM(payments.amount)`
- حالة العقد:
  - `paid`: المتبقي <= 0
  - `partial`: المتبقي > 0 مع وجود دفعات
  - `overdue`: المتبقي > 0 مع وجود أقساط متأخرة
  - `active`: عقد جارٍ بدون تأخر
- حالة القسط:
  - `paid` إذا `paid_amount >= amount`
  - `partial` إذا `0 < paid_amount < amount`
  - `overdue` إذا `due_date < today` و`paid_amount < amount`

## 5) Missing Inputs Required From Excel
- أسماء الأعمدة الفعلية في ملف المقتصد.
- هل رقم العقد موجود كقيمة أعمال مستقلة؟
- هل توجد حالات إضافية غير (`paid/partial/overdue`)؟
- هل توجد قواعد خصومات/إعفاءات/غرامات؟
- هل الوصل يجب أن يكون unique عالميًا أم داخل المدرسة فقط؟

## Next Update Path
- عند توفير ملف Excel:
  1. استخراج أسماء الأوراق (Sheets).
  2. mapping كل عمود إلى جدول/حقل.
  3. توثيق business rules الدقيقة (حسابات، استثناءات).
  4. تثبيت نسخة نهائية من هذا الملف قبل بناء workflows النهائية.
