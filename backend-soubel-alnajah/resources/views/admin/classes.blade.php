@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('main_sidebar.classerooms') }}
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
        <div class="box-header with-border">
         <h3 class="box-title"><a data-bs-target="#modal-store" data-bs-toggle="modal" class="btn btn-success">{{ trans('main_sidebar.addclasseroom') }}</a></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
           <form method="GET" action="{{ route('Classes.index') }}" class="admin-form-panel mb-15">
             <div class="row">
               <div class="col-md-3">
                 <label class="form-label">بحث</label>
                 <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم القسم الدراسي">
               </div>
               @if(!$currentSchoolId)
               <div class="col-md-3">
                 <label class="form-label">{{ trans('inscription.ecole') }}</label>
                 <select name="school_id" class="form-select">
                   <option value="">الكل</option>
                   @foreach ($School as $sc)
                     <option value="{{ $sc->id }}" @selected((string) request('school_id') === (string) $sc->id)>{{ $sc->name_school }}</option>
                   @endforeach
                 </select>
               </div>
               @endif
               <div class="col-md-3">
                 <label class="form-label">{{ trans('inscription.niveau') }}</label>
                 <select name="grade_id" class="form-select">
                   <option value="">الكل</option>
                   @foreach ($Schoolgradee as $grade)
                     <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_grade }}</option>
                   @endforeach
                 </select>
               </div>
               <div class="col-md-3 d-flex align-items-end gap-2">
                 <button type="submit" class="btn btn-primary">بحث</button>
                 <a href="{{ route('Classes.index') }}" class="btn btn-light">Reset</a>
               </div>
             </div>
           </form>
           <div class="table-responsive">
             <table class="table table-bordered table-striped text-center" style="width:100%">
              <thead>
                 <tr>
                    <th>#</th>              
                    <th>{{ trans('inscription.ecole') }}</th>                   
                    <th>{{ trans('inscription.Anneescolaire') }} </th>
                    <th>{{ trans('inscription.niveau') }}</th>
                    <th>{{ trans('inscription.action') }}</th>

                 </tr>
              </thead>
              <tbody>

               @php($i = ($Classroom->currentPage() - 1) * $Classroom->perPage())
               @forelse ($Classroom as $cr)
               @php($i++)
                 <tr>
                    <td>{{ $i }}</td>                    
                    <td>{{ $cr->schoolgrade->school->name_school }}</td>
                    <td>{{ $cr->name_class }}</td>
                    <td>{{ $cr->schoolgrade->name_grade }}</td>
                    <td >

                     {{-- <a href="{{ route('Addnotestudent.index', $cr->id ) }}" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a> --}}
                     <a data-bs-target="#modal-update{{ $cr->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                     <a data-bs-target="#modal-delete{{ $cr->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                     </td>
                 </tr>  

<!-- Update Form -->
<div class="modal center-modal fade" id="modal-update{{ $cr->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form novalidate id="update-form{{ $cr->id }}" action="{{ route('Classes.update', $cr->id ) }}" method="POST" > 
            
            {{ method_field('patch') }}
            @csrf
         
            <div class="box-body">
               <div class="row">
                 <div class="col-md-6">
                  <div class="form-croup">
                    <label class="form-label">{{ trans('opt.sallenamefr') }}</label>
                    <input type="text" name="name_classfr" value="{{ $cr->getTranslation('name_class', 'fr') }}" class="form-control" >
                  </div>
                 </div>
                 <div class="col-md-6">
                  <div class="form-croup">
                    <label class="form-label">{{ trans('opt.sallenamear') }}</label>
                    <input type="text" name="name_classar" value="{{ $cr->getTranslation('name_class', 'ar') }}" class="form-control" >
                  </div>
                 </div>
               </div>
               <div class="row">
     
                 <div class="col-md-6">
               <div class="form-croup">
                 <label class="form-label">{{ trans('inscription.ecole') }}</label>
                 <select class="form-select" name="school_id">
                    <option value="{{ $cr->schoolgrade->school->id }}" selected >{{ $cr->schoolgrade->school->name_school }}</option>

                 @foreach ($School as $sc)
                 @if ($sc->id != $cr->schoolgrade->school->id)
                 <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                 @endif
                 @endforeach
                 </select>
               </div>
             </div>
     
             <div class="col-md-6">
                 <div class="form-croup">
                   <label class="form-label">{{ trans('inscription.niveau') }}</label>
                   <select id="schoolgrade_id" class="form-select" name="grade_id">
                    <option value="{{ $cr->schoolgrade->id }}" selected >{{ $cr->schoolgrade->name_grade }}</option>
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
       document.getElementById('update-form{{ $cr->id}}').submit();">{{ trans('opt.update2') }}</a>

      </div>
    </div>
   </div>
   </div>


 <!-- Delete -->
<div class="modal center-modal fade" id="modal-delete{{ $cr->id }}" tabindex="-1">
  <div class="modal-dialog">
   <div class="modal-content">
     <div class="modal-body">
        <form id="delete-form{{ $cr->id }}" action="{{ route('Classes.destroy',$cr->id) }}" method="POST" > 
           
           {{ method_field('Delete') }}
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
      document.getElementById('delete-form{{ $cr->id }}').submit();">{{ trans('opt.delete2') }}</a>
     </div>
   </div>
  </div>
  </div>
               @empty
                 <tr>
                   <td colspan="5"><div class="admin-empty-state">لا توجد أقسام دراسية مطابقة.</div></td>
                 </tr>
               @endforelse

              </tbody>
              <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('inscription.ecole') }}</th>                   
                  <th>{{ trans('inscription.Anneescolaire') }} </th>
                  <th>{{ trans('inscription.niveau') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
               </tr>
            </tfoot>
           </table>
           <div class="mt-15 d-flex justify-content-end">
            {{ $Classroom->links() }}
           </div>
           </div>
        </div>
        <!-- /.box-body -->
       </div>
       <!-- /.box -->      
     </div>    

</div>




<!-- Store Form -->
<div class="modal center-modal fade" id="modal-store" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
         <h4 class="box-title text-info mb-0"><i class="ti-user me-15"></i>   {{ trans('main_sidebar.addecoles') }} </h4>
         <a type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></a>
      </div>
      <div class="modal-body">
         <form  id="store-form" action="{{ route('Classes.store') }}" method="POST"> 
               @csrf
                <div class="box-body">
                   <div class="row">
                     <div class="col-md-6">
                      <div class="form-croup">
                        <label class="form-label">{{ trans('opt.sallenamefr') }}</label>
                        <input type="text" name="name_classfr" class="form-control" placeholder="{{ trans('opt.sallenamefr') }}">
                      </div>
                     </div>
                     <div class="col-md-6">
                      <div class="form-croup">
                        <label class="form-label">{{ trans('opt.sallenamear') }}</label>
                        <input type="text" name="name_classar" class="form-control" placeholder="{{ trans('opt.sallenamear') }}">
                      </div>
                     </div>
                   </div>
                   <div class="row">
         
                     <div class="col-md-6">
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
         
                 <div class="col-md-6">
                     <div class="form-croup">
                       <label class="form-label">{{ trans('inscription.niveau') }}</label>
                       <select id="schoolgrade_id" class="form-select" name="grade_id">
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      </select>
                     </div>
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

@endsection
