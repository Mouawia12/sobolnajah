@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   {{ trans('main_sidebar.publication') }}
@stop
@endsection

@section('contenta')
<div class="row">
   
   <div class="col-12">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><a class="popup-with-form btn btn-success" href="#addPublications-form">{{ trans('opt.addpublication') }}</a>
          </h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
           <form method="GET" class="row mb-3">
             <div class="col-md-3">
               <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                 placeholder="بحث: عنوان / محتوى">
             </div>
             <div class="col-md-2">
               <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
             </div>
             <div class="col-md-2">
               <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
             </div>
             <div class="col-md-2">
               <select name="grade_id" class="form-select">
                 <option value="">كل المستويات</option>
                 @foreach ($Grade as $g)
                   <option value="{{ $g->id }}" @selected((string) request('grade_id') === (string) $g->id)>{{ $g->name_grades }}</option>
                 @endforeach
               </select>
             </div>
             <div class="col-md-2">
               <select name="agenda_id" class="form-select">
                 <option value="">كل الأجندات</option>
                 @foreach ($Agenda as $ag)
                   <option value="{{ $ag->id }}" @selected((string) request('agenda_id') === (string) $ag->id)>{{ $ag->name_agenda }}</option>
                 @endforeach
               </select>
             </div>
             <div class="col-md-1 d-flex gap-1">
               <button class="btn btn-primary" type="submit">فلترة</button>
             </div>
           </form>
           <div class="table-responsive">
             <table class="table table-bordered text-center" style="width:100%">
              <thead>
                 <tr>
                    <th>#</th>
                    <th>{{ trans('opt.titlepublication') }}</th>
                    <th>{{ trans('opt.datepub') }}</th>
                    <th>{{ trans('inscription.action') }}</th>

                 </tr>
              </thead>
              <tbody>

               @foreach ($Publications as $index => $pub)
                 <tr>
                    <td>{{ $Publications->firstItem() + $index }}</td>
                    <td>{{ $pub->title }}</td>
                    <td>{{ $pub->created_at }}</td>

                    <td >
                                    {{-- زر تعديل --}}
                     <a data-bs-target="#modal-centeredit{{ $pub->id }}" data-bs-toggle="modal"
                        class="waves-effect waves-light btn btn-primary-light btn-circle mx-2">
                        <span class="icon-Write"><span class="path1"></span><span class="path2"></span></span>
                     </a>

                     {{-- زر حذف --}}
                     <a data-bs-target="#modal-centerdelete{{ $pub->id }}" data-bs-toggle="modal"
                        class="waves-effect waves-light btn btn-danger-light btn-circle">
                        <span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span>
                     </a>
                  </td>

                 </tr> 




<!-- Modal Edit -->
<div class="modal center-modal fade" id="modal-centeredit{{ $pub->id }}" tabindex="-1">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-body">
            <form id="update-form{{ $pub->id }}" action="{{ route('Publications.update',$pub->id) }}" method="POST" enctype="multipart/form-data">
               @csrf
               @method('PUT')

               <div class="box-body">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="form-label">{{ trans('pub.titlear') }}</label>
                           <input type="text" name="titlear" value="{{ $pub->getTranslation('title','ar') }}"
                                  class="form-control" required>
                        </div>
                     </div>

                     <div class="col-md-12">
                        <div class="form-croup">
                           <label class="form-label">{{ trans('inscription.ecole') }}</label>
                           <select class="form-select" name="school_id2">
                              @foreach ($School as $sc)
                              <option value="{{ $sc->id }}" {{ $sc->id == $pub->school_id ? 'selected' : '' }}>
                                 {{ $sc->name_school }}
                              </option>
                              @endforeach
                           </select>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="form-croup">
                           <label class="form-label">{{ trans('inscription.niveau') }}</label>
                           <select class="form-select" name="grade_id2">
                              @foreach ($Grade as $g)
                              <option value="{{ $g->id }}" {{ $g->id == $pub->grade_id ? 'selected' : '' }}>
                                 {{ $g->name_grades }}
                              </option>
                              @endforeach
                           </select>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="form-croup">
                           <label class="form-label">{{ trans('opt.nameagenda') }}</label>
                           <select class="form-select" name="agenda_id">
                              @foreach ($Agenda as $ag)
                              <option value="{{ $ag->id }}" {{ $ag->id == $pub->agenda_id ? 'selected' : '' }}>
                                 {{ $ag->name_agenda }}
                              </option>
                              @endforeach
                           </select>
                        </div>
                     </div>

                     <div class="col-md-12">
                        <div class="form-group">
                           <label class="form-label">{{ trans('pub.sujetar') }}</label>
                           <textarea rows="4" name="bodyar" class="form-control">{{ $pub->getTranslation('body','ar') }}</textarea>
                        </div>
                        <div class="mb-3">
                           <label class="form-label">{{ trans('opt.selectimg') }}</label>
                           <input class="form-control" type="file" name="img_url[]" multiple>
                           @if($pub->gallery && $pub->gallery->img_url)
                              <div class="mt-2">
                                 @foreach(json_decode($pub->gallery->img_url,true) as $img)
                                    <img src="{{ asset('agenda/'.$img) }}" width="80" class="m-1 rounded">
                                 @endforeach
                              </div>
                           @endif
                        </div>
                     </div>
                  </div>
               </div>

               <div class="box-footer">
                  <a type="submit" class="btn btn-primary"
                     onclick="event.preventDefault();document.getElementById('update-form{{ $pub->id }}').submit();">
                     <i class="ti-save-alt"></i> {{ trans('opt.update2') }}
                  </a>
                  <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

   <!-- Delete -->
