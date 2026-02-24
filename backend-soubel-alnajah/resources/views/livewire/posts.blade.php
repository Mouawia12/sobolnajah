<div class="row">

    <div class="col-lg-9 col-md-8 col-12">

        @foreach ($Publication as $pub)
            <div class="blog-post mb-30">

                <div class="entry-image clearfix">
                    @foreach ($pub->galleries as $gal)
                        <div class="item">
                            <img src="{{ asset('storage/agenda/'.json_decode($gal->img_url, true)[0]) }}" alt="">
                        </div>
                    @endforeach
                </div>

                <div class="blog-detail">
                    <div class="entry-meta mb-10">
                        <ul class="list-unstyled">
                            <li>
                                <a href="#">
                                    <i class="fa fa-heart-o"></i>{{ $pub->like }}
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fa fa-calendar-o"></i>{{ $pub->created_at }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <hr>

                    <div class="entry-title mb-10">
                        <a href="#" class="fs-24">{{ $pub->title }}</a>
                    </div>

                    <div class="entry-content text-end">
                        <a href="{{ route('Publications.show', $pub->id) }}" class="text-danger">
                            {{ trans('pub.readmore') }}
                        </a>
                    </div>

                    <blockquote class="blockquote mt-20 pb-0 mb-0">
                        {{-- 
                        <p>
                            مهما بلغت مبلغك من السُوء هناك شخص واحد في العالم يراك جيدًا بالنسبة له.
                        </p>
                        --}}
                        <div class="widget">
                            <ul class="list-inline mb-0">
                                <li>
                                    <a href="https://www.facebook.com/مدرسة-سبل-النجاح-الخاصة-بالوادي-1732169393669163"
                                       class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook">
                                        <i class="fa fa-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.instagram.com/ecolenadjah39/"
                                       class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram">
                                        <i class="fa fa-instagram"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.youtube.com/channel/UCL7Lo3O794hhVipQA8nen2A"
                                       class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube">
                                        <i class="fa fa-youtube"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {{-- <footer class="blockquote-footer">{{ $pub->grade->school->name_school }}</footer> --}}
                    </blockquote>
                </div>
            </div>
        @endforeach

        <button type="button" wire:click="load"
                class="waves-effect waves-light btn btn-rounded btn-info mb-5">
            {{ trans('pub.readmore') }}
        </button>

    </div>

    <div class="col-lg-3 col-md-4 col-12">
        <div class="side-block px-20 py-10 bg-white position-sticky t-100">

            <div class="widget courses-search-bx placeholdertx mb-10">
                <div class="form-group">
                    <div class="input-group">
                        <label class="form-label">{{ trans('inscription.search') }}</label>
                        <input name="name" type="text" required class="form-control">
                    </div>
                </div>
            </div>

            <div class="widget clearfix">
                <h4 class="pb-15 mb-15 bb-1">
                    {{ trans('pub.grade') }}
                    <span class="mx-0 badge badge-info-light">{{ $Publication->count() }}</span>
                </h4>

                <ul class="list list-unstyled">
                    <li>
                        <a href="#" wire:click="allpub()">
                            <i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{ trans('pub.all') }}
                        </a>
                    </li>

                    @foreach ($Grade as $g)
                        <li>
                            <a href="#" wire:click="grade({{ $g->id }})">
                                <i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i>{{ $g->name_grades }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="widget clearfix">
                <h4 class="pb-15 mb-25 bb-1">
                    {{ trans('pub.agenda') }}
                    <span class="mx-0 badge badge-danger-light">{{ $Agenda->count() }}</span>
                </h4>
                <ul class="list list-unstyled">
                    <li>
                        <a href="#" wire:click="allpub()">
                            <i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{ trans('pub.all') }}
                        </a>
                    </li>

                    @foreach ($Agenda as $a)
                        <li>
                            <a href="#" wire:click="agenda({{ $a->id }})">
                                <i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{ $a->name_agenda }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <?php $i=0; ?>

            <div class="widget mb-10">
                <h4 class="pb-15 mb-25 bb-1">{{ trans('contact.Getintouch') }}</h4>
                <form class="gray-form">
                    <div class="form-group">
                        <input type="email" class="form-control" id="exampleInputEmail1"
                               placeholder="{{ trans('contact.firstname') }}">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="exampleInputphone"
                               placeholder="{{ trans('contact.email') }}">
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" rows="4"
                                  placeholder="{{ trans('contact.subject') }}"></textarea>
                    </div>
                    <a class="btn btn-primary w-p100" href="#">{{ trans('contact.sendmessage') }}</a>
                </form>
            </div>

        </div>
    </div>

</div>
