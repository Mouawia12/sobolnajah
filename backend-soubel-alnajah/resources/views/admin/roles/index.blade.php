@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('roles.title') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">

        {{-- روابط إدارة المستخدمين --}}
        <div class="box">
            <div class="box-body d-flex flex-wrap gap-2 align-items-center">
                <strong class="me-2">{{ trans('roles.manage_users') }}:</strong>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> {{ trans('roles.create_user') }}</a>
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-users-cog"></i> {{ trans('roles.assign_roles') }}</a>
            </div>
        </div>

        {{-- الأدوار + إنشاء دور --}}
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('roles.roles_list') }}</h4>
                <a href="javascript:void(0);" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-create-role"><i class="fa fa-plus"></i> {{ trans('roles.create_role') }}</a>
            </div>
            <div class="box-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <span class="badge {{ $role->name === 'admin' ? 'bg-dark' : 'bg-info-light' }} p-2 d-inline-flex align-items-center gap-2">
                            {{ $role->display_name ?: $role->name }} <small class="text-muted" dir="ltr">({{ $role->name }})</small>
                            @if (!in_array($role->name, $protectedRoles, true))
                                <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="d-inline" onsubmit="return confirm('{{ trans('opt.deletemsg') }}');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger" title="{{ trans('roles.delete') }}"><i class="fa fa-times"></i></button>
                                </form>
                            @else
                                <small class="text-muted">{{ trans('roles.core') }}</small>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- جدول الصلاحيات --}}
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('roles.permissions_matrix') }}</h4>
                <p class="text-muted mb-0">{{ trans('roles.permissions_hint') }}</p>
            </div>
            <div class="box-body">
                <div class="alert alert-dark py-2"><i class="fa fa-shield"></i> {{ trans('roles.admin_full') }}</div>

                <form method="POST" action="{{ route('roles.permissions.save') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width:200px;text-align:start;">{{ trans('roles.section') }}</th>
                                    @foreach ($editableRoles as $role)
                                        <th>{{ $role->display_name ?: $role->name }}<br><small class="text-muted" dir="ltr">{{ $role->name }}</small></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sections as $key => $section)
                                    <tr>
                                        <td class="fw-bold" style="text-align:start;">{{ $section['label'] }}</td>
                                        @foreach ($editableRoles as $role)
                                            @php $isChecked = in_array($key, $granted[$role->id] ?? [], true); @endphp
                                            <td>
                                                <input type="checkbox" name="perms[{{ $role->id }}][]" value="{{ $key }}" @checked($isChecked)>
                                            </td>
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

{{-- مودال إنشاء دور --}}
<div class="modal fade" id="modal-create-role" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">{{ trans('roles.create_role') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body text-start">
            <div class="mb-3">
                <label class="form-label">{{ trans('roles.display_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="display_name" class="form-control" required>
            </div>
            <div class="mb-1">
                <label class="form-label">{{ trans('roles.slug') }}</label>
                <input type="text" name="name" class="form-control" dir="ltr" placeholder="e.g. censeur">
                <small class="text-muted">{{ trans('roles.slug_hint') }}</small>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button type="submit" class="btn btn-success">{{ trans('roles.create_role') }}</button></div>
    </form>
</div></div></div>
@endsection
