<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">
		@if(!empty($preferences->avatar)) 
			<img src="{!! @$preferences->avatar !== '' ? url("storage/preferences/".@$preferences->avatar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
		@else
			<img src="{!! url('storage/preferences/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
		@endif
	</div> 
	{!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic']) !!}
	<span class="help-block">
		<font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font>
	</span>
</div>
<div class="form-group  row {{ $errors->has('description') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Description</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::textarea('description',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('description') ? "".$errors->first('description')."" : '' }} </font>
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
				{{ Form::radio('status', 'inactive',false,['id' => 'inactive']) }}
				<i></i> InActive 
			</label>
		</div>                                                 

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
		</span>
	</div>
</div>



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
</script>

@endsection