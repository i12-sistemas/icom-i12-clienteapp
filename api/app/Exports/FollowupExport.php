<?php

namespace App\Exports;

use App\Models\Followup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class FollowupExport implements FromCollection, WithColumnFormatting, WithMapping,  WithHeadings, WithColumnWidths, ShouldAutoSize
{
    use Exportable;

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
            $dataset->cliente ? $dataset->cliente->fantasia_followup : '(indefinido)',
            $dataset->cliente ? $dataset->cliente->followupid : '(indefinido)',
            $dataset->requisicao,
            $dataset->aprovacaorc ? $dataset->aprovacaorc->format('d/m/Y') : '-',
            $dataset->compradelegada,
            $dataset->normalurgente,
            $dataset->tipooc,
            $dataset->ordemcompra,
            $dataset->ordemcompradig ? $dataset->ordemcompradig : '-',
            $dataset->statusoc,
            ($dataset->statusliberacao ? $dataset->statusliberacao : '') === '' ? '-' : $dataset->statusliberacao,
            $dataset->situacaolinha,
            $dataset->comprador,
            ($dataset->compradoracordo ? $dataset->compradoracordo : '') === '' ? '-' : $dataset->compradoracordo,
            $dataset->datanecessidaderc ? $dataset->datanecessidaderc->format('d/m/Y') : '',
            $dataset->criacaooc ? $dataset->criacaooc->format('d/m/Y') : '',
            $dataset->aprovacaooc ? $dataset->aprovacaooc->format('d/m/Y') : '',
            $dataset->dataliberacao ? $dataset->dataliberacao->format('d/m/Y') : '-',
            $dataset->datapromessa ? $dataset->datapromessa->format('d/m/Y') : '',
            // pendente
            // dias_atraso_ = f.dia_atraso,
            // dias_atraso_ = (hoje - f.data_promessa).Days - 0,
            $dataset->diaatraso,
            $dataset->fornecrazao,
            $dataset->condpagto,
            '0' . substr($dataset->forneccnpj, 0, 8) . '/' . substr($dataset->forneccnpj, 8, 6), // CNPJ no padrão doido demais
            $dataset->forneccidade,
            $dataset->fornecuf,


            $dataset->contato,
            ($dataset->fornectelefone ? $dataset->fornectelefone : '') === '' ? '-' : $dataset->fornectelefone,
            $dataset->email,
            $dataset->itemnumerolinhapedido,
            $dataset->grupo,
            $dataset->familia,
            $dataset->subfamilia,
            $dataset->itemid,
            $dataset->itemdescricao,
            $dataset->qtdesolicitada ? $dataset->qtdesolicitada : 0,
            $dataset->udm,
            $dataset->qtderecebida,  // number_format($dataset->qtderecebida, 2, ',' , ''),
            $dataset->qtdedevida ? $dataset->qtdedevida : 0,
            $dataset->vlrunitario ? $dataset->vlrunitario : 0,
            $dataset->vlrultcompra ? $dataset->vlrultcompra : 0,
            $dataset->moeda,
            $dataset->totallinhaoc ? $dataset->totallinhaoc : 0,
            $dataset->dataultimaentrada ? $dataset->dataultimaentrada->format('d/m/Y') : '-',
            ($dataset->tipofrete ? $dataset->tipofrete : '') === '' ? '-' : $dataset->tipofrete,

            ($dataset->erroagendastatus === '2' ? 'ERRO' : ($dataset->erroagendastatus === '1' ? 'OK' : '')),
            $dataset->erroagendastatus === '2' ? $dataset->erroagenda->descricao : '',
            $dataset->datasolicitacao ? $dataset->datasolicitacao->format('d/m/Y') : '',
            $dataset->dataagendamentocoleta ? $dataset->dataagendamentocoleta->format('d/m/Y') : '',
            ($dataset->iniciofollowup === '2' ? 'Fornecedor' : ($dataset->iniciofollowup === '1' ? 'Follow-UP' : '')), //0=Sem status, 1=Conecta/Follow-UP, 2=Fornecedor

            ($dataset->errodtpromessastatus === '2' ? 'ERRO' : ($dataset->errodtpromessastatus === '1' ? 'OK' : '')),
            $dataset->errodtpromessastatus === '2' ? $dataset->errodtpromessa->descricao : '',

            $dataset->dataconfirmacao ? $dataset->dataconfirmacao->format('d/m/Y') : '',

            ($dataset->statusconfirmacaocoleta === '2' ? 'ERRO' : ($dataset->statusconfirmacaocoleta === '1' ? 'OK' : '')),

            ($dataset->errocoletastatus === '2' ? 'ERRO' : ($dataset->errocoletastatus === '1' ? 'OK' : '')),
            $dataset->errocoletastatus === '2' ? $dataset->errocoleta->descricao : '',


            $dataset->coleta ? ($dataset->coleta->dhbaixa ? $dataset->coleta->dhbaixa->format('d/m/Y') : '') : ($dataset->datacoleta ? $dataset->datacoleta->format('d/m/Y') : ''), // data_coleta_realizada = (h.id_coleta != null) ? h?.coleta?.data_baixa : h?.data_coleta,
            $dataset->notafiscal,
            $dataset->observacao,
            $dataset->coleta ? ($dataset->coleta->dhbaixa ? 'Concluída pela Conecta' : 'Aberta') : 'Aberta', // status_fup = ((h?.coleta?.data_baixa == null) ? "Aberta" : ("Concluída pela Conecta")),

            $dataset->coleta ? 'Coleta ' . $dataset->coleta->id . ($dataset->coleta->dhbaixa ? $dataset->coleta->dhbaixa->format('d/m/Y') : '') : '', // num_coleta = ((h?.coleta?.data_baixa == null) ? "" : ("Coleta " + h?.coleta?.id_coleta.ToString() + " baixada em " + h?.coleta?.data_baixa.Value.ToShortDateString())),

            $dataset->dhimportacao ? $dataset->dhimportacao->format('d/m/Y') : '',
            $dataset->datahora_followup ? $dataset->datahora_followup->format('d/m/Y') : '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'AI' => NumberFormat::FORMAT_NUMBER_00,
            // 'AK' => NumberFormat::FORMAT_NUMBER_00,
            // 'AL' => NumberFormat::FORMAT_NUMBER_00,
            // 'AM' => NumberFormat::FORMAT_NUMBER_00,
            // 'AN' => NumberFormat::FORMAT_NUMBER_00,
            // 'AP' => NumberFormat::FORMAT_NUMBER_00,
            // 'B' => DataType::TYPE_STRING,
            // 'C' => DataType::TYPE_STRING2
        ];
    }

    public function columnWidths(): array
    {
        return [
            'C' => 15,
        ];
    }

    public function headings(): array
    {
        return [
            'Empresa',
            'Organização para Entrega OC',
            'Num RC',
            'Aprovação RC',
            'Compra Delegada',
            'Normal/Urgente',
            'Tipo OC',
            'Num OC',
            'Num Liberação',
            'Status OC',
            'Status Liberação',
            'Situação Linha',
            'Comprador',
            'Comprador Acordo',
            'Data Necessid. RC',
            'Criação OC (1)',
            'Aprovação OC (2)',
            'Data Liberação',
            'Data Promessa OC (4)',
            'Dias Atraso (Hoje - Data Promessa(4))',
            'Fornecedor',
            'Cond. Pagto',
            'CNPJ',
            'Cidade',
            'Estado',
            'Contato Fornecedor',
            'Telefone',
            'E-mail',
            'Linha Item',
            'Grupo',
            'Família',
            'SubFamilia',
            'Item',
            'Descrição',
            'Qtde (6)',
            'UDM',
            'Qtde Recebida (7)',
            'Saldo Linha (6) - (7)',
            'Valor Unitário',
            'Valor Última Compra',
            'Moeda Tipo',
            'Total Linha OC',
            'Ultima Entrada',
            'Tipo Frete',
            'Status Agend.',
            'Erro - Agendamento',
            'Dt Solicitação',
            'Dt Agend. Coleta',
            'Agendamento',
            'Agend. até Promessa',
            'Erro - Data Promessa',
            'Dt Conf. Coleta',
            'Conf. Coleta',
            'Status Coleta',
            'Erro - Coleta',
            'Dt Coleta Realizada',
            'N° Nota Fiscal',
            'Observações',
            'Status',
            'Nº coleta',
            'Dt. Importação',
            'Dt. Atl. FUP',
        ];
    }
}
