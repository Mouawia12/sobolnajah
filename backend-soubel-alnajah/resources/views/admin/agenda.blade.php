@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('main_sidebar.agenda') }}
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
          <h3 class="box-title"><a class="popup-with-form btn btn-success" href="#addAgenda-form">{{ trans('opt.addagenda') }} </a>
          </h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
           <form method="GET" action="{{ route('Agendas.index') }}" class="admin-form-panel mb-15">
             <div class="row">
               <div class="col-md-4">
                 <label class="form-label">بحث</label>
                 <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ trans('opt.nameagenda') }}">
               </div>
               <div class="col-md-4 d-flex align-items-end gap-2">
                 <button class="btn btn-primary" type="submit">بحث</button>
                 <a href="{{ route('Agendas.index') }}" class="btn btn-light">Reset</a>
               </div>
             </div>
           </form>
           <div class="table-responsive">
             <table class="table table-bordered text-center" style="width:100%">
              <thead>
                 <tr>
                    <th>#</th>
                    <th>{{ trans('opt.nameagenda') }}</th>
                    <th>{{ trans('inscription.action') }}</th>

                 </tr>
              </thead>
              <tbody>

               @php($i = ($Agenda->currentPage() - 1) * $Agenda->perPage())
               @forelse ($Agenda as $ag)
               @php($i++)
                 <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $ag->name_agenda }}</td>
                    <td >
                     <a data-bs-target="#modal-center{{ $ag->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                     <a data-bs-target="#modal-centerdelete{{ $ag->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                     </td>

                 </tr>  

<!-- update Form -->
<div class="modal center-modal fade" id="modal-center{{ $ag->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form novalidate id="update-form{{ $ag->id }}" action="{{ route('Agendas.update', $ag->id ) }}" method="POST" > 
            
            {{ method_field('patch') }}
            @csrf
            <div class="box-body">
               <div class="row">
                 <div class="col-md-12">
                  <div class="form-group">
                    <label class="form-label">{{ trans('opt.agendanamear') }}</label>
                    <input type="text" name="name_agendaar" value="{{ $ag->getTranslation('name_agenda', 'ar') }}" class="form-control"  required >
                  </div>
                 </div>
               </div>
            </div>
         </form>
      </div>
      <div class="modal-footer modal-footer-uniform">
       <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
       <a type="submit" class="btn btn-primary float-end" onclick="event.preventDefault();
       document.getElementById('update-form{{ $ag->id }}').submit();">{{ trans('opt.update2') }}</a>

      </div>
    </div>
   </div>
   </div>


   <!-- Delete -->
<div class="modal center-modal fade" id="modal-centerdelete{{ $ag->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form id="delete-form{{ $ag->id }}" action="{{ route('Agendas.destroy',$ag->id) }}" method="POST" > 
            
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
       document.getElementById('delete-form{{ $ag->id }}').submit();">{{ trans('opt.delete2') }}</a>
      </div>
    </div>
   </div>
   </div>
               @empty
                <tr>
                  <td colspan="3"><div class="admin-empty-state">لا توجد نتائج مطابقة.</div></td>
                </tr>
               @endforelse

              </tbody>
              <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('opt.nameagenda') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
               </tr>
            </tfoot>
           </table>
           <div class="mt-15 d-flex justify-content-end">
             {{ $Agenda->links() }}
           </div>
           </div>
        </div>
        <!-- /.box-body -->
       </div>
       <!-- /.box -->      
     </div>    

</div>

     
<!-- Store Form -->
<form  id="addAgenda-form" action="{{ route('Agendas.store') }}" method="POST" class="mfp-hide white-popup-block"> 
   @csrf
   <div class="box-body">
      <div class="row">
        <div class="col-md-12">
         <div class="form-group">
           <label class="form-label">{{ trans('opt.agendanamear') }}</label>
           <input type="text" name="name_agendaar" class="form-control" placeholder="{{ trans('opt.agendanamear') }}" required >
         </div>
        </div>
      </div>
   </div>
   <!-- /.box-body -->
   <div class="box-footer">
      <a type="submit" class="btn btn-primary" onclick="event.preventDefault();
      document.getElementById('addAgenda-form').submit();" >
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
