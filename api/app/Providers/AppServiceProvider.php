<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Etiquetas;
use App\Models\Coletas;
use App\Models\ColetasEventos;
use Carbon\Carbon;
use App\Enums\ColetasSituacaoType;
use Exception;

class AppServiceProvider extends ServiceProvider
{
    private $coleta_depara = null;
    private $coleta_action = 'insert';
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        setLocale(LC_TIME, 'pt_BR');
        \Carbon\Carbon::setLocale('pt_BR');
        // foreach (glob(app_path() . '/helpers/*.php') as $file) {
        //     require_once($file);
        // }
    }

    public function boot()
    {
        Coletas::saving(function ($coleta) {
            $coletaDirty = $coleta->getDirty();
            $this->coleta_action = $coleta->id ? ($coleta->id > 0 ? 'update' : 'insert') : 'insert' ;
            $this->coleta_depara = [];
            foreach ($coletaDirty as $field => $newdata) {
                $olddata = $coleta->getOriginal($field);
                if (($field == 'situacao') && ((($olddata == ColetasSituacaoType::tcsLiberado) || ($olddata == ColetasSituacaoType::tcsLiberado)) && ($newdata == ColetasSituacaoType::tcsEncerrado)))
                    $this->coleta_action = 'baixa';

                if (($field == 'situacao') && (($olddata == ColetasSituacaoType::tcsEncerrado) && ($newdata == ColetasSituacaoType::tcsLiberado)))
                    $this->coleta_action = 'baixaundo';

                if (($field == 'situacao') && (($olddata !== ColetasSituacaoType::tcsCancelado) && ($newdata == ColetasSituacaoType::tcsCancelado)))
                    $this->coleta_action = 'cancel';

                if (($field == 'situacao') && (($olddata == ColetasSituacaoType::tcsCancelado) && ($newdata != ColetasSituacaoType::tcsCancelado)))
                    $this->coleta_action = 'cancelundo';

                if ($olddata != $newdata) {
                    $this->coleta_depara[$field] = [
                        'old' => $olddata,
                        'new' => $newdata,
                    ];
                }
            }
        });
        Coletas::saved(function ($coleta) {
            $logevento = new ColetasEventos();
            $logevento->created_at = Carbon::now();
            $logevento->created_usuarioid = $coleta->updated_usuarioid;
            $logevento->coletaid = $coleta->id;
            $logevento->ip = getIp();
            switch ($this->coleta_action) {
                case 'baixaundo':
                    $logevento->detalhe  = 'Justificativa: ' . $coleta->justsituacao;
                    break;
                case 'cancel':
                    $logevento->detalhe  = 'Justificativa: ' . $coleta->justsituacao;
                    break;
                case 'cancelundo':
                    $logevento->detalhe  = 'Justificativa: ' . $coleta->justsituacao;
                    break;
                default:
                    $logevento->detalhe  = '';
                    break;
            }
            $logevento->tipo  = $this->coleta_action;
            $logevento->datajson  = json_encode($this->coleta_depara);
            $ins = $logevento->save();
            if (!$ins) throw new Exception("Log de evento não foi inserido");
        });
    }
}
