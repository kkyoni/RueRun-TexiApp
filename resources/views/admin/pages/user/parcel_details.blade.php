@extends('admin.layouts.app')
@section('title')
    User Trip Details
@endsection
@section('mainContent')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>User Parcel Booking Details </h2>
        </div>
        <div class="col-lg-2">
        </div>
    </div>

    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="col-md-12 text-right">
                            <div class="clearfix"></div>
                        </div>
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
                    <h4 class="modal-title">User Parcel Details</h4>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td><strong>Driver Name </strong></td>
                            <td class="driver"></td>
                            <td>|</td>
                            <td><strong>User Name </strong></td>
                            <td class="user"></td>
                        </tr>
                        <tr>
                            <td><strong>PickUp Location </strong></td>
                            <td class="pick_up_location"></td>
                            <td>|</td>
                            <td><strong>Drop Location </strong></td>
                            <td class="drop_location"></td>
                        </tr>
                        <tr>
                            <td><strong>Start Time </strong></td>
                            <td class="start_time"></td>
                            <td>|</td>
                            <td><strong>End Time </strong></td>
                            <td class="end_time"></td>
                        </tr>
                        <tr>
                            <td><strong>Parcel Trip Status </strong></td>
                            <td class="trip_status"></td>
                            <td>|</td>
                            <td><strong>Total Amount </strong></td>
                            <td>$<label class="total_amount"></label></td>
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
        table.dataTable {
            clear: both;
            margin-top: 6px !important;
            margin-bottom: 6px !important;
            max-width: none !important;
            border-collapse: separate !important;
            width: 100% !important;
        }
        .op-btn{
            margin-right:22px;
        }
        .modal_css{
            max-width: 700px;
            margin: 1.75rem auto;
        }

    </style>

@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
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
                cancelButtonText: "No, cancel plx!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm){
                if (isConfirm) {
                    $.ajax({
                        url:"{{route('admin.delete',[''])}}"+"/"+id,
                        type: 'post',
                        data: {"_token": "{{ csrf_token() }}"
                        },
                        success:function(msg){
                            if(msg.status == 'success'){
                                location.reload();
                            }else{
                                swal("Warning!", msg.message, "warning");
                                //swal("Deleted!",  msg.message, "success");

                            }
                        },
                        error:function(){
                            swal("Error!", 'Error in delete Record', "error");
                        }
                    });
                    //swal("Deleted!", "Operator has been deleted.", "success");

                } else {
                    swal("Cancelled", "Your user is safe :)", "error");
                }
            });
            return false;
        })

        $(document).on("click",".changeStatusRecord",function(e){
            var row = $(this);
            var id = $(this).attr('data-id');
            swal({
                title: "Are you sure?",
                text: "You want's to update this record status ",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e69a2a",
                confirmButtonText: "Yes, updated it!",
                cancelButtonText: "No, cancel plx!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm){
                if (isConfirm) {
                    $.ajax({
                        url:"{{ route('admin.change_status','replaceid') }}",
                        type: 'post',
                        data: {"_method": 'post',
                            'id':id,
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
                    //swal("Updated!", "Status has been updated.", "success");

                } else {
                    swal("Cancelled", "Your Status is safe :)", "error");
                }
            });
            return false;
        })


        $(document).on("click","a.showparceldetail",function(e){
            var row = $(this);
            var id = $(this).attr('data-id');
            $.ajax({
                url:"{{route('admin.u_booking_info',[''])}}"+"/"+id,
                type: 'post',
                data: {"_token": "{{ csrf_token() }}"
                },
                success:function(data) {

                    $('.driver').html(data.driver);
                    $('.user').html(data.user);
                    $('.pick_up_location').html(data.pick_up_location);
                    $('.drop_location').html(data.drop_location);
                    $('.start_time').html(data.start_time);
                    $('.end_time').html(data.end_time);
                    $('.hold_time').html(data.hold_time);
                    $('.base_fare').html(data.base_fare);
                    $('.total_km').html(data.total_km);
                    $('.admin_commision').html(data.admin_commision);
                    $('.transaction_id').html(data.transaction_id);
                    $('.trip_status').html(data.parcel_status);
                    $('.extra_notes').html(data.extra_notes);
                    $('.promo_name').html(data.promo_name);
                    $('.promo_amount').html(data.promo_amount);
                    $('.total_amount').html(data.total_amount);
                    $('.promo_id').html(data.promo_id);
                    $('.booking_date').html(data.booking_date);

                    $('#myModal4').modal('show');

                },
                error:function(){
                    swal("Error!", 'Error in Not Get Record', "error");
                }
            });
            //swal("Deleted!", "Operator has been deleted.", "success");

        });
    </script>
@endsection
