<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
	<div id="imagePreview" class="profile-image">

		@if(!empty($users['avatar']))
		<img src="{!! url("storage/avatar/".@$users['avatar']) !!}" alt="user-img" class="img-circle">
		@else
		<img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
		@endif
	</div>
	{!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic pimg',]) !!}
</div>
<span >
	<center>	<font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font> </center>
</span>
<br>

<div class="form-group  row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>First Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('first_name',null,[
		'class' => 'form-control',
		'id'	=> 'first_name',
		'maxlength' => '50'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('first_name') ? "".$errors->first('first_name')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group  row {{ $errors->has('last_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Last Name</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('last_name',null,[
		'class' => 'form-control',
		'id'	=> 'last_name',
		'maxlength' => '50'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('last_name') ? "".$errors->first('last_name')."" : '' }} </font>
		</span>
	</div>
</div>
@if(!empty($users))
@php($disable = 'disabled')
@php($contactdisable='disabled')
@else
@php($disable = '')
@php($contactdisable='')
@endif
<div class="form-group row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Number</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('contact_number',null,[
		'class' => 'form-control',
		'id'	=> 'contact_number',$contactdisable,
		'maxlength'    =>  '10',
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Email address</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('email',null,[
		'class' => 'form-control',
		'id'	=> 'email',$disable
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('email') ? "".$errors->first('email')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Password</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::password('password',[
		'class' => 'form-control',
		'id'	=> 'password',
		'maxlength' => '50'
		]) !!}
		<span class="help-block">
			<font color="red"> {{$errors->has('password') ? "".$errors->first('password')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('address') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Address</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('address',null,[
		'class' => 'form-control',
		'id'	=> 'address',
		'maxlength' => '200'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('address') ? "".$errors->first('address')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('state_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>State</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">

		{!! Form::text('state_id',null,[
		'class' => 'form-control',
		'id'	=> 'state_id',
		'maxlength' => '30'
		]) !!}


		{{--		{!! Form::select('state_id',$states,null,[--}}
		{{--		'class'         => 'form-control',--}}
		{{--		'id'            => 'states',--}}
		{{--		'placeholder'   => 'Please select State'--}}
		{{--		]) !!}--}}
		<span class="help-block">
			<font color="red"> {{ $errors->has('state_id') ? "".$errors->first('state_id')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('city_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>City</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">
		{!! Form::text('city_id',null,[
		'class' => 'form-control',
		'id'	=> 'city_id',
		'maxlength' => '30'
		]) !!}


		{{--		{!! Form::select('city_id',$cities,null,[--}}
		{{--		'class'         => 'form-control',--}}
		{{--		'id'            => 'city',--}}
		{{--		'placeholder'   => 'Please select City'--}}
		{{--		]) !!}--}}

		<span class="help-block">
			<font color="red"> {{ $errors->has('city_id') ? "".$errors->first('city_id')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('country') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Country</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-6">{!! Form::text('country',null,[
		'class' => 'form-control',
		'id'	=> 'country',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('country') ? "".$errors->first('country')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">

		<div class="i-checks">
			<label>
				{{ Form::radio('status', 'active' ,true,['id'=> 'status_active']) }} <i></i> Active
			</label>
			<label>
				{{ Form::radio('status', 'inactive' ,false,['id' => 'status_inactive']) }}
				<i></i> InActive
			</label>
			@if(!empty($users) && $users->status === 'inactive' && $users->reason_for_inactive)
			@php($hiden='')
			@else
			@php($hiden='hidden')
			@endif
			{!! Form::textarea('reason_for_inactive',null,[
			'class' => "form-control ".$hiden,
			'id'	=> 'reason_for_inactive','cols'=>"10",'rows'=>"5"
			,'placeholder'=>"Reason For Inactive",
			]) !!}
		</div>

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
		</span>
	</div>
</div>

<br>
<br>

<div class="form-group row {{ $errors->has('vehicle_id') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Select Vehicle Type</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		{!! Form::select('vehicle_id',$vehicles,null,[
		'class'         => 'form-control',
		'id'            => 'vehicle_id',
		'placeholder'   => 'Please select Vehicle','required',
		'maxlength' => '30'
		]) !!}

		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_id') ? "".$errors->first('vehicle_id')."" : '' }} </font>
		</span>
	</div>
</div>

@if(!empty($users) && $users->driver_details)
<div class="form-group row {{ $errors->has('vehicle_model') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Model</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('vehicle_model',@$users->driver_details->vehicle_model,[
		'class' => 'form-control',
		'id'	=> 'vehicle_model',
		'maxlength' => '60'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_model') ? "".$errors->first('vehicle_model')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_plate') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Plate No</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('vehicle_plate',@$users->driver_details->vehicle_plate,[
		'class' => 'form-control',
		'id'	=> 'vehicle_plate',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_plate') ? "".$errors->first('vehicle_plate')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('color') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Color</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('color',@$users->driver_details->color,[
		'class' => 'form-control',
		'id'	=> 'color',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('color') ? "".$errors->first('color')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('mileage') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Mileage</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('mileage',@$users->driver_details->mileage,[
		'class' => 'form-control',
		'id'	=> 'mileage', 'maxlength'=>'3'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('mileage') ? "".$errors->first('mileage')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('year') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Manufacturing Year</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::number('year',@$users->driver_details->year,[
		'class' => 'form-control manufacturing_year',
		'id'	=> 'manufacturing_year','data-provide'=>"datepicker"
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('year') ? "".$errors->first('year')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group  row {{ $errors->has('ride_type') ? 'has-error' : '' }}">
	<label class="col-sm-3 col-form-label"><strong>Ride Type</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		{!! Form::select('ride_type[]',$ridesetting,@$driverridetypes,[
		'class'         => 'select2 form-control',
		'id'            => 'ride_type',
		'multiple'      => 'multiple',
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('ride_type') ? "".$errors->first('ride_type')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_image') ? 'has-error' : '' }}">
	<label class="col-sm-3 col-form-label"><strong> Vehicle Image</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		<input type="file" class="form-control" name="vehicle_image">
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_image') ? "".$errors->first('vehicle_image')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_image') ? 'has-error' : '' }}">
	<label class="col-sm-3 col-form-label"><strong></strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		<img src="{{ url('storage/vehicle_images/'.@$users->driver_details->vehicle_image) }}" onError="this.onerror=null;this.src='{!! url('storage/avatar/default.png') !!}' " style="max-height: 200px;max-width: 200px;">
	</div>
</div>
@else
<div class="form-group row {{ $errors->has('vehicle_model') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Model</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('vehicle_model',null,[
		'class' => 'form-control',
		'id'	=> 'vehicle_model'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_model') ? "".$errors->first('vehicle_model')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_plate') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Plate No</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('vehicle_plate',null,[
		'class' => 'form-control',
		'id'	=> 'vehicle_plate'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_plate') ? "".$errors->first('vehicle_plate')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('color') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Color</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('color',null,[
		'class' => 'form-control',
		'id'	=> 'color'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('color') ? "".$errors->first('color')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('mileage') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Mileage</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::text('mileage',null,[
		'class' => 'form-control',
		'id'	=> 'mileage','maxlength'=>'3'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('mileage') ? "".$errors->first('mileage')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('year') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Manufacturing Year</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">{!! Form::number('year',null,[
		'class' => 'form-control manufacturing_year',
		'id'	=> 'manufacturing_year','data-provide'=>"datepicker"
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('year') ? "".$errors->first('year')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group  row {{ $errors->has('ride_type') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Ride Type Setting</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		{!! Form::select('ride_type[]',$ridesetting,@$ridesetting,[
		'class'         => 'select2 form-control',
		'id'            => 'ride_type',
		'multiple'      => 'multiple',
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('ride_type') ? "".$errors->first('ride_type')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('vehicle_image') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Vehicle Image</strong> <span class="text-danger"></span></label>
	<div class="col-sm-6">
		<input type="file" class="form-control" name="vehicle_image">
		<span class="help-block">
			<font color="red"> {{ $errors->has('vehicle_image') ? "".$errors->first('vehicle_image')."" : '' }} </font>
		</span>
	</div>
</div>
@endif
<!-- <div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Gender</strong></label>
	&nbsp;&nbsp;
	<div class="col-sm-6 inline-block">

		<div class="i-checks">
			<label>
				{{ Form::radio('gender', 'male' ,true,['id'=> 'active']) }} <i></i> Male
			</label>
		</div>
		<div class="i-checks">
			<label>
				{{ Form::radio('gender', 'female' ,false,['id' => 'inactive']) }}
				<i></i> Female
			</label>
		</div>

		<span class="help-block">
			<font color="red"> 	{{ $errors->has('gender') ? "".$errors->first('gender')."" : '' }} </font>
		</span>
	</div>
</div> -->

<br>
<br>
@if(!empty($users->card_details))

@foreach($users->card_details as $card_user)
    <div class="col-md-4">
        <div class="payment-card">
            @if($card_user->card_name == "Visa")
                <i class="fa fa-cc-visa payment-icon-big text-success"></i>
            @elseif($card_user->card_name == "Mestro")
                <img src="https://cdn.imgbin.com/4/18/18/imgbin-maestro-mastercard-debit-card-logo-cirrus-mastercard-g5PHCPzJpFME1zVTBH7qr5mvP.jpg" style="width: 77.14px; height: 60px;">
            @elseif($card_user->card_name == "MasterCard")
                <i class="fa fa-cc-mastercard payment-icon-big text-warning"></i>
            @elseif($card_user->card_name == "American Express")
                <i class="fa fa-cc-amex payment-icon-big text-success"></i>
            @elseif($card_user->card_name == "RuPay")
                <img src="https://resize.indiatvnews.com/en/resize/newbucket/715_-/2019/08/rupay-card-1566468196.jpg" style="width: 77.14px; height: 60px;">
            @endif
            <h2>{{$card_user->card_number}}</h2>
            <div class="row">
                <div class="col-sm-6">
                    <small><strong>Expiry date:</strong> {{$card_user->card_expiry_month}}/{{$card_user->card_expiry_year}}</small>
                </div>
                <div class="col-sm-6 text-right">
                    <small><strong>Name:</strong> {{$card_user->card_holder_name}}</small>
                </div>
            </div>
        </div>
    </div>

@endforeach
{{--@else--}}
{{--<div class="form-group  row {{ $errors->has('card_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Card Number</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('card_number[]',null,[--}}
{{--		'class' => 'form-control credit-card',--}}
{{--		'id'	=> 'credit-card',$contactdisable,--}}
{{--		'maxlength'    =>  '19',--}}
{{--		'onkeypress' =>  'return isNumberKey(event)'--}}
{{--		]) !!}--}}

{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('card_number') ? "".$errors->first('card_number')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}

{{--<div class="form-group  row {{ $errors->has('card_holder_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Card Holder Name</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('card_holder_name[]',null,[--}}
{{--		'class' => 'form-control',--}}
{{--		'id'	=> 'card_holder_name',$contactdisable--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('card_holder_name') ? "".$errors->first('card_holder_name')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}
{{--<div class="form-group  row {{ $errors->has('card_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Name of Card</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('card_name[]',null,[--}}
{{--		'class' => 'form-control','id'	=> 'card_name',$contactdisable--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('card_name') ? "".$errors->first('card_name')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}
{{--<div class="form-group  row {{ $errors->has('card_expiry_month') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Card Expiry Month</strong></label>--}}
{{--	<div class="col-sm-3">{!! Form::number('card_expiry_month[]',null,[--}}
{{--		'class' => 'form-control w-45 date_abc date',--}}
{{--		'id'            => 'date',$contactdisable,--}}
{{--		'data-provide'  => 'datepicker'--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('card_expiry_month') ? "".$errors->first('card_expiry_month')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}
{{--<div class="form-group  row {{ $errors->has('card_expiry_year') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Card Expiry Year</strong></label>--}}
{{--	<div class="col-sm-3">{!! Form::number('card_expiry_year[]',null,[--}}
{{--		'class' => 'form-control year',--}}
{{--		'id'            => 'year',$contactdisable,--}}
{{--		'data-provide'  => 'datepicker'--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('card_expiry_year') ? "".$errors->first('card_expiry_year')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}

{{--<div class="form-group  row {{ $errors->has('cvv') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>CVV</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('cvv[]',null,[--}}
{{--		'class' => 'form-control','id'	=> 'cvv',$contactdisable,'maxlength'=>'3','onkeypress' =>  'return isNumberKey(event)'--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('cvv') ? "".$errors->first('cvv')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}


{{--<div class="form-group  row {{ $errors->has('billing_address') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Billing Address</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('billing_address[]',null,[--}}
{{--		'class' => 'form-control',--}}
{{--		'id'	=> 'billing_address',$contactdisable,--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('billing_address') ? "".$errors->first('billing_address')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}
{{--<div class="form-group  row {{ $errors->has('bank_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Bank Name</strong></label>--}}
{{--	<div class="col-sm-6">{!! Form::text('bank_name[]',null,[--}}
{{--		'class' => 'form-control',--}}
{{--		'id'	=> 'bank_name',$contactdisable,--}}
{{--		]) !!}--}}
{{--		<span class="help-block">--}}
{{--			<font color="red"> {{ $errors->has('bank_name') ? "".$errors->first('bank_name')."" : '' }} </font>--}}
{{--		</span>--}}
{{--	</div>--}}
{{--</div>--}}
@endif
<br>
<div class="input_fields_wrap"></div>
<br>
<!-- <button class="add_field_button btn btn-warning">Add cards details</button> -->
<div class="hr-line-dashed"></div>

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

<script type="text/javascript" src="{{ asset('js/star-rating.min.js')}}"></script>
<script type="text/javascript">
	$(document).ready(function() {
var max_fields = 15; //maximum input boxes allowed
var wrapper = $(".input_fields_wrap"); //Fields wrapper
var add_button = $(".add_field_button"); //Add button ID

var x = 1; //initlal text box count
$(add_button).click(function(e){ //on add input button click
	//alert('asdas');
	e.preventDefault();
if(x < max_fields){ //max input box allowed
x++; //text box increment


$(wrapper).append('<div class ="remove_test"><div class="hr-line-dashed"></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Number</strong></label><div class="col-sm-6"><input type ="text" name="card_number[]" class="form-control credit-card" id="credit-card" maxlength="17" onkeypress="return isNumberKey(event)" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Holder Name</strong></label><div class="col-sm-6"><input type ="text" name="card_holder_name[]" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Name</strong></label><div class="col-sm-6"><input type ="text" name="card_name[]" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Month</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_month[]" class="form-control w-45 date_abc date" id="date" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Year</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_year[]" class="form-control year" id="year" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>CVV</strong></label><div class="col-sm-6"><input type ="text" name="cvv[]" class="form-control" onkeypress="return isNumberKey(event)" id="cvv" maxlength="3" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Billing Address</strong></label><div class="col-sm-6"><input type ="text" name="billing_address[]" class="form-control "></div></div> <div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Bank Name</strong></label><div class="col-sm-6"><input type ="text" name="bank_name[]" class="form-control" ></div></div> <div style="cursor:pointer;background-color:red;" class="remove_field btn btn-info">Remove</div></div>');//add input box
var date = new Date();
date.setDate(date.getDate());

$('.date').datepicker({
	startDate: date,
	format: "mm",
	viewMode: "months",
	minViewMode: "months"
});


var datee = new Date();
date.setDate(date.getDate());

$('.year').datepicker({
	startDate: datee,
	format: "yyyy",
	viewMode: "years",
	minViewMode: "years"
});


function isNumberKey(evt)
{
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;

	return true;
}

$('.credit-card').on('keypress change blur', function () {
	$(this).val(function (index, value) {

		return value.replace(/[^a-z0-9]+/gi, '').replace(/(.{4})/g, '$1 ');
	});
});

$('.credit-card').on('copy cut paste', function () {
	setTimeout(function () {
		$('.credit-card').trigger("change");
	});
});




}
});
$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
	e.preventDefault(); $(this).parent('div').remove(); x--;
})
});


$(wrapper).on("click",".remove_field_id", function(e){ //user click on remove text
	//alert('sasdsda');
	e.preventDefault(); $(this).parent('.remove_test').remove(); x--;
})

// $('#ride_type').multiselect({
// 	columns: 1,
// 	placeholder: 'Select Username',
// 	search: true,
// 	selectAll: true
// });

</script>
<script type="text/javascript">

	function isNumberKey(evt)
	{
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;

		return true;
	}

	$('.credit-card').on('keypress change blur', function () {
		$(this).val(function (index, value) {

			return value.replace(/[^a-z0-9]+/gi, '').replace(/(.{4})/g, '$1 ');
		});
	});

	$('.credit-card').on('copy cut paste', function () {
		setTimeout(function () {
			$('.credit-card').trigger("change");
		});
	});



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
		$('.user_profile_pic').trigger('click');
		$('.user_profile_pic').change(function() {
			readURL(this);
		});
	});


	var date = new Date();
	date.setDate(date.getDate());

	$('.date').datepicker({
		startDate: date,
		format: "mm",
		viewMode: "months",
		minViewMode: "months"
	});

	var datee = new Date();
	date.setDate(date.getDate());

	$('.year').datepicker({
		startDate: datee,
		format: "yyyy",
		viewMode: "years",
		minViewMode: "years"
	});

	var datee = new Date();
	date.setDate(date.getDate());

	$('.manufacturing_year').datepicker({
		endDate: datee,
		format: "yyyy",
		viewMode: "years",
		minViewMode: "years"
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

		$(".i-checks input").on('ifChanged', function (e) {
			if(this.value == 'active'){
				if(!$("#reason_for_inactive").hasClass( "hidden" )){
					$("#reason_for_inactive").addClass('hidden');
				}
			}
			if(this.value == 'inactive'){
				$("#reason_for_inactive").removeClass('hidden');
			}
		});

		$('#last_name, #first_name, #bank_name').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
		});

		$('#amount, #contact_number').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[0-9 ._\\b\\t]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}

            // if(($(this).val().length > 9)){
            //     event.preventDefault();
            //     return false;
            // }
        });

		$('#company_name, #recipient_name, #job_title, #country, #state_id, #city_id, #color').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
		});

		$('#company_size, #mileage, #manufacturing_year').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[0-9 ._\\b\\t]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}

		});

		$('#contact_number').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[0-9 ._\\b\\t]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}

            // if(($(this).val().length > 9)){
            //     event.preventDefault();
            //     return false;
            // }
        });

		// get cities as per state
		$(document).on("change","#states",function(e){
			var row = $(this);
			var id = $(this).val();
			if (id) {
				$.ajax({
					url:"{{ route('admin.subadmin.getcities') }}",
					type: 'post',
					data: {"_method": 'post',
					'id':id,
					"_token": "{{ csrf_token() }}"
				},
				success:function(result){
					var html='';

					$("#city").html('');
					html = '<option value>Please Select City</option>';
					$.each(result.all_cities, function (key, val) {
						html += '<option value="'+val.id+'">'+val.city+'</option>';
					});
					$("#city").html(html);
				},
				error:function(){
					swal("Error!", 'Error in updated Record', "error");
				}
			});
			} else {
				swal("Cancelled", "Your Status is safe :)", "error");
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

	$(document).on("change","#input-rating",function(e){
		var rating = $(this).val();
		var userid = $(this).attr('data-userid');
		if (rating) {
			$.ajax({
				url:"{{ route('admin.driver.setadminrating') }}",
				type: 'post',
				data: {"_method": 'post',
				'rating':rating, userid: userid,
				"_token": "{{ csrf_token() }}"
			},
			success:function(result){
				location.reload();
			},
			error:function(){
				swal("Error!", 'Error in updated Record', "error");
			}
		});
		} else {
			swal("Cancelled", "Your Status is safe :)", "error");
		}
	});

	$('#cvv, .cvv').on('keyup onmouseout keydown keypress blur change', function (e) {
		var regex = new RegExp("^[0-9 ._\\b\\t]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
			return false;
		}
	});

	function passwordStrength(password,ref) {

		var desc = [{'width':'0px'}, {'width':'20%'}, {'width':'40%'}, {'width':'60%'}, {'width':'80%'}, {'width':'100%'}];

		var descClass = ['', 'progress-bar-danger', 'progress-bar-danger', 'progress-bar-warning', 'progress-bar-success', 'progress-bar-success'];

		var score = 0;

        //if password bigger than 6 give 1 point
        if (password.length >= 8) score++;

        //if password has both lower and uppercase characters give 1 point
        if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/))) score++;

        //if password has at least one number give 1 point
        if (password.match(/d+/)) score++;

        //if password has at least one special caracther give 1 point
        if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) )	score++;

        //if password bigger than 12 give another 1 point
        if (password.length >= 8) score++;
        // display indicator
        console.log(score);
        if(score<4){
        	ref.next('.help-block').find('font').html('Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters');
        }else{
        	ref.next('.help-block').find('font').html('');
        }
        //$("#jak_pstrength").removeClass(descClass[score-1]).addClass(descClass[score]).css(desc[score]);
    }
    jQuery("#password").keyup(function() {
    	passwordStrength(jQuery(this).val(),$(this));
    });
</script>
<script src="{{ asset('assets/admin/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/tagging.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/prism.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
	$('#ride_type').select2({
		closeOnSelect: false
	});
</script>

@endsection
