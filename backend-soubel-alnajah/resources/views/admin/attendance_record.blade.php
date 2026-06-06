@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('opt.attendance_record_title') }}
@stop
@endsection

@section('contenta')
<style>
    :root {
        --att-bg: #faf8f5;
        --att-green: #2e9e5b;
        --att-green-soft: #e6f4ec;
        --att-yellow: #f0ad2e;
        --att-yellow-soft: #fdf3df;
        --att-red: #e0533d;
        --att-red-soft: #fbe9e6;
        --att-card-radius: 16px;
        --att-shadow: 0 2px 8px rgba(0, 0, 0, .06);
    }

    .att-page {
        background: var(--att-bg);
        border-radius: var(--att-card-radius);
        padding: 16px;
        max-width: 760px;
        margin-inline: auto;
        min-height: 70vh;
    }

    /* ====== شريط الفلاتر ====== */
    .att-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .att-filters .form-select,
    .att-filters .form-control {
        border-radius: 12px;
        border: 1px solid #e6e2db;
        background-color: #fff;
        box-shadow: var(--att-shadow);
        min-height: 46px;
        font-weight: 600;
    }

    .att-filters .att-section-select { flex: 1 1 220px; }
    .att-filters .att-date-input { flex: 0 1 170px; }

    /* ====== شريط الساعات ====== */
    .att-hours {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 12px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .att-hours::-webkit-scrollbar { display: none; }

    .att-hour-pill {
        flex: 0 0 auto;
        border: 1.5px solid #e0dbd2;
        background: #fff;
        color: #5b5b5b;
        border-radius: 999px;
        padding: 7px 18px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all .15s ease;
        direction: ltr;
    }

    .att-hour-pill.active {
        background: var(--att-green);
        border-color: var(--att-green);
        color: #fff;
        box-shadow: 0 3px 10px rgba(46, 158, 91, .35);
    }

    /* ====== الأزرار الجماعية ====== */
    .att-bulk {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .att-bulk-chip {
        flex: 1 1 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        background: #fff;
        border: 1.5px solid #e0dbd2;
        border-radius: 12px;
        padding: 9px 14px;
        font-weight: 700;
        font-size: 14px;
        color: #333;
        cursor: pointer;
        box-shadow: var(--att-shadow);
        transition: transform .1s ease, box-shadow .15s ease;
    }

    .att-bulk-chip:active { transform: scale(.97); }
    .att-bulk-chip svg { width: 18px; height: 18px; }

    /* ====== العدّاد والبحث ====== */
    .att-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .att-counter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        color: var(--att-green);
        font-size: 15px;
        white-space: nowrap;
    }

    .att-counter svg { width: 20px; height: 20px; }

    .att-search {
        flex: 1 1 200px;
        border-radius: 12px;
        border: 1px solid #e6e2db;
        background: #fff;
        box-shadow: var(--att-shadow);
        min-height: 42px;
        padding-inline: 14px;
        font-size: 14px;
        width: 100%;
        outline: none;
    }

    .att-search:focus { border-color: var(--att-green); }

    /* ====== بطاقة دليل الألوان ====== */
    .att-legend {
        background: #eef4fb;
        border-radius: var(--att-card-radius);
        padding: 14px 18px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .att-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 14px;
        color: #333;
    }

    .att-legend-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid transparent;
    }

    .att-legend-icon svg { width: 17px; height: 17px; }

    .att-legend-icon.present { background: var(--att-green-soft); border-color: var(--att-green); color: var(--att-green); }
    .att-legend-icon.late { background: var(--att-yellow-soft); border-color: var(--att-yellow); color: var(--att-yellow); }
    .att-legend-icon.absent { background: var(--att-red-soft); border-color: var(--att-red); color: var(--att-red); }

    /* ====== كروت التلاميذ ====== */
    .att-card {
        background: #fff;
        border-radius: var(--att-card-radius);
        box-shadow: var(--att-shadow);
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        transition: box-shadow .15s ease;
    }

    .att-card:hover { box-shadow: 0 4px 14px rgba(0, 0, 0, .1); }

    .att-avatar {
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--att-green-soft);
        color: var(--att-green);
        font-weight: 800;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        user-select: none;
    }

    .att-info {
        flex: 1 1 auto;
        min-width: 0;
    }

    .att-name {
        font-weight: 800;
        font-size: 15px;
        color: #222;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-sub {
        font-size: 12px;
        color: #8a8a8a;
        direction: ltr;
        text-align: start;
        unicode-bidi: embed;
    }

    .att-actions {
        display: flex;
        gap: 8px;
        flex: 0 0 auto;
    }

    .att-status-btn {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        border: 1.5px solid;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: transparent;
        transition: all .15s ease;
        padding: 0;
    }

    .att-status-btn svg { width: 20px; height: 20px; }

    .att-status-btn[data-status="1"] { border-color: var(--att-green); background: var(--att-green-soft); color: var(--att-green); }
    .att-status-btn[data-status="2"] { border-color: var(--att-yellow); background: var(--att-yellow-soft); color: var(--att-yellow); }
    .att-status-btn[data-status="0"] { border-color: var(--att-red); background: var(--att-red-soft); color: var(--att-red); }

    .att-status-btn[data-status="1"].active { background: var(--att-green); color: #fff; box-shadow: 0 4px 12px rgba(46, 158, 91, .4); }
    .att-status-btn[data-status="2"].active { background: var(--att-yellow); color: #fff; box-shadow: 0 4px 12px rgba(240, 173, 46, .4); }
    .att-status-btn[data-status="0"].active { background: var(--att-red); color: #fff; box-shadow: 0 4px 12px rgba(224, 83, 61, .4); }

    .att-status-btn:active { transform: scale(.92); }

    /* ====== حالة فارغة ====== */
    .att-empty {
        text-align: center;
        color: #9a958d;
        padding: 50px 20px;
        font-weight: 600;
        font-size: 15px;
    }

    .att-empty svg {
        width: 56px;
        height: 56px;
        display: block;
        margin: 0 auto 12px;
        opacity: .4;
    }

    /* ====== سبينر التحميل ====== */
    .att-loading {
        text-align: center;
        padding: 40px;
    }

    @media (max-width: 480px) {
        .att-page { padding: 12px; border-radius: 12px; }
        .att-bulk-chip { font-size: 13px; padding: 8px 10px; }
        .att-status-btn { width: 42px; height: 42px; }
        .att-avatar { flex-basis: 42px; width: 42px; height: 42px; font-size: 14px; }
    }

    /* ====== iPad والحاسوب: استغلال كامل للمساحة ====== */
    @media (min-width: 768px) {
        .att-page {
            max-width: none;
            padding: 22px 26px;
        }

        /* شريط أدوات موحّد أبيض لاصق بالأعلى */
        .att-topbar {
            position: sticky;
            top: 76px;
            z-index: 20;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, .08);
            padding: 16px 20px 8px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 18px;
        }

        .att-topbar .att-filters {
            flex: 1 1 46%;
            margin-bottom: 0;
            min-width: 300px;
        }

        .att-topbar .att-filters .form-select,
        .att-topbar .att-filters .form-control {
            box-shadow: none;
            background-color: #faf8f5;
        }

        .att-topbar .att-meta-row {
            flex: 1 1 42%;
            margin-bottom: 0;
            min-width: 280px;
        }

        .att-topbar .att-search {
            box-shadow: none;
            background-color: #faf8f5;
        }

        .att-topbar .att-hours {
            flex: 1 1 56%;
            margin-bottom: 0;
            padding-bottom: 0;
            min-width: 320px;
            order: 3;
        }

        .att-topbar .att-bulk {
            flex: 1 1 36%;
            margin-bottom: 0;
            justify-content: flex-end;
            order: 4;
        }

        .att-topbar .att-bulk-chip {
            flex: 0 0 auto;
            box-shadow: none;
        }

        /* دليل الألوان: سطر رفيع أسفل الشريط */
        .att-topbar .att-legend {
            order: 5;
            flex: 1 1 100%;
            background: transparent;
            border-radius: 0;
            border-top: 1px dashed #e8e4dc;
            padding: 10px 4px 4px;
            margin-bottom: 0;
            justify-content: flex-start;
            gap: 28px;
        }

        .att-topbar .att-legend-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
        }

        .att-topbar .att-legend-icon svg { width: 14px; height: 14px; }

        /* شبكة كروت بعمودين */
        #attList {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        #attList .att-card { margin-bottom: 0; }
        #attList .att-loading { grid-column: 1 / -1; }

        .att-empty { padding: 80px 20px; }
    }

    /* شاشات واسعة: 3 أعمدة */
    @media (min-width: 1200px) {
        #attList {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

{{-- قوالب SVG للأيقونات (تُستنسخ بالـ JS) --}}
<svg style="display:none" xmlns="http://www.w3.org/2000/svg">
    <symbol id="att-icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6L9 17l-5-5"/>
    </symbol>
    <symbol id="att-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 7v5l3 3"/>
    </symbol>
    <symbol id="att-icon-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 6L6 18M6 6l12 12"/>
    </symbol>
    <symbol id="att-icon-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
    </symbol>
</svg>

<div class="att-page">

    <div class="att-topbar">

    {{-- شريط الفلاتر: القسم + التاريخ --}}
    <div class="att-filters">
        <select id="attSection" class="form-select att-section-select">
            <option value="">{{ trans('opt.choose_section') }}</option>
            @foreach ($Sections as $section)
                <option value="{{ $section->id }}">
                    {{ $section->classroom->schoolgrade->name_grade ?? '' }} /
                    {{ $section->classroom->name_class ?? '' }} /
                    {{ $section->name_section ?? '' }}
                </option>
            @endforeach
        </select>
        <input type="date" id="attDate" class="form-control att-date-input" value="{{ date('Y-m-d') }}">
    </div>

    {{-- شريط الساعات --}}
    <div class="att-hours" id="attHours">
        @foreach ($hours as $key => $label)
            <button type="button" class="att-hour-pill @if($key === $defaultHourKey) active @endif" data-hour="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- الأزرار الجماعية --}}
    <div class="att-bulk">
        <button type="button" class="att-bulk-chip" data-bulk="1">
            <span style="color: var(--att-green)"><svg><use href="#att-icon-check"/></svg></span>
            {{ trans('opt.all_present') }}
        </button>
        <button type="button" class="att-bulk-chip" data-bulk="2">
            <span style="color: var(--att-yellow)"><svg><use href="#att-icon-clock"/></svg></span>
            {{ trans('opt.all_late') }}
        </button>
        <button type="button" class="att-bulk-chip" data-bulk="0">
            <span style="color: var(--att-red)"><svg><use href="#att-icon-x"/></svg></span>
            {{ trans('opt.all_absent') }}
        </button>
    </div>

    {{-- العدّاد + البحث --}}
    <div class="att-meta-row">
        <span class="att-counter">
            <svg><use href="#att-icon-users"/></svg>
            <span id="attCounter">0 {{ trans('opt.recorded_of_total') }} 0</span>
        </span>
        <input type="search" id="attSearch" class="att-search" placeholder="{{ trans('opt.attendance_search') }}">
    </div>

    {{-- دليل الألوان --}}
    <div class="att-legend">
        <span class="att-legend-item">
            <span class="att-legend-icon present"><svg><use href="#att-icon-check"/></svg></span>
            {{ trans('opt.present') }}
        </span>
        <span class="att-legend-item">
            <span class="att-legend-icon late"><svg><use href="#att-icon-clock"/></svg></span>
            {{ trans('opt.late') }}
        </span>
        <span class="att-legend-item">
            <span class="att-legend-icon absent"><svg><use href="#att-icon-x"/></svg></span>
            {{ trans('opt.absent') }}
        </span>
    </div>

    </div>{{-- /att-topbar --}}

    {{-- قائمة التلاميذ --}}
    <div id="attList"></div>

    {{-- حالة فارغة --}}
    <div id="attEmpty" class="att-empty">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        {{ trans('opt.select_section_first') }}
    </div>

</div>
@endsection

@section('jsa')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const sectionSelect = document.getElementById('attSection');
    const dateInput = document.getElementById('attDate');
    const hoursBar = document.getElementById('attHours');
    const listEl = document.getElementById('attList');
    const emptyEl = document.getElementById('attEmpty');
    const counterEl = document.getElementById('attCounter');
    const searchInput = document.getElementById('attSearch');

    const TRANS = {
        ofTotal: @json(trans('opt.recorded_of_total')),
        saved: @json(trans('opt.absence_saved')),
        saveError: @json(trans('opt.absence_save_error')),
        noStudents: @json(trans('opt.no_students_in_section')),
        selectFirst: @json(trans('opt.select_section_first')),
        bulkDone: @json(trans('opt.bulk_done')),
        confirmBulk: @json(trans('opt.confirm_bulk')),
        statusNames: { 0: @json(trans('opt.absent')), 1: @json(trans('opt.present')), 2: @json(trans('opt.late')) },
    };

    const ICONS = { 1: 'att-icon-check', 2: 'att-icon-clock', 0: 'att-icon-x' };

    let students = [];
    let selectedHour = @json($defaultHourKey);
    let total = 0;
    let recorded = 0;

    function notify(type, msg) {
        if (window.toastr) { toastr[type](msg); }
        else if (type === 'error') { alert(msg); }
    }

    function initials(name) {
        const parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].substring(0, 2);
        return parts[0].charAt(0) + parts[parts.length - 1].charAt(0);
    }

    function updateCounter() {
        counterEl.textContent = recorded + ' ' + TRANS.ofTotal + ' ' + total;
    }

    function statusButton(status) {
        return '<button type="button" class="att-status-btn" data-status="' + status + '">' +
            '<svg><use href="#' + ICONS[status] + '"/></svg></button>';
    }

    function render() {
        if (!students.length) {
            listEl.innerHTML = '';
            emptyEl.style.display = '';
            emptyEl.lastChild.textContent = sectionSelect.value ? TRANS.noStudents : TRANS.selectFirst;
            updateCounter();
            return;
        }
        emptyEl.style.display = 'none';

        listEl.innerHTML = students.map(function (s) {
            return '<div class="att-card" data-id="' + s.id + '" data-search="' + (s.name + ' ' + s.subtitle).toLowerCase() + '">' +
                '<span class="att-avatar">' + initials(s.name) + '</span>' +
                '<div class="att-info">' +
                    '<div class="att-name">' + s.name + '</div>' +
                    (s.subtitle ? '<div class="att-sub">' + s.subtitle + '</div>' : '') +
                '</div>' +
                '<div class="att-actions">' + statusButton(1) + statusButton(2) + statusButton(0) + '</div>' +
            '</div>';
        }).join('');

        refreshActiveStates();
        applySearch();
        updateCounter();
    }

    function refreshActiveStates() {
        students.forEach(function (s) {
            const card = listEl.querySelector('.att-card[data-id="' + s.id + '"]');
            if (!card) return;
            const current = s.hours[selectedHour];
            card.querySelectorAll('.att-status-btn').forEach(function (btn) {
                btn.classList.toggle('active', parseInt(btn.dataset.status, 10) === current);
            });
        });
    }

    function applySearch() {
        const q = searchInput.value.trim().toLowerCase();
        listEl.querySelectorAll('.att-card').forEach(function (card) {
            card.style.display = (!q || card.dataset.search.indexOf(q) !== -1) ? '' : 'none';
        });
    }

    function loadData() {
        const sectionId = sectionSelect.value;
        const date = dateInput.value;
        if (!sectionId || !date) {
            students = []; total = 0; recorded = 0;
            render();
            return;
        }

        listEl.innerHTML = '<div class="att-loading"><div class="spinner-border text-success"></div></div>';
        emptyEl.style.display = 'none';

        fetch("{{ route('attendance.data') }}?section_id=" + encodeURIComponent(sectionId) + "&date=" + encodeURIComponent(date), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (res) { if (!res.ok) throw new Error(); return res.json(); })
            .then(function (data) {
                students = data.students;
                total = data.total;
                recorded = data.recorded;
                render();
            })
            .catch(function () {
                students = []; total = 0; recorded = 0;
                render();
                notify('error', TRANS.saveError);
            });
    }

    // ====== تغيير القسم / التاريخ ======
    sectionSelect.addEventListener('change', loadData);
    dateInput.addEventListener('change', loadData);

    // ====== تبديل الساعة ======
    hoursBar.addEventListener('click', function (e) {
        const pill = e.target.closest('.att-hour-pill');
        if (!pill) return;
        selectedHour = pill.dataset.hour;
        hoursBar.querySelectorAll('.att-hour-pill').forEach(function (p) {
            p.classList.toggle('active', p === pill);
        });
        refreshActiveStates();
    });

    // ====== نقرة حالة لتلميذ ======
    listEl.addEventListener('click', function (e) {
        const btn = e.target.closest('.att-status-btn');
        if (!btn) return;
        const card = btn.closest('.att-card');
        const studentId = parseInt(card.dataset.id, 10);
        const status = parseInt(btn.dataset.status, 10);
        const student = students.find(function (s) { return s.id === studentId; });
        if (!student || student.hours[selectedHour] === status) return;

        const previous = student.hours[selectedHour];
        const hadRecord = student.has_record;

        // تحديث متفائل
        student.hours[selectedHour] = status;
        if (!student.has_record) { student.has_record = true; recorded++; }
        refreshActiveStates();
        updateCounter();

        fetch("{{ route('absence.update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId,
                hour: selectedHour,
                status: status,
                date: dateInput.value
            })
        })
            .then(function (res) { if (!res.ok) throw new Error(); return res.json(); })
            .then(function () { notify('success', TRANS.saved); })
            .catch(function () {
                // تراجع عن التحديث المتفائل
                student.hours[selectedHour] = previous;
                if (!hadRecord) { student.has_record = false; recorded--; }
                refreshActiveStates();
                updateCounter();
                notify('error', TRANS.saveError);
            });
    });

    // ====== الأزرار الجماعية ======
    document.querySelectorAll('.att-bulk-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            const sectionId = sectionSelect.value;
            if (!sectionId || !students.length) return;
            const status = parseInt(this.dataset.bulk, 10);

            if (!confirm(TRANS.confirmBulk.replace(':status', TRANS.statusNames[status]))) return;

            fetch("{{ route('attendance.bulk') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    section_id: sectionId,
                    date: dateInput.value,
                    hour: selectedHour,
                    status: status
                })
            })
                .then(function (res) { if (!res.ok) throw new Error(); return res.json(); })
                .then(function (data) {
                    students.forEach(function (s) {
                        s.hours[selectedHour] = status;
                        s.has_record = true;
                    });
                    recorded = data.recorded;
                    refreshActiveStates();
                    updateCounter();
                    notify('success', TRANS.bulkDone);
                })
                .catch(function () { notify('error', TRANS.saveError); });
        });
    });

    // ====== البحث ======
    searchInput.addEventListener('input', applySearch);

    updateCounter();
});
</script>
@endsection
