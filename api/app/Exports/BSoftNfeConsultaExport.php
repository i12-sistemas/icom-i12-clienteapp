<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

use Carbon\Carbon;

class BSoftNfeConsultaExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
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
        $data = Carbon::createFromFormat('Y-m-d', $dataset['data_emissao']);
        return [
            $dataset['numero'],
            $data->format('d/m/Y'),
            formatCnpjCpf($dataset['emitente_cnpj']),
            $dataset['emitente_nome'],
            formatCnpjCpf($dataset['destinatario_cnpj']),
            $dataset['destinatario_nome'],
            number_format($dataset['peso'],  3, '.' , ''),
            number_format($dataset['valor'], 2, '.' , ''),
            $dataset['chave_acesso'],
            $dataset['id']
        ];

    }

    public function headings(): array
    {
        return [
            'Número',
            'Data de Emissão',
            'Emitente CNPJ',
            'Emitente Nome',
            'Destinatário CNPJ',
            'Destinatário Nome',
            'Peso',
            'Valor',
            'Chave de acesso',
            'id',
        ];
    }
}
