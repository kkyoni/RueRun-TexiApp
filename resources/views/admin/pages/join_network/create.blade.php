
<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
Promocode Management - Create
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
		<h2>Add Promocode</h2>
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
					{!! 
					Form::open([
					'route'	=> ['admin.promocode.store'],
					'id'	=> 'userCreateForm',
					'files' => 'true' 
					])
					!!}
					@include('admin.pages.promocode.form')
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-sm-8 col-sm-offset-8">
								<a href="{{route('admin.promocode.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
								<button class="btn btn-primary btn-sm" type="submit">Save</button>
							</div>
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>

@endsection


