<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
    Sub-Admin Permission Management - Edit
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
            <h2>Edit Sub-Admin Permission</h2>
        </div>
    </div>
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                    </div>
                    <div class="ibox-content">
                        {!!Form::model($permissions,array('method'=>'post','files'=>true,'route'=>array('admin.subadmin.permission_update',$permissions->id)))!!}

                        <div class="form-group  row {{ $errors->has('module_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Module Name</strong> <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                {!! Form::text('module_name',null,['class' => 'form-control','id'=> 'module_name','readonly']) !!}
                                <span class="help-block">
                                    <font color="red"> {{ $errors->has('module_name') ? "".$errors->first('module_name')."" : '' }} </font>
                                </span>
                            </div>
                        </div>

                        <br><hr>
                        <div class="form-group row {{ $errors->has('view') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label">
                                <strong>View</strong> <span class="text-danger">*</span></label>
                            <div class="col-sm-3">
                                @if($permissions->view === '1')
                                    @php($value='1')
                                    @php($checked='checked')
                                @else
                                    @php($value='0')
                                    @php($checked='')
                                @endif
                                <input name="view" {{$checked}} type="checkbox" value="{{ $value}}" id="view">

                                <span class="help-block">
                                    <font color="red"> {{ $errors->has('view') ? "".$errors->first('view')."" : '' }} </font>
                                </span>
                            </div>
                        </div>

                        <div class="form-group  row {{ $errors->has('add') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label">
                                <strong>Add</strong> <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                @if($permissions->add === '1')
                                    @php($value='1')
                                    @php($checked='checked')
                                @else
                                    @php($value='0')
                                    @php($checked='')
                                @endif
                                <input name="add" {{$checked}} type="checkbox" value="{{ $value}}" id="add">

                                <span class="help-block">
                                    <font color="red"> {{ $errors->has('add') ? "".$errors->first('add')."" : '' }} </font>
                                </span>
                            </div>
                        </div>

                        <div class="form-group  row {{ $errors->has('edit') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label">
                                <strong>Edit</strong> <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                @if($permissions->edit === '1')
                                    @php($value='1')
                                    @php($checked='checked')
                                @else
                                    @php($value='0')
                                    @php($checked='')
                                @endif
                                <input name="edit" {{$checked}} type="checkbox" value="{{ $value}}" id="edit">
                                <span class="help-block">
                                    <font color="red"> {{ $errors->has('edit') ? "".$errors->first('edit')."" : '' }} </font>
                                </span>
                            </div>
                        </div>

                        <div class="form-group  row {{ $errors->has('delete') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label">
                                <strong>Delete</strong> <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                @if($permissions->delete === '1')
                                    @php($value='1')
                                    @php($checked='checked')
                                @else
                                    @php($value='0')
                                    @php($checked='')
                                @endif
                                <input name="delete" {{$checked}} type="checkbox" value="{{ $value}}" id="delete">
                                <span class="help-block">
                                    <font color="red"> {{ $errors->has('delete') ? "".$errors->first('delete')."" : '' }} </font>
                                </span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group row">
                                <div class="col-sm-8 col-sm-offset-8">
                                    <a href="{{route('admin.subadmin.permission_index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a>
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

    <script>
        $(document).ready(function () {
            $('#view, #add, #edit, #delete'). change(function() {
                if ($(this).is(":checked")) {
                    $(this).val('1');
                } else {
                    $(this).val('0');
                }
            });
        });

        $('#last_name, #first_name, #bank_name').on('keyup onmouseout keydown keypress blur change', function (event) {
            var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
                event.preventDefault();
                return false;
            }
        });

    </script>

@endsection


