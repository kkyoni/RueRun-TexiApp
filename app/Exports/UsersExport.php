<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function collection()
    {
        return User::all();
    }
}
