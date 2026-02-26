@extends('layouts.masterhome')
@section('css')

@section('title')
   {{ trans('main_header.inscription') }}
@stop
@endsection

@section('content')
<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">						
                    <h2 class="page-title text-white">{{ trans('main_header.inscription') }}</h2>
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">{{ trans('main_header.inscription') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="py-50">
    <div class="container">
        <div class="row align-items-center">

          @if(Session::get('success'))   
              <div class="alert alert-success alert-dismissible">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

              <h4><i class="icon fa fa-check"></i>{{  trans('inscription.enregistrésuccès') }}</h4>
              <h6>{{  trans('inscription.inscriptionok') }}</h6>
              </div>
         @endif

         @if ($errors->any())
            @foreach ($errors->all() as $error)
               <div class="alert alert-danger col-md-6">     
                     <p>{{ $error }}</p>
               </div>
            @endforeach
      @endif

      <div class="col-12"> 

     <!-- Validation wizard -->
     <div class="box">
			<div class="box-header with-border">
        <h3 class="box-title text-info mb-0 mt-20"><i class="mdi mdi-school me-15"></i>{{ trans('inscription.forminscription') }}</h3>
		
			</div>
			<!-- /.box-header -->
			<div class="box-body wizard-content">
				<form novalidate id="inscription-form" action="{{ route('Inscriptions.store')}}" class="validation-wizard wizard-circle" method="POST"> 
          {{-- validation-wizard  --}}
          @csrf
          <!-- Step 1 -->
					<h6>{{ trans('inscription.informationecole')}}</h6>
					<section>
						<div class="row">

              <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.ecole') }}</label>
                      <select id="school_id" class="form-select" name="school_id" onchange="console.log($(this).val())" required data-validation-required-message="This field is sssss">
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                         @foreach ($School as $sc)
                         <option value="{{ $sc->id}}">{{ $sc->name_school }}</option>
                         @endforeach
                      </select>
                    </div>
              </div>

              <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.niveau') }}</label>
                      <select id="grade_id" class="form-select" name="grade_id" onchange="console.log($(this).val())" required>
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      </select>
                    </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                      <select id="classroom_id" class="form-select" name="classroom_id" required>
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                      </select>
                    </div>
              </div>
            
              <div class="col-md-6">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.inscription') }}</label>
                      <select class="form-select" name="inscriptionetat" required>
                          <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                          <option value="nouvelleinscription">{{ trans('inscription.nouvelleinscription') }}</option>
                          <option value="reenregistrement">{{ trans('inscription.reenregistrement') }}</option>
                      </select>
                    </div>
              </div>
              
            </div>
  
            <div class="row">
              <div class="col-md-6">
               <div class="form-group">
                 <label class="form-label">{{ trans('inscription.nomecoleprecedente') }}</label>
                 <input type="text" name="nomecoleprecedente" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required >
               </div>
              </div>
              <div class="col-md-6">
               <div class="form-group">
                 <label class="form-label">{{ trans('inscription.dernieresection') }}</label>
                 <input type="text" name="dernieresection" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
               </div>
              </div>
              <div class="col-md-6">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.moyensannuels') }}</label>
                   <input type="number" name="moyensannuels" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                 </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">{{ trans('inscription.numeronationaletudiant') }}</label>
                    <input type="number" name="numeronationaletudiant" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                  </div>
                 </div>
              </div>  
					</section>
					<!-- Step 2 -->


					<h6>{{ trans('inscription.informationétudiant') }}</h6>
					<section>
						
            <div class="row">
              <div class="col-md-4">
               <div class="form-group">
                 <label class="form-label">{{ trans('inscription.prenomfr') }}</label>
                 <input type="text" name="prenomfr" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
               </div>
              </div>
              <div class="col-md-4">
               <div class="form-group">
                 <label class="form-label">{{ trans('inscription.nomfr') }}</label>
                 <input type="text" name="nomfr" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
               </div>
              </div>
              <div class="col-md-4">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.prenomar') }}</label>
                   <input type="text" name="prenomar" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                 </div>
                </div>
                <div class="col-md-4">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.nomar') }}</label>
                   <input type="text" name="nomar" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                 </div>
                </div>
              
           
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.email') }}</label>
                <input type="email" name="email" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
              </div>
             </div>
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.gender') }}</label>
                <select class="form-select" name="gender" required>
                  <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                  <option value="{{1}}">{{ trans('inscription.male') }}</option>
                  <option value="{{0}}">{{ trans('inscription.female') }}</option>
               </select>
                </div>
             </div>
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.numtelephone') }}</label>
                <input type="number" name="numtelephone" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
              </div>
             </div>
             <div class="col-md-4">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.datenaissance') }}</label>
                   <input type="date" name="datenaissance" class="form-control" required>
                 </div>
                </div>
             <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">{{ trans('inscription.lieunaissance') }}</label>
                  <input type="text" name="lieunaissance" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                </div>
               </div>
               
             
           
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.wilaya') }}</label>
                <input type="text" name="wilaya" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
              </div>
             </div>
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.dayra') }}</label>
                <input type="text" name="dayra" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
              </div>
             </div>
             <div class="col-md-4">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.baladia') }}</label>
                   <input type="text" name="baladia" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                 </div>
             </div>
             <div class="col-md-4">
                 <div class="form-group">
                   <label class="form-label">{{ trans('inscription.adresseactuelle') }}</label>
                   <input type="text" name="adresseactuelle" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                 </div>
                </div> 
         
             
             <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">{{ trans('inscription.codepostal') }}</label>
                <input type="number" name="codepostal" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
              </div>
             </div>
             <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">{{ trans('inscription.residenceactuelle') }}</label>
                  <select class="form-select" name="residenceactuelle" required>
                     <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                     <option value="parents">{{ trans('inscription.parents') }}</option>
                     <option value="pere">{{ trans('inscription.pere') }}</option>
                     <option value="mere">{{ trans('inscription.mere') }}</option>
                     <option value="autrepersonne">{{ trans('inscription.autrepersonne') }}</option>
                  </select>
                </div>
             </div>
             <div class="col-md-4">
                 <div class="form-group">
                     <label class="form-label">{{ trans('inscription.etatsante') }}</label>
                     <select class="form-select" name="etatsante" required>
                        <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                        <option value="bien">{{ trans('inscription.bien') }}</option>
                        <option value="maladiechronique">{{ trans('inscription.maladiechronique') }}</option>
                     </select>
             </div>
                          
             </div> 
             <div class="col-md-4">
                     <div class="form-group">
                       <label class="form-label">{{ trans('inscription.identificationmaladie') }}</label>
                       <input type="text" name="identificationmaladie" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                     </div>
             </div>
          
            <div class="col-lg-6">
                 <div class="form-group">
                     <label class="form-label">{{ trans('inscription.alfdlprsaldr') }}</label>
                     <select class="form-select" name="alfdlprsaldr" required>
                         <option value="" selected disabled>{{ trans('inscription.choisir') }}</option>
                        <option value="oui">{{ trans('inscription.oui') }}</option>
                        <option value="no">{{ trans('inscription.no') }}</option>
                     </select>
                   </div>
                </div>
           </div>
			  	</section>
					<!-- Step 3 -->
					<h6>{{ trans('inscription.informationwali') }}</h6>

					<section>
						
                  <div class="row">
                    <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.prenomfr') }}</label>
                      <input type="text" name="prenomfrwali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                    </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.nomfr') }}</label>
                      <input type="text" name="nomfrwali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                    </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.prenomar') }}</label>
                        <input type="text" name="prenomarwali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                      </div>
                      </div>
                      <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.nomar') }}</label>
                        <input type="text" name="nomarwali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                      </div>
                      </div>
                    

                    <div class="col-md-4">
                        <div class="form-group">
                          <label class="form-label">{{ trans('inscription.relationetudiant') }}</label>
                          <input type="text" name="relationetudiant" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label class="form-label">{{ trans('inscription.adresse') }}</label>
                          <input type="text" name="adressewali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                        </div>
                      </div>
                    <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.numtelephone') }}</label>
                      <input type="number" name="numtelephonewali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required> 
                    </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">{{ trans('inscription.email') }}</label>
                        <input type="email" name="emailwali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                      </div>
                     </div>
                    <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">{{ trans('inscription.wilaya') }}</label>
                      <input type="text" name="wilayawali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                    </div>
                    </div>
                    
             
                    <div class="col-md-4">
                              <div class="form-group">
                                <label class="form-label">{{ trans('inscription.dayra') }}</label>
                                <input type="text" name="dayrawali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                              </div>
                      </div>
                    <div class="col-md-4">
                              <div class="form-group">
                                <label class="form-label">{{ trans('inscription.baladia') }}</label>
                                <input type="text" name="baladiawali" class="form-control" placeholder="{{ trans('inscription.ecrire') }}" required>
                        </div>
                      </div>

                  </div>
		
					</section>
					<!-- Step 4 -->


					<h6>{{ trans('inscription.autresnotes') }}</h6>
					<section>
            <div class="row">
              <div class="col-md-12">
                  <div class="form-group">
                      <label class="form-label">{{ trans('inscription.autresnotes') }}</label>
                      <textarea rows="4" name="autresnotes" class="form-control"  placeholder="{{ trans('inscription.ecrire') }}" ></textarea>
                    </div>
              </div> 
            </div>
					</section>
				</form>

			</div>
			<!-- /.box-body -->
