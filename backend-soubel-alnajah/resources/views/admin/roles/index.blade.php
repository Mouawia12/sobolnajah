@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('roles.title') }}
@stop
@endsection

@section('contenta')
@php
    $rolesForJs = $roles->map(fn ($r) => ['name' => $r->name, 'label' => $r->display_name ?: $r->name])->values();
@endphp
<style>
    .rp-toast{position:fixed;top:80px;inset-inline-end:20px;z-index:2000;min-width:240px;padding:12px 18px;border-radius:12px;color:#fff;font-weight:700;box-shadow:0 6px 20px rgba(0,0,0,.2);display:none;}
    .rp-toast.ok{background:#2e9e5b;} .rp-toast.err{background:#e0533d;}
    .rp-role-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:20px;background:#eef4fb;font-weight:700;}
    .rp-role-badge.admin{background:#12355b;color:#fff;}
    .rp-matrix th,.rp-matrix td{text-align:center;vertical-align:middle;}
    .rp-matrix th:first-child,.rp-matrix td:first-child{text-align:start;}
    .nav-tabs .nav-link{font-weight:700;}
    /* القالب يخفي checkbox الخام؛ نفرض ظهورها داخل هذه الصفحة ومودالاتها. */
    .tab-content input[type="checkbox"],
    #userRolesModal input[type="checkbox"],
    #userPassModal input[type="checkbox"]{
        position:static !important;opacity:1 !important;pointer-events:auto !important;
        appearance:auto !important;-webkit-appearance:auto !important;
        width:18px;height:18px;margin-inline:4px;vertical-align:middle;cursor:pointer;
    }
</style>

<div class="rp-toast" id="rpToast"></div>

<div class="row"><div class="col-12">
    <div class="box">
        <div class="box-header with-border">
            <h4 class="box-title">{{ trans('roles.title') }}</h4>
            <p class="text-muted mb-0">{{ trans('roles.subtitle') }}</p>
        </div>
        <div class="box-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-users">👥 {{ trans('roles.users_tab') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-roles">🛡️ {{ trans('roles.roles_tab') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-perms">✅ {{ trans('roles.permissions_tab') }}</a></li>
            </ul>

            <div class="tab-content">

                {{-- ===================== المستخدمون ===================== --}}
                <div class="tab-pane fade show active" id="tab-users">
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <input type="search" id="userSearch" class="form-control" style="max-width:340px;" placeholder="{{ trans('roles.search_users') }}">
                        <button type="button" class="btn btn-primary ms-auto" id="openCreateUser">
                            <i class="fa fa-user-plus"></i> {{ trans('roles.new_user') }}
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>{{ trans('roles.full_name') }}</th>
                                    <th>{{ trans('roles.email') }}</th>
                                    <th>{{ trans('roles.roles') }}</th>
                                    <th style="width:170px;">{{ trans('roles.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="usersTbody">
                                <tr><td colspan="4" class="text-center text-muted py-4">…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ===================== الأدوار ===================== --}}
                <div class="tab-pane fade" id="tab-roles">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="card border">
                                <div class="card-header fw-bold">{{ trans('roles.create_role') }}</div>
                                <div class="card-body">
                                    <form id="createRoleForm">
                                        <div class="mb-2">
                                            <label class="form-label">{{ trans('roles.display_name') }}</label>
                                            <input type="text" name="display_name" class="form-control" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">{{ trans('roles.slug') }}</label>
                                            <input type="text" name="name" class="form-control" dir="ltr" placeholder="e.g. censeur">
                                            <small class="text-muted">{{ trans('roles.slug_hint') }}</small>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100"><i class="fa fa-plus"></i> {{ trans('roles.create_role') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card border h-100">
                                <div class="card-header fw-bold">{{ trans('roles.roles_list') }}</div>
                                <div class="card-body">
                                    <div id="rolesList" class="d-flex flex-wrap gap-2">
                                        @foreach ($roles as $role)
                                            <span class="rp-role-badge {{ $role->name === 'admin' ? 'admin' : '' }}" data-role-id="{{ $role->id }}">
                                                {{ $role->display_name ?: $role->name }} <small class="text-muted" dir="ltr">({{ $role->name }})</small>
                                                @if (!in_array($role->name, $protectedRoles, true))
                                                    <button class="btn btn-xs btn-danger rp-del-role" data-id="{{ $role->id }}"><i class="fa fa-times"></i></button>
                                                @else
                                                    <small class="text-muted">{{ trans('roles.core') }}</small>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================== الصلاحيات ===================== --}}
                <div class="tab-pane fade" id="tab-perms">
                    <div class="alert alert-dark py-2"><i class="fa fa-shield"></i> {{ trans('roles.admin_full') }}</div>
                    <p class="text-muted">{{ trans('roles.permissions_hint') }}</p>
                    <form id="permsForm">
                        <div class="table-responsive">
                            <table class="table table-bordered rp-matrix">
                                <thead>
                                    <tr>
                                        <th style="min-width:200px;">{{ trans('roles.section') }}</th>
                                        @foreach ($editableRoles as $role)
                                            <th>{{ $role->display_name ?: $role->name }}<br><small class="text-muted" dir="ltr">{{ $role->name }}</small></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sections as $key => $section)
                                        <tr>
                                            <td class="fw-bold">{{ $section['label'] }}</td>
                                            @foreach ($editableRoles as $role)
                                                <td><input type="checkbox" name="perms[{{ $role->id }}][]" value="{{ $key }}" @checked(in_array($key, $granted[$role->id] ?? [], true))></td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('roles.save') }}</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div></div>

