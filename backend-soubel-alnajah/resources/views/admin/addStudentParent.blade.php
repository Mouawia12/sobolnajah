@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('student.addstudent') }}
@stop
@endsection

@section('contenta')
<div class="row align-items-center">

    @if (Session::get('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

            <h4><i class="icon fa fa-check"></i>{{ trans('inscription.enregistrésuccès') }}</h4>
            <h6>{{ trans('inscription.inscriptionok') }}</h6>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger col-md-6">
                <p>{{ $error }}</p>
            </div>
        @endforeach
    @endif

    <div class="col-12">

        <div class="box box-slided-up">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>{{ trans('opt.addStudentbyExcel') }}</strong></h4>
                <ul class="box-controls pull-right">
                    <li><a class="box-btn-slide text-white" href="#"></a></li>
                    <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                </ul>
            </div>

            <div class="box-body">
                <form id="students-import-form" action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data"
                    class="mb-3">
                    @csrf
                    <input type="hidden" name="import_token" id="students-import-token" value="">
                    <div class="form-group">
                        <label>{{ trans('opt.uploadStudentsExcel') }}</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" id="students-import-submit" class="btn btn-success mt-2">{{ trans('opt.importStudents') }}</button>
                </form>

                <div id="students-import-progress-card" class="alert alert-light border d-none">
                    <div class="d-flex justify-content-between align-items-center mb-10">
                        <strong>حالة استيراد الطلبة</strong>
                        <span id="students-import-progress-state" class="badge badge-info">قيد المعالجة...</span>
                    </div>

                    <div class="progress progress-lg mb-10">
                        <div id="students-import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>

                    <div class="row g-2 text-center mb-10">
                        <div class="col-6 col-md-2">
                            <div class="fw-bold" id="students-import-total">0</div>
                            <small>الإجمالي</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-primary" id="students-import-processed">0</div>
                            <small>تمت المعالجة</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-success" id="students-import-added">0</div>
                            <small>مضاف</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-info" id="students-import-section-updated">0</div>
                            <small>تصحيح قسم</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-warning" id="students-import-duplicates">0</div>
                            <small>مكرر</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-danger" id="students-import-skipped">0</div>
                            <small>متجاهل</small>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="fw-bold text-dark" id="students-import-not-added">0</div>
                            <small>غير مضاف</small>
                        </div>
                    </div>

                    <div class="row g-2 mb-8">
                        <div class="col-md-6"><strong>تعويض تلقائي للحقول:</strong> <span id="students-import-autofill">0</span></div>
                        <div class="col-md-6"><strong>آخر تحديث:</strong> <span id="students-import-updated-at">-</span></div>
                    </div>

                    <div id="students-import-message" class="mb-6 text-muted"></div>
                    <div id="students-import-issues-wrapper" class="d-none">
                        <strong>ملاحظات مهمة:</strong>
                        <ul id="students-import-issues" class="mb-0 mt-5"></ul>
                    </div>
                </div>

            </div>
        </div>


        <!-- Validation wizard -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title text-info mb-0 mt-20"><i
                        class="mdi mdi-school me-15"></i>{{ trans('inscription.forminscription') }}</h3>

            </div>
            <!-- /.box-header -->
            <div class="box-body wizard-content">
                <form novalidate id="inscription-form" action="{{ route('Students.store') }}"
                    class="validation-wizard wizard-circle" method="POST">
                    {{-- validation-wizard  --}}
                    @csrf
                    <!-- Step 1 -->
                    <h6>{{ trans('inscription.informationecole') }}</h6>
                    <section>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                    <select id="school_id" class="form-select" name="school_id"
                                        onchange="console.log($(this).val())" required
                                        data-validation-required-message="This field is sssss">
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                        </option>
                                        @foreach ($School as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.niveau') }}</label>
                                    <select id="grade_id" class="form-select" name="grade_id"
                                        onchange="console.log($(this).val())" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                    <select id="classroom_id" class="form-select" name="classroom_id" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ trans('inscription.section') }}</label>
                                <select id="section_id" class="form-select" name="section_id" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                </select>
                            </div>

                        </div>

                    </section>
                    <!-- Step 2 -->


                    <h6>{{ trans('inscription.informationétudiant') }}</h6>
                    <section>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.prenomfr') }}</label>
                                    <input type="text" name="prenomfr" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.nomfr') }}</label>
                                    <input type="text" name="nomfr" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.prenomar') }}</label>
                                    <input type="text" name="prenomar" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.nomar') }}</label>
                                    <input type="text" name="nomar" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.email') }}</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.gender') }}</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                        </option>
                                        <option value="{{ 1 }}">{{ trans('inscription.male') }}</option>
                                        <option value="{{ 0 }}">{{ trans('inscription.female') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.numtelephone') }}</label>
                                    <input type="number" name="numtelephone" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.datenaissance') }}</label>
                                    <input type="date" name="datenaissance" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.lieunaissance') }}</label>
                                    <input type="text" name="lieunaissance" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.wilaya') }}</label>
                                    <input type="text" name="wilaya" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.dayra') }}</label>
                                    <input type="text" name="dayra" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.baladia') }}</label>
                                    <input type="text" name="baladia" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>

                        </div>
                    </section>
                    <!-- Step 3 -->
                    <h6>{{ trans('inscription.informationwali') }}</h6>

                    <section>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.prenomfr') }}</label>
                                    <input type="text" name="prenomfrwali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.nomfr') }}</label>
                                    <input type="text" name="nomfrwali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.prenomar') }}</label>
                                    <input type="text" name="prenomarwali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.nomar') }}</label>
                                    <input type="text" name="nomarwali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.relationetudiant') }}</label>
                                    <input type="text" name="relationetudiant" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.adresse') }}</label>
                                    <input type="text" name="adressewali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.numtelephone') }}</label>
                                    <input type="number" name="numtelephonewali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.email') }}</label>
                                    <input type="email" name="emailwali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.wilaya') }}</label>
                                    <input type="text" name="wilayawali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.dayra') }}</label>
                                    <input type="text" name="dayrawali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('inscription.baladia') }}</label>
                                    <input type="text" name="baladiawali" class="form-control"
                                        placeholder="{{ trans('inscription.ecrire') }}" required>
                                </div>
                            </div>

                        </div>

                    </section>

                </form>

            </div>
            <!-- /.box-body -->
        </div>
    </div>

    <!-- /.box -->
</div>
@endsection


@section('jsa')
<script>
    (function() {
        const form = document.getElementById('students-import-form');
        if (!form || !window.fetch) {
            return;
        }

        const submitBtn = document.getElementById('students-import-submit');
        const tokenInput = document.getElementById('students-import-token');
        const progressCard = document.getElementById('students-import-progress-card');
        const progressBar = document.getElementById('students-import-progress-bar');
        const progressState = document.getElementById('students-import-progress-state');
        const progressMessage = document.getElementById('students-import-message');
        const issuesWrapper = document.getElementById('students-import-issues-wrapper');
        const issuesList = document.getElementById('students-import-issues');
        const statusUrlTemplate = @json(route('students.import.status', ['token' => '__TOKEN__']));

        let pollingTimer = null;
        let activeToken = null;

        const map = {
            total: document.getElementById('students-import-total'),
            processed: document.getElementById('students-import-processed'),
            added: document.getElementById('students-import-added'),
            sectionUpdated: document.getElementById('students-import-section-updated'),
            duplicates: document.getElementById('students-import-duplicates'),
            skipped: document.getElementById('students-import-skipped'),
            notAdded: document.getElementById('students-import-not-added'),
            autofill: document.getElementById('students-import-autofill'),
            updatedAt: document.getElementById('students-import-updated-at'),
        };

        function generateToken() {
            const randomPart = Math.random().toString(36).slice(2, 10);
            return `stimport_${Date.now()}_${randomPart}`;
        }

        function setStateBadge(status, text) {
            progressState.className = 'badge';
            if (status === 'completed') {
                progressState.classList.add('badge-success');
            } else if (status === 'failed') {
                progressState.classList.add('badge-danger');
            } else {
                progressState.classList.add('badge-info');
            }
            progressState.textContent = text;
        }

        function setProgressPercent(percent) {
            const safePercent = Math.max(0, Math.min(100, Number(percent) || 0));
            progressBar.style.width = `${safePercent}%`;
            progressBar.textContent = `${safePercent.toFixed(1)}%`;
            progressBar.setAttribute('aria-valuenow', safePercent.toFixed(1));
            if (safePercent >= 100) {
                progressBar.classList.remove('progress-bar-animated');
            }
        }

        function setIssues(issues) {
            issuesList.innerHTML = '';
            if (!Array.isArray(issues) || !issues.length) {
                issuesWrapper.classList.add('d-none');
                return;
            }

            issuesWrapper.classList.remove('d-none');
            issues.slice(0, 5).forEach((issue) => {
                const li = document.createElement('li');
                li.textContent = issue;
                issuesList.appendChild(li);
            });
        }

        function renderProgress(payload) {
            if (!payload) {
                return;
            }

            map.total.textContent = payload.total_rows ?? 0;
            map.processed.textContent = payload.processed_rows ?? 0;
            map.added.textContent = payload.imported_rows ?? 0;
            map.sectionUpdated.textContent = payload.section_updated_rows ?? 0;
            map.duplicates.textContent = payload.duplicate_rows ?? 0;
            map.skipped.textContent = payload.skipped_rows ?? 0;
            map.notAdded.textContent = payload.not_added_rows ?? 0;
            map.autofill.textContent = payload.auto_filled_fields ?? 0;
            map.updatedAt.textContent = payload.updated_at ?? '-';

            setProgressPercent(payload.progress_percent ?? 0);
            setIssues(payload.issues_preview ?? []);

            if (payload.message) {
                progressMessage.textContent = payload.message;
            } else if (payload.latest_issue) {
                progressMessage.textContent = payload.latest_issue;
            } else {
                progressMessage.textContent = '';
            }

            if (payload.status === 'completed') {
                setStateBadge('completed', 'مكتمل');
            } else if (payload.status === 'failed') {
                setStateBadge('failed', 'فشل');
            } else if (payload.status === 'running') {
                setStateBadge('running', 'قيد المعالجة...');
            } else {
                setStateBadge('pending', 'بانتظار البدء...');
            }
        }

        async function pollStatusOnce() {
            if (!activeToken) {
                return;
            }

            try {
                const url = statusUrlTemplate.replace('__TOKEN__', encodeURIComponent(activeToken));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('#students-import-form input[name="_token"]').value,
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                renderProgress(payload);

                if (payload.status === 'completed' || payload.status === 'failed') {
                    stopPolling();
                }
            } catch (error) {
                // Ignore transient polling errors while import is running.
            }
        }

        function startPolling() {
            stopPolling();
            pollingTimer = setInterval(pollStatusOnce, 1000);
        }

        function stopPolling() {
            if (pollingTimer) {
                clearInterval(pollingTimer);
                pollingTimer = null;
            }
        }

        function lockForm(locked) {
            if (submitBtn) {
                submitBtn.disabled = locked;
            }
            const fileInput = form.querySelector('input[name="file"]');
            if (fileInput) {
                fileInput.disabled = locked;
            }
        }

        function resetPanel() {
            progressCard.classList.remove('d-none');
            setStateBadge('running', 'قيد المعالجة...');
            setProgressPercent(0);
            progressBar.classList.add('progress-bar-animated');
            progressMessage.textContent = '';
            issuesWrapper.classList.add('d-none');
            issuesList.innerHTML = '';
            map.total.textContent = '0';
            map.processed.textContent = '0';
            map.added.textContent = '0';
            map.sectionUpdated.textContent = '0';
            map.duplicates.textContent = '0';
            map.skipped.textContent = '0';
            map.notAdded.textContent = '0';
            map.autofill.textContent = '0';
            map.updatedAt.textContent = '-';
        }

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const fileInput = form.querySelector('input[name="file"]');
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                toastr.error('يرجى اختيار ملف Excel أولا.');
                return;
            }

            activeToken = generateToken();
            tokenInput.value = activeToken;
            resetPanel();
            lockForm(true);
            startPolling();
            await pollStatusOnce();

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formData.get('_token'),
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));
                if (payload && payload.progress) {
                    renderProgress(payload.progress);
                } else {
                    await pollStatusOnce();
                }

                if (!response.ok || !payload.ok) {
                    setStateBadge('failed', 'فشل');
                    progressMessage.textContent = payload.message || 'فشل أثناء استيراد الملف.';
                    toastr.error(payload.message || 'فشل أثناء استيراد الملف.');
                    return;
                }

                setStateBadge('completed', 'مكتمل');
                progressMessage.textContent = 'تمت معالجة الملف بنجاح. يمكن مراجعة الإحصائيات أعلاه.';
                if (payload.issues && payload.issues.length) {
                    setIssues(payload.issues);
                }
                toastr.success('تم استيراد ملف الطلبة بنجاح.');
            } catch (error) {
                setStateBadge('failed', 'فشل');
                progressMessage.textContent = 'حدث خطأ أثناء رفع الملف أو متابعة التقدم.';
                toastr.error('تعذر إكمال الاستيراد حاليا.');
            } finally {
                stopPolling();
                lockForm(false);
            }
        });
    })();
