<div class="form-group  row {{ $errors->has('name') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">
		@if(!empty($emergency->image))
		<img src="{!! @$emergency->image !== '' ? url("storage/avatar/".@$emergency->image) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
		@else
		<img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" accept="image/*">
		@endif
	</div> 
	{!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic']) !!}
</div>
<div class="form-group  row {{ $errors->has('contact_person') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Person</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('contact_person',null,[
		'class' => 'form-control',
		'id'	=> 'contact_person',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_person') ? "".$errors->first('contact_person')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group  row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Number</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('contact_number',null,[
		'class' => 'form-control',
		'id'	=> 'contact_number',
		'maxlength' => '15'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group row {{ $errors->has('contact_details') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Details</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::textarea('contact_details',null,[
		'class' => 'form-control',
		'id'	=> 'contact_details'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_details') ? "".$errors->first('contact_details')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">  

		<div class="i-checks">
			<label> 
				{{ Form::radio('status', 'active',true,['id'=> 'active']) }} <i></i> Active
			</label>
		</div>
		<div class="i-checks">
			<label> 
				{{ Form::radio('status', 'inactive',false ,['id' => 'inactive']) }}
				<i></i> Inactive
			</label>
		</div>                                                 

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
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

<!-- iCheck -->
<link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

<script>

	$('#contact_number').on('keyup onmouseout keydown keypress blur change', function (event) {
		var regex = new RegExp("^[0-9 ._\\b\\t]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
			event.preventDefault();
			return false;
		}

		if(($(this).val().length > 9)){
			event.preventDefault();
			return false;
		}
	});


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
