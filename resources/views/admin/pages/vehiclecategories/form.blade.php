<div class="form-group" id="imagePreview">
	@if(!empty($vehiclecategories->vehicle_image))
        @if (file_exists( "storage/vehicle_images/".@$vehiclecategories->vehicle_image))
            <img src="{!!  @$vehiclecategories->vehicle_image !== '' ? url("storage/vehicle_images/".@$vehiclecategories->vehicle_image) : url('storage/vehicle_images/default.png') !!}" alt="vehicle-img" class="img-circle">
        @else
            <img src="{!! url('storage/vehicle_images/default.png') !!}" name="vehicle_image" alt="vehicle-img" class="img-circle" accept="image/*">
        @endif
	@else
        <img src="{!! url('storage/vehicle_images/default.png') !!}" name="vehicle_image" alt="vehicle-img" class="img-circle" accept="image/*">
	@endif

</div>
{!! Form::file('vehicle_image',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic']) !!}
<br>
<span >
	<center><font color="red"> {{ $errors->has('vehicle_image') ? "".$errors->first('vehicle_image')."" : '' }} </font></center>
</span>
<br>
<div class="form-group  row {{ $errors->has('name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('name',null,[
		'class' => 'form-control',
		'id'	=> 'name',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('name') ? "".$errors->first('name')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_type_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Type</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">

		{{ Form::select('vehicle_type_id',$car_type,null,[
		'class'         => 'form-control',
		'id'            => 'vehicle_type_id',
		'placeholder'   => 'Please select vehicle type'
		])}}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_type_id') ? "".$errors->first('vehicle_type_id')."" : '' }} </font>
		</span>
	</div>
</div>
<!-- <div class="form-group row {{ $errors->has('vehicle_type') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Type</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		@php($vehicle_type=array('car'=>'car','auto'=>'auto','bike'=>'bike'))
		{!! Form::select('vehicle_type',$vehicle_type,null,[
             'class'         => 'form-control',
             'id'            => 'vehicle_type',
             'placeholder'   => 'Please select vehicle type'
     	]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_type') ? "".$errors->first('vehicle_type')."" : '' }} </font>
		</span>
	</div>
</div> -->

<div class="form-group  row {{ $errors->has('base_fare') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Base Fare</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::number('base_fare',null,[
		'class' => 'form-control',
		'id'	=> 'base_fare','min'=>'1','max'=>'100'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('base_fare') ? "".$errors->first('base_fare')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group row {{ $errors->has('price_per_km') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Price Per Km</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::number('price_per_km',null,[
		'class' => 'form-control',
		'id'	=> 'price_per_km','min'=>'1','max'=>'100'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('price_per_km') ? "".$errors->first('price_per_km')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('extra_cost_dropdown') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Extra Cost</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!!
		Form::select('extra_cost_dropdown',['1'=>'1X','1.5'=>'1.5x','2'=>'2X','2.5'=>'2.5X'],null,[
		'class'         => 'form-control',
		'id'            => 'extra_cost_dropdown',
		'placeholder'   => 'Enter Extra Cost'
		])
		!!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('extra_cost_dropdown') ? "".$errors->first('extra_cost_dropdown')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('extra_cost_include') ? 'has-error' : '' }}" id="data_1"><label class="col-sm-3 col-form-label"><strong>Extra Cost Include</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6 inline-block">
		<div class="i-checks">
			<label>
				{{ Form::radio('extra_cost_include', 'yes',true,['id'=> 'yes']) }} <i></i> Yes
			</label>
			&nbsp;
			<label>
				{{ Form::radio('extra_cost_include', 'no',false,['id'=> 'no']) }} <i></i> No
			</label>
		</div>

		<span class="help-block">
			<font color="red">  {{ $errors->has('extra_cost_include') ? "".$errors->first('extra_cost_include')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong> <span class="text-danger">*</span></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">

		<div class="i-checks">
			<label>
				{{ Form::radio('status', 'active',true,['id'=> 'active']) }} <i></i> Active
			</label>
			&nbsp;
			<label>
				{{ Form::radio('status', 'inactive',false,['id' => 'inactive']) }}
				<i></i> Inactive
			</label>
		</div>

		<span class="help-block">
			<font color="red">  {{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('cancellation_time_in_minutes') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Cancellation Time In Minutes</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::number('cancellation_time_in_minutes',null,[
		'class' => 'form-control',
		'id'	=> 'cancellation_time_in_minutes','min'=>'1','max'=>'100'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('cancellation_time_in_minutes') ? "".$errors->first('cancellation_time_in_minutes')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('cancellation_charge_in_per') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Cancellation Charge In Per Minutes</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::number('cancellation_charge_in_per',null,[
		'class' => 'form-control',
		'id'	=> 'cancellation_charge_in_per','min'=>'1','max'=>'100'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('cancellation_charge_in_per') ? "".$errors->first('cancellation_charge_in_per')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('total_seat') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Total Seats</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::number('total_seat',null,[
		'class' => 'form-control',
		'id'	=> 'total_seat', 'min'=>'1', 'max'=>'60'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('total_seat') ? "".$errors->first('total_seat')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('wheel_type') ? 'has-error' : '' }}">
	<label class="col-sm-3 col-form-label"><strong>Wheel Type</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!!
		Form::select('wheel_type',['4'=>'4','6'=>'6'],null,[
		'class'         => 'form-control',
		'id'            => 'wheel_type',
		'placeholder'   => 'Select Wheel Type'
		])
		!!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('wheel_type') ? "".$errors->first('wheel_type')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group  row {{ $errors->has('ranking') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Ranking</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::number('ranking',null,[
		'class' => 'form-control',
		'id'	=> 'ranking',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('ranking') ? "".$errors->first('ranking')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="hr-line-dashed"></div>
@section('styles')
<style type="text/css">
	.help-block {
		display: inline-block;
		margin-top: 5px;
		margin-bottom: 0px;
		margin-left: 5px;
	}
	.form-group {
		margin-bottom: 10px;
	}
	.form-control {
		font-size: 14px;
		font-weight: 500;
	}
	#imagePreview {
		width: 135px;
		height: 100%;
		text-align: center;
		margin: 0 auto;
		position: relative;
	}
	#hidden{
		display: none !important;
	}
	#imagePreview img {
		height: 150px;
		width: 150px;
		border: 3px solid rgba(0,0,0,0.4);
		padding: 3px;
	}
	#imagePreview i{
		position: absolute;
		right: 0px;
		background: rgba(0,0,0,0.5);
		padding: 5px;
		border-radius: 50%;
		width: 30px;
		height: 30px;
		color: #fff;
		font-size: 18px;
	}
</style>
@endsection
@section('scripts')
<script type="text/javascript">
	function readURL(input) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('#imagePreview img').attr('src', e.target.result);
			}
			reader.readAsDataURL(input.files[0]);
		}
	}
	$('#imagePreview img').on('click',function(){
		$('input[type="file"]').trigger('click');
		$('input[type="file"]').change(function() {
			readURL(this);
		});
	});
</script>
<script>
	$(document).ready(function(){

		$('.tagsinput').tagsinput({
			tagClass: 'label label-primary'
		});

		var $image = $(".image-crop > img")
		$($image).cropper({
			aspectRatio: 1.618,
			preview: ".img-preview",
			done: function(data) {
                    // Output the result data for cropping image.
                }
            });

		var $inputImage = $("#inputImage");
		if (window.FileReader) {
			$inputImage.change(function() {
				var fileReader = new FileReader(),
				files = this.files,
				file;

				if (!files.length) {
					return;
				}

				file = files[0];

				if (/^image\/\w+$/.test(file.type)) {
					fileReader.readAsDataURL(file);
					fileReader.onload = function () {
						$inputImage.val("");
						$image.cropper("reset", true).cropper("replace", this.result);
					};
				} else {
					showMessage("Please choose an image file.");
				}
			});
		} else {
			$inputImage.addClass("hide");
		}

		$("#download").click(function() {
			window.open($image.cropper("getDataURL"));
		});

		$("#zoomIn").click(function() {
			$image.cropper("zoom", 0.1);
		});

		$("#zoomOut").click(function() {
			$image.cropper("zoom", -0.1);
		});

		$("#rotateLeft").click(function() {
			$image.cropper("rotate", 45);
		});

		$("#rotateRight").click(function() {
			$image.cropper("rotate", -45);
		});

		$("#setDrag").click(function() {
			$image.cropper("setDragMode", "crop");
		});
		var dateToday = new Date();
		var mem = $('#data_1 .input-group.date').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			minDate: dateToday,
			startDate: new Date(),
			onSelect: function(selectedDate) {
				var option = this.id == "from" ? "minDate" : "maxDate",
				instance = $(this).data("datepicker"),
				date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
				dates.not(this).datepicker("option", option, date);
			}
		});

		var mem = $('#data_2 .input-group.date').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			minDate: dateToday,
			startDate: new Date(),
			onSelect: function(selectedDate) {
				var option = this.id == "from" ? "minDate" : "maxDate",
				instance = $(this).data("datepicker"),
				date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
				dates.not(this).datepicker("option", option, date);
			}
		});


		var yearsAgo = new Date();
		yearsAgo.setFullYear(yearsAgo.getFullYear() - 20);

		$('#selector').datepicker('setDate', yearsAgo );


		$('#data_1 .input-group.date').datepicker({
			startView: 1,
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true,
			format: "dd/mm/yyyy"
		});

		$('#data_3 .input-group.date').datepicker({
			startView: 2,
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true
		});

		$('#data_4 .input-group.date').datepicker({
			minViewMode: 1,
			keyboardNavigation: false,
			forceParse: false,
			forceParse: false,
			autoclose: true,
			todayHighlight: true
		});

		$('#data_5 .input-daterange').datepicker({
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true
		});

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

		$('#name').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[a-zA-Z ._\\b]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
		});

		$('#base_fare, #price_per_km, #cancellation_time_in_minutes, #total_seat, #cancellation_charge_in_per').on('keyup onmouseout keydown keypress blur change', function (e) {
			var key = e.charCode || e.keyCode || 0;
			if(($(this).val().length > 100) || ($(this).val() > 100)){
				$(this).val('');
				return false;
			}
			return (
				key == 8 ||
				key == 9 ||
				key == 13 ||
				key == 46 ||
				key == 110 ||
				key == 190 ||
				(key >= 35 && key <= 40) ||
				(key >= 48 && key <= 57) ||
				(key >= 96 && key <= 105));
		});
		$(' #total_seat').on('keyup onmouseout keydown keypress blur change', function (e) {
			var key = e.charCode || e.keyCode || 0;
			if(($(this).val().length > 60) || ($(this).val() > 60)){
				$(this).val('');
				return false;
			}
			return (
				key == 8 ||
				key == 9 ||
				key == 13 ||
				key == 46 ||
				key == 110 ||
				key == 190 ||
				(key >= 35 && key <= 40) ||
				(key >= 48 && key <= 57) ||
				(key >= 96 && key <= 105));
		});
	});

</script>
<!-- iCheck -->
<link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

<script>
	$(document).ready(function () {
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});
	});

	$(".user_profile_pic").change(function() {

		var val = $(this).val();

		switch(val.substring(val.lastIndexOf('.') + 1).toLowerCase()){
			case 'gif': case 'jpg': case 'png': case 'jpeg':
            //alert("an image");
            break;
            default:
            $(this).val('');
            // error message here
            alert("Not an image");
            break;
        }
    });
</script>

@endsection
