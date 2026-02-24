@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('student.studentlist') }}
@stop
@endsection

@section('contenta')
<?php $i = 0; ?>

<div class="row">
    <div class="col-12">

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger col-md-6">
                    <p>{{ $error }}</p>
                </div>
            @endforeach
        @endif


        <!-- box-slided-up -->

        <div class="box box-slided-down">
            <div class="box-header with-border bg-info">
                <h4 class="box-title"><strong>{{ trans('student.studentspromotion') }}</strong></h4>
                <ul class="box-controls pull-right">
                    <li><a class="box-btn-slide text-white" href="#"></a></li>
                    <li><a class="box-btn-fullscreen text-white" href="#"></a></li>
                </ul>
            </div>

            <div class="box-body">
                <form method="POST" id="promotion" action="{{ route('Addnotestudents.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">

                        <div class="row">

                            <div class="form-group col">
                                <label class="form-label">{{ trans('inscription.ecole') }}</label>
                                <select id="student" class="form-select" name="student_id">
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                                    @foreach ($StudentInfo as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->id }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col">
                                <label class="form-label">{{ trans('school.Annéescolaire') }}</label>
                                <input class="form-control" name="note_file" type="file" id="formFile"
                                    accept="application/pdf">
                                {{-- accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*,image/jpeg" --}}
                            </div>

                            <div class="form-group col">
                                <label class="form-label">{{ trans('school.Annéescolaire') }}</label>
                                <select id="Anneescolaire" class="form-select" name="Anneescolaire" required>
                                    <option value="" selected disabled>{{ trans('inscription.choisir') }}
                                    </option>
                                    <option value="1">سداسي الاول</option>
                                    <option value="2">السداسي الثاني</option>
                                    <option value="3">السداسي الثالث</option>


                                </select>
                            </div>



                        </div>



                        <a type="button" class="btn btn-primary"
                            onclick="event.preventDefault();
                                 document.getElementById('promotion').submit();">{{ trans('opt.save') }}</a>
                    </div>

                </form>
            </div>
        </div>




        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <table id="example5" class="table table-bordered text-center" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th> {{ trans('school.season1') }}</th>
                            <th class="col-md-2">{{ trans('school.season2') }}</th>
                            <th class="col-md-2">{{ trans('school.season3') }}</th>
                            {{-- <th class="col-md-2">{{ trans('school.Annéescolaire') }}</th> --}}
                            <th class="col-md-2">{{ trans('inscription.action') }}</th>

                        </tr>
                    </thead>
                    <tbody>

                        <?php $i = 0; ?>
                        @foreach ($StudentInfo as $student)
                            @foreach ($NoteStudents as $noteStudent)
                                @if ($student->id == $noteStudent->student_id)
                                    <?php $i++; ?>
                                    <tr>

                                        <td class="col-md-1">{{ $i }}</td>
                                        <td class="col-md-2">
                                            <a href="#"
                                                class="text-dark fw-600 hover-primary fs-16">{{ $student->prenom }}
                                                {{ $student->id }}</a>
                                            <span class="text-fade d-block">2023-2024</span>
                                        </td>

                                        @if ($student->id == $noteStudent->student_id && $noteStudent->urlfile1 != null)
                                            <td class="col-md-2">
                                                <a href="/DisplqyNoteFromAdmin/{{ $noteStudent->urlfile1 }}"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="/DownloadNoteFromAdmin/{{ $noteStudent->urlfile1 }}"
                                                    class="waves-effect waves-light btn btn-success btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @else
                                            <td class="col-md-2">
                                                <a href="#"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info-light"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="#"
                                                    class="waves-effect waves-light btn btn-success-light btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @endif


                                        @if ($student->id == $noteStudent->student_id && $noteStudent->urlfile2 != null)
                                            <td class="col-md-2 ">
                                                <a href="/DisplqyNoteFromAdmin/{{ $noteStudent->urlfile2 }}"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="/DownloadNoteFromAdmin/{{ $noteStudent->urlfile2 }}"
                                                    class="waves-effect waves-light btn btn-success btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @else
                                            <td class="col-md-2 ">
                                                <a href="#"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info-light"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="#"
                                                    class="waves-effect waves-light btn btn-success-light btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @endif

                                        {{-- <td class="col-md-2"><input class="form-control" type="file" id="formFile"></td> --}}


                                        @if ($student->id == $noteStudent->student_id && $noteStudent->urlfile3 != null)
                                            <td class="col-md-2 ">
                                                <a href="/DisplqyNoteFromAdmin/{{ $noteStudent->urlfile3 }}"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="/DownloadNoteFromAdmin/{{ $noteStudent->urlfile3 }}"
                                                    class="waves-effect waves-light btn btn-success btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @else
                                            <td class="col-md-2 ">
                                                <a href="#"
                                                    class="waves-effect waves-circle btn btn-social-icon btn-circle btn-info-light"><span
                                                        class="fa fa-eye"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                                <a href="#"
                                                    class="waves-effect waves-light btn btn-success-light btn-circle"><span
                                                        class="fa fa-download"><span class="path1"></span><span
                                                            class="path2"></span></span></a>
                                            </td>
                                        @endif


                                        {{-- <td > 2023-2024</td> --}}

                                        <td class="col-md-2">
                                            {{-- <a data-bs-target=".modal-update{{ $student->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a> --}}
                                            <a data-bs-target="#modal-delete{{ $noteStudent->id }}"
                                                data-bs-toggle="modal"
                                                class="waves-effect waves-light btn btn-danger-light btn-circle"><span
                                                    class="icon-Trash1 fs-18"><span class="path1"></span><span
                                                        class="path2"></span></span></a>
                                        </td>
                                    </tr>

                                    <!-- Delete -->
                                    <div class="modal center-modal fade" id="modal-delete{{ $noteStudent->id }}"
                                        tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">

                                                <div class="modal-body">
                                                    <form id="delete-form{{ $noteStudent->id }}"
                                                        action="{{ route('Addnotestudents.destroy', $noteStudent->id) }}"
                                                        method="POST">

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
                                                    <a type="button" class="btn btn-danger"
                                                        data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                                    <a type="button" class="btn btn-primary float-end"
                                                        onclick="event.preventDefault();
            document.getElementById('delete-form{{ $noteStudent->id }}').submit();">{{ trans('opt.delete2') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update Form -->
                                    {{-- <div class="modal fade modal-update{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
          <div class="modal-content">
            
            <div class="modal-body">
                <form novalidate id="update-form{{ $student->id }}" action="{{ route('Addnotestudents.store') }}" method="POST" > 
                
                  @csrf
                  
                  

              <div class="box-body">

              <h4 class="box-title text-info mb-0 mt-20"><i class="ti-home me-15"></i>{{ trans('inscription.informationecole') }}</h4>
                <hr class="my-15">

                
                <div class="row">
  
                 
              
                  <div class="form-group col">
                      <label class="form-label">{{ trans('inscription.niveau') }}</label>
                          <select id="grade_id" class="form-select"  onchange="console.log($(this).val())" required>
                              <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                          </select>
                  </div>
                 

              </div>

                <h4 class="box-title text-info mb-0"><i class="ti-id-badge me-15"></i>   {{ trans('inscription.informationétudiant') }} </h4>
                <hr class="my-15">

                <div class="row">
                 
                  <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">{{ trans('inscription.dayra') }}</label>
                    <input type="text" name="student_id" class="form-control" >
                  </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.baladia') }}</label>
                        <input class="form-control" type="file" name="note_file" id="formFile">
                      </div>
                  </div>
            

                </div>

                </form>
            </div>
            <div class="modal-footer modal-footer-uniform">
              <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
              <a type="submit" class="btn btn-primary float-end" onclick="event.preventDefault();
              document.getElementById('update-form{{ $student->id}}').submit();">{{ trans('opt.update2') }}</a>

            </div>
          </div>
          </div>
      </div> --}}
                                @endif
                            @endforeach
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <th> </th>
                            <th>{{ trans('inscription.student') }}</th>
                            <th>{{ trans('inscription.ecole') }}</th>
                            <th>{{ trans('inscription.Anneescolaire') }}</th>
                            <th>{{ trans('inscription.niveau') }}</th>
                            {{-- <th>{{ trans('inscription.section') }}</th> --}}
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
<script>
    $(document).ready(function() {
        $('select[name="section_id"]').on('change', function() {
            var section_id = $(this).val();
            var result_explode = section_id.split('');
            var student_id = $result_explode[0];
            var section_id = $result_explode[1];

            if (section_id) {
                $.ajax({
                    url: "{{ URL::to('getsection') }}",
                    type: "POST",
                    data: {
                        "1": $student_id,
                        "2": $section_id
                    },
                    dataType: "json",
                    success: function(data) {
                        $('select[name="section_id"]').empty();
                        $('select[name="section_id"]').append(
                            '<option value="" selected disabled>{{ trans('Inscription.choisir') }}</option>'
                            );
                        $.each(data, function(key, value) {
                            $('select[name="grade_id"]').append('<option value="' +
                                key + '">' + value + '</option>');
                        });
                    },
                });
            } else {
                console.log('AJAX load did not work');
            }
        });
    });
</script>

<script src="{{ asset('assets/vendor_components/datatable/datatables.min.js') }}"></script>

@include('layoutsadmin.datatabels')

@endsection
