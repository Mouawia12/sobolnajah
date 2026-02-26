@extends('layoutsadmin.masteradmin')

@section('titlea')
    {{ trans('student.studentlist') }}
@stop

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box box-slided-down">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>{{ trans('student.studentspromotion') }}</strong></h4>
                <ul class="box-controls pull-right">
                    <li><a class="box-btn-slide text-white" href="#"></a></li>
                    <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                </ul>
            </div>

            <div class="box-body">
                <form method="POST" id="promotion" action="{{ route('NoteStudents.store') }}" enctype="multipart/form-data" class="admin-form-panel">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label class="form-label">{{ trans('inscription.student') }}</label>
                            <select id="student" class="form-select" name="student_id" required>
                                <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                @foreach ($UploadStudents as $studentOption)
                                    <option value="{{ $studentOption->id }}">
                                        #{{ $studentOption->id }} - {{ $studentOption->prenom }} {{ $studentOption->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label class="form-label">{{ trans('school.Annéescolaire') }}</label>
                            <input class="form-control" name="note_file" type="file" id="formFile" accept="application/pdf" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label class="form-label">{{ trans('school.Annéescolaire') }}</label>
                            <select id="Anneescolaire" class="form-select" name="Anneescolaire" required>
                                <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                <option value="1">سداسي الاول</option>
                                <option value="2">السداسي الثاني</option>
                                <option value="3">السداسي الثالث</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-form-actions mt-10">
                        <button type="submit" class="btn btn-primary">{{ trans('opt.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box-body">
            <form method="GET" action="{{ route('NoteStudents.show', $section->id) }}" class="admin-form-panel mb-15">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم التلميذ / البريد / الهاتف">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">حالة الملفات</label>
                        <select name="has_notes" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" @selected(request('has_notes') === '1')>لديه ملفات</option>
                            <option value="0" @selected(request('has_notes') === '0')>بدون ملفات</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" type="submit">بحث</button>
                        <a href="{{ route('NoteStudents.show', $section->id) }}" class="btn btn-light">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th>{{ trans('school.season1') }}</th>
                            <th>{{ trans('school.season2') }}</th>
                            <th>{{ trans('school.season3') }}</th>
                            <th>{{ trans('inscription.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($i = ($StudentInfo->currentPage() - 1) * $StudentInfo->perPage())
                        @forelse ($StudentInfo as $student)
                            @php
                                $i++;
                                $noteStudent = $student->noteStudent;
                            @endphp
                            <tr>
                                <td class="col-md-1">{{ $i }}</td>
                                <td class="col-md-3">
                                    <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ $student->prenom }} {{ $student->nom }}</a>
                                    <span class="text-fade d-block">{{ $student->user->email ?? '-' }}</span>
                                    <span class="text-fade d-block">{{ $student->numtelephone }}</span>
                                </td>

                                @foreach (['urlfile1', 'urlfile2', 'urlfile3'] as $column)
                                    <td class="col-md-2">
                                        @if($noteStudent && $noteStudent->{$column})
                                            <a href="{{ route('DisplayNoteFromAdmin', ['url' => $noteStudent->{$column}]) }}" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info">
                                                <span class="fa fa-eye"></span>
                                            </a>
                                            <a href="{{ route('DownloadNoteFromAdmin', ['url' => $noteStudent->{$column}]) }}" class="waves-effect waves-light btn btn-success btn-circle">
                                                <span class="fa fa-download"></span>
                                            </a>
                                        @else
                                            <a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info-light disabled">
                                                <span class="fa fa-eye"></span>
                                            </a>
                                            <a href="#" class="waves-effect waves-light btn btn-success-light btn-circle disabled">
                                                <span class="fa fa-download"></span>
                                            </a>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="col-md-2">
                                    @if($noteStudent)
                                        <a data-bs-target="#modal-delete{{ $noteStudent->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-danger-light btn-circle">
                                            <span class="icon-Trash1 fs-18"></span>
                                        </a>

                                        <div class="modal center-modal fade" id="modal-delete{{ $noteStudent->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        <form id="delete-form{{ $noteStudent->id }}" action="{{ route('NoteStudents.destroy', $noteStudent->id) }}" method="POST">
                                                            {{ method_field('Delete') }}
                                                            @csrf
                                                            <div class="box-body">
                                                                <div class="row">
                                                                    <h1>{{ trans('opt.deletemsg') }}</h1>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer modal-footer-uniform">
                                                        <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                                        <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault(); document.getElementById('delete-form{{ $noteStudent->id }}').submit();">{{ trans('opt.delete2') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="admin-empty-state">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"><div class="admin-empty-state">لا توجد نتائج مطابقة.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-15 d-flex justify-content-end">
                {{ $StudentInfo->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
@endsection
