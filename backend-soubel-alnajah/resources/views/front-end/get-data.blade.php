@if (!$Publications ->isEmpty())
@foreach ($Publications as $pub)
            <div class="blog-post mb-30">
                
                <div class="entry-image clearfix">
                    <div class="owl-carousel bottom-dots-center owl-theme" data-nav-dots="true" data-autoplay="true"  data-items="1" data-md-items="1" data-sm-items="1" data-xs-items="1" data-xx-items="1">
                       
                        @foreach ($pub->galleries as $gal)
                        @foreach(json_decode($gal->img_url, true) as $images)


                        <div class="item">
                            <img src="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('publications.media', now()->addHours(12), ['filename' => $images]) }}" alt="">
                        </div>
                        
                          @endforeach 
                          @endforeach 


                    </div>
          
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
                      
                          <div class="widget">
                            <ul class="list-inline mb-0">
                                <li><a href="https://www.facebook.com/%D9%85%D8%AF%D8%B1%D8%B3%D8%A9-%D8%B3%D8%A8%D9%84-%D8%A7%D9%84%D9%86%D8%AC%D8%A7%D8%AD-%D8%A7%D9%84%D8%AE%D8%A7%D8%B5%D8%A9-%D8%A8%D8%A7%D9%84%D9%88%D8%A7%D8%AF%D9%8A-1732169393669163" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook"><i class="fa fa-facebook"></i></a></li>
                                <li><a href="https://www.instagram.com/ecolenadjah39/" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram"><i class="fa fa-instagram"></i></a></li> 
                                <li><a href="https://www.youtube.com/channel/UCL7Lo3O794hhVipQA8nen2A" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube"><i class="fa fa-youtube"></i></a></li>  
                            </ul>							
                        </div>

                          {{-- <footer class="blockquote-footer">{{ $pub->grade->school->name_school }}</footer> --}}
                    
                        </blockquote>	
                </div>
            </div>
            @php($lastId = $pub->id) 
 @endforeach

 <button type="button" data-id="{{ $lastId }}" id="loadMoreButton" class="waves-effect waves-light btn btn-rounded btn-info mb-5">{{ trans('pub.readmore') }}</button>

@else
<h1>test</h1>
@endif
