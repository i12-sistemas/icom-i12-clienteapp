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


class FollowupLogExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    public function __construct($dataset)
    {
        $this->dataset = $dataset;
    }

    public function collection()
    {
        return $this->dataset;
    }

    public function map($dataset): array
    {
        return [

            ($dataset->tipoorigem === 1 ? 'Edição (M)' : ($dataset->tipoorigem === 2 ? 'Novo (I)' : 'Edição (I)')),
            $dataset->created_at ? $dataset->created_at->format('d/m/Y') : '',
            $dataset->created_at ? $dataset->created_at->format('h:i:s') : '',
            $dataset->created_usuario ? $dataset->created_usuario->nome : '',
            $dataset->datasolicitacao ? $dataset->datasolicitacao->format('d/m/Y') : '',
            $dataset->datapromessa ? $dataset->datapromessa->format('d/m/Y') : '',

            ($dataset->erroagendastatus === '2' ? 'ERRO' : ($dataset->erroagendastatus === '1' ? 'OK' : 'SEM STATUS')),
            $dataset->erroagendastatus === '2' ? $dataset->erroagenda->descricao : '',

            ($dataset->errodtpromessastatus === '2' ? 'ERRO' : ($dataset->errodtpromessastatus === '1' ? 'OK' : 'SEM STATUS')),
            $dataset->errodtpromessastatus === '2' ? $dataset->errodtpromessa->descricao : '',

            ($dataset->errocoletastatus === '2' ? 'ERRO' : ($dataset->errocoletastatus === '1' ? 'OK' : '')),
            $dataset->errocoletastatus === '2' ? $dataset->errocoleta->descricao : '',
            ($dataset->iniciofollowup === '2' ? 'FORNECEDOR' : ($dataset->iniciofollowup === '1' ? 'FOLLOW-UP' : 'SEM STATUS')), //0=Sem status, 1=Conecta/Follow-UP, 2=Fornecedor
            $dataset->notafiscal,
            $dataset->totallinhaoc ? $dataset->totallinhaoc : 0,
            $dataset->qtdesolicitada ? $dataset->qtdesolicitada : 0,
            $dataset->qtderecebida,  // number_format($dataset->qtderecebida, 2, ',' , ''),
            $dataset->qtdedevida ? $dataset->qtdedevida : 0,
            $dataset->observacao,
            $dataset->itemnumerolinhapedido,
            ($dataset->statusconfirmacaocoleta === '2' ? 'ERRO' : ($dataset->statusconfirmacaocoleta === '1' ? 'OK' : 'SEM STATUS')),
            $dataset->dataagendamentocoleta ? $dataset->dataagendamentocoleta->format('d/m/Y') : '',
            $dataset->dataconfirmacao ? $dataset->dataconfirmacao->format('d/m/Y') : '',
            $dataset->coletaid,
            $dataset->datacoleta ? $dataset->datacoleta->format('d/m/Y') : '',
            $dataset->ordemcompra,
            $dataset->ordemcompradig ? $dataset->ordemcompradig : '-',

            $dataset->id
        ];
    }

    public function headings(): array
    {
        return [
            'Origem',
            'Data da alteração',
            'Hora da alteração',
            'Usuário',

            'Data Solicitação',
            'Data Promessa',
            'Status Agenda',
            'Erro Agenda',

            'Status Promessa',
            'Erro Promessa',

            'Status Coleta',
            'Erro Coleta',

            'Início FUP',
            'Nota Fiscal',

            'Total Linha OC',
            'Qtde Sol.',
            'Qtde Receb',
            'Qtde Dev',
            'Observação',
            'Nº linha do item',
            'St. Confirmação',
            'Agenda Coleta',
            'Data Confirmação',
            'Nº da Coleta',
            'Data Coleta',
            'Ordem Compra',
            'Ordem Compra Dígito',

            'id',
        ];
    }
}
