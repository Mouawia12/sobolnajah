@extends('layoutsadmin.masteradmin')
@section('titlea', trans('recruitment.applications'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('recruitment.applications') }}</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ trans('recruitment.admin.search_application') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">{{ trans('recruitment.admin.all_statuses') }}</option>
                            @foreach (['new', 'in_review', 'accepted', 'rejected'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ trans('recruitment.statuses.application.' . $status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="post_id" class="form-select">
                            <option value="">{{ trans('recruitment.admin.all_job_posts') }}</option>
                            @foreach ($posts as $post)
                                <option value="{{ $post->id }}" @selected((string)request('post_id') === (string)$post->id)>{{ $post->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" type="submit">{{ trans('recruitment.admin.filter') }}</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('recruitment.admin.job_post') }}</th>
                            <th>{{ trans('recruitment.admin.candidate') }}</th>
                            <th>{{ trans('recruitment.admin.phone') }}</th>
                            <th>{{ trans('recruitment.admin.email') }}</th>
                            <th>{{ trans('recruitment.admin.status') }}</th>
                            <th>{{ trans('recruitment.admin.cv') }}</th>
                            <th>{{ trans('recruitment.admin.update') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($applications as $index => $application)
                            <tr>
                                <td>{{ $applications->firstItem() + $index }}</td>
                                <td>{{ optional($application->post)->title }}</td>
                                <td>{{ $application->full_name }}</td>
                                <td>{{ $application->phone }}</td>
                                <td>{{ $application->email ?? '-' }}</td>
                                <td><span class="admin-status admin-status-{{ $application->status }}">{{ trans('recruitment.statuses.application.' . $application->status) }}</span></td>
                                <td>
                                    <a href="{{ route('recruitment.applications.cv', $application) }}" class="btn btn-sm btn-info">{{ trans('recruitment.admin.download') }}</a>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('recruitment.applications.status', $application) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach (['new', 'in_review', 'accepted', 'rejected'] as $status)
                                                <option value="{{ $status }}" @selected($application->status === $status)>{{ trans('recruitment.statuses.application.' . $status) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">{{ trans('recruitment.admin.save_status') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="admin-empty-state">{{ trans('recruitment.admin.empty_applications') }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
