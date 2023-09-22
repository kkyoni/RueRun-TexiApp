<!-- admin user update -->
@extends('admin.layouts.app')

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
            <h2>Create Bank Office Level</h2>
        </div>
    </div>
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                    </div>
                    <div class="ibox-content">
                        {!! Form::open(array(
                            'route' => ['admin.role.store'],
                            'id'    => 'userCreateForm',
                            'files' => 'true')) !!}
                        <div class="">
                            <div class="form-group  row {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label class="col-sm-3 col-form-label"><strong>Bank Official Level</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-6">
                                    {!! Form::text('name',null,[
                                    'class' => 'form-control',
                                    'id'	=> 'name'
                                    ]) !!}
                                    <span class="help-block">
                                        <font color="red"> {{ $errors->has('name') ? "".$errors->first('name')."" : '' }} </font>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group  row {{ $errors->has('email') ? 'has-error' : '' }}">
                                <label class="col-sm-3 col-form-label"><strong>Email</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-6">
                                    {!! Form::text('email',null,[
                                    'class' => 'form-control',
                                    'id'	=> 'email'
                                    ]) !!}
                                    <span class="help-block">
                                        <font color="red"> {{ $errors->has('email') ? "".$errors->first('email')."" : '' }} </font>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group  row {{ $errors->has('password') ? 'has-error' : '' }}">
                                <label class="col-sm-3 col-form-label"><strong>Password</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-6">
                                    {!! Form::password('password',[
                                    'class' => 'form-control',
                                    'id'	=> 'password'
                                    ]) !!}
                                    <span class="help-block">
                                        <font color="red"> {{ $errors->has('password') ? "".$errors->first('password')."" : '' }} </font>
                                    </span>
                                </div>
                            </div>

                            <div class="formform-group row {{ $errors->has('status') ? 'has-error' : '' }}"><label
                                    class="col-sm-3 col-form-label"><strong>Status</strong></label>
                                <div class="col-sm-6 inline-block">
                                    <div class="i-checks">
                                        <label>
                                            {{ Form::radio('status', 'block' ,['id'=> 'block']) }} <i></i> Block
                                        </label>
                                    </div>
                                    <div class="i-checks">
                                        <label>
                                            {{ Form::radio('status', 'active' ,['id' => 'active']) }}
                                            <i></i> Active
                                        </label>
                                    </div>
                                    <span class="help-block">
			                            <font
                                            color="red"> 	{{ $errors->has('status') ? "".$errors->first('status')."" : '' }} </font>
		                            </span>
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
