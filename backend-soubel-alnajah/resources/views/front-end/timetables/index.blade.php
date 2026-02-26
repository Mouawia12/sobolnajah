@extends('layouts.masterhome')
@section('title', 'الجداول')

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center"><h2 class="page-title text-white">الجداول الدراسية</h2></div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <form method="GET" class="row mb-4">
            <div class="col-md-6">
                <select name="section_id" class="form-select">
                    <option value="">كل الأقسام</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected((string)request('section_id') === (string)$section->id)>
                            {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <button class="btn btn-primary">عرض</button>
            </div>
        </form>

        <div class="row">
            @forelse($timetables as $timetable)
                <div class="col-md-6 mb-3">
                    <div class="box">
                        <div class="box-body">
                            <h4>{{ $timetable->title ?: 'الجدول الدراسي' }}</h4>
                            <p>{{ $timetable->section->classroom->schoolgrade->name_grade ?? '' }} / {{ $timetable->section->classroom->name_class ?? '' }} / {{ $timetable->section->name_section ?? '' }}</p>
                            <a class="btn btn-info" href="{{ route('public.timetables.show', $timetable) }}">عرض الجدول</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">لا توجد جداول منشورة حاليا.</div></div>
            @endforelse
        </div>
        {{ $timetables->links() }}
    </div>
</section>
@endsection
