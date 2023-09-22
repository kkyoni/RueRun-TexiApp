<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
  <div id="imagePreview" class="profile-image ">
    @if(!empty($users->avatar))
    <img src="{!! @$users->avatar !== '' ? url("storage/avatar/".@$users->avatar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
    @else
    <img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
    @endif
  </div>
  {!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic']) !!}
</div>
<span>
	<center><font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font> </center>
</span>
<br>

<div class="form-group row {{ $errors->has('user_signup_as') ? 'has-error' : '' }}">
  <label class="col-sm-3 col-form-label"><strong>User Signup As</strong> <span class="text-danger"></span></label>
  <div class="col-sm-6">
    @php($types = array('individual'=>'Individual', 'company'=>'Company'))
    {!! Form::select('driver_signup_as',$types,null,[
    'class'         => 'form-control',
    'id'            => 'user_signup_as',
    'placeholder'   => 'Please select','required'
    ]) !!}

    <span class="help-block">
     <font color="red"> {{ $errors->has('driver_signup_as') ? "".$errors->first('driver_signup_as')."" : '' }} </font>
   </span>
 </div>
</div>

@if(!empty($users) && ($users->driver_signup_as === 'individual'))
<div class="form-group first_name_block row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>First Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('first_name',null,[
    'class' => 'form-control',
    'id'	=> 'name',
    'maxlength' => '30'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('first_name') ? "".$errors->first('first_name')."" : '' }} </font>
    </span>
  </div>
</div>

<div class="form-group last_name_block row {{ $errors->has('last_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Last Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('last_name',null,[
    'class' => 'form-control',
    'id'	=> 'last_name',
    'maxlength' => '30'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('last_name') ? "".$errors->first('last_name')."" : '' }} </font>
    </span>
  </div>
</div>
@else
<div class="form-group hidden first_name_block row {{ $errors->has('first_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>First Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('first_name',null,[
    'class' => 'form-control',
    'id'	=> 'name',
    'maxlength' => '30'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('first_name') ? "".$errors->first('first_name')."" : '' }} </font>
   </span>
 </div>
</div>

<div class="form-group hidden last_name_block row {{ $errors->has('last_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Last Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('last_name',null,[
    'class' => 'form-control',
    'id'	=> 'last_name',
    'maxlength' => '30'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('last_name') ? "".$errors->first('last_name')."" : '' }} </font>
   </span>
 </div>
</div>
@endif

@if(!empty($users))
@php($disable = 'disabled')
@php($contactdisable='disabled')
@php($email=$users->email)
@php($contact=$users->contact_number)
@else
@php($disable = '')
@php($contactdisable='')
@php($email='')
@php($contact='')
@endif
<div class="form-group row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Number</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">
    @if(\Request::route()->getName() == 'admin.edit')
    {!! Form::tel('contact_number',null,['class'=>'tel_no form-control','id'  => 'contact_number',$contactdisable,
    'maxlength' => '10',"data-number"=>"$users->country_code$users->contact_number"]) !!}
    @else
    {!! Form::tel('contact_number',null,['maxlength' => '10','class'=>'tel_no form-control']) !!}
    @endif

    <input type="hidden" name="country_code" id="countryCode" value="<?php if(isset($users) && !empty($users)) {
     echo $users->country_code;
   } ?>">
   <input type="hidden" name="country_code_al" id="countryCodeal" value="">
   <input type="hidden" id="currentcode" value="<?php if(isset($users) && !empty($users)) {
     echo $users->country_code_al;
   } ?>">
   @if(!empty($users))
   <input type="hidden" name="contact_number" value="{{$contact}}">
   @endif
   <span class="help-block">
     <font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
   </span>
   
 </div>
</div>

<div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Email address</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('email',null,[
    'class' => 'form-control',
    'id'	=> 'email',$disable,
    'maxlength' => '100'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('email') ? "".$errors->first('email')."" : '' }} </font>
   </span>
   @if(!empty($users))
   <input type="hidden" name="email" value="{{$email}}">
   @endif

 </div>
