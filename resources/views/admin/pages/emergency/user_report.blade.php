@extends('admin.layouts.app')
@section('title')
    User Report Management
@endsection
@section('mainContent')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>User Report Management</h2>
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
                    <h4 class="modal-title">User Report Description</h4>
                </div>
                <div class="modal-body">
                    <div class="description"></div>
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

        @media screen and (min-width: 768px) {
            .modal-dialog {
                width: 700px; /* New width for default modal */
            }
            .modal-sm {
                width: 350px; /* New width for small modal */
            }
        }
        @media screen and (min-width: 992px) {
            .modal-lg {
                width: 950px; /* New width for large modal */
            }
        }
    </style>

@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    {!! $html->scripts() !!}
    <script type="text/javascript">
        $(document).on("click","a.get_reports",function(e){
            var row = $(this);
            var id = $(this).attr('data-id');
            $('.description').text('');
            $.ajax({
                url:"{{route('admin.get_reports',[''])}}"+"/"+id,
                type: 'get',
                data: {"_token": "{{ csrf_token() }}"
                },
                success:function(data) {
                    $('.description').text(data.description);
                    $('#myModal4').modal('show');
                },
                error:function(){
                    swal("Error!", 'Error in Not Get Record', "error");
                }
            });
        });
    </script>
@endsection
