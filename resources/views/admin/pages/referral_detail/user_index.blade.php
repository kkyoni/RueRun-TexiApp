@extends('admin.layouts.app')
@section('title')
Referral Details
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>User Referral Detail</h2>
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
							<select class="col-md-2 mr-3 pull-right form-control country_id" onchange="window.location.href=this.options[this.selectedIndex].value;">
							<option selected="selected" value="{{ route('admin.user_ref.index') }}">User Referral</option>
							<option value="{{ route('admin.driver_ref.index') }}">Driver Referral</option>
							</select>
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
				<h4 class="modal-title">User Referral Details</h4>
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

<div class="modal inmodal" id="myModal2" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">User Details</h4>
			</div>
			<div class="modal-body">
				<table class="table ">
					<tbody>
						<tr>
							<td><strong>First Name  <strong></td>
							<td class="fname_user"></td>
							<td>|</td>
							<td><strong>Last Name  </strong></td>
							<td class="lname_user"></td>
						</tr>
						<tr>
							<td><strong>Email address </strong></td>
							<td class="email_add_user"></td>
							<td>|</td>
							<td><strong>Status </strong></td>
							<td class="status_user"></td>
						</tr>
						<tr>
							<td><strong>Contact Number </strong></td>
							<td class="c_number_user"></td>
							<td>|</td>
							{{--							<td><strong>Gender :-</strong></td>--}}
							{{--							<td class="gender_user"></td>--}}
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

	$(document).on("click","a.get_ref_detail",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');

		$.ajax({
			url:"{{route('admin.referral_info',[''])}}"+"/"+id,
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



	$(document).on("click","a.user_show",function(e){

		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.get_user_info_data',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('.fname_user').html(data.first_name);
			$('.email_add_user').html(data.email_add);
			$('.lname_user').html(data.last_name);
			$('.status_user').html(data.status);
			$('.c_number_user').html(data.contact_number);
			$('.gender_user').html(data.gender);
			console.log(data);
			$('#myModal2').modal('show');

		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
				//swal("Deleted!", "Operator has been deleted.", "success");

    });
</script>
@endsection