</div>
    </div>

    <!-- /.box -->			
 </div>
</div>
</section>

@endsection


@section('js')
<script>
  $(document).ready(function () {
      $('select[name="school_id"]').on('change', function () {
          var school_id = $(this).val();
          if (school_id) {
              $.ajax({
                  url: "{{ route('lookup.schoolGrades', ['id' => '__ID__']) }}".replace('__ID__', school_id),
                  type: "GET",
                  dataType: "json",
                  success: function (data) {
                      $('select[name="grade_id"]').empty();
                      $('select[name="grade_id"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
                      $.each(data, function (key, value) {
                          $('select[name="grade_id"]').append('<option value="' + key + '">' + value + '</option>');
                      });
                  },
              });
          } else {
              console.log('AJAX load did not work');
          }
      });
  });

</script>
<script>
  $(document).ready(function () {
      $('select[name="grade_id"]').on('change', function () {
          var grade_id = $(this).val();
          if (grade_id) {
              $.ajax({
                  url: "{{ route('lookup.gradeClasses', ['id' => '__ID__']) }}".replace('__ID__', grade_id),
                  type: "GET",
                  dataType: "json",
                  success: function (data) {
                      $('select[name="classroom_id"]').empty();
                      $('select[name="classroom_id"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
                      $.each(data, function (key, value) {
                          $('select[name="classroom_id"]').append('<option value="' + key + '">' + value + '</option>');
                      });
                  },
              });
          } else {
              console.log('AJAX load did not work');
          }
      });
  });

</script>




<script src="{{ asset('assets/vendor_components/jquery-steps-master/build/jquery.steps.js')}}"></script>
<script src="{{ asset('assets/vendor_components/jquery-validation-1.17.0/dist/jquery.validate.min.js')}}"></script>


	

	
<script>
          
        $(".tab-wizard").steps({
            headerTag: "h6"
            , bodyTag: "section"
            , transitionEffect: "none"
            , titleTemplate: '<span class="step">#index#</span> #title#'
            , labels: {
                finish: "Submit",
              
            }
            , onFinished: function (event, currentIndex) {
              swal("Your Order Submitted!", "Sed dignissim lacinia nunc. Curabitur tortor. Pellentesque nibh. Aenean quam. In scelerisque sem at dolor. Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor.");
                    
            }
        });


        var form = $(".validation-wizard").show();

        $(".validation-wizard").steps({
            headerTag: "h6"
            , bodyTag: "section"
            , transitionEffect: "none"
            , titleTemplate: '<span class="step">#index#</span> #title#'
            , labels: {
                finish: "{{ trans('inscription.inscription') }}",
                next:  "{{ trans('inscription.next')}}" ,
                previous: "{{ trans('inscription.Previous')}}",
                
            }
            , onStepChanging: function (event, currentIndex, newIndex) {
                return currentIndex > newIndex || !(3 === newIndex && Number($("#age-2").val()) < 18) && (currentIndex < newIndex && (form.find(".body:eq(" + newIndex + ") label.error").remove(), form.find(".body:eq(" + newIndex + ") .error").removeClass("error")), form.validate().settings.ignore = ":disabled,:hidden", form.valid())
            }
            , onFinishing: function (event, currentIndex) {
                return form.validate().settings.ignore = ":disabled", form.valid()

            }
            , onFinished: function (event, currentIndex) {
                //swal("Your Form Submitted!", "Sed dignissim lacinia nunc. Curabitur tortor. Pellentesque nibh. Aenean quam. In scelerisque sem at dolor. Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor.");
           
                event.preventDefault();
               document.getElementById('inscription-form').submit();

              }
        }), $(".validation-wizard").validate({
            ignore: "input[type=hidden]"
            , errorClass: "text-danger"
            , successClass: "text-success"
            , highlight: function (element, errorClass) {
                $(element).removeClass(errorClass)
            }
            , unhighlight: function (element, errorClass) {
                $(element).removeClass(errorClass)
            }
            , errorPlacement: function (error, element) {
                error.insertAfter(element)
            }
            , rules: {
                email: {
                    email: !0 
                },
                moyensannuels: {
                   //minlength: 10,
                   number : 1
                },
                codepostal: {
                   //minlength: 10,
                   number : 1
                },
                emailwali: {
                    email: !0 
                },
            },
            messages: {
        school_id: {
            required: "{{ trans('validation.select') }}"
        },
        grade_id: {
            required: "{{ trans('validation.select') }}"
        },
        classroom_id: {
            required: "{{ trans('validation.select') }}"
        },
        inscriptionetat: {
            required: "{{ trans('validation.select') }}"
        },
        nomecoleprecedente: {
            required: "{{ trans('validation.required') }}"
        },
        dernieresection: {
            required: "{{ trans('validation.required') }}"
        },
        moyensannuels: {
            required: "{{ trans('validation.required') }}",
            //minlength: "please min",
            number : "{{ trans('validation.numeric') }}"

        },
        numeronationaletudiant: {
            required: "{{ trans('validation.required') }}"
        },
        prenom: {
            required: "{{ trans('validation.required') }}"
        },
        nom: {
            required: "{{ trans('validation.required') }}"
        },
        email: {
            required: "{{ trans('validation.required') }}",
            email: "{{ trans('validation.email') }}"
        },
        emailwali :{
            required: "{{ trans('validation.required') }}",
            email: "{{ trans('validation.email') }}"
        },
        numtelephone: {
            required: "{{ trans('validation.required') }}"
        },
        datenaissance: {
            required: "{{ trans('validation.date') }}"
        },
        lieunaissance: {
            required: "{{ trans('validation.required') }}"
        },
        wilaya: {
            required: "{{ trans('validation.required') }}"
        },
        dayra: {
            required: "{{ trans('validation.required') }}"
        },
        baladia: {
            required: "{{ trans('validation.required') }}"
        },
        adresseactuelle: {
            required: "{{ trans('validation.required') }}"
        },
        codepostal: {
            required: "{{ trans('validation.required') }}",
            number : "{{ trans('validation.numeric') }}"

        },
        residenceactuelle: {
            required: "{{ trans('validation.select') }}"
        },
        etatsante: {
            required: "{{ trans('validation.select') }}"
        },
        identificationmaladie: {
            required: "{{ trans('validation.required') }}"
        },
        alfdlprsaldr: {
            required: "{{ trans('validation.select') }}"
        },
        autresnotes: {
            required: "{{ trans('validation.required') }}"
        },
        prenomwali: {
            required: "{{ trans('validation.required') }}"
        },
        nomwali: {
            required: "{{ trans('validation.required') }}"
        },
        relationetudiant: {
            required: "{{ trans('validation.required') }}"
        },
        adressewali: {
            required: "{{ trans('validation.required') }}"
        },
        numtelephonewali: {
            required: "{{ trans('validation.required') }}"
        },
        wilayawali: {
            required: "{{ trans('validation.required') }}"
        },
        dayrawali: {
            required: "{{ trans('validation.required') }}"
        },
        baladiawali: {
            required: "{{ trans('validation.required') }}"
        }
    },
        })

</script>


@endsection
