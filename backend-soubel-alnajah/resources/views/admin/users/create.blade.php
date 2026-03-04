@extends('layoutsadmin.masteradmin')
@section('titlea', 'إضافة مستخدم')

@section('contenta')
<div class="row">
    <div class="col-12">
        @include('layoutsadmin.partials.status_alerts')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">إضافة مستخدم جديد</h3>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('admin.users.store') }}" id="portal-user-form">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">الدور</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="">اختر الدور</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php $isSchoolLocked = auth()->user()?->school_id !== null; @endphp
                        <div class="col-12 col-md-4 {{ $isSchoolLocked ? 'd-none' : '' }}" id="school-wrapper">
                            <label class="form-label">المؤسسة</label>
                            <select name="school_id" id="school_id" class="form-select">
                                <option value="">اختر المؤسسة</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" @selected((string) old('school_id') === (string) $school->id)>
                                        {{ $school->name_school }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">الاسم الأول (FR)</label>
                            <input type="text" name="first_name_fr" class="form-control" value="{{ old('first_name_fr') }}" placeholder="Ahmed">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">اللقب (FR)</label>
                            <input type="text" name="last_name_fr" class="form-control" value="{{ old('last_name_fr') }}" placeholder="Benali">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">الاسم الأول (AR)</label>
                            <input type="text" name="first_name_ar" class="form-control" value="{{ old('first_name_ar') }}" placeholder="أحمد">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">اللقب (AR)</label>
                            <input type="text" name="last_name_ar" class="form-control" value="{{ old('last_name_ar') }}" placeholder="بن علي">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div id="teacher-fields" class="role-fields d-none">
                        <h5 class="mb-3">بيانات المعلم</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">التخصص</label>
                                <select name="specialization_id" class="form-select">
                                    <option value="">اختر التخصص</option>
                                    @foreach($specializations as $specialization)
                                        <option value="{{ $specialization->id }}" @selected((string) old('specialization_id') === (string) $specialization->id)>
                                            {{ $specialization->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الجنس</label>
                                <select name="gender" class="form-select">
                                    <option value="">اختر</option>
                                    <option value="1" @selected(old('gender') === '1')>ذكر</option>
                                    <option value="0" @selected(old('gender') === '0')>أنثى</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">تاريخ الانضمام</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                            </div>
                        </div>
                    </div>

                    <div id="guardian-fields" class="role-fields d-none">
                        <h5 class="mb-3">بيانات الولي</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">صلة القرابة</label>
                                <input type="text" name="guardian_relation" class="form-control" value="{{ old('guardian_relation') }}" placeholder="أب / أم / أخ ...">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الهاتف</label>
                                <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الولاية</label>
                                <input type="text" name="guardian_wilaya" class="form-control" value="{{ old('guardian_wilaya') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الدائرة</label>
                                <input type="text" name="guardian_dayra" class="form-control" value="{{ old('guardian_dayra') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">البلدية</label>
                                <input type="text" name="guardian_baladia" class="form-control" value="{{ old('guardian_baladia') }}">
                            </div>
                        </div>
                    </div>

                    <div id="student-fields" class="role-fields d-none">
                        <h5 class="mb-3">بيانات التلميذ</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">ولي التلميذ</label>
                                <select name="guardian_user_id" id="guardian_user_id" class="form-select">
                                    <option value="">اختر ولي</option>
                                    @foreach($guardians as $guardian)
                                        <option
                                            value="{{ $guardian->id }}"
                                            data-school-id="{{ $guardian->school_id }}"
                                            @selected((string) old('guardian_user_id') === (string) $guardian->id)
                                        >
                                            {{ $guardian->name }} - {{ $guardian->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">الجنس</label>
                                <select name="gender" class="form-select">
                                    <option value="">اختر</option>
                                    <option value="1" @selected(old('gender') === '1')>ذكر</option>
                                    <option value="0" @selected(old('gender') === '0')>أنثى</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">الهاتف</label>
                                <input type="text" name="student_phone" class="form-control" value="{{ old('student_phone') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">القسم</label>
                                <select name="section_id" id="section_id" class="form-select">
                                    <option value="">اختر القسم</option>
                                    @foreach($sections as $section)
                                        <option
                                            value="{{ $section->id }}"
                                            data-school-id="{{ $section->school_id }}"
                                            @selected((string) old('section_id') === (string) $section->id)
                                        >
                                            {{ $section->classroom->schoolgrade->name_grade ?? '-' }} / {{ $section->classroom->name_class ?? '-' }} / {{ $section->name_section }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">تاريخ الميلاد</label>
                                <input type="date" name="student_birth_date" class="form-control" value="{{ old('student_birth_date') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">مكان الميلاد</label>
                                <input type="text" name="student_birth_place" class="form-control" value="{{ old('student_birth_place') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الولاية</label>
                                <input type="text" name="student_wilaya" class="form-control" value="{{ old('student_wilaya') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">الدائرة</label>
                                <input type="text" name="student_dayra" class="form-control" value="{{ old('student_dayra') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">البلدية</label>
                                <input type="text" name="student_baladia" class="form-control" value="{{ old('student_baladia') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">حفظ المستخدم</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">رجوع</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script>
    (function () {
        const roleInput = document.getElementById('role');
        const schoolInput = document.getElementById('school_id');

        const groups = {
            teacher: document.getElementById('teacher-fields'),
            guardian: document.getElementById('guardian-fields'),
            student: document.getElementById('student-fields')
        };

        const filterBySchool = (selectId) => {
            const select = document.getElementById(selectId);
            if (!select) {
                return;
            }

            const selectedSchool = schoolInput ? schoolInput.value : '';

            Array.from(select.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const optionSchool = option.getAttribute('data-school-id');
                option.hidden = !!selectedSchool && optionSchool !== selectedSchool;
                if (option.hidden && option.selected) {
                    option.selected = false;
                }
            });
        };

        const applyRole = () => {
            const role = roleInput ? roleInput.value : '';
            Object.values(groups).forEach((group) => {
                group.classList.add('d-none');
                Array.from(group.querySelectorAll('input, select, textarea')).forEach((field) => {
                    field.disabled = true;
                });
            });

            if (groups[role]) {
                groups[role].classList.remove('d-none');
                Array.from(groups[role].querySelectorAll('input, select, textarea')).forEach((field) => {
                    field.disabled = false;
                });
            }

            if (role === 'student') {
                filterBySchool('section_id');
                filterBySchool('guardian_user_id');
            }
        };

        if (roleInput) {
            roleInput.addEventListener('change', applyRole);
        }

        if (schoolInput) {
            schoolInput.addEventListener('change', function () {
                filterBySchool('section_id');
                filterBySchool('guardian_user_id');
            });
        }

        applyRole();
    })();
</script>
@endsection
