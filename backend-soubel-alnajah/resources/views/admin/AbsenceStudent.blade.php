@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('student.abdence') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box-body">
            <form method="GET" class="row mb-3">
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="بحث: الاسم / البريد / الهاتف">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
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
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary" type="submit">تصفية</button>
                    <a href="{{ route('Absences.index') }}" class="btn btn-outline-secondary">إعادة</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th> {{-- عمود الترقيم --}}
                            <th>{{ trans('inscription.student') }}</th>
                            <th>{{ trans('student.date') }}</th> {{-- عمود التاريخ --}}
                            @php
                                $hours = range(8, 16); // من 8 إلى 16 فقط (حذف 17)
                            @endphp
                            @foreach ($hours as $hour)
                                <th>{{ $hour }}:00</th>
                            @endforeach
                            <th>Section</th> {{-- العمود الجديد --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($Absence as $index => $absence)
                            <tr>
                                {{-- الترقيم --}}
                                <td>{{ $Absence->firstItem() + $index }}</td>

                                {{-- بيانات الطالب --}}
                                <td class="col-md">
                                    <div style="white-space: nowrap; font-size:14px; font-weight:600;">
                                        {{ $absence->student->prenom }} {{ $absence->student->nom }}
                                    </div>
                                    <div style="font-size:12px; color:#6c757d;">
                                        0{{ $absence->student->numtelephone }}
                                    </div>
                                </td>

                                {{-- عرض التاريخ --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($absence->date)->format('d-m-Y') }}
                                </td>

                                {{-- الساعات --}}
                                @for ($h = 1; $h <= 9; $h++)
                                    @php $hourKey = "hour_$h"; @endphp
                                    <td>
                                        @if ($absence->$hourKey)
                                            <span style="color: green; font-size:18px;">&#10004;</span>
                                        @else
                                            <span style="color: red; font-size:18px;">&#10006;</span>
                                        @endif
                                    </td>
                                @endfor

                                {{-- العمود الجديد فارغ --}}
                                <td>{{ $absence->student->section->name_section }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th>{{ trans('student.date') }}</th>
                            @foreach ($hours as $hour)
                                <th>{{ $hour }}:00</th>
                            @endforeach
                            <th>Section</th> {{-- نفس العمود الجديد --}}
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3">
                    {{ $Absence->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('jsa')
@endsection
