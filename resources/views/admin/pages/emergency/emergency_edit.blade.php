<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
Emergency Type - Edit
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
        <h2>Edit Emergency Type</h2>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                </div>
                <div class="ibox-content">
                    {!!Form::model($emergency,array('method'=>'POST','files'=>true,'route'=>array('admin.emergencytypeupdate',$emergency->id)))!!}
                    <div class="form-group  row {{ $errors->has('type_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Emergency Type Name</strong> <span class="text-danger">*</span></label>
                        <div class="col-sm-6">{!! Form::text('type_name',null,[
                            'class' => 'form-control',
                            'id'	=> 'type_name', 'readonly'
                            ]) !!}
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('type_name') ? "".$errors->first('type_name')."" : '' }} </font>
                            </span>
                        </div>
                    </div>

                    <div class="form-group  row {{ $errors->has('contact_number') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Contact Number</strong> <span class="text-danger">*</span></label>
                        <div class="col-sm-6">{!! Form::text('contact_number',null,[
                            'class' => 'form-control',
                            'id'	=> 'contact_number',
                            'maxlength'    =>  '12'
                            ]) !!}
                            <span class="help-block">
                                <font color="red"> {{ $errors->has('contact_number') ? "".$errors->first('contact_number')."" : '' }} </font>
                            </span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group row">
                            <div class="col-sm-8 col-sm-offset-8">
                                <a href="{{route('admin.emergencytypes')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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
@section('scripts')
<script type="text/javascript">
    $('#contact_number').on('keyup onmouseout keydown keypress blur change', function (event) {
        var regex = new RegExp("^[0-9 ._\\b\\t]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

</script>
@endsection

