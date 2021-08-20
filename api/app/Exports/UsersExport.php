<?php

namespace App\Exports;

use App\Models\Usuario;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UsersExport implements FromView
{
    public function view(): View
    {
        return view('exports.usuarios.list', [
            'dataset' => Usuario::all()
        ]);
    }
}
