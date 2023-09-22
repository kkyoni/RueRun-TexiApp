@extends('admin.layouts.app')
@section('title')
Booking Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Booking Management</h2>
	</div>
	<div class="col-lg-2">
	</div>
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

<div class="modal inmodal" id="myModal5" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal_css">
		<div class="modal-content animated flipInY">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Trip Map</h4>
			</div>
			<div >
				<div id="googleMap" style="width:100%;height:490px;"></div>
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
				<table class="table">
					<tbody>
						<tr>
							<td><strong>First Name  </strong></td>
							<td class="fname"></td>
							<td>|</td>
							<td><strong>Last Name  </strong></td>
							<td class="lname"></td>
						</tr>
						<tr>
							<td><strong>Email address</strong></td>
							<td class="email_add"></td>
							<td>|</td>
							<td><strong>Status </strong></td>
							<td class="status"></td>
						</tr>
						<tr>
							<td><strong>Contact Number </strong></td>
							<td class="c_number"></td>
							<td>|</td>
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
				<h4 class="modal-title">Booking Details</h4>
			</div>
			<div class="modal-body">
				<table class="table">
					<tbody>
						<tr>
							<td><strong>Driver Name </strong></td>
							<td class="driver"></td>
							<td>|</td>
							<td><strong>User Name </strong></td>
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
							<td><strong>Start Date </strong></td>
							<td class="start_date"></td>
							<td>|</td>
							<td><strong>Booking Date </strong></td>
							<td class="booking_date"></td>
						</tr>
						<tr>
							<td><strong>Base Fare </strong></td>
							<td class="base_fare"></td>

						</tr>
						<tr>
							<td><strong>Total Km </strong></td>
							<td class="total_km"></td>
							<td>|</td>
							<td><strong>Admin Commision  </strong></td>
							<td>$<label class="admin_commision"></label></td>
						</tr>
						<tr>
							<td><strong>Promocode</strong></td>
							<td class="promocode"></td>
						</tr>
						<tr>
							<td><strong>Extra Notes</strong></td>
							<td class="extra_notes"></td>
							<td>|</td>
							<td><strong>Trip Status </strong></td>
							<td class="trip_status"></td>
						</tr>
						{{--						<tr>--}}
						{{--							<td><strong>Promo Amount :-<strong></td>--}}
						{{--							<td class="promo_amount"></td>--}}
						{{--							<td>|</td>--}}
						{{--							<td><strong>Promo Id :-</strong></td>--}}
						{{--							<td class="promo_id"></td>--}}
						{{--						</tr>--}}
						<tr>
							<td><strong>Total Amount </strong></td>
							<td>$<label class="total_amount"></label></td>
							<td>|</td>
							<td><strong>Trip Type </strong></td>
							<td class="trip_type_status"></td>
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
	$(document).on("click","a.deletetrip",function(e){
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
					url:"{{route('admin.tripdelete',[''])}}"+"/"+id,
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


	$(document).on("click","a.show_driver",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.get_driver_info',[''])}}"+"/"+id,
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
				//swal("Deleted!", "Operator has been deleted.", "success");

			});


	$(document).on("click","a.ride_info",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		$.ajax({
			url:"{{route('admin.ride_info',[''])}}"+"/"+id,
			type: 'post',
			data: {"_token": "{{ csrf_token() }}"
		},
		success:function(data) {
			var mapProp = {
				center:new google.maps.LatLng(20.5937,78.9629),
				zoom:3,
				mapTypeId:google.maps.MapTypeId.ROADMAP
			};
			var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);

			var marker = new google.maps.Marker({
				title:'Click to zoom'
			});

			marker.setMap(map);

			google.maps.event.addListener(marker,'click',function() {
				map.setZoom(9);
				map.setCenter(marker.getPosition());
			});

			var tourplan = new google.maps.Polyline({
				path:[
				new google.maps.LatLng(data.start_latitude, data.start_longitude),
				new google.maps.LatLng(data.drop_latitude, data.drop_longitude)
				],

				strokeColor:"#FF2929",
				strokeOpacity:0.6,
				strokeWeight:2
			});

			tourplan.setMap(map);
            //to remove plylines
            //tourplan.setmap(null);
            $('#myModal5').modal('show');

        },
        error:function(){
        	swal("Error!", 'Error in Not Get Record', "error");
        }
    });
				//swal("Deleted!", "Operator has been deleted.", "success");

			});



//

$(document).on("click","a.show_info",function(e){
	var row = $(this);
	var id = $(this).attr('data-id');
	$.ajax({
		url:"{{route('admin.show_info',[''])}}"+"/"+id,
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
		$('.total_km').html(data.total_km);
		$('.admin_commision').html(data.admin_commision);
		$('.transaction_id').html(data.transaction_id);
		$('.promocode').html(data.promocode);
		if(data.trip_status == 'on_going'){
			$('.trip_status').html('on going');
		}
		else{

			$('.trip_status').html(data.trip_status);
		}
		$('.extra_notes').html(data.extra_notes);
		$('.total_amount').html(data.total_amount);
		$('.date').html(data.date);
		$('.trip_type_status').html(data.trip_type_status);
		if(data.start_date != ''){
			$('.start_date').html(data.start_date);
		}else{
			$('.start_date').html(data.booking_date);
		}
		$('.booking_date').html(data.booking_date);
		console.log(data);
		$('#myModal4').modal('show');

	},
	error:function(){
		swal("Error!", 'Error in Not Get Record', "error");
	}
});
				//swal("Deleted!", "Operator has been deleted.", "success");

			});

//




$(document).on("click","a.show_user",function(e){
	var row = $(this);
	var id = $(this).attr('data-id');
	$.ajax({
		url:"{{route('admin.get_user_info',[''])}}"+"/"+id,
		type: 'post',
		data: {"_token": "{{ csrf_token() }}"
	},
	success:function(data) {
		$('.fname').html(data.first_name);
		$('.email_add').html(data.email_add);
		$('.lname').html(data.last_name);
		$('.status').html(data.status);
		$('.c_number').html(data.contact_number);
		$('.gender').html(data.gender);
		console.log(data);
		$('#myModal3').modal('show');

	},
	error:function(){
		swal("Error!", 'Error in Not Get Record', "error");
	}
});
				//swal("Deleted!", "Operator has been deleted.", "success");

			});



//



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
				url:"{{ route('admin.emergencychange_status','replaceid') }}",
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

<script src = "http://maps.google.com/maps/api/js?sensor=false"></script>
@endsection
