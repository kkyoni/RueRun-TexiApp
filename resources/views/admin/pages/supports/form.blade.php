
<div class="form-group  row {{ $errors->has('username') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Category Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('cat_name',null,[
		'class' => 'form-control',
		'id'	=> 'name'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('cat_name') ? "".$errors->first('cat_name')."" : '' }} </font>
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
</script>

@endsection