@extends('admin.layouts.app')
@section('title')
    Driver Location
@endsection
@section('mainContent')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Location of Drivers</h2>
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
                            <div class="table-striped">

                                <div class="row">
                                    <div class="col-lg-3 hidden">
                                        {{ Form::select('state',$state,null,['placeholder' => 'Select State','id'=>'state','class'=>'form-control','required'])}}
                                    </div>
                                    <div class="col-lg-3 hidden">
                                        {{ Form::select('city',$city,null,['placeholder' => 'Select City','id'=>'city','class'=>'form-control','required','disabled'])}}
                                    </div>
                                    <div class="col-lg-3">

                                        <select id="taxi" class="form-control" required="" name="taxi">
                                            <option selected="selected" value="">Select Driver</option>
                                            @if($taxi->count() > 0)
                                                @foreach($taxi as $taxiuser)
                                                    @if($taxiuser->latitude && $taxiuser->longitude)
                                                        @if($taxiuser->first_name)
                                                            <option value='{{ $taxiuser->id }} '>{{ $taxiuser->first_name }}</option>
                                                        @elseif($taxiuser->company_name)
                                                            <option value='{{ $taxiuser->id }}'>{{ $taxiuser->company_name }}</option>
                                                        @endif
                                                    @endif

                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="ibox ">
                                            <div class="ibox-content no-padding">
                                                <div id="map-canvas" style="height:400px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <!--     <div class="col-md-6"  >
                                        <div class="ibox ">
                                            <div class="ibox-content no-padding">
                                                <div  id="dynamic_data"></div>
                                                <div id="dashboard_div"  style="margin: 2em; " style="width:250px;height:250px;">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <div id="control3" align="center"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <div id="chart2" align="center"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>

                            </div>
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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/variable-pie.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!--   <script type="text/javascript" src="https://www.google.com/jsapi"></script> -->
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo&signed_in=false&libraries=places">
    </script>
    <script type="text/javascript">
        Highcharts.chart('dynamic_data', {
            chart: {
                type: 'variablepie',
                backgroundColor: null
            },

            title: {
                text: ''
            },
            exporting: { enabled: false },
            tooltip: {
                headerFormat: '',
                pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name} : {point.y}</b><br/>'
            },
            series: [{
                minPointSize: 80,
                innerSize: '30%',
                zMin: 0,
                name: '{{Settings::get('project_title')}}',
                data: [{
                    name: 'Super Admin : {{$total_superadmin}}' ,
                    y: {{$total_superadmin}},

                }, {
                    name: 'Users : {{$total_user}}',
                    y: {{$total_user}},
                }, {
                    name: 'Driver : {{$total_driver}}',
                    y: {{$total_driver}},
                }, {
                    name: 'EmergencyDetails : {{$total_emergencyDetails}}',
                    y: {{$total_emergencyDetails}},
                }, {
                    name: 'Promocode : {{$total_promocode}}',
                    y: {{$total_promocode}},
                },
                    {
                        name: 'Vehicle Categories : {{$total_vehiclecategories}}',
                        y: {{$total_vehiclecategories}},
                    },
                    {
                        name: 'Rating And Reviews : {{$total_ratingreviews}}',
                        y: {{$total_ratingreviews}},
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
                            if (results && results[0]
                                && results[0].geometry && results[0].geometry.viewport)
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

            // var pointArray = new google.maps.MVCArray(taxiData);

            // what data for the heatmap and how to display it

            // heatmap = new google.maps.visualization.HeatmapLayer({
            //   data: pointArray,
            //   radius: 10
            // });

            // placing the heatmap on the map

            // heatmap.setMap(map);
        }
        var timer;
        function getMarkers() {
            window.clearTimeout(timer);
            id = $(this).attr('data-id');
            var url = "{{ route('admin.location.live_map') }}";
            var country = 1;
            var state = 1;
            var city = 1;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                method: 'get',
                data: {
                    id:id,
                    country:country,
                    state:state,
                    city:city,
                },
                success: function(result){
                    clearMarker(markerStore);
                    var driver_title='';
                    if(result.data.length>0){
                        for(var i=0, len=result.data.length; i<len; i++){
                            if(result.data[i].first_name){
                                driver_title = result.data[i].first_name+' '+result.data[i].last_name;
                            }else if(result.data[i].company_name){
                                driver_title = result.data[i].company_name;
                            }
                            var marker = new google.maps.Marker({
                                position: new google.maps.LatLng(result.data[i].latitude,result.data[i].longitude),
                                title:driver_title,
                                map:map,
                                icon : "{{ url('/uploads/32x32.png')  }}"
                            });
                            var contentString = '<div id="content" style="min-width: 300px;position:relative;bottom:5px; min-height: 50px;">'+
                                '<div id="siteNotice">'+
                                '<img src="{{url('storage/avatar/')}}/'+result.data[i].avatar+'" class="img-thumbnail" style="max-width: 37%;float:left;height:64px;' +
                                '    ">'+
                                '<h4 id="firstHeading" class="firstHeading" style="color:#1e579d;position:relative;left:10px;">'+driver_title+'</h4>'+
                                '<div id="bodyContent">'+
                                //'<p style="font-weight:700;margin-bottom:0px;position:relative;left:10px;">Documents Status: '+result.data[i].doc_status+'</p>'+
                                '</div>'+
                                '</div>'+
                                '</div>';
                            addInfoWindow(marker,contentString);
                            markerStore[result.data[i].id] = marker;
                        }
                        // every 5 second get location
                        timer  = window.setTimeout(getMarkers,INTERVAL);
                    }
                },
                error:function(error){
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
            }
            else {
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
                    },
                    success: function (result) {
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
                    },
                    success: function (result) {
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
            address = "India";
            if(taxi_id!="")
            {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: url,
                    type: "get",
                    data: {
                        "taxi_id" : taxi_id
                    },
                    success: function (result) {
                        // result = JSON.parse(result);
                            console.log(result);
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
                location.reload();
            }
        });
    </script>
@endsection
