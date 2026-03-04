@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
     {{ trans('main_sidebar.classe') }}
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
         <h3 class="box-title"><a data-bs-target="#modal-store" data-bs-toggle="modal" class="btn btn-success">{{ trans('main_sidebar.addclasse') }}</a></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
           <form method="GET" action="{{ route('Schoolgrades.index') }}" class="admin-form-panel mb-15">
             <div class="row">
               <div class="col-md-4">
                 <label class="form-label">بحث</label>
                 <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم المستوى">
               </div>
               @if(!$currentSchoolId)
               <div class="col-md-4">
                 <label class="form-label">{{ trans('inscription.ecole') }}</label>
                 <select name="school_id" class="form-select">
                   <option value="">الكل</option>
                   @foreach ($School as $sc)
                     <option value="{{ $sc->id }}" @selected((string) request('school_id') === (string) $sc->id)>{{ $sc->name_school }}</option>
                   @endforeach
                 </select>
               </div>
               @endif
               <div class="col-md-4 d-flex align-items-end gap-2">
                 <button type="submit" class="btn btn-primary">بحث</button>
                 <a href="{{ route('Schoolgrades.index') }}" class="btn btn-light">Reset</a>
               </div>
             </div>
           </form>
           <div class="table-responsive">
            <table class="table table-bordered text-center" style="width:100%">
               <thead>
                 <tr>
                    <th>#</th>
                    <th>{{ trans('inscription.niveau') }}</th>

                    <th>{{ trans('inscription.ecole') }}</th>
                    <th>{{ trans('inscription.notes') }}</th>
                    <th>{{ trans('inscription.action') }}</th>
                 </tr>
              </thead>
              <tbody>

               @php($i = ($Schoolgrade->currentPage() - 1) * $Schoolgrade->perPage())
               @forelse ($Schoolgrade as $gr)
               @php($i++)
                 <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $gr->name_grade }}</td>
                    <td>{{ $gr->school->name_school }}</td>
                    <td>{{ $gr->notes }}</td>
                    <td >
                     {{-- <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a> --}}
                     <a data-bs-target="#modal-update{{ $gr->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                     <a data-bs-target="#modal-delete{{ $gr->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                     </td>
                 </tr>  



<!-- Update Form -->
<div class="modal center-modal fade" id="modal-update{{ $gr->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
         <form novalidate id="update-form{{ $gr->id }}" action="{{ route('Schoolgrades.update', $gr->id ) }}" method="POST" > 
            
            {{ method_field('patch') }}
            @csrf
            <div class="box-body">
               <div class="row">
                 <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">{{ trans('opt.classnamefr') }}</label>
                    <input type="text" name="name_gradefr" value="{{ $gr->getTranslation('name_grade', 'fr') }}" class="form-control" placeholder="france Name" required data-validation-required-message="{{ trans('validation.required') }}">
                  </div>
                 </div>
                 <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">{{ trans('opt.classnamear') }}</label>
                    <input type="text" name="name_gradear" value="{{ $gr->getTranslation('name_grade', 'ar') }}"  class="form-control" placeholder="arabic Name" required data-validation-required-message="{{ trans('validation.required') }}">
                  </div>
                 </div>
                 <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.ecole') }} </label>
                      <select class="form-select" name="school_id">
                        <option value="{{ $gr->school->id }}" selected >{{ $gr->school->name_school }}</option>
                      @foreach ($School as $sc)
                        <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                      @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.autrenotesfr') }} </label>
                      <textarea rows="5" name="notesfr"  class="form-control" placeholder="About Project">{{ $gr->getTranslation('notes', 'fr') }}</textarea>
                    </div>
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.autrenotesar') }}</label>
                      <textarea rows="5" name="notesar"  class="form-control" placeholder="About Project">{{ $gr->getTranslation('notes', 'ar') }}</textarea>
                    </div>
                </div>
               </div>
            </div>
         </form>
      </div>
      <div class="modal-footer modal-footer-uniform">
       <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
       <a type="submit" class="btn btn-primary float-end" onclick="event.preventDefault();
       document.getElementById('update-form{{ $gr->id }}').submit();">{{ trans('opt.update2') }}</a>

      </div>
    </div>
   </div>
   </div>



                    <!-- Delete -->
<div class="modal center-modal fade" id="modal-delete{{ $gr->id }}" tabindex="-1">
   <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-body">
         <form id="delete-form{{ $gr->id }}" action="{{ route('Schoolgrades.destroy',$gr->id) }}" method="POST" > 
            
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
       document.getElementById('delete-form{{ $gr->id }}').submit();">{{ trans('opt.delete2') }}</a>
      </div>
    </div>
   </div>
   </div>
               @empty
                <tr>
                  <td colspan="5"><div class="admin-empty-state">لا توجد مستويات مطابقة للفلترة.</div></td>
                </tr>
               @endforelse

              </tbody>
              <tfoot>
               <tr>
                  <th></th>
                  <th>{{ trans('inscription.niveau') }}</th>
                  <th>{{ trans('inscription.ecole') }}</th>
                  <th>{{ trans('inscription.notes') }}</th>
                  <th>{{ trans('inscription.action') }}</th>

               </tr>
            </tfoot>
           </table>
           <div class="mt-15 d-flex justify-content-end">
            {{ $Schoolgrade->links() }}
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
            <form  id="store-form" action="{{ route('Schoolgrades.store') }}" method="POST"> 
               @csrf
                   <div class="box-body">
                      <div class="row">
                        <div class="col-md-6">
                         <div class="form-group">
                           <label class="form-label">{{ trans('opt.classnamefr') }}</label>
                           <input type="text" name="name_gradefr" class="form-control" placeholder="{{ trans('opt.classnamefr') }}">
                         </div>
                        </div>
                        <div class="col-md-6">
                         <div class="form-group">
                           <label class="form-label">{{ trans('opt.classnamear') }}</label>
                           <input type="text" name="name_gradear" class="form-control" placeholder="{{ trans('opt.classnamefr') }}">
                         </div>
                        </div>
                      </div>
                      <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.ecole') }}</label>
                        <select class="form-select" name="school_id">
                        
                        @foreach ($School as $sc)
                          <option value="{{ $sc->id }}">{{ $sc->name_school }}</option>
                        @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ trans('inscription.autrenotesfr') }}</label>
                        <textarea rows="5" name="notesfr" class="form-control" placeholder="{{ trans('inscription.autrenotesfr') }}"></textarea>
                      </div>
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.autrenotesar') }}</label>
                        <textarea rows="5" name="notesar" class="form-control" placeholder="{{ trans('inscription.autrenotesar') }}"></textarea>
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
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>

@endsection
