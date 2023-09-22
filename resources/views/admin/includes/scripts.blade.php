<!-- jQuery -->
<script src="{{ asset('assets/admin/js/jquery-3.1.1.min.js') }}"></script>
<!-- Bootstrap Core JavaScript -->
<script src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/admin/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ asset('assets/admin/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>
<script src="{{ asset('assets/admin/js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
<!-- Image cropper -->
<script src="{{ asset('assets/admin/js/plugins/cropper/cropper.min.js') }}"></script>
<!-- Date range use moment.js same as full calendar plugin -->
<script src="{{ asset('assets/admin/js/plugins/fullcalendar/moment.min.js') }}"></script>
<!-- Date range picker -->
<script src="{{ asset('assets/admin/js/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/admin/js/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/inspinia.js') }}"></script>
<script src="{{ asset('assets/admin/js/plugins/pace/pace.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js') }}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.js"></script>
<!-- Tags Input -->
<script src="{{ asset('assets/admin/js/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
<!-- Dual Listbox -->
<script src="{{ asset('assets/admin/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
<script type="text/javascript" src="http://demos.codexworld.com/multi-select-dropdown-list-with-checkbox-jquery/multiselect/jquery.multiselect.js">
</script>
<script src="{{asset('assets/admin/js/intlTelInput.js')}}"  type="text/javascript"></script>
<script>
	$('.i-checks').iCheck({
		checkboxClass: 'icheckbox_square-green',
		radioClass: 'iradio_square-green'
	});
	$('input[name="daterange"]').daterangepicker();
	$('#reportrange span').html(moment().subtract(29, 'days').format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
	$('#reportrange').daterangepicker({
		format: 'DD/MM/YYYY',
		startDate: moment().subtract(29, 'days'),
		endDate: moment(),
		minDate: '01/01/2012',
		maxDate: '12/31/2015',
		dateLimit: { days: 60 },
		showDropdowns: true,
		showWeekNumbers: true,
		timePicker: false,
		timePickerIncrement: 1,
		timePicker12Hour: true,
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		opens: 'right',
		drops: 'down',
		buttonClasses: ['btn', 'btn-sm'],
		applyClass: 'btn-primary',
		cancelClass: 'btn-default',
		separator: ' to ',
		locale: {
			applyLabel: 'Submit',
			cancelLabel: 'Cancel',
			fromLabel: 'From',
			toLabel: 'To',
			customRangeLabel: 'Custom',
			daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
			monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			firstDay: 1
		}
	}, function(start, end, label) {
		console.log(start.toISOString(), end.toISOString(), label);
		$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	});
	$(".select2_demo_1").select2();
	$(".select2_demo_2").select2();
	$(".select2_demo_3").select2({
		placeholder: "Select a state",
		allowClear: true
	});
	$('.dual_select').bootstrapDualListbox({
		selectorMinimalHeight: 160
	});
	$(function() {
		$(".preload").fadeOut(10,function() {
			$(".car_data").fadeIn(10);
		});
	});
</script>
{!! Notify::render() !!}
@yield('scripts')
