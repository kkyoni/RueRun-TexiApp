<script src="{{ asset('assets/admin/js/jquery-3.1.1.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.js') }}"></script>

<!-- particle js -->
<script src='https://rawgithub.com/soulwire/sketch.js/v1.0/js/sketch.min.js'></script>
<script src="{{ asset('assets/particles/script.js') }}"></script>
<script src="{{asset('assets/admin/js/intlTelInput.js')}}"  type="text/javascript"></script>
<!-- particle js -->

{!! Notify::render() !!}
@yield('authScripts')