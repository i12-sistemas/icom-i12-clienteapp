<?php

namespace App\Imports;

// use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class FollowupPlanilhaImport implements ToModel
{
    public function model(array $row)
    {
        return [
           'linha0'     => $row[0],
           'linha1'    => $row[1]
        ];
    }
}
