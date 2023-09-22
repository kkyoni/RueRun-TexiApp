<table>
    <thead>
        <tr>
            <th>Sr No</th>
            <th>Tag</th>
            <th>Country</th>
            <th>Operator Name</th>
            <th>Operator Email</th>
            <th>Name</th>
            <th>Surname</th>
            <th>Email</th>
            <th>Contact Number</th>
            <th>Tag 1</th>
            <th>Tag 2</th>
            <th>Tag 3</th>
            <th>Tag 4</th>
            <th>Tag 5</th>
            <th>Tag 6</th>
            <th>Tag 7</th>
            <th>Tag 8</th>
            <th>Tag 9</th>
            <th>Tag 10</th>
        </tr>
    </thead>
    <tbody>
        @foreach($leads as $key=>$lead)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{$lead->tag}}</td>
            <td>{{$lead->CountryName->country}}</td>
            <td>{{$lead->OperatorData->name}}</td>
            <td>{{$lead->OperatorData->email}}</td>
            <td>{{$lead->LeadData->name}}</td>
            <td>{{$lead->LeadData->surname}}</td>
            <td>{{$lead->LeadData->email}}</td>
            <td>{{$lead->LeadData->phone}}</td>
            <td>{{$lead->LeadData->tag1}}</td>
            <td>{{$lead->LeadData->tag2}}</td>
            <td>{{$lead->LeadData->tag3}}</td>
            <td>{{$lead->LeadData->tag4}}</td>
            <td>{{$lead->LeadData->tag5}}</td>
            <td>{{$lead->LeadData->tag6}}</td>
            <td>{{$lead->LeadData->tag7}}</td>
            <td>{{$lead->LeadData->tag8}}</td>
            <td>{{$lead->LeadData->tag9}}</td>
            <td>{{$lead->LeadData->tag10}}</td>
        </tr>
        @endforeach
    </tbody>
</table>