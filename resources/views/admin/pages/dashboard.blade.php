@extends('admin.layouts.app')
@section('title')
Dashboard
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-sm-4">
    <h2>Dashboard</h2>
  </div>
</div>

<div class="wrapper wrapper-content">
  <div class="row">
    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-user_count">
              <i class="fa fa-user-circle fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-user_count white media-body">
              <h3>Users</h3>
              <h5 class="text-bold-400 mb-0">{{$total_user}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.index') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-driver_count">
              <i class="fa fa-user-secret fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-driver_count white media-body">
              <h3>Drivers</h3>
              <h5 class="text-bold-400 mb-0">{{$total_driver}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.driver.index')}}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-company_count">
              <i class="fa fa-users fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-company_count white media-body">
              <h3>Company</h3>
              <h5 class="text-bold-400 mb-0">{{$total_company}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.company.index')}}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-todayBooking_count bg-darken-2">
              <i class="fa fa-book fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-todayBooking_count white media-body">
              <h3>Today's completed Rides</h3>
              <h5 class="text-bold-400 mb-0">{{$t_admin_profit->count()}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.tripindex') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br><br><br><br><br><br><br>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-todayProfit_count bg-darken-2">
              <i class="fa fa-money fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-todayProfit_count white media-body">
              <h3>Today Profit</h3>
              <h5 class="text-bold-400 mb-0">${{$t_amount_profit['earnings_total_amount']}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.transaction_detail.index') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-vehicles_count bg-darken-2">
              <i class="fa fa-car fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-vehicles_count white media-body">
              <h3>Vehicles</h3>
              <h5 class="text-bold-400 mb-0">{{$total_vehiclecategories}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.vehicleindex') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-adminProfit_count bg-darken-2">
              <i class="fa fa-money fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-adminProfit_count white media-body">
              <h3>Admin Profit</h3>
              <h5 class="text-bold-400 mb-0">${{$total_admin_pr}}</h5>
              <div class="media-left media-middle mt-1">
                <!-- <a class="white" href="#">View more</a> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-review_count bg-darken-2">
              <i class="fa fa-star fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-review_count white media-body">
              <h3>Reviews</h3>
              <h5 class="text-bold-400 mb-0">{{$total_ratingreviews}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.reviewindex') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <br><br><br><br><br><br><br>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-promocode_count bg-darken-2">
              <i class="fa fa-tag fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-promocode_count white media-body">
              <h3>Promocode</h3>
              <h5 class="text-bold-400 mb-0">{{$total_promocode}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.promocode.index') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-12">
      <div class="card">
        <div class="card-content">
          <div class="media align-items-stretch">
            <div class="p-2 text-center bg-emergency_count bg-darken-2">
              <i class="fa fa-warning fa-3x icon_admin"></i>
            </div>
            <div class="p-2 bg-gradient-x-emergency_count white media-body">
              <h3>Emergency</h3>
              <h5 class="text-bold-400 mb-0">{{$total_emergencyDetails}}</h5>
              <div class="media-left media-middle mt-1">
                <a class="white" href="{{ route('admin.emergencyindex') }}">View more</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-12 col-lg-12 col-12">
      <div class="ibox">
        <div class="ibox-content no-padding">
          <div  id="dynamic_data"></div>
          <div id="dashboard_div" style="margin: 2em; " style="width:250px;height:250px;">
            <table>
              <tr>
                <td><div id="control3" align="center"></div></td>
              </tr>
            </table>
            <div id="chart2" align="center"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('styles')
<style type="text/css">
  .ibox-content{background-color:#fff; border: none; border-style:none !important;}
  .wrapper-content{padding: 20px 10px 40px;}
  .white{color:#FFF;}
  .white:hover{color:#FFF;}
  .bg-user_count {background-color: #00A5A8 !important;}
  .bg-gradient-x-user_count {background-image: linear-gradient(to right, #00A5A8 0%, #4DCBCD 100%); background-repeat: repeat-x;}
  .bg-driver_count {background-color: #FF6275 !important;}
  .bg-gradient-x-driver_count {background-image: linear-gradient(to right, #FF6275 0%, #FF9EAC 100%); background-repeat: repeat-x;}
  .bg-company_count {background-color: #fc7703 !important;}
  .bg-gradient-x-company_count {background-image: linear-gradient(to right, #fc7703 0%, #FF976A 100%); background-repeat:repeat-x;}
  .bg-todayBooking_count {background-color: #10C888 !important;}
  .bg-gradient-x-todayBooking_count {background-image: linear-gradient(to right, #10C888 0%, #5CE0B8 100%); background-repeat: repeat-x;}
  .bg-todayProfit_count {background-color: #d8db21!important;}
  .bg-gradient-x-todayProfit_count {background-image: linear-gradient(to right, #d8db21 0%, #edeb6b 100%); background-repeat: repeat-x;}
  .bg-vehicles_count {background-color: #4b5ff1!important;}
  .bg-gradient-x-vehicles_count {background-image: linear-gradient(to right, #4b5ff1 0%, #6CDDEB 100%); background-repeat: repeat-x;}
  .bg-adminProfit_count {background-color: #FF5733!important;}
  .bg-gradient-x-adminProfit_count {background-image: linear-gradient(to right, #FF5733 0%, #ed836b 100%);
    background-repeat: repeat-x;}
  .bg-review_count {background-color: #fcbe03!important;}
  .bg-gradient-x-review_count {background-image: linear-gradient(to right, #fcbe03 0%, #fdd868 100%);
    background-repeat: repeat-x;}
  .bg-promocode_count{background-color: #8803fc !important;}
  .bg-gradient-x-promocode_count{background-image: linear-gradient(to right, #8803fc 0%, #cf9afe 100%);
    background-repeat: repeat-x;}
  .bg-emergency_count {background-color: #33FFBD!important;}
  .bg-gradient-x-emergency_count {background-image: linear-gradient(to right, #33FFBD 0%, #b3ffe7 100%);
    background-repeat: repeat-x;}
  .card{color:#FFF!important; font-weight: 600!important; font-size: 1.14rem!important;}
  .p-2 {padding: 1rem!important;}
  #dynamic_data {margin: 2em auto;}
  #container_chart{margin: 0 auto;}
  .nprofit-bg {background-color: #7a8e8a !important; color: #ffffff;}
  .emergency-bg {background-color: #eadddd;}
  .rating-bg {background-color: #627d7d !important; color: #ffffff;}
  .to_profit-bg{background-color: #ab6e2b !important; color: #ffffff;}
  .gm-style-iw-d{overflow: hidden !important;}
  .fa-3x {font-size: 3em;}
  .highcharts-figure, .highcharts-data-table table {min-width: 360px; max-width: 800px; margin: 1em auto;}
  .highcharts-data-table table {font-family: Verdana, sans-serif; border-collapse: collapse; border: 1px solid #EBEBEB; margin: 10px auto; text-align: center; width: 100%; max-width: 500px;}
  .highcharts-data-table caption {padding: 1em 0; font-size: 1.2em; color: #555;}
  .highcharts-data-table th {font-weight: 600; padding: 0.5em;}
  .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {padding: 0.5em;}
  .highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {background: #f8f8f8;}
  .highcharts-data-table tr:hover {background: #f1f7ff;}
</style>
@endsection
@section('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo&signed_in=false&libraries=places">
</script>
<script type="text/javascript">
  Highcharts.chart('dynamic_data', {
    chart: {
      type: 'variablepie',
      backgroundColor: null
    },title: {
      text: ''
    },exporting: {
      enabled: false 
    },tooltip: {
      headerFormat: '',
      pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name} : {point.y}</b><br/>'
    },series: [{
      minPointSize: 80,
      innerSize: '30%',
      zMin: 0,
      name: '{{Settings::get('project_title')}}',
      data: [{
        name: 'Users',
        y: {{$total_user}},
        color : '#00A5A8',
      }, {
        name: 'Driver',
        y: {{$total_driver}},
        color : '#FF6275',
      }, {
        name: 'Company',
        y: {{$total_company}},
        color : '#fc7703',
      },{
        name: 'Vehicle Categories',
        y: {{$total_vehiclecategories}},
        color : '#4b5ff1',
      },{
        name: 'Rating And Reviews',
        y: {{$total_ratingreviews}},
        color : '#fcbe03',
      },{
        name: 'Promocode',
        y: {{$total_promocode}},
        color : '#8803fc',
      },{
        name: 'EmergencyDetails',
        y: {{$total_emergencyDetails}},
        color : '#33FFBD',
      },]
    }]
  });
</script>
<script>
  function clearMarker (markerStore) {
    if(Object.keys(markerStore).length > 0) {
      for (i in markerStore) {
        markerStore[i].setMap(null);
      }
    }
  }
  function addInfoWindow(marker, message) {
    var infoWindow = new google.maps.InfoWindow({
      content: message
    });
    google.maps.event.addListener(marker, 'click', function () {
      infoWindow.open(map, marker);
    });
  }
  function findAddress(address) {
    if ((address != '') && geocoder) {
      geocoder.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
            if (results && results[0] && results[0].geometry && results[0].geometry.viewport)
              map.fitBounds(results[0].geometry.viewport);
          } else {
            alert("No results found");
          }
        } else {
          alert("Geocode was not successful for the following reason: " + status);
        }
      });
    }
  }
  var INTERVAL = 3000;
  var markerStore = {};
  var map, pointarray, heatmap;
  getMarkers();
  // a shortened version of the data for Google's taxi example
  var taxiData = [
  @foreach($usersData as $data)
  @if(!empty($data->latitude) || !empty($data->longitude))
  new google.maps.LatLng({{$data->latitude}},{{$data->longitude}}),
  @endif
  @endforeach
  // ...
  ];
  function initialize() {
  // the map's options
  var mapOptions = {
    zoom: 2,
    center: new google.maps.LatLng(23.012073, 72.503169),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  // the map and where to place it
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  }
  var timer;
  function getMarkers() {
    window.clearTimeout(timer);
    id = $(this).attr('data-id');
    var url = "{{ route('admin.live_map') }}";
    var country = 1;
    var state = 1;
    var city = 1;
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: url,
      method: 'get',
      data: {id:id,country:country,state:state,city:city,},
      success: function(result){
        clearMarker(markerStore);
        if(result.data.length>0){
          for(var i=0, len=result.data.length; i<len; i++){
            var marker = new google.maps.Marker({
              position: new google.maps.LatLng(result.data[i].latitude,result.data[i].longitude),
              title:result.data[i].first_name+' '+result.data[i].last_name,
              map:map,
              icon : "{{ url('/uploads/32x32.png')  }}"
            });
            var contentString = '<div id="content" style="min-width: 300px;position:relative;bottom:5px; min-height: 50px;">'+'<div id="siteNotice">'+'<img src="{{url('storage/avatar/')}}/'+result.data[i].avatar+'" class="img-thumbnail" style="max-width: 37%;float:left;height:64px;' +'">'+'<h4 id="firstHeading" class="firstHeading" style="color:#1e579d;position:relative;left:10px;">'+result.data[i].first_name+' '+result.data[i].last_name+'</h4>'+'<div id="bodyContent">'+'</div>'+'</div>'+'</div>';
            addInfoWindow(marker,contentString);
            markerStore[result.data[i].id] = marker;
          }
          timer  = window.setTimeout(getMarkers,INTERVAL);
        }
      },error:function(error){
        console.log(error);
      }
    });
  }
  // as soon as the document is ready the map is initialized
  google.maps.event.addDomListener(window, 'load', initialize);
  geocoder = new google.maps.Geocoder();
  $("#state").on("change", function () {
    var state_id = $(this).val();
    var url = "{{ route('admin.get_city') }}";
    $("#state_text").val(state_id);
    if (state_id == "") {
      address = "India";
      $("#city").html("<option value=''>Select City</option>");
      $("#city").attr("disabled", true);
      $("#taxi").attr("disabled", true);
    } else {
      address = "India";
      address += " "+$("#state option:selected").html();
      $("#city").attr("disabled", false);
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: url,
        type: "get",
        data: {
          "id": state_id,
        },success: function (result) {
          $("#city").html(result);
        }
      });
    }
    findAddress(address);
    getMarkers();
  });
  $("#city").on("change", function () {
    $(this).attr('selected', 'selected');
    var url = "{{ route('admin.get_taxi') }}";
    var city_id = $(this).val();
    $("#city_text").val(city_id);
    address = "India";
    address += " "+$("#state option:selected").html();
    if(city_id!=""){
      address += " "+$("#city option:selected").html();
      $("#taxi").attr("disabled", false);
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: url,
        type: "get",
        data: {
          "id": city_id,
        },success: function (result) {
          $("#taxi").html(result);
        }
      });
    }else{
      $("#taxi").attr("disabled", true);
    }
    findAddress(address);
    getMarkers();
  });
  $("#taxi").on("change", function () {
    var taxi_id = $(this).val();
    var url = "{{ route('admin.get_taxi_lat_long') }}";
    if(taxi_id!=""){
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: url,
        type: "get",
        data: {
          "taxi_id" : taxi_id
        },success: function (result) {
          var lat = parseFloat(result.latitude);
          var lng = parseFloat(result.longitude);
          getMarkers();
          var bounds = new google.maps.LatLngBounds();
          var pt = new google.maps.LatLng(lat, lng);
          bounds.extend(pt);
          map.fitBounds(bounds);
        }
      });
    }else{
      findAddress(address);
      getMarkers();
    }
  });
</script>
@endsection