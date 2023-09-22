
<div class="form-group  row {{ $errors->has('promo_code') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Promocode</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('promo_code',null,[
		'class' => 'form-control',
		'id'	=> 'promo_code'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('promo_code') ? "".$errors->first('promo_code')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group row {{ $errors->has('amount') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Amount (In Percentage)</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::number('amount',null,[
		'class' => 'form-control',
		'id'	=> 'amount'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('amount') ? "".$errors->first('amount')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group row {{ $errors->has('start_date') ? 'has-error' : '' }} " id="data_2"><label class="col-sm-3 col-form-label"><strong>Start Date</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6 input-group date">
		<span class="input-group-addon"><i class="fa fa-calendar"></i></span>{!! Form::text('start_date',null,[
		'class' => 'form-control',
		'id'	=> 'start_date'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('start_date') ? "".$errors->first('start_date')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('end_date') ? 'has-error' : '' }}" id="data_1"><label class="col-sm-3 col-form-label"><strong>End Date</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6 input-group date">
		<span class="input-group-addon"><i class="fa fa-calendar"></i></span>{!! Form::text('end_date',null,[
		'class' => 'form-control',
		'id'	=> 'end_date'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('end_date') ? "".$errors->first('end_date')."" : '' }} </font>
		</span>
		
	</div>
</div>

<div class="form-group row {{ $errors->has('description') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Description</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::textarea('description',null,[
		'class' => 'form-control',
		'id'	=> 'description'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('description') ? "".$errors->first('description')."" : '' }} </font>
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
		</div>
		<div class="i-checks">
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


<div class="hr-line-dashed"></div>




<div class="col-sm-6">
	<div class="form-group row">
		<div class="col-sm-8 col-sm-offset-8">
			<a href="{{route('admin.promocode.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
			<button class="btn btn-primary btn-sm" type="submit">Edit Save</button>
		</div>
	</div>
</div>

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
	#imagePreview{
		width: 100%;
		height: 100%;
		text-align: center;
		margin:0 auto;	
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
</script>

@endsection