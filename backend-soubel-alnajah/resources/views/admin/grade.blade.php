@extends('layoutsadmin.masteradmin')

@section('titlea')
   {{ trans('main_sidebar.classe') }}
@stop

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
          <h3 class="box-title">
            <a class="popup-with-form btn btn-success" href="#addGrade-form">
               {{ trans('main_sidebar.addclasse') }}
            </a>
          </h3>
        </div>
        
        <div class="box-body">
           <form method="GET" action="{{ route('Grades.index') }}" class="admin-form-panel mb-15">
             <div class="row">
               <div class="col-md-4">
                 <label class="form-label">بحث</label>
                 <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم المستوى">
               </div>
               <div class="col-md-4 d-flex align-items-end gap-2">
                 <button class="btn btn-primary" type="submit">بحث</button>
                 <a href="{{ route('Grades.index') }}" class="btn btn-light">Reset</a>
               </div>
             </div>
           </form>
           <div class="table-responsive">
             <table class="table table-bordered text-center" style="width:100%">
              <thead>
                 <tr>
                    <th>#</th>
                    <th>{{ trans('inscription.niveau') }}</th>
                    <th>{{ trans('inscription.action') }}</th>
                 </tr>
              </thead>
              <tbody>
               @php($i = ($Grade->currentPage() - 1) * $Grade->perPage())
               @forelse ($Grade as $g)
               @php($i++)
                 <tr>
                    <td>{{ $i }}</td>
                    <td>
                        {{ $g->getTranslation('name_grades', app()->getLocale()) }}
                    </td>
                    <td>
                     <!-- Edit -->
                     <a data-bs-target="#modal-center{{ $g->id }}" data-bs-toggle="modal" 
                        class="waves-effect waves-light btn btn-primary-light btn-circle mx-2">
                        <span class="icon-Write"><span class="path1"></span><span class="path2"></span></span>
                     </a>
                     <!-- Delete -->
                     <a data-bs-target="#modal-centerdelete{{ $g->id }}" data-bs-toggle="modal" 
                        class="waves-effect waves-light btn btn-danger-light btn-circle">
                        <span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span>
                     </a>
                    </td>
                 </tr>  

<!-- Update Modal -->
<div class="modal center-modal fade" id="modal-center{{ $g->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form id="update-form{{ $g->id }}" action="{{ route('Grades.update', $g->id ) }}" method="POST"> 
            {{ method_field('patch') }}
            @csrf
            <div class="box-body">
               <div class="form-group">
                 <label class="form-label">{{ trans('opt.classnamear') }}</label>
                 <input type="text" name="name_gradesar" 
                        value="{{ $g->getTranslation('name_grades', 'ar') }}" 
                        class="form-control" 
                        placeholder="{{ trans('opt.classnamear') }}" required >
               </div>
            </div>
         </form>
      </div>
      <div class="modal-footer modal-footer-uniform">
       <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
       <a type="submit" class="btn btn-primary float-end" 
          onclick="event.preventDefault(); document.getElementById('update-form{{ $g->id }}').submit();">
          {{ trans('opt.update2') }}
       </a>
      </div>
    </div>
   </div>
</div>

<!-- Delete Modal -->
<div class="modal center-modal fade" id="modal-centerdelete{{ $g->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form id="delete-form{{ $g->id }}" action="{{ route('Grades.destroy',$g->id) }}" method="POST"> 
            {{ method_field('Delete') }}
            @csrf
            <div class="box-body">
                <h4>{{ trans('opt.deletemsg') }}</h4>
            </div>
         </form>
      </div>
      <div class="modal-footer modal-footer-uniform">
       <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
       <a type="button" class="btn btn-primary float-end" 
          onclick="event.preventDefault(); document.getElementById('delete-form{{ $g->id }}').submit();">
          {{ trans('opt.delete2') }}
       </a>
      </div>
    </div>
   </div>
</div>
               @empty
                <tr>
                  <td colspan="3"><div class="admin-empty-state">لا توجد مستويات مطابقة للفلترة.</div></td>
                </tr>
               @endforelse
              </tbody>
           </table>
           <div class="mt-15 d-flex justify-content-end">
             {{ $Grade->links() }}
           </div>
           </div>
        </div>
       </div>      
     </div>    
</div>

<!-- Store Form -->
<form  id="addGrade-form" action="{{ route('Grades.store') }}" method="POST" class="mfp-hide white-popup-block"> 
   @csrf
   <div class="box-body">
      <div class="form-group">
        <label class="form-label">{{ trans('opt.classnamear') }}</label>
        <input type="text" name="name_gradesar" class="form-control" 
               placeholder="{{ trans('opt.classnamear') }}" required >
      </div>
   </div>
   <div class="box-footer">
      <a type="submit" class="btn btn-primary" 
         onclick="event.preventDefault(); document.getElementById('addGrade-form').submit();" >
        <i class="ti-save-alt"></i> {{ trans('opt.add') }}
      </a>
   </div>
</form>
@endsection

@section('jsa')
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>
<script src="{{ asset('jsadmin/pages/validation.js')}}"></script>
<script src="{{ asset('jsadmin/pages/form-validation.js')}}"></script>
<script src="{{ asset('assets/vendor_components/jquery-toast-plugin-master/src/jquery.toast.js')}}"></script>
<script src="{{ asset('jsadmin/pages/toastr.js')}}"></script>
<script src="{{ asset('jsadmin/pages/notification.js')}}"></script>
@endsection
