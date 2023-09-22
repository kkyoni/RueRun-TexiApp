@extends('admin.layouts.app')
@section('title')
	Mail Box
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Mail Box Management</h2>
	</div>
</div>

        
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12 animated fadeInRight">
            <div class="mail-box-header">
                <div class="float-right tooltip-demo">
                    <a href="{{ route('admin.mailbox') }}" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Back"><i class="fa fa-reply"></i> Back</a>
<a href="javascript:void(0)" class="btn btn-white btn-sm deletemail" data-id ="{{$notifications_list->id}}" data-toggle="tooltip" data-placement="top" title="Move to trash"><i class="fa fa-trash-o"></i> </a>
                </div>
                <h2>View Message</h2>
                <div class="mail-tools tooltip-demo m-t-md">
                    <h3>
                        <span class="font-normal">Subject: </span>{{$notifications_list->title}}
                    </h3>
                    <h5>
                        <span class="float-right font-normal">{{$notifications_list->created_at->diffForHumans()}}</span>
                        <span class="font-normal">From: </span>{{$notifications_list->user->email}}
                    </h5>
                </div>
            </div>
            <div class="mail-box">
                <div class="mail-body">
                    {{strip_tags($notifications_list->description)}}
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('styles')

@endsection
@section('scripts')
<script type="text/javascript">
    $(document).on("click","a.deletemail",function(e){
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
                    url:"{{route('admin.deletemail',[''])}}"+"/"+id,
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}"
                },
                success:function(msg){
                    if(msg.status == 'success'){
                        window.location.href = '{{route( "admin.mailbox" )}}';
                        //location.reload();
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
    </script>
@endsection