@extends('admin.layouts.app')
@section('title')
User Profile
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-4">
        <h2>User Profile</h2>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-md-6">
            {!! Form::open([
            'route' => 'admin.updateProfileDetail',
            'files' => true
            ]) !!}
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Profile update</h5>
                </div>

                <div class="ibox-content" id="imagePreview">
                   @if(!empty(\Auth::user()->avatar))
                   <img src="{!!  \Auth::user()->avatar !== '' ? url("storage/avatar/".\Auth::user()->avatar) : url('storage/default.png') !!}" alt="user-img" class="img-circle">
                   @else
                   <img src="{!! url('storage/avatar/default.png') !!}" name="avatar" alt="user-img" class="img-circle" accept="image/*">
                   @endif
                   <br>
                   <span >
                    <center>    <font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font> </center>
                </span>
            </div>
            {!! Form::file('avatar',['id' => 'hidden','accept'=>'image/*','class'=>'user_profile_pic']) !!}

            <div class="ibox-content profile-content">
                <div class="ibox ">
                    <div class="form-group row {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <label class="col-lg-12 col-form-label">First Name <span class="text-danger">*</span></label>
                        <div class="col-lg-12">
                            <input type="text" placeholder="First Name"  value="{{$user->first_name}}" id="first_name" name="first_name" class="form-control" maxlength="30" required>
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('first_name') ? "".$errors->first('first_name')."" : '' }} </font>
                            </span>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <label class="col-lg-12 col-form-label">Last Name <span class="text-danger">*</span></label>
                        <div class="col-lg-12">
                            <input type="text" placeholder="Last Name"  value="{{$user->last_name}}" id="last_name" name="last_name" class="form-control" maxlength="30" required>
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('last_name') ? "".$errors->first('last_name')."" : '' }} </font>
                            </span>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('contact_number') ? 'has-error' : '' }}">
                        <label class="col-lg-12 col-form-label">Contact Number<span class="text-danger">*</span></label>
                        <div class="col-lg-12">
                            <input type="text" placeholder="Contact Number"  value="{{$user->contact_number}}" id="contact_number" name="contact_number" class="form-control" required maxlength="10">
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
                            </span>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label class="col-lg-12 col-form-label">Email address <span class="text-danger">*</span></label>
                        <div class="col-lg-12">
                            <input type="text" placeholder="Email address"  value="{{$user->email}}" name="email" class="form-control" required>
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('email') ? "".$errors->first('email')."" : '' }} </font>
                            </span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-primary mr-10 mb-30">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::open([
        'route' => 'admin.updatePassword',
        'files' => true
        ]) !!}

        <div class="ibox">
            <div class="ibox-title">
                <h5>Change Password</h5>
            </div>
            <div class="ibox-content">
                <div class="form-group row {{ $errors->has('old_password') ? 'has-error' : '' }}"><label class="col-lg-4 col-form-label">Current Password <span class="text-danger">*</span></label>

                    <div class="col-lg-8"><input type="password" name="old_password" id="old_password" placeholder="Current Password" class="form-control" value="{{ old('old_password') }}">
                        <span class="help-blockk">
                            <font color="red"> {{ $errors->has('old_password') ? "".$errors->first('old_password')."" : '' }} </font>
                        </span>
                    </div>
                </div>
                <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}"><label class="col-lg-4 col-form-label">New Password <span class="text-danger">*</span></label>

                    <div class="col-lg-8"><input type="password" name="password" id="password" placeholder="New Password"  class="form-control">
                        <span class="help-blockk">
                            <font color="red"> {{ $errors->has('password') ? "".$errors->first('password')."" : '' }} </font>
                        </span>
                    </div>

                </div>
                <div class="form-group row {{ $errors->has('password_confirmation') ? 'has-error' : '' }}"><label class="col-lg-4 col-form-label">Confirm Password <span class="text-danger">*</span></label>

                    <div class="col-lg-8"><input type="password"  name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" class="form-control">
                       <span class="help-blockk">
                        <font color="red"> {{ $errors->has('password_confirmation') ? "".$errors->first('password_confirmation')."" : '' }} </font>
                    </span>

                </div>

            </div>
            <div class="form-group row">
                <div class="col-lg-offset-2 col-lg-10">
                    <button type="submit" class="btn btn-primary mr-10 mb-30">Save</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

        {!! Form::open([
        'route' => 'admin.sendAdminCommission',
        'files' => true
        ]) !!}

        <div class="ibox hidden">
            <div class="ibox-title">
                <h5>Pending Admin Commision to Transfer</h5>
            </div>
            <div class="ibox-content">
                <div class="form-group row ">
                    <label class="col-lg-7 col-form-label">Pending Admin Commision to Transfer <span class="text-danger"></span></label>

                    <div class="col-lg-8"><input type="text" name="" disabled id="admin_commision" placeholder="Admin Amount" class="form-control" value="{{$total_admin_pr}}">

                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-offset-2 col-lg-10">
                        <input type="hidden" name="admin_amount" id="admin_amount" placeholder="Admin Amount" class="form-control" value="{{$total_admin_pr}}">
                        <button type="submit" class="btn btn-primary mr-10 mb-30">Transfer To Wallet</button>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}

    </div>
</div>
</div>
</div>
@endsection
@section('styles')
<style type="text/css">
    .help-block {
        display: inline-block;
        margin-top: 5px;
        margin-bottom: 0px;
        margin-left: 8px;
    }
    .help-blockk{
     display: inline-block;
     margin-top:0px;
     margin-bottom: 0px;
     margin-left: 4px;
 }
</style>
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
    #imagePreview{
        width: 100%;
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
        right: -18px;
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

    $('#last_name, #first_name').on('keyup onmouseout keydown keypress blur change', function (event) {
        var regex = new RegExp("^[a-zA-Z ._\\b]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $('#amount, #contact_number').on('keyup onmouseout keydown keypress blur change', function (e) {
        var regex = new RegExp("^[0-9 ._\\b\\t]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $('#old_password, #password, #password_confirmation').on('keyup onmouseout keydown keypress blur change', function (e) {
        var key = e.charCode || e.keyCode || 0;

        if(($(this).val().length > 20)){
            $(this).val('');
            return false;
        }
    });
</script>
@endsection
