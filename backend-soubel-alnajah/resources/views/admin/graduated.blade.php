@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
      {{ trans('student.studentlist') }}
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

        <div class="box box-slided-up">
                <div class="box-header with-border bg-info">
                    <h4 class="box-title"><strong>{{ trans('student.addGraduated') }}</strong></h4>
                        <ul class="box-controls pull-right">
                        <li><a class="box-btn-slide text-white" href="#"></a></li>
                        <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                        </ul>
                </div>
        
                <div class="box-body">
                    <form method="POST" id="promotion" action="{{ route('graduated.store') }}">
                    @csrf
                    <div class="box-body">
                        <h6 style="color: red;">{{ trans('student.addGraduated') }}</h6><br>
        
                        <div class="row">
        
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                <select id="school_id" class="form-select" name="school_id" onchange="console.log($(this).val())" required data-validation-required-message="This field is sssss">
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    @foreach ($School as $sc)
                                    <option value="{{ $sc->id}}">{{ $sc->name_school }}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.niveau') }}</label>
                                    <select id="grade_id" class="form-select" name="grade_id" onchange="console.log($(this).val())" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                    <select id="classroom_id" class="form-select" name="classroom_id" onchange="console.log($(this).val())" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    </select>
                            </div>
                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.section') }}</label>
                                    <select id="section_id" class="form-select" name="section_id" required>
                                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    </select>
                            </div>
        
                        </div>
                        <a type="button" class="btn btn-primary" onclick="event.preventDefault();
                                        document.getElementById('promotion').submit();">{{ trans('opt.save') }}</a>
                    </div>
            
                </form>   
                    </div>
        </div>
        
         <!-- /.box-header -->
         <div class="box-body">
            <form method="GET" action="{{ route('graduated.index') }}" class="admin-form-panel mb-15">
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label">بحث</label>
                  <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم التلميذ أو البريد أو الهاتف">
                </div>
                <div class="col-md-4">
                  <label class="form-label">{{ trans('inscription.section') }}</label>
                  <select name="section_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($Sections as $section)
                      <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>
                        {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section ?? '' }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                  <button class="btn btn-primary" type="submit">بحث</button>
                  <a href="{{ route('graduated.index') }}" class="btn btn-light">Reset</a>
                </div>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-bordered text-center"  style="width:100%">
               <thead>
                  <tr>
                     <th></th>          
                     <th>{{ trans('inscription.student') }}</th>
                     <th> {{ trans('inscription.ecole') }}</th>
                     <th class="col-md-2">{{ trans('inscription.Anneescolaire') }}</th>
                     <th class="col-md-2">{{ trans('inscription.niveau') }}</th>
                     <th class="col-md-2">{{ trans('inscription.section') }}</th>
                     <th class="col-md-2">{{ trans('inscription.action') }}</th>
 
                  </tr>
               </thead>
               <tbody>
 
                @php($i = ($StudentInfo->currentPage() - 1) * $StudentInfo->perPage())
                @forelse ($StudentInfo as $ins)
                @php($i++)
                  <tr>
                    
                     <td>{{ $i }}</td> 
                     <td class="col-md-2">
                        <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ $ins->prenom }} {{ $ins->nom }}</a><span class="text-fade d-block">{{ $ins->email }}</span>
                          <span class="text-fade d-block">{{ $ins->numtelephone }}</span>
                    </td> 
                
 
                     <td class="col-md-2">  {{ $ins->section->classroom->schoolgrade->school->name_school }} </td>     

                     <td class="col-md-2">{{ $ins->section->classroom->name_class }}</td>
                     <td class="col-md-2">{{ $ins->section->classroom->schoolgrade->name_grade }}</td>
                     <td> {{ $ins->section->name_section}} </td>

                     <td class="col-md-2">
                      <a data-bs-target="#modal-restore{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="fa fa-refresh"><span class="path1"></span><span class="path2"></span></span></a>
                      <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a> 
                     </td>
                  </tr>   


            
      <!-- Delete -->
      <div class="modal center-modal fade" id="modal-delete{{ $ins->id }}" tabindex="-1">
        <div class="modal-dialog">
        <div class="modal-content">
          
          <div class="modal-body">
              <form id="delete-form{{ $ins->id }}" action="{{ route('graduated.destroy',$ins->id) }}" method="POST" > 
                
                {{ method_field('Delete') }}
                @csrf
                <div class="box-body text-center">
                    <div class="row">
                    <input type="hidden" name="delete_id" value="{{1}}">
                    <input type="hidden" name="student_id" value="{{$ins->id}}">
                    <h1>{{ trans('opt.deletemsg') }}</h1>
                    </div>
                </div>
              </form>
          </div>
          <div class="modal-footer modal-footer-uniform">
            <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
            <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
            document.getElementById('delete-form{{ $ins->id }}').submit();">{{ trans('opt.delete2') }}</a>
          </div>
        </div>
        </div>
      </div> 


      <!-- Restore -->
      <div class="modal center-modal fade" id="modal-restore{{ $ins->id }}" tabindex="-1">
        <div class="modal-dialog">
        <div class="modal-content">
          
          <div class="modal-body">
              <form id="Restore-form{{ $ins->id }}" action="{{ route('graduated.update',$ins->id) }}" method="POST" > 
                
                {{ method_field('patch') }}
                @csrf
                <div class="box-body text-center">
                    <div class="row">
                    <h1>{{ trans('opt.restorestudent') }}</h1>
                    </div>
                </div>
              </form>
          </div>
          <div class="modal-footer modal-footer-uniform">
            <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
            <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
            document.getElementById('Restore-form{{ $ins->id }}').submit();">{{ trans('opt.save') }}</a>
          </div>
        </div>
        </div>
      </div>

    


             
  
                @empty
                <tr>
                  <td colspan="7"><div class="admin-empty-state">لا توجد سجلات تلاميذ محذوفين مطابقة للفلترة.</div></td>
                </tr>
                @endforelse
 
               </tbody>
               <tfoot>
                <tr>
                   <th> </th>
                   <th>{{ trans('inscription.student') }}</th>
                   <th>{{ trans('inscription.ecole') }}</th>
                   <th>{{ trans('inscription.Anneescolaire') }}</th>
                   <th>{{ trans('inscription.niveau') }}</th>
                   <th>{{ trans('inscription.section') }}</th>
                   <th>{{ trans('inscription.action') }}</th>
                </tr>
             </tfoot>
            </table>
            <div class="mt-15 d-flex justify-content-end">
              {{ $StudentInfo->links() }}
            </div>
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
          var section_id = $(this).val();
          var result_explode = section_id.split('');
          var student_id = $result_explode[0];
          var section_id = $result_explode[1];

          if (section_id) {
              $.ajax({
                  url: "{{ URL::to('getsection') }}",
                  type: "POST",
                  data: {"1":$student_id,"2":$section_id},
                  dataType: "json",
                  success: function (data) {
                      $('select[name="section_id"]').empty();
                      $('select[name="section_id"]').append('<option value="" selected disabled>{{ trans('Inscription.choisir') }}</option>');
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
@endsection
