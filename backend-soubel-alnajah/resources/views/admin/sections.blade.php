@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('section.title') }}
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
      <div class="box">
         <div class="box-body">
            <form method="GET" action="{{ route('Sections.index') }}" class="admin-form-panel">
               <div class="row">
                  <div class="col-md-3">
                     <label class="form-label">بحث</label>
                     <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم القسم أو القاعة">
                  </div>
                  <div class="col-md-3">
                     <label class="form-label">{{ trans('inscription.niveau') }}</label>
                     <select name="grade_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($SchoolgradeFilterOptions as $gradeOption)
                           <option value="{{ $gradeOption->id }}" @selected((string) request('grade_id') === (string) $gradeOption->id)>
                              {{ $gradeOption->name_grade }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3">
                     <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                     <select name="classroom_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($ClassroomFilterOptions as $classOption)
                           <option value="{{ $classOption->id }}" @selected((string) request('classroom_id') === (string) $classOption->id)>
                              {{ $classOption->name_class }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-2">
                     <label class="form-label">{{ trans('inscription.status') }}</label>
                     <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="1" @selected(request('status') === '1')>{{ trans('inscription.sectionopen') }}</option>
                        <option value="0" @selected(request('status') === '0')>{{ trans('inscription.sectionclose') }}</option>
                     </select>
                  </div>
                  <div class="col-md-1 d-flex align-items-end">
                     <button class="btn btn-primary w-p100" type="submit">بحث</button>
                  </div>
               </div>
            </form>
         </div>
      </div>
      <?php $j = 0; ?>
      @forelse ($Schoolgrade as $grade)
      <div class="box box-slided-up">
         <div class="box-header with-border bg-info">
            <h4 class="box-title"><strong>{{ $grade->school->name_school}} - {{ $grade->name_grade}}</strong></h4>
               <ul class="box-controls pull-right">
                  <li><a class="box-btn-slide text-white" href="#"></a></li>
                  <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
               </ul>
         </div>

         <div class="box-body">
            <h3 class="box-title"><a data-bs-target="#modal-store" data-bs-toggle="modal" class="btn btn-info">{{ trans('main_sidebar.addclasseroom') }}</a></h3>

               <div class="table-responsive">
               <table class="table table-bordered text-center" style="width:100%">
               <thead>
                  <tr>
                     <th>#</th>
                     <th>{{ trans('inscription.Anneescolaire') }}</th>                     
                     <th>{{ trans('inscription.section') }}</th>
                     <th>{{ trans('inscription.ecole') }}</th>
                     <th>{{ trans('inscription.status') }}</th>
                     <th class="col-md-3">{{ trans('inscription.action') }}</th>

                  </tr>
               </thead>
               <tbody>

               <?php $i = 0; ?>
               @foreach ($grade->sections as $sc)
                   <?php $i++; ?>
                     <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $sc->classroom->name_class }}</td>
                        <td>{{ $sc->name_section }}</td>
                        <td>{{ $sc->classroom->schoolgrade->school->name_school }}</td>

                        <td>
                           <form method="POST" action="{{ route('Sections.status', $sc->id) }}">
                              @csrf
                              <input type="hidden" name="statu" value="{{ (int) !$sc->Status }}">
                              @if ($sc->Status == 1)
                                 <button type="submit" class="btn p-0 border-0 bg-transparent">
                                    <span class="badge badge-success-light">{{ trans('inscription.sectionopen') }}</span>
                                 </button>
                              @else
                                 <button type="submit" class="btn p-0 border-0 bg-transparent">
                                    <span class="badge badge-danger-light">{{ trans('inscription.sectionclose') }}</span>
                                 </button>
                              @endif
                           </form>
                        </td>



                        <td class="col-md-3">
                        <a href="{{ route('NoteStudents.show' ,$sc->id) }}" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                        <a data-bs-target="#modal-teacher{{ $sc->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-success-light btn-circle mx-5"><span class="fa fa-mortar-board"><span class="path1"></span><span class="path2"></span></span></a>
                        <a data-bs-target="#modal-center{{ $sc->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                        <form method="POST" action="{{ route('Sections.destroy', $sc->id) }}" class="d-inline" onsubmit="return confirm('{{ trans('opt.deletemsg') }}')">
                           @method('DELETE')
                           @csrf
                           <button type="submit" class="waves-effect waves-light btn btn-danger-light btn-circle">
                              <span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span>
                           </button>
                        </form>
                        </td>

                     </tr>  

                           <!-- update Form -->
                           <div class="modal center-modal fade" id="modal-center{{ $sc->id }}" tabindex="-1">
                              <div class="modal-dialog modal-lg">
                                 <div class="modal-content">

                                 <div class="modal-body">
                                    <form novalidate id="update-form{{ $sc->id }}" action="{{ route('Sections.update', $sc->id ) }}" method="POST" > 
                                       {{ method_field('patch') }}
                                       @csrf
                                           <div class="box-body">
                                              <div class="row">
                                                <div class="col-md-6">
                                                 <div class="form-croup">
                                                   <label class="form-label">{{ trans('opt.sectionnamefr') }}</label>
                                                   <input type="text" name="name_sectionfr" class="form-control" value="{{ $sc->getTranslation('name_section', 'fr') }}" placeholder="{{ trans('opt.sectionnamefr') }}">
                                                 </div>
                                                </div>
                                                <div class="col-md-6">
                                                 <div class="form-croup">
                                                   <label class="form-label">{{ trans('opt.sectionnamear') }}</label>
                                                   <input type="text" name="name_sectionar" class="form-control" value="{{ $sc->getTranslation('name_section', 'ar') }}" placeholder="{{ trans('opt.sectionnamear') }}">
                                                 </div>
                                                </div>
                                              </div>
                                              <br>
                                              <div class="row">
                                    
                                             <div class="col-md-3">
                                              <div class="form-croup">
                                                <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                                <select id="school_id" class="form-select" name="school_id" onchange="console.log($(this).val())">
                                                 <option value="{{ $sc->classroom->schoolgrade->school->id }}" selected >{{ $sc->classroom->schoolgrade->school->name_school }}</option>

                                                   @foreach ($School as $schol)
                                                   @if ($schol->id != $sc->classroom->schoolgrade->school->id)
                                                   <option value="{{ $schol->id }}">{{ $schol->name_school }}</option>
                                                   @endif
                                                   @endforeach


                                                </select>
                                              </div>
                                            </div>
                                    
                                            <div class="col-md-3">
                                                <div class="form-croup">
                                                  <label class="form-label">{{ trans('inscription.niveau') }}</label>
                                                  <select id="schoolgrade_id" class="form-select" name="grade_id">
                                                   <option value="{{ $sc->classroom->schoolgrade->id }}" selected >{{ $sc->classroom->schoolgrade->name_grade }}</option>
                                                </select>
                                                </div>
                                            </div>
                           
                           
                                            <div class="col-md-3">
                                             <div class="form-group">
                                                 <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                                 <select id="classroom_id" class="form-select" name="classroom_id" required>
                                                   <option value="{{ $sc->classroom->id }}" selected >{{ $sc->classroom->name_class}}</option>
                                                 </select>
                                               </div>
                                            </div>
                           
                           
                                             <div class="col-md-3">
                                                <div class="form-group">
                                                   <label class="form-label">{{ trans('inscription.status') }}</label>
                                                   
                                                <select id="statu"  onchange="console.log($(this).val())"  class="form-select" name="statu">
                                                   @if ($sc->Status == 1)                                                   
                                                   <option value="{{1}}" selected>{{ trans('inscription.sectionopen') }}</option>
                                                   <option value="{{0}}">{{ trans('inscription.sectionclose') }}</option>                                                       
                                                   @else
                                                   <option value="{{1}}">{{ trans('inscription.sectionopen') }}</option>
                                                   <option value="{{0}}" selected>{{ trans('inscription.sectionclose') }}</option>  
                                                   @endif
                                                </select>
                                                </div>
                                             </div>

                                          </div>


                                           </div>
                                    </form>
                                 </div>
                                 <div class="modal-footer modal-footer-uniform">
                                    <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                    <a type="submit" class="btn btn-primary float-end" onclick="event.preventDefault();
                                    document.getElementById('update-form{{ $sc->id }}').submit();">{{ trans('opt.update2') }}</a>

                                 </div>
                                 </div>
                              </div>
                           </div>
                               <!-- update  teacher-->
                           <div class="modal center-modal fade" id="modal-teacher{{ $sc->id }}" tabindex="-1">
                              <div class="modal-dialog">
                              <div class="modal-content">
                                 <div class="modal-body">
                                    <form id="teacher-form{{ $sc->id }}" action="{{ route('Sections.teachers',$sc->id) }}" method="POST" > 
                                       
                                    @csrf
                                       <div class="box-body text-center">
                                          <div class="row">
                                                <label class="form-label">{{ trans('teacher.teacher') }}</label>
                                                <select id="teacher_id" multiple name="teacher_id[]" class="form-select" name="teacher_id">
                              
                                                    @foreach($sc->teachers as $teacher)
                                                        <option selected value="{{$teacher->id}}">{{$teacher['name']}}</option>
                                                    @endforeach
                              
                                                    @foreach($Teacher as $teacher)
                                                        <option value="{{$teacher->id}}">{{$teacher->name}}</option>
                                                    @endforeach
                                                </select>
                                          </div>
                                       </div>
                                    </form>
                                 </div>
                                 <div class="modal-footer modal-footer-uniform">
                                 <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                 <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
                                 document.getElementById('teacher-form{{ $sc->id }}').submit();">{{ trans('opt.save') }}</a>
                                 </div>
                              </div>
                              </div>
                              </div>

                @endforeach

               </tbody>
               <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('inscription.ecole') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
                  <th>{{ trans('inscription.action') }}</th>

               </tr>
            </tfoot>
            </table>
            </div>
         </div>
      </div>
      <?php $j++; ?>
      @empty
      <div class="box">
         <div class="box-body text-center">
            <div class="admin-empty-state">لا توجد أقسام مطابقة للفلترة الحالية.</div>
         </div>
      </div>
      @endforelse

      <div class="d-flex justify-content-end mt-10">
         {{ $Schoolgrade->links() }}
      </div>


      


   </div>
