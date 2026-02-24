@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
    {{ trans('inscription.inscriptionstudent') }}
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

            <!-- /.box-header -->

            <div class="box-body">
                <h3 class="box-title"><a data-bs-target="#modal-store" data-bs-toggle="modal" class="btn btn-danger"
                        id="btn_delete_all">{{ trans('opt.deleteall') }}</a></h3>

                <div class="table-responsive">
                    <table id="example5" class="table table-bordered text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="md_checkbox_0"
                                        onclick="CheckAll('chk-col-primary', this)" class="chk-col-info" />
                                    <label for="md_checkbox_0"></label>
                                </th>
                                <th>{{ trans('inscription.student') }}</th>
                                <th>{{ trans('inscription.status') }}</th>
                                <th> {{ trans('inscription.ecole') }}</th>
                                <th class="col-md-2">{{ trans('inscription.Anneescolaire') }}</th>
                                <th>{{ trans('inscription.niveau') }}</th>
                                <th>{{ trans('inscription.section') }}</th>







                                {{-- <th>School Name</th>                   
                    <th>classes Name</th>
                    <th>School grade name</th> --}}

                                <th class="col-md-3">{{ trans('inscription.action') }}</th>

                            </tr>
                        </thead>
                        <tbody>

                            <?php $i = 0; ?>
                            @foreach ($Inscription as $ins)
                                <?php $i++; ?>
                                <tr>

                                    <td>
                                        <input type="checkbox" id="md_checkbox_{{ $i }}"
                                            value="{{ $ins->id }}" class="chk-col-primary" />
                                        <label for="md_checkbox_{{ $i }}">{{ $i }}</label>
                                    </td>
                                    <td>
                                        <a href="#"
                                            class="text-dark fw-600 hover-primary fs-16">{{ $ins->prenom }}
                                            {{ $ins->nom }}</a>
                                        <span class="text-fade d-block">{{ $ins->email }}</span>
                                        <span class="text-fade d-block">{{ $ins->numtelephone }}</span>

                                    </td>

                                    <td>
                                        @if ($ins->statu == 'procec')
                                            <a class="btn" data-bs-target="#modal-status{{ $ins->id }}"
                                                data-bs-toggle="modal"><span
                                                    class="badge badge-warning-light">{{ trans('inscription.undefined') }}
                                                </span> </a>
                                        @endif
                                        @if ($ins->statu == 'accept')
                                            <a class="btn" data-bs-target="#modal-status{{ $ins->id }}"
                                                data-bs-toggle="modal"><span
                                                    class="badge badge-success-light">{{ trans('inscription.accept') }}</span>
                                            </a>
                                        @endif
                                        @if ($ins->statu == 'noaccept')
                                            <a class="btn" data-bs-target="#modal-status{{ $ins->id }}"
                                                data-bs-toggle="modal"><span
                                                    class="badge badge-danger-light">{{ trans('inscription.noaccept') }}
                                                </span> </a>
                                        @endif
                                    </td>
                                    <td class="col-md-2">
                                        {{ $ins->classroom->schoolgrade->school->name_school }}
                                    </td>
                                    <td>{{ $ins->classroom->name_class }}</td>
                                    <td>{{ $ins->classroom->schoolgrade->name_grade }}</td>

                                    <td>
                                        <select id="section_id" class="form-select" name="section_id"
                                            onchange="console.log($(this).val())">
                                            <option value="" disabled selected>{{ trans('inscription.choisir') }}
                                            </option>
                                            @foreach ($ins->classroom->sections as $sc)
                                                <option value="{{ $sc->id }}">{{ $sc->name_section }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="col-md-3">
                                        {{-- <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a> --}}
                                        {{-- <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal" class="waves-effect waves-light btn btn-primary-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>  --}}
                                        <a data-bs-target=".modal-update{{ $ins->id }}" data-bs-toggle="modal"
                                            class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span
                                                class="icon-Write"><span class="path1"></span><span
                                                    class="path2"></span></span></a>
                                        <a data-bs-target="#modal-delete{{ $ins->id }}" data-bs-toggle="modal"
                                            class="waves-effect waves-light btn btn-danger-light btn-circle"><span
                                                class="icon-Trash1 fs-18"><span class="path1"></span><span
                                                    class="path2"></span></span></a>
                                        {{-- <a href="store/{{$ins->id}}" onclick="event.preventDefault();
                      document.getElementById('store-form{{ $ins->id}}').submit();" class="waves-effect waves-light btn btn-success-light btn-circle"><span class="fa fa-plus fs-18"><span class="path1"></span><span class="path2"></span></span></a>  --}}
                                        <a onclick="event.preventDefault();
  document.getElementById('store-form{{ $ins->id }}').submit();"
                                            class="waves-effect waves-light btn btn-success-light btn-circle"><span
                                                class="fa fa-plus fs-18"><span class="path1"></span><span
                                                    class="path2"></span></span></a>

                                    </td>
                                </tr>




                                <!-- store  student-->
                                <div class="modal center-modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <form id="store-form{{ $ins->id }}"
                                                    action="{{ route('Inscriptions.show', $ins->id) }}" method="POST">

                                                    {{ method_field('GET') }}
                                                    @csrf
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label
                                                                class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                                            <select id="section_id2" class="form-select"
                                                                name="section_id2">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Form -->
                                <div class="modal fade modal-update{{ $ins->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">

                                            <div class="modal-body">
                                                <form novalidate id="update-form{{ $ins->id }}"
                                                    action="{{ route('Inscriptions.update', $ins->id) }}"
                                                    method="POST">

                                                    {{ method_field('patch') }}
                                                    @csrf

                                                    <div class="box-body">

                                                        <h4 class="box-title text-info mb-0 mt-20"><i
                                                                class="ti-home me-15"></i>{{ trans('inscription.informationecole') }}
                                                        </h4>
                                                        <hr class="my-15">

                                                        <div class="row">

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.ecole') }}</label>
                                                                    <select id="school_id" class="form-select"
                                                                        name="school_id"
                                                                        onchange="console.log($(this).val())">
                                                                        <option
                                                                            value="{{ $ins->classroom->schoolgrade->school->id }}"
                                                                            selected>
                                                                            {{ $ins->classroom->schoolgrade->school->name_school }}
                                                                        </option>
                                                                        @foreach ($School as $sc)
                                                                            @if ($sc->id != $ins->classroom->schoolgrade->school->id)
                                                                                <option value="{{ $sc->id }}">
                                                                                    {{ $sc->name_school }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.niveau') }}</label>
                                                                    <select id="grade_id" class="form-select"
                                                                        name="grade_id"
                                                                        onchange="console.log($(this).val())">
                                                                        <option
                                                                            value="{{ $ins->classroom->schoolgrade->id }}"
                                                                            selected>
                                                                            {{ $ins->classroom->schoolgrade->name_grade }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.Anneescolaire') }}</label>
                                                                    <select id="classroom_id" class="form-select"
                                                                        name="classroom_id">
                                                                        <option value="{{ $ins->classroom->id }}"
                                                                            selected>{{ $ins->classroom->name_class }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>









                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.inscription') }}</label>
                                                                    <select class="form-select"
                                                                        name="inscriptionetat">
                                                                        @if ($ins->inscriptionetat == 'nouvelleinscription')
                                                                            <option value="nouvelleinscription"
                                                                                selected>{{ $ins->inscriptionetat }}
                                                                            </option>
                                                                            <option value="reenregistrement">
                                                                                {{ trans('inscription.reenregistrement') }}
                                                                            </option>
                                                                        @else
                                                                            <option value="nouvelleinscription">
                                                                                {{ trans('inscription.nouvelleinscription') }}
                                                                            </option>
                                                                            <option value="reenregistrement" selected>
                                                                                {{ $ins->inscriptionetat }}</option>
                                                                        @endif

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.nomecoleprecedente') }}</label>
                                                                    <input type="text" name="nomecoleprecedente"
                                                                        value="{{ $ins->nomecoleprecedente }}"
                                                                        class="form-control">
                                                                    <input type="hidden" value="{{ $ins->id }}"
                                                                        name="test" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.dernieresection') }}</label>
                                                                    <input type="text" name="dernieresection"
                                                                        value="{{ $ins->dernieresection }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.moyensannuels') }}</label>
                                                                    <input type="number" name="moyensannuels"
                                                                        value="{{ $ins->moyensannuels }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.numeronationaletudiant') }}</label>
                                                                    <input type="number"
                                                                        name="numeronationaletudiant"
                                                                        value="{{ $ins->numeronationaletudiant }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h4 class="box-title text-info mb-0"><i
                                                                class="ti-id-badge me-15"></i>
                                                            {{ trans('inscription.informationétudiant') }} </h4>
                                                        <hr class="my-15">

                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.prenomfr') }}</label>
                                                                    <input type="text" name="prenomfr"
                                                                        value="{{ $ins->getTranslation('prenom', 'fr') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.nomfr') }}</label>
                                                                    <input type="text" name="nomfr"
                                                                        value="{{ $ins->getTranslation('nom', 'fr') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.prenomar') }}</label>
                                                                    <input type="text" name="prenomar"
                                                                        value="{{ $ins->getTranslation('prenom', 'ar') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.nomar') }}</label>
                                                                    <input type="text" name="nomar"
                                                                        value="{{ $ins->getTranslation('nom', 'ar') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.email') }}</label>
                                                                    <input type="email" name="email"
                                                                        value="{{ $ins->email }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.gender') }}</label>
                                                                    <select class="form-select" name="gender"
                                                                        required>
                                                                        @if ($ins->gender == 1)
                                                                            <option selected
                                                                                value="{{ 1 }}">
                                                                                {{ trans('inscription.male') }}
                                                                            </option>
                                                                            <option value="{{ 0 }}">
                                                                                {{ trans('inscription.female') }}
                                                                            </option>
                                                                        @else
                                                                            <option value="{{ 1 }}">
                                                                                {{ trans('inscription.male') }}
                                                                            </option>
                                                                            <option selected
                                                                                value="{{ 0 }}">
                                                                                {{ trans('inscription.female') }}
                                                                            </option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.numtelephone') }}</label>
                                                                    <input type="number" name="numtelephone"
                                                                        value="{{ $ins->numtelephone }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.datenaissance') }}</label>
                                                                    <input type="date" name="datenaissance"
                                                                        value="{{ $ins->datenaissance }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.lieunaissance') }}</label>
                                                                    <input type="text" name="lieunaissance"
                                                                        value="{{ $ins->lieunaissance }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.wilaya') }}</label>
                                                                    <input type="text" name="wilaya"
                                                                        value="{{ $ins->wilaya }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.dayra') }}</label>
                                                                    <input type="text" name="dayra"
                                                                        value="{{ $ins->dayra }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.baladia') }}</label>
                                                                    <input type="text" name="baladia"
                                                                        value="{{ $ins->baladia }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.adresseactuelle') }}</label>
                                                                    <input type="text" name="adresseactuelle"
                                                                        value="{{ $ins->adresseactuelle }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.codepostal') }}</label>
                                                                    <input type="number" name="codepostal"
                                                                        value="{{ $ins->codepostal }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.residenceactuelle') }}</label>
                                                                    <select class="form-select"
                                                                        name="residenceactuelle">
                                                                        @if ($ins->residenceactuelle == 'parents')
                                                                            <option value="parents" selected>
                                                                                {{ trans('inscription.parents') }}
                                                                            </option>
                                                                            <option value="pere">
                                                                                {{ trans('inscription.pere') }}
                                                                            </option>
                                                                            <option value="mere">
                                                                                {{ trans('inscription.mere') }}
                                                                            </option>
                                                                            <option value="autrepersonne">
                                                                                {{ trans('inscription.autrepersonne') }}
                                                                            </option>
                                                                        @endif
                                                                        @if ($ins->residenceactuelle == 'pere')
                                                                            <option value="parents">
                                                                                {{ trans('inscription.parents') }}
                                                                            </option>
                                                                            <option value="pere" selected>
                                                                                {{ trans('inscription.pere') }}
                                                                            </option>
                                                                            <option value="mere">
                                                                                {{ trans('inscription.mere') }}
                                                                            </option>
                                                                            <option value="autrepersonne">
                                                                                {{ trans('inscription.autrepersonne') }}
                                                                            </option>
                                                                        @endif
                                                                        @if ($ins->residenceactuelle == 'mere')
                                                                            <option value="parents">
                                                                                {{ trans('inscription.parents') }}
                                                                            </option>
                                                                            <option value="pere">
                                                                                {{ trans('inscription.pere') }}
                                                                            </option>
                                                                            <option value="mere" selected>
                                                                                {{ trans('inscription.mere') }}
                                                                            </option>
                                                                            <option value="autrepersonne">
                                                                                {{ trans('inscription.autrepersonne') }}
                                                                            </option>
                                                                        @endif
                                                                        @if ($ins->residenceactuelle == 'autrepersonne')
                                                                            <option value="parents">
                                                                                {{ trans('inscription.parents') }}
                                                                            </option>
                                                                            <option value="pere">
                                                                                {{ trans('inscription.pere') }}
                                                                            </option>
                                                                            <option value="mere">
                                                                                {{ trans('inscription.mere') }}
                                                                            </option>
                                                                            <option value="autrepersonne" selected>
                                                                                {{ trans('inscription.autrepersonne') }}
                                                                            </option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.etatsante') }}</label>
                                                                    <select class="form-select" name="etatsante">
                                                                        @if ($ins->etatsante == 'bien')
                                                                            <option value="bien" selected>
                                                                                {{ trans('inscription.bien') }}
                                                                            </option>
                                                                            <option value="maladiechronique">
                                                                                {{ trans('inscription.maladiechronique') }}
                                                                            </option>
                                                                        @else
                                                                            <option value="bien">
                                                                                {{ trans('inscription.bien') }}
                                                                            </option>
                                                                            <option value="maladiechronique" selected>
                                                                                {{ trans('inscription.maladiechronique') }}
                                                                            </option>
                                                                        @endif
                                                                    </select>
                                                                </div>

                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.identificationmaladie') }}</label>
                                                                    <input type="text" name="identificationmaladie"
                                                                        value="{{ $ins->identificationmaladie }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-8">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.alfdlprsaldr') }}</label>
                                                                    <select class="form-select" name="alfdlprsaldr">
                                                                        @if ($ins->alfdlprsaldr == 'oui')
                                                                            <option value="oui" selected>
                                                                                {{ trans('inscription.oui') }}
                                                                            </option>
                                                                            <option value="no">
                                                                                {{ trans('inscription.no') }}</option>
                                                                        @else
                                                                            <option value="oui">
                                                                                {{ trans('inscription.oui') }}
                                                                            </option>
                                                                            <option value="no" selected>
                                                                                {{ trans('inscription.no') }}</option>
                                                                        @endif

                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.autresnotes') }}</label>
                                                                    <textarea rows="4" name="autresnotes" class="form-control">{{ $ins->autresnotes }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h4 class="box-title text-info mb-0 mt-20"><i
                                                                class="ti-user me-15"></i>
                                                            {{ trans('inscription.informationwali') }}</h4>
                                                        <hr class="my-15">

                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.prenomfr') }}</label>
                                                                    <input type="text" name="prenomfrwali"
                                                                        value="{{ $ins->getTranslation('prenomwali', 'fr') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.nomfr') }}</label>
                                                                    <input type="text" name="nomfrwali"
                                                                        value="{{ $ins->getTranslation('nomwali', 'fr') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.prenomar') }}</label>
                                                                    <input type="text" name="prenomarwali"
                                                                        value="{{ $ins->getTranslation('prenomwali', 'ar') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.nomar') }}</label>
                                                                    <input type="text" name="nomarwali"
                                                                        value="{{ $ins->getTranslation('nomwali', 'ar') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.relationetudiant') }}</label>
                                                                    <input type="text" name="relationetudiant"
                                                                        value="{{ $ins->relationetudiant }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.adresse') }}</label>
                                                                    <input type="text" name="adressewali"
                                                                        value="{{ $ins->adressewali }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.numtelephone') }}</label>
                                                                    <input type="number" name="numtelephonewali"
                                                                        value="{{ $ins->numtelephonewali }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.email') }}</label>
                                                                    <input type="email" name="emailwali"
                                                                        value="{{ $ins->emailwali }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.wilaya') }}</label>
                                                                    <input type="text" name="wilayawali"
                                                                        value="{{ $ins->wilayawali }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.dayra') }}</label>
                                                                    <input type="text" name="dayrawali"
                                                                        value="{{ $ins->dayrawali }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.baladia') }}</label>
                                                                    <input type="text" name="baladiawali"
                                                                        value="{{ $ins->baladiawali }}"
                                                                        class="form-control" required>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">

                                                                <div class="form-group">
                                                                    <label
                                                                        class="form-label">{{ trans('inscription.status') }}</label>

                                                                    <select class="form-select" name="statu">
                                                                        <option value="{{ $ins->statu }}" selected
                                                                            disabled>
                                                                            {{ trans('inscription.choisir') }}
                                                                        </option>

                                                                        @if ($ins->statu == 'accept')
                                                                            <option value="accept" selected>
                                                                                {{ trans('inscription.accept') }}
                                                                            </option>
                                                                            <option value="noaccept">
                                                                                {{ trans('inscription.noaccept') }}
                                                                            </option>
                                                                            <option value="procec">
                                                                                {{ trans('inscription.undefined') }}
                                                                            </option>
                                                                        @endif
                                                                        @if ($ins->statu == 'noaccept')
                                                                            <option value="accept">
                                                                                {{ trans('inscription.accept') }}
                                                                            </option>
                                                                            <option value="noaccept" selected>
                                                                                {{ trans('inscription.noaccept') }}
                                                                            </option>
                                                                            <option value="procec">
                                                                                {{ trans('inscription.undefined') }}
                                                                            </option>
                                                                        @endif
                                                                        @if ($ins->statu == 'procec')
                                                                            <option value="accept">
                                                                                {{ trans('inscription.accept') }}
                                                                            </option>
                                                                            <option value="noaccept">
                                                                                {{ trans('inscription.noaccept') }}
                                                                            </option>
                                                                            <option value="procec" selected>
                                                                                {{ trans('inscription.undefined') }}
                                                                            </option>
                                                                        @endif

                                                                    </select>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>

                                                </form>
                                            </div>
                                            <div class="modal-footer modal-footer-uniform">
                                                <a type="button" class="btn btn-danger"
                                                    data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                                <a type="submit" class="btn btn-primary float-end"
                                                    onclick="event.preventDefault();
       document.getElementById('update-form{{ $ins->id }}').submit();">{{ trans('opt.update2') }}</a>

                                            </div>
                                        </div>
                                    </div>
                                </div>








                                <!-- Delete -->





                                <div class="modal center-modal fade" id="modal-delete{{ $ins->id }}"
                                    tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <div class="modal-body">
                                                <form id="delete-form{{ $ins->id }}"
                                                    action="{{ route('Inscriptions.destroy', $ins->id) }}"
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
      document.getElementById('delete-form{{ $ins->id }}').submit();">{{ trans('opt.delete2') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                                <!-- update  status-->
                                <div class="modal center-modal fade" id="modal-status{{ $ins->id }}"
                                    tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <form id="status-form{{ $ins->id }}"
                                                    action="{{ route('Inscriptions.edit', $ins->id) }}"
                                                    method="POST">

                                                    {{ method_field('GET') }}
                                                    @csrf
                                                    <div class="box-body">
                                                        <div class="row">
                                                            <div class="form-group">
                                                                <label
                                                                    class="form-label">{{ trans('inscription.status') }}</label>

                                                                <select id="status" class="form-select"
                                                                    name="statu">
                                                                    <option value="" selected disabled>
                                                                        {{ trans('inscription.choisir') }}</option>
                                                                    <option value="accept">
                                                                        {{ trans('inscription.accept') }}</option>
                                                                    <option value="noaccept">
                                                                        {{ trans('inscription.noaccept') }}</option>
                                                                    <option value="procec">
                                                                        {{ trans('inscription.undefined') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer modal-footer-uniform">
                                                <a type="button" class="btn btn-danger"
                                                    data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                                                <a type="button" class="btn btn-primary float-end"
                                                    onclick="event.preventDefault();
      document.getElementById('status-form{{ $ins->id }}').submit();">{{ trans('opt.save') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                <th> </th>
                                <th>{{ trans('inscription.student') }}</th>
                                <th>{{ trans('inscription.status') }}</th>
                                <th>{{ trans('inscription.ecole') }}</th>
                                <th>{{ trans('inscription.Anneescolaire') }}</th>
                                <th>{{ trans('inscription.niveau') }}</th>
                                <th>{{ trans('inscription.niveau') }}</th>
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
<!-- Delete  All-->
<div class="modal center-modal fade" id="delete_all" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

                <form id="delete_all2" action="{{ route('delete_all') }}" method="GET">
                    {{ method_field('GET') }}
                    @csrf
                    <div class="modal-body">
                        <h1>{{ trans('opt.deletemsg') }}</h1>
                        <input class="text" type="hidden" id="delete_all_id" name="delete_all_id"
                            value=''>
                    </div>

                </form>
            </div>
            <div class="modal-footer modal-footer-uniform">
                <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
                <a type="button" class="btn btn-primary float-end"
                    onclick="event.preventDefault();
      document.getElementById('delete_all2').submit();">{{ trans('opt.save') }}</a>
            </div>
        </div>
    </div>
</div>






@endsection


@section('jsa')

<script>
    $(function() {
        $("#btn_delete_all").click(function() {
            var selected = new Array();
            $("#example5 input[type=checkbox]:checked").each(function() {
                selected.push(this.value);
            });

            if (selected.length > 0) {
                $('#delete_all').modal('show')
                $('input[id="delete_all_id"]').val(selected);
            }
        });
    });
</script>

<script src="{{ asset('assets/vendor_components/datatable/datatables.min.js') }}"></script>

<script>
    $(function() {
        "use strict";

        $('#example1').DataTable();
        $('#example2').DataTable({
            'paging': true,
            'lengthChange': false,
            'searching': false,
            'ordering': true,
            'info': true,
            'autoWidth': false
        });


        $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        $('#tickets').DataTable({
            'paging': true,
            'lengthChange': true,
            'searching': true,
            'ordering': true,
            'info': true,
            'autoWidth': false,
        });

        $('#productorder').DataTable({
            'paging': true,
            'lengthChange': true,
            'searching': true,
            'ordering': true,
            'info': true,
            'autoWidth': false,
        });


        $('#complex_header').DataTable();





        // Setup - add a text input to each footer cell
        $('#example5 tfoot th').each(function() {

            var title = $(this).text();
            $(this).html('<input type="text" placeholder=" ' + title + '" />');

        });
        // DataTable
        var table = $('#example5').DataTable({
            "pageLength": 100,
            "language": {
                "paginate": {
                    "next": "{{ trans('inscription.next') }}",
                    "last": "Last page",
                    "previous": "{{ trans('inscription.Previous') }}",
                },
                "info": "{{ trans('inscription.show') }} _START_ {{ trans('inscription.to') }} _END_ {{ trans('inscription.of') }} _TOTAL_ {{ trans('inscription.entries') }}",
                "infoEmpty": "{{ trans('inscription.noentries') }}",
                "emptyTable": "{{ trans('inscription.nodata') }}",
                "search": "{{ trans('inscription.search') }}",
                "infoFiltered": " - {{ trans('inscription.filteringfrom') }} _MAX_ {{ trans('inscription.records') }}",
                "lengthMenu": "{{ trans('inscription.show') }} _MENU_ {{ trans('inscription.records') }}",
                "zeroRecords": "{{ trans('inscription.norecords') }}",


            }
        });

        // Apply the search
        table.columns().every(function() {
            var that = this;

            $('input', this.footer()).on('keyup change', function() {
                if (that.search() !== this.value) {
                    that
                        .search(this.value)
                        .draw();
                }

            });

        });






        for (let i = 0; i < 10; i++) {

            // Setup - add a text input to each footer cell
            $('#example5' + i + ' tfoot th').each(function() {

                var title = $(this).text();
                $(this).html('<input type="text" placeholder=" ' + title + '" />');

            });
            // DataTable
            var table = $('#example5' + i + '').DataTable({
                "language": {
                    "paginate": {
                        "next": "{{ trans('inscription.next') }}",
                        "last": "Last page",
                        "previous": "{{ trans('inscription.Previous') }}",
                    },
                    "info": "{{ trans('inscription.show') }} _START_ {{ trans('inscription.to') }} _END_ {{ trans('inscription.of') }} _TOTAL_ {{ trans('inscription.entries') }}",
                    "infoEmpty": "{{ trans('inscription.noentries') }}",
                    "emptyTable": "{{ trans('inscription.nodata') }}",
                    "search": "{{ trans('inscription.search') }}",
                    "infoFiltered": " - {{ trans('inscription.filteringfrom') }} _MAX_ {{ trans('inscription.records') }}",
                    "lengthMenu": "{{ trans('inscription.show') }} _MENU_ {{ trans('inscription.records') }}",
                    "zeroRecords": "{{ trans('inscription.norecords') }}"

                }
            });

            // Apply the search
            table.columns().every(function() {
                var that = this;

                $('input', this.footer()).on('keyup change', function() {
                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }

                });

            });

        }





        //---------------Form inputs
        var table = $('#example6').DataTable();

        $('button').click(function() {
            var data = table.$('input, select').serialize();
            alert(
                "The following data would have been submitted to the server: \n\n" +
                data.substr(0, 120) + '...'
            );
            return false;
        });




    }); // End of use strict
</script>

@include('layoutsadmin.DeleteCheckbox')
@endsection
