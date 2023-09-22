<!DOCTYPE html>
<html lang="en">	
@include('admin.includes.headAuth')

<body id="container">
	<div class="middle-box text-center loginscreen">
			@yield('authContent')	
	</div>
	<!-- Mainly scripts -->
	@include('admin.includes.scriptsAuth')
</body>
</html>



