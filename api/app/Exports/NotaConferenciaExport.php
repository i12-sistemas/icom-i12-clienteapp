<?php

namespace App\Exports;

use App\Models\NotaConferencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NotaConferenciaExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
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
            $dataset->cliente ? $dataset->cliente->razaosocial : '** SEM CLIENTE IDENTIFICADO **',
            $dataset->clienteid,
            formatCnpjCpf($dataset->notacnpj),
            $dataset->notanumero,
            formatRS($dataset->peso, 3, false),
            $dataset->qtde,
            $dataset->notachave,
            $dataset->created_at ? $dataset->created_at->format('d/m/Y H:i:s') : null,
            $dataset->baixado == 1 ? 'Sim' : 'Não',
            $dataset->baixado == 1 ? ($dataset->baixado_at ? $dataset->baixado_at->format('d/m/Y H:i:s') : null) : null,
            $dataset->diastotal,
            $dataset->id
        ];
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Código do cliente',
            'CNPJ da nota',
            'Número da nota',
            'Peso Kg',
            'Quantidade',
            'Chave da nota',
            'Data do lançamento',
            'Baixado',
            'Data da baixa',
            'Dias total',
            'id',
        ];
    }
}
