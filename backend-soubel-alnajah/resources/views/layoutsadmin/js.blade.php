	<!-- Vendor JS -->
   <script src="{{ asset('jsadmin/vendors.min.js')}}"></script>
	<script src="{{ asset('jsadmin/pages/chat-popup.js')}}"></script>
    <script src="{{ asset('assets/icons/feather-icons/feather.min.js')}}"></script>

	<script src="{{ asset('assets/vendor_components/apexcharts-bundle/dist/apexcharts.js')}}"></script>
	<script src="{{ asset('assets/vendor_components/moment/min/moment.min.js')}}"></script>
	<script src="{{ asset('assets/vendor_components/fullcalendar/fullcalendar.js')}}"></script>
	
	<!-- EduAdmin App -->
	<script src="{{ asset('jsadmin/template.js')}}"></script>
	<script src="{{ asset('jsadmin/pages/dashboard.js')}}"></script>
	<script src="{{ asset('jsadmin/pages/calendar.js')}}"></script>

	@jquery
    @toastr_js
    @toastr_render
	

<script>
(function () {
    const STORAGE_KEY = 'eduadmin-theme-mode';
    const body = document.body;
    const toggle = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-toggle-icon');

    const applyTheme = (mode) => {
        if (!body) return;
        if (mode === 'dark') {
            body.classList.add('dark-skin');
            body.classList.remove('light-skin');
            if (icon) {
                icon.classList.remove('mdi-weather-night');
                icon.classList.add('mdi-white-balance-sunny');
            }
        } else {
            body.classList.remove('dark-skin');
            body.classList.add('light-skin');
            if (icon) {
                icon.classList.remove('mdi-white-balance-sunny');
                icon.classList.add('mdi-weather-night');
            }
        }
    };

    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark' || stored === 'light') {
        applyTheme(stored);
    } else if (body.classList.contains('dark-skin')) {
        applyTheme('dark');
    } else {
        applyTheme('light');
    }

    if (toggle) {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            const nextMode = body.classList.contains('dark-skin') ? 'light' : 'dark';
            applyTheme(nextMode);
            localStorage.setItem(STORAGE_KEY, nextMode);
        });
    }
})();
</script>


@yield('jsa')


<script>
	$(document).ready(function () {
		$('select[name="school_id"]').on('change', function () {
			var school_id = $(this).val();
			if (school_id) {
				$.ajax({
					url: "{{ URL::to('getgrade') }}/" + school_id,
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
					url: "{{ URL::to('getclasse') }}/" + grade_id,
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
<script>
	$(document).ready(function () {
		$('select[name="classroom_id"]').on('change', function () {
			var classroom_id = $(this).val();
			if (classroom_id) {
				$.ajax({
					url: "{{ URL::to('getsection') }}/" + classroom_id,
					type: "GET",
					dataType: "json",
					success: function (data) {
						$('select[name="section_id"]').empty();
						$('select[name="section_id"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
						$.each(data, function (key, value) {
							$('select[name="section_id"]').append('<option value="' + key + '">' + value + '</option>');
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
		$('select[name="section_id"]').on('change', function () {
			var section_id = $(this).val();
			if (section_id) {
				$.ajax({
					url: "{{ URL::to('getsection2') }}/" + section_id,
					type: "GET",
					dataType: "json",
					success: function (data) {
						$('select[name="section_id2"]').empty();
						$.each(data, function (key, value) {
							$('select[name="section_id2"]').append('<option value="' + key + '" selected>' + value + '</option>');
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
		$('select[name="school_id_new"]').on('change', function () {
			var school_id_new = $(this).val();
			if (school_id_new) {
				$.ajax({
					url: "{{ URL::to('getgrade') }}/" + school_id_new,
					type: "GET",
					dataType: "json",
					success: function (data) {
						$('select[name="grade_id_new"]').empty();
						$('select[name="grade_id_new"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
						$.each(data, function (key, value) {
							$('select[name="grade_id_new"]').append('<option value="' + key + '">' + value + '</option>');
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
		$('select[name="grade_id_new"]').on('change', function () {
			var grade_id_new = $(this).val();
			if (grade_id_new) {
				$.ajax({
					url: "{{ URL::to('getclasse') }}/" + grade_id_new,
					type: "GET",
					dataType: "json",
					success: function (data) {
						$('select[name="classroom_id_new"]').empty();
						$('select[name="classroom_id_new"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
						$.each(data, function (key, value) {
							$('select[name="classroom_id_new"]').append('<option value="' + key + '">' + value + '</option>');
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
		$('select[name="classroom_id_new"]').on('change', function () {
			var classroom_id_new = $(this).val();
			if (classroom_id_new) {
				$.ajax({
					url: "{{ URL::to('getsection') }}/" + classroom_id_new,
					type: "GET",
					dataType: "json",
					success: function (data) {
						$('select[name="section_id_new"]').empty();
						$('select[name="section_id_new"]').append('<option value="" selected disabled>{{ trans('inscription.choisir') }}</option>');
						$.each(data, function (key, value) {
							$('select[name="section_id_new"]').append('<option value="' + key + '">' + value + '</option>');
						});
					},
				});
			} else {
				console.log('AJAX load did not work');
			}
		});
	});

</script>


