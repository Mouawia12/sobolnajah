@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('teacher.teacherlist') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
       <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><a data-bs-target="#modal-store" data-bs-toggle="modal" class="btn btn-info">{{ trans('teacher.addteacher') }}</a></h3>
        </div>
         <!-- /.box-header -->
         <div class="box-body">
            <form method="GET" class="row mb-3">
              <div class="col-md-4">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                  placeholder="بحث: الاسم / البريد">
              </div>
              <div class="col-md-3">
                <select name="specialization_id" class="form-select">
                  <option value="">كل التخصصات</option>
                  @foreach ($Specializations as $sp)
                    <option value="{{ $sp->id }}" @selected((string) request('specialization_id') === (string) $sp->id)>
                      {{ $sp->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <select name="gender" class="form-select">
                  <option value="">كل الجنس</option>
                  <option value="1" @selected(request('gender') === '1')>{{ trans('inscription.male') }}</option>
                  <option value="0" @selected(request('gender') === '0')>{{ trans('inscription.female') }}</option>
                </select>
              </div>
              <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary" type="submit">تصفية</button>
                <a href="{{ route('Teachers.index') }}" class="btn btn-outline-secondary">إعادة</a>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-bordered text-center"  style="width:100%">
               <thead>
                  <tr>
                     <th></th>          
                     <th>{{ trans('teacher.teacher') }}</th>
                     <th> {{ trans('teacher.specialization') }}</th>
                     <th class="col-md-2">{{ trans('inscription.gender') }}</th>
                     <th>{{ trans('teacher.joiningdate') }}</th>
                     <th class="col-md-2">{{ trans('teacher.address') }}</th>
                     <th class="col-md-2">{{ trans('inscription.action') }}</th>

 
                  </tr>
               </thead>
               <tbody>
 
                @foreach ($Teacher as $index => $ins)
                  <tr>
                    
                     <td>{{ $Teacher->firstItem() + $index }}</td> 
                     <td  class="col-md-2">
                        <a href="#" class="text-dark fw-600 hover-primary fs-16">{{ $ins->name }}</a>
                        <span class="text-fade d-block">{{ optional($ins->user)->email }}</span>
                    </td> 
    
                
 
                     <td>{{ optional($ins->specialization)->name }}</td>         
                     <td class="col-md-2">
                        @if ( $ins->gender == 1 )              
                         {{ trans('inscription.male') }}
                        @else
                         {{ trans('inscription.female') }}     
                        @endif                     
                    </td>
                     <td class="col-md-2">{{  $ins->joining_date }}</td>
                     <td class="col-md-2">{{  $ins->address }}</td>
                     <td class="col-md-2">
  
                      <a data-bs-target=".modal-update{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                      <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
 
                     </td>
                  </tr>  



                   <!-- Delete -->
 <div class="modal center-modal fade" id="modal-delete{{ $ins->id }}" tabindex="-1">
  <div class="modal-dialog">
  <div class="modal-content">
    
    <div class="modal-body">
        <form id="delete-form{{ $ins->id }}" action="{{ route('Teachers.destroy',$ins->id) }}" method="POST" > 
          
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
      document.getElementById('delete-form{{ $ins->id }}').submit();">{{ trans('opt.delete2') }}</a>
    </div>
  </div>
  </div>
</div>




<!-- Update Form -->
<div class="modal fade modal-update{{ $ins->id }}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg">
<div class="modal-content">
  
  <div class="modal-body">
      <form novalidate id="update-form{{ $ins->id }}" action="{{ route('Teachers.update', $ins->id ) }}" method="POST" > 
        
        {{ method_field('patch') }}
        @csrf
        
        <div class="box-body">
          <div class="row">
            <div class="col-md-6">
             <div class="form-group">
               <label class="form-label">{{ trans('teacher.namefr') }}</label>
               <input type="text" name="name_teacherfr" class="form-control" value="{{ $ins->getTranslation('name', 'fr') }}" placeholder="{{ trans('teacher.namefr') }}">
             </div>
            </div>
            <div class="col-md-6">
             <div class="form-group">
               <label class="form-label">{{ trans('teacher.namear') }}</label>
               <input type="text" name="name_teacherar" class="form-control" value="{{ $ins->getTranslation('name', 'ar') }}" placeholder="{{ trans('teacher.namear') }}">
             </div>
            </div>
          </div>
          <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label class="form-label">{{ trans('teacher.specialization') }}</label>
            <select class="form-select" name="specialization_id">
              <option selected value="{{ $ins->specialization_id }}">{{ $ins->specialization->name }}</option>
            @foreach ($Specializations as $sp)
              @if ($sp->id != $ins->specialization_id)
                 <option value="{{ $sp->id }}">{{ $sp->name }}</option>
              @endif
            @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
              <label class="form-label">{{ trans('inscription.gender') }}</label>
              <select class="form-select" name="gender"  required>
                @if ($ins->gender==1)
                <option selected value="{{1}}">{{ trans('inscription.male') }}</option>
                <option value="{{0}}">{{ trans('inscription.female') }}</option>
                @else
                <option value="{{1}}">{{ trans('inscription.male') }}</option>
                <option selected value="{{0}}">{{ trans('inscription.female') }}</option>
                @endif
               
             </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
              <label class="form-label">{{ trans('teacher.joiningdate') }}</label>
              <input type="date" name="joining_date" value="{{$ins->joining_date}}" class="form-control" required>
          </div>
        </div>
        
        <div class="col-md-12">
          <div class="form-group">
            <label class="form-label">{{ trans('teacher.address') }}</label>
            <input type="text" name="address" value="{{$ins->address}}" class="form-control" placeholder="{{ trans('teacher.address') }}" required>
          </div>
         </div>
         <div class="col-md-12">
          <div class="form-group">
            <label class="form-label">{{ trans('inscription.email') }}</label>
            <input type="email" name="email" value="{{$ins->user->email}}" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
          </div>
         </div>
      </div>
       </div> 
      

      </form>
  </div>
  <div class="modal-footer modal-footer-uniform">
    <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
    <a type="submit" class="btn btn-primary float-end" onclick="event.preventDefault();
    document.getElementById('update-form{{ $ins->id}}').submit();">{{ trans('opt.update2') }}</a>

  </div>
</div>
</div>
</div>
                  @endforeach
 
               </tbody>
               <tfoot>
                <tr>
                   <th> </th>
                   <th>{{ trans('teacher.teacher') }}</th>
                   <th>{{ trans('teacher.specialization') }}</th>
                   <th>{{ trans('inscription.gender') }}</th>
                   <th>{{ trans('teacher.joiningdate') }}</th>
                   <th>{{ trans('teacher.address') }}</th>
                   <th>{{ trans('inscription.action') }}</th>

                </tr>
             </tfoot>
            </table>
            </div>
            <div class="mt-3">
              {{ $Teacher->links() }}
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
          <form  id="store-form" action="{{ route('Teachers.store') }}" method="POST"> 
             @csrf
                 <div class="box-body">
                    <div class="row">
                      <div class="col-md-6">
                       <div class="form-group">
                         <label class="form-label">{{ trans('teacher.namefr') }}</label>
                         <input type="text" name="name_teacherfr" class="form-control" placeholder="{{ trans('teacher.namefr') }}">
                       </div>
                      </div>
                      <div class="col-md-6">
                       <div class="form-group">
                         <label class="form-label">{{ trans('teacher.namear') }}</label>
                         <input type="text" name="name_teacherar" class="form-control" placeholder="{{ trans('teacher.namear') }}">
                       </div>
                      </div>
                    </div>
                    <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">{{ trans('teacher.specialization') }}</label>
                      <select class="form-select" name="specialization_id">
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      @foreach ($Specializations as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                      @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ trans('inscription.gender') }}</label>
                        <select class="form-select" name="gender" required>
                          <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                          <option value="{{1}}">{{ trans('inscription.male') }}</option>
                          <option value="{{0}}">{{ trans('inscription.female') }}</option>
                       </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ trans('teacher.joiningdate') }}</label>
                        <input type="date" name="joining_date" class="form-control" required>
                    </div>
                  </div>
                  
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">{{ trans('teacher.address') }}</label>
                      <input type="text" name="address" class="form-control" placeholder="{{ trans('teacher.address') }}" required>
                    </div>
                   </div>
                   <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.email') }}</label>
                      <input type="email" name="email" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
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
@endsection
