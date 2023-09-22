@extends('admin.layouts.app')
@section('title')
Parcel Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Parcel Management</h2>
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
							<option value="{{ route('admin.tripindex') }}">Booking</option>
							<option selected="selected" value="{{ route('admin.parcels.index') }}">Parcel Booking</option>
							<option value="{{ route('admin.lineride.index') }}">Shuttle Ride Booking</option>
							</select>
							{!! $html->table(['class' => 'table table-striped table-bordered dt-responsive'], true) !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<div class="modal inmodal" id="myModal2" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Driver Details</h4>
			</div>
			<div class="modal-body">
				<table class="table ">
					<tbody>
						<tr>
							<td><strong>First Name  </strong></td>
							<td class="fname_user"></td>
							<td>|</td>
							<td><strong>Last Name </strong></td>
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
							<td></td>
							{{--							<td><strong>Gender  :-</strong></td>--}}
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

<div class="modal inmodal" id="myModal3" tabindex="-1" role="dialog" aria-hidden="true">
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
							<td><strong>First Name  </strong></td>
							<td class="fname_user"></td>
							<td>|</td>
							<td><strong>Last Name </strong></td>
							<td class="lname_user"></td>
						</tr>
						<tr>
							<td><strong>Email address </strong></td>
							<td class="email_add"></td>
							<td>|</td>
							<td><strong>Status </strong></td>
							<td class="status"></td>
						</tr>
						<tr>
							<td><strong>Contact Number </strong></td>
							<td class="c_number_user"></td>
							{{--							<td>|</td>--}}
							{{--							<td><strong>Gender :-</strong></td>--}}
							{{--							<td class="gender"></td>--}}
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


<div class="modal inmodal" id="myModal4" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Parcel Details</h4>
			</div>
			<div class="modal-body">
				<table class="table">
					<tbody>
						<tr>
							<td><strong>Driver Name </strong></td>
							<td class="driver"></td>
							<td>|</td>
							<td><strong>User Name</strong></td>
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
							<td><strong>End Time</strong></td>
							<td class="end_time"></td>
						</tr>
						<tr>
                            <td><strong>Transaction Id  </strong></td>
                            <td class="transaction_id"></td>
                            <td>|</td>
							<td><strong>Base Fare  </strong></td>
							<td class="base_fare"></td>
						</tr>
						<tr>
                            <td><strong>Promo Amount </strong></td>
                            <td class="promo_amount"></td>
							<td>|</td>
							<td><strong>Trip Status </strong></td>
							<td class="trip_status"></td>
						</tr>
						<tr>
							<td><strong>Total Amount </strong></td>
							<td>$<label class="total_amount"></label></td>
							<td>|</td>
                            <td><strong>Total Distance  </strong></td>
                            <td class="total_km"></td>
						</tr>
					</tbody>
				</table>
                <table class="table packages">
                    <tbody>
                    <tr class="">
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

<div class="modal inmodal" id="myModalImage" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Parcel Images</h4>
			</div>
			<div class="modal-body">
				<div class="parcelimage_block"></div>
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
					url:"{{route('admin.parcels.delete',[''])}}"+"/"+id,
					type: 'post',
					data: {"_token": "{{ csrf_token() }}"},
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


	$(document).on("click","a.show_driver",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.parcels.parcel_driver_info',[''])}}"+"/"+id,
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

			$('#myModal2').modal('show');
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});

	$(document).on("click","a.show_user",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.parcels.parcel_user_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('.fname_user').html(data.first_name);
			$('.email_add').html(data.email_add);
			$('.lname_user').html(data.last_name);
			$('.status').html(data.status);
			$('.c_number_user').html(data.contact_number);
			$('.gender').html(data.gender);

			$('#myModal3').modal('show');
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});

	$(document).on("click","a.show_info",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$('.packages').html('');
		$.ajax({
			url:"{{route('admin.parcels.show_info',[''])}}"+"/"+id,
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
			$('.total_km').html(data.total_distance);
			$('.admin_commision').html(data.admin_commision);
			$('.transaction_id').html(data.transaction_id);
			$('.trip_status').html(data.trip_status);
			$('.extra_notes').html(data.extra_notes);
			$('.promo_name').html(data.promo_name);
			$('.promo_amount').html(data.promo_amount);
			$('.total_amount').html(data.total_amount);
			$('.p_length').html(data.parcel_length);
			$('.p_type').html(data.package_type);
			$('.p_deep').html(data.parcel_deep);
			$('.p_weight').html(data.parcel_weight);

			var html='';
            if(data.parcel_packages.length > 0){
                $.each(data.parcel_packages, function(key,val) {
                    html += '<tbody><tr><td><p><strong>Package Type:</strong>  '+val.package_type+'</p></td>|' +
                        '<td><p><strong>Package Length:</strong> '+val.parcel_length+'</p></td>|' +
                        '<td><p><strong>Package Deep:</strong> '+val.parcel_deep+'</p></td>|'+
                        '<td><p><strong>Package Height:</strong> '+val.parcel_height+'</p></td>|'+
                        '<td><p><strong>Amount:</strong> '+val.total_amount+'</p></td>|'+
                        '</tr></tbody>';
                });
            }
            $('.packages').html(html);

			$('#myModal4').modal('show');
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});


	$(document).on("click","a.show_parcelimages",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.parcels.parcel_images',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			$('#myModalImage').modal('show');
			$('.parcelimage_block').html(data.data);
		},
		error:function(){
			swal("Error!", 'Error in Not Get Record', "error");
		}
	});
	});

</script>
@endsection
