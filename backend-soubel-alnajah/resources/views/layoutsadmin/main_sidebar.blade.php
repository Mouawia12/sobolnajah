<aside class="main-sidebar">
    <section class="sidebar position-relative">
        <div class="multinav">
            <div class="multinav-scroll" style="height: 100%;">
                <ul class="sidebar-menu" data-widget="tree">
                    @php
                        $user = auth()->user();
                        $isAccountantOnly = auth()->check()
                            && $user->hasRole('accountant')
                            && !$user->hasRole('admin');
                    @endphp

                    @if($isAccountantOnly)
                        <li class="header">المالية</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="mdi mdi-cash-multiple me-15"><span class="path1"></span><span class="path2"></span></i>
                                <span>الإدارة المالية</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('accounting.contracts.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>العقود المالية</a></li>
                                <li><a href="{{ route('accounting.payments.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>الدفعات والوصولات</a></li>
                            </ul>
                        </li>
                    @else
                        <li class="header">الهيكل المدرسي</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="mdi mdi-school me-15"><span class="path1"></span><span class="path2"></span></i>
                                <span>إعدادات المدرسة</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('Schools.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.addecoles') }}</a></li>
                                <li><a href="{{ route('Schoolgrades.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.addclasse') }}</a></li>
                                <li><a href="{{ route('Classes.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.addclasseroom') }}</a></li>
                                <li><a href="{{ route('Sections.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.addsection') }}</a></li>
                            </ul>
                        </li>

                        <li class="header">الطلاب</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="si-people si"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('inscription.student') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('Inscriptions.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('inscription.studentinscription') }}</a></li>
                                <li><a href="{{ route('Students.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.studentlist') }}</a></li>
                                <li><a href="{{ route('Students.create') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.addstudent') }}</a></li>
                                <li><a href="{{ route('Promotions.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.promotion') }}</a></li>
                                <li><a href="{{ route('graduated.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.graduated') }}</a></li>
                                <li><a href="{{ route('Absences.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.Absences') }}</a></li>
                            </ul>
                        </li>

                        <li class="header">المعلمون</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="si-people si"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('teacher.teacher') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('Teachers.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('teacher.teacherlist') }}</a></li>
                            </ul>
                        </li>

                        <li class="header">الأجندة والمحتوى</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="icon-Write"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('main_header.agendascolaire') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('Agendas.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.agenda') }}</a></li>
                                <li><a href="{{ route('Grades.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.grades') }}</a></li>
                                <li><a href="{{ route('timetables.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>الجداول</a></li>
                                <li><a href="{{ route('Publications.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.publication') }}</a></li>
                                <li><a href="{{ route('Exames.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('exam.exam') }}</a></li>
                            </ul>
                        </li>

                        <li class="header">{{ trans('main_header.recruitment') }}</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="mdi mdi-briefcase-check-outline me-15"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('main_sidebar.recruitment_management') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('JobPosts.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.recruitment_posts') }}</a></li>
                                <li><a href="{{ route('recruitment.applications.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('main_sidebar.recruitment_applications') }}</a></li>
                            </ul>
                        </li>

                        @if(auth()->check() && ($user->hasRole('admin') || $user->hasRole('accountant')))
                            <li class="header">المالية</li>
                            <li class="treeview">
                                <a href="#">
                                    <i class="mdi mdi-cash-multiple me-15"><span class="path1"></span><span class="path2"></span></i>
                                    <span>الإدارة المالية</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-right pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="{{ route('accounting.contracts.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>العقود المالية</a></li>
                                    <li><a href="{{ route('accounting.payments.index') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>الدفعات والوصولات</a></li>
                                </ul>
                            </li>
                        @endif

                        <li class="header">{{ trans('opt.application') }}</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="si-layers si"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('opt.application') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('chat.ai') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ trans('opt.chatai') }}</a></li>
                                <li><a href="{{ route('Chats.index') }}"><i class="icon-Speach-Bubble4"><span class="path1"></span><span class="path2"></span></i>{{ trans('opt.chat_users') }}</a></li>
                            </ul>
                        </li>

                        <li class="header">{{ trans('main_sidebar.langue') }}</li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-refresh"><span class="path1"></span><span class="path2"></span></i>
                                <span>{{ trans('main_sidebar.langue') }}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-right pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    @if(in_array($properties['native'], ['العربية', 'English', 'français'], true))
                                        <li>
                                            <a hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                                <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ $properties['native'] }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </section>

    <div class="sidebar-footer">
        <a href="{{ route('admin.password.change.page') }}" class="link" data-bs-toggle="tooltip" title="{{ trans('main_header.setting') }}"><span class="icon-Settings-2"></span></a>
        <a href="#" class="link" data-bs-toggle="tooltip" title="Email"><span class="icon-Mail"></span></a>
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form2').submit();"
           class="link" data-bs-toggle="tooltip" title="{{ trans('main_sidebar.logout') }}"><span class="icon-Lock-overturning"><span class="path1"></span><span class="path2"></span></span></a>

        <form id="logout-form2" action="/logout/{{ App::currentLocale()}}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>
