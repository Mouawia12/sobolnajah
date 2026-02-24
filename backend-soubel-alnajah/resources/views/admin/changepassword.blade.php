@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   empty
@stop
@endsection

@section('contenta')
<div class="row">
   <div class="col-12">
       <form class="form" action="{{ route('changePassword')}}" method="POST">
           @csrf
           <div>
               <h4 class="box-title text-info"><i class="ti-user me-15"></i> {{ trans('student.changePassword') }}</h4>
               <hr class="mb-15">
               {{-- <div class="form-group">
                   <label class="form-label">User Name</label>
                   <div class="input-group mb-3">
                       <div class="input-group-prepend">
                           <span class="input-group-text"><i class="ti-user"></i></span>
                       </div>
                       <input type="text" class="form-control" placeholder="Username">
                   </div>
               </div> --}}
            
               <div class="form-group">
                   <label class="form-label">{{ trans('student.password') }}</label>
                   <div class="input-group mb-3">
                       <div class="input-group-prepend">
                           <span class="input-group-text"><i class="ti-lock"></i></span>
                       </div>
                       <input type="password" name="password" class="form-control" placeholder="{{ trans('student.password') }}">
                   </div>
               </div>
               <div class="form-group">
                   <label class="form-label">{{ trans('student.newpassword') }}</label>
                   <div class="input-group mb-3">
                       <div class="input-group-prepend">
                           <span class="input-group-text"><i class="ti-lock"></i></span>
                       </div>
                       <input type="Password" name="newPassword" class="form-control" placeholder="{{ trans('student.newpassword') }}">
                   </div>
               </div>
               <div class="form-group">
                   <label class="form-label">{{ trans('student.confirmnewpassword') }}</label>
                   <div class="input-group mb-3">
                       <div class="input-group-prepend">
                           <span class="input-group-text"><i class="ti-lock"></i></span>
                       </div>
                       <input type="password" name="confirmNewPassword" class="form-control" placeholder="{{ trans('student.confirmnewpassword') }}">
                   </div>
               </div>
           </div>
           <div class="d-flex justify-content-end gap-items-2">
               <button type="submit" class="btn btn-success">
                 <i class="ti-save-alt"></i>{{ trans('opt.save') }}
               </button>
           </div>  
       </form>
   </div>
</div>
@endsection


@section('jsa')
    
@endsection