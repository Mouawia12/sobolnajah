<div class="row">
<div class="col-lg-9 col-md-8 col-12">
     
    @foreach ($Publication as $pub)
                    <div class="blog-post mb-30">
                        
                        <div class="entry-image clearfix">
                            <ul class="list-unstyled grid-post">
                              
                               
                              
                                {{-- @foreach ($pub->galleries as $gal) --}}
                                @foreach(json_decode($pub->gallery, true) as $images)
    
                                 <li>
                                   <img src="{{ asset('storage/'.$images.'')}}"  alt=""/>
                                </li>
                                  @endforeach 
    
                            </ul>
                        </div>
                        <div class="blog-detail">
                            <div class="entry-meta mb-10">
                                <ul class="list-unstyled">
                                    <li><a href="#"><i class="fa fa-heart-o"></i>{{ $pub->like}}</a></li>
                                    <li><a href="#"><i class="fa fa-calendar-o"></i>{{ $pub->created_at}}</a></li>
                                </ul>
                            </div>
                            <hr>
                            <div class="entry-title mb-10">
                                <a href="#" class="fs-24">{{ $pub->title}}</a>
                            </div>
                            <div class="entry-content">
                                <p>{{ $pub->body }}</p>
                            </div>	
                              <blockquote class="blockquote mt-20 pb-0 mb-0">
                                  <p>
                                      مهما بلغت مبلغك من السُوء هناك شخص واحد في العالم يراك جيدًا بالنسبة له.
    
                                    </p>
                                  <div class="widget">
                                    <ul class="list-inline mb-0">
                                        <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-twitter"><i class="fa fa-twitter"></i></a></li> 
                                        <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram"><i class="fa fa-instagram"></i></a></li> 
                                        <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-linkedin"><i class="fa fa-linkedin"></i></a></li> 
                                        <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube"><i class="fa fa-youtube"></i></a></li>  
                                    </ul>							
                                </div>
    
                                  {{-- <footer class="blockquote-footer">{{ $pub->grade->school->name_school }}</footer> --}}
                            
                                </blockquote>	
                        </div>
                    </div>
         @endforeach
    
    
    
    
                    <div class="box ">
                     
                    <div aria-label="Page navigation example ">
                         <ul class="pagination mb-0 ">
                        
                         <li class="page-item text-center"><a wire:click="load2" class="page-link" >load more ...</a></li>
                    </ul>
             
                    </div>
             </div>
            
    </div>

    <div class="col-lg-3 col-md-4 col-12">
        <div class="side-block px-20 py-10 bg-white ">
            <div class="widget courses-search-bx placeholdertx mb-10">
                <div class="form-group">
                    <div class="input-group">
                        <label class="form-label">Search...</label>
                        <input name="name" type="text" required="" class="form-control">
                    </div>
                </div>
            </div>	
            <div class="widget clearfix">
                <h4 class="pb-15 mb-15 bb-1">Grade       <span class="mx-0 badge badge-info-light">{{$Publication->count()}}</span>
                </h4>
                
                <ul class="list list-unstyled">
                   
                    @foreach ($Grade as $g)
                            
                        <li><a href="#"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i>{{$g->name_grades}}</a></li>

                    @endforeach

                </ul>
            </div>
            
            <div class="widget clearfix">
                <h4 class="pb-15 mb-25 bb-1">Agenda      <span class="mx-0 badge badge-danger-light">{{$Agenda->count()}}</span></h4>
                <ul class="list list-unstyled">
                    @foreach ($Agenda as $a)
                    <li><a class="stretched-link" href="#" wire:click="load({{$a->id}})"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{$a->name_agenda}}</a></li>
                    @endforeach

                </ul>
            </div>
            <div class="widget clearfix">
                <h4 class="pb-15 mb-25 bb-1">Archives</h4>
                <ul class="list list-unstyled">
                    <li><a href="#"><i class="fa fa-angle-double-right"></i> November 2020</a></li>
                    <li><a href="#"><i class="fa fa-angle-double-right"></i> October 2020</a></li>
                    <li><a href="#"><i class="fa fa-angle-double-right"></i> September 2020</a></li>
                    <li><a href="#"><i class="fa fa-angle-double-right"></i> August 2020</a></li>
                    <li><a href="#"><i class="fa fa-angle-double-right"></i> July 2020</a></li>
                </ul>
            </div>
            <div class="widget">
                <h4 class="pb-15 mb-25 bb-1">Tags</h4>
                <div class="widget-tags">
                    <ul class="list-unstyled">
                        <li><a href="#">Bootstrap</a></li>
                        <li><a href="#">HTML5</a></li>
                        <li><a href="#">Wordpress</a></li>
                        <li><a href="#">CSS3</a></li>
                        <li><a href="#">Creative</a></li>
                        <li><a href="#">Multipurpose</a></li>
                        <li><a href="#">Bootstrap</a></li>
                        <li><a href="#">HTML5</a></li>
                        <li><a href="#">Wordpress</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="widget">
                <h4 class="pb-15 mb-25 bb-1">Recent Posts </h4>
               
            
                
            <?php $i=0; ?>
       
            @for ($i ; $i <= 3; $i++)
                @if ($i < $Publication->count())
                    <div class="recent-post clearfix">
                    <div class="recent-post-image">
                        <img class="img-fluid bg-primary-light" src="../images/front-end-img/courses/cor-logo-3.png" alt="">
                    </div>
                    <div class="recent-post-info">
                        <a href="#">{{    $Publication[$i]->title; }} </a>
                        <span><i class="fa fa-calendar-o"></i>{{ $Publication[$i]->created_at; }} </span>
                    </div>
                </div>
                @endif     
            @endfor 
                
            </div>
            <div class="widget">
                <h4 class="pb-15 mb-25 bb-1">Newsletter</h4>
                <div class="widget-newsletter">
                    <div class="newsletter-icon">
                        <i class="fa fa-envelope-o"></i>
                    </div>
                    <div class="newsletter-content">
                        <i>Fusce tincidunt, metus at dignissim fringilla, lorem velit posuere mi, sed pretium turpis leo ac metus. Aenean sit amet sapien eget eros </i>
                    </div>
                    <div class="newsletter-form mt-20">
                        <div class="form-group">
                            <input type="email" class="form-control" id="exampleInputEmail2" placeholder="Name">
                        </div>
                        <a class="btn btn-primary w-p100" href="#">Submit</a>
                    </div>
                </div>
            </div>
            <div class="widget">
                <h4 class="pb-15 mb-25 bb-1">Testimonials</h4>
                <div class="owl-carousel" data-nav-dots="false" data-items="1" data-md-items="1" data-sm-items="1" data-xs-items="1" data-xx-items="1">
                    <div class="item">
                        <div class="testimonial-widget">
                            <div class="testimonial-content">
                                <p>In odio metus, porta vitae neque vitae, faucibus viverra orci. Quisque in lorem aliquam, ullamcorper turpis a, aliquam dui. In accumsan aliquam viverra.</p>
                            </div>
                            <div class="testimonial-info mt-20">
                                <div class="testimonial-avtar">
                                    <img class="img-fluid" src="../images/front-end-img/avatar/1.jpg" alt="">
                                </div>
                                <div class="testimonial-name">
                                    <strong>Johen Doe</strong>
                                    <span>Project Manager</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="testimonial-widget">
                            <div class="testimonial-content">
                                <p>Morbi condimentum leo eu lacinia accumsan. Phasellus cursus rhoncus elit, mattis convallis sapien efficitur non phasellus et erat sapien phasellus. </p>
                            </div>
                            <div class="testimonial-info mt-20">
                                <div class="testimonial-avtar">
                                    <img class="img-fluid" src="../images/front-end-img/avatar/2.jpg" alt="">
                                </div>
                                <div class="testimonial-name">
                                    <strong>Johen Doe</strong>
                                    <span>Design</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="testimonial-widget">
                            <div class="testimonial-content">
                                <p>In odio metus, porta vitae neque vitae, faucibus viverra orci. Quisque in lorem aliquam, ullamcorper turpis a, aliquam dui. In accumsan aliquam viverra.</p>
                            </div>
                            <div class="testimonial-info mt-20">
                                <div class="testimonial-avtar">
                                    <img class="img-fluid" src="../images/front-end-img/avatar/3.jpg" alt="">
                                </div>
                                <div class="testimonial-name">
                                    <strong>Johen Doe</strong>
                                    <span>Project Manager</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="testimonial-widget">
                            <div class="testimonial-content">
                                <p>Morbi condimentum leo eu lacinia accumsan. Phasellus cursus rhoncus elit, mattis convallis sapien efficitur non phasellus et erat sapien phasellus. </p>
                            </div>
                            <div class="testimonial-info mt-20">
                                <div class="testimonial-avtar">
                                    <img class="img-fluid" src="../images/front-end-img/avatar/4.jpg" alt="">
                                </div>
                                <div class="testimonial-name">
                                    <strong>Johen Doe</strong>
                                    <span>Design</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="widget mb-10">
                <h4 class="pb-15 mb-25 bb-1">Quick contact</h4>
                <form class="gray-form">
                    <div class="form-group">
                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Name">
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="exampleInputphone" placeholder="Email">
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" rows="4" placeholder="message"></textarea>
                    </div>
                    <a class="btn btn-primary w-p100" href="#">Submit</a>
                </form>
            </div>	
        </div>
    </div>
</div>
    
    
    
    
            
    
             
        