</div>


<!-- Store Form -->
<div class="modal center-modal fade" id="modal-store" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form  id="store-form" action="{{ route('Sections.store') }}" method="POST"> 
               @csrf
                <div class="box-body">
                   <div class="row">
                     <div class="col-md-6">
                      <div class="form-croup">
                        <label class="form-label">{{ trans('opt.sectionnamefr') }}</label>
                        <input type="text" name="name_sectionfr" class="form-control" placeholder="{{ trans('opt.sectionnamefr') }}">
                      </div>
                     </div>
                     <div class="col-md-6">
                      <div class="form-croup">
                        <label class="form-label">{{ trans('opt.sectionnamear') }}</label>
                        <input type="text" name="name_sectionar" class="form-control" placeholder="{{ trans('opt.sectionnamear') }}">
                      </div>
                     </div>
                   </div>
                   <br>
                   <div class="row">
         
                  <div class="col-md-4">
                   <div class="form-croup">
                     <label class="form-label">{{ trans('inscription.ecole') }}</label>
                     <select id="school_id" class="form-select" name="school_id" onchange="console.log($(this).val())">
                      <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>

                     @foreach ($School as $sc)
                       <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                     @endforeach
                     </select>
                   </div>
                 </div>
         
                 <div class="col-md-4">
                     <div class="form-croup">
                       <label class="form-label">{{ trans('inscription.niveau') }}</label>
                       <select id="schoolgrade_id" class="form-select" name="grade_id">
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      </select>
                     </div>
                 </div>


                 <div class="col-md-4">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                      <select id="classroom_id" class="form-select" name="classroom_id" required>
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      </select>
                    </div>
                 </div>

                 <div class="col-md-12">
                  <label class="form-label">{{ trans('teacher.teacher') }}</label>
                  <select id="teacher_id" multiple name="teacher_id[]" class="form-select" >

                      {{-- @foreach($list_Sections->teachers as $teacher)
                          <option selected value="{{$teacher['id']}}">{{$teacher['Name']}}</option>
                      @endforeach --}}

                      @foreach($Teacher as $teacher)
                          <option value="{{$teacher->id}}">{{$teacher->name}}</option>
                      @endforeach
                  </select>
              </div>


               </div>
                </div>
         </form>
      </div>
      <div class="modal-footer modal-footer-uniform">
       <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
       <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
          document.getElementById('store-form').submit();">{{ trans('opt.add') }}</a>
      </div>
    </div>
   </div>
