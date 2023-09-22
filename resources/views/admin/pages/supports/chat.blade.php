
@foreach($get_comment_data as $data)
@if((int)$data->sender_id !== (int) $user_id)
<div class="chat-message left">

	<img class="message-avatar" src="{!! @$data->userDetail->avatar !== '' ? url("storage/avatar/".@$data->userDetail->avatar) : url('storage/default.png') !!}" alt="" >
	<div class="message">
		<a class="message-author" href="#"> {{$data->userDetail->username}} </a>
		<span class="message-date"> {{date('D M d Y - H:i:s', strtotime($data->created_at))}}</span>
		<span class="message-content">
			{{$data->comment}}
		</span>
	</div>
</div>
@else
<div class="chat-message right">
	<img class="message-avatar" src="{!! @$data->userDetail->avatar !== '' ? url("storage/avatar/".@$data->userDetail->avatar) : url('storage/default.png') !!}" alt="" >

	<div class="message">
		<a class="message-author" href="#"> {{$data->userDetail->username}} </a>
		<span class="message-date">  {{date('D M d Y - H:i:s', strtotime($data->created_at))}} </span>
		<span class="message-content">
			{{$data->comment}}
		</span>
	</div>
</div>
@endif
@endforeach

