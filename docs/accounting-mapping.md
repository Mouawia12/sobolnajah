# Accounting Mapping (Finalized From Excel)

> الحالة: Final (اعتمادًا على ملف Excel: `/Users/mw/Downloads/حسام الدين.xlsx`)

## 1) Source Workbook
- Workbook: `حسام الدين.xlsx`
- Sheets:
  - `عقود التلاميذ`
  - `دراهم`
- Join Key الأساسي بين الورقتين: `رقم العقد`

## 2) Canonical Domain Targets
- `student_contracts`
- `contract_installments`
- `payments`
- `payment_receipts`

## 3) Sheet Mapping

### A) Sheet: `عقود التلاميذ` (Contract master + monthly plan)
Header row المعتمدة: الصف 3.

| Excel Column | Arabic Header | Target Table | Target Field | Rule |
|---|---|---|---|---|
| C | تاريخ امضاء العقد | `student_contracts` | `signed_at` (أو metadata) | `d/m/Y` -> `Y-m-d` |
| D | رقم العقد | `student_contracts` | `external_contract_no` (أو `id` business key) | string، unique per school/year |
| F | السنة الدراسية | `student_contracts` | `academic_year` | normalize (`2025/2026` -> `2025-2026`) |
| G | اسم ولقب التلاميذ | `student_contracts` | link to `student_id` | via student name lookup + school scoping |
| H | اسم ولي | `student_contracts` | `guardian_name` (metadata) | optional |
| I | تاريخ ميلاد | `student_contracts` | `student_birth_date` (metadata) | Excel serial/date -> `Y-m-d` |
| J | رقم الهاتف | `student_contracts` | `guardian_phone` (metadata) | keep as string |
| K..S | سبتمبر..ماي | `contract_installments` | `amount` + `label` + `due_date` | create installment only when amount > 0 |
| T | مجموع | `student_contracts` | `total_amount` | numeric decimal |

#### Installment labels/due dates
- K: `September`
- L: `October`
- M: `November`
- N: `December`
- O: `January`
- P: `February`
- Q: `March`
- R: `April`
- S: `May`

`due_date` rule:
- derive from `academic_year` + month label (default day = `01`).

### B) Sheet: `دراهم` (Receipts + paid movements)
Header row المعتمدة: الصف 2.

| Excel Column | Arabic Header | Target Table | Target Field | Rule |
|---|---|---|---|---|
| B | رقم العقد | `payments` | `contract_id` (lookup by external_contract_no) | required |
| C | الاخوة | `student_contracts` | `sibling_group` (metadata) | optional |
| D | السنة الدراسية | `student_contracts` | `academic_year` | normalize |
| E | اسم ولقب التلاميذ | `payments` | integrity check only | must match contract student |
| F | رقم وصل اشتراك | `payment_receipts` | `receipt_code` (registration) | optional unique policy per school |
| G | حقوق الاشتراك | `payments` | registration payment amount | map as upfront payment when present |
| H,J,L,N,P,R,T,V,X | رقم الوصل (09..05) | `payment_receipts` | `receipt_code` | per monthly payment |
| I,K,M,O,Q,S,U,W | دفعة (09..05) | `payments` | `amount`,`paid_on`,`payment_type` | payment_type = monthly |
| Z | المجموع الإجمالي | computed check | `sum(registration + monthly)` | validation only |
| AA | مجموع حقوق الاشتراك | computed check | compare with G | validation only |
| AB | مجموع دفعات | computed check | compare with monthly sum | validation only |
| AC..AE | ملاحظ | metadata | `notes` | concat non-empty |

## 4) Business Rules (From Real File Behavior)
- العقد هو السجل المرجعي، والمدفوعات تُربط به عبر `رقم العقد`.
- يوجد دفعات شهرية قد تكون فارغة لبعض الأشهر، لذا الأقساط والمدفوعات تُنشأ فقط للقيم > 0.
- `T (مجموع)` في `عقود التلاميذ` هو إجمالي العقد المستهدف.
- `Z/AA/AB` في `دراهم` تستخدم للتحقق لا كمصدر نهائي للحفظ.
- أرقام الهواتف تحفظ كنص (`string`) للحفاظ على الأصفار البادئة.

## 5) Data Quality / Validation Rules
- رفض أي صف بدون `رقم العقد`.
- رفض duplication لنفس (`school_id`, `academic_year`, `external_contract_no`).
- عند تعذر مطابقة اسم الطالب، يسجل الصف في rejection report ولا يُحفظ جزئيًا.
- التحقق من توازن المجاميع:
  - `total_amount` ≈ sum(installments)
  - `monthly_paid_sum` ≈ `AB`
  - `registration_sum` ≈ `AA`

## 6) Import Execution Order
1. استيراد `عقود التلاميذ` -> إنشاء/تحديث `student_contracts`.
2. توليد `contract_installments` من K..S (القيم > 0).
3. استيراد `دراهم` -> إنشاء `payments` و`payment_receipts`.
4. تحديث الحالة الآلية للعقد (`paid/partial/overdue/active`) بعد كل دفعة.

## 7) Outstanding Clarifications (Non-blocking)
- هل `رقم وصل اشتراك` يجب أن يدخل ضمن نفس عداد `receipt_code` الشهري أم عداد مستقل؟
- سياسة uniqueness للوصل: global أم per-school.

تم اعتماد هذا mapping لإغلاق بند `F3-Analysis` لأنه مبني على ملف Excel فعلي وليس Draft افتراضي.
