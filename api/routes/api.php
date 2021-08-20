<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('echo', 'EchoServerController@echo');
});

Route::prefix('v1/publico')->namespace('api\v1\publico')->group(function () {
    Route::group(['prefix' => 'notas'], function () {
        Route::get('xmlpendente', 'ClienteXMLPendenteController@xmlpendente');
        Route::post('{cnpj}/xmlpendente/envio', 'ClienteXMLPendenteController@xmlenvio');
    });
});


Route::prefix('v1')->namespace('api\v1')->group(function () {
    Route::group(['prefix' => 'login'], function () {
        Route::post('usuario/auth', 'UsuarioAuthController@auth');
        Route::post('usuario/checklogin', 'UsuarioAuthController@checklogin');
        Route::post('usuario/resetpwd/request', 'UsuarioAuthController@resetpwd_request');
        Route::post('usuario/resetpwd/checkcode', 'UsuarioAuthController@resetpwd_checkcode');
        Route::post('usuario/resetpwd/changepwd', 'UsuarioAuthController@resetpwd_changepwd');
        Route::post('usuario/resetpwd/revoke', 'UsuarioAuthController@resetpwd_revoke');
    });

    Route::group(['prefix' => 'painelcliente/login'], function () {
        Route::post('usuario/auth', 'ClienteUsuarioAuthController@auth');
        Route::post('usuario/checklogin', 'ClienteUsuarioAuthController@checklogin');
        Route::post('usuario/resetpwd/request', 'ClienteUsuarioAuthController@resetpwd_request');
        Route::post('usuario/resetpwd/checkcode', 'ClienteUsuarioAuthController@resetpwd_checkcode');
        Route::post('usuario/resetpwd/changepwd', 'ClienteUsuarioAuthController@resetpwd_changepwd');
    });
});

Route::prefix('v1/painelcliente')->namespace('api\v1\painelcliente')->middleware(['api.auth.painelcliente'])->group(function () {
    Route::get('meuperfil', 'ClienteUsuarioController@meuperfil');
    Route::post('meuperfil', 'ClienteUsuarioController@savemeuperfil');

    Route::get('followup/dashboard/1', 'FollowupController@dashboard1');
});

// app coletas 2.1
// sem autenticação de dispositivo
Route::prefix('v1/mobile')->namespace('api\v1\mobile')->group(function () {
    Route::post('dispositivo', 'DispositivoController@store');
    Route::get('dispositivo/{uuid}/requesttoken', 'DispositivoController@requestLinkAllow');

    Route::post('dispositivo/auth', 'DispositivoController@auth')->middleware(['log.appmotorista']);
});
 // com autenticação de dispositivo - autenticação do motorista
 Route::prefix('v1/mobile')->namespace('api\v1\mobile')->middleware(['auth.device', 'log.appmotorista'])->group(function () {
    Route::post('motorista/auth', 'MotoristaController@auth');
});

// com autenticação de dispositivo - autenticação do usuario
 Route::prefix('v1/mobile')->namespace('api\v1\mobile')->middleware(['auth.device'])->group(function () {
    Route::post('useradmin/auth', 'UsuarioAdminAuth@auth');
    Route::post('useradmin/checklogin', 'UsuarioAdminAuth@checklogin');
});

// com autenticação de dispositivo e de motorista
Route::prefix('v1/mobile')->namespace('api\v1\mobile')->middleware(['auth.device', 'auth.motorista', 'log.appmotorista'])->group(function () {

    Route::get('msgmotorista', 'MotoristaMsgController@listall');
    Route::post('mensagem/motorista/resposta', 'MotoristaMsgController@addRespostaMotorista');

    Route::get('coletas', 'ColetasController@listall');

    // Route::post('coleta/syncmobile', 'ColetaController@SyncMobile'); // substituido pelo coleta/baixa
    Route::post('coleta/baixa', 'ColetasController@baixa');

    // Route::get('telefones/sincronizar', 'TelefonesController@sincronizar'); // revisado, será removido pois perdeu o uso com a troca de sistema
    // Route::get('telefones/synclear', 'TelefonesController@clearsync'); // revisado, será removido pois perdeu o uso com a troca de sistema
    Route::get('telefones', 'TelefonesController@listall');

    // Route::get('motoristas/sincronizar', 'MotoristaController@sincronizar');
    // Route::get('motoristas/synclear', 'MotoristaController@clearsync');

    // Route::get('clientes/sincronizar', 'ClienteController@sincronizar');
    // Route::get('clientes/synclear', 'ClienteController@clearsync');
    // Route::get('clientes/getmaps', 'ClienteController@getGoogleMaps');
    Route::get('clientes', 'ClientesController@listall');
});

