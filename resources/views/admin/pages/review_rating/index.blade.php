@extends('admin.layouts.app')
@section('title')
Review And Rating Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Review And Rating Management</h2>
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
				<h4 class="modal-title">Booking Details</h4>
			</div>
			<div class="modal-body">
				<table class="table">
					<tbody>
						<tr>
							<td><strong>Driver Name <a href="#" data-value="1" class="driver_id" data-id=""><i class="fa fa-eye"></i></a></strong></td>
							<td class="driver"></td>
							<td>|</td>
							<td><strong>User name <a href="#" data-value="1" class="user_id" data-id=""><i class="fa fa-eye"></i></a></strong></td>
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
							<td><strong>Base Fare </strong></td>
							<td class="base_fare"></td>
							<td>|</td>
							<td><strong>Booking Date </strong></td>
							<td class="booking_date"></td>
						</tr>
						<tr>
							<td><strong>Total Km </strong></td>
							<td class="total_km"></td>
							{{--							<td>|</td>--}}
							{{--							<td><strong>Admin Commision  :-</strong></td>--}}
							{{--							<td class="admin_commision"></td>--}}
						</tr>
						<tr>
							<td><strong>Promo Amount </strong></td>
							<td class="promo_amount"></td>
							<td>|</td>
							<td><strong>Promo Name </strong></td>
							<td class="promo_name"></td>
						</tr>
						<tr>
							<td><strong>Extra Notes </strong></td>
							<td class="extra_notes"></td>
							<td>|</td>
							<td><strong>Booking Status </strong></td>
							<td class="trip_status"></td>
						</tr>

						<tr>
							<td><strong>Total Amount </strong></td>
							<td class="total_amount"></td>
							<td>|</td>
							<td><strong>Comment </strong></td>
							<td class="Comments"></td>
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

<div class="modal inmodal" id="myModal45" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Driver Details</h4>
			</div>
			<div class="modal-body">
				<table class="table">
					<tbody>
						<tr>
							<td><strong>First Name </strong></td>
							<td class="first_name"></td>

							<td>|</td>
							<td><strong>Email </strong></td>
							<td class="email"></td>
						</tr>
						<tr>
							<td><strong>Contact number</strong></td>
							<td class="contact_number"></td>
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




<div class="modal inmodal" id="myModal46" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">User Details</h4>
			</div>
			<div class="modal-body">
				<table class="table">
					<tbody>
						<tr>
							<td><strong>First Name </strong></td>
							<td class="first_name"></td>

							<td>|</td>
							<td><strong>Email </strong></td>
							<td class="email"></td>
						</tr>
						<tr>
							<td><strong>Contact number</strong></td>
							<td class="contact_number"></td>
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
	.star-ratings-sprite {
		background: url("https://s3-us-west-2.amazonaws.com/s.cdpn.io/2605/star-rating-sprite.png") repeat-x;
		font-size: 0;
		height: 21px;
		line-height: 0;
		overflow: hidden;
		text-indent: -999em;
		width: 110px;
	}
	.rating {
		background: url("https://s3-us-west-2.amazonaws.com/s.cdpn.io/2605/star-rating-sprite.png") repeat-x;
		background-position: 0 100%;
		float: left;
		height: 21px;
		display:block;
	}
</style>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
{!! $html->scripts() !!}
<script type="text/javascript">
	$(document).on("click","a.deleteratingreviews",function(e){
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
					url:"{{route('admin.reviewdelete',[''])}}"+"/"+id,
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


	$(document).on("click","a.trip_info",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.trip_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"},
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
				$('.trip_status').html(data.trip_status);
				$('.extra_notes').html(data.extra_notes);
				$('.promo_name').html(data.promo_name);
				$('.promo_amount').html(data.promo_amount);
				$('.promo_id').html(data.promo_id);
				$('.total_amount').html(data.total_amount);
				$('.driver_id').attr('data-id',data.driver_id);
				$('.user_id').attr('data-id',data.user_id);
				$('.Comments').html(data.comment);
				$('.booking_date').html(data.booking_date);

				$('.modal-title').text('Booking Details');
				$('#myModal4').modal('show');
			},
			error:function(){
				swal("Error!", 'Error in Not Get Record', "error");
			}
		});
	});

    $(document).on("click","a.parcel_trip_info",function(e){
        var row = $(this);
        var id = $(this).attr('data-id');
        $.ajax({
            url:"{{route('admin.review.get_parcel_details',[''])}}"+"/"+id,
            type: 'post',
            data: {"_token": "{{ csrf_token() }}"},
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
                $('.trip_status').html(data.trip_status);
                $('.extra_notes').html(data.extra_notes);
                $('.promo_name').html(data.promo_name);
                $('.promo_amount').html(data.promo_amount);
                $('.promo_id').html(data.promo_id);
                $('.total_amount').html(data.total_amount);
                $('.driver_id').attr('data-id',data.driver_id);
                $('.user_id').attr('data-id',data.user_id);
                $('.Comments').html(data.comment);
                $('.booking_date').html(data.booking_date);
                $('.modal-title').text('Parcel Details');
                $('#myModal4').modal('show');
            },
            error:function(){
                swal("Error!", 'Error in Not Get Record', "error");
            }
        });
    });


	

	$(document).on("click","a.driver_id",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.driver_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"},
			success:function(data) {
				$('.first_name').html(data.first_name);
				$('.email').html(data.email);
				$('.contact_number').html(data.contact_number);
				$('#myModal45').modal('show');
			},
			error:function(){
				swal("Error!", 'Error in Not Get Record', "error");
			}
		});
	});


	$(document).on("click","a.user_id",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.user_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"},
			success:function(data) {
				$('.first_name').html(data.first_name);
				$('.email').html(data.email);
				$('.contact_number').html(data.contact_number);
				$('#myModal46').modal('show');
			},
			error:function(){
				swal("Error!", 'Error in Not Get Record', "error");
			}
		});
	});

	$(document).on("change",".revie_status",function(e){
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
			cancelButtonText: "No, cancel please!",
			closeOnConfirm: false,
			closeOnCancel: false
		}, function(isConfirm){
			if (isConfirm) {
				$.ajax({
					url:"{{ route('admin.revie_status','replaceid') }}",
					type: 'post',
					data: {"_method": 'post',
					'id':id,status:value,
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