{{-- مودال إنشاء مستخدم جديد بدور واحد + بيانات حسب الدور --}}
<div class="modal fade" id="createUserModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="createUserForm">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fa fa-user-plus"></i> {{ trans('roles.create_user_title') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ trans('roles.full_name') }}</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ trans('roles.email') }}</label>
                    <input type="email" name="email" class="form-control" dir="ltr" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ trans('roles.password') }}</label>
                    <input type="text" name="password" class="form-control" dir="ltr" minlength="6" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ trans('roles.role') }}</label>
                    <select name="role" id="cuRole" class="form-select">
                        <option value="">{{ trans('roles.no_role') }}</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}" data-core="{{ in_array($r->name, $coreRoleNames, true) ? '1' : '0' }}">{{ $r->display_name ?: $r->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ trans('roles.single_role_hint') }}</small>
                </div>
            </div>

            {{-- تلميح لدور مخصّص --}}
            <div class="alert alert-info mt-3 d-none" id="cuCustomHint"><i class="fa fa-info-circle"></i> {{ trans('roles.custom_role_hint') }}</div>

            {{-- بيانات المعلّم (اختيارية) --}}
            <div class="cu-role-fields d-none mt-3" data-role="teacher">
                <h6 class="fw-bold">{{ trans('roles.teacher_data') }}</h6>
                <p class="text-muted small mb-2">{{ trans('roles.teacher_data_hint') }}</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.specialization') }}</label>
                        <select name="specialization_id" class="form-select">
                            <option value="">{{ trans('roles.choose') }}</option>
                            @foreach ($specializations as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.gender') }}</label>
                        <select name="gender_teacher" class="form-select">
                            <option value="">{{ trans('roles.choose') }}</option>
                            <option value="1">{{ trans('roles.male') }}</option>
                            <option value="0">{{ trans('roles.female') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.joining_date') }}</label>
                        <input type="date" name="joining_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ trans('roles.address') }}</label>
                        <input type="text" name="address_teacher" class="form-control">
                    </div>
                </div>
            </div>

            {{-- بيانات الولي --}}
            <div class="cu-role-fields d-none mt-3" data-role="guardian">
                <h6 class="fw-bold">{{ trans('roles.guardian_data') }}</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.relation') }}</label>
                        <input type="text" name="guardian_relation" class="form-control" placeholder="{{ trans('roles.male') }} / {{ trans('roles.female') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.phone') }}</label>
                        <input type="text" name="guardian_phone" class="form-control" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.address') }}</label>
                        <input type="text" name="address_guardian" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.wilaya') }}</label>
                        <input type="text" name="guardian_wilaya" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.dayra') }}</label>
                        <input type="text" name="guardian_dayra" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.baladia') }}</label>
                        <input type="text" name="guardian_baladia" class="form-control">
                    </div>
                </div>
            </div>

            {{-- بيانات التلميذ --}}
            <div class="cu-role-fields d-none mt-3" data-role="student">
                <h6 class="fw-bold">{{ trans('roles.student_data') }}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('roles.guardian') }} <span class="text-danger">*</span></label>
                        <select name="guardian_user_id" class="form-select">
                            <option value="">{{ trans('roles.choose') }}</option>
                            @foreach ($guardians as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} — {{ $g->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('roles.section') }} <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-select">
                            <option value="">{{ trans('roles.choose') }}</option>
                            @foreach ($classSections as $sec)
                                <option value="{{ $sec->id }}">{{ optional(optional($sec->classroom)->schoolgrade)->name_grade ?? '-' }} / {{ optional($sec->classroom)->name_class ?? '-' }} / {{ $sec->name_section }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.gender') }}</label>
                        <select name="gender_student" class="form-select">
                            <option value="">{{ trans('roles.choose') }}</option>
                            <option value="1">{{ trans('roles.male') }}</option>
                            <option value="0">{{ trans('roles.female') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.phone') }}</label>
                        <input type="text" name="student_phone" class="form-control" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('roles.birth_date') }}</label>
                        <input type="date" name="student_birth_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('roles.birth_place') }}</label>
                        <input type="text" name="student_birth_place" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('roles.wilaya') }}</label>
                        <input type="text" name="student_wilaya" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('roles.dayra') }}</label>
                        <input type="text" name="student_dayra" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('roles.baladia') }}</label>
                        <input type="text" name="student_baladia" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('roles.create_user') }}</button>
        </div>
    </form>
