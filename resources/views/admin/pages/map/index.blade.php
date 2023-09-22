@extends('admin.layouts.app')
@section('title')
Map
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">

		<h2>Map</h2>

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
						<div class="clearfix">
							Note :- <img src="{{ asset('/storage/icon/driver.png')}}"> Driver,  <img src="{{ asset('/storage/icon/user.png')}}"> User
						</div>
					</div>
					<br>
					<div class="col-md-12">
						<div id="map" style="width:100%;height:490px;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('styles')
@endsection
@section('scripts')
<script src="http://maps.google.com/maps/api/js?sensor=false" 
type="text/javascript"></script>
<script>
	var map;
	var InforObj = [];
	var centerCords = {
		lat: -25.344,
		lng: 131.036
	};
	var markersOnMap = [
	@foreach($user as $data)
	@if(!empty($data->latitude) || !empty($data->longitude))
	{
		LatLng: [{
			lat: {{$data->latitude}},
			lng: {{$data->longitude}}
		}],
		userDatail: [{
			username: '{{$data->username}}',
			email : '{{$data->email}}',
			user_type : '{{$data->user_type}}',
			contact_number : '{{$data->contact_number}}'
		}],
	},
	@endif

	@endforeach
	];

	window.onload = function () {
		initMap();
	};

	function addMarkerInfo() {
		for (var i = 0; i < markersOnMap.length; i++) {
			
			var contentString = '<div id="content"><h1>'+markersOnMap[i].userDatail[0].username+'</h1><h5>User Type :- '+markersOnMap[i].userDatail[0].user_type+'</h5><h5>Email :-'+markersOnMap[i].userDatail[0].email+'</h5><h5>Contact No:-'+markersOnMap[i].userDatail[0].contact_number+'</h5></div>';
			if(markersOnMap[i].userDatail[0].user_type == "driver"){
				const marker = new google.maps.Marker({
					position: markersOnMap[i].LatLng[0],
					map: map,
					icon:"{{ asset('/storage/icon/driver.png')}}"
				});

				marker.addListener('click', function () {
					closeOtherInfo();
					infowindow.open(marker.get('map'), marker);
					InforObj[0] = infowindow;
				});
			}
			else{

				const marker = new google.maps.Marker({
					position: markersOnMap[i].LatLng[0],
					map: map,
					icon:"{{ asset('/storage/icon/user.png')}}"
				});

				marker.addListener('click', function () {
					closeOtherInfo();
					infowindow.open(marker.get('map'), marker);
					InforObj[0] = infowindow;
				});

			}

			if(markersOnMap[i].userDatail[0].user_type == "user"){



			}

			const infowindow = new google.maps.InfoWindow({
				content: contentString,
				maxWidth: 200
			});


                    // marker.addListener('mouseover', function () {
                    //     closeOtherInfo();
                    //     infowindow.open(marker.get('map'), marker);
                    //     InforObj[0] = infowindow;
                    // });
                    // marker.addListener('mouseout', function () {
                    //     closeOtherInfo();
                    //     infowindow.close();
                    //     InforObj[0] = infowindow;
                    // });
                }
            }

            function closeOtherInfo() {
            	if (InforObj.length > 0) {
            		/* detach the info-window from the marker ... undocumented in the API docs */
            		InforObj[0].set("marker", null);
            		/* and close it */
            		InforObj[0].close();
            		/* blank the array */
            		InforObj.length = 0;
            	}
            }

            function initMap() {
            	map = new google.maps.Map(document.getElementById('map'), {
            		zoom: 2,
            		center: centerCords
            	});
            	addMarkerInfo();
            }
        </script>
        @endsection