<div class="modal center-modal fade" id="modal-centerdelete{{ $pub->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form id="delete-form{{ $pub->id }}" action="{{ route('Publications.destroy',$pub->id) }}" method="POST" > 
            
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
       document.getElementById('delete-form{{ $pub->id }}').submit();">{{ trans('opt.delete2') }}</a>
      </div>
    </div>
   </div>
   </div>
   @endforeach

              </tbody>
              <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('opt.titlepublication') }}</th>
                  <th>{{ trans('opt.datepub') }}</th>
                  <th>{{ trans('inscription.action') }}</th>
               </tr>
            </tfoot>
           </table>
           <div class="mt-3">
            {{ $Publications->links() }}
           </div>
           </div>
        </div>
        <!-- /.box-body -->
       </div>
       <!-- /.box -->      
     </div>    

</div>





     
<!-- Store Form -->
<form  id="addPublications-form" action="{{ route('Publications.store') }}" method="POST" class="mfp-hide white-popup-block" enctype="multipart/form-data"> 
   @csrf
   <div class="box-body">
      <div class="row">
        <div class="col-md-6">
         <div class="form-group">
           <label class="form-label">{{ trans('pub.titlear') }}</label>
           <input type="text" name="titlear" class="form-control" placeholder="{{ trans('pub.titlear') }}" required >
         </div>
        </div>
        <div class="col-md-12">
         <div class="form-croup">
           <label class="form-label">{{ trans('inscription.ecole') }}</label>
           <select class="form-select" name="school_id2">
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
           <select class="form-select" name="grade_id2">
            <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
            @foreach ($Grade as $g)
            <option value="{{ $g->id }}">{{ $g->name_grades }}</option>
            @endforeach
          </select>
         </div>
       </div>
       <div class="col-md-6">
         <div class="form-croup">
           <label class="form-label">{{ trans('opt.nameagenda') }}</label>
           <select id="agenda_id" class="form-select" name="agenda_id">
            <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
            @foreach ($Agenda as $ag)
            <option value="{{ $ag->id }}">{{ $ag->name_agenda }}</option>
            @endforeach
          </select>
         </div>
       </div>
        <div class="col-md-12">
        <div class="form-group">
          <label class="form-label">{{ trans('pub.sujetar') }}</label>
          <textarea rows="4" name="bodyar" class="form-control" placeholder="{{ trans('inscription.ecrire') }}"></textarea>
        </div>
        <div class="mb-3">
         <label for="formFileMultiple" class="form-label">{{ trans('opt.selectimg') }}</label>
         <input class="form-control"  type="file" name="img_url[]" id="formFileMultiple" multiple>
       </div>
  </div> 
      </div>
   </div>
   <!-- /.box-body -->
   <div class="box-footer">
      <a type="submit" class="btn btn-primary" onclick="event.preventDefault();
      document.getElementById('addPublications-form').submit();" >
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
