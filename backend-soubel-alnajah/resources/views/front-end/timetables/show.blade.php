@extends('layouts.masterhome')
@section('title', $timetable->title ?: 'الجدول الدراسي')

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center"><h2 class="page-title text-white">{{ $timetable->title ?: 'الجدول الدراسي' }}</h2></div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <div class="box">
            <div class="box-body">
                <h5>{{ $timetable->section->classroom->schoolgrade->name_grade ?? '' }} / {{ $timetable->section->classroom->name_class ?? '' }} / {{ $timetable->section->name_section ?? '' }} - {{ $timetable->academic_year }}</h5>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>اليوم</th>
                            <th>الحصة</th>
                            <th>التوقيت</th>
                            <th>المادة</th>
                            <th>الأستاذ</th>
                            <th>القاعة</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($timetable->entries as $entry)
                            <tr>
                                <td>{{ $entry->day_of_week }}</td>
                                <td>{{ $entry->period_index }}</td>
                                <td>{{ $entry->starts_at ?: '-' }} - {{ $entry->ends_at ?: '-' }}</td>
                                <td>{{ $entry->subject_name }}</td>
                                <td>{{ optional($entry->teacher)->name ?? '-' }}</td>
                                <td>{{ $entry->room_name ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
