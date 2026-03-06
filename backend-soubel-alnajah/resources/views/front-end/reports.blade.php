@extends('layouts.masterhome')

@section('title')
    كشوف النقاط
@endsection

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <h2 class="page-title text-white">كشوف النقاط</h2>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <div class="box">
            <div class="box-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-20">
                    <h4 class="mb-0">النتائج المتاحة</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('Chats.index') }}" class="btn btn-primary btn-sm">المحادثات</a>
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">العودة للوحة</a>
                    </div>
                </div>

                @if($students->isEmpty())
                    <div class="alert alert-info mb-0">لا توجد كشوف نقاط متاحة حاليًا.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>التلميذ</th>
                                    <th>القسم</th>
                                    <th>الفصل الأول</th>
                                    <th>الفصل الثاني</th>
                                    <th>الفصل الثالث</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php
                                        $locale = app()->getLocale();
                                        $firstName = $student->getTranslation('prenom', $locale);
                                        $lastName = $student->getTranslation('nom', $locale);
                                        $section = $student->section;
                                        $sectionName = $section ? $section->getTranslation('name_section', $locale) : '-';
                                        $classroomName = $section && $section->classroom ? $section->classroom->getTranslation('name_class', $locale) : '-';
                                        $note = $student->noteStudent;
                                    @endphp
                                    <tr>
                                        <td>{{ $firstName }} {{ $lastName }}</td>
                                        <td>{{ $classroomName }} - {{ $sectionName }}</td>
                                        @foreach($reportColumns as $column)
                                            @php($filename = $note?->{$column['key']})
                                            <td>
                                                @if($filename)
                                                    <a href="{{ route('DisplayNoteFromAdmin', ['url' => $filename]) }}" class="btn btn-warning btn-sm" target="_blank">عرض</a>
                                                    <a href="{{ route('DownloadNoteFromAdmin', ['url' => $filename]) }}" class="btn btn-success btn-sm">تنزيل</a>
                                                @else
                                                    <span class="text-muted">غير متوفر</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
