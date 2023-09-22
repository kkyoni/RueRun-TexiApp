
<div class="form-group  row {{ $errors->has('username') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Credit</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('amount',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('amount') ? "".$errors->first('amount')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="col-sm-6">
	<div class="form-group row">
		<div class="col-sm-8 col-sm-offset-8">
			<a href="{{route('admin.user_credit.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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