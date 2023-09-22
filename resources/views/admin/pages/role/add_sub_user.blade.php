
<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
	Sub-Admin Management - Create
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
		<h2>Add Sub-Admin</h2>
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
					{!! Form::open(array('route' => 'admin.role.store','method'=>'POST')) !!}
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <strong>Name:</strong>
                                                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                        <strong>Status:</strong>
                                                        {!! Form::text('status', null, array('placeholder' => 'status','class' => 'form-control')) !!}
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                 <div class="form-group">
                                                        <strong>Email:</strong>
                                                        {!! Form::text('email', null, array('placeholder' => 'Enter email','class' => 'form-control')) !!}
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                 <div class="form-group">
                                                        <strong>Password:</strong>
                                                        {!! Form::text('password', null, array('placeholder' => 'Enter password','class' => 'form-control')) !!}
                                                    </div>
                                                </div>
                                                  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <strong>Permission:</strong>
                                                        <br/>
                                                        @foreach($permission as $value)
                                                            <label>{{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name')) }}
                                                            {{ $value->name }}</label>
                                                        <br/>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                            {!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>

@endsection


