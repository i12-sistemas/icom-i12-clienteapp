<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impressão de etiquetas</title>
    <style>
    @page {
        size: 10cm 10cm landscape;
        margin: 1mm;
    }
    body {
        /* border: 1px solid black; */
        background: white;
        position: relative;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    </style>
</head>

<body>
@foreach ($dataset as $key => $row)
    <div style="width: 100%; position: absolute; left: 0; top: 0; height: 98mm; width: 98mm; 1px solid rgb(223, 14, 14); babk">

        <div style="width: 100%; position: relative; height: 21mm; border-bottom: 1px solid black;">
            <div style="width: 4cm; font-size: 14px; height: 12mm; position: absolute; top: 4mm; left: 1mm;">
                <img src="{{url('/')}}/img/logo-conecta.png" style="width:100%;height:100%;max-width:4cm;max-height:15mm;object-fit: scale-down;">
            </div>
            <div style="width: 5cm; height: 15mm; position: absolute; top: 1mm; right: 1mm;">
                <img src="data:image/png;base64,{{ $row->barcode() }}" alt="barcode" style="width:100%;height:100%;" >
            </div>
            <div style="width: 5cm; height: 10mm; position: absolute; top: 16.5mm; right: 1mm; margin: 0; padding: 0; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; ">
                {{ $row->ean13 }}
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 12mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px;  border-bottom: 1px solid black;">
            <div style="width: 100%; font-size: 8px; position: relative; top: 1mm; left: 1mm; ">
                Fornecedor/Origem
            </div>
            <div style="width: 96%; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; ">
                <?php
                    $origem = $row->origem();
                    echo ($origem) ? $origem->razaosocial : 'Não identificado!';
                    ?>
            </div>
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; ">
                CNPJ: <span style="font-weight: bold;">{{ $origem ? formatCnpjCpf($origem->cnpj) : '' }}</span>
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 16mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-bottom: 1px solid black;">
            <div style="width: 100%; height: 6mm; font-size: 16px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 48%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Nota Fiscal:
                    <span style="font-size: 18px; font-weight: bold;">{{$row->itemcargaentrada->coletanota->notanumero}}</span>
                </div>
                <div style="width: 48%; position: absolute; top: 0.3mm; right: : 0.3mm; text-align:right;">
                    Volume:
                    <span style="font-size: 18px; font-weight: bold;">
                        {{str_pad( $row->volnum, 3, "0", STR_PAD_LEFT) }} / {{ str_pad( $row->voltotal, 3, "0", STR_PAD_LEFT)  }}
                    </span>
                </div>
            </div>
            <div style="width: 100%; height: 4.5mm; font-size: 12px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 48%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    Coleta:
                    <span style="font-weight: bold">{{$row->itemcargaentrada->coletaid}}</span>
                </div>
                <div style="width: 48%; position: absolute; top: 0.5mm; right: 0.3mm; text-align:right; font-size: 10px;">
                    Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                </div>
            </div>
            <div style="width: 100%; height: 3.5mm; font-size: 10px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 40%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    TrackID:
                    <span >{{ $row->itemcargaentrada->cargaentrada->id . '.' . $row->itemcargaentrada->id}}</span>
                </div>
                <div style="width: 48%; position: absolute; top: 0.5mm; right: 0.3mm; text-align:right; font-size: 10px;">
                    Criada por {{ $row->created_usuario->nome }}
                </div>
            </div>
        </div>


        <div style="width: 100%; position: relative; padding-bottom: 2mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
            <div style="width: 100%; font-size: 8px; position: relative; top: 1mm; left: 1mm;">
                Destinatário
            </div>
            <div style="width: 100%; font-size: 14px; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: nowrap;">
                <?php
                    $destinatario = $row->destinatario();
                    echo ($destinatario) ? $destinatario->razaosocial : 'Não identificado!';
                ?>
            </div>
            @if($destinatario)
            <div style="width: 100%; font-size: 12px; position: relative; top: 1mm; left: 1mm; white-space: nowrap; ">
                <div style="white-space: nowrap; width: 100%; position: relative; ">
                    CNPJ: <span style="font-weight: bold;">{{ formatCnpjCpf($destinatario->cnpj) }}</span>
                </div>
                <div style="white-space: nowrap; width: 98%; font-size: 10px; position: relative; ">
                    End.: <span>{{ mb_strtoupper($destinatario->enderecoenumero) }}</span>
                </div>
                <div style="white-space: nowrap; width: 96%; font-size: 10px; position: relative; text-overflow: ellipsis; overflow: hidden;">
                    Bairro: {{ mb_strtoupper($destinatario->bairro) . (($destinatario->complemento !== '') ? '- Complemento: '.$destinatario->complemento : '') }}
                </div>
                <div style="white-space: nowrap; width: 98%; font-size: 10px;  ">
                    CEP: <span> {{formatFloat("##.###-###", $destinatario->cep)}}</span>
                    - Cidade: <span> {{ mb_strtoupper($destinatario->cidadeeuf)}}</span>
                </div>
            </div>
            @endif
        </div>





        {{-- chave de acesso --}}
        <div style="width: 100%; position: absolute; line-height: normal; height: 2cm; top: 74mm; left: 0; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-top: 1px solid black;">
            <div style="width: 100%; height: 5mm;  position: absolute; top: 1mm; left: 0; font-size: 8px; text-align: center;">
                <div>Chave de acesso NF-e</div>
                <div style="font-size: 12px; font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                    {{ $row->itemcargaentrada->nfechave }}
                </div>
            </div>
            <div style="width: 90mm; height: 13mm; position: absolute; top: 7mm; left: 3mm;">
                <img src="data:image/png;base64,{{ $row->barcodenfe() }}" alt="barcode" style="width:100%;height:100%;" >
            </div>
        </div>
        {{-- chave de acesso --}}
    </div>
    @if(($dataset->count() > 1) && ($key < ($dataset->count() - 1)))
        <div class="line-separator" style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
