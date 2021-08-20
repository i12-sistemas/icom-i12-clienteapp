<?php

namespace App\Exports;

use App\Models\Followup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;


class FollowupListagemExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    public function __construct($dataset, $visiblecolumns)
    {
        $this->visiblecolumns = $visiblecolumns;
        $this->dataset = $dataset;
    }

    public function collection()
    {
        return $this->dataset;
    }

    public function getColData ($pName, $row, $islabel = true)
    {
        $name = mb_strtolower($pName);
        switch ($name) {
            case 'id':
                return ($islabel ? 'ID' : $row->id);
                break;
            case 'notafiscal':
                return ($islabel ? 'Nota Fiscal' : $row->notafiscal);
                break;

            case 'fornecrazao':
                return ($islabel ? 'Fornecedor Razão Social' : $row->fornecrazao);
                break;
            case 'forneccnpj':
                return ($islabel ? 'Fornecedor CNPJ' : formatCnpjCpf($row->forneccnpj));
                break;
            case 'fornectelefone':
                return ($islabel ? 'Fornecedor Telefone' : $row->fornectelefone);
                break;
            case 'forneccidade':
                return ($islabel ? 'Fornecedor Cidade Fiscal' : ($row->forneccidade . '/' . $row->fornecuf));
                break;
            case 'fornecedorid':
                return ($islabel ? 'Fornecedor ID' : $row->fornecedorid);
                break;

            case 'ordemcompra':
                return ($islabel ? 'O.C.' : $row->ordemcompra);
                break;
            case 'ordemcompradig':
                return ($islabel ? 'O.C. Dígito' : $row->ordemcompradig);
                break;
            case 'observacao':
                return ($islabel ? 'Observação' : $row->observacao);
                break;
            case 'requisicao':
                return ($islabel ? 'Requisição' : $row->requisicao);
                break;
            case 'itemnumerolinhapedido':
                return ($islabel ? 'Nº da linha item' : $row->itemnumerolinhapedido);
                break;
            case 'comprador':
                return ($islabel ? 'Comprador' : $row->comprador);
                break;
            case 'email':
                return ($islabel ? 'E-mail' : $row->email);
                break;
            case 'itemid':
                return ($islabel ? 'Cód. do item' : $row->itemid);
                break;
            case 'itemdescricao':
                return ($islabel ? 'Item descrição' : $row->itemdescricao);
                break;

            case 'aprovacaooc':
                return ($islabel ? 'Aprovação O.C.' : ($row->aprovacaooc ? $row->aprovacaooc->format('d/m/Y') : ''));
                break;
            case 'coletaid':
                return ($islabel ? 'Núm. Coleta' : $row->coletaid);
                break;
            case 'contato':
                return ($islabel ? 'Contato' : $row->contato);
                break;
            case 'dhimportacao':
                return ($islabel ? 'Data/Hora importação' : ($row->dhimportacao ? $row->dhimportacao->format('d/m/Y H:i:s') : ''));
                break;
            case 'qtdedevida':
                return ($islabel ? 'Qtde Devida' : ($row->qtdedevida ? $row->qtdedevida : 0));
                break;
            case 'vlrunitario':
                return ($islabel ? 'Vlr. Unitário' : ($row->vlrunitario ? $row->vlrunitario : 0));
                break;
            case 'qtderecebida':
                return ($islabel ? 'Qtde Recebida' : ($row->qtderecebida ? $row->qtderecebida : 0));
                break;
            case 'qtdesolicitada':
                return ($islabel ? 'Qtde Solicitada' : ($row->qtdesolicitada ? $row->qtdesolicitada : 0));
                break;




            case 'datapromessa':
                return ($islabel ? 'Data Promessa' : ($row->datapromessa ? $row->datapromessa->format('d/m/Y') : ''));
                break;
            case 'datacoleta':
                return ($islabel ? 'Data Coleta' : ($row->datacoleta ? $row->datacoleta->format('d/m/Y') : ''));
                break;
            case 'datahorafollowup':
                return ($islabel ? 'Data/hora FUP' : ($row->datahora_followup ? $row->datahora_followup->format('d/m/Y H:i:s') : ''));
                break;

            case 'datasolicitacao':
                return ($islabel ? 'Data solicitação' : ($row->datasolicitacao ? $row->datasolicitacao->format('d/m/Y') : ''));
                break;
            case 'dataagendamentocoleta':
                return ($islabel ? 'Data agendamento coleta' : ($row->dataagendamentocoleta ? $row->dataagendamentocoleta->format('d/m/Y') : ''));
                break;
            case 'dataconfirmacao':
                return ($islabel ? 'Data confirmação' : ($row->dataconfirmacao ? $row->dataconfirmacao->format('d/m/Y') : ''));
                break;
            case 'dataliberacao':
                return ($islabel ? 'Data liberação' : ($row->dataliberacao ? $row->dataliberacao->format('d/m/Y') : ''));
                break;
            case 'updatedusuario':
                return ($islabel ? 'Usuário última atualização' : ($row->updated_usuario ? $row->updated_usuario->nome : ''));
                break;

            case 'clienterazaosocial':
                return ($islabel ? 'Cliente Razão Social' : ($row->cliente ? $row->cliente->razaosocial : ''));
                break;

            case 'clientefollowupid':
                return ($islabel ? 'Cliente FUP ID' :($row->cliente ? $row->cliente->followupid : ''));
                break;

            case 'clienteid':
                return ($islabel ? 'Cliente ID' : $row->clienteid);
                break;

            case 'cliente':
                return ($islabel ? 'Cliente' : ($row->cliente ? $row->cliente->fantasia_followup : ''));
                break;


            case 'erroagendastatus':
                if (!$islabel) {
                    $v = ($row->erroagendastatus === '2' ? 'ERRO' : ($row->erroagendastatus === '1' ? 'OK' : 'SEM STATUS'));
                    if ($row->erroagendastatus === '2') $v = $v . ' :: ' . ($row->erroagenda ? $row->erroagenda->descricao : '?');
                }
                return ($islabel ? 'Status Agendamento' : $v);
                break;

            case 'errocoletastatus':
                if (!$islabel) {
                    $v = ($row->errocoletastatus === '2' ? 'ERRO' : ($row->errocoletastatus === '1' ? 'OK' : 'SEM STATUS'));
                    if ($row->errocoletastatus === '2') $v = $v . ' :: ' . ($row->errocoleta ? $row->errocoleta->descricao : '?');
                }
                return ($islabel ? 'Status Coleta' : $v);
                break;

            case 'errodtpromessastatus':
                if (!$islabel) {
                    $v = ($row->errodtpromessastatus === '2' ? 'ERRO' : ($row->errodtpromessastatus === '1' ? 'OK' : 'SEM STATUS'));
                    if ($row->errodtpromessastatus === '2') $v = $v . ' :: ' . ($row->errodtpromessa ? $row->errodtpromessa->descricao : '?');
                }
                return ($islabel ? 'Status Data Promessa' : $v);
                break;

            case 'iniciofollowup':
                if (!$islabel) {
                    $v = ($row->iniciofollowup === '2' ? 'FORNECEDOR' : ($row->iniciofollowup === '1' ? 'FOLLOW-UP' : 'SEM STATUS'));
                }
                return ($islabel ? 'Início FUP' : $v);
                break;


            case 'statusconfirmacaocoleta':
                if (!$islabel) {
                    $v = ($row->statusconfirmacaocoleta === '2' ? 'ERRO' : ($row->statusconfirmacaocoleta === '1' ? 'OK' : 'SEM STATUS'));
                }
                return ($islabel ? 'Status Confirmação Coleta' : $v);
                break;

            default:
                return '<naoachounada>';
                break;
        }
    }

    public function map($dataset): array
    {
        $dados = [];
        foreach ($this->visiblecolumns as $col) {
            $v = self::getColData($col, $dataset, false);
            if ($v !== '<naoachounada>') $dados[] = $v;
        }
        return $dados;
    }

    public function headings(): array
    {
        $dados = [];
        foreach ($this->visiblecolumns as $col) {
            $v = self::getColData($col, null, true);
            if ($v !== '<naoachounada>') $dados[] = $v;
        }
        return $dados;
    }
}
