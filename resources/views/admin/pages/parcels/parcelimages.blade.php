
<div class="ibox">
	<div class="ibox-content">
		<p> <strong>Image</strong></p>
		<div class="container">
			@if(sizeof($parcelimages) > 0)
                @foreach($parcelimages as $views)
                    @if($views->image != "")
                    <img src="{{url('storage/parcel_image/'.$views->image)}}" onError="this.onerror=null;this.src='{!! url('storage/parcel_image/not-image.png') !!}' " class="img-thumbnail" style="max-height: 200px;max-width: 200px;">
                        <p>{{$views->image_name}}</p>
                        <a href="{{url('storage/parcel_image/'.$views->image)}}" class="btn btn-sm btn-success" target="_blank"> Download&nbsp;<i class="fa fa-download" aria-hidden="true"></i></a>
                    <br><br>
                    @endif
                @endforeach
			@else
			<p>No Image Found</p>
			@endif
		</div>
	</div>
</div>
