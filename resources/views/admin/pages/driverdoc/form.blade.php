<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">
		@if(!empty($users->avatar)) 

		<img src="{!! @$users->avatar !== '' ? url("storage/avatar/".@$users->avatar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
		@else
		<img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
		@endif
	</div> 
	{!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*"]) !!}
	<span class="help-block">
		<font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font>
	</span>
</div>
<div class="form-group  row {{ $errors->has('username') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Username</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('username',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('username') ? "".$errors->first('username')."" : '' }} </font>
		</span>
	</div>
</div>


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
	<div class="col-sm-6">{!! Form::number('contact_number',null,[
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

<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">  

		<div class="i-checks">
			<label> 
				{{ Form::radio('status', 'active' ,true,['id'=> 'active']) }} <i></i> Active
			</label>
		</div>
		<div class="i-checks">
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


<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Gender</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">  

		<div class="i-checks">
			<label> 
				{{ Form::radio('gender', 'male' ,['id'=> 'active']) }} <i></i> Male
			</label>
		</div>
		<div class="i-checks">
			<label> 
				{{ Form::radio('gender', 'female' ,['id' => 'inactive']) }}
				<i></i> Female 
			</label>
		</div>                                                 

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('gender') ? "".$errors->first('gender')."" : '' }} </font>
		</span>
	</div>
</div>



<div class="hr-line-dashed"></div>




<div class="col-sm-6">
	<div class="form-group row">
		<div class="col-sm-8 col-sm-offset-8">
			<a href="{{route('admin.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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