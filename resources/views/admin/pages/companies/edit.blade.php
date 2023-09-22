<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
Company Management - Edit
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
		<h2>Edit Company</h2>
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
					{!!Form::model($company_detail,array('method'=>'post','files'=>true,'route'=>array('admin.company.update',$company_detail->id)))!!}
					@include('admin.pages.companies.form')
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-sm-8 col-sm-offset-8">
								<a href="{{route('admin.company.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
								<button class="btn btn-primary btn-sm" type="submit">Edit Save</button>
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


