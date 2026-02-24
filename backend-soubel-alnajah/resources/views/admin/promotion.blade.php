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
       <div class="box">
        <div class="box-header with-border">
         <h3 class="box-title"><a data-bs-target="#modal-deleteAll" data-bs-toggle="modal" class="btn btn-danger">{{ trans('student.Undoall') }}</a></h3>
        </div>
         <!-- /.box-header -->
         <div class="box-body">
            <div class="table-responsive">
              <table id="example5" class="table table-bordered text-center"  style="width:100%">
               <thead>
                  <tr>
                     <th></th>          
                     <th class="col-md-2 alert-info">{{ trans('inscription.student') }}</th>
                     {{-- class before --}}
                     <th class="col-md-2 alert-danger"> {{ trans('inscription.ecole') }}</th>
                     <th class="col-md-2 alert-danger">{{ trans('inscription.Anneescolaire') }}</th>
                     <th class="col-md-2 alert-danger">{{ trans('inscription.niveau') }}</th>
                     <th class="col-md-2 alert-danger">{{ trans('inscription.section') }}</th>
                    {{-- class after --}}
                     <th class="col-md-2 alert-success"> {{ trans('inscription.ecole') }}</th>
                     <th class="col-md-2 alert-success">{{ trans('inscription.Anneescolaire') }}</th>
                     <th class="col-md-2 alert-success">{{ trans('inscription.niveau') }}</th>
                     <th class="col-md-2 alert-success">{{ trans('inscription.section') }}</th>
                     <th>{{ trans('inscription.action') }}</th>
 
                  </tr>
               </thead>
               <tbody>
 
                <?php $i = 0; ?>
                @foreach ($promotions as $ins)
                <?php $i++; ?>
                  <tr>
                    
                     <td>{{ $i }}</td> 
                     <td class="col-md-2">
                        <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ $ins->student->prenom }} {{ $ins->student->nom }}</a><span class="text-fade d-block">{{ $ins->email }}</span>
                          <span class="text-fade d-block">{{ $ins->student->numtelephone }}</span>
                    </td> 
                
 
                     <td class="col-md-2">  {{ $ins->f_school->name_school }} </td>     

                     <td class="col-md-2">{{ $ins->f_classroom->name_class }}</td>
                     <td class="col-md-2">{{ $ins->f_grade->name_grade }}</td>
                     <td>{{ $ins->f_section->name_section }}</td>


                     <td class="col-md-2">  {{ $ins->t_school->name_school }} </td>     

                     <td class="col-md-2">{{ $ins->t_classroom->name_class }}</td>
                     <td class="col-md-2">{{ $ins->t_grade->name_grade }}</td>
                     <td>{{ $ins->t_section->name_section }}</td>

                     <td class="col-md-2">
                      <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="fa fa-refresh"><span class="path1"></span><span class="path2"></span></span></a>
                      <a data-bs-target="#modal-deleteStudent{{ $ins->student_id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-success-light btn-circle"><span class="fa fa-mortar-board"><span class="path1"></span><span class="path2"></span></span></a>
                     </td>
                  </tr>  

        <!-- Graduated Student -->
        <div class="modal center-modal fade" id="modal-deleteStudent{{ $ins->student_id }}" tabindex="-1">
          <div class="modal-dialog">
          <div class="modal-content">
            
            <div class="modal-body">
                <form id="delete-form{{ $ins->student_id }}" action="{{ route('graduated.destroy',$ins->student_id) }}" method="POST" > 
                  
                  {{ method_field('Delete') }}
                  @csrf
                  <div class="box-body text-center">
                      <div class="row">
                        <input type="hidden" name="student_id" value="{{$ins->student_id}}">
                        <input type="hidden" name="promotion_id" value="{{$ins->id}}">
                      <h1>{{ trans('opt.graduated') }}</h1>
                      </div>
                  </div>
                </form>
            </div>
            <div class="modal-footer modal-footer-uniform">
              <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
              <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
              document.getElementById('delete-form{{ $ins->student_id }}').submit();">{{ trans('opt.save') }}</a>
            </div>
          </div>
          </div>
        </div>

      <!-- Restore Student -->
      <div class="modal center-modal fade" id="modal-delete{{ $ins->id }}" tabindex="-1">
        <div class="modal-dialog">
        <div class="modal-content">
          
          <div class="modal-body">
              <form id="delete-form{{ $ins->id }}" action="{{ route('Promotions.destroy',$ins->id) }}" method="POST" > 
                
                {{ method_field('Delete') }}
                @csrf
                <div class="box-body text-center">
                    <div class="row">
                      <input type="hidden" name="id" value="{{$ins->id}}">
                    <h1>{{ trans('opt.restore') }}</h1>
                    </div>
                </div>
              </form>
          </div>
          <div class="modal-footer modal-footer-uniform">
            <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
            <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
            document.getElementById('delete-form{{ $ins->id }}').submit();">{{ trans('opt.save') }}</a>
          </div>
        </div>
        </div>
      </div>

       <!-- Restore All Student -->
      <div class="modal center-modal fade" id="modal-deleteAll" tabindex="-1">
        <div class="modal-dialog">
        <div class="modal-content">
          
          <div class="modal-body">
              <form id="deleteAll-form" action="{{ route('Promotions.destroy',$ins->id) }}" method="POST" > 
                
                {{ method_field('Delete') }}
                @csrf
                <div class="box-body">
                    <div class="row">
                      <input type="hidden" name="page_id" value="1">
                    <h1>{{ trans('opt.deletemsg') }}</h1>
                    </div>
                </div>
              </form>
          </div>
          <div class="modal-footer modal-footer-uniform">
            <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
            <a type="button" class="btn btn-primary float-end" onclick="event.preventDefault();
            document.getElementById('deleteAll-form').submit();">{{ trans('opt.delete2') }}</a>
          </div>
        </div>
        </div>
      </div>

    

             
  
@endforeach
 
               </tbody>
               <tfoot>
                <tr>
                   <th> </th>
                   <th>{{ trans('inscription.student') }}</th>
                    {{-- class before --}}
                   <th>{{ trans('inscription.ecole') }}</th>
                   <th>{{ trans('inscription.Anneescolaire') }}</th>
                   <th>{{ trans('inscription.niveau') }}</th>
                   <th>{{ trans('inscription.section') }}</th>
                    {{-- class after --}}
                   <th>{{ trans('inscription.ecole') }}</th>
                   <th>{{ trans('inscription.Anneescolaire') }}</th>
                   <th>{{ trans('inscription.niveau') }}</th>
                   <th>{{ trans('inscription.section') }}</th>

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

    
<script src="{{ asset('assets/vendor_components/datatable/datatables.min.js')}}"></script>

@include('layoutsadmin.datatabels')

@endsection