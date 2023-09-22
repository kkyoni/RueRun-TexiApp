<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
User Management - Edit
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
		<h2>Edit User</h2>
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
					{!!Form::model($users,array('method'=>'post','files'=>true,'route'=>array('admin.update',$users->id)))!!}
					@include('admin.pages.user.form')
					<div class="hr-line-dashed"></div>


					<div class="form-group hidden row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Total Ride Booking</strong> <span class="text-danger">*</span></label>
						<label class="col-sm-3 col-form-label"><strong>
							@php($rides=\App\Models\Booking::where('user_id', $users->id)->where('trip_status','completed')->get()->count())
							{{$rides}}
						</strong> Rides Completed
					</label>
				</div>
				<div class="form-group hidden row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Total Parcel Booking</strong><span class="text-danger">*</span></label>
					<label class="col-sm-3 col-form-label"><strong>
						@php($parcels=\App\Models\ParcelDetail::where('user_id', $users->id)->where('parcel_status','completed')->get()->count())
						{{$parcels}}
					</strong> Parcel Delivery Completed
				</label>
			</div>

			<div class="form-group hidden row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Star Ratings</strong><span class="text-danger"></span></label>
				<label class="col-sm-3 col-form-label"><strong>
					@php($rating=\App\Models\RatingReviews::where('to_user_id', $users->id)->where('status','approved')->get())

					@if((int)$rating->avg('rating') > 0)
					{{$rating->avg('rating')}}
					@else
					0
					@endif
				</strong> Average
			</label>
		</div>

		<div class="form-group hidden row"><label class="col-sm-3 col-form-label"><strong>Admin Rating To User</strong> <span class="text-danger"></span></label>
			<div class="col-sm-6">
				<div class="rating">
					@php($rating=\App\Models\RatingReviews::where('to_user_id', $users->id)->where('from_user_id',Auth::User()->id)->first())

					@if(!empty($rating))
					<input id="input-rating" name="input-rating" class="rating rating-loading" data-show-clear="false" data-show-caption="true" data-readonly="true" value="{{$rating->rating}}" data-min="0" data-max="5" data-userid="{{$users->id}}">
					@else
					<input id="input-rating" name="input-rating" class="rating rating-loading" data-show-clear="false" data-show-caption="true" data-readonly="false" value="0" data-min="0" data-max="5" data-userid="{{$users->id}}">
					@endif
				</div>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-sm-8 col-sm-offset-8">
					<a href="{{route('admin.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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