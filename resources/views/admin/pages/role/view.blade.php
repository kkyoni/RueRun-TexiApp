<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
Role Management - View
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
        <h2>{{ $bol->first_name }} View Permission</h2>
    </div>
    <div class="col-lg-2">

    </div>
</div>


<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="container">

                        <div class="row wrapper border-bottom white-bg page-heading" style="background-color:#ffffff;    padding: 0px 0px 20px 0px;">
                            <div class="col-lg-12">
                                <form action="">
                                    Back Office Level  {{ Form::select('bank_office_level',$bank_office_level, $bol->id, array('id' => 'some-id','class' => 'form-control','onchange' => 'changeBankOfficeLevel(this)')) }}
                                </form>
                            </div>
                        </div>
                        <!-- <h2 id="Operator_role" class="text-center">{{ $bol->first_name }} Permission List</h2> -->

                        <input type="hidden" id="role_id" name="role_id" value="{{ $bol->role_id }}">
                        <table class="table ">
                            <thead>
                                <tr>
                                    <th>Module name</th>
                                    <th>List</th>
                                    <th>Add</th>
                                    <th>Edit</th>
                                    <th>Delete</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($permission as $key=>$permissions)
                                    <tr>
                                        <td>{{$key}}</td>
                                        @foreach($permissions as $perm)
                                        <td>
                                            @if(in_array($perm->name,array_column($user_permission,'name')))
                                            <label class="i-checks">
                                                <input type="checkbox" disabled
                                                class="iCheck-helper" checked
                                                name="permission[]" value="{{ $perm->id }}"/>
                                            </label>
                                            @else
                                            <label class="i-checks">
                                                <input type="checkbox" disabled
                                                class="iCheck-helper"
                                                value="{{ $perm->id }}"
                                                name="permission[]"/>
                                            </label>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                        <div class="hr-line-dashed"></div>
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <div class="col-sm-8 col-sm-offset-8">

                                    <a href="{{route('admin.role.index')}}"><button class="btn btn-danger btn-sm" type="button">Back to List</button></a>

                                </div>
                            </div>
                        </div>  
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<style type="text/css">
    #some-id{
        width: 20%
    }
    td {
        border-top: none !important;
    }
</style>
<script>
    function changeBankOfficeLevel(e) {
        var selectedBankOfficeLevel = $('#some-id').children("option:selected").val();
        $('#bank_office_level_id').val(selectedBankOfficeLevel)
        var selectedBankOfficeLevelName = $('#some-id').children("option:selected").text();
        $('#Operator_role').text(selectedBankOfficeLevelName+' Role Permission Table')
        var text = '/admin/role/'+selectedBankOfficeLevel+'/view';
        var url = "{{ url('/') }}"+text;
        window.location.href=url;
    }

    $(document).ready(function () {

        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        $('#bank_office_level_id').val({{ $bol->id }})
    });
</script>
@endsection
@endsection
