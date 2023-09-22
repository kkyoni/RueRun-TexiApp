<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">
		@if(!empty($users->avatar)) 

		<img src="{!! @$users->avatar !== '' ? url("storage/avatar/".@$users->avatar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
		@else
		<img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
		@endif
	</div> 
	{!! Form::file('avatar',['id' => 'hidden','accept'=>'image/*','class'=>'user_profile_pic']) !!}
	
</div>
<span >
	<center><font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font></center>
</span>
<br>
<!-- <div class="form-group  row {{ $errors->has('username') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Username</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('username',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('username') ? "".$errors->first('username')."" : '' }} </font>
		</span>
	</div>
</div> -->
<div class="form-group  row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>First Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('first_name',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('first_name') ? "".$errors->first('first_name')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group  row {{ $errors->has('last_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Last Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('last_name',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('last_name') ? "".$errors->first('last_name')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Number</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('contact_number',null,[
		'class' => 'form-control',
		'id'	=> 'contact_number'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Email address</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('email',null,[
		'class' => 'form-control',
		'id'	=> 'email'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('email') ? "".$errors->first('email')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Password</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::password('password',[
		'class' => 'form-control',
		'id'	=> 'password'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('password') ? "".$errors->first('password')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('address') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Address</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('address',null,[
		'class' => 'form-control',
		'id'	=> 'address'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('address') ? "".$errors->first('address')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('state_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>State</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">	
		{!! Form::select('state_id',$states,null,[
             'class'         => 'form-control',
             'id'            => 'states',
             'placeholder'   => 'Please select State'
     	]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('state_id') ? "".$errors->first('state_id')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('city_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>City</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		<select class="form-control" name="city_id" id="city">
			<option value>Please Select City</option>
			@foreach($cities as $city)
				<option value="{{$city->id}}" @if(!empty($users) && ($city->id == $users->city_id)) selected @endif>
				{{$city->city}}</option>
			@endforeach
		</select>
	
		<span class="help-block">
			<font color="red"> {{ $errors->has('city_id') ? "".$errors->first('city_id')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('country') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Country</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('country',null,[
		'class' => 'form-control',
		'id'	=> 'country'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('country') ? "".$errors->first('country')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">  

		<div class="i-checks">
			<label> 
				{{ Form::radio('status', 'active' ,true,['id'=> 'active']) }} <i></i> Active
			</label>
		
			<label> 
				{{ Form::radio('status', 'inactive' ,false,['id' => 'inactive']) }}
				<i></i> InActive 
			</label>
		</div>                                                 

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
		</span>
	</div>
</div>


<!-- <div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Gender</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">  

		<div class="i-checks">
			<label> 
				{{ Form::radio('gender', 'male' ,true,['id'=> 'active']) }} <i></i> Male
			</label>
			<label> 
				{{ Form::radio('gender', 'female' ,false,['id' => 'inactive']) }}
				<i></i> Female 
			</label>
		</div>                                                 

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('gender') ? "".$errors->first('gender')."" : '' }} </font>
		</span>
	</div>
</div> -->


<div class="hr-line-dashed"></div>

@section('styles')
<style type="text/css">
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

	function isNumberKey(evt)
	{
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;

		return true;
	}

	$('#credit-card').on('keypress change blur', function () {
		$(this).val(function (index, value) {

			return value.replace(/[^a-z0-9]+/gi, '').replace(/(.{4})/g, '$1 ');
		});
	});

	$('#credit-card').on('copy cut paste', function () {
		setTimeout(function () {
			$('#credit-card').trigger("change");
		});
	});


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

	var date = new Date();
	date.setDate(date.getDate());

	$('#date').datepicker({ 
		startDate: date,
		format: "mm",
		viewMode: "months", 
		minViewMode: "months"
	});

	var datee = new Date();
	date.setDate(date.getDate());

	$('#year').datepicker({ 
		startDate: datee,
		format: "yyyy",
		viewMode: "years", 
		minViewMode: "years"
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
            alert("not an image");
            break;
        }
    });


	// get cities as per state
    $(document).on("change","#states",function(e){
		var row = $(this);
		var id = $(this).val();
		if (id) {
			$.ajax({
				url:"{{ route('admin.subadmin.getcities') }}",
				type: 'post',
				data: {"_method": 'post',
					'id':id,
					"_token": "{{ csrf_token() }}"
				},
				success:function(result){
					var html='';		
					
					$("#city").html('');
					html = '<option value>Please Select City</option>'; 
				    $.each(result.all_cities, function (key, val) {
				        html += '<option value="'+val.id+'">'+val.city+'</option>';
				    });
				    $("#city").html(html);		
				},
				error:function(){
					swal("Error!", 'Error in updated Record', "error");
				}
			});
		} else {
			swal("Cancelled", "Your Status is safe :)", "error");
		}
	});
</script>

@endsection