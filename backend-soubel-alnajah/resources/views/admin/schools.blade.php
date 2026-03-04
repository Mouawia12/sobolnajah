@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('main_sidebar.Listecoles') }}
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
          <h3 class="box-title"><a class="popup-with-form btn btn-success" href="#addschool-form">{{ trans('main_sidebar.addecoles') }}</a>
          </h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
           <form method="GET" action="{{ route('Schools.index') }}" class="admin-form-panel mb-15">
            <div class="row">
               <div class="col-md-4">
                  <label class="form-label">بحث</label>
                  <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم المدرسة">
               </div>
               <div class="col-md-4 d-flex align-items-end gap-2">
                  <button type="submit" class="btn btn-primary">بحث</button>
                  <a href="{{ route('Schools.index') }}" class="btn btn-light">Reset</a>
               </div>
            </div>
           </form>
           <div class="table-responsive">
             <table class="table table-bordered text-center" style="width:100%">
              <thead>
                 <tr>
                    <th>#</th>
                    <th>{{ trans('inscription.ecole') }}</th>
                    <th>{{ trans('inscription.action') }}</th>

                 </tr>
              </thead>
              <tbody>

               @php($i = ($School->currentPage() - 1) * $School->perPage())
               @forelse ($School as $sc)
               @php($i++)
                 <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $sc->name_school }}</td>
                    <td >
                     {{-- <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a> --}}
                     <a data-bs-target="#modal-center{{ $sc->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                     <a data-bs-target="#modal-centerdelete{{ $sc->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                     </td>

                 </tr>  

<!-- update Form -->
<div class="modal center-modal fade" id="modal-center{{ $sc->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
  
      <div class="modal-body">
         <form novalidate id="update-form{{ $sc->id }}" action="{{ route('Schools.update', $sc->id ) }}" method="POST" > 
            
            {{ method_field('patch') }}
            @csrf
            <div class="box-body">
               <div class="row">
                 <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">{{ trans('opt.schoolnamefr') }}</label>
                    <input type="text" name="name_schoolfr" value="{{ $sc->getTranslation('name_school', 'fr') }}" class="form-control" placeholder="france Name" required data-validation-required-message="{{ trans('validation.required') }}">
                  </div>
                 </div>
                 <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">{{ trans('opt.schoolnamear') }}</label>
                    <input type="text" name="name_schoolar" value="{{ $sc->getTranslation('name_school', 'ar') }}"  class="form-control" placeholder="arabic Name" required data-validation-required-message="{{ trans('validation.required') }}">
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


 <!-- Delete -->
 <div class="modal center-modal fade" id="modal-centerdelete{{ $sc->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-body">
         <form id="delete-form{{ $sc->id }}" action="{{ route('Schools.destroy',$sc->id) }}" method="POST" > 
            
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
       document.getElementById('delete-form{{ $sc->id }}').submit();">{{ trans('opt.delete2') }}</a>
      </div>
    </div>
   </div>
   </div>
               @empty
                <tr>
                  <td colspan="3"><div class="admin-empty-state">لا توجد مدارس مطابقة.</div></td>
                </tr>
               @endforelse

              </tbody>
              <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('inscription.ecole') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
               </tr>
            </tfoot>
           </table>
           <div class="mt-15 d-flex justify-content-end">
            {{ $School->links() }}
           </div>
           </div>
        </div>
        <!-- /.box-body -->
       </div>
       <!-- /.box -->      
     </div>    

</div>





     
<!-- Store Form -->
<form  id="addschool-form" action="{{ route('Schools.store') }}" method="POST" class="mfp-hide white-popup-block"> 
   @csrf
   <div class="box-body">
 
      <div class="row">
        <div class="col-md-6">
         <div class="form-group">
           <label class="form-label">{{ trans('opt.schoolnamefr') }}</label>
           <input type="text" name="name_schoolfr" class="form-control" placeholder="{{ trans('opt.schoolnamefr') }}" required data-validation-required-message="{{ trans('validation.required') }}">
         </div>
        </div>
        <div class="col-md-6">
         <div class="form-group">
           <label class="form-label">{{ trans('opt.schoolnamear') }}</label>
           <input type="text" name="name_schoolar" class="form-control" placeholder="{{ trans('opt.schoolnamear') }}" required data-validation-required-message="{{ trans('validation.required') }}">
         </div>
        </div>
      </div>
   </div>
   <!-- /.box-body -->
   <div class="box-footer">
      <a type="submit" class="btn btn-primary" onclick="event.preventDefault();
      document.getElementById('addschool-form').submit();" >
        <i class="ti-save-alt"></i> {{ trans('opt.add') }}
      </a>
   </div>
</form>
@endsection


@section('jsa')
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>

@endsection

