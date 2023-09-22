<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>{{Settings::get('application_title')}} - @yield('title')</title>
	<link href="{{ asset('assets/admin/css/bootstrap.min.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" type="text/css" media="all">
	<link href="{{ asset('assets/admin/font-awesome/css/font-awesome.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/morris/morris-0.4.3.min.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/animate.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/custom.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/style.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
	<link href="{{ asset('assets/admin/css/plugins/datapicker/datepicker3.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/select2/select2.min.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/daterangepicker/daterangepicker-bs3.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/iCheck/custom.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css')}}" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.css.map">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.js">
	<link href="{{ asset('assets/admin/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet">
	<link href="{{ asset('assets/admin/css/plugins/dualListbox/bootstrap-duallistbox.min.css')}}" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css" rel="stylesheet"/>
	<link rel="icon" href="{{asset(\Settings::get('application_logo'))}}" type="image/gif" sizes="16x16">
	<link href="http://demos.codexworld.com/multi-select-dropdown-list-with-checkbox-jquery/multiselect/jquery.multiselect.css" rel="stylesheet"/>
	<link href="{{ asset('css/star-rating.css')}}" rel="stylesheet">
	<link href="{{asset('assets/admin/css/intlTelInput.css')}}" rel="stylesheet" type="text/css" media="all" />
	<style>
		.nav-label{font-size: 12px;}
		table tfoot{display: none;}
		.bars{background-color: #2c4292 !important; border-color: #2c4292 !important; color: #FFF;}
		.bars:hover{background-color: #ffb014 !important; border-color: #ffb014 !important; color: #FFF;}
		.table-bordered > thead > tr > th, .table-bordered > thead > tr > td{background-color:#FFF;}
		.abcdhhh{color:#000; font-size: 12px !important; display: contents;}
		.min{font-size: 10px;}
		.btn-primary{background: #2c4292; border-color: #2c4292;}
		.btn-primary:hover, .btn-primary:focus, .btn-primary.focus{background: #ffb014;border-color:#ffb014;}
		body{background-color:#2c4292 !important;}
		.nav-header{background-color:#2c4292 !important;}
		ul.nav-second-level{background-color:#2c4292 !important;}
	</style>
	@yield('styles')
</head>