</div>
@endsection


@section('jsa')
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>

<script>
   $(document).ready(function () {
       $('select[name="school_id"]').on('change', function () {
           var school_id = $(this).val();
           if (school_id) {
               $.ajax({
                   url: "{{ route('lookup.schoolGrades', ['id' => '__ID__']) }}".replace('__ID__', school_id),
                   type: "GET",
                   dataType: "json",
                   success: function (data) {
                       $('select[name="grade_id"]').empty();
                       $('select[name="grade_id"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
                       $.each(data, function (key, value) {
                           $('select[name="grade_id"]').append('<option value="' + key + '">' + value + '</option>');
                       });
                   },
               });
           } else {
               console.log('AJAX load did not work');
           }
       });
   });
</script>
<script>
   $(document).ready(function () {
       $('select[name="grade_id"]').on('change', function () {
           var grade_id = $(this).val();
           if (grade_id) {
               $.ajax({
                   url: "{{ route('lookup.gradeClasses', ['id' => '__ID__']) }}".replace('__ID__', grade_id),
                   type: "GET",
                   dataType: "json",
                   success: function (data) {
                       $('select[name="classroom_id"]').empty();
                       $('select[name="classroom_id"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
                       $.each(data, function (key, value) {
                           $('select[name="classroom_id"]').append('<option value="' + key + '">' + value + '</option>');
                       });
                   },
               });
           } else {
               console.log('AJAX load did not work');
           }
       });
   });
 
</script>
{{-- <script>
   $(document).ready(function () {
       $('select[name="statu"]').on('change', function () {
           var section_id = $(this).val();
           if (section_id) {
               $.ajax({
                   url: "{{ URL::to('statu') }}/" + section_id,
                   type: "GET",
                   dataType: "json",
                   success: function (data) {
                       $('select[name="grade_id"]').empty();
                       $('select[name="grade_id"]').append('<option value="" selected disabled>{{ trans('Inscription.choisir') }}</option>');
                       $.each(data, function (key, value) {
                           $('select[name="grade_id"]').append('<option value="' + key + '">' + value + '</option>');
                       });
                   },
               });
           } else {
               console.log('AJAX load did not work');
           }
       });
   });
 
</script> --}}
@endsection
