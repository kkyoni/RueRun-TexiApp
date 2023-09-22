@extends('admin.layouts.app')
@section('title')
Company Driver Listing
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		@if(\Request::route()->getName() == 'admin.company.companyDriver')
		<h2>Company Wise Driver Listing</h2>
		@else
		<h2>Company Listing</h2>
		@endif
	</div>
	<div class="col-lg-2">
	</div>
</div>

<div class="wrapper wrapper-content">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox ">
				<div class="ibox-content">
					@if(Auth::User()->user_type === 'superadmin')
					@if(\Request::route()->getName() == 'admin.company.companyDriver')
					<div class="col-md-12 text-right">
						<a class="btn btn-primary btn-sm pull-right mb-3 op-btn" href="{{route('admin.company.companyDriverCreate',$id)}}">
							<i class="icon-plus fa-fw">
							</i>
							Add Driver
						</a>
						<div class="clearfix"></div>
					</div>
					@else
					<div class="col-md-12 text-right">
						@php 
						$checkPermission = \App\Helpers\Helper::checkPermission(['company-driver-create']);
						@endphp
						@if($checkPermission)
						<a class="btn btn-primary btn-sm pull-right mb-3 op-btn" href="{{route('admin.company.create')}}">
							<i class="icon-plus fa-fw">
							</i>
							Add Company
						</a>
						<select class="col-md-2 mr-3 pull-right form-control country_id" onchange="window.location.href=this.options[this.selectedIndex].value;">
							<option value="{{ route('admin.index') }}">User</option>
							<option value="{{ route('admin.driver.index') }}">Driver</option>
							<option selected="selected" value="{{ route('admin.company.index') }}">Company</option>
						</select>
						@endif
						<div class="clearfix"></div>
					</div>
					@endif

					@endif
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
		//alert();
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
					url:"{{ route('admin.driver.change_status','replaceid') }}",
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
			} else {
				swal("Cancelled", "Your Status is safe :)", "error");
			}
		});
		return false;
	})
</script>
@endsection
