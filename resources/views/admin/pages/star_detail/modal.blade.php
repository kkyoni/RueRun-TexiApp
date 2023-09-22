	<table class="table table-striped  table-bordered">
				<thead>
					<tr>
						<th>S. No.</th>
						<th>User Name</th>
						<th>Amount</th>
					</tr>
				</thead>
					<tbody>
					@if($data->count()>0)
						<?php $i=1;  ?>
						@foreach($data as $val)
							<tr>
								<td><strong>{{$i++}}<strong></td>
								<td>{{$val->first_name}} {{$val->last_name}}</td>
						<?php  
							$amt = \App\Models\ReferWallets::where('refer_id',$userData->id)->where('user_id',$val->id)->sum('amount');
						?>
							<td><a href="#" class="earning_detail" data-rid="{{$userData->id}}">{{$amt}}</a></td>
							</tr>
						@endforeach
					@else
						<tr>
							<td class="text-center" colspan="3"><p>No Data Found...</p></td>
						</tr>
					@endif
					</tbody>
</table>