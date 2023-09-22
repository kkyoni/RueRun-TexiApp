<table class="table table-striped  table-bordered">
    <thead>
        <tr>
            <th>S. No.</th>
            <th>User Name</th>
            <th>Amount</th>
            <th>Join Date</th>
            <!-- <th>Expairy Date</th> -->

        </tr>
    </thead>
    <tbody>
        @if($data->count()>0)
        <?php $i=1;  ?>
        @foreach($data as $val)
        
        <tr>
            <td><strong>{{$i++}}</strong></td>
            <td>
             {{$val->first_name}}
         </td>
         <?php
         $amt = \App\Models\Booking::groupBy('driver_id')->where('driver_id',$val->id)->sum('total_amount');
                     // $amt1 = \App\Models\Booking::where('driver_id',$val->id)->groupBy('driver_id')->SUM('total_amount')->get();
                     // $amt = json_decode($amt1);
         ?>
         <!-- <td><a href="#" class="earning_detail" data-rid="{{$userData->id}}">{{@$amt}}</a></td> -->
         <td>{{@$amt}}</td>
         <td>{{$val->created_at->format('m-d-Y h:i:s')}}</td>
         <!-- <td>{{$val->created_at->addYear()->toDateTimeString()}}</td> -->
     </tr>
     @endforeach
     @else
     <tr>
        <td></td>
        <td class="text-center" colspan="3"><p>No Data Found...</p></td>
        <td></td>
    </tr>
    @endif
</tbody>
</table>
