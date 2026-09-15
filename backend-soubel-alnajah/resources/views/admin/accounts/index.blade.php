@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('accounts.title') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('accounts.title') }}</h4>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('main_sidebar.addstudent') ?? 'إضافة مستخدم' }}</a>
            </div>

            <div class="box-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ trans('accounts.search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">{{ trans('accounts.all_roles') }}</option>
                            @foreach ($roles as $key => $label)
                                <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button class="btn btn-primary" type="submit">{{ trans('accounts.filter') }}</button>
                        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">{{ trans('opt.close') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('accounts.name') }}</th>
                                <th>{{ trans('accounts.email') }}</th>
                                <th>{{ trans('accounts.roles') }}</th>
                                <th>{{ trans('accounts.school') }}</th>
                                <th>{{ trans('accounts.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $index => $user)
                                @php $userRoles = $user->roles->pluck('name')->all(); @endphp
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td class="fw-bold">{{ $user->name }}</td>
                                    <td dir="ltr">{{ $user->email }}</td>
                                    <td>
                                        @forelse ($user->roles as $role)
                                            <span class="badge bg-info-light">{{ $roles[$role->name] ?? $role->name }}</span>
                                        @empty
                                            <span class="text-muted">{{ trans('accounts.no_roles') }}</span>
                                        @endforelse
                                    </td>
                                    <td>{{ optional($user->school)->name_school ?? '—' }}</td>
                                    <td>
                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm" data-bs-toggle="modal" data-bs-target="#roles-{{ $user->id }}"><i class="fa fa-user-tag"></i> {{ trans('accounts.edit_roles') }}</a>
                                        <a href="javascript:void(0);" class="btn btn-warning-light btn-sm" data-bs-toggle="modal" data-bs-target="#pass-{{ $user->id }}"><i class="fa fa-key"></i> {{ trans('accounts.reset_password') }}</a>
                                        @if ($user->id !== $currentUserId)
                                            <a href="javascript:void(0);" class="btn btn-danger-light btn-sm" data-bs-toggle="modal" data-bs-target="#del-{{ $user->id }}"><i class="fa fa-trash"></i></a>
                                        @endif
                                    </td>
                                </tr>

                                {{-- مودال الأدوار --}}
                                <div class="modal fade" id="roles-{{ $user->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="{{ route('accounts.roles', $user->id) }}">
                                        @csrf
                                        <div class="modal-header"><h5 class="modal-title">{{ trans('accounts.edit_roles') }} — {{ $user->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body text-start">
                                            @foreach ($roles as $key => $label)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $key }}" id="r-{{ $user->id }}-{{ $key }}" @checked(in_array($key, $userRoles, true))>
                                                    <label class="form-check-label" for="r-{{ $user->id }}-{{ $key }}">{{ $label }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button type="submit" class="btn btn-primary">{{ trans('accounts.save') }}</button></div>
                                    </form>
                                </div></div></div>

                                {{-- مودال كلمة المرور --}}
                                <div class="modal fade" id="pass-{{ $user->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="{{ route('accounts.reset', $user->id) }}">
                                        @csrf
                                        <div class="modal-header"><h5 class="modal-title">{{ trans('accounts.reset_password') }} — {{ $user->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body text-start">
                                            <label class="form-label">{{ trans('accounts.new_password') }}</label>
                                            <input type="text" name="new_password" class="form-control" dir="ltr" required minlength="6">
                                            <small class="text-muted">{{ trans('accounts.reset_hint') }}</small>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button type="submit" class="btn btn-warning">{{ trans('accounts.save') }}</button></div>
                                    </form>
                                </div></div></div>

                                {{-- مودال الحذف --}}
                                @if ($user->id !== $currentUserId)
                                    <div class="modal fade" id="del-{{ $user->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                        <form method="POST" action="{{ route('accounts.destroy', $user->id) }}">
                                            @csrf @method('DELETE')
                                            <div class="modal-body"><h5 class="text-danger">{{ trans('opt.deletemsg') }}</h5><p class="mb-0">{{ $user->name }} — {{ $user->email }}</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button><button type="submit" class="btn btn-danger">{{ trans('opt.delete2') }}</button></div>
                                        </form>
                                    </div></div></div>
                                @endif
                            @empty
                                <tr><td colspan="6" class="text-muted py-4">{{ trans('accounts.no_accounts') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
