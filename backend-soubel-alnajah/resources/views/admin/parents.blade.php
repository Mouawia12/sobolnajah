@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    أولياء الأمور
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- بطاقات إحصائية --}}
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('Parents.index') }}" class="text-decoration-none">
                    <div class="box box-body bg-info-light text-center">
                        <h2 class="mb-0 fw-700">{{ $stats['total'] }}</h2>
                        <span class="text-fade">إجمالي الأولياء</span>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('Parents.index', ['filter' => 'multi']) }}" class="text-decoration-none">
                    <div class="box box-body bg-success-light text-center">
                        <h2 class="mb-0 fw-700">{{ $stats['multi'] }}</h2>
                        <span class="text-fade">أولياء بعدة أبناء (إخوة)</span>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('Parents.index', ['filter' => 'synthetic']) }}" class="text-decoration-none">
                    <div class="box box-body bg-warning-light text-center">
                        <h2 class="mb-0 fw-700">{{ $stats['synthetic'] }}</h2>
                        <span class="text-fade">حسابات مؤقتة من الاستيراد</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>إدارة أولياء الأمور</strong></h4>
                <ul class="box-controls pull-right">
                    <li><a class="box-btn-slide text-white" href="#"></a></li>
                    <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                </ul>
            </div>

            <div class="box-body">

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a data-bs-target="#modal-add-parent" data-bs-toggle="modal"
                        class="btn btn-primary"><i class="fa fa-plus me-5"></i>إضافة ولي أمر</a>

                    <a href="{{ route('Parents.index', ['tab' => 'suggestions']) }}"
                        class="btn {{ request('tab') === 'suggestions' ? 'btn-warning' : 'btn-outline-warning' }}">
                        <i class="fa fa-magic me-5"></i>اقتراحات ذكية: دمج الإخوة
                    </a>
                </div>

                {{-- اقتراحات الدمج الذكية --}}
                @if (request('tab') === 'suggestions')
                    <div class="box box-bordered border-warning mb-4">
                        <div class="box-header bg-warning-light">
                            <h4 class="box-title"><i class="fa fa-magic me-10"></i><strong>إخوة محتملون — دمج
                                    الأولياء المكررين</strong></h4>
                        </div>
                        <div class="box-body">
                            <p class="text-fade">
                                استيراد ملف الوزارة ينشئ وليّاً مؤقتاً لكل تلميذ، فالإخوة يظهرون بأولياء مكررين.
                                المجموعات التالية تتشارك اللقب نفسه وأولياؤها مختلفون: حدّد التلاميذ الإخوة فعلاً،
                                اختر الولي الذي سيبقى، ثم اضغط دمج — الحسابات المؤقتة الفارغة تُحذف تلقائياً.
                            </p>

                            @forelse ($suggestions as $groupIndex => $group)
                                <form method="POST" action="{{ route('Parents.merge') }}"
                                    class="border rounded p-3 mb-3 bg-lightest">
                                    @csrf
                                    <h5 class="fw-700 text-warning mb-3">
                                        <i class="fa fa-users me-5"></i>عائلة: {{ $group['family'] }}
                                        <small class="text-fade">({{ count($group['students']) }} تلاميذ)</small>
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ضمن الدمج؟</th>
                                                    <th>التلميذ</th>
                                                    <th>القسم</th>
                                                    <th>الولي الحالي</th>
                                                    <th>الولي الذي سيبقى</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($group['students'] as $student)
                                                    <tr>
                                                        <td>
                                                            <input class="form-check-input" type="checkbox"
                                                                name="student_ids[]" value="{{ $student->id }}"
                                                                checked>
                                                        </td>
                                                        <td>
                                                            <span class="fw-600">{{ $student->prenom }}
                                                                {{ $student->nom }}</span>
                                                            @if ($student->national_id)
                                                                <span class="badge badge-info-light"
                                                                    style="direction: ltr;">{{ $student->national_id }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ optional(optional(optional($student->section)->classroom)->schoolgrade)->name_grade }}
                                                            /
                                                            {{ optional($student->section)->name_section }}
                                                        </td>
                                                        <td>
                                                            {{ optional($student->parent)->prenomwali }}
                                                            {{ optional($student->parent)->nomwali }}
                                                            @php
                                                                $guardianEmail = (string) optional(optional($student->parent)->user)->email;
                                                            @endphp
                                                            @if (\Illuminate\Support\Str::endsWith($guardianEmail, '@import.local'))
                                                                <span class="badge badge-warning-light">مؤقت</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <input class="form-check-input" type="radio"
                                                                name="target_parent_id"
                                                                value="{{ $student->parent_id }}"
                                                                @checked($loop->first)>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('سيتم نقل التلاميذ المحددين إلى الولي المختار وحذف الحسابات المؤقتة الفارغة. متابعة؟');">
                                        <i class="fa fa-compress me-5"></i>دمج تحت الولي المختار
                                    </button>
                                </form>
                            @empty
                                <div class="alert alert-success mb-0">
                                    <i class="fa fa-check-circle me-5"></i>لا توجد اقتراحات دمج حالياً — لا
                                    إخوة محتملون بأولياء مكررين.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- البحث والتصفية --}}
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                            placeholder="بحث: اسم الولي / الهاتف / اسم التلميذ / رقم التعريف">
                    </div>
                    <div class="col-md-3">
                        <select name="branch_id" class="form-select" onchange="this.form.submit()">
                            <option value="">كل الفروع</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" @selected((string) request('branch_id') === (string) $school->id)>
                                    {{ $school->name_school }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="filter" class="form-select">
                            <option value="">كل الأولياء</option>
                            <option value="multi" @selected(request('filter') === 'multi')>بعدة أبناء (إخوة)</option>
                            <option value="synthetic" @selected(request('filter') === 'synthetic')>حسابات مؤقتة من الاستيراد</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                        <a href="{{ route('Parents.index') }}" class="btn btn-outline-secondary">إعادة</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>الولي</th>
                                <th>صلة القرابة</th>
                                <th>الهاتف</th>
                                <th class="col-md-4">الأبناء</th>
                                <th class="col-md-2">العملية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($Parents as $index => $parent)
                                @php
                                    $parentEmail = (string) optional($parent->user)->email;
                                    $isSynthetic = \Illuminate\Support\Str::endsWith($parentEmail, '@import.local');
                                    $isHiddenEmail = $isSynthetic || \Illuminate\Support\Str::endsWith($parentEmail, '@manual.local');
                                @endphp
                                <tr>
                                    <td>{{ $Parents->firstItem() + $index }}</td>
                                    <td>
                                        <span class="text-dark fw-600 fs-16">{{ $parent->prenomwali }}
                                            {{ $parent->nomwali }}</span>
                                        @if ($isSynthetic)
                                            <span class="badge badge-warning-light d-block mt-5"
                                                style="width: fit-content; margin-inline: auto;">حساب مؤقت من
                                                الاستيراد</span>
                                        @elseif (!$isHiddenEmail && $parentEmail)
                                            <span class="text-fade d-block">{{ $parentEmail }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $parent->relationetudiant }}</td>
                                    <td style="direction: ltr; unicode-bidi: embed;">0{{ $parent->numtelephonewali }}</td>
                                    <td class="text-start">
                                        @forelse ($parent->students as $student)
                                            <span class="badge badge-primary-light mb-1 fs-13">
                                                {{ $student->prenom }} {{ $student->nom }}
                                                <small class="text-fade">
                                                    ({{ optional(optional(optional($student->section)->classroom)->schoolgrade)->name_grade }}
                                                    / {{ optional($student->section)->name_section }})
                                                </small>
                                            </span>
                                        @empty
                                            <span class="text-fade">بدون أبناء</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" title="ربط تلميذ"
                                            class="waves-effect waves-light btn btn-success-light btn-circle mx-1 open-link-modal"
                                            data-parent-id="{{ $parent->id }}"
                                            data-parent-name="{{ $parent->prenomwali }} {{ $parent->nomwali }}">
                                            <i class="fa fa-link"></i>
                                        </a>

                                        <a data-bs-target="#modal-edit{{ $parent->id }}" data-bs-toggle="modal"
                                            title="تعديل"
                                            class="waves-effect waves-light btn btn-primary-light btn-circle mx-1"><span
                                                class="icon-Write"><span class="path1"></span><span
                                                    class="path2"></span></span></a>

                                        @if ($parent->students_count == 0)
                                            <a data-bs-target="#modal-delete{{ $parent->id }}" data-bs-toggle="modal"
                                                title="حذف"
                                                class="waves-effect waves-light btn btn-danger-light btn-circle mx-1"><span
                                                    class="icon-Trash1 fs-18"><span class="path1"></span><span
                                                        class="path2"></span></span></a>
                                        @endif
                                    </td>
                                </tr>

                                {{-- مودال التعديل --}}
                                <div class="modal fade" id="modal-edit{{ $parent->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary">
                                                <h5 class="modal-title text-white">تعديل بيانات الولي:
                                                    {{ $parent->prenomwali }} {{ $parent->nomwali }}</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="edit-form{{ $parent->id }}"
                                                    action="{{ route('Parents.update', $parent->id) }}" method="POST">
                                                    @method('patch')
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="form-label">الاسم بالعربية *</label>
                                                                <input type="text" name="prenomar" required
                                                                    value="{{ $parent->getTranslation('prenomwali', 'ar') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="form-label">اللقب بالعربية *</label>
                                                                <input type="text" name="nomar" required
                                                                    value="{{ $parent->getTranslation('nomwali', 'ar') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="form-label">الاسم بالفرنسية</label>
                                                                <input type="text" name="prenomfr"
                                                                    value="{{ $parent->getTranslation('prenomwali', 'fr') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="form-label">اللقب بالفرنسية</label>
                                                                <input type="text" name="nomfr"
                                                                    value="{{ $parent->getTranslation('nomwali', 'fr') }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">صلة القرابة *</label>
                                                                <select name="relation" class="form-select" required>
                                                                    @foreach (['ولي', 'أب', 'أم', 'جد', 'جدة', 'عم', 'خال', 'أخ', 'كفيل'] as $relation)
                                                                        <option value="{{ $relation }}"
                                                                            @selected($parent->relationetudiant === $relation)>
                                                                            {{ $relation }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">رقم الهاتف *</label>
                                                                <input type="text" name="phone" required
                                                                    value="{{ $parent->numtelephonewali }}"
                                                                    class="form-control" style="direction: ltr;">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">البريد الالكتروني</label>
                                                                <input type="email" name="email"
                                                                    value="{{ $isHiddenEmail ? '' : $parentEmail }}"
                                                                    placeholder="{{ $isHiddenEmail ? 'لا يوجد بريد حقيقي — أدخل بريداً لتفعيل الحساب' : '' }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">كلمة سر جديدة (اختياري)</label>
                                                                <input type="password" name="password"
                                                                    class="form-control"
                                                                    placeholder="اتركه فارغًا بدون تغيير">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label class="form-label">العنوان</label>
                                                                <input type="text" name="address"
                                                                    value="{{ $parent->adressewali }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">الولاية</label>
                                                                <input type="text" name="wilaya"
                                                                    value="{{ $parent->wilayawali }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">الدائرة</label>
                                                                <input type="text" name="dayra"
                                                                    value="{{ $parent->dayrawali }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="form-label">البلدية</label>
                                                                <input type="text" name="baladia"
                                                                    value="{{ $parent->baladiawali }}"
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
                document.getElementById('edit-form{{ $parent->id }}').submit();">{{ trans('opt.update2') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- مودال الحذف --}}
                                @if ($parent->students_count == 0)
                                    <div class="modal center-modal fade" id="modal-delete{{ $parent->id }}"
                                        tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <form id="delete-parent-form{{ $parent->id }}"
                                                        action="{{ route('Parents.destroy', $parent->id) }}"
                                                        method="POST">
                                                        @method('Delete')
                                                        @csrf
                                                        <div class="box-body">
                                                            <div class="row">
                                                                <h1>{{ trans('opt.deletemsg') }}</h1>
                                                                <p class="text-fade">سيُحذف حساب الولي
                                                                    {{ $parent->prenomwali }} {{ $parent->nomwali }}
                                                                    نهائياً (لا يملك أبناء مرتبطين).</p>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer modal-footer-uniform">
                                                    <a type="button" class="btn btn-danger"
                                                        data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                                    <a type="button" class="btn btn-primary float-end"
                                                        onclick="event.preventDefault();
                document.getElementById('delete-parent-form{{ $parent->id }}').submit();">{{ trans('opt.delete2') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $Parents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- مودال إضافة ولي --}}
<div class="modal fade" id="modal-add-parent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fa fa-plus me-10"></i>إضافة ولي أمر جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-parent-form" action="{{ route('Parents.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">الاسم بالعربية *</label>
                                <input type="text" name="prenomar" required value="{{ old('prenomar') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">اللقب بالعربية *</label>
                                <input type="text" name="nomar" required value="{{ old('nomar') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">الاسم بالفرنسية</label>
                                <input type="text" name="prenomfr" value="{{ old('prenomfr') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">اللقب بالفرنسية</label>
                                <input type="text" name="nomfr" value="{{ old('nomfr') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">صلة القرابة *</label>
                                <select name="relation" class="form-select" required>
                                    @foreach (['أب', 'أم', 'ولي', 'جد', 'جدة', 'عم', 'خال', 'أخ', 'كفيل'] as $relation)
                                        <option value="{{ $relation }}" @selected(old('relation') === $relation)>
                                            {{ $relation }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">رقم الهاتف *</label>
                                <input type="text" name="phone" required value="{{ old('phone') }}"
                                    class="form-control" style="direction: ltr;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">البريد الالكتروني (اختياري)</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">كلمة السر (اختياري)</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="بدونها يُرسل رابط تهيئة للبريد">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" value="{{ old('address') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">الولاية</label>
                                <input type="text" name="wilaya" value="{{ old('wilaya') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">الدائرة</label>
                                <input type="text" name="dayra" value="{{ old('dayra') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">البلدية</label>
                                <input type="text" name="baladia" value="{{ old('baladia') }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    <h5 class="text-info mt-2"><i class="fa fa-link me-5"></i>ربط أبناء (اختياري)</h5>
                    <hr class="my-10">
                    <div class="form-group">
                        <input type="text" id="add-student-search" class="form-control"
                            placeholder="ابحث عن تلميذ بالاسم أو رقم التعريف...">
                        <div id="add-student-results" class="list-group mt-1"></div>
                        <div id="add-student-chips" class="d-flex flex-wrap gap-1 mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer modal-footer-uniform">
                <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                <a type="submit" class="btn btn-primary float-end"
                    onclick="event.preventDefault(); document.getElementById('add-parent-form').submit();">{{ trans('opt.save') }}</a>
            </div>
        </div>
    </div>
</div>

{{-- مودال ربط تلميذ (مشترك) --}}
<div class="modal fade" id="modal-link-student" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fa fa-link me-10"></i>ربط تلميذ بالولي:
                    <span id="link-parent-name"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="link-student-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="student_id" id="link-student-id" value="">
                    <div class="form-group">
                        <input type="text" id="link-student-search" class="form-control"
                            placeholder="ابحث عن تلميذ بالاسم أو رقم التعريف...">
                        <div id="link-student-results" class="list-group mt-1"></div>
                    </div>
                    <div id="link-student-selected" class="alert alert-success d-none mb-0"></div>
                    <p class="text-fade fs-12 mt-2 mb-0">ملاحظة: التلميذ يُنقل من وليّه الحالي إلى هذا الولي،
                        وإذا أصبح حساب الولي السابق المؤقت بلا أبناء يُحذف تلقائياً.</p>
                </form>
            </div>
            <div class="modal-footer modal-footer-uniform">
                <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                <button type="button" class="btn btn-success float-end" id="link-student-submit" disabled
                    onclick="document.getElementById('link-student-form').submit();">ربط التلميذ</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchUrl = "{{ route('Parents.students.search') }}";
        const linkUrlTemplate = "{{ route('Parents.link', ['parent' => '__ID__']) }}";

        function debounce(fn, delay) {
            let timer = null;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        function renderResults(container, results, onPick) {
            container.innerHTML = '';
            results.forEach(student => {
                const item = document.createElement('a');
                item.href = 'javascript:void(0);';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML =
                    '<strong>' + student.name + '</strong>' +
                    (student.national_id ?
                        ' <span class="badge badge-info-light" style="direction:ltr">' + student.national_id + '</span>' : '') +
                    '<br><small class="text-muted">' + (student.section || '') +
                    (student.guardian ?
                        ' — الولي الحالي: ' + student.guardian +
                        (student.guardian_synthetic ? ' (مؤقت)' : '') : '') +
                    '</small>';
                item.addEventListener('click', () => onPick(student));
                container.appendChild(item);
            });
            if (!results.length) {
                container.innerHTML = '<div class="list-group-item text-muted">لا نتائج</div>';
            }
        }

        function bindSearch(inputEl, resultsEl, onPick) {
            inputEl.addEventListener('input', debounce(function() {
                const query = inputEl.value.trim();
                if (query.length < 2) {
                    resultsEl.innerHTML = '';
                    return;
                }
                fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => renderResults(resultsEl, data.results || [], onPick))
                    .catch(() => resultsEl.innerHTML =
                        '<div class="list-group-item text-danger">خطأ في البحث</div>');
            }, 300));
        }

        // --- مودال الإضافة: اختيار عدة تلاميذ كرقائق ---
        const addSearch = document.getElementById('add-student-search');
        const addResults = document.getElementById('add-student-results');
        const addChips = document.getElementById('add-student-chips');
        const addForm = document.getElementById('add-parent-form');
        const pickedIds = new Set();

        if (addSearch) {
            bindSearch(addSearch, addResults, function(student) {
                if (pickedIds.has(student.id)) return;
                pickedIds.add(student.id);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'student_ids[]';
                hidden.value = student.id;
                hidden.dataset.chipId = student.id;
                addForm.appendChild(hidden);

                const chip = document.createElement('span');
                chip.className = 'badge badge-primary-light fs-13 p-2';
                chip.innerHTML = student.name +
                    ' <a href="javascript:void(0);" class="text-danger ms-1">&times;</a>';
                chip.querySelector('a').addEventListener('click', function() {
                    pickedIds.delete(student.id);
                    addForm.querySelector('input[data-chip-id="' + student.id + '"]')?.remove();
                    chip.remove();
                });
                addChips.appendChild(chip);

                addResults.innerHTML = '';
                addSearch.value = '';
            });
        }

        // --- مودال الربط المشترك ---
        const linkModalEl = document.getElementById('modal-link-student');
        const linkModal = new bootstrap.Modal(linkModalEl);
        const linkForm = document.getElementById('link-student-form');
        const linkSearch = document.getElementById('link-student-search');
        const linkResults = document.getElementById('link-student-results');
        const linkSelected = document.getElementById('link-student-selected');
        const linkStudentId = document.getElementById('link-student-id');
        const linkSubmit = document.getElementById('link-student-submit');

        bindSearch(linkSearch, linkResults, function(student) {
            linkStudentId.value = student.id;
            linkSelected.classList.remove('d-none');
            linkSelected.innerHTML = '<i class="fa fa-check me-5"></i>المحدد: <strong>' + student.name +
                '</strong>' + (student.guardian ? ' — سينقل من: ' + student.guardian : '');
            linkSubmit.disabled = false;
            linkResults.innerHTML = '';
            linkSearch.value = '';
        });

        document.querySelectorAll('.open-link-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                const parentId = this.getAttribute('data-parent-id');
                document.getElementById('link-parent-name').textContent =
                    this.getAttribute('data-parent-name');
                linkForm.action = linkUrlTemplate.replace('__ID__', parentId);
                linkStudentId.value = '';
                linkSelected.classList.add('d-none');
                linkSubmit.disabled = true;
                linkSearch.value = '';
                linkResults.innerHTML = '';
                linkModal.show();
            });
        });
    });
</script>
@endsection
