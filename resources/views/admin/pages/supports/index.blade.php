@extends('admin.layouts.app')
@section('title')
Support Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Support Management</h2>
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
				<h4 class="modal-title">Admin Comment Add</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12">
					<div class="chat-discussion">
						<div class="commment"></div>
					</div>
				</div>
				<div  class="myDivClass" id="id_admin" style=""></div>
				<!-- <input type="hidden"  name="id_admin" id="id_admin" class="id_admin_send"> -->

				<br>
				<textarea class="form-control" id="add_comment_admin" name="add_comment_admin" cols="30" rows="1"></textarea>
				<br>
				<div class="col-sm-8 col-sm-offset-8">
					<a href="#"><button class="btn btn-danger btn-sm" type="button" data-dismiss="modal">Close</button></a>
					<button class="btn btn-primary btn-sm add_comment" type="submit">Save</button>
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

	.modal_css{
		max-width: 700px;
		margin: 1.75rem auto;
	}
	.myDivClass{

		display: none;
	}
</style>

@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
{!! $html->scripts() !!}
<script type="text/javascript">


	$(document).on("change",".changeDocStatus",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		var status = $(this).val();
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
					url:"{{ route('admin.support.changestatus') }}",
					type: 'post',
					data: {"_method": 'post',
					'id':id,
					'status':status,
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
				//swal("Cancelled", "Your Status is safe :)", "error");
				location.reload();
			}
		});
		return false;
	})


</script>
<script type="text/javascript">
	$(document).on("click","a.open_modal",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$('#id_admin').html(id);

		$.ajax({
			url:"{{route('admin.add_comment_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('.commment').html(data.data);
			console.log(data);

			$('#myModal4').modal('show');

		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});

	})



	$(document).on("click",".add_comment",function(e){
		var row = $(this);
		var id = $('#id_admin').text();
		var add_comment_admin = $('#add_comment_admin').val();
		$.ajax({
			url:"{{ route('admin.add_comment_admin') }}",
			type: 'post',
			data: {"_method": 'post',
			'id':id,
			'add_comment_admin':add_comment_admin,
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
	})
	});

	$('#myModal4').on('hidden.bs.modal', function () {
		location.reload();
	})

</script>

@endsection