</script>
<script>
    $(document).ready(function() {
        $('select[name="school_id"]').on('change', function() {
            var school_id = $(this).val();
            if (school_id) {
                $.ajax({
                    url: "{{ route('lookup.schoolGrades', ['id' => '__ID__']) }}".replace('__ID__', school_id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('select[name="grade_id"]').empty();
                        $('select[name="grade_id"]').append(
                            '<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="grade_id"]').append('<option value="' +
                                key + '">' + value + '</option>');
                        });
                    },
                });
            } else {
                console.log('AJAX load did not work');
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('select[name="grade_id"]').on('change', function() {
            var grade_id = $(this).val();
            if (grade_id) {
                $.ajax({
                    url: "{{ route('lookup.gradeClasses', ['id' => '__ID__']) }}".replace('__ID__', grade_id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('select[name="classroom_id"]').empty();
                        $('select[name="classroom_id"]').append(
                            '<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="classroom_id"]').append(
                                '<option value="' + key + '">' + value +
                                '</option>');
                        });
                    },
                });
            } else {
                console.log('AJAX load did not work');
            }
        });
    });
</script>




<script src="{{ asset('assets/vendor_components/jquery-steps-master/build/jquery.steps.js') }}"></script>
<script src="{{ asset('assets/vendor_components/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}"></script>





