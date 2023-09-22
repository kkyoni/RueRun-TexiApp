<table>
    <thead>
        <tr>
            <th>Sr No</th>
            <th>Country</th>
            <th>No of calls attended</th>
            <th>Operator Name</th>
            <th>Operator Email</th>
            <th>Potential</th>
            <th>Callback</th>
            <th>No Answer</th>
            <th>Not interested</th>
            <th>Fake</th>
        </tr>
    </thead>
    <tbody>
        @foreach($operator as $key=>$data)
        <?php
        $totalLead=$data->where('operator_id',$data->operator_id)->where('tag','!=','Pending')->count('operator_id');
        $potential=$data->where('tag','Potential')->where('operator_id',$data->operator_id)->count('tag');
        $callback=$data->where('tag','Callback')->where('operator_id',$data->operator_id)->count('tag');
        $no_ans=$data->where('tag','No Answer')->where('operator_id',$data->operator_id)->count('tag');
        $no_int=$data->where('tag','Not interested')->where('operator_id',$data->operator_id)->count('tag');
        $fake=$data->where('tag','Fake')->where('operator_id',$data->operator_id)->count('tag');
        ?>
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{$data->CountryName->country}}</td>
            <td>{{$totalLead}}</td>
            <td>{{$data->OperatorData->name}}</td>
            <td>{{$data->OperatorData->email}}</td>
            <td>{{$potential}} ({{\App\Helpers\Helper::ValueInPer($potential,$totalLead)}})</td>
            <td>{{$callback}} ({{\App\Helpers\Helper::ValueInPer($callback,$totalLead)}})</td>
            <td>{{$no_ans}} ({{\App\Helpers\Helper::ValueInPer($no_ans,$totalLead)}})</td>
            <td>{{$no_int}} ({{\App\Helpers\Helper::ValueInPer($no_int,$totalLead)}})</td>
            <td>{{$fake}} ({{\App\Helpers\Helper::ValueInPer($fake,$totalLead)}})</td>
        </tr>
        @endforeach
    </tbody>
</table>


