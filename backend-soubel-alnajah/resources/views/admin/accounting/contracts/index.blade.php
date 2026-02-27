@extends('layoutsadmin.masteradmin')
@section('titlea', trans('accounting.contracts'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <style>
            #warningsTable .js-contract-shortcut {
                cursor: pointer;
            }
            #warningsTable .js-contract-shortcut:hover {
                background-color: #f4f9ff;
            }
        </style>
        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">استيراد Excel (العقود + الدفعات)</h4>
            </div>
            <div class="box-body">
                @if(session('import_report_url'))
                    <div class="alert alert-warning">
                        توجد صفوف متخطاة أثناء آخر عملية. 
                        <a href="{{ session('import_report_url') }}" target="_blank">تحميل تقرير الصفوف المتخطاة (CSV)</a>
                    </div>
                @endif
                @if(session('import_preview'))
                    @php
                        $preview = session('import_preview');
                        $previewSummary = $preview['summary'] ?? [];
                        $warnings = $preview['validation_warnings'] ?? [];
                        $warningSheets = collect($warnings)->pluck('sheet')->filter()->unique()->values();
                        $warningTypes = collect($warnings)->pluck('type')->filter()->unique()->values();
                    @endphp
                    <div class="alert alert-info">
                        تمت المعاينة بدون حفظ. تم استخراج {{ count($preview['contracts'] ?? []) }} عقد و{{ count($preview['payments'] ?? []) }} دفعة (عينة عرض).
                    </div>
                    @if(session('import_preview_csv_url'))
                        <div class="alert alert-secondary">
                            <a href="{{ session('import_preview_csv_url') }}" target="_blank">تحميل CSV للمعاينة (عقود + دفعات)</a>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="admin-form-panel text-center">
                                <div class="admin-section-title">عقود جديدة</div>
                                <div class="h4 mb-0">{{ $previewSummary['contracts_created'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="admin-form-panel text-center">
                                <div class="admin-section-title">عقود محدثة</div>
                                <div class="h4 mb-0">{{ $previewSummary['contracts_updated'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="admin-form-panel text-center">
                                <div class="admin-section-title">دفعات متوقعة</div>
                                <div class="h4 mb-0">{{ $previewSummary['payments_created'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="admin-form-panel text-center">
                                <div class="admin-section-title">صفوف متخطاة</div>
                                <div class="h4 mb-0">{{ $previewSummary['rows_skipped'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mt-2">
                            <div class="admin-form-panel text-center">
                                <div class="admin-section-title">تحذيرات تحقق</div>
                                <div class="h4 mb-0">{{ $previewSummary['warnings_count'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>رقم العقد</th>
                                    <th>الطالب</th>
                                    <th>السنة</th>
                                    <th>الإجمالي</th>
                                    <th>العملية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($preview['contracts'] ?? []) as $item)
                                    <tr>
                                        <td>{{ $item['contract_no'] ?? '' }}</td>
                                        <td>{{ $item['student_name'] ?? '' }}</td>
                                        <td>{{ $item['academic_year'] ?? '' }}</td>
                                        <td>{{ number_format((float) ($item['total_amount'] ?? 0), 2) }}</td>
                                        <td>{{ !empty($item['is_new']) ? 'إنشاء' : 'تحديث' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="admin-empty-state">لا توجد عقود في عينة المعاينة.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mb-2">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>رقم الوصل</th>
                                    <th>رقم العقد</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($preview['payments'] ?? []) as $item)
                                    <tr>
                                        <td>{{ $item['receipt_number'] ?? '' }}</td>
                                        <td>{{ $item['contract_no'] ?? '' }}</td>
                                        <td>{{ $item['type'] ?? '' }}</td>
                                        <td>{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                                        <td>{{ $item['paid_on'] ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="admin-empty-state">لا توجد دفعات في عينة المعاينة.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(count($warnings))
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label class="form-label">فلترة حسب الورقة</label>
                                <select id="warningsSheetFilter" class="form-select form-select-sm">
                                    <option value="">الكل</option>
                                    @foreach($warningSheets as $sheet)
                                        <option value="{{ $sheet }}">{{ $sheet }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">فلترة حسب النوع</label>
                                <select id="warningsTypeFilter" class="form-select form-select-sm">
                                    <option value="">الكل</option>
                                    @foreach($warningTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ترتيب</label>
                                <select id="warningsSort" class="form-select form-select-sm">
                                    <option value="row_desc">السطر: تنازلي</option>
                                    <option value="row_asc">السطر: تصاعدي</option>
                                    <option value="contract_asc">رقم العقد: تصاعدي</option>
                                    <option value="contract_desc">رقم العقد: تنازلي</option>
                                    <option value="type_asc">النوع: تصاعدي</option>
                                    <option value="type_desc">النوع: تنازلي</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">بحث نصي (رقم عقد/وصف)</label>
                                <input id="warningsTextFilter" type="text" class="form-control form-control-sm" placeholder="اكتب للبحث...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="warningsClearFilters" class="btn btn-outline-secondary btn-sm">إعادة ضبط الفلاتر</button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="warningsExportVisibleCsv" class="btn btn-outline-primary btn-sm">تصدير التحذيرات الظاهرة CSV</button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="warningsCopyVisibleCsv" class="btn btn-outline-success btn-sm">نسخ التحذيرات الظاهرة</button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <span class="admin-empty-state" id="warningsVisibleCounter">الصفوف الظاهرة: 0</span>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <span class="admin-empty-state" id="warningsCopyStatus" aria-live="polite"></span>
                            </div>
                        </div>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered text-center" id="warningsTable">
                                <thead>
                                <tr>
                                    <th>الورقة</th>
                                    <th>السطر</th>
                                    <th>رقم العقد</th>
                                    <th>نوع التحذير</th>
                                    <th>الوصف</th>
                                    <th>إجراء</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($warnings as $warning)
                                    <tr
                                        data-sheet="{{ $warning['sheet'] ?? '' }}"
                                        data-type="{{ $warning['type'] ?? '' }}"
                                        data-row="{{ (int) ($warning['row'] ?? 0) }}"
                                        data-contract="{{ $warning['contract_no'] ?? '' }}"
                                        data-message="{{ $warning['message'] ?? '' }}"
                                        data-search="{{ trim(($warning['contract_no'] ?? '') . ' ' . ($warning['message'] ?? '')) }}"
                                    >
                                        <td>{{ $warning['sheet'] ?? '' }}</td>
                                        <td>{{ $warning['row'] ?? '' }}</td>
                                        <td
                                            class="js-contract-shortcut"
                                            data-contract-link="{{ route('accounting.contracts.index', ['q' => $warning['contract_no'] ?? '', 'highlight_contract' => $warning['contract_no'] ?? '']) }}#contractsList"
                                            title="Ctrl/Cmd + Click لفتح العقد مباشرة"
                                        >
                                            {{ $warning['contract_no'] ?? '' }}
                                        </td>
                                        <td>{{ $warning['type'] ?? '' }}</td>
                                        <td
                                            class="js-contract-shortcut"
                                            data-contract-link="{{ route('accounting.contracts.index', ['q' => $warning['contract_no'] ?? '', 'highlight_contract' => $warning['contract_no'] ?? '']) }}#contractsList"
                                            title="Ctrl/Cmd + Click لفتح العقد مباشرة"
                                        >
                                            {{ $warning['message'] ?? '' }}
                                        </td>
                                        <td>
                                            @if(!empty($warning['contract_no']))
                                                <a href="{{ route('accounting.contracts.index', ['q' => $warning['contract_no'], 'highlight_contract' => $warning['contract_no']]) }}#contractsList" class="btn btn-outline-primary btn-sm">فتح العقد</a>
                                                <a
                                                    href="{{ route('accounting.contracts.index', ['q' => $warning['contract_no'], 'highlight_contract' => $warning['contract_no']]) }}#contractsList"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-outline-dark btn-sm"
                                                >
                                                    فتح في تبويب جديد
                                                </a>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-info btn-sm js-copy-contract-link"
                                                    data-contract-link="{{ route('accounting.contracts.index', ['q' => $warning['contract_no'], 'highlight_contract' => $warning['contract_no']]) }}#contractsList"
                                                >
                                                    نسخ رابط العقد
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-sm js-copy-contract" data-contract-copy="{{ $warning['contract_no'] }}">نسخ رقم العقد</button>
                                                <span class="admin-empty-state js-copy-contract-status" aria-live="polite"></span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-empty-state mb-2" id="warningsNoResults" style="display:none;">
                            لا توجد نتائج مطابقة للفلاتر الحالية.
                        </div>
                        <script>
                            (function () {
                                const storageKey = 'accounting_import_warnings_filters_v1';
                                const sheetFilter = document.getElementById('warningsSheetFilter');
                                const typeFilter = document.getElementById('warningsTypeFilter');
                                const sortSelect = document.getElementById('warningsSort');
                                const textFilter = document.getElementById('warningsTextFilter');
                                const clearButton = document.getElementById('warningsClearFilters');
                                const exportButton = document.getElementById('warningsExportVisibleCsv');
                                const copyButton = document.getElementById('warningsCopyVisibleCsv');
                                const visibleCounter = document.getElementById('warningsVisibleCounter');
                                const copyStatus = document.getElementById('warningsCopyStatus');
                                const table = document.getElementById('warningsTable');
                                const noResultsState = document.getElementById('warningsNoResults');
                                if (!sheetFilter || !typeFilter || !sortSelect || !textFilter || !clearButton || !exportButton || !copyButton || !visibleCounter || !copyStatus || !table || !noResultsState) return;

                                const rows = Array.from(table.querySelectorAll('tbody tr'));
                                const tbody = table.querySelector('tbody');
                                let textFilterTimer = null;
                                const csvValue = (value) => `"${String(value ?? '').replace(/"/g, '""')}"`;
                                const copyTextToClipboard = async (text) => {
                                    if (navigator.clipboard && window.isSecureContext) {
                                        await navigator.clipboard.writeText(text);
                                        return;
                                    }
                                    const textarea = document.createElement('textarea');
                                    textarea.value = text;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    textarea.focus();
                                    textarea.select();
                                    const copied = document.execCommand('copy');
                                    document.body.removeChild(textarea);
                                    if (!copied) throw new Error('copy failed');
                                };
                                const saveFilters = () => {
                                    const payload = {
                                        sheet: sheetFilter.value || '',
                                        type: typeFilter.value || '',
                                        sort: sortSelect.value || 'row_desc',
                                        text: textFilter.value || '',
                                    };
                                    try {
                                        localStorage.setItem(storageKey, JSON.stringify(payload));
                                    } catch (e) {}
                                };
                                const loadFilters = () => {
                                    try {
                                        const raw = localStorage.getItem(storageKey);
                                        if (!raw) return;
                                        const payload = JSON.parse(raw);
                                        const hasOption = (select, value) => Array.from(select.options).some((option) => option.value === value);
                                        if (payload && typeof payload === 'object') {
                                            if (typeof payload.sheet === 'string') sheetFilter.value = hasOption(sheetFilter, payload.sheet) ? payload.sheet : '';
                                            if (typeof payload.type === 'string') typeFilter.value = hasOption(typeFilter, payload.type) ? payload.type : '';
                                            if (typeof payload.sort === 'string') sortSelect.value = payload.sort;
                                            if (typeof payload.text === 'string') textFilter.value = payload.text;
                                        }
                                    } catch (e) {}
                                };
                                const sortRows = () => {
                                    const sortBy = sortSelect.value;
                                    rows.sort((a, b) => {
                                        const aRow = parseInt(a.dataset.row || '0', 10);
                                        const bRow = parseInt(b.dataset.row || '0', 10);
                                        const aContract = (a.dataset.contract || '').toLowerCase();
                                        const bContract = (b.dataset.contract || '').toLowerCase();
                                        const aType = (a.dataset.type || '').toLowerCase();
                                        const bType = (b.dataset.type || '').toLowerCase();

                                        if (sortBy === 'row_asc') return aRow - bRow;
                                        if (sortBy === 'row_desc') return bRow - aRow;
                                        if (sortBy === 'contract_asc') return aContract.localeCompare(bContract, 'ar');
                                        if (sortBy === 'contract_desc') return bContract.localeCompare(aContract, 'ar');
                                        if (sortBy === 'type_asc') return aType.localeCompare(bType, 'ar');
                                        if (sortBy === 'type_desc') return bType.localeCompare(aType, 'ar');
                                        return 0;
                                    });

                                    rows.forEach((row) => tbody.appendChild(row));
                                };

                                const applyFilters = () => {
                                    const sheet = sheetFilter.value.trim().toLowerCase();
                                    const type = typeFilter.value.trim().toLowerCase();
                                    const text = textFilter.value.trim().toLowerCase();

                                    sortRows();
                                    let visibleCount = 0;

                                    rows.forEach((row) => {
                                        const rowSheet = (row.dataset.sheet || '').toLowerCase();
                                        const rowType = (row.dataset.type || '').toLowerCase();
                                        const rowSearch = (row.dataset.search || '').toLowerCase();

                                        const passSheet = !sheet || rowSheet === sheet;
                                        const passType = !type || rowType === type;
                                        const passText = !text || rowSearch.includes(text);

                                        row.style.display = (passSheet && passType && passText) ? '' : 'none';
                                        if (row.style.display !== 'none') {
                                            visibleCount++;
                                        }
                                    });
                                    visibleCounter.textContent = 'الصفوف الظاهرة: ' + visibleCount;
                                    noResultsState.style.display = visibleCount === 0 ? '' : 'none';
                                    exportButton.disabled = visibleCount === 0;
                                    copyButton.disabled = visibleCount === 0;
                                    saveFilters();
                                };

                                sheetFilter.addEventListener('change', applyFilters);
                                typeFilter.addEventListener('change', applyFilters);
                                sortSelect.addEventListener('change', applyFilters);
                                textFilter.addEventListener('input', () => {
                                    if (textFilterTimer) {
                                        clearTimeout(textFilterTimer);
                                    }
                                    textFilterTimer = setTimeout(applyFilters, 250);
                                });
                                clearButton.addEventListener('click', () => {
                                    sheetFilter.value = '';
                                    typeFilter.value = '';
                                    sortSelect.value = 'row_desc';
                                    textFilter.value = '';
                                    if (textFilterTimer) {
                                        clearTimeout(textFilterTimer);
                                        textFilterTimer = null;
                                    }
                                    copyStatus.textContent = '';
                                    applyFilters();
                                });
                                const buildCsvLines = (targetRows) => {
                                    const headers = ['sheet', 'row', 'contract_no', 'type', 'message'];
                                    const lines = [headers.map(csvValue).join(',')];
                                    targetRows.forEach((row) => {
                                        lines.push([
                                            row.dataset.sheet || '',
                                            row.dataset.row || '',
                                            row.dataset.contract || '',
                                            row.dataset.type || '',
                                            row.dataset.message || '',
                                        ].map(csvValue).join(','));
                                    });
                                    return lines;
                                };
                                exportButton.addEventListener('click', () => {
                                    const visibleRows = rows.filter((row) => row.style.display !== 'none');
                                    const lines = buildCsvLines(visibleRows);

                                    const blob = new Blob([lines.join('\n') + '\n'], { type: 'text/csv;charset=utf-8;' });
                                    const url = URL.createObjectURL(blob);
                                    const anchor = document.createElement('a');
                                    anchor.href = url;
                                    anchor.download = 'warnings_filtered_' + new Date().toISOString().replace(/[:.]/g, '-') + '.csv';
                                    document.body.appendChild(anchor);
                                    anchor.click();
                                    document.body.removeChild(anchor);
                                    URL.revokeObjectURL(url);
                                });
                                copyButton.addEventListener('click', async () => {
                                    const visibleRows = rows.filter((row) => row.style.display !== 'none');
                                    const csvText = buildCsvLines(visibleRows).join('\n') + '\n';
                                    copyStatus.textContent = '';
                                    try {
                                        await copyTextToClipboard(csvText);
                                        copyStatus.textContent = 'تم نسخ التحذيرات الظاهرة.';
                                    } catch (e) {
                                        copyStatus.textContent = 'تعذر النسخ. استخدم زر التصدير.';
                                    }
                                });
                                table.addEventListener('click', async (event) => {
                                    const button = event.target.closest('.js-copy-contract');
                                    if (!button) return;
                                    const contractNo = button.getAttribute('data-contract-copy') || '';
                                    const row = button.closest('tr');
                                    const rowStatus = row ? row.querySelector('.js-copy-contract-status') : null;
                                    if (!contractNo) return;
                                    if (rowStatus) rowStatus.textContent = '';
                                    try {
                                        await copyTextToClipboard(contractNo);
                                        if (rowStatus) rowStatus.textContent = 'تم النسخ';
                                    } catch (e) {
                                        if (rowStatus) rowStatus.textContent = 'فشل النسخ';
                                    }
                                });
                                table.addEventListener('click', async (event) => {
                                    const button = event.target.closest('.js-copy-contract-link');
                                    if (!button) return;
                                    const contractLink = button.getAttribute('data-contract-link') || '';
                                    const row = button.closest('tr');
                                    const rowStatus = row ? row.querySelector('.js-copy-contract-status') : null;
                                    if (!contractLink) return;
                                    if (rowStatus) rowStatus.textContent = '';
                                    try {
                                        await copyTextToClipboard(contractLink);
                                        if (rowStatus) rowStatus.textContent = 'تم نسخ الرابط';
                                    } catch (e) {
                                        if (rowStatus) rowStatus.textContent = 'فشل نسخ الرابط';
                                    }
                                });
                                table.addEventListener('click', (event) => {
                                    const cell = event.target.closest('.js-contract-shortcut');
                                    if (!cell) return;
                                    if (!(event.ctrlKey || event.metaKey)) return;
                                    const contractLink = cell.getAttribute('data-contract-link') || '';
                                    if (!contractLink) return;
                                    window.open(contractLink, '_blank', 'noopener,noreferrer');
                                });
                                loadFilters();
                                applyFilters();
                            })();
                        </script>
                    @endif
                @endif
                <form method="POST" action="{{ route('accounting.contracts.import') }}" enctype="multipart/form-data" class="row g-2">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label">ملف Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-outline-info" type="submit" name="preview" value="1">معاينة فقط</button>
                        <button class="btn btn-primary" type="submit">تنفيذ الاستيراد</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">إضافة عقد تلميذ</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('accounting.contracts.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">التلميذ</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">اختر التلميذ</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->user->name ?? ('Student #' . $student->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">السنة الدراسية</label>
                        <input type="text" class="form-control" name="academic_year" value="{{ old('academic_year', date('Y') . '-' . (date('Y') + 1)) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المبلغ الإجمالي</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="total_amount" value="{{ old('total_amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">خطة الدفع</label>
                        <select name="plan_type" class="form-select" required>
                            <option value="yearly">سنوي</option>
                            <option value="monthly">شهري</option>
                            <option value="installments">دفعات</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">عدد الدفعات</label>
                        <input type="number" min="1" max="24" class="form-control" name="installments_count" value="{{ old('installments_count', 3) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">نموذج الخطة (اختياري)</label>
                        <select name="payment_plan_id" class="form-select">
                            <option value="">بدون</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تاريخ البداية</label>
                        <input type="date" class="form-control" name="starts_on" value="{{ old('starts_on') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            @foreach(['draft' => 'مسودة', 'active' => 'نشط', 'partial' => 'جزئي', 'paid' => 'مدفوع', 'overdue' => 'متأخر'] as $status => $label)
                                <option value="{{ $status }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="submit">حفظ العقد</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3" id="contractsList">
            <div class="box-header with-border">
                <h4 class="box-title">العقود</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="بحث باسم التلميذ/البريد/رقم العقد">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach(['draft', 'active', 'partial', 'paid', 'overdue'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                        <a href="{{ route('accounting.contracts.index') }}" class="btn btn-outline-secondary">إعادة ضبط</a>
                    </div>
                </form>
                @if(request('highlight_contract'))
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" id="contractHighlightNotice">
                        <span id="contractHighlightNoticeText">تم التركيز على العقد رقم {{ request('highlight_contract') }}.</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearContractHighlight">إلغاء التمييز</button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم العقد</th>
                            <th>التلميذ</th>
                            <th>السنة</th>
                            <th>الخطة</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>تعديل</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($contracts as $i => $contract)
                            @php
                                $paidTotal = (float) ($contract->paid_total ?? 0);
                                $remaining = (float) $contract->total_amount - $paidTotal;
                            @endphp
                            <tr data-external-contract="{{ (string) ($contract->external_contract_no ?? '') }}">
                                <td>{{ $contracts->firstItem() + $i }}</td>
                                <td>{{ $contract->external_contract_no ?: '-' }}</td>
                                <td>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }}</td>
                                <td>{{ $contract->academic_year }}</td>
                                <td>{{ $contract->plan_type }}</td>
                                <td>{{ number_format((float) $contract->total_amount, 2) }}</td>
                                <td>{{ number_format($paidTotal, 2) }}</td>
                                <td>{{ number_format(max($remaining, 0), 2) }}</td>
                                <td><span class="admin-status admin-status-{{ $contract->status }}">{{ $contract->status }}</span></td>
                                <td style="min-width:300px;">
                                    <form method="POST" action="{{ route('accounting.contracts.update', $contract) }}" class="row g-1">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm" name="academic_year" value="{{ $contract->academic_year }}" required>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control form-control-sm" min="0" step="0.01" name="total_amount" value="{{ $contract->total_amount }}" required>
                                        </div>
                                        <div class="col-4">
                                            <select name="plan_type" class="form-select form-select-sm" required>
                                                @foreach(['yearly','monthly','installments'] as $planType)
                                                    <option value="{{ $planType }}" @selected($contract->plan_type === $planType)>{{ $planType }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control form-control-sm" name="installments_count" value="{{ $contract->installments_count }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="date" class="form-control form-control-sm" name="starts_on" value="{{ optional($contract->starts_on)->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="date" class="form-control form-control-sm" name="ends_on" value="{{ optional($contract->ends_on)->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-4">
                                            <select name="status" class="form-select form-select-sm" required>
                                                @foreach(['draft','active','partial','paid','overdue'] as $status)
                                                    <option value="{{ $status }}" @selected($contract->status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="form-control form-control-sm" name="notes" value="{{ $contract->notes }}" placeholder="ملاحظات">
                                        </div>
                                        <input type="hidden" name="payment_plan_id" value="{{ $contract->payment_plan_id }}">
                                        <div class="col-12">
                                            <button class="btn btn-sm btn-info" type="submit">تحديث</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10"><div class="admin-empty-state">لا توجد عقود بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $contracts->links() }}
            </div>
        </div>
        @if(request('highlight_contract'))
            <script>
                (function () {
                    const target = "{{ (string) request('highlight_contract') }}".trim().toLowerCase();
                    if (!target) return;
                    const notice = document.getElementById('contractHighlightNotice');
                    const noticeText = document.getElementById('contractHighlightNoticeText');
                    const clearButton = document.getElementById('clearContractHighlight');
                    const rows = document.querySelectorAll('#contractsList table tbody tr[data-external-contract]');
                    let highlightedRow = null;
                    for (const row of rows) {
                        const contractNo = (row.getAttribute('data-external-contract') || '').trim().toLowerCase();
                        if (contractNo === target) {
                            row.style.outline = '2px solid #2f7d32';
                            row.style.backgroundColor = '#eef8ee';
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            highlightedRow = row;
                            break;
                        }
                    }
                    if (!highlightedRow && noticeText) {
                        noticeText.textContent = 'لم يتم العثور على العقد المطلوب في هذه الصفحة.';
                    }
                    if (clearButton) {
                        clearButton.addEventListener('click', () => {
                            if (highlightedRow) {
                                highlightedRow.style.outline = '';
                                highlightedRow.style.backgroundColor = '';
                            }
                            if (notice) {
                                notice.style.display = 'none';
                            }
                        });
                    }
                })();
            </script>
        @endif

        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">عقود متأخرة</h4>
            </div>
            <div class="box-body">
                <ul>
                    @forelse($overdueContracts as $contract)
                        <li>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }} - {{ $contract->academic_year }}</li>
                    @empty
                        <li class="admin-empty-state">لا توجد عقود متأخرة حالياً.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
