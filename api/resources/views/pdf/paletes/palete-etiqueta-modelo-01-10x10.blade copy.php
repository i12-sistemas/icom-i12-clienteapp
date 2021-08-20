<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impressão de etiquetas de palete</title>
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
    <div style="width: 100%; position: absolute; left: 0; top: 0; height: 98mm; width: 98mm; ">

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

        <div style="width: 100%; position: relative; height: 10mm; line-height: normal; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif; font-size: 22px; font-weight: bold;  background: black; color: white;">
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; text-align: center ">
                PALETE
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 22mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px;  border-bottom: 1px solid black;">
            <div style="width: 100%; font-size: 12px; position: relative; top: 1mm; left: 1mm; ">
                Unidade
            </div>
            <div style="width: 96%; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; ">
                <span style="font-weight: bold; font-size: 24px;">{{ $row->unidade->fantasia }}</span>
            </div>
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; ">
                <span style="font-size: 10px; font-size: 24px;">{{ $row->unidade->cidade->cidade . ' - ' . $row->unidade->cidade->uf }}</span>
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 20mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-bottom: 1px solid black;">
            <div style="width: 100%; height: 10mm; font-size: 28px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="width: 100%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Volumes:
                    <span style="font-size: 30px; font-weight: bold;">{{$row->volqtde}}</span>
                </div>
            </div>
            <div style="width: 100%; height: 8mm; font-size: 20px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 100%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Peso estimado:
                    <span style="font-weight: bold;">
                        {{ formatRS($row->pesototal, 3, '')}} KG
                    </span>
                </div>
            </div>
        </div>


        <div style="width: 100%; position: relative; padding-bottom: 2mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 18px; ">
            <div style="width: 100%; font-size: 12px; position: relative; top: 1mm; left: 1mm;">
                Descrição
            </div>
            <div style="width: 100%; height: 11mm; font-size: 24px; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: normal;">
                {{ $row->descricao }}
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 16mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-top: 1px solid black;">
            <div style="width: 100%; height: 4.5mm; font-size: 11px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 98%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} - Criada por {{ $row->created_usuario->nome }}
                </div>
            </div>
        </div>

    </div>
    {{-- itens do palete --}}
    <div style="width: 100%; position: absolute; left: 0; top: 0; height: 98mm; width: 98mm; ">

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

        <div style="width: 100%; position: relative; height: 10mm; line-height: normal; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif; font-size: 22px; font-weight: bold;  background: black; color: white;">
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; text-align: center ">
                PALETE
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 22mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px;  border-bottom: 1px solid black;">
            <div style="width: 100%; font-size: 12px; position: relative; top: 1mm; left: 1mm; ">
                Unidade
            </div>
            <div style="width: 96%; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; ">
                <span style="font-weight: bold; font-size: 24px;">{{ $row->unidade->fantasia }}</span>
            </div>
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; ">
                <span style="font-size: 10px; font-size: 24px;">{{ $row->unidade->cidade->cidade . ' - ' . $row->unidade->cidade->uf }}</span>
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 20mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-bottom: 1px solid black;">
            <div style="width: 100%; height: 10mm; font-size: 28px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="width: 100%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Volumes:
                    <span style="font-size: 30px; font-weight: bold;">{{$row->volqtde}}</span>
                </div>
            </div>
            <div style="width: 100%; height: 8mm; font-size: 20px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 100%; position: absolute; top: 0.3mm; left: 0.3mm;">
                    Peso estimado:
                    <span style="font-weight: bold;">
                        {{ formatRS($row->pesototal, 3, '')}} KG
                    </span>
                </div>
            </div>
        </div>


        <div style="width: 100%; position: relative; padding-bottom: 2mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 18px; ">
            <div style="width: 100%; font-size: 12px; position: relative; top: 1mm; left: 1mm;">
                Descrição
            </div>
            <div style="width: 100%; height: 11mm; font-size: 24px; font-weight: bold; position: relative; top: 1mm; left: 1mm; white-space: normal;">
                {{ $row->descricao }}
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 16mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-top: 1px solid black;">
            <div style="width: 100%; height: 4.5mm; font-size: 11px; position: relative; top: 0.3mm; left: 0.3mm;">
                <div style="width: 98%; position: absolute; top: 0.3mm; left: 0.3mm; ">
                    Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} - Criada por {{ $row->created_usuario->nome }}
                </div>
            </div>
        </div>

    </div>
    @if(($dataset->count() > 1) && ($key < ($dataset->count() - 1)))
        <div class="line-separator" style="page-break-after: always;"></div>
    @endif
    {{-- itens do palete --}}

    @if(($dataset->count() > 1) && ($key < ($dataset->count() - 1)))
        <div class="line-separator" style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
