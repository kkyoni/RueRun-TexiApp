@extends('admin.layouts.app')
@section('title')
Promocode Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Join Network List</h2>
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
						@php 
						$checkPermission = \App\Helpers\Helper::checkPermission(['promocode-create']);
						@endphp
						@if($checkPermission)
						<!-- <a class="btn btn-primary btn-sm pull-right mb-3 op-btn" href="{{route('admin.promocode.create')}}">
							<i class="icon-plus fa-fw">
							</i>
							Add Owner
						</a> -->
						@endif
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
		<div class="modal-content animated flipInY" style="width: 854px;margin-left: 242px;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Owner Details</h4>
			</div>
			<div class="modal-body data">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal" id="myModal5" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Referral Earning Details</h4>
			</div>
			<div class="modal-body earnData">

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
</style>

@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
{!! $html->scripts() !!}
<script type="text/javascript">

$(document).on("click","a.get_ref_detail",function(e){
		
		var row = $(this);
		var id = $(this).attr('data-id');

		$.ajax({
			url:"{{route('admin.joinreferral_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('.data').html(data.html);
			$('#myModal4').modal('show');
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
				//swal("Deleted!", "Operator has been deleted.", "success");

    });

$(document).on("click","a.earning_detail",function(e){
		var row = $(this);
		var id = $(this).attr('data-rid');

		$.ajax({
			url:"{{route('admin.earn_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('.earnData').html(data.html);
			$('#myModal4').modal('hide');
			$('#myModal5').modal('show');
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
				//swal("Deleted!", "Operator has been deleted.", "success");

			});


	$(document).on("click","a.deletepromocode",function(e){
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
					url:"{{route('admin.promocodedelete',[''])}}"+"/"+id,
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
			cancelButtonText: "No, cancel please!",
			closeOnConfirm: false,
			closeOnCancel: false
		}, function(isConfirm){
			if (isConfirm) {
				$.ajax({
					url:"{{ route('admin.statusupdate','replaceid') }}",
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
