@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('academic.enter_marks') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ $assessment->title }}</h4>
                <div class="text-muted">
                    {{ optional($assessment->specialization)->name }} ·
                    {{ optional(optional($assessment->section)->classroom)->name_class }} / {{ optional($assessment->section)->name_section }}
                    · {{ trans('academic.max_mark') }}: <strong>{{ (int) $assessment->max_mark }}</strong>
                    · {{ trans('academic.coefficient') }}: <strong>{{ (float) $assessment->coefficient }}</strong>
                </div>
            </div>

            <div class="box-body">
                <form method="POST" action="{{ route('assessments.marks.store', $assessment->id) }}">
                    @csrf
                    @if ($students->isEmpty())
                        <p class="text-muted py-4 text-center">{{ trans('academic.no_students') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" style="max-width:760px;margin-inline:auto;">
                                <thead>
                                    <tr>
                                        <th style="width:48px;">#</th>
                                        <th>{{ trans('academic.student') }}</th>
                                        <th style="width:150px;">{{ trans('academic.mark') }} / {{ (int) $assessment->max_mark }}</th>
                                        <th style="width:90px;">{{ trans('academic.absent') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $i => $student)
                                        @php $m = $existing->get($student->id); @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="text-start fw-600">{{ $student->prenom }} {{ $student->nom }}
                                                @if($student->national_id)<small class="text-muted d-block" dir="ltr">{{ $student->national_id }}</small>@endif
                                            </td>
                                            <td>
                                                <input type="number" step="0.25" min="0" max="{{ (float) $assessment->max_mark }}"
                                                    name="mark[{{ $student->id }}]" class="form-control text-center mark-input"
                                                    value="{{ $m && !$m->is_absent && $m->mark !== null ? (float) $m->mark : '' }}"
                                                    @if($m && $m->is_absent) disabled @endif>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="absent[{{ $student->id }}]" value="1"
                                                    class="absent-toggle" @checked($m && $m->is_absent)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('assessments.index') }}" class="btn btn-secondary">{{ trans('opt.close') }}</a>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> {{ trans('academic.save') }}</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // تعطيل خانة النقطة عند تعليم الغياب
    document.querySelectorAll('.absent-toggle').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var row = cb.closest('tr');
            var input = row.querySelector('.mark-input');
            if (cb.checked) { input.value = ''; input.disabled = true; }
            else { input.disabled = false; }
        });
    });
});
</script>
@endsection
