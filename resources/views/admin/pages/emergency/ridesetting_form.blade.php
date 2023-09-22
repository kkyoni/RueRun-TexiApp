
<div class="form-group  row {{ $errors->has('contact_person') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Ride Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('name',null,[
		'class' => 'form-control',
		'id'	=> 'contact_person',
		'maxlength'    =>  '40'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('name') ? "".$errors->first('name')."" : '' }} </font>
		</span>
	</div>
</div>
<div class="form-group  row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>City</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">



		<select class="form-control" name="city_list[]" id="demo" multiple>
			@foreach($cityList as $key=>$city)
			<option 
			value="{{$key}}"
			@if(in_array($key,$cityArr)) selected @endif
			>{{$city}}</option>
			@endforeach
		</select>



		<span class="help-block">
			<font color="red"> {{ $errors->has('city_list') ? "".$errors->first('city_list')."" : '' }} </font>
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
<script src="{{ asset('assets/admin/js//select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/tagging.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/prism.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
	$('#demo').select2({
		closeOnSelect: false
	});
</script>

<!-- iCheck -->
<link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

<script>
	
</script>

@endsection