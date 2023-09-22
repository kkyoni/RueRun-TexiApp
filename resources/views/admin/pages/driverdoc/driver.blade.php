
<div class="ibox">
	<div class="ibox-content">
        <div class="row">
            @if($user_doc_list->count() > 0)
                @foreach($user_doc_list as $views)
                    <div class="col-md-12 row doc_block" style="margin: 8px 0 16px 0; border-bottom: 1px solid #ccc;">
                        <div class="col-md-4 img-block text-center" style="margin-bottom: 21px;">
                            <img src="{{url('storage/driver_documents/'.$views->doc_image)}}" class="img-thumbnail" alt="doc-img" style="height: 70px;width: 70px">
                        </div>
                        <div class="col-md-4 img-block text-center" style="margin-bottom: 21px;">
                            <span>{{$views->doc_name}}</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <a href="{{url('storage/driver_documents/'.$views->doc_image)}}" class="btn btn-sm btn-success" target="_blank"> Download&nbsp;<i class="fa fa-download" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <br><hr><br>
                @endforeach
            @endif
        </div>
	</div>
</div>

