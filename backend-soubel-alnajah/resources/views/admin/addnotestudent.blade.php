@extends('layoutsadmin.masteradmin')

@section('titlea')
    كشف نقاط التلاميذ
@stop

@section('contenta')
<div class="row">
    <div class="col-12">
        <style>
            .student-search-select {
                position: relative;
            }
            .student-search-menu {
                position: absolute;
                inset-inline: 0;
                top: calc(100% + 4px);
                z-index: 1050;
                background: #fff;
                border: 1px solid #d9d9d9;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
                padding: 8px;
                display: none;
            }
            .student-search-menu.is-open {
                display: block;
            }
            .student-search-list {
                max-height: 240px;
                overflow-y: auto;
                margin-top: 6px;
            }
            .student-search-option {
                width: 100%;
                border: 0;
                background: transparent;
                text-align: start;
                padding: 6px 8px;
                border-radius: 6px;
                cursor: pointer;
            }
            .student-search-option:hover {
                background: #eef5ff;
            }
        </style>
        <div class="box box-slided-down">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>كشف نقاط التلاميذ</strong></h4>
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
                            <select id="student" class="d-none" name="student_id">
                                <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                @foreach ($UploadStudents as $studentOption)
                                    <option value="{{ $studentOption->id }}" {{ (string) old('student_id') === (string) $studentOption->id ? 'selected' : '' }}>
                                        #{{ $studentOption->id }} - {{ (string) ($studentOption->prenom ?? '-') }} {{ (string) ($studentOption->nom ?? '-') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="student-search-select" id="studentSearchSelect">
                                <button type="button" id="studentSearchToggle" class="form-control text-start d-flex justify-content-between align-items-center">
                                    <span id="studentSearchSelectedText">{{ trans('inscription.choisir') }}</span>
                                    <span>▾</span>
                                </button>
                                <div class="student-search-menu" id="studentSearchMenu">
                                    <input type="text" id="studentSearchInput" class="form-control" placeholder="ابحث عن التلميذ بالاسم أو الرقم">
                                    <div class="student-search-list" id="studentSearchList"></div>
                                </div>
                            </div>
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
                            <option value="1" {{ request('has_notes') === '1' ? 'selected' : '' }}>لديه ملفات</option>
                            <option value="0" {{ request('has_notes') === '0' ? 'selected' : '' }}>بدون ملفات</option>
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
                        @php $i = ($StudentInfo->currentPage() - 1) * $StudentInfo->perPage(); @endphp
                        @forelse ($StudentInfo as $student)
                            @php
                                $i++;
                                $noteStudent = $student->noteStudent;
                            @endphp
                            <tr>
                                <td class="col-md-1">{{ $i }}</td>
                                <td class="col-md-3">
                                    <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ (string) ($student->prenom ?? '-') }} {{ (string) ($student->nom ?? '-') }}</a>
                                    <span class="text-fade d-block">{{ optional($student->user)->email ?? '-' }}</span>
                                    <span class="text-fade d-block">{{ $student->numtelephone ?? '-' }}</span>
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
<script>
    (function () {
        const studentSelect = document.getElementById('student');
        const wrapper = document.getElementById('studentSearchSelect');
        const toggle = document.getElementById('studentSearchToggle');
        const menu = document.getElementById('studentSearchMenu');
        const searchInput = document.getElementById('studentSearchInput');
        const list = document.getElementById('studentSearchList');
        const selectedText = document.getElementById('studentSearchSelectedText');
        if (!studentSelect || !wrapper || !toggle || !menu || !searchInput || !list || !selectedText) {
            return;
        }

        const studentOptions = Array.from(studentSelect.options)
            .filter((option) => option.value !== '')
            .map((option) => ({
                value: option.value,
                text: option.textContent.trim(),
            }));

        function updateSelectedText() {
            const selected = studentSelect.options[studentSelect.selectedIndex];
            selectedText.textContent = selected && selected.value
                ? selected.textContent
                : "{{ trans('inscription.choisir') }}";
        }

        function closeMenu() {
            menu.classList.remove('is-open');
        }

        function openMenu() {
            menu.classList.add('is-open');
            searchInput.focus();
            searchInput.select();
        }

        function renderList(term) {
            const normalized = (term || '').trim().toLowerCase();
            const filtered = studentOptions.filter((item) => item.text.toLowerCase().includes(normalized));

            list.innerHTML = '';
            if (filtered.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'admin-empty-state';
                empty.textContent = 'لا توجد نتائج';
                list.appendChild(empty);
                return;
            }

            filtered.forEach((item) => {
                const optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'student-search-option';
                optionButton.textContent = item.text;
                optionButton.dataset.value = item.value;
                optionButton.addEventListener('click', () => {
                    studentSelect.value = item.value;
                    updateSelectedText();
                    closeMenu();
                    searchInput.value = '';
                    renderList('');
                });
                list.appendChild(optionButton);
            });
        }

        toggle.addEventListener('click', () => {
            if (menu.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        searchInput.addEventListener('input', function () {
            renderList(this.value);
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                closeMenu();
            }
        });

        updateSelectedText();
        renderList('');
    })();
</script>
@endsection
