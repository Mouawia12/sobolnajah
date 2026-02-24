@extends('layoutsadmin.masteradmin')

@section('cssa')
@endsection

@section('titlea')
    {{ trans('exam.exam') }}
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

        <div class="box box-slided-up">
                <div class="box-header with-border bg-info">
                    <h4 class="box-title"><strong>{{ trans('exam.addExam') }}</strong></h4>
                        <ul class="box-controls pull-right">
                        <li><a class="box-btn-slide text-white" href="#"></a></li>
                        <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                        </ul>
                </div>
        
                <div class="box-body">
                    <form method="POST" id="exam" action="{{ route('Exames.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        <h6 style="color: red;">{{ trans('exam.addExam') }}</h6><br>
        
                        <div class="row">
        
                                <div class="form-group col">
                                  <label class="form-label">{{ trans('exam.namefilear') }}</label>
                                  <input type="text" name="name_ar"  class="form-control" >
                                </div>
                                
                                

                                <div class="form-group col">
                                    <label class="form-label">{{ trans('exam.module') }}</label>
                                        <select id="specialization_id" class="form-select" name="specialization_id" required>
                                            <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                            @foreach ($Specializations as $sp)
                                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                            @endforeach
                                        </select>
                                </div>
                        </div>
                            <div class="row">
                            <div class="form-group col">
                                <label class="form-label">{{ trans('exam.perscolaire') }}</label>
                                <select id="grade_id" class="form-select" name="grade_id" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    @foreach ($Schoolgrade as $sc)
                                    <option value="{{$sc->id}}">{{ $sc->name_grade }}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                        
                            <div class="form-group col">
                                <label class="form-label">{{ trans('exam.phase') }}</label>
                                    <select id="classroom_id" class="form-select" name="classroom_id" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    </select>
                            </div>

                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                    <select id="Annscolaire" class="form-select" name="Annscolaire" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                        {{ $last= date('Y')-120 }}
                                        {{ $now = date('Y') }}

                                        @for ($i = $now; $i >= $last; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor

                                    </select>
                            </div>
                         
                        </div>

                        <div class="row">

                        <div class="form-group col-4">
                            <label for="formFile" class="form-label">{{ trans('exam.selectfile') }}</label>
				        <input class="form-control" name="file_url" type="file" id="formFile">
                          </div>

                        </div>

                        <a type="submit" class="btn btn-primary" onclick="event.preventDefault();
                        document.getElementById('exam').submit();" >
                         <i class="ti-save-alt"></i> {{ trans('opt.add') }}
                        </a>
                        {{-- <button type="button" class="btn btn-primary">{{ trans('opt.save') }}</button> --}}
                    </div>
            
                </form>   
                </div>
        </div>
        
         <!-- /.box-header -->
         <div class="box-body">
            <div class="table-responsive">
                <table id="example5" class="table table-bordered text-center"  style="width:100%">
                 <thead>
                    <tr>
                       <th></th>          
                       <th class="col-md-2 alert-info">{{ trans('exam.name') }}</th>
                       {{-- class before --}}
                       <th class="col-md-2 alert-success"> {{ trans('exam.module') }}</th>
                       <th class="col-md-2 alert-danger">{{ trans('exam.perscolaire') }}</th>
                       <th class="alert-danger">{{ trans('exam.phase') }}</th>
        
                      {{-- class after --}}
                       <th class="col-md-2 alert-danger"> {{ trans('exam.Annscolaire') }}</th>
                       <th>{{ trans('inscription.action') }}</th>
        
                    </tr>
                 </thead>
                 <tbody>
                    
                  <?php $j = 0; ?>
                  @foreach ($Exames as $ex)
                  <?php $j++; ?>
                    <tr>
                      
                       <td>{{ $j }}</td> 
                       <td class="col-md-3">
                          <a href="#" class="text-dark fw-600 hover-primary fs-16">{{$ex->name}}</a>
                      </td> 
                  
        
                      <td class="col-md-2">{{$ex->specialization->name}}</td>     
                       <td class="col-md-2">{{$ex->classroom->name_class}}</td>
                       <td >{{$ex->schoolgrade->name_grade}}</td>
                       <td>{{$ex->Annscolaire}}</td>
   
                       <td class="col-md-3">
                        <a data-bs-target=".modal-update{{ $ex->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                        <a href="{{ route('Exames.show',$ex->id) }}" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="fa fa-download"><span class="path1"></span><span class="path2"></span></span></a> 
                        <a data-bs-target="#modal-delete{{ $ex->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>  
                    </td>
                    </tr>  




                     <!-- Delete -->
                <div class="modal center-modal fade" id="modal-delete{{ $ex->id }}" tabindex="-1">
                    <div class="modal-dialog">
                    <div class="modal-content">
                    
                    <div class="modal-body">
                        <form id="delete-form{{ $ex->id }}" action="{{ route('Exames.destroy',$ex->id) }}" method="POST" > 
                            
                            {{ method_field('DELETE') }}
                            @csrf
                            <div class="box-body">
                                <div class="row">
                                
                                <h1>{{ trans('opt.deletemsg') }}</h1>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer modal-footer-uniform">
                        <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                        <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
                        document.getElementById('delete-form{{ $ex->id }}').submit();">{{ trans('opt.delete2') }}</a>
                    </div>
                    </div>
                    </div>
                    </div>







                <!-- Update Form -->
                <div class="modal fade modal-update{{ $ex->id }}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-lg">
                <div class="modal-content">
                
                <div class="modal-body">
                    <form novalidate id="update-form{{ $ex->id }}" action="{{ route('Exames.update', $ex->id ) }}" method="POST" enctype="multipart/form-data"> 
                        
                        {{ method_field('PATCH') }}
                        @csrf
                        <div class="box-body">
                            <h6 style="color: red;">{{ trans('exam.updateExam') ?? 'تحديث امتحان' }}</h6><br>

                            <div class="row">

                                    <div class="form-group col">
                                    <label class="form-label">{{ trans('exam.namefilear') }}</label>
                                    <input type="text" name="name_ar" value="{{ $ex->getTranslation('name','ar') }}"  class="form-control" >
                                    </div>
                                    
                                    <div class="form-group col">
                                        <label class="form-label">{{ trans('exam.module') }}</label>
                                            <select id="specialization_id_{{ $ex->id }}" class="form-select" name="specialization_id" required>
                                                <option value="" disabled>{{ trans('inscription.choisir') }}</option>
                                                @foreach ($Specializations as $sp)
                                                    <option value="{{ $sp->id }}" @if($sp->id == $ex->specialization_id) selected @endif>{{ $sp->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                            </div>
                                <div class="row">
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('exam.perscolaire') }}</label>
                                    <select id="grade_id_{{ $ex->id }}" class="form-select" name="grade_id" required>
                                        <option value="{{$ex->grade_id}}" selected>{{ $ex->schoolgrade->name_grade }}</option>
                                        @foreach ($Schoolgrade as $sc)
                                        <option value="{{$sc->id}}">{{ $sc->name_grade }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            
                            
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('exam.phase') }}</label>
                                        <select id="classroom_id_{{ $ex->id }}" class="form-select" name="classroom_id" required>
                                            <option value="{{$ex->classroom_id}}" selected>{{ $ex->classroom->name_class }}</option>
                                        </select>
                                </div>

                                <div class="form-group col">
                                    <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                        <select id="Annscolaire_{{ $ex->id }}" class="form-select" name="Annscolaire" required>
                                            <option value="{{ $ex->Annscolaire }}" selected >{{ $ex->Annscolaire }}</option>
                                            {{ $last= date('Y')-120 }}
                                            {{ $now = date('Y') }}

                                            @for ($i = $now; $i >= $last; $i--)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor

                                        </select>
                                </div>
                            
                            </div>

                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="file_url_{{ $ex->id }}">{{ trans('exam.selectfile') }}</label>
                                    <input type="file" name="file_url" id="file_url_{{ $ex->id }}" class="form-control">
                                    @if(!empty($ex->file_url))
                                        <a href="{{ route('Exames.show',$ex->id) }}" target="_blank" class="btn btn-sm btn-info mt-2">{{ trans('exam.currentfile') ?? 'الملف الحالي' }}</a>
                                    @endif
                                </div>
                            </div>

                            <!-- لا تستدعي نموذج الإضافة هنا -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-uniform">
                <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
                document.getElementById('update-form{{ $ex->id}}').submit();">{{ trans('opt.update2') }}</a>

                </div>
                </div>
                </div>
                </div>
                    @endforeach
                 </tbody>
                 <tfoot>
                  <tr>
                     <th> </th>
                     <th class="col-md-2">{{ trans('exam.name') }}</th>
                       {{-- class before --}}
                       <th class="col-md-2"> {{ trans('exam.module') }}</th>
                       <th class="col-md-2">{{ trans('exam.perscolaire') }}</th>
                       <th >{{ trans('exam.phase') }}</th>
        
                      {{-- class after --}}
                       <th > {{ trans('exam.Annscolaire') }}</th>
                       <th>{{ trans('inscription.action') }}</th>
                  </tr>
               </tfoot>
              </table>
              </div>
        
         </div>
         <!-- /.box-body -->
        </div>
        <!-- /.box -->      
      </div>    
 
 </div>
