@extends('admin.layouts.app')
@section('title')
    Sub-Admin Permission
@endsection
@section('mainContent')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Sub-Admin Permission Management</h2>
        </div>
        <div class="col-lg-2"></div>
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
    </style>

@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    {!! $html->scripts() !!}
    <script type="text/javascript">

        $(document).on("click",".changeStatusRecord",function(e){
            var row = $(this);
            var id = $(this).attr('data-id');
            swal({
                title: "Are you sure?",
                text: "You wants to update this record status ",
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
                        url:"{{ route('admin.subadmin.change_status','replaceid') }}",
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
    </script>
@endsection
