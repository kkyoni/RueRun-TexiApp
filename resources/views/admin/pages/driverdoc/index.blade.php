@extends('admin.layouts.app')
@section('title')
Driver Document Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Driver Document Management</h2>
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
						<!-- <a class="btn btn-success  pull-right mb-3 op-btn" href="{{route('admin.driver.create')}}">
							<i class="icon-plus fa-fw">
							</i>
							Add Driver
						</a> -->
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
				<h4 class="modal-title">Driver Documents</h4>
			</div>
			<div class="modal-body">
				<div class="commmentt"></div>
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
			cancelButtonText: "No, cancel plx!",
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
				location.reload();
			}
		});
		return false;
	})


	$(document).on("click","a.get_user_doc",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.user_doc',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('#myModal4').modal('show');
			$('.commmentt').html(data.data);
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});

	$(document).on("click","a.get_vehicle_doc",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.vehicle_doc',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('#myModal4').modal('show');
			$('.commmentt').html(data.data);
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});
</script>
@endsection
