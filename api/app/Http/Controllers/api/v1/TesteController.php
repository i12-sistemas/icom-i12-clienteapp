<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\Coletas;


class TesteController extends Controller
{


    public function teste(Request $request)
    {
        $id = isset($request->id) ? $request->id : null;
        $id = explode(",", $id);
        if (!is_array($id)) $id[] = $id;
        $id = count($id) > 0 ? $id : null;
        $coletas = Coletas::whereIn('id', $id)->get();
        $html = isset($request->view) ? $request->view == 'html' : false;
        if ($html) {
            return view('pdf.coletas.ordemcoleta', compact('coletas'));
        } else {
            $pdf = $pdf = \PDF::loadView('pdf.coletas.ordemcoleta', compact('coletas'));
            return $pdf->stream('document.pdf');
        }
    }

}
