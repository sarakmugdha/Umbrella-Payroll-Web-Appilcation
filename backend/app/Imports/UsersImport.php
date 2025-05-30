<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //  dd($row);

        // foreach($rows as $row) {
        //     User::create([

        //     ])
        // }

        return new User([
            "name"=> $row["name"],
            "email"=> $row["email"],
            "password"=> bcrypt($row["password"]),
        ]);
    }
}