</div></div></div>

{{-- مودالات المستخدم (أدوار / كلمة مرور) --}}
<div class="modal fade" id="userRolesModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ trans('roles.edit_roles') }} — <span id="urName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">{{ trans('roles.role') }}</label>
        <select id="urRole" class="form-select"></select>
        <small class="text-muted">{{ trans('roles.single_role_hint') }}</small>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button class="btn btn-primary" id="urSave">{{ trans('roles.save_roles') }}</button></div>
</div></div></div>

<div class="modal fade" id="userPassModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">{{ trans('roles.reset_password') }} — <span id="upName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">{{ trans('roles.new_password') }}</label>
        <input type="text" id="upPassword" class="form-control" dir="ltr" minlength="6">
        <small class="text-muted">{{ trans('accounts.reset_hint') }}</small>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button class="btn btn-warning" id="upSave">{{ trans('roles.save') }}</button></div>
</div></div></div>
@endsection

@section('jsa')
<script>
(function () {
    'use strict';
    const CSRF = '{{ csrf_token() }}';
    const ROLES = @json($rolesForJs);
    const PROTECTED = @json($protectedRoles);
    const CURRENT_USER_ID = {{ (int) $currentUserId }};
    const T = { you: @json(trans('roles.you')), core: @json(trans('roles.core')), noUsers: @json(trans('roles.no_users')), noRole: @json(trans('roles.no_role')), confirmDel: @json(trans('opt.deletemsg')) };
    const URL = {
        usersData: "{{ route('roles.users.data') }}",
        storeUser: "{{ route('roles.users.store') }}",
        userRoles: "{{ route('roles.users.roles', ['user' => 'UID']) }}",
        userReset: "{{ route('roles.users.reset', ['user' => 'UID']) }}",
        userDestroy: "{{ route('roles.users.destroy', ['user' => 'UID']) }}",
        storeRole: "{{ route('roles.store') }}",
        destroyRole: "{{ route('roles.destroy', ['role' => 'RID']) }}",
        savePerms: "{{ route('roles.permissions.save') }}",
    };

    const toastEl = document.getElementById('rpToast');
    function toast(msg, ok = true) {
        toastEl.textContent = msg;
        toastEl.className = 'rp-toast ' + (ok ? 'ok' : 'err');
        toastEl.style.display = 'block';
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => { toastEl.style.display = 'none'; }, 3000);
    }

    async function api(url, method, body) {
        const res = await fetch(url, {
            method: method || 'GET',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : undefined,
        });
        let data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok) { throw (data && data.message) ? data.message : ('خطأ ' + res.status); }
        return data;
    }

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }
    function roleLabel(name) { const r = ROLES.find(x => x.name === name); return r ? r.label : name; }

    /* ---------- المستخدمون ---------- */
    const usersTbody = document.getElementById('usersTbody');
    let currentRolesUser = null, currentPassUser = null;

    function renderUsers(users) {
        if (!users.length) { usersTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">' + T.noUsers + '</td></tr>'; return; }
        usersTbody.innerHTML = users.map(u => {
            const badges = (u.roles || []).map(r => '<span class="badge bg-info-light">' + esc(roleLabel(r)) + '</span>').join(' ') || '<span class="text-muted">—</span>';
            const del = (u.id === CURRENT_USER_ID)
                ? '<span class="badge bg-secondary">' + T.you + '</span>'
                : '<button class="btn btn-sm btn-danger-light rp-user-del" data-id="' + u.id + '"><i class="fa fa-trash"></i></button>';
            return '<tr data-id="' + u.id + '">' +
                '<td class="fw-bold">' + esc(u.name) + '</td>' +
                '<td dir="ltr">' + esc(u.email) + '</td>' +
                '<td data-roles="' + esc((u.roles || []).join(',')) + '">' + badges + '</td>' +
                '<td>' +
                    '<button class="btn btn-sm btn-primary-light rp-user-roles" data-id="' + u.id + '" data-name="' + esc(u.name) + '"><i class="fa fa-user-tag"></i></button> ' +
                    '<button class="btn btn-sm btn-warning-light rp-user-pass" data-id="' + u.id + '" data-name="' + esc(u.name) + '"><i class="fa fa-key"></i></button> ' + del +
                '</td></tr>';
        }).join('');
    }

    async function loadUsers() {
        try {
            const q = document.getElementById('userSearch').value.trim();
            const data = await api(URL.usersData + '?q=' + encodeURIComponent(q));
            renderUsers(data.users);
        } catch (e) { toast(e, false); }
    }

    let searchTimer;
    document.getElementById('userSearch').addEventListener('input', function () {
        clearTimeout(searchTimer); searchTimer = setTimeout(loadUsers, 300);
    });

    /* ---------- مودال إنشاء مستخدم ---------- */
    const createUserForm = document.getElementById('createUserForm');
    const cuRole = document.getElementById('cuRole');
    const cuCustomHint = document.getElementById('cuCustomHint');
    const cuFieldGroups = Array.from(document.querySelectorAll('.cu-role-fields'));
    let createUserModal = null;

    function cuApplyRole() {
        const opt = cuRole.options[cuRole.selectedIndex];
        const role = cuRole.value;
        const isCore = opt ? opt.dataset.core === '1' : false;

        cuFieldGroups.forEach(g => g.classList.toggle('d-none', g.dataset.role !== role));
        // تلميح الدور المخصّص: دور مختار وغير أساسي.
        cuCustomHint.classList.toggle('d-none', !(role && !isCore));
    }

    document.getElementById('openCreateUser').addEventListener('click', function () {
        createUserForm.reset();
        cuApplyRole();
        createUserModal = createUserModal || new bootstrap.Modal(document.getElementById('createUserModal'));
        createUserModal.show();
    });

    cuRole.addEventListener('change', cuApplyRole);

    createUserForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const f = e.target;
        const role = f.role.value;
        const payload = {
            name: f.name.value,
            email: f.email.value,
            password: f.password.value,
            role: role,
        };

        if (role === 'teacher') {
            payload.specialization_id = f.specialization_id.value || null;
            payload.gender = f.gender_teacher.value || null;
            payload.joining_date = f.joining_date.value || null;
            payload.address = f.address_teacher.value || null;
        } else if (role === 'guardian') {
            payload.guardian_relation = f.guardian_relation.value || null;
            payload.guardian_phone = f.guardian_phone.value || null;
            payload.address = f.address_guardian.value || null;
            payload.guardian_wilaya = f.guardian_wilaya.value || null;
            payload.guardian_dayra = f.guardian_dayra.value || null;
            payload.guardian_baladia = f.guardian_baladia.value || null;
        } else if (role === 'student') {
            payload.guardian_user_id = f.guardian_user_id.value || null;
            payload.section_id = f.section_id.value || null;
            payload.gender = f.gender_student.value || null;
            payload.student_phone = f.student_phone.value || null;
            payload.student_birth_date = f.student_birth_date.value || null;
            payload.student_birth_place = f.student_birth_place.value || null;
            payload.student_wilaya = f.student_wilaya.value || null;
            payload.student_dayra = f.student_dayra.value || null;
            payload.student_baladia = f.student_baladia.value || null;
        }

        try {
            await api(URL.storeUser, 'POST', payload);
            toast(@json(trans('roles.user_created')));
            if (createUserModal) createUserModal.hide();
            f.reset();
            cuApplyRole();
            loadUsers();
        } catch (e2) { toast(e2, false); }
    });

    usersTbody.addEventListener('click', function (e) {
        const rolesBtn = e.target.closest('.rp-user-roles');
        const passBtn = e.target.closest('.rp-user-pass');
        const delBtn = e.target.closest('.rp-user-del');

        if (rolesBtn) {
            currentRolesUser = rolesBtn.dataset.id;
            document.getElementById('urName').textContent = rolesBtn.dataset.name;
            const current = (rolesBtn.closest('tr').querySelector('[data-roles]').dataset.roles || '').split(',').filter(Boolean);
            const currentRole = current[0] || '';
            document.getElementById('urRole').innerHTML =
                '<option value="">' + esc(T.noRole) + '</option>' +
                ROLES.map(r => '<option value="' + esc(r.name) + '" ' + (r.name === currentRole ? 'selected' : '') + '>' + esc(r.label) + '</option>').join('');
            new bootstrap.Modal(document.getElementById('userRolesModal')).show();
        } else if (passBtn) {
            currentPassUser = passBtn.dataset.id;
            document.getElementById('upName').textContent = passBtn.dataset.name;
            document.getElementById('upPassword').value = '';
            new bootstrap.Modal(document.getElementById('userPassModal')).show();
        } else if (delBtn) {
            if (!confirm(T.confirmDel)) return;
            api(URL.userDestroy.replace('UID', delBtn.dataset.id), 'DELETE')
                .then(() => { toast(@json(trans('roles.user_deleted'))); loadUsers(); })
                .catch(er => toast(er, false));
        }
    });

    document.getElementById('urSave').addEventListener('click', async function () {
        const role = document.getElementById('urRole').value;
        try {
            await api(URL.userRoles.replace('UID', currentRolesUser), 'POST', { role: role });
            toast(@json(trans('roles.roles_updated')));
            bootstrap.Modal.getInstance(document.getElementById('userRolesModal')).hide();
            loadUsers();
        } catch (e) { toast(e, false); }
    });

    document.getElementById('upSave').addEventListener('click', async function () {
        const pass = document.getElementById('upPassword').value;
        if (pass.length < 6) { toast('6+', false); return; }
        try {
            await api(URL.userReset.replace('UID', currentPassUser), 'POST', { new_password: pass });
            toast(@json(trans('roles.password_reset_done')));
            bootstrap.Modal.getInstance(document.getElementById('userPassModal')).hide();
        } catch (e) { toast(e, false); }
    });

    /* ---------- الأدوار ---------- */
    document.getElementById('createRoleForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const f = e.target;
        try {
            const data = await api(URL.storeRole, 'POST', { display_name: f.display_name.value, name: f.name.value });
            toast(data.message);
            const span = document.createElement('span');
            span.className = 'rp-role-badge';
            span.dataset.roleId = data.role.id;
            span.innerHTML = esc(data.role.display_name) + ' <small class="text-muted" dir="ltr">(' + esc(data.role.name) + ')</small> ' +
                '<button class="btn btn-xs btn-danger rp-del-role" data-id="' + data.role.id + '"><i class="fa fa-times"></i></button>';
            document.getElementById('rolesList').appendChild(span);
            ROLES.push({ name: data.role.name, label: data.role.display_name });
            f.reset();
            setTimeout(() => location.reload(), 800); // لتحديث جدول الصلاحيات وقوائم الأدوار
        } catch (e2) { toast(e2, false); }
    });

    document.getElementById('rolesList').addEventListener('click', function (e) {
        const btn = e.target.closest('.rp-del-role');
        if (!btn) return;
        if (!confirm(T.confirmDel)) return;
        api(URL.destroyRole.replace('RID', btn.dataset.id), 'DELETE')
            .then(d => { toast(d.message); btn.closest('.rp-role-badge').remove(); setTimeout(() => location.reload(), 600); })
            .catch(er => toast(er, false));
    });

    /* ---------- الصلاحيات ---------- */
    document.getElementById('permsForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const perms = {};
        this.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
            const m = cb.name.match(/perms\[(\d+)\]/);
            if (!m) return;
            (perms[m[1]] = perms[m[1]] || []).push(cb.value);
        });
        try {
            const data = await api(URL.savePerms, 'POST', { perms: perms });
            toast(data.message);
        } catch (e2) { toast(e2, false); }
    });

    loadUsers();
})();
</script>
@endsection
