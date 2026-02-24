@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('student.abdence') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger col-md-6">
                    <p>{{ $error }}</p>
                </div>
            @endforeach
        @endif

        <div class="box-body">
            <div class="table-responsive">
                <table id="example5" class="table table-bordered text-center align-middle" style="width:100%">
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
                        @foreach ($Absence as $absence)
                            <tr>
                                {{-- الترقيم --}}
                                <td>{{ $loop->iteration }}</td>

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
            </div>
        </div>

    </div>
</div>
@endsection

@section('jsa')
<script src="{{ asset('assets/vendor_components/datatable/datatables.min.js') }}"></script>
@include('layoutsadmin.datatabels')
@endsection