<script>
    $(".tab-wizard").steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "none",
        titleTemplate: '<span class="step">#index#</span> #title#',
        labels: {
            finish: "Submit",

        },
        onFinished: function(event, currentIndex) {
            swal("Your Order Submitted!",
                "Sed dignissim lacinia nunc. Curabitur tortor. Pellentesque nibh. Aenean quam. In scelerisque sem at dolor. Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor."
            );

        }
    });


    var form = $(".validation-wizard").show();

    $(".validation-wizard").steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "none",
        titleTemplate: '<span class="step">#index#</span> #title#',
        labels: {
            finish: "{{ trans('inscription.inscription') }}",
            next: "{{ trans('inscription.next') }}",
            previous: "{{ trans('inscription.Previous') }}",

        },
        onStepChanging: function(event, currentIndex, newIndex) {
            return currentIndex > newIndex || !(3 === newIndex && Number($("#age-2").val()) < 18) && (
                currentIndex < newIndex && (form.find(".body:eq(" + newIndex + ") label.error")
                    .remove(), form.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
                form
                .validate().settings.ignore = ":disabled,:hidden", form.valid())
        },
        onFinishing: function(event, currentIndex) {
            return form.validate().settings.ignore = ":disabled", form.valid()

        },
        onFinished: function(event, currentIndex) {
            //swal("Your Form Submitted!", "Sed dignissim lacinia nunc. Curabitur tortor. Pellentesque nibh. Aenean quam. In scelerisque sem at dolor. Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor.");

            event.preventDefault();
            document.getElementById('inscription-form').submit();

        }
    }), $(".validation-wizard").validate({
        ignore: "input[type=hidden]",
        errorClass: "text-danger",
        successClass: "text-success",
        highlight: function(element, errorClass) {
            $(element).removeClass(errorClass)
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass)
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element)
        },
        rules: {
            email: {
                email: !0
            },
            moyensannuels: {
                //minlength: 10,
                number: 1
            },
            codepostal: {
                //minlength: 10,
                number: 1
            },
            emailwali: {
                email: !0
            },
        },
        messages: {
            school_id: {
                required: "{{ trans('validation.select') }}"
            },
            grade_id: {
                required: "{{ trans('validation.select') }}"
            },
            classroom_id: {
                required: "{{ trans('validation.select') }}"
            },
            section_id: {
                required: "{{ trans('validation.select') }}"
            },
            inscriptionetat: {
                required: "{{ trans('validation.select') }}"
            },
            nomecoleprecedente: {
                required: "{{ trans('validation.required') }}"
            },
            dernieresection: {
                required: "{{ trans('validation.required') }}"
            },
            moyensannuels: {
                required: "{{ trans('validation.required') }}",
                //minlength: "please min",
                number: "{{ trans('validation.numeric') }}"

            },
            numeronationaletudiant: {
                required: "{{ trans('validation.required') }}"
            },
            prenom: {
                required: "{{ trans('validation.required') }}"
            },
            nom: {
                required: "{{ trans('validation.required') }}"
            },
            email: {
                required: "{{ trans('validation.required') }}",
                email: "{{ trans('validation.email') }}"
            },
            emailwali: {
                required: "{{ trans('validation.required') }}",
                email: "{{ trans('validation.email') }}"
            },
            numtelephone: {
                required: "{{ trans('validation.required') }}"
            },
            datenaissance: {
                required: "{{ trans('validation.date') }}"
            },
            lieunaissance: {
                required: "{{ trans('validation.required') }}"
            },
            wilaya: {
                required: "{{ trans('validation.required') }}"
            },
            dayra: {
                required: "{{ trans('validation.required') }}"
            },
            baladia: {
                required: "{{ trans('validation.required') }}"
            },
            adresseactuelle: {
                required: "{{ trans('validation.required') }}"
            },
            codepostal: {
                required: "{{ trans('validation.required') }}",
                number: "{{ trans('validation.numeric') }}"

            },
            residenceactuelle: {
                required: "{{ trans('validation.select') }}"
            },
            etatsante: {
                required: "{{ trans('validation.select') }}"
            },
            identificationmaladie: {
                required: "{{ trans('validation.required') }}"
            },
            alfdlprsaldr: {
                required: "{{ trans('validation.select') }}"
            },
            autresnotes: {
                required: "{{ trans('validation.required') }}"
            },
            prenomwali: {
                required: "{{ trans('validation.required') }}"
            },
            nomwali: {
                required: "{{ trans('validation.required') }}"
            },
            relationetudiant: {
                required: "{{ trans('validation.required') }}"
            },
            adressewali: {
                required: "{{ trans('validation.required') }}"
            },
            numtelephonewali: {
                required: "{{ trans('validation.required') }}"
            },
            wilayawali: {
                required: "{{ trans('validation.required') }}"
            },
            dayrawali: {
                required: "{{ trans('validation.required') }}"
            },
            baladiawali: {
                required: "{{ trans('validation.required') }}"
            }
        },
    })
</script>
@endsection
