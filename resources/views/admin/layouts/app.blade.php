<!DOCTYPE html>
<html>
@include('admin.includes.head')
<style>
.car_data {display:none;}
.preload .loader_img {position: absolute; margin: 0px auto; z-index: 9999; background: white; top: 40%; left: 0; right: 0; bottom: 0;}
.custom_load {overflow: hidden; width: 100%; height: 100%; position: relative; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; margin: 0 auto; background-color: white;}
table#dataTableBuilder {width: 100% !important;}
.custom-control-input {display: inline-block; opacity: unset; z-index: 2; position: relative;}
</style>
<div class="preload custom_load">
    <img class="loader_img" src="https://www.carlo.in/assets/images/load-gif.gif">
</div>
<body class="">
    <div class="car_data">
        <div id="wrapper">
            @include('admin.includes.sideBar')
            <div id="page-wrapper" class="gray-bg">
            @include('admin.includes.topNavigation')
            @yield('mainContent')
            @include('admin.includes.footer')
            </div>
        </div>
    </div>
    @include('admin.includes.scripts')
</body>
</html>