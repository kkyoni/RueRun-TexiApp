	<table class="table table-striped  table-bordered">
				<thead>
					<tr>
						<th>S. No.</th>
						<th>User Name</th>
						<th>Amount</th>
						<th>Percentage</th>
						<th>Booking Id</th>
					</tr>
				</thead>
					<tbody>
					@if($data->count()>0)
						<?php $i=1;  ?>
						@foreach($data as $val)
							<tr>
								<td><strong>{{$i++}}<strong></td>
								<td>{{$val->userDetail->first_name}}</td>
								<td>${{$val->amount}}</td>
								<td>{{$val->percentage}}%</td>
								<td><a href="{{route('admin.tripindex')}}">#{{$val->booking_id}}</a></td>
							</tr>
						@endforeach
					@else
						<tr>
							<td class="text-center" colspan="5"><p>No Data Found...</p></td>
						</tr>
					@endif
					</tbody>
</table>