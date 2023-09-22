

<div class="form-group  row {{ $errors->has('name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Make Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('name',null,[
		'class' => 'form-control',
		'id'	=> 'name',
		'maxlength'    =>  '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('name') ? "".$errors->first('name')."" : '' }} </font>
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

<!-- iCheck -->
<link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

<script>
	$(document).ready(function () {
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});

		$(' #name').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[a-zA-Z0-9 ._\\b]+$");    console.log("",event.keycode,event.charCode);
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
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
