@extends('layoutsadmin.masteradmin')
@section('titlea', trans('accounting.payments'))

@section('contenta')
<style>
    .fam-search-wrap { position: relative; }
    .fam-results {
        position: absolute;
        top: 100%;
        right: 0;
        left: 0;
        z-index: 1050;
        max-height: 320px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,.12);
        display: none;
    }
    .fam-results .fam-result-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
    }
    .fam-results .fam-result-item:hover { background: #f6f9ff; }
    .fam-parent-banner {
        background: linear-gradient(135deg, #eef6ff, #f3fbf5);
        border: 1px solid #d8e8f5;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .fam-parent-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #2e9e5b1f;
        color: #2e9e5b;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }
    .fam-child-row {
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        transition: border-color .15s, background .15s;
    }
    .fam-child-row { cursor: pointer; user-select: none; }
    .fam-child-row:not(.no-contract):hover { border-color: #2e9e5b99; }
    .fam-child-row.checked { border-color: #2e9e5b; background: #f4fbf7; }
    .fam-child-row.no-contract { opacity: .6; background: #fafafa; cursor: not-allowed; }
    .fam-child-row.is-target { box-shadow: 0 0 0 2px #2e9e5b40; }

    /* مؤشر تحديد واضح بدل checkbox المخفي */
    .fam-toggle {
        width: 28px;
        height: 28px;
        border-radius: 9px;
        border: 2px solid #c9c4b8;
        background: #fff;
        color: transparent;
        font-weight: 900;
        font-size: 16px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        transition: all .15s ease;
    }
    .fam-child-row.checked .fam-toggle {
        background: #2e9e5b;
        border-color: #2e9e5b;
        color: #fff;
        box-shadow: 0 2px 8px rgba(46, 158, 91, .4);
    }
    .fam-child-info { flex: 1 1 220px; min-width: 0; }
    .fam-child-name { font-weight: 700; }
    .fam-child-sub { font-size: 12px; color: #888; }
    .fam-balance { font-size: 12.5px; white-space: nowrap; }
    .fam-balance .rem { color: #e0533d; font-weight: 700; }
    .fam-amount-input { width: 140px; }
    .fam-total-bar {
        background: #2e9e5b;
        color: #fff;
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 800;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .fam-check { width: 22px; height: 22px; cursor: pointer; flex: 0 0 auto; }
    .fam-target-badge {
        background: #2e9e5b;
        color: #fff;
        border-radius: 999px;
        font-size: 11px;
        padding: 2px 10px;
    }
</style>

<div class="row">
    <div class="col-12">

        {{-- ====== دفعة عائلية: ولي الأمر / التلميذ ====== --}}
        <div class="box mb-3">
            <div class="box-header with-border bg-success">
                <h4 class="box-title text-white"><strong>{{ trans('accounting.family_payment.title') }}</strong></h4>
            </div>
            <div class="box-body">
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-3">
                        <label class="form-label d-block">{{ trans('accounting.family_payment.search_mode') }}</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="famMode" id="famModeParent" value="parent" checked>
                            <label class="btn btn-outline-success" for="famModeParent">{{ trans('accounting.family_payment.by_parent') }}</label>
                            <input type="radio" class="btn-check" name="famMode" id="famModeStudent" value="student">
                            <label class="btn btn-outline-success" for="famModeStudent">{{ trans('accounting.family_payment.by_student') }}</label>
                        </div>
                    </div>
                    <div class="col-md-9 fam-search-wrap">
                        <label class="form-label">{{ trans('accounting.family_payment.search_label') }}</label>
                        <input type="text" id="famSearch" class="form-control" autocomplete="off"
                            placeholder="{{ trans('accounting.family_payment.search_placeholder_parent') }}">
                        <div id="famResults" class="fam-results"></div>
                    </div>
                </div>

                {{-- لوحة العائلة --}}
                <form method="POST" action="{{ route('accounting.payments.family.store') }}" id="famForm" style="display:none">
                    @csrf
                    <div id="famParentBanner"></div>
                    <div class="text-muted fs-12 mb-2">{{ trans('accounting.family_payment.toggle_hint') }}</div>
                    <div id="famChildren"></div>

                    <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <label class="form-label">{{ trans('accounting.payments_page.receipt_number') }}</label>
                            <input type="text" class="form-control" name="receipt_number" id="famReceiptNumber"
                                value="{{ $nextReceiptNumber }}" placeholder="{{ trans('accounting.family_payment.receipt_auto') }}">
                            <small class="text-muted">{{ trans('accounting.family_payment.receipt_hint') }}</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ trans('accounting.payments_page.date') }}</label>
                            <input type="date" class="form-control" name="paid_on" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ trans('accounting.payments_page.payment_method') }}</label>
                            <select name="payment_method" class="form-select">
                                @foreach(['cash', 'transfer', 'card', 'other'] as $key)
                                    <option value="{{ $key }}">{{ trans('accounting.payment_methods.' . $key) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ trans('accounting.payments_page.notes') }}</label>
                            <input type="text" class="form-control" name="notes">
                        </div>
                    </div>

                    <div class="fam-total-bar mt-3">
                        <span>{{ trans('accounting.family_payment.total_payment') }}: <span id="famTotal" class="ltr">0.00</span></span>
                        <button class="btn btn-light fw-bold" type="submit" id="famSubmit" disabled>
                            {{ trans('accounting.family_payment.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.new_payment') }}</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('accounting.payments.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('accounting.payments_page.contract') }}</label>
                        <select name="contract_id" id="contractSelect" class="d-none" required>
                            <option value="">{{ trans('accounting.payments_page.choose_contract') }}</option>
                        </select>
                        <div class="contract-search-select" id="contractSearchSelect"
                             data-search-url="{{ route('accounting.payments.contract-search') }}"
                             data-branch-id="{{ request('branch_id') }}">
                            <button type="button" id="contractSearchToggle" class="form-control text-start d-flex justify-content-between align-items-center">
                                <span id="contractSearchSelectedText">{{ trans('accounting.payments_page.choose_contract') }}</span>
                                <span>▾</span>
                            </button>
                            <div class="contract-search-menu" id="contractSearchMenu">
                                <input type="text" id="contractSearchInput" class="form-control" placeholder="{{ trans('accounting.payments_page.choose_contract') }}">
                                <div class="contract-search-list" id="contractSearchList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.receipt_number') }}</label>
                        <input type="text" class="form-control" name="receipt_number"
                            value="{{ old('receipt_number', $nextReceiptNumber) }}"
                            placeholder="{{ trans('accounting.family_payment.receipt_auto') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.date') }}</label>
                        <input type="date" class="form-control" name="paid_on" value="{{ old('paid_on', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.amount') }}</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.payment_method') }}</label>
                        <select name="payment_method" class="form-select">
                            @foreach(['cash', 'transfer', 'card', 'other'] as $key)
                                <option value="{{ $key }}">{{ trans('accounting.payment_methods.' . $key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('accounting.payments_page.installment_optional') }}</label>
                        <input type="number" min="1" class="form-control" name="installment_id" value="{{ old('installment_id') }}" placeholder="{{ trans('accounting.payments_page.installment_placeholder') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('accounting.payments_page.notes') }}</label>
                        <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="submit">{{ trans('accounting.payments_page.submit_payment') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.filter_title') }}</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.from_date') }}</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.to_date') }}</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الفرع</label>
                        <select name="branch_id" class="form-select" onchange="this.form.submit()">
                            <option value="">كل الفروع</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" @selected((string) request('branch_id') === (string) $school->id)>
                                    {{ $school->name_school }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('accounting.payments_page.section') }}</label>
                        <select name="section_id" class="form-select">
                            <option value="">{{ trans('accounting.payments_page.all_sections') }}</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected((string)request('section_id') === (string)$section->id)>
                                    {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-info" type="submit">{{ trans('accounting.payments_page.filter') }}</button>
                        <a href="{{ route('accounting.payments.index') }}" class="btn btn-outline-secondary ms-2">{{ trans('accounting.payments_page.reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.list_title') }}</h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('accounting.payments_page.student') }}</th>
                            <th>{{ trans('accounting.payments_page.receipt_number') }}</th>
                            <th>{{ trans('accounting.payments_page.date') }}</th>
                            <th>{{ trans('accounting.payments_page.amount') }}</th>
                            <th>{{ trans('accounting.payments_page.method') }}</th>
                            <th>{{ trans('accounting.payments_page.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td>{{ $payments->firstItem() + $index }}</td>
                                <td>{{ $payment->contract->student->user->name ?? ('Student #' . $payment->contract->student_id) }}</td>
                                <td>{{ $payment->receipt_number }}</td>
                                <td>{{ optional($payment->paid_on)->format('Y-m-d') }}</td>
                                <td>{{ number_format((float)$payment->amount, 2) }}</td>
                                <td>{{ trans('accounting.payment_methods.' . $payment->payment_method) }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('accounting.payments.receipt', $payment) }}" target="_blank">{{ trans('accounting.payments_page.view_receipt') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="admin-empty-state">{{ trans('accounting.payments_page.empty_payments') }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $payments->links() }}
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.overdue_title') }}</h4>
            </div>
            <div class="box-body">
                <ul>
                    @forelse($overdue as $contract)
                        <li>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }} - {{ $contract->academic_year }}</li>
                    @empty
                        <li class="admin-empty-state">{{ trans('accounting.payments_page.empty_overdue') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const TRANS = {
        placeholderParent: @json(trans('accounting.family_payment.search_placeholder_parent')),
        placeholderStudent: @json(trans('accounting.family_payment.search_placeholder_student')),
        noResults: @json(trans('accounting.family_payment.no_results')),
        childrenCount: @json(trans('accounting.family_payment.children_count')),
        guardian: @json(trans('accounting.family_payment.guardian')),
        noContract: @json(trans('accounting.family_payment.no_contract')),
        year: @json(trans('accounting.family_payment.contract_year')),
        total: @json(trans('accounting.family_payment.total')),
        paid: @json(trans('accounting.family_payment.paid')),
        remaining: @json(trans('accounting.family_payment.remaining')),
        target: @json(trans('accounting.family_payment.searched_student')),
        noParent: @json(trans('accounting.family_payment.no_parent')),
    };

    const searchInput = document.getElementById('famSearch');
    const resultsBox = document.getElementById('famResults');
    const famForm = document.getElementById('famForm');
    const parentBanner = document.getElementById('famParentBanner');
    const childrenBox = document.getElementById('famChildren');
    const totalEl = document.getElementById('famTotal');
    const submitBtn = document.getElementById('famSubmit');

    let mode = 'parent';
    let searchTimer = null;

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    function fmt(n) {
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ====== تبديل نمط البحث ======
    document.querySelectorAll('input[name="famMode"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            mode = this.value;
            searchInput.placeholder = mode === 'parent' ? TRANS.placeholderParent : TRANS.placeholderStudent;
            searchInput.value = '';
            resultsBox.style.display = 'none';
            searchInput.focus();
        });
    });

    // ====== البحث ======
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        if (q.length < 2) { resultsBox.style.display = 'none'; return; }
        searchTimer = setTimeout(function () { doSearch(q); }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.fam-search-wrap')) resultsBox.style.display = 'none';
    });

    function doSearch(q) {
        fetch("{{ route('accounting.payments.family.search') }}?type=" + mode + "&q=" + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const results = data.results || [];
                if (!results.length) {
                    resultsBox.innerHTML = '<div class="fam-result-item text-muted">' + TRANS.noResults + '</div>';
                } else {
                    resultsBox.innerHTML = results.map(function (r) {
                        const sub = mode === 'parent'
                            ? [r.relation, r.phone, TRANS.childrenCount.replace(':count', r.children_count)].filter(Boolean).join(' • ')
                            : [r.section, r.national_id, r.parent_name ? TRANS.guardian + ': ' + r.parent_name : ''].filter(Boolean).join(' • ');
                        return '<div class="fam-result-item" data-id="' + r.id + '">' +
                            '<div class="fw-bold">' + escapeHtml(r.name) + '</div>' +
                            '<div class="fam-child-sub">' + escapeHtml(sub) + '</div>' +
                        '</div>';
                    }).join('');
                }
                resultsBox.style.display = 'block';
                resultsBox.querySelectorAll('.fam-result-item[data-id]').forEach(function (item) {
                    item.addEventListener('click', function () {
                        resultsBox.style.display = 'none';
                        searchInput.value = '';
                        loadFamily(this.dataset.id);
                    });
                });
            })
            .catch(function () { resultsBox.style.display = 'none'; });
    }

    // ====== تحميل العائلة ======
    function loadFamily(id) {
        fetch("{{ route('accounting.payments.family.data') }}?type=" + mode + "&id=" + encodeURIComponent(id), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { renderFamily(data); })
            .catch(function () {});
    }

    function renderFamily(data) {
        const parent = data.parent;
        if (parent) {
            const initials = parent.name.trim().split(/\s+/).map(function (p) { return p.charAt(0); }).slice(0, 2).join('');
            parentBanner.innerHTML =
                '<div class="fam-parent-banner">' +
                    '<span class="fam-parent-avatar">' + escapeHtml(initials) + '</span>' +
                    '<div>' +
                        '<div class="fw-bold fs-16">' + escapeHtml(parent.name) + '</div>' +
                        '<div class="fam-child-sub">' + escapeHtml([parent.relation, parent.phone].filter(Boolean).join(' • ')) + '</div>' +
                    '</div>' +
                    '<span class="badge bg-success ms-auto">' + TRANS.childrenCount.replace(':count', data.children.length) + '</span>' +
                '</div>';
        } else {
            parentBanner.innerHTML = '<div class="alert alert-warning py-2">' + TRANS.noParent + '</div>';
        }

        childrenBox.innerHTML = data.children.map(function (child, idx) {
            const hasContract = !!child.contract;
            // عند البحث بالتلميذ: المحدد فقط؛ عند ولي الأمر: كل من له عقد ومتبقٍ
            const checked = hasContract && (child.is_target || (mode === 'parent' && child.contract.remaining > 0));
            const amount = hasContract ? child.contract.remaining : 0;

            let html = '<div class="fam-child-row' + (checked ? ' checked' : '') + (hasContract ? '' : ' no-contract') + (child.is_target ? ' is-target' : '') + '">';
            html += '<span class="fam-toggle">&#10004;</span>';
            html += '<div class="fam-child-info">' +
                '<div class="fam-child-name">' + escapeHtml(child.name) +
                    (child.is_target ? ' <span class="fam-target-badge">' + TRANS.target + '</span>' : '') +
                '</div>' +
                '<div class="fam-child-sub">' + escapeHtml([child.section, child.national_id].filter(Boolean).join(' • ')) + '</div>' +
            '</div>';

            if (hasContract) {
                html += '<div class="fam-balance text-muted">' +
                    TRANS.year + ': <b>' + escapeHtml(child.contract.academic_year) + '</b><br>' +
                    TRANS.total + ': ' + fmt(child.contract.total) + ' • ' +
                    TRANS.paid + ': ' + fmt(child.contract.paid) + ' • ' +
                    '<span class="rem">' + TRANS.remaining + ': ' + fmt(child.contract.remaining) + '</span>' +
                '</div>';
                html += '<input type="hidden" name="items[' + idx + '][contract_id]" value="' + child.contract.id + '"' + (checked ? '' : ' disabled') + '>';
                html += '<input type="number" step="0.01" min="0.01" class="form-control fam-amount-input" name="items[' + idx + '][amount]" value="' + (amount > 0 ? amount.toFixed(2) : '') + '"' + (checked ? '' : ' disabled') + ' required>';
            } else {
                html += '<span class="badge bg-secondary">' + TRANS.noContract + '</span>';
            }

            html += '</div>';
            return html;
        }).join('');

        famForm.style.display = '';
        bindRows();
        recalcTotal();
        famForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ====== تفعيل/تعطيل الصفوف بالنقر على البطاقة كاملة ======
    function bindRows() {
        childrenBox.querySelectorAll('.fam-child-row').forEach(function (row) {
            if (row.classList.contains('no-contract')) return;
            const inputs = row.querySelectorAll('input[name^="items["]');

            row.addEventListener('click', function (e) {
                // النقر داخل خانة المبلغ لا يغيّر التحديد
                if (e.target.closest('.fam-amount-input')) return;

                const willCheck = !row.classList.contains('checked');
                row.classList.toggle('checked', willCheck);
                inputs.forEach(function (inp) { inp.disabled = !willCheck; });

                if (willCheck) {
                    const amountInput = row.querySelector('.fam-amount-input');
                    if (amountInput) { amountInput.focus(); amountInput.select(); }
                }
                recalcTotal();
            });
        });

        childrenBox.querySelectorAll('.fam-amount-input').forEach(function (inp) {
            inp.addEventListener('input', recalcTotal);
        });
    }

    function recalcTotal() {
        let total = 0;
        let count = 0;
        childrenBox.querySelectorAll('.fam-amount-input').forEach(function (inp) {
            if (!inp.disabled) {
                total += parseFloat(inp.value) || 0;
                count++;
            }
        });
        totalEl.textContent = fmt(total);
        submitBtn.disabled = count === 0 || total <= 0;
    }
});
</script>

<style>
    .contract-search-select { position: relative; }
    .contract-search-menu {
        position: absolute;
        inset-inline: 0;
        top: calc(100% + 4px);
        z-index: 1050;
        background: #fff;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        padding: 8px;
        display: none;
    }
    .contract-search-menu.is-open { display: block; }
    .contract-search-list { max-height: 240px; overflow-y: auto; margin-top: 6px; }
    .contract-search-option {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: start;
        padding: 6px 8px;
        border-radius: 6px;
        cursor: pointer;
    }
    .contract-search-option:hover { background: #eef5ff; }
</style>
<script>
    (function () {
        const select = document.getElementById('contractSelect');
        const wrapper = document.getElementById('contractSearchSelect');
        const toggle = document.getElementById('contractSearchToggle');
        const menu = document.getElementById('contractSearchMenu');
        const searchInput = document.getElementById('contractSearchInput');
        const list = document.getElementById('contractSearchList');
        const selectedText = document.getElementById('contractSearchSelectedText');
        if (!select || !wrapper || !toggle || !menu || !searchInput || !list || !selectedText) {
            return;
        }

        const searchUrl = wrapper.dataset.searchUrl;
        const branchId = wrapper.dataset.branchId || '';
        const hintText = 'اكتب حرفين على الأقل (الاسم أو رقم العقد)...';
        let debounceTimer = null;
        let activeController = null;

        function setPlaceholder(text) {
            list.innerHTML = '';
            const el = document.createElement('div');
            el.className = 'admin-empty-state';
            el.textContent = text;
            list.appendChild(el);
        }

        function closeMenu() { menu.classList.remove('is-open'); }

        function openMenu() {
            menu.classList.add('is-open');
            searchInput.focus();
            searchInput.select();
        }

        function chooseContract(value, label) {
            let option = Array.from(select.options).find(function (o) { return o.value === value; });
            if (!option) {
                option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                select.appendChild(option);
            }
            select.value = value;
            selectedText.textContent = label;
            closeMenu();
            searchInput.value = '';
        }

        function renderResults(results) {
            list.innerHTML = '';
            if (!results.length) {
                setPlaceholder('لا توجد نتائج');
                return;
            }
            results.forEach(function (item) {
                const optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'contract-search-option';
                optionButton.textContent = item.label;
                optionButton.dataset.value = item.id;
                optionButton.addEventListener('click', function () {
                    chooseContract(String(item.id), item.label);
                });
                list.appendChild(optionButton);
            });
        }

        function fetchResults(term) {
            if (term.trim().length < 2) {
                setPlaceholder(hintText);
                return;
            }
            setPlaceholder('جارٍ البحث...');
            if (activeController) { activeController.abort(); }
            activeController = new AbortController();

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', term.trim());
            if (branchId) { url.searchParams.set('branch_id', branchId); }

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: activeController.signal
            })
                .then(function (response) { return response.ok ? response.json() : { results: [] }; })
                .then(function (data) { renderResults(data.results || []); })
                .catch(function (error) {
                    if (error.name !== 'AbortError') { setPlaceholder('تعذّر البحث، حاول مجدداً'); }
                });
        }

        toggle.addEventListener('click', function () {
            if (menu.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
                if (!searchInput.value.trim()) { setPlaceholder(hintText); }
            }
        });

        searchInput.addEventListener('input', function () {
            const term = searchInput.value;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { fetchResults(term); }, 250);
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) { closeMenu(); }
        });

        setPlaceholder(hintText);
    })();
</script>
@endsection
