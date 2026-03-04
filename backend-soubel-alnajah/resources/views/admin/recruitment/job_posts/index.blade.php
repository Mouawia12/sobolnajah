@extends('layoutsadmin.masteradmin')
@section('titlea', trans('recruitment.job_posts'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between">
                <h4 class="box-title">{{ trans('recruitment.job_posts') }}</h4>
                <a href="{{ route('JobPosts.create') }}" class="btn btn-info">{{ trans('recruitment.admin.add_job_post') }}</a>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ trans('recruitment.admin.search_title') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">{{ trans('recruitment.admin.all_statuses') }}</option>
                            @foreach (['draft', 'published', 'closed'] as $key)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ trans('recruitment.statuses.job.' . $key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit">{{ trans('recruitment.admin.filter') }}</button>
                        <a href="{{ route('JobPosts.index') }}" class="btn btn-outline-secondary">{{ trans('recruitment.admin.reset') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('recruitment.admin.title') }}</th>
                            <th>{{ trans('recruitment.admin.status') }}</th>
                            <th>{{ trans('recruitment.admin.applications_count') }}</th>
                            <th>{{ trans('recruitment.admin.published_at') }}</th>
                            <th>{{ trans('recruitment.admin.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($jobPosts as $index => $jobPost)
                            <tr>
                                <td>{{ $jobPosts->firstItem() + $index }}</td>
                                <td>{{ $jobPost->title }}</td>
                                <td><span class="admin-status admin-status-{{ $jobPost->status }}">{{ trans('recruitment.statuses.job.' . $jobPost->status) }}</span></td>
                                <td>{{ $jobPost->applications_count }}</td>
                                <td>{{ $jobPost->published_at ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('JobPosts.edit', $jobPost) }}" class="btn btn-sm btn-primary">{{ trans('recruitment.admin.edit') }}</a>
                                    <form method="POST" action="{{ route('JobPosts.destroy', $jobPost) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('{{ trans('recruitment.admin.delete_confirm') }}')">{{ trans('recruitment.admin.delete') }}</button>
                                    </form>
                                    <a href="{{ route('recruitment.applications.index', ['post_id' => $jobPost->id]) }}" class="btn btn-sm btn-info">{{ trans('recruitment.admin.view_applications') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="admin-empty-state">{{ trans('recruitment.admin.empty_job_posts') }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $jobPosts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
