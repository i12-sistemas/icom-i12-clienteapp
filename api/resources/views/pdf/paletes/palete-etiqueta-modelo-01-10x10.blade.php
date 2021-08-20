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
            <div style="width: 5cm; height: 18mm; position: absolute; top: 1mm; right: 1mm;">
                <img src="data:image/png;base64,{{ $row->barcode() }}" alt="barcode" style="width:100%;height:100%;" >
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 10mm; line-height: normal; font-family: 'Courier New', Courier, monospace; font-size: 28px; font-weight: bold;  background: black; color: white;">
            <div style="width: 100%; position: relative; top: 1mm; left: 1mm; text-align: center ">
                PALETE  {{ $row->ean13 }}
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

        @php
            $rowCount = $row->itensEtiquetaDetalhe->count();
        @endphp


        <div style="width: 100%; position: relative; height: 20mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border-bottom: 1px solid black;">
            <div style="width: 100%; height: 10mm; font-size: 28px; position: relative; top: 0.5mm; left: 0.3mm;">
                <div style="width: 56%; position: absolute; top: 0.3mm; left: 0.3mm; font-size: 30px; ">
                    Volumes:
                    <span style="font-weight: bold;">{{$row->volqtde}}</span>
                </div>
                <div style="width: 40%; position: absolute; top: 0.3mm; right: 0.3mm; font-size: 30px; text-align: right; ">
                    Notas:
                    <span style="font-weight: bold;">{{$rowCount}}</span>
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
    @php
        $rodapeH = 4;
        $totalBody = 100 - $rodapeH;
        $nCol = 2;
        $nRows = 4;
        $nItem = 0;
        $rowCount = $row->itensEtiquetaDetalhe->count();
        $h = ((($totalBody-2)/$nRows) - (1.5)) ;
        $pageActive = 1;
        $pageCount = ceil($rowCount / ($nCol * $nRows));
    @endphp
    @while ($nItem < $rowCount)
        <div class="line-separator" style="page-break-after: always;"></div>
        @for ($n=0; $n < $nRows; $n++)
            <div style="width: 100%;  letter-spacing: -1px; position: relative; height: 22.6mm; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 12px;  ">
                @for ($i=0; $i < $nCol; $i++)
                    @php
                        $item = $row->itensEtiquetaDetalhe[$nItem];
                    @endphp
                    {{-- <div style="font-weight: bold; width: 49%; position: absolute; height:{{$h}}mm; font-size: 10px; top: 0; left: 0; left: {{($i*49) + ($i==0 ? 0 : 0)}}%; {{$i>0 ? 'padding-left: 1mm; border-left: 0.5mm solid black; ' : ''}} {{$n>0 ? 'padding-top: 1mm; border-top: 0.5mm solid black; ' : ''}}"> --}}
                    <div style="font-weight: bold; width: 47.8mm; position: absolute; height:{{$h}}mm; top:0; font-size: 10px; left: {{($i*48) + ($i==0 ? 0 : 2)}}mm; border-right: 1px solid black; border-bottom: 1px solid black;">
                        <div style="font-size: 13px; letter-spacing: normal;">NF:e: {{ $item->etiqueta->itemcargaentrada->nfenumero}} - Série: {{ str_pad( $item->etiqueta->itemcargaentrada->coletanota->notaserie, 3, "0", STR_PAD_LEFT) }}</div>
                        <div style="white-space: nowrap; text-overflow: ellipsis; overflow: hidden; width: 99%;">{{ $item->etiqueta->itemcargaentrada->coletanota->destinatarionome}}</div>
                        <div style="font-size: 13px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">{{ $item->etiqueta->itemcargaentrada->coletanota->cidade->cidade}} / {{$item->etiqueta->itemcargaentrada->coletanota->cidade->uf}}</div>
                        <div style="font-size: 12px; letter-spacing: normal; white-space: normal; text-overflow: clip; overflow: visible; width: 98%; ">{{ $item->totalvolume }} vol. - nº: {{ $item->volumes }}
                            @if ($item->totalvolume !== $item->etiqueta->voltotal)
                                <span style="background-color: black; color: white; padding-left: 1mm">*{{(($item->etiqueta->voltotal-$item->totalvolume) === 1) ? 'Falta' : 'Faltam' }} {{$item->etiqueta->voltotal-$item->totalvolume}} volume</span>
                            @endif
                        </div>
                    </div>
                    @php
                        $nItem = $nItem + 1;
                    @endphp
                    @if($nItem >= $rowCount)
                        @break
                    @endif
                @endfor
            </div>
            @if($nItem >= $rowCount)
                @break
            @endif
        @endfor
        <div style="width: 100%; font-weight: bold; position: absolute; height: {{$rodapeH}}mm; bottom: 0px; line-height: normal; font-family: Arial, Helvetica, sans-serif; font-size: 10px; ">
            <div style="width: 100%;  position: relative;  left: 0.3mm; text-align: center">
                <div style="width: 45%;  position: absolute;  left: 0.3mm; text-align: left">
                    PALETE  {{ $row->ean13 }}
                </div>
                <div style="width: 45%;  position: absolute;  right: 0.3mm; text-align: right">
                    Página {{$pageActive}} de {{$pageCount}}
                </div>
            </div>

        </div>
        @php
            $pageActive = $pageActive + 1;
        @endphp
    @endwhile
    {{-- itens do palete --}}

    @if(($dataset->count() > 1) && ($key < ($dataset->count() - 1)))
        <div class="line-separator" style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
