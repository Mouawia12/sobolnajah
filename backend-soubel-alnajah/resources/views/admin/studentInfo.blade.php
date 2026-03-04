@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('student.studentlist') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box box-slided-up">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>{{ trans('student.studentspromotion') }}</strong></h4>
                <ul class="box-controls pull-right">
                    <li><a class="box-btn-slide text-white" href="#"></a></li>
                    <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                </ul>
            </div>

            <div class="box-body">
                <form method="POST" id="promotion" action="{{ route('Promotions.store') }}">
                    @csrf
                    <div class="box-body">
                        <h6 style="color: red;">{{ trans('student.oldschoolstage') }}</h6><br>

                        <div class="row">

                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                <select id="school_id" class="form-select" name="school_id"
                                    onchange="console.log($(this).val())" required
                                    data-validation-required-message="This field is sssss">
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    @foreach ($School as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.niveau') }}</label>
                                <select id="grade_id" class="form-select" name="grade_id"
                                    onchange="console.log($(this).val())" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                <select id="classroom_id" class="form-select" name="classroom_id"
                                    onchange="console.log($(this).val())" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.section') }}</label>
                                <select id="section_id" class="form-select" name="section_id" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>

                        </div>

                        <br>
                        <h6 style="color: red;font-family: Cairo">{{ trans('student.newschoolstage') }}</h6><br>

                        <div class="row">


                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                <select id="school_id_new" class="form-select" name="school_id_new"
                                    onchange="console.log($(this).val())" required
                                    data-validation-required-message="This field is sssss">
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                    @foreach ($School as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.niveau') }}</label>
                                <select id="grade_id_new" class="form-select" name="grade_id_new"
                                    onchange="console.log($(this).val())" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                <select id="classroom_id_new" class="form-select" name="classroom_id_new"
                                    onchange="console.log($(this).val())" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.section') }}</label>
                                <select id="section_id_new" class="form-select" name="section_id_new" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>

                        </div>
                        <a type="button" class="btn btn-primary"
                            onclick="event.preventDefault();
                                   document.getElementById('promotion').submit();">{{ trans('opt.save') }}</a>
                    </div>

                </form>
            </div>
        </div>

        <!-- /.box-header -->
        <div class="box-body">
            <form method="GET" class="row mb-3">
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="بحث: الاسم / البريد / الهاتف">
                </div>
                <div class="col-md-3">
                    <select name="section_id" class="form-select">
                        <option value="">كل الأقسام</option>
                        @foreach ($Sections as $section)
                            <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>
                                {{ $section->classroom->schoolgrade->name_grade ?? '' }} /
                                {{ $section->classroom->name_class ?? '' }} /
                                {{ $section->name_section ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="classroom_id" class="form-select">
                        <option value="">كل الأقسام الدراسية</option>
                        @foreach ($Sections->pluck('classroom')->filter()->unique('id') as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string) request('classroom_id') === (string) $classroom->id)>
                                {{ $classroom->name_class }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="grade_id" class="form-select">
                        <option value="">كل المستويات</option>
                        @foreach ($Sections->pluck('classroom.schoolgrade')->filter()->unique('id') as $grade)
                            <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>
                                {{ $grade->name_grade }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary" type="submit">تصفية</button>
                    <a href="{{ route('Students.index') }}" class="btn btn-outline-secondary">إعادة</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered text-center" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th> {{ trans('inscription.ecole') }}</th>
                            <th class="col-md-2">{{ trans('inscription.Anneescolaire') }}</th>
                            <th class="col-md-2">{{ trans('inscription.niveau') }}</th>
                            <th class="col-md-2">{{ trans('inscription.section') }}</th>
                            <th class="col-md-3">{{ trans('inscription.action') }}</th>

                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($StudentInfo as $index => $ins)
                            <tr>

                                <td>{{ $StudentInfo->firstItem() + $index }}</td>
                                <td class="col-md-2">
                                    <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ $ins->prenom }}
                                        {{ $ins->nom }}</a><span
                                        class="text-fade d-block">{{ optional($ins->user)->email }}</span>
                                    <span class="text-fade d-block">0{{ $ins->numtelephone }}</span>
                                </td>


                                <td class="col-md-2"> {{ $ins->section->classroom->schoolgrade->school->name_school }}
                                </td>

                                <td class="col-md-2">{{ $ins->section->classroom->name_class }}</td>
                                <td class="col-md-2">{{ $ins->section->classroom->schoolgrade->name_grade }}</td>
                                <td> {{ $ins->section->name_section }} </td>

                                <td class="col-md-3">
                                    <a data-bs-target=".modal-update{{ $ins->id }}" data-bs-toggle="modal"
                                        class="waves-effect waves-light btn btn-primary-light btn-circle mx-2"><span
                                            class="icon-Write"><span class="path1"></span><span
                                                class="path2"></span></span></a>

                                    <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal"
                                        class="waves-effect waves-light btn btn-danger-light btn-circle mx-2"><span
                                            class="icon-Trash1 fs-18"><span class="path1"></span><span
                                                class="path2"></span></span></a>

                                    <a href="javascript:void(0);"
                                        class="waves-effect waves-light btn btn-warning-light btn-circle mx-2 open-absence-modal"
                                        data-student-id="{{ $ins->id }}"
                                        data-student-name="{{ $ins->prenom }} {{ $ins->nom }}">
                                        <i class="fa fa-calendar-check-o"></i>
                                    </a>


                                </td>
                            </tr>

                            <!-- Delete -->
                            <div class="modal center-modal fade" id="modal-delete{{ $ins->id }}"
                                tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <div class="modal-body">
                                            <form id="delete-form{{ $ins->id }}"
                                                action="{{ route('Students.destroy', $ins->id) }}" method="POST">

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
                                            <a type="button" class="btn btn-danger"
                                                data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                            <a type="button" class="btn btn-primary float-end"
                                                onclick="event.preventDefault();
            document.getElementById('delete-form{{ $ins->id }}').submit();">{{ trans('opt.delete2') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Update Form -->
                            <div class="modal fade modal-update{{ $ins->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <div class="modal-body">
                                            <form novalidate id="update-form{{ $ins->id }}"
                                                action="{{ route('Students.update', $ins->id) }}" method="POST">

                                                {{ method_field('patch') }}
                                                @csrf


                                                <div class="box-body">

                                                    <h4 class="box-title text-info mb-0 mt-20"><i
                                                            class="ti-home me-15"></i>{{ trans('inscription.informationecole') }}
                                                    </h4>
                                                    <hr class="my-15">


                                                    <div class="row">

                                                        <div class="form-group col">
                                                            <label
                                                                class="form-label">{{ trans('inscription.ecole') }}</label>
                                                            <select id="school_id" class="form-select"
                                                                name="school_id" onchange="console.log($(this).val())"
                                                                required
                                                                data-validation-required-message="This field is sssss">
                                                                <option value="" selected disabled>
                                                                    {{ trans('inscription.choisir') }}</option>
                                                                @foreach ($School as $sc)
                                                                    <option value="{{ $sc->id }}">
                                                                        {{ $sc->name_school }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label
                                                                class="form-label">{{ trans('inscription.niveau') }}</label>
                                                            <select id="grade_id" class="form-select"
                                                                name="grade_id" onchange="console.log($(this).val())"
                                                                required>
                                                                <option value="" selected disabled>
                                                                    {{ trans('inscription.choisir') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col">
                                                            <label
                                                                class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                                            <select id="classroom_id" class="form-select"
                                                                name="classroom_id"
                                                                onchange="console.log($(this).val())" required>
                                                                <option value="" selected disabled>
                                                                    {{ trans('inscription.choisir') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col">
                                                            <label
                                                                class="form-label">{{ trans('inscription.section') }}</label>
                                                            <select id="section_id" class="form-select"
                                                                name="section_id" required>
                                                                <option value="{{ $ins->section_id }}" selected>
                                                                    {{ $ins->section->name_section }}</option>
                                                                @foreach ($ins->section->classroom->sections as $sc)
                                                                    @if ($ins->section_id != $sc->id)
                                                                        <option value="{{ $sc->id }}">
                                                                            {{ $sc->name_section }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                    </div>

                                                    <h4 class="box-title text-info mb-0"><i
                                                            class="ti-id-badge me-15"></i>
                                                        {{ trans('inscription.informationétudiant') }} </h4>
                                                    <hr class="my-15">

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.prenomfr') }}</label>
                                                                <input type="text" name="prenomfr"
                                                                    value="{{ $ins->getTranslation('prenom', 'fr') }}"
                                                                    class="form-control">

                                                                <input type="hidden" name="user_id"
                                                                    value="{{ $ins->user->id }}">

                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.nomfr') }}</label>
                                                                <input type="text" name="nomfr"
                                                                    value="{{ $ins->getTranslation('nom', 'fr') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.prenomar') }}</label>
                                                                <input type="text" name="prenomar"
                                                                    value="{{ $ins->getTranslation('prenom', 'ar') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.nomar') }}</label>
                                                                <input type="text" name="nomar"
                                                                    value="{{ $ins->getTranslation('nom', 'ar') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.email') }}</label>
                                                                <input type="email" name="email"
                                                                    value="{{ $ins->user->email }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.gender') }}</label>
                                                                <select class="form-select" name="gender" required>
                                                                    @if ($ins->gender == 1)
                                                                        <option selected value="{{ 1 }}">
                                                                            {{ trans('inscription.male') }}</option>
                                                                        <option value="{{ 0 }}">
                                                                            {{ trans('inscription.female') }}</option>
                                                                    @else
                                                                        <option value="{{ 1 }}">
                                                                            {{ trans('inscription.male') }}</option>
                                                                        <option selected value="{{ 0 }}">
                                                                            {{ trans('inscription.female') }}</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.numtelephone') }}</label>
                                                                <input type="number" name="numtelephone"
                                                                    value="{{ $ins->numtelephone }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.datenaissance') }}</label>
                                                                <input type="date" name="datenaissance"
                                                                    value="{{ $ins->datenaissance }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.lieunaissance') }}</label>
                                                                <input type="text" name="lieunaissance"
                                                                    value="{{ $ins->lieunaissance }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>


                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.wilaya') }}</label>
                                                                <input type="text" name="wilaya"
                                                                    value="{{ $ins->wilaya }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.dayra') }}</label>
                                                                <input type="text" name="dayra"
                                                                    value="{{ $ins->dayra }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.baladia') }}</label>
                                                                <input type="text" name="baladia"
                                                                    value="{{ $ins->baladia }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>


                                                    </div>

                                            </form>
                                        </div>
                                        <div class="modal-footer modal-footer-uniform">
                                            <a type="button" class="btn btn-danger"
                                                data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                            <a type="submit" class="btn btn-primary float-end"
                                                onclick="event.preventDefault();
              document.getElementById('update-form{{ $ins->id }}').submit();">{{ trans('opt.update2') }}</a>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <th> </th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th>{{ trans('inscription.ecole') }}</th>
                            <th>{{ trans('inscription.Anneescolaire') }}</th>
                            <th>{{ trans('inscription.niveau') }}</th>
                            <th>{{ trans('inscription.section') }}</th>
                            <th>{{ trans('inscription.action') }}</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3">
                    {{ $StudentInfo->links() }}
                </div>
                <!-- مودال الغيابات المشترك -->
                <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    {{ trans('opt.attendance_modal_title') }}
                                    <span id="attendanceStudentName"></span>
                                </h5>


                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="attendanceStudentId" value="">
                                <div class="mb-2">
                                    <label>{{ trans('opt.attendance_date') }} </label>
                                    <input type="date" id="attendanceDate" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <h6 class="mt-3"> {{ trans('opt.morning_period') }} </h6>
                                <div class="row mb-3" id="attendanceMorning">
                                    @php
                                        // سننشئ ساعات من 08:00 حتى 12:00 => indexes 1..5
                                        $morning = [8, 9, 10, 11, 12];
                                    @endphp
                                    @foreach ($morning as $idx => $hour)
                                        @php $index = $hour - 7; @endphp
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input attendance-hour" type="checkbox"
                                                    id="hour_{{ $index }}"
                                                    data-hour="hour_{{ $index }}">
                                                <label class="form-check-label"
                                                    for="hour_{{ $index }}">{{ $hour }}:00</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <h6 class="mt-3">{{ trans('opt.afternoon_period') }} </h6>
                                <div class="row" id="attendanceAfternoon">
                                    @php
                                        // سهلنا يدوياً 13:00 - 17:00 => indexes 6..10
                                        $afternoon = [13, 14, 15, 16];
                                    @endphp
                                    @foreach ($afternoon as $hour)
                                        @php $index = $hour - 7; @endphp
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input attendance-hour" type="checkbox"
                                                    id="hour_{{ $index }}"
                                                    data-hour="hour_{{ $index }}">
                                                <label class="form-check-label"
                                                    for="hour_{{ $index }}">{{ $hour }}:00</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer text-center">
                                <small class="text-muted me-auto">
                                    {{ trans('opt.toggle_attendance_hint') }} </small><br>
                                <a href="javascript:void(0);" class="btn btn-secondary" data-bs-dismiss="modal">
                                    {{ trans('opt.close') }}
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- نهاية مودال الغيابات -->

            </div>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>

</div>
@endsection

@section('jsa')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const attendanceModalEl = document.getElementById('attendanceModal');
        const attendanceModal = new bootstrap.Modal(attendanceModalEl);
        const studentNameEl = document.getElementById('attendanceStudentName');
        const studentIdEl = document.getElementById('attendanceStudentId');
        const dateEl = document.getElementById('attendanceDate');
        const csrfToken = '{{ csrf_token() }}';

        // زر فتح المودال
        document.querySelectorAll('.open-absence-modal').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');

                studentIdEl.value = studentId;
                studentNameEl.textContent = studentName;
                dateEl.value = '{{ date('Y-m-d') }}';

                const checkboxes = Array.from(document.querySelectorAll('.attendance-hour'));

                // فقط تفريغ قبل التحميل (بدون تعطيل)
                checkboxes.forEach(chk => chk.checked = false);

                fetch("{{ url('absences/today') }}?student_id=" + studentId)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (!data || Object.keys(data).length === 0) {
                            // لا سجل لليوم => افتراضياً الطلاب "حاضر"
                            checkboxes.forEach(chk => chk.checked = true);
                        } else {
                            checkboxes.forEach(chk => {
                                const key = chk.dataset.hour;
                                chk.checked = data.hasOwnProperty(key) ? !!data[
                                    key] : false;
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching absence:', err);
                        checkboxes.forEach(chk => chk.checked = true);
                    })
                    .finally(() => {
                        attendanceModal.show();
                    });
            });
        });

        // تحديث حالة الحضور عند تبديل أي خانة
        document.querySelectorAll('.attendance-hour').forEach(chk => {
            chk.addEventListener('change', function() {
                const hourKey = this.dataset.hour;
                const status = this.checked ? 1 : 0;
                const studentId = studentIdEl.value;

                fetch("{{ route('absence.update') }}", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            student_id: studentId,
                            hour: hourKey,
                            status: status
                        })
                    })
                    .then(async res => {
                        if (!res.ok) throw new Error(await res.text());
                        return res.json();
                    })
                    .then(json => {
                        console.log('{{ trans('opt.absence_saved') }} ', json);
                    })
                    .catch(err => {
                        // إعادة الحالة السابقة إذا فشل الحفظ
                        this.checked = !this.checked;
                        alert('{{ trans('opt.absence_save_error') }}');
                        console.error(err);
                    });
            });
        });
    });
</script>
@endsection
