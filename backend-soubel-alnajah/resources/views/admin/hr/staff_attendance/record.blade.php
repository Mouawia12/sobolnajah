@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ $pageTitle }}
@stop
@endsection

@section('contenta')
<style>
    :root {
        --att-bg:#faf8f5; --att-green:#2e9e5b; --att-green-soft:#e6f4ec;
        --att-yellow:#f0ad2e; --att-yellow-soft:#fdf3df; --att-red:#e0533d; --att-red-soft:#fbe9e6;
        --att-radius:16px; --att-shadow:0 2px 8px rgba(0,0,0,.06);
    }
    .att-page{background:var(--att-bg);border-radius:var(--att-radius);padding:16px;max-width:820px;margin-inline:auto;min-height:70vh;}
    .att-topbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:16px;}
    .att-filters{display:flex;gap:10px;flex-wrap:wrap;flex:1 1 300px;}
    .att-filters .form-select,.att-filters .form-control{border-radius:12px;border:1px solid #e6e2db;background:#fff;box-shadow:var(--att-shadow);min-height:46px;font-weight:600;}
    .att-bulk{display:flex;gap:8px;flex-wrap:wrap;}
    .att-bulk-chip{display:inline-flex;align-items:center;justify-content:center;gap:7px;background:#fff;border:1.5px solid #e0dbd2;border-radius:12px;padding:9px 14px;font-weight:700;font-size:14px;color:#333;cursor:pointer;box-shadow:var(--att-shadow);}
    .att-bulk-chip svg{width:18px;height:18px;}
    .att-meta-row{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;flex:1 1 100%;margin-top:4px;}
    .att-counter{display:inline-flex;align-items:center;gap:6px;font-weight:800;color:var(--att-green);font-size:15px;}
    .att-counter svg{width:20px;height:20px;}
    .att-search{flex:1 1 220px;border-radius:12px;border:1px solid #e6e2db;background:#fff;box-shadow:var(--att-shadow);min-height:42px;padding-inline:14px;font-size:14px;outline:none;}
    .att-legend{background:#eef4fb;border-radius:var(--att-radius);padding:12px 18px;display:flex;justify-content:space-around;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
    .att-legend-item{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:#333;}
    .att-legend-icon{width:32px;height:32px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid transparent;}
    .att-legend-icon svg{width:16px;height:16px;}
    .att-legend-icon.present{background:var(--att-green-soft);border-color:var(--att-green);color:var(--att-green);}
    .att-legend-icon.late{background:var(--att-yellow-soft);border-color:var(--att-yellow);color:var(--att-yellow);}
    .att-legend-icon.absent{background:var(--att-red-soft);border-color:var(--att-red);color:var(--att-red);}
    #attList{display:grid;grid-template-columns:1fr;gap:12px;}
    .att-card{background:#fff;border-radius:var(--att-radius);box-shadow:var(--att-shadow);padding:12px 14px;display:flex;align-items:center;gap:12px;}
    .att-avatar{flex:0 0 46px;width:46px;height:46px;border-radius:14px;background:var(--att-green-soft);color:var(--att-green);font-weight:800;display:inline-flex;align-items:center;justify-content:center;}
    .att-avatar.emp{background:#eef;color:#5561d6;}
    .att-info{flex:1 1 auto;min-width:0;}
    .att-name{font-weight:800;font-size:15px;color:#222;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .att-sub{font-size:12px;color:#8a8a8a;}
    .att-actions{display:flex;gap:8px;flex:0 0 auto;}
    .att-status-btn{width:44px;height:44px;border-radius:13px;border:1.5px solid;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;background:transparent;padding:0;}
    .att-status-btn svg{width:20px;height:20px;}
    .att-status-btn[data-status="1"]{border-color:var(--att-green);background:var(--att-green-soft);color:var(--att-green);}
    .att-status-btn[data-status="2"]{border-color:var(--att-yellow);background:var(--att-yellow-soft);color:var(--att-yellow);}
    .att-status-btn[data-status="0"]{border-color:var(--att-red);background:var(--att-red-soft);color:var(--att-red);}
    .att-status-btn[data-status="1"].active{background:var(--att-green);color:#fff;}
    .att-status-btn[data-status="2"].active{background:var(--att-yellow);color:#fff;}
    .att-status-btn[data-status="0"].active{background:var(--att-red);color:#fff;}
    .att-empty{text-align:center;color:#9a958d;padding:50px 20px;font-weight:600;}
    @media (min-width:768px){.att-page{max-width:none;padding:22px 26px;}#attList{grid-template-columns:repeat(2,1fr);}}
    @media (min-width:1200px){#attList{grid-template-columns:repeat(3,1fr);}}
</style>

<svg style="display:none" xmlns="http://www.w3.org/2000/svg">
    <symbol id="att-icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></symbol>
    <symbol id="att-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></symbol>
    <symbol id="att-icon-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></symbol>
    <symbol id="att-icon-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
</svg>

<div class="att-page">
    <div class="att-topbar">
        <div class="att-filters">
            @if ($schools->count() > 1)
                <select id="attBranch" class="form-select"
                    onchange="window.location='{{ route('staff-attendance.record', $kind) }}'+(this.value?'?branch_id='+this.value:'')">
                    <option value="">{{ trans('hr.all_branches') }}</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected((string) request('branch_id') === (string) $school->id)>{{ $school->name_school }}</option>
                    @endforeach
                </select>
            @endif
            <input type="date" id="attDate" class="form-control" value="{{ date('Y-m-d') }}">
            <a href="{{ route('staff-attendance.report', array_merge(['kind' => $kind], request()->only('branch_id'))) }}" class="btn btn-outline-primary d-flex align-items-center">{{ trans('hr.attendance_report') }}</a>
        </div>
        <div class="att-bulk">
            <button type="button" class="att-bulk-chip" data-bulk="1"><span style="color:var(--att-green)"><svg><use href="#att-icon-check"/></svg></span>{{ trans('opt.all_present') }}</button>
            <button type="button" class="att-bulk-chip" data-bulk="2"><span style="color:var(--att-yellow)"><svg><use href="#att-icon-clock"/></svg></span>{{ trans('opt.all_late') }}</button>
            <button type="button" class="att-bulk-chip" data-bulk="0"><span style="color:var(--att-red)"><svg><use href="#att-icon-x"/></svg></span>{{ trans('opt.all_absent') }}</button>
        </div>
        <div class="att-meta-row">
            <span class="att-counter"><svg><use href="#att-icon-users"/></svg><span id="attCounter">0 {{ trans('hr.staff_count_of') }} 0</span></span>
            <input type="search" id="attSearch" class="att-search" placeholder="{{ trans('hr.search_staff') }}">
        </div>
    </div>

    <div class="att-legend">
        <span class="att-legend-item"><span class="att-legend-icon present"><svg><use href="#att-icon-check"/></svg></span>{{ trans('opt.present') }}</span>
        <span class="att-legend-item"><span class="att-legend-icon late"><svg><use href="#att-icon-clock"/></svg></span>{{ trans('opt.late') }}</span>
        <span class="att-legend-item"><span class="att-legend-icon absent"><svg><use href="#att-icon-x"/></svg></span>{{ trans('opt.absent') }}</span>
    </div>

    <div id="attList"></div>
    <div id="attEmpty" class="att-empty">{{ trans('hr.select_branch_first') }}</div>
</div>
@endsection

@section('jsa')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dateInput = document.getElementById('attDate');
    const listEl = document.getElementById('attList');
    const emptyEl = document.getElementById('attEmpty');
    const counterEl = document.getElementById('attCounter');
    const searchInput = document.getElementById('attSearch');
    const branchId = @json((string) request('branch_id'));

    const TRANS = {
        ofTotal: @json(trans('hr.staff_count_of')),
        saved: @json(trans('hr.saved')),
        saveError: @json(trans('hr.save_error')),
        noStaff: @json(trans('hr.no_staff')),
        bulkDone: @json(trans('hr.bulk_done')),
        confirmBulk: @json(trans('hr.confirm_bulk')),
        statusNames: { 0:@json(trans('opt.absent')), 1:@json(trans('opt.present')), 2:@json(trans('opt.late')) },
    };
    const ICONS = { 1:'att-icon-check', 2:'att-icon-clock', 0:'att-icon-x' };

    let staff = [], total = 0, recorded = 0;

    function notify(type,msg){ if(window.toastr){toastr[type](msg);} else if(type==='error'){alert(msg);} }
    function initials(n){const p=n.trim().split(/\s+/);return p.length===1?p[0].substring(0,2):p[0].charAt(0)+p[p.length-1].charAt(0);}
    function updateCounter(){ counterEl.textContent = recorded+' '+TRANS.ofTotal+' '+total; }
    function keyOf(s){ return s.type+':'+s.id; }
    function statusButton(st){ return '<button type="button" class="att-status-btn" data-status="'+st+'"><svg><use href="#'+ICONS[st]+'"/></svg></button>'; }

    function render(){
        if(!staff.length){ listEl.innerHTML=''; emptyEl.style.display=''; emptyEl.textContent=TRANS.noStaff; updateCounter(); return; }
        emptyEl.style.display='none';
        listEl.innerHTML = staff.map(function(s){
            return '<div class="att-card" data-key="'+keyOf(s)+'" data-search="'+(s.name+' '+(s.subtitle||'')).toLowerCase()+'">'+
                '<span class="att-avatar '+(s.type==='employee'?'emp':'')+'">'+initials(s.name)+'</span>'+
                '<div class="att-info"><div class="att-name">'+s.name+'</div>'+(s.subtitle?'<div class="att-sub">'+s.subtitle+'</div>':'')+'</div>'+
                '<div class="att-actions">'+statusButton(1)+statusButton(2)+statusButton(0)+'</div></div>';
        }).join('');
        refreshActive(); applySearch(); updateCounter();
    }
    function refreshActive(){
        staff.forEach(function(s){
            const card=listEl.querySelector('.att-card[data-key="'+keyOf(s)+'"]'); if(!card) return;
            card.querySelectorAll('.att-status-btn').forEach(function(btn){ btn.classList.toggle('active', parseInt(btn.dataset.status,10)===s.status); });
        });
    }
    function applySearch(){
        const q=searchInput.value.trim().toLowerCase();
        listEl.querySelectorAll('.att-card').forEach(function(c){ c.style.display=(!q||c.dataset.search.indexOf(q)!==-1)?'':'none'; });
    }
    function recompute(){ recorded = staff.filter(function(s){return s.has_record;}).length; }

    function loadData(){
        const date=dateInput.value; if(!date){ return; }
        listEl.innerHTML='<div class="att-empty"><div class="spinner-border text-success"></div></div>'; emptyEl.style.display='none';
        let url="{{ route('staff-attendance.data', $kind) }}?date="+encodeURIComponent(date);
        if(branchId){ url+="&branch_id="+encodeURIComponent(branchId); }
        fetch(url,{headers:{'Accept':'application/json'}})
            .then(function(r){ if(!r.ok) throw new Error(); return r.json(); })
            .then(function(d){ staff=d.staff; total=d.total; recorded=d.recorded; render(); })
            .catch(function(){ staff=[];total=0;recorded=0; render(); notify('error',TRANS.saveError); });
    }

    dateInput.addEventListener('change', loadData);

    listEl.addEventListener('click', function(e){
        const btn=e.target.closest('.att-status-btn'); if(!btn) return;
        const card=btn.closest('.att-card'); const key=card.dataset.key; const status=parseInt(btn.dataset.status,10);
        const s=staff.find(function(x){return keyOf(x)===key;}); if(!s||s.status===status) return;
        const prev=s.status, hadRecord=s.has_record;
        s.status=status; if(!s.has_record){s.has_record=true;} recompute(); refreshActive(); updateCounter();
        fetch("{{ route('staff-attendance.update') }}",{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body:JSON.stringify({ staff_type:s.type, staff_id:s.id, status:status, date:dateInput.value })
        }).then(function(r){ if(!r.ok) throw new Error(); return r.json(); })
          .then(function(){ notify('success',TRANS.saved); })
          .catch(function(){ s.status=prev; s.has_record=hadRecord; recompute(); refreshActive(); updateCounter(); notify('error',TRANS.saveError); });
    });

    document.querySelectorAll('.att-bulk-chip').forEach(function(chip){
        chip.addEventListener('click', function(){
            if(!staff.length) return;
            const status=parseInt(this.dataset.bulk,10);
            if(!confirm(TRANS.confirmBulk.replace(':status',TRANS.statusNames[status]))) return;
            const body={ status:status, date:dateInput.value }; if(branchId){ body.branch_id=branchId; }
            fetch("{{ route('staff-attendance.bulk', $kind) }}",{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                body:JSON.stringify(body)
            }).then(function(r){ if(!r.ok) throw new Error(); return r.json(); })
              .then(function(){ staff.forEach(function(s){ s.status=status; s.has_record=true; }); recompute(); refreshActive(); updateCounter(); notify('success',TRANS.bulkDone); })
              .catch(function(){ notify('error',TRANS.saveError); });
        });
    });

    searchInput.addEventListener('input', applySearch);
    loadData();
});
</script>
@endsection
