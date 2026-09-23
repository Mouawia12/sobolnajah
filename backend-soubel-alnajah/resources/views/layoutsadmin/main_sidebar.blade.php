<aside class="main-sidebar">
    <section class="sidebar position-relative">
        <div class="multinav">
            <div class="multinav-scroll" style="height: 100%;">
                <ul class="sidebar-menu" data-widget="tree">
                    @php
                        $user = auth()->user();
                        $isAdmin = $user && $user->hasRole('admin');
                        $menuAccess = app(\App\Services\MenuAccessService::class);
                        $allowedSections = $menuAccess->allowedSections($user);
                        $catalog = \App\Support\MenuCatalog::sections();
                        $dashboardUrl = $isAdmin
                            ? url('/admin')
                            : ($user && $user->hasRole('accountant')
                                ? route('accountant.dashboard')
                                : ($user && $user->hasRole('teacher') ? route('teacher.dashboard') : route('home')));
                    @endphp

                    {{-- الرئيسية (دائماً) --}}
                    <li class="header">{{ __('الرئيسية') }}</li>
                    <li>
                        <a href="{{ $dashboardUrl }}">
                            <i class="mdi mdi-view-dashboard me-15"></i>
                            <span>{{ __('الرئيسية') }}</span>
                        </a>
                    </li>

                    {{-- الأدوار والصلاحيات (للمسؤول فقط، غير قابل للإخفاء) --}}
                    @if($isAdmin)
                        <li class="header">{{ trans('roles.title') }}</li>
                        <li>
                            <a href="{{ route('roles.index') }}">
                                <i class="mdi mdi-shield-account me-15"></i>
                                <span>{{ trans('roles.title') }}</span>
                            </a>
                        </li>
                    @endif

                    {{-- الأقسام حسب صلاحيات الدور --}}
                    @foreach($catalog as $key => $section)
                        @continue(!in_array($key, $allowedSections, true))
                        @php
                            // إخفاء الروابط المحمية بدور لا يملكه المستخدم (كانت تعطي 403).
                            $links = $isAdmin
                                ? $section['links']
                                : array_values(array_filter($section['links'], fn ($link) => $menuAccess->canAccessRoute($user, $link['route'])));
                        @endphp
                        @continue(count($links) === 0)

                        @if(count($links) === 1)
                            <li class="header">{{ $section['label'] }}</li>
                            <li>
                                <a href="{{ route($links[0]['route'], $links[0]['params'] ?? []) }}">
                                    <i class="{{ $section['icon'] }} me-15"></i>
                                    <span>{{ $links[0]['label'] }}</span>
                                </a>
                            </li>
                        @else
                            <li class="header">{{ $section['label'] }}</li>
                            <li class="treeview">
                                <a href="#">
                                    <i class="{{ $section['icon'] }} me-15"></i>
                                    <span>{{ $section['label'] }}</span>
                                    <span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
                                </a>
                                <ul class="treeview-menu">
                                    @foreach($links as $link)
                                        <li>
                                            <a href="{{ route($link['route'], $link['params'] ?? []) }}">
                                                <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>{{ $link['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

                    {{-- اللغة (دائماً) --}}
                    <li class="header">{{ trans('main_sidebar.langue') }}</li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-refresh"><span class="path1"></span><span class="path2"></span></i>
                            <span>{{ trans('main_sidebar.langue') }}</span>
                            <span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
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
                </ul>
            </div>
        </div>
    </section>

    <div class="sidebar-footer">
        <a href="{{ route('password.change.page') }}" class="link" data-bs-toggle="tooltip" title="{{ trans('main_header.setting') }}"><span class="icon-Settings-2"></span></a>
        <a href="#" class="link" data-bs-toggle="tooltip" title="Email"><span class="icon-Mail"></span></a>
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form2').submit();"
           class="link" data-bs-toggle="tooltip" title="{{ trans('main_sidebar.logout') }}"><span class="icon-Lock-overturning"><span class="path1"></span><span class="path2"></span></span></a>

        <form id="logout-form2" action="/logout/{{ App::currentLocale()}}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>