// teste
Route::prefix('v1')->namespace('api\v1')->group(function () {
    // gerar coleta
    // Route::post('coletasnotas/gerarcoletaavulsa', 'ColetasNotasController@processaColetaAvulsa');
    Route::get('teste', 'ClienteUsuarioController@teste');
    // Route::get('testa/{chave}', 'ColetasNotasController@testa');
    // Route::get('/print', 'PaletesController@printEtiqueta');
});


// administrativo
Route::prefix('v1')->namespace('api\v1')->middleware('api.auth.usuario')->group(function () {
    //consulta receitaws
    Route::get('receitaws/{cnpj}', 'ReceitaWSController@find');
    Route::get('viacep/{cep}', 'ViaCEPController@find');

    Route::get('cidade', 'CidadesController@list');
    Route::get('cidade/{id}', 'CidadesController@find');
    // insert or update
    Route::post('cidade', 'CidadesController@save');
    // delete
    Route::delete('cidade/{id}', 'CidadesController@delete');


    Route::group(['prefix' => 'usuarios'], function () {

        //listagem
        Route::get('/', 'UsuariosController@list');
        // find one
        Route::get('usuario/{id}', 'UsuariosController@find');
        // insert or update
        Route::post('/', 'UsuariosController@save');
        // delete regiao
        Route::delete('usuario/{id}', 'UsuariosController@delete');
    });

    Route::group(['prefix' => 'perfisacesso'], function () {
        //listagem
        Route::get('/', 'PerfilAcessoController@list');
        // find one
        Route::get('perfil/{id}', 'PerfilAcessoController@find');
        Route::get('perfil/{id}/usuarios', 'PerfilAcessoController@listUsuarios');
        // insert or update
        Route::post('/', 'PerfilAcessoController@save');
        // delete regiao
        Route::delete('perfil/{id}', 'PerfilAcessoController@delete');
    });

    Route::group(['prefix' => 'permissoes'], function () {
        //listagem
        Route::get('/', 'PermissoesController@listall');
    });


    Route::group(['prefix' => 'veiculotipo'], function () {
        //listagem
        Route::get('/', 'VeiculoTipoController@list');
        // find one
        Route::get('/{id}', 'VeiculoTipoController@find');
        // insert or update
        Route::post('/', 'VeiculoTipoController@save');
        // delete regiao
        Route::delete('/{id}', 'VeiculoTipoController@delete');
    });

    Route::group(['prefix' => 'veiculo'], function () {
        //listagem
        Route::get('/', 'VeiculoController@list');
        // find one
        Route::get('/{id}', 'VeiculoController@find');
        Route::get('/{id}/ultimokmacerto', 'VeiculoController@ultimokm_acerto');
        // insert or update
        Route::post('/', 'VeiculoController@save');
        // delete regiao
        Route::delete('/{id}', 'VeiculoController@delete');


        Route::post('/manutencao/ligar', 'VeiculoAlertaManutController@ligar');
        Route::post('/manutencao/desligar', 'VeiculoAlertaManutController@desligar');
        Route::get('/manutencao/{id}', 'VeiculoAlertaManutController@find');
    });

    Route::group(['prefix' => 'manutencoes'], function () {
        //listagem
        Route::get('/', 'ManutencaoController@list');
        Route::get('/agenda', 'ManutencaoController@agenda');
        // find one
        Route::get('/manutencao/{id}', 'ManutencaoController@find');
        // // insert or update
        Route::post('/', 'ManutencaoController@save');
        // // delete regiao
        // Route::delete('/{id}', 'VeiculoController@delete');

        Route::get('/print/listagemagenda', 'ManutencaoController@printlistagemagenda');

        Route::get('/dashboard1', 'ManutencaoController@dashboard1');

    });

    Route::group(['prefix' => 'dispositivo'], function () {
        Route::get('/', 'DispositivoController@list');
        Route::get('/{id}', 'DispositivoController@find');

        Route::post('/{id}/allowtoken', 'DispositivoController@allowToken');
        Route::post('/{id}/revoketoken', 'DispositivoController@revokeToken');

        Route::post('/', 'DispositivoController@save');
        Route::delete('/{id}', 'DispositivoController@delete');
    });

    Route::group(['prefix' => 'caixadepto'], function () {
        Route::get('/', 'CaixasDeptoController@list');
        Route::get('/{id}', 'CaixasDeptoController@find');
        Route::post('/', 'CaixasDeptoController@save');
        Route::delete('/{id}', 'CaixasDeptoController@delete');
    });

    Route::group(['prefix' => 'caixacategoria'], function () {
        Route::get('/', 'CaixasCategoriaController@list');
        Route::get('/{id}', 'CaixasCategoriaController@find');
        Route::post('/', 'CaixasCategoriaController@save');
        Route::delete('/{id}', 'CaixasCategoriaController@delete');
    });

    Route::group(['prefix' => 'caixas'], function () {
        Route::get('/{deptoid}/extrato', 'CaixasController@extrato');
        Route::get('/resumo', 'CaixasController@resumo');
        Route::get('/{id}', 'CaixasController@find');
        Route::post('/', 'CaixasController@save');
    });

    Route::group(['prefix' => 'manutencaoservicos'], function () {
        //listagem
        Route::get('/', 'ManutencaoServicosController@list');
        // // find one
        Route::get('/{id}', 'ManutencaoServicosController@find');
        // // insert or update
        Route::post('/', 'ManutencaoServicosController@save');
        // // delete regiao
        Route::delete('/{id}', 'ManutencaoServicosController@delete');
    });

    Route::group(['prefix' => 'unidade'], function () {
        //listagem
        Route::get('/', 'UnidadeController@list');
        // find one
        Route::get('/{id}', 'UnidadeController@find');
        // insert or update
        Route::post('/', 'UnidadeController@save');
        // delete regiao
        Route::delete('/{id}', 'UnidadeController@delete');
    });

    Route::group(['prefix' => 'produto'], function () {
        //listagem
        Route::get('/', 'ProdutoController@list');
        // find one
        Route::get('/{id}', 'ProdutoController@find');
        // insert or update
        Route::post('/', 'ProdutoController@save');
        // delete regiao
        Route::delete('/{id}', 'ProdutoController@delete');
    });

    Route::group(['prefix' => 'cliente'], function () {
        //listagem
        Route::get('/', 'ClienteController@list');
        // find one
        Route::get('/{id}', 'ClienteController@find');
        // insert or update
        Route::post('/', 'ClienteController@save');
        // delete regiao
        Route::delete('/{id}', 'ClienteController@delete');
    });

    Route::group(['prefix' => 'clienteusuarios'], function () {
        Route::get('/', 'ClienteUsuarioController@list');
        Route::get('/{id}', 'ClienteUsuarioController@find');
        Route::post('/', 'ClienteUsuarioController@save');
        Route::delete('/{id}', 'ClienteUsuarioController@delete');
    });



    Route::group(['prefix' => 'motorista'], function () {
        //listagem
        Route::get('/', 'MotoristaController@list');
        // find one
        Route::get('/{id}', 'MotoristaController@find');
        // insert or update
        Route::post('/', 'MotoristaController@save');
        // delete regiao
        Route::delete('/{id}', 'MotoristaController@delete');
    });

    Route::group(['prefix' => 'regiao'], function () {
        //listagem
        Route::get('/', 'RegioesController@list');
        // find one
        Route::get('/{id}', 'RegioesController@find');
        // insert or update
        Route::post('/', 'RegioesController@save');
        // delete regiao
        Route::delete('/{id}', 'RegioesController@delete');

        // cidades
        Route::get('/{id}/cidades', 'RegioesController@cidadeslist');
        //cidade delete
        Route::post('/{id}/cidades/delete', 'RegioesController@deleteCidades');
        Route::post('/{id}/cidades/add', 'RegioesController@addCidades');
    });

    Route::group(['prefix' => 'configuracoes'], function () {
        Route::get('/', 'ConfiguracoesController@list');
        Route::post('/', 'ConfiguracoesController@save');
    });

    Route::group(['prefix' => 'msgmotorista'], function () {
        Route::get('/', 'MotoristaMsgController@list');
        Route::get('/{id}', 'MotoristaMsgController@find');
        Route::post('/', 'MotoristaMsgController@add');
    });


    Route::group(['prefix' => 'coletas'], function () {
        //listagem
        Route::get('/', 'ColetasController@list');
        Route::get('/resumo/regiao/{regiaoid}', 'ColetasController@ultimas_coletas_porregiao');

        // // print
        // Route::get('/print', 'ColetasController@printOrdemColeta');
        // find one
        Route::get('/coleta/{id}', 'ColetasController@find');
        // Route::get('/coleta/{coletaid}/regiao/{regiaoid}/resumo', 'ColetasController@ultimas_coletas_porregiao');
        // insert or update
        Route::post('/coleta', 'ColetasController@save');
        Route::post('/updatemass', 'ColetasController@savemass');

        // delete regiao
        Route::delete('/coleta/{id}', 'ColetasController@delete');

        Route::get('/print', 'ColetasController@printOrdemColeta');
        Route::get('/print/listagem', 'ColetasController@print_listagem');
        Route::post('/share', 'ColetasController@share');

        Route::get('/coleta/{id}/eventos', 'ColetasController@eventos_list');
        Route::post('/coleta/{id}/encerrar', 'ColetasController@save_encerrar');
        Route::post('/coleta/{id}/encerrardesfazer', 'ColetasController@save_encerrar_desfazer');
        Route::post('/coleta/{id}/cancelar', 'ColetasController@cancelar');
        Route::post('/coleta/{id}/cancelardesfazer', 'ColetasController@cancelar_desfazer');

        Route::get('/dashboard1', 'ColetasController@dashboard1');
    });

    Route::group(['prefix' => 'telefones'], function () {
        //listagem
        Route::get('/', 'TelefonesController@list');
        Route::get('/{id}', 'TelefonesController@find');
        Route::post('/', 'TelefonesController@save');
        Route::delete('/{id}', 'TelefonesController@delete');
    });

    Route::group(['prefix' => 'despesaviagem'], function () {
        //listagem
        Route::get('/', 'DespesasViagemController@list');
        Route::get('/{id}', 'DespesasViagemController@find');
        Route::post('/', 'DespesasViagemController@save');
        Route::delete('/{id}', 'DespesasViagemController@delete');
    });


    Route::group(['prefix' => 'notaconferencia'], function () {
        Route::get('/', 'NotaConferenciaController@list');
        Route::post('/', 'NotaConferenciaController@saveadd');
        Route::post('/manualedit', 'NotaConferenciaController@saveeditmanual');
        Route::put('/', 'NotaConferenciaController@savebaixa');
        Route::get('/print/listagem', 'NotaConferenciaController@print_listagem');
    });


    Route::group(['prefix' => 'coletasnotas'], function () {
        Route::get('/', 'ColetasNotasController@list');
        Route::get('/find', 'ColetasNotasController@find');
        Route::post('/', 'ColetasNotasController@save');
        Route::post('/xmlpendente/sharelink', 'ColetasNotasController@addLinkInputXMLNFe');
    });

    Route::group(['prefix' => 'acertoviagem'], function () {
        //listagem
        Route::get('/', 'AcertoViagemController@list');
        Route::get('/acerto/{id}', 'AcertoViagemController@find');
        Route::post('/acerto', 'AcertoViagemController@save');

        Route::get('/print/fichaliberacao', 'AcertoViagemController@printFichaLiberacao');
        Route::get('/print/acertodetalhe', 'AcertoViagemController@printAcertoDetalhe');

        Route::post('/acerto/{id}/encerrar', 'AcertoViagemController@encerrar');
        Route::post('/acerto/{id}/encerrardesfazer', 'AcertoViagemController@encerrardesfazer');
    });


    Route::group(['prefix' => 'etiquetas'], function () {
        Route::get('/', 'EtiquetasController@list');
        Route::get('/find/ean/{ean}', 'EtiquetasController@find');
        Route::get('/print', 'EtiquetasController@printEtiqueta');
    });


    Route::group(['prefix' => 'guaritacheck'], function () {
        Route::get('/minhaguarita', 'GuaritaCheckController@findMinhaGuarita');
        Route::post('/minhaguarita', 'GuaritaCheckController@saveMinhaGuarita');
        Route::post('/minhaguarita/encerrar', 'GuaritaCheckController@encerrarMinhaGuarita');
        Route::post('/minhaguarita/itens/delete', 'GuaritaCheckController@deleteItensMinhaGuarita');
        Route::delete('/minhaguarita', 'GuaritaCheckController@deleteMinhaGuarita');
        // Route::get('/', 'CargaTransferController@list');
        // Route::get('/carga/{id}', 'CargaTransferController@find');
        // Route::delete('/carga/{id}', 'CargaTransferController@delete');
        // Route::get('/carga/{id}/print/detalhe', 'CargaTransferController@printDetalhe');
        // Route::post('/carga/{id}/alterarstatus/{status}', 'CargaTransferController@changestatus');

        // Route::group(['prefix' => '/carga/{cargaid}/itens'], function () {
        //     Route::post('/conferir', 'CargaTransferController@item_conferir');
        //     Route::post('/', 'CargaTransferController@item_save');
        //     Route::delete('/', 'CargaTransferController@item_delete');
        //     Route::post('/id/{itemid}/etiquetas/gerar', 'CargaTransferController@etiquetas_gerar');
        // });
    });

    Route::group(['prefix' => 'cargaentrada'], function () {
        Route::get('/', 'CargaEntradaController@list');
        Route::get('/carga/{id}', 'CargaEntradaController@find');
        Route::delete('/carga/{id}', 'CargaEntradaController@delete');
        Route::get('/carga/{id}/print/detalhe', 'CargaEntradaController@printDetalhe');
        Route::post('/carga/{id}/alterarstatus/{status}', 'CargaEntradaController@changestatus');
        Route::post('/carga', 'CargaEntradaController@save');

        Route::group(['prefix' => '/carga/{cargaid}/itens'], function () {
            Route::post('/conferir', 'CargaEntradaController@item_conferir');
            Route::post('/', 'CargaEntradaController@item_save');
            Route::delete('/id/{itemid}', 'CargaEntradaController@item_delete');
            Route::post('/id/{itemid}/etiquetas/gerar', 'CargaEntradaController@etiquetas_gerar');
        });
    });

    Route::group(['prefix' => 'cargatransfer'], function () {
        Route::get('/', 'CargaTransferController@list');
        Route::get('/carga/{id}', 'CargaTransferController@find');
        Route::delete('/carga/{id}', 'CargaTransferController@delete');
        Route::get('/carga/{id}/print/detalhe', 'CargaTransferController@printDetalhe');
        Route::post('/carga/{id}/alterarstatus/{status}', 'CargaTransferController@changestatus');
        Route::post('/carga', 'CargaTransferController@save');

        Route::group(['prefix' => '/carga/{cargaid}/itens'], function () {
            Route::post('/conferir', 'CargaTransferController@item_conferir');
            Route::post('/', 'CargaTransferController@item_save');
            Route::delete('/', 'CargaTransferController@item_delete');
            Route::post('/id/{itemid}/etiquetas/gerar', 'CargaTransferController@etiquetas_gerar');
        });
    });


    Route::group(['prefix' => 'cargaentrega'], function () {
        Route::get('/', 'CargaEntregaController@list');
        Route::get('/carga/{id}', 'CargaEntregaController@find');
        Route::get('/carga/{id}/print/detalhe', 'CargaEntregaController@printDetalhe');
        Route::post('/carga/{id}/status/alterar', 'CargaEntregaController@changestatus');
        Route::post('/carga', 'CargaEntregaController@save');

        Route::post('/baixa/entrega', 'CargaEntregaController@entregaBaixa');

        Route::group(['prefix' => '/carga/{cargaid}/itens'], function () {
            Route::post('/', 'CargaEntregaController@item_add');
            Route::post('/update', 'CargaEntregaController@item_update');

            Route::delete('/', 'CargaEntregaController@item_delete');
            Route::post('/id/{itemid}/etiquetas/gerar', 'CargaEntregaController@etiquetas_gerar');

        });
    });

    Route::group(['prefix' => 'paletes'], function () {
        Route::get('/', 'PaletesController@list');
        Route::post('/', 'PaletesController@save');
        Route::delete('/palete/{id}', 'PaletesController@delete');
        Route::get('/palete/{id}', 'PaletesController@find');
        Route::post('/palete/{id}/etiquetas/add', 'PaletesController@add_etiquetas');
        Route::post('/palete/{id}/etiquetas/delete', 'PaletesController@delete_etiquetas');

        Route::get('/print', 'PaletesController@printEtiqueta');

        Route::post('/palete/{id}/status/alterar', 'PaletesController@changestatus');
    });

    Route::group(['prefix' => 'orcamentos'], function () {
        //listagem
        Route::get('/', 'OrcamentosController@list');
        // // find one
        Route::get('/orcamento/{id}', 'OrcamentosController@find');

        Route::get('/print', 'OrcamentosController@printOrcamento');

        // // insert or update
        Route::post('/orcamento', 'OrcamentosController@save');
        Route::post('/orcamento/{id}/aprovar', 'OrcamentosController@changeSituacaoAprovado');
        Route::post('/orcamento/{id}/reprovar', 'OrcamentosController@changeSituacaoReprovado');
        Route::post('/orcamento/{id}/reabrir', 'OrcamentosController@undoSituacaoToAberto');
    });

    Route::group(['prefix' => 'nfe'], function () {
        Route::get('/export/pdf/{chave}', 'NFeController@getPDF');
        Route::get('/consulta', 'NFeController@consulta_detalhe');
    });

    Route::group(['prefix' => 'followup'], function () {
        Route::get('/', 'FollowupController@list');
        Route::get('/print/listagem', 'FollowupController@print_listagem');
        Route::post('/updatemass', 'FollowupController@savemass');

        Route::get('/{id}/log', 'FollowupController@log');

        Route::get('/dashboard/1', 'FollowupController@dashboard1');

        Route::get('/list/clientesfupid', 'FollowupController@list_clientesfupid');
        Route::get('/list/compradores', 'FollowupController@list_compradores');
        Route::get('/list/itemdescricao', 'FollowupController@list_itemdescricao');
        Route::get('/list/itemid', 'FollowupController@list_itemid');

        Route::group(['prefix' => 'planilhas'], function () {
            Route::post('/addfile', 'FollowupFilesController@addfile');
            Route::post('/planilha/read', 'FollowupFilesController@readplanilha');
            Route::post('/export', 'FollowupFilesController@exportfile');
            Route::get('/', 'FollowupFilesController@list');

            Route::delete('/{id}', 'FollowupFilesController@delete');
            Route::post('/deletemass ', 'FollowupFilesController@deletemass');
        });
    });


    Route::group(['prefix' => 'errosfollowup'], function () {
        Route::get('/', 'FollowupErrosController@list');
        Route::get('/{id}', 'FollowupErrosController@find');
        // // insert or update
        Route::post('/', 'FollowupErrosController@save');
        // // delete regiao
        Route::delete('/{id}', 'FollowupErrosController@delete');
    });



});
