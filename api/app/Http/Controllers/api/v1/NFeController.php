<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\RetApiController;

use NFePHP\DA\NFe\Danfe;


use App\Exports\BSoftNfeConsultaExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadings;


class NFeController extends Controller
{
    public function consulta_detalhe(Request $request)
    {
        $ret = new RetApiController;
        try {
            $output = ($request->has('output') ? $request->output : 'data');
            if (!(($output === 'xlsx') || ($output === 'xls') || ($output === 'csv'))) $output = 'data';

            $outrosfiltros = [];
            if ($request->has('numero')) $outrosfiltros['numero'] = $request->numero;
            if ($request->has('data_emissao')) $outrosfiltros['data_emissao'] = $request->data_emissao;

            if ($request->has('emitente_cnpj')) $outrosfiltros['emitente_cnpj'] = $request->emitente_cnpj;
            if ($request->has('emitente_nome')) $outrosfiltros['emitente_nome'] = $request->emitente_nome;

            if ($request->has('destinatario_cnpj')) $outrosfiltros['destinatario_cnpj'] = $request->destinatario_cnpj;
            if ($request->has('destinatario_nome')) $outrosfiltros['destinatario_nome'] = $request->destinatario_nome;
            if ($request->has('peso')) {
                $v = floatval($request->peso);
                $outrosfiltros['peso'] = number_format($request->peso,  3, ',' , '');

            }if ($request->has('valor')) {
                $v = floatval($request->valor);
                $outrosfiltros['valor'] = number_format($request->valor,  2, ',' , '');
            }

            if (count($outrosfiltros) == 0) $outrosfiltros = null;

            $param = [
                'filtros' => isset($request->filtros) ? $request->filtros : '',
                'outrosfiltros' => $outrosfiltros,
                'quantidade' => isset($request->perpage) ? $request->perpage : 20,
                'deslocamento' => isset($request->deslocamento) ? $request->deslocamento : 0,
            ];

            $cc = app()->make('App\Http\Controllers\api\v1\BSoftNFeController');
            $ret = app()->call([$cc, 'consulta_documentos'], $param);


            if (($output === 'xlsx') || ($output === 'xls') || ($output === 'csv')) {
                try {
                    $url = $this->exportexcel($ret->data, $output);
                    $ret->msg = $url;
                    $ret->ok = true;
                    $ret->data = null;
                } catch (\Throwable $th) {
                    $ret->msg = $th->getMessage();
                    $ret->ok = false;
                }
            }

            // if (!$ret->ok) throw new Exception($ret->msg);
            // $ret->ok = $pdf->ok;
            // $ret->msg = $pdf->msg ? $pdf->msg : null;
            // $ret->data = $pdf->data ? $pdf->data : null;

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }



    public function exportexcel($dataset, $format) {
      try {
          $format = mb_strtolower($format);
          if (!(($format=='xlsx') || ($format=='csv') || ($format=='xls')))
              throw new Exception('Formato inválido. Permitido somente XLSX, XLS, CSV');

          $path = 'export/' . Carbon::now()->format('Y-m-d') . '/';
          $filename = 'nfe-consulta-' . Carbon::now()->format('Y-m-d-H-i-s-') . md5(createRandomVal(5) . Carbon::now()) . '.' . $format;
          $fullfilename = '';
          ini_set('memory_limit', '-1');

          $export = new BSoftNfeConsultaExport(collect($dataset));
          $fullfilename =  $path . $filename;

          $formatExport = \Maatwebsite\Excel\Excel::XLSX;
          switch ($format) {
              case 'csv':
                  $formatExport = \Maatwebsite\Excel\Excel::CSV;
                  break;

              case 'xls':
                  $formatExport = \Maatwebsite\Excel\Excel::XLS;
                  break;

              default:
                  $formatExport = \Maatwebsite\Excel\Excel::XLSX;
                  break;
          }

          Excel::store($export, $fullfilename, 'public', $formatExport);

          $disk = Storage::disk('public');
          if (!$disk->exists($fullfilename)) throw new Exception('Nenhum arquivo encontrado no disco');

          return $disk->url($fullfilename);

      } catch (\Throwable $th) {
          throw new Exception('Erro ao gerar arquivo - ' . $th->getMessage());
      }
  }

    public function getPDF(Request $request, $chave)
    {
        $ret = new RetApiController;
        try {
            $cc = app()->make('App\Http\Controllers\api\v1\BSoftNFeController');
            $ret = app()->call([$cc, 'download'], ['chave' => $chave, 'delaydownload' => 0]);
            if (!$ret->ok) throw new Exception($ret->msg);

            $filexml = $ret->data;

            $pdf = self::exportDanfe($filexml);
            // header('Content-Type: application/pdf');
            // echo $pdf;

            $ret->ok = $pdf->ok;
            $ret->msg = $pdf->msg ? $pdf->msg : null;
            $ret->data = $pdf->data ? $pdf->data : null;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }

    public function exportDanfe($filename)
    {
        $ret = new RetApiController;
        try {

            $file = $filename;
            $disk = Storage::disk('public');
            $arquivoxml = $disk->get($file);
            if (!$arquivoxml) throw new Exception('Nenhum arquivo XML encontrado');

            // Transformando o conteúdo XML da variável $string em Objeto
            $xml = simplexml_load_string($arquivoxml);
            $chave = $xml->NFe->infNFe['Id']->__toString();
            $chave = str_replace('NFe', '', $chave) ;

            $strpath = 'nfe/pdf';
            $path = $disk->path($strpath);
            $file = $strpath . '/' . $chave . '.pdf';
            if ($disk->exists($file)) {
                $ret->ok = true;
                $ret->data = [
                    'url' => $disk->url($file),
                    'internal' => $file
                ];
                throw new Exception('Arquivo PDF reaproveitado!');
            }

            $danfe = new Danfe($arquivoxml);
            $danfe->debugMode(true);
            $danfe->printParameters( $orientacao = '', $papel = 'A4', $margSup = 5, $margEsq = 5 );
            $danfe->creditsIntegratorFooter(ENV('APP_NAME', ''));
            $pdf = $danfe->render();
            // return $pdf;

            if (!$disk->exists($strpath)) $disk->makeDirectory($strpath);
            $disk->put($file, $pdf);

            // header('Content-Type: application/pdf');
            // echo $pdf;

            $ret->ok = true;
            $ret->data = [
                'url' => $disk->url($file),
                'internal' => $file
            ];
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret;
    }

}