</div>


<div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Password</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">
    {!! Form::password('password',[
    'class' => 'form-control',
    'id'	=> 'password',
    'maxlength' => '40'
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
		'maxlength' => '40'
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
		'maxlength' => '40'
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
		'maxlength' => '40'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('country') ? "".$errors->first('country')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Status</strong></label>
	<div class="col-sm-6 inline-block">
		<div class="i-checks">
			<label>
				{{ Form::radio('status', 'active' ,true,['id'=> 'active']) }} <i></i> Active
			</label>
			<label>
				{{ Form::radio('status', 'inactive' ,false,['id' => 'inactive']) }}
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
     ,'placeholder'=>"Reason For Inactive",'minlength'=>'4'
     ]) !!}
   </div>
   <span class="help-block">
     <font color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
   </span>
 </div>
</div>

<br><br>
@if(!empty($users) && !empty($users->company_details) && ($users->driver_signup_as === 'company'))
<div class="company_detail">
  <div class="form-group row {{ $errors->has('company_name') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Company Name</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">{!! Form::text('company_name',@$users->company_name,[
      'class' => 'form-control',
      'id'	=> 'company_name',
      'maxlength' => '60'
      ]) !!}
      <span class="help-block">
        <font color="red"> {{ $errors->has('company_name') ? "".$errors->first('company_name')."" : '' }} </font>
      </span>
    </div>
  </div>

  <div class="form-group row {{ $errors->has('recipient_name') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Recipient Name</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">{!! Form::text('recipient_name',@$users->company_details->recipient_name,[
      'class' => 'form-control',
      'id'	=> 'recipient_name',
      'maxlength' => '60'
      ]) !!}
      <span class="help-block">
        <font color="red"> {{ $errors->has('recipient_name') ? "".$errors->first('recipient_name')."" : '' }} </font>
      </span>
    </div>
  </div>

  <div class="form-group row {{ $errors->has('job_title') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Job Title</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">{!! Form::text('job_title',@$users->company_details->job_title,[
      'class' => 'form-control',
      'id'	=> 'job_title',
      'maxlength' => '30'
      ]) !!}
      <span class="help-block">
       <font color="red"> {{ $errors->has('job_title') ? "".$errors->first('job_title')."" : '' }} </font>
     </span>
   </div>
 </div>

 <div class="form-group row {{ $errors->has('company_size') ? 'has-error' : '' }}">
  <label class="col-sm-3 col-form-label"><strong>Company Size</strong> <span class="text-danger"></span></label>
  <div class="col-sm-6">{!! Form::text('company_size',@$users->company_details->company_size,[
    'class' => 'form-control',
    'id'	=> 'company_size',
    'maxlength' => '5'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('job_title') ? "".$errors->first('job_title')."" : '' }} </font>
   </span>
 </div>
</div>
<div class="form-group row {{ $errors->has('website') ? 'has-error' : '' }}">
  <label class="col-sm-3 col-form-label"><strong>Website</strong> <span class="text-danger"></span></label>
  <div class="col-sm-6">{!! Form::text('website',@$users->company_details->website,[
    'class' => 'form-control',
    'id'	=> 'website',
    'maxlength' => '30'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('website') ? "".$errors->first('website')."" : '' }} </font>
   </span>
 </div>
</div>
</div>
@else
<div class="company_detail hidden">
  <div class="form-group row {{ $errors->has('company_name') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Company Name</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">
      {!! Form::text('company_name',null,[
      'class' => 'form-control',
      'id'	=> 'company_name',
      'maxlength' => '60','placeholder'   => 'Company Name'
      ]) !!}
      <span class="help-block">
        <font color="red"> {{ $errors->has('company_name') ? "".$errors->first('company_name')."" : '' }} </font>
      </span>
    </div>
  </div>

  <div class="form-group row {{ $errors->has('recipient_name') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Recipient Name</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">{!! Form::text('recipient_name',null,[
      'class' => 'form-control',
      'id'	=> 'recipient_name',
      'maxlength' => '60','placeholder'   => 'Recipient Name'
      ]) !!}
      <span class="help-block">
        <font color="red"> {{ $errors->has('recipient_name') ? "".$errors->first('recipient_name')."" : '' }} </font>
      </span>
    </div>
  </div>

  <div class="form-group row {{ $errors->has('job_title') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Job Title</strong> <span class="text-danger"></span></label>
    <div class="col-sm-6">{!! Form::text('job_title',null,[
      'class' => 'form-control',
      'id'	=> 'job_title',
      'maxlength' => '30','placeholder'   => 'Job Title'
      ]) !!}
      <span class="help-block">
       <font color="red"> {{ $errors->has('job_title') ? "".$errors->first('job_title')."" : '' }} </font>
     </span>
   </div>
 </div>

 <div class="form-group row {{ $errors->has('company_size') ? 'has-error' : '' }}">
  <label class="col-sm-3 col-form-label"><strong>Company Size</strong> <span class="text-danger"></span></label>
  <div class="col-sm-6">{!! Form::text('company_size',null,[
    'class' => 'form-control',
    'id'	=> 'company_size',
    'maxlength' => '5','placeholder'   => 'Company Size'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('job_title') ? "".$errors->first('job_title')."" : '' }} </font>
   </span>
 </div>
</div>
<div class="form-group row {{ $errors->has('website') ? 'has-error' : '' }}">
  <label class="col-sm-3 col-form-label"><strong>Website</strong> <span class="text-danger"></span></label>
  <div class="col-sm-6">{!! Form::text('website',null,[
    'class' => 'form-control',
    'id'	=> 'website',
    'maxlength' => '30','placeholder'   => 'Website'
    ]) !!}
    <span class="help-block">
     <font color="red"> {{ $errors->has('website') ? "".$errors->first('website')."" : '' }} </font>
   </span>
 </div>
</div>
</div>
@endif
<br>
<br>
@if(!empty($users->card_details))
<?php $i ="1"; ?>
<div class="row">
  @foreach($users->card_details as $card_user)
  <div class="col-md-4">
    <div class="payment-card">
     @if($card_user->card_name == "Visa")
     <i class="fa fa-cc-visa payment-icon-big text-success"></i>
     @elseif($card_user->card_name == "Mestro")
     <img src="{!! url('storage/card_details/Mestro.jpg') !!}" style="width: 77.14px; height: 60px;">
     @elseif($card_user->card_name == "MasterCard")
     <i class="fa fa-cc-mastercard payment-icon-big text-warning"></i>
     @elseif($card_user->card_name == "American Express")
     <i class="fa fa-cc-amex payment-icon-big text-success"></i>
     @elseif($card_user->card_name == "RuPay")
     <img src="{!! url('storage/card_details/rupay.jpg') !!}" style="width: 77.14px; height: 60px;">
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
<?php $i ++; ?>
@endforeach
</div>
@else
@endif
@section('styles')
<style type="text/css">
	.card1{
		/*position: absolute;*/
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		margin: auto;
		width: 85.6mm;
		height: 53.98mm;
		color: #fff;
		font: 22px/1 'Iceland', monospace;
		background: #23189a;
		border: 1px solid #1e1584;
		-webkit-border-radius: 10px;
		-webkit-background-clip: padding-box;
		-moz-border-radius: 10px;
		-moz-background-clip: padding;
		border-radius: 10px;
		background-clip: padding-box;
		overflow: hidden;
	}

	.bank-name{float: right;
		margin-top: 15px;
		margin-right: 30px;
		font: 800 28px 'Open Sans', Arial, sans-serif;}

    .chip{    position: relative;
      z-index: 1000;
      width: 50px;
      height: 40px;
      margin-top: 50px;
      margin-bottom: 10px;
      margin-left: 30px;
      background: #fffcb1;
      background: -moz-linear-gradient(-45deg, #fffcb1 0%, #b4a365 100%);
      background: -webkit-gradient(linear, left top, right bottom, color-stop(0%, #fffcb1), color-stop(100%, #b4a365));
      background: -webkit-linear-gradient(-45deg, #fffcb1 0%, #b4a365 100%);
      background: -o-linear-gradient(-45deg, #fffcb1 0%, #b4a365 100%);
      background: -ms-linear-gradient(-45deg, #fffcb1 0%, #b4a365 100%);
      background: linear-gradient(135deg, #fffcb1 0%, #b4a365 100%);
      filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#fffcb1", endColorstr="#b4a365", GradientType=1);
      border: 1px solid #322d28;
      -webkit-border-radius: 10px;
      -webkit-background-clip: padding-box;
      -moz-border-radius: 10px;
      -moz-background-clip: padding;
      border-radius: 10px;
      background-clip: padding-box;
      -webkit-box-shadow: 0 1px 2px #322d28, 0 0 5px 0 0 5px rgba(144, 133, 87, 0.25) inset;
      -moz-box-shadow: 0 1px 2px #322d28, 0 0 5px 0 0 5px rgba(144, 133, 87, 0.25) inset;
      box-shadow: 0 1px 2px #322d28, 0 0 5px 0 0 5px rgba(144, 133, 87, 0.25) inset;
      overflow: hidden;}
      .chip .side.left {
        left: 0;
        border-left: none;
        -webkit-border-radius: 0 2px 2px 0;
        -webkit-background-clip: padding-box;
        -moz-border-radius: 0 2px 2px 0;
        -moz-background-clip: padding;
        border-radius: 0 2px 2px 0;
        background-clip: padding-box;
      }
      .chip .side.right {
        right: 0;
        border-right: none;
        -webkit-border-radius: 2px 0 0 2px;
        -webkit-background-clip: padding-box;
        -moz-border-radius: 2px 0 0 2px;
        -moz-background-clip: padding;
        border-radius: 2px 0 0 2px;
        background-clip: padding-box;
      }
      .chip .side {
        position: absolute;
        top: 8px;
        width: 12px;
        height: 24px;
        border: 1px solid #322d28;
        -webkit-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
      }
      .chip .vertical.top {
        top: 0;
        border-top: none;
      }
      .chip .vertical {
        position: absolute;
        left: 0;
        right: 0;
        margin: 0 auto;
        width: 8.66666667px;
        height: 12px;
        border: 1px solid #322d28;
        -webkit-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
      }
      .chip .vertical.bottom {
        bottom: 0;
        border-bottom: none;
      }
      .chip .vertical {
        position: absolute;
        left: 0;
        right: 0;
        margin: 0 auto;
        width: 8.66666667px;
        height: 12px;
        border: 1px solid #322d28;
        -webkit-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
        box-shadow: 0 0 5px rgba(144, 133, 87, 0.25) inset, 0 0 5px rgba(144, 133, 87, 0.25), 0 0 4px rgba(0, 0, 0, 0.1), 0 0 4px rgba(0, 0, 0, 0.1) inset;
      }
      .data {
        position: relative;
        z-index: 100;
        margin-left: 30px;
        text-transform: uppercase;
      }
      .data .pan {
        position: relative;
        z-index: 50;
        margin: 0;
        letter-spacing: 1px;
        font-size: 26px;
      }
      .data .pan, .data .month, .data .year, .data .year:before, .data .name-on-card, .data .date {
        position: relative;
        z-index: 20;
        letter-spacing: 1px;
        text-shadow: 1px 1px 1px #000;
      }
      .data .first-digits {
        margin: 0;
        font: 400 10px/1 'Open Sans', Arial, sans-serif;
      }
      .data .exp-date-wrapper {
        margin-top: 5px;
        margin-left: 64px;
        line-height: 1;
        *zoom: 1;
      }
      .data .exp-date-wrapper .left-label {
        float: left;
        display: inline-block;
        width: 40px;
        font: 400 7px/8px 'Open Sans', Arial, sans-serif;
        letter-spacing: 0.5px;
      }
      .data .exp-date-wrapper .exp-date {
        display: inline-block;
        float: left;
        margin-top: -10px;
        margin-left: 10px;
        text-align: center;
      }
      .data .exp-date-wrapper .exp-date .upper-labels {
        font: 400 7px/1 'Open Sans', Arial, sans-serif;
      }
      .data .pan, .data .month, .data .year, .data .year:before, .data .name-on-card, .data .date {
        position: relative;
        z-index: 20;
        letter-spacing: 1px;
        text-shadow: 1px 1px 1px #000;
      }
      .data .name-on-card {
        margin-top: 10px;
      }
      .data .pan, .data .month, .data .year, .data .year:before, .data .name-on-card, .data .date {
        position: relative;
        z-index: 20;
        letter-spacing: 1px;
        text-shadow: 1px 1px 1px #000;
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
      .iti--allow-dropdown input, .iti--allow-dropdown input[type=text], .iti--allow-dropdown input[type=tel], .iti--separate-dial-code input, .iti--separate-dial-code input[type=text], .iti--separate-dial-code input[type=tel] {
        padding-right: 6px;
        padding-left: 410px;
        margin-left: 0;
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


                $(wrapper).append('<div class ="remove_test"><div class="hr-line-dashed"></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Number</strong></label><div class="col-sm-6"><input type ="text" name="card_number[]" class="form-control credit-card" id="credit-card" maxlength="19" onkeypress="return isNumberKey(event)" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Holder Name</strong></label><div class="col-sm-6"><input type ="text" name="card_holder_name[]" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Name</strong></label><div class="col-sm-6"><input type ="text" name="card_name[]" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Month</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_month[]" class="form-control w-45 date_abc date" id="date" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Year</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_year[]" class="form-control year" id="year" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>CVV</strong></label><div class="col-sm-6"><input type ="text" name="cvv[]" class="form-control" onkeypress="return isNumberKey(event)" id="cvv" maxlength="3" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Billing Address</strong></label><div class="col-sm-6"><input type ="text" name="billing_address[]" class="form-control "></div></div> <div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Bank Name</strong></label><div class="col-sm-6"><input type ="text" name="bank_name[]" class="form-control" ></div></div> <div style="cursor:pointer;background-color:red;" class="remove_field btn btn-info">Remove</div></div>'); //add input box
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
      e.preventDefault(); $(this).parent('.remove_test').remove(); x--;
    })
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
        $('#credit-card').trigger("change");
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
      $('input[type="file"]').trigger('click');
      $('input[type="file"]').change(function() {
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
            $("#reason_for_inactive").addClass('hidden').prop('required', false);
          }
        }
        if(this.value == 'inactive'){
          $("#reason_for_inactive").removeClass('hidden').prop('required', true);
        }
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
            swal("Cancelled", "Please Select State", "error");
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
                alert("Not an image");
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


    $('#last_name, #name, #bank_name, #company_name, #recipient_name, #job_title').on('keyup onmouseout keydown keypress blur change', function (event) {
      var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
      var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
      if (!regex.test(key)) {
        event.preventDefault();
        return false;
      }
    });

    $('#amount, #contact_number, #company_size').on('keyup onmouseout keydown keypress blur change', function (event) {
      var regex = new RegExp("^[0-9 ._\\b\\t]+$");
      var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
      if (!regex.test(key)) {
        event.preventDefault();
        return false;
      }

    });

    $('#cvv, .cvv').on('keyup onmouseout keydown keypress blur change', function (e) {
      var regex = new RegExp("^[0-9 ._\\b\\t]+$");
      var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
      if (!regex.test(key)) {
        return false;
      }
    });

    $("#user_signup_as").on('change', function () {
      var val = $(this).val();
      if(val === 'company'){
        $(".company_detail").removeClass('hidden');
        $(".first_name_block, .last_name_block").addClass('hidden');
        $('#company_name, #recipient_name, #job_title, #company_size, #website').prop('required', true);
      }else if(val === 'individual' || val === ''){
        if(!$(".company_detail").hasClass('hidden')){
          $(".company_detail").addClass('hidden');
          $('#company_name, #recipient_name, #job_title, #company_size, #website').prop('required', false);
        }
        if($(".first_name_block, .last_name_block").hasClass('hidden')){
          $(".first_name_block, .last_name_block").removeClass('hidden');
        }
      }
    }).addClass('form-control');

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
        if (password.length > 10) score++;
        // display indicator
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
    <script src="{{asset('assets/admin/js/intlTelInput.js')}}"  type="text/javascript"></script>
    @if(\Request::route()->getName() == 'admin.edit')
    <script>

      $(".tel_no").intlTelInput({
        placeholderNumberType: "MOBILE",
        preferredCountries: ['us'],
        separateDialCode: true,
      });
      var currentcode = $('#currentcode').val();
      if(currentcode){
        $(".tel_no").intlTelInput("setCountry", currentcode); 
      }else{
        $(".tel_no").intlTelInput("setNumber",$('.tel_no').attr('data-number'));
      }
      $(document).ready(function() 
      {
        var countryCode = $('.selected-dial-code').html();
        $("#countryCode").val(countryCode);
        var newcountryCode = countryCode.substring(1,countryCode.length);
        var country_code = $('ul.country-list').find("[data-dial-code='"+newcountryCode+"']");
        var code = country_code.attr('data-country-code');
        $("#countryCodeal").val(code);
        $('ul.country-list li').click(function(e)
        { 
          var countryCode =  $(this).attr('data-dial-code');
          var countryCodeal =  $(this).attr('data-country-code');
          $("#countryCode").val(countryCode);
          $("#countryCodeal").val(countryCodeal);
        });
      });
      function callback() {
        var codeVal = $('.selected-dial-code').html();
        var actualVal =  $("#countryCode").val();
        var newcountryCode = codeVal.substring(1,codeVal.length);

        var country_code = $('ul.country-list').find("[data-dial-code='"+newcountryCode+"']");
        var code = country_code.attr('data-country-code');
        $("#countryCodeal").val(code);
        if(actualVal != codeVal){
          $("#countryCode").val(codeVal);
        }
      }
      setInterval(callback, 1000);
    </script>
    @else
    <script>

      $(".tel_no").intlTelInput({
        placeholderNumberType: "MOBILE",
        preferredCountries: ['us'],
        separateDialCode: true,
      });


      $(document).ready(function() 
      {
    // $(".tel_no").intlTelInput("setCountry", "in"); 
    var countryCode = $('.selected-dial-code').html();
    $("#countryCode").val(countryCode);
    var newcountryCode = countryCode.substring(1,countryCode.length);
    var country_code = $('ul.country-list').find("[data-dial-code='"+newcountryCode+"']");
    var code = country_code.attr('data-country-code');
    $("#countryCodeal").val(code);
    $('ul.country-list li').click(function(e)
    { 
      var countryCode =  $(this).attr('data-dial-code');
      var countryCodeal =  $(this).attr('data-country-code');
      $("#countryCode").val(countryCode);
      $("#countryCodeal").val(countryCodeal);
    });
  });
      function callback() {
        var codeVal = $('.selected-dial-code').html();
        var actualVal =  $("#countryCode").val();
        var newcountryCode = codeVal.substring(1,codeVal.length);

        var country_code = $('ul.country-list').find("[data-dial-code='"+newcountryCode+"']");
        var code = country_code.attr('data-country-code');
        $("#countryCodeal").val(code);
        if(actualVal != codeVal){
          $("#countryCode").val(codeVal);
        }
      }
      setInterval(callback, 1000);
    </script> 
    @endif
    @endsection
