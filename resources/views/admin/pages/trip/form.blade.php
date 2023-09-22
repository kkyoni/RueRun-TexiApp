<div class="form-group  row {{ $errors->has('name') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">
		@if(!empty($emergency->avtar))
		<img src="{!! @$emergency->avtar !== '' ? url("storage/avtar/".@$emergency->avtar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
		@else
		<img src="{!! url('storage/avtar/default.png') !!}" alt="user-img" class="img-circle" accept="image/*">
		@endif
	</div> 
	{!! Form::file('avtar',['id' => 'hidden','accept'=>"image/*"]) !!}
</div>
<div class="form-group  row {{ $errors->has('contact_person') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Person</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('contact_person',null,[
		'class' => 'form-control',
		'id'	=> 'contact_person'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_person') ? "".$errors->first('contact_person')."" : '' }} </font>
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




<div class="col-sm-6">
	<div class="form-group row">
		<div class="col-sm-8 col-sm-offset-8">
			<a href="{{route('admin.emergencyindex')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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
	.fa-pencil{
		float: right;
		margin-right: 554px;
		margin-bottom: -48px;
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