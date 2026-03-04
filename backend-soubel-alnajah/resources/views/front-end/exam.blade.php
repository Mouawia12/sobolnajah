@extends('layouts.masterhome')
@section('css')

@section('title')
    {{ trans('exam.exam') }}
@stop
@endsection

@section('content')
<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">						
                    <h2 class="page-title text-white">{{ trans('exam.exam') }}</h2>
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">{{ trans('exam.exam') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>


<!--Page content -->
	
<section class="py-50">

    <div class="table-responsive">
        <table class="table table-bordered text-center"  style="width:100%">
         <thead>
            <tr>
               <th></th>          
               <th class="col-md-2 alert-info">{{ trans('exam.name') }}</th>
               {{-- class before --}}
               <th class="col-md-2 alert-success"> {{ trans('exam.module') }}</th>
               <th class="col-md-2 alert-danger">{{ trans('exam.perscolaire') }}</th>
               <th class="col-md-2 alert-danger">{{ trans('exam.phase') }}</th>

              {{-- class after --}}
               <th class="col-md-2 alert-danger"> {{ trans('exam.Annscolaire') }}</th>
               <th>{{ trans('inscription.action') }}</th>

            </tr>
         </thead>
         <tbody>
            @php($i = ($Exames->currentPage() - 1) * $Exames->perPage())
            @forelse ($Exames as $ex)
            @php($i++)
              <tr>
                
                 <td>{{ $i }}</td> 
                 <td class="col-md-3">
                    <a href="#" class="text-dark fw-600 hover-primary fs-16">{{$ex->name}}</a>
                 </td>
                <td>{{$ex->specialization->name}}</td>     
                 <td class="col-md-2">{{$ex->classroom->name_class}}</td>
                 <td >{{$ex->schoolgrade->name_grade}}</td>
                 <td>{{$ex->Annscolaire}}</td>

                 <td >
                  <a href="{{ route('Exames.show',$ex->id) }}" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="fa fa-download"><span class="path1"></span><span class="path2"></span></span></a>
                 </td>
              </tr>  
              @empty
              <tr>
                <td colspan="7" class="text-muted py-3">لا توجد امتحانات متاحة حالياً.</td>
              </tr>
              @endforelse

         </tbody>
         <tfoot>
          <tr>
             <th> </th>
             <th class="col-md-2">{{ trans('exam.name') }}</th>
               {{-- class before --}}
               <th class="col-md-2"> {{ trans('exam.module') }}</th>
               <th class="col-md-2">{{ trans('exam.perscolaire') }}</th>
               <th class="col-md-2">{{ trans('exam.phase') }}</th>

              {{-- class after --}}
               <th > {{ trans('exam.Annscolaire') }}</th>
               <th>{{ trans('inscription.action') }}</th>
          </tr>
       </tfoot>
      </table>
      <div class="mt-3 d-flex justify-content-center">
        {{ $Exames->links() }}
      </div>
      </div>

</section>


@endsection


@section('js')
@endsection
