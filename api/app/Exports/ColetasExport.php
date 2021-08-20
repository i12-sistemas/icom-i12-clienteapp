<?php

namespace App\Exports;

use App\Models\Coletas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

use App\Enums\ColetasSituacaoType;
use App\Enums\ColetasEncerramentoTipoType;

class ColetasExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
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
        $origem = '*Não identificado';
        switch ($dataset->origem) {
            case '1':
                $origem = '1-Interno Manual';
                break;
            case '2':
                $origem = '2-Orçamento';
                break;
            case '3':
                $origem = '3-Painel do Cliente';
                break;
            case '4':
                $origem = '4-Coleta Avulsa';
                break;
        };

        return [
            $origem,
            $dataset->id,
            $dataset->chavenota,
            $dataset->situacao . '-' . ColetasSituacaoType::getDescription($dataset->situacao),
            $dataset->dhcoleta ? $dataset->dhcoleta->format('d/m/Y') : null,
            $dataset->dhbaixa ? $dataset->dhbaixa->format('d/m/Y H:i:s') : null,
            $dataset->encerramentotipo ? $dataset->encerramentotipo . '-' . ColetasEncerramentoTipoType::getDescription($dataset->encerramentotipo) : '',
            $dataset->ctenumero ? $dataset->ctenumero : '',
            $dataset->veiculoexclusico === 1 ? 'Sim' : 'Não',
            $dataset->cargaurgente === 1 ? 'Sim' : 'Não',
            $dataset->produtosperigosos === 1 ? 'Sim' : 'Não',
            number_format($dataset->peso, 3, '.' , ''),
            number_format($dataset->qtde, 0, '.' , ''),
            $dataset->especie,
            $dataset->motorista ? $dataset->motorista->nome : '*Indefinido',
            $dataset->clienteorigem ? $dataset->clienteorigem->razaosocial : '*Indefinido',
            $dataset->coletaregiao ? $dataset->coletaregiao->id . '-' . $dataset->coletaregiao->regiao : '',
            $dataset->coletacidade ? $dataset->coletacidade->cidade . ' / ' . $dataset->coletacidade->uf : '',
            $dataset->clientedestino ? $dataset->clientedestino->razaosocial : '*Indefinido',
            $dataset->clientedestino ? $dataset->clientedestino->cidade->cidade . ' / ' . $dataset->clientedestino->cidade->uf : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Origem',
            'Núm. Coleta',
            'Chave Nota',
            'Situação',
            'Data Coleta',
            'Data da baixa/cancelamento',
            'Forma de Encerramento',
            'Número do CT-e',
            'Exclusivo',
            'Carga Urgente',
            'Produtos Perigosos',
            'Peso',
            'Qtde',
            'Espécie',
            'Motorista',
            'Cliente de origem',
            'Região',
            'Cidade/UF',
            'Cliente de destino',
            'Cidade/UF'
        ];
    }
}
