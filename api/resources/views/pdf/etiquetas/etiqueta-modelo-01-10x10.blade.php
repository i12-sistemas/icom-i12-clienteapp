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
                <img src="data:image/png;base64,{{ barcodeEtiqueta($row->ean13) }}" alt="barcode" style="width:100%;height:100%;" >
            </div>
            <div style="width: 5cm; height: 10mm; position: absolute; top: 16.5mm; right: 1mm; margin: 0; padding: 0; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; ">
                {{ $row->ean13 }}
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 11mm; line-height: normal; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px;  border-bottom: 1px solid black;">
            <div style="width: 100%; height: 4mm; font-size: 11px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 48%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    Fornecedor/Origem
                </div>
                <div style="width: 48%; position: absolute; top: 0.5mm; right: 0.3mm; text-align:right;">
                    CNPJ: <span style="font-weight: bold;">{{ formatCnpjCpf($row->origemcnpj) }}</span>
                </div>
            </div>
            <div style="width: 96%; position: relative; top: 1mm; left: 1mm; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; font-size: 16px; ">
                {{ utf8_encode($row->origemrazaosocial) }}
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 27mm; font-weight: bold; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-bottom: 1px solid black;">
            <div style="width: 100%; height: 9mm; font-size: 22px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="width: 56%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Vol:
                    <span style="font-size: 28px;">
                        {{str_pad( $row->volnum, 3, "0", STR_PAD_LEFT) }} / {{ str_pad( $row->voltotal, 3, "0", STR_PAD_LEFT)  }}
                    </span>
                </div>
                <div style="width: 42%; position: absolute; top: 0.7mm; right: : 0.3mm; text-align:right;">
                    Coleta:
                    <span style="font-size: 20px;">{{$row->coletaid}}</span>
                </div>
            </div>
            <div style="width: 100%; height: 10mm; font-size: 16px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="width: 48%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    NF-e:
                    <span style="font-size: 30px; background: black; color: white; border: 2mm; border: 2px solid black;">{{$row->notanumero}}</span>
                </div>
                <div style="width: 48%; position: absolute; top: 0.7mm; right: : 0.3mm; text-align:right; ">
                    Série:
                    <span style="font-size: 26px;">{{str_pad( $row->notaserie, 3, "0", STR_PAD_LEFT) }}</span>
                </div>
            </div>
            <div style="width: 100%; height: 6mm; font-size: 16px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="font-size: 12px; font-family: 'Courier New', Courier, monospace;">Chave de acesso NF-e</div>
                <div style="font-size: 14px; font-family: 'Courier New', Courier, monospace;">
                    {{ $row->nfechave }}
                </div>
            </div>
        </div>


        <div style="width: 100%; position: relative; padding-bottom: 2mm; line-height: normal; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
            <div style="width: 100%; height: 5mm; font-size: 14px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 48%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    Destinatário
                </div>
                <div style="width: 48%; position: absolute; top: 0.5mm; right: 0.3mm; text-align:right; font-size: 13px; ">
                    CNPJ: <span style="font-weight: bold;">{{ formatCnpjCpf($row->destinocnpj) }}</span>
                </div>
            </div>
            <div style="width: 100%; font-size: 22px; line-height: 18px; font-weight: bold; position: relative; top: 1mm; left: 1mm;">
                {{ utf8_encode($row->destinorazaosocial) }}
            </div>
            <div style="width: 100%; size: 14px; position: relative; top: 1mm; left: 1mm;  ">
                <div style="white-space: nowrap; width: 98%; font-size: 18px;  ">
                    Cidade: <span> {{ mb_strtoupper(utf8_encode($row->destinocidade)) . ' - ' . mb_strtoupper($row->destinouf)}}</span>
                </div>
            </div>
        </div>



        <div style="width: 100%; position: absolute; bottom: 1mm; height: 5mm;  font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 10px;  border-top: 1px solid black;">
            <div style="width: 100%;  position: relative; top: 1mm; right: 1mm; white-space: nowrap; text-align:right;">
                Gerado por {{ ellipsis($row->createdusuarionome, 20) }} - Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </div>
        </div>


    </div>
    @if((count($dataset) > 1) && ($key < (count($dataset) - 1)))
        <div class="line-separator" style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
