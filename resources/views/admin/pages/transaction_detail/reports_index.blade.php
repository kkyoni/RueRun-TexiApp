@extends('admin.layouts.app')
@section('title')
Reports
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Reports</h2>
    </div>
    <div class="col-lg-2">
    </div>
</div>

<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            {!! $html->table(['class' => 'table table-striped table-bordered dt-responsive'], true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="myModal4" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal_css">
        <div class="modal-content animated flipInY">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Transaction Details</h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>User </strong></td>
                            <td class="user_name"></td>
                            <td>|</td>
                            <td><strong>Driver </strong></td>
                            <td class="driver_name"></td>
                        </tr>
                        <tr>
                            <td><strong>Amount </strong></td>
                            <td class="amount"></td>
                            <td>|</td>
                            <td><strong>Admin Commision </strong></td>
                            <td class="admin_commision"></td>
                        </tr>
                        <tr>
                            <td><strong>Total Distance </strong></td>
                            <td class="total_km"></td>
                            <td>|</td>
                            <td><strong>Promo Code</strong></td>
                            <td class="promo_id"></td>
                        </tr>

                        <tr>
                            <td><strong>Pickup Location </strong></td>
                            <td class="pickup_location"></td>
                            <td>|</td>
                            <td><strong>Drop Location </strong></td>
                            <td class="drop_location"></td>
                        </tr>
                        <tr>
                            <td><strong>Booking Date </strong></td>
                            <td class="booking_date"></td>
                            <td>|</td>
                            <td><strong>Booking End Time </strong></td>
                            <td class="booking_end_time"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')

<style type="text/css">
   input, textarea, select, button, meter, progress {height: 2.05rem; width: 75px; display: inline-block; background-color: #FFFFFF; background-image: none; border: 1px solid #e5e6e7; border-radius: 1px; color: inherit; padding: 6px 12px; transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;}
</style>
<link rel="stylesheet" type="text/css"  href="{{ asset('new/jquery.dataTables.min.css') }}" />
<link rel="stylesheet" type="text/css"  href="{{ asset('new/buttons.dataTables.min.css') }}" />
@endsection
@section('scripts')
<script src="{{ asset('new/jszip.min.js') }}"></script>
<script src="{{ asset('new/pdfmake.min.js') }}"></script>
<script src="{{ asset('new/vfs_fonts.js') }}"></script>
<script src="{{ asset('new/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('new/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('new/buttons.flash.min.js') }}"></script>
<script src="{{ asset('new/buttons.html5.min.js') }}"></script>
<script src="{{ asset('new/buttons.print.min.js') }}"></script>
{!! $html->scripts() !!}
<script type="text/javascript">
    $(document).on("click","a.deleteuser",function(e){
        var row = $(this);
        var id = $(this).attr('data-id');
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this record",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e69a2a",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function(isConfirm){
            if (isConfirm) {
                $.ajax({
                    url:"{{route('admin.driver.delete',[''])}}"+"/"+id,
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}"
                },
                success:function(msg){
                    if(msg.status == 'success'){
                        location.reload();
                    }else{
                        swal("Warning!", msg.message, "warning");
                    }
                },
                error:function(){
                    swal("Error!", 'Error in delete Record', "error");
                }
            });
            } else {
                swal("Cancelled", "Your user is safe :)", "error");
            }
        });
        return false;
    })

    $(document).on("change","#changeDocStatus",function(e){
        var row = $(this);
        var id = $(this).attr('data-id');
        var value = $(this).val();

        swal({
            title: "Are you sure?",
            text: "You want's to update this record status ",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e69a2a",
            confirmButtonText: "Yes, updated it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function(isConfirm){
            if (isConfirm) {
                $.ajax({
                    url:"{{ route('admin.driverdoc.change_status','replaceid') }}",
                    type: 'post',
                    data: {"_method": 'post',
                    'id':id,
                    'status':value,
                    "_token": "{{ csrf_token() }}"
                },
                success:function(msg){
                    if(msg.status_code == 200){
                        swal("Warning!", msg.message, "warning");
                    }else{

                        location.reload();
                    }
                },
                error:function(){
                    swal("Error!", 'Error in updated Record', "error");
                }
            });
            } else {
                swal("Cancelled", "Your Status is safe :)", "error");
            }
        });
        return false;
    });



    $(document).on("click","a.get_trnsection_details",function(e){
        var row = $(this);
        var id = $(this).attr('data-id');
        $.ajax({
                url:"{{route('admin.transaction_info',[''])}}"+"/"+id,
                type: 'post',
                data: {"_token": "{{ csrf_token() }}"
            },
            success:function(data) {
                $('.amount').html(data.amount);
                $('.total_km').html(data.total_km);
                $('.user_id').html(data.user_id);
                $('.promo_id').html(data.promo_id);
                $('.promo_id').html(data.promo_id);
                $('.admin_commision').html(data.admin_commision);

                $('.pickup_location').html(data.pick_up_location);
                $('.drop_location').html(data.drop_location);
                $('.booking_date').html(data.booking_date);
                $('.booking_end_time').html(data.booking_end_time);
                $('.user_name').html(data.user_name);
                $('.driver_name').html(data.driver_name);
                $('#myModal4').modal('show');
            },
            error:function(){
                swal("Error!", 'Error in Not Get Record', "error");
            }
        });

    });

    $(document).on("click","a.get_parcel_details",function(e){
        var row = $(this);
        var id = $(this).attr('data-id');
        $.ajax({
            url:"{{route('admin.get_parcel_details',[''])}}"+"/"+id,
            type: 'post',
            data: {"_token": "{{ csrf_token() }}"
            },
            success:function(data) {
                $('.amount').html(data.amount);
                $('.total_km').html(data.total_km);
                $('.user_id').html(data.user_id);
                $('.promo_id').html(data.promo_id);
                $('.promo_id').html(data.promo_id);
                $('.admin_commision').html(data.admin_commision);

                $('.pickup_location').html(data.pick_up_location);
                $('.drop_location').html(data.drop_location);
                $('.booking_date').html(data.booking_date);
                $('.booking_end_time').html(data.booking_end_time);
                $('.user_name').html(data.user_name);
                $('.driver_name').html(data.driver_name);
                $('#myModal4').modal('show');
            },
            error:function(){
                swal("Error!", 'Error in Not Get Record', "error");
            }
        });
        //swal("Deleted!", "Operator has been deleted.", "success");

    });

    $(document).on('change','.find_date',function (event) {
        window.LaravelDataTables["dataTableBuilder"].ajax.reload();
    });


    var mem = $('#data_1 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true
    });

    var yearsAgo = new Date();
    yearsAgo.setFullYear(yearsAgo.getFullYear() - 20);

    $('#selector').datepicker('setDate', yearsAgo );


    $('#data_2 .input-group.date').datepicker({
        startView: 1,
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: "dd/mm/yyyy"
    });

    $('#data_3 .input-group.date').datepicker({
        startView: 2,
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true
    });

    $('#data_4 .input-group.date').datepicker({
        minViewMode: 1,
        keyboardNavigation: false,
        forceParse: false,
        forceParse: false,
        autoclose: true,
        todayHighlight: true
    });

    $('#data_5 .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true
    });

</script>
@endsection
