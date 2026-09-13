@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('academic.assessments') }}
@stop
@endsection

@section('contenta')
@php
    $typeLabels = ['devoir' => trans('academic.type_devoir'), 'exam' => trans('academic.type_exam')];
@endphp
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('academic.assessments') }}</h4>
                <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-assessment">
                    <i class="fa fa-plus"></i> {{ trans('academic.add_assessment') }}
                </a>
            </div>

            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('academic.title') }}</th>
                                <th>{{ trans('academic.type') }}</th>
                                <th>{{ trans('academic.subject') }}</th>
                                <th>{{ trans('academic.section') }}</th>
                                <th>{{ trans('academic.date') }}</th>
                                <th>{{ trans('academic.marks_count') }}</th>
                                <th>{{ trans('academic.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assessments as $index => $a)
                                <tr>
                                    <td>{{ $assessments->firstItem() + $index }}</td>
                                    <td class="fw-bold">{{ $a->title }}
                                        @if($a->term)<span class="badge bg-info-light d-block mt-1">{{ trans('academic.term') }} {{ $a->term }}</span>@endif
                                    </td>
                                    <td><span class="badge {{ $a->type === 'exam' ? 'bg-danger' : 'bg-primary' }}">{{ $typeLabels[$a->type] ?? $a->type }}</span></td>
                                    <td>{{ optional($a->specialization)->name ?? '—' }}</td>
                                    <td>{{ optional($a->section)->name_section ?? '—' }}
                                        <small class="d-block text-muted">{{ optional(optional($a->section)->classroom)->name_class ?? '' }}</small>
                                    </td>
                                    <td dir="ltr">{{ $a->date }}</td>
                                    <td>{{ $a->marks_count }} <small class="text-muted">/ {{ (int) $a->max_mark }}</small></td>
                                    <td>
                                        <a href="{{ route('assessments.marks', $a->id) }}" class="btn btn-primary-light btn-sm" title="{{ trans('academic.enter_marks') }}">
                                            <i class="fa fa-edit"></i> {{ trans('academic.enter_marks') }}
                                        </a>
                                        <a href="{{ route('assessments.results', ['section' => $a->section_id, 'specialization_id' => $a->specialization_id, 'term' => $a->term]) }}" target="_blank" class="btn btn-secondary-light btn-sm" title="{{ trans('academic.results_sheet') }}">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger-light btn-sm" data-bs-toggle="modal" data-bs-target="#modal-del-assessment-{{ $a->id }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modal-del-assessment-{{ $a->id }}" tabindex="-1">
                                    <div class="modal-dialog"><div class="modal-content">
                                        <form method="POST" action="{{ route('assessments.destroy', $a->id) }}">
                                            @csrf @method('DELETE')
                                            <div class="modal-body"><h5 class="text-danger">{{ trans('opt.deletemsg') }}</h5><p class="mb-0">{{ $a->title }}</p></div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
                                                <button type="submit" class="btn btn-danger">{{ trans('opt.delete2') }}</button>
                                            </div>
                                        </form>
                                    </div></div>
                                </div>
                            @empty
                                <tr><td colspan="8" class="text-muted py-4">{{ trans('academic.no_assessments') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $assessments->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-assessment" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('assessments.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('academic.add_assessment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="form-label">{{ trans('academic.section') }} <span class="text-danger">*</span></label>
                    <select name="section_id" class="form-select" required>
                        <option value="" disabled selected>{{ trans('academic.choose_section') }}</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">
                                {{ optional($section->classroom)->name_class ?? '' }} / {{ $section->name_section }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @unless ($isTeacherOnly)
                    <div class="mb-3">
                        <label class="form-label">{{ trans('academic.subject') }}</label>
                        <select name="specialization_id" class="form-select">
                            <option value="">{{ trans('academic.choose_subject') }}</option>
                            @foreach ($specializations as $spec)
                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless

                <div class="mb-3">
                    <label class="form-label">{{ trans('academic.title') }} <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="{{ trans('academic.type_devoir') }} 1">
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ trans('academic.type') }}</label>
                        <select name="type" class="form-select">
                            <option value="devoir">{{ trans('academic.type_devoir') }}</option>
                            <option value="exam">{{ trans('academic.type_exam') }}</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ trans('academic.term') }}</label>
                        <select name="term" class="form-select">
                            <option value="">—</option>
                            <option value="1">{{ trans('academic.term_1') }}</option>
                            <option value="2">{{ trans('academic.term_2') }}</option>
                            <option value="3">{{ trans('academic.term_3') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-4 mb-3">
                        <label class="form-label">{{ trans('academic.max_mark') }}</label>
                        <input type="number" step="0.25" min="1" max="100" name="max_mark" class="form-control" value="20">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">{{ trans('academic.coefficient') }}</label>
                        <input type="number" step="0.5" min="0.5" max="20" name="coefficient" class="form-control" value="1">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">{{ trans('academic.date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
                <button type="submit" class="btn btn-primary">{{ trans('opt.save') }}</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
