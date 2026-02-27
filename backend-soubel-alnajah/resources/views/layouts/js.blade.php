@livewireScripts

<!-- Vendor JS -->
<script src="{{ asset('js/vendors.min.js')}}"></script>
<!-- Corenav Master JavaScript -->
<script src="{{ asset('corenav-master/coreNavigation-1.1.3.js')}}"></script>
<script src="{{ asset('js/nav.js')}}"></script>
<script src="{{ asset('assets/vendor_components/OwlCarousel2/dist/owl.carousel.js')}}"></script>

<script src="{{ asset('assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js')}}"></script>
@yield('js')
<!-- EduAdmin front end script -->
<script src="{{ asset('js/template.js')}}"></script>
<script src="{{ asset('js/pages/widget.js')}}"></script>

<!-- EduAdmin Admin script -->
<script src="{{ asset('jsadmin/pages/chat-popup.js')}}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js')}}"></script>

<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{ asset('assets/vendor_components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>

<script>
// Remove legacy cookie consent banner if any script injects it.
document.addEventListener('DOMContentLoaded', function () {
    var banner = document.getElementById('gdpr-cookie-message');
    if (banner) {
        banner.remove();
    }
});
</script>
