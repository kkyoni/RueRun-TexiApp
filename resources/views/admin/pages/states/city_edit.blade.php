
<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
City Management - Edit
@endsection
@section('mainContent')
@if(Session::has('message'))
<div class="row">
	<div class="col-md-12">
		<div class="alert alert-{{ Session::has('alert-type') }}">
			{!! Session::get('message') !!}
		</div>
	</div>
</div>
@endif
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Edit City</h2>
	</div>
	<div class="col-lg-2">

	</div>
</div>
<div class="wrapper wrapper-content">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox ">
				<div class="ibox-title">
					<h5></small></h5>

				</div>
				<div class="ibox-content">
					{!!Form::model($city,array('method'=>'POST','route'=>array('admin.states.city_update',$city->id)))!!}
					
					<div class="form-group  row {{ $errors->has('state') ? 'has-error' : '' }}">
						<label class="col-sm-3 col-form-label"><strong>State</strong> <span class="text-danger">*</span></label>
						<div class="col-sm-9">
							<select class="form-control" name="state_id" required>
								<option value=""> --- Please Select State ---</option>
								@foreach($states as $state)
								<option value="{{$state->id}}" @if($state->id == $city->state_id) selected @endif>{{$state->state}}</option>
								@endforeach
							</select>
							
							<span class="help-block">
								<font color="red"> {{ $errors->has('state') ? "".$errors->first('state')."" : '' }} </font>
							</span>
						</div>
					</div>
					<div class="form-group  row {{ $errors->has('city') ? 'has-error' : '' }}">
						<label class="col-sm-3 col-form-label"><strong>City</strong> <span class="text-danger">*</span></label>
						<div class="col-sm-9">
							{!! Form::text('city',null,[
							'class' => 'form-control',
							'id'	=> 'city','required',
							'maxlength' => '35'
							]) !!}
							<span class="help-block">
								<font color="red"> {{ $errors->has('city') ? "".$errors->first('city')."" : '' }} </font>
							</span>
						</div>
					</div>
					<div class="form-group ">
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
				
				{!! Form::close() !!}
			</div>
		</div>
		<!-- </div> -->
</div>
</div>

@endsection