@endsection


@section('jsa')
<script>
  $(document).ready(function () {
      $('select[name="section_id"]').on('change', function () {
          var section_value = $(this).val();

          if (section_value) {
              // نفترض أن القيمة فيها رقمين أو مقطعين (مثلاً "12")
              var result_explode = section_value.split('');
              var student_id = result_explode[0];
              var section_id = result_explode[1];

              $.ajax({
                  url: "{{ URL::to('getsection') }}",
                  type: "POST",
                  data: {
                      student_id: student_id,
                      section_id: section_id,
                      _token: '{{ csrf_token() }}' // ضروري إذا كان عندك حماية CSRF
                  },
                  dataType: "json",
                  success: function (data) {
                      // نفرغ قائمة grade_id
                      $('select[name="grade_id"]').empty();
                      $('select[name="grade_id"]').append('<option value="" selected disabled>{{ trans("Inscription.choisir") }}</option>');
                      
                      $.each(data, function (key, value) {
                          $('select[name="grade_id"]').append('<option value="' + key + '">' + value + '</option>');
                      });
                  },
                  error: function (xhr) {
                      console.log('AJAX error:', xhr.responseText);
                  }
              });
          } else {
              console.log('AJAX load did not work');
          }
      });
  });
</script>

    
<script src="{{ asset('assets/vendor_components/datatable/datatables.min.js')}}"></script>

@include('layoutsadmin.datatabels')

@endsection
