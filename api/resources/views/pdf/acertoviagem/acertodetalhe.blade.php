<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impressão de ordem de coleta</title>
    <style>
    body {
        position: relative;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }
    .page_break { page-break-before: always; }
    .invoice-box {
        height: auto;
        font-size: 16px;
        line-height: 24px;
        color: #000;
        margin-bottom: 20px;
        position: relative;
    }

    .invoice-box table {
        width: 100%;
        line-height: 15px;
        text-align: left;
        font-size: 14px;
    }

    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }

    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 10px;
    }

    .invoice-box table tr.heading td {
        border-bottom: 1px solid rgb(0, 0, 0);
        font-weight: bold;
        border-radius: 0;
        font-size: 12px;
        border-right: 1px solid;
    }

    .invoice-box table tr.details td {
        text-align: left;
        border-bottom: 1px solid;
        border-right: 1px solid;
        border-radius: 0;
    }

    .invoice-box table tr.item.last td {
        border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }

    .text-infoempresa {
        font-size: 14px;
        line-height: 16px;
    }

    .line-separator {
        border-top: 2px #383568 dashed;
        margin: 15px 0;
        position: relative;
        height: 15px;
    }

    .box-origem {
        border: 1px solid;
        width: 100%;
        position: relative;
        height: 24px;
        border-radius: 5px;
        font-size: 14px;
        line-height: 20px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #000;
    }

    .box-origem .box-origem-item {
        position: absolute;
        padding-top: 0;
        padding-bottom: 0;
        padding-left: 10px;
        border-right: 1px solid;
        height: 24px;
        font-weight: bold;
        top: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .box-origem .box-origem-item .box-origem-label {
        font-size: 14px;
        font-weight: normal;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }

        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    /** RTL **/
    .rtl {
        direction: rtl;
    }

    .rtl table {
        text-align: right;
    }

    .rtl table tr td:nth-child(2) {
        text-align: left;
    }

    span.text-ordemcoleta-caption {
        font-size: 18px;
    }

    .box-fornecedor-email {
        font-size: 18px;
    }

    .text-emergencia {
        color: white;
        background: #000;
        padding: 10px;
        text-align: center;
        border-radius: 6px;
        margin: 18px 0;
    }

    .box-info {
        width: 100%;
        position: relative;
        height: 110px;
    }

    .box-info-empresa {
        width: 50%;
        font-size: 14px;
        line-height: 20px;
        position: absolute;
        top: 0;
        left: 0;
    }

    .box-info-motorista {
        border: 1px solid black;
        width: 310px;
        direction: rtl;
        position: absolute;
        top: 0;
        right: 0;
        border-radius: 6px;
    }

    .box-info-row {
        font-weight: bold;
        font-size: 20px;
        padding: 3px 10px;
        border-bottom: 1px solid;
        direction: rtl;
    }
    span.box-info-l {
        font-size: 14px;
        position: absolute;
        left: 0;
        padding-left: 10px;
        width: 200px;
        direction: ltr;
    }
    span.box-info-r {
        padding-left: 10px;
        position: relative;
        top: 0;
        right: 0px;
        font-size: 18px;
    }

    .box-info-row-2 {
        font-weight: bold;
        font-size: 14px;
        padding: 0px 10px;
        direction: rtl;
        line-height: 19px;
    }
    span.box-info-l-2 {
        font-size: 12px;
        position: absolute;
        left: 0;
        padding-left: 10px;
        width: 70px;
        direction: ltr;
        font-weight: normal;
    }

    span.box-info-r-2 {
        padding-left: 10px;
        position: relative;
        top: 0;
        right: 0px;
        font-size: 12px;
        font-weight: bold;
    }

    .box-texto-dissidio {
        border: 1px solid;
        margin-top: 5px;
        border-radius: 6px;
        padding: 0 5px 0 10px;
        font-size: 12px;
        line-height: 11px;
        position: relative;
    }

    .box-fornecedor {
        border: 1px solid;
        margin-top: 5px;
        border-radius: 6px;
        font-size: 14px;
        line-height: 18px;
        position: relative;
    }

    .box-fornecedor-row {
        font-weight: bold;
        line-height: 18px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 22px;
    }
    .box-fornecedor-row-total {
        font-weight: bold;
        line-height: 18px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 36px;
    }
    .box-fornecedor-field-total {
      padding-top: 0;
      padding-bottom: 0;
      padding-left: 5px;
      padding-right: 10px;
      font-weight: bold;
      position: absolute;
      height: 36px;
      top: 0;
      white-space: pre-line;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .box-fornecedor-field {
      padding-top: 0;
      padding-bottom: 0;
      padding-left: 5px;
      padding-right: 10px;
      font-weight: bold;
      position: absolute;
      height: 22px;
      top: 0;
      white-space: pre-line;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .box-fornecedor-label {
        font-size: 13px;
        font-weight: normal;
        padding-right: 5px;
    }

    .box-fornecedor-label-total {
        font-size: 13px;
        font-weight: bold;
        padding-right: 5px;
    }

    .box-fornecedor-row .min {
        font-size: 12px;
        font-weight: normal;
    }





    .box-fornecedor-field .min {
        font-size: 12px;
        font-weight: normal;
    }
    .box-fornecedor-label .min {
        font-size: 10px;
        font-weight: bold !important;
    }

    .box-obs {
        border: 1px solid;
        margin-top: 15px;
        border-radius: 6px;
        font-size: 14px;
        line-height: 19px;
        position: relative;
        min-height: 60px;
        height: auto;
    }

    .box-obs-row {
        height: auto;
        font-weight: bold;
        line-height: 22px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 24px;
    }

    .box-roteiro {
        border: 1px solid;
        border-radius: 6px;
        font-size: 12px;
        line-height: 18px;
        position: relative;
    }

    .box-roteiro-row {
        font-weight: bold;
        line-height: 18px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 24px;
    }

    .box-roteiro-col {
      padding-top: 0;
      padding-bottom: 0;
      padding-left: 5px;
      padding-right: 10px;
      font-weight: bold;
      position: absolute;
      height: 24px;
      top: 0;
      white-space: pre-line;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .box-roteiro-label {
        font-size: 14px;
        font-weight: normal;
        padding-right: 5px;
    }
    #footer { position: absolute; left: 0px; bottom: -145px; right: 0px; height: 165px; }
    </style>
</head>

<body>
@foreach ($rows as $keyrow => $row)
<div class="invoice-box" >
        <div class="box-info">
            <div class="box-info-empresa">
                <div>
                    <img src="{{url('/')}}/img/logo-conecta.png" style="width:100%; max-width:300px;">
                </div>
                <div style="font-weight: bold; font-size: 15px">Conecta Transp Quím. e Equip. Ind LTDA</div>
                CNPJ: 09.721.533/0001-37 - Sertãozinho/SP
            </div>
            <div class="box-info-motorista">
                <div class="box-info-row">
                    <span class="box-info-l">Relatório de Viagem</span>
                    <span class="box-info-r">{{$row->id}}</span>
                </div>

                <div class="box-info-row-2">
                    <span class="box-info-l-2">Motorista</span>
                    <span class="box-info-r-2">{{$row->motorista ? $row->motorista->nome : 'INDEFINIDO'}}</span>
                </div>
                <div class="box-info-row-2">
                    <span class="box-info-l-2">Veículo</span>
                    <span class="box-info-r-2">{{$row->veiculo ? formatarPlaca($row->veiculo->placa) : ' - '}}</span>
                </div>
                <div class="box-info-row-2" style="margin-bottom: 5px">
                    <span class="box-info-l-2">Carreta</span>
                    <span class="box-info-r-2">{{$row->veiculocarreta ? formatarPlaca($row->veiculocarreta->placa) : ' - '}}</span>
                </div>
            </div>
        </div>
        {{-- origem --}}
        <div class="box-origem">
            <div class="box-origem-item" style="width: 350px;">
                <span class="box-origem-label">Origem:</span> {{ $row->cidadeorigem ? $row->cidadeorigem->cidade . ' / ' . $row->cidadeorigem->uf : ' - ' }}
            </div>
            <div class="box-origem-item" style="width: auto; left: 355px; border-right: 0px solid;">
                <span class="box-origem-label">Destino:</span> {{ $row->cidadedestino ? $row->cidadedestino->cidade . ' / ' . $row->cidadedestino->uf : ' - ' }}
            </div>
        </div>
        {{-- origem --}}
        <div style="height: 5px;"></div>
        {{-- KM --}}
        <div class="box-origem">
            <div class="box-origem-item" style="width: 220px;">
                <span class="box-origem-label">KM inicial:</span> {{ $row->kmini > 0 ? formatRS($row->kmini, 0, '') : '-' }}
            </div>
            <div class="box-origem-item" style="width: 220px; left: 225px;">
                <span class="box-origem-label">KM final:</span> {{ $row->kmfim > 0 ? formatRS($row->kmfim, 0, '') : '-' }}
            </div>
            <div class="box-origem-item" style="width: auto; left: 450px; border-right: 0px solid;">
                <span class="box-origem-label">KM total:</span> {{ $row->kmtotal > 0 ? formatRS($row->kmtotal, 0, '') : '-' }}
            </div>
        </div>
        {{-- KM --}}
        {{-- periodos --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 440px; ">
                    <span class="box-fornecedor-label"><b>Períodos de viagem</b></span>
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 150px">
                    <span class="box-fornecedor-label min" ><b>Data e hora de início</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 165px; border-left: 1px solid;">
                    <span class="box-fornecedor-label min" ><b>Data e hora de término</b></span>
                </div>
                {{-- <div class="box-fornecedor-field min" style="width: 55px; left: 330px; border-left: 1px solid; text-align: center; ">
                    <span class="box-fornecedor-label min" ><b>Dias</b></span>
                </div> --}}
                <div class="box-fornecedor-field min" style="width: 55px; left: 400px; border-left: 1px solid; text-align: center; ">
                    <span class="box-fornecedor-label min" ><b>Café</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 55px; left: 470px; border-left: 1px solid; text-align: center; ">
                    <span class="box-fornecedor-label min" ><b>Almoço</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 55px; left: 540px; border-left: 1px solid; text-align: center; ">
                    <span class="box-fornecedor-label min" ><b>Jantar</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 55px; left: 610px; border-left: 1px solid; text-align: center; ">
                    <span class="box-fornecedor-label min" ><b>Pernoite</b></span>
                </div>
            </div>
            @if(count($row->periodos) <= 0)
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 670px; text-align: center;  ">
                    <span class="box-fornecedor-label" style="font-size: 11px;">Nenhum período lançado</span>
                </div>
            </div>
            @endif
            @foreach ($row->periodos as $periodo)
                <div class="box-fornecedor-row min">
                    <div class="box-fornecedor-field min" style="width: 150px">
                        {{$periodo->dhi ? $periodo->dhi->format('d/m/Y - H:i') : '**'}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 150px; left: 165px; border-left: 1px solid;">
                        {{$periodo->dhf ? $periodo->dhf->format('d/m/Y - H:i') : '**'}}
                    </div>
                    {{-- <div class="box-fornecedor-field min" style="width: 55px; left: 330px; border-left: 1px solid; text-align: center; ">
                        {{  $periodo->qtdedias > 0 ? $periodo->qtdedias : '-' }}
                    </div> --}}
                    <div class="box-fornecedor-field min" style="width: 55px; left: 400px; border-left: 1px solid; text-align: center; ">
                        {{  $periodo->cafeqtde > 0 ? $periodo->cafeqtde : '-' }}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 55px; left: 470px; border-left: 1px solid; text-align: center; ">
                        {{  $periodo->almocoqtde > 0 ? $periodo->almocoqtde : '-' }}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 55px; left: 540px; border-left: 1px solid; text-align: center; ">
                        {{  $periodo->jantarqtde > 0 ? $periodo->jantarqtde : '-' }}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 55px; left: 610px; border-left: 1px solid; text-align: center; ">
                        {{  $periodo->pernoiteqtde > 0 ? $periodo->pernoiteqtde : '-' }}
                    </div>
                </div>
            @endforeach
        </div>
        {{-- periodos --}}

        {{-- diarias e despesas  --}}
        <table style="border: 0;">
            <tr>
                <td style="width: 250px; padding: 0; margin: 0;">
                    <div class="box-fornecedor">
                        <div class="box-fornecedor-row" >
                            <div class="box-fornecedor-field" style="width: 80px;  ">
                                <span class="box-fornecedor-label"  ><b>Diárias</b></span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 95px; text-align: center; border-left: 1px solid black; ">
                                <span class="box-fornecedor-label min"><b>Viagem</b></span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 160px; text-align: center; border-left: 1px solid black;">
                                <span class="box-fornecedor-label min" ><b>Pago</b></span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 80px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label min"><b>Vlr Pago (R$)</b></span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 80px;">
                                <span class="box-fornecedor-label min" style="padding-left: 10px;">Café</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 95px; text-align: center; border-left: 1px solid black; ">
                                <span class="box-fornecedor-label min" style="padding-right: 0;">{{$row->cafeqtde}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 160px; text-align: center; border-left: 1px solid black;">
                                <span class="box-fornecedor-label min" style="padding-right: 0;">{{$row->cafetotal}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 80px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label min" style="padding-right: 0;">{{formatRS($row->cafeapagar, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 80px;">
                                <span class="box-fornecedor-label min" style="padding-left: 10px;">Almoço</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 95px; text-align: center; border-left: 1px solid black; ">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->almocoqtde}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 160px; text-align: center; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->almocototal}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 80px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->almocoapagar, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 80px;">
                                <span class="box-fornecedor-label min" style="padding-left: 10px;">Jantar</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 95px; text-align: center; border-left: 1px solid black; ">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->jantarqtde}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 160px; text-align: center; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->jantartotal}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 80px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->jantarapagar, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 80px;">
                                <span class="box-fornecedor-label min" style="padding-left: 10px;">Pernoite</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 95px; text-align: center; border-left: 1px solid black; ">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->pernoiteqtde}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 50px; left: 160px; text-align: center; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{$row->pernoitetotal}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 80px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->pernoiteapagar, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" style="border: 0;" >
                            <div class="box-fornecedor-row min" style="width: 120px; border: 0;">
                                <span class="box-fornecedor-label" style="padding-left: 10px; font-weight: bold;">Total diária</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 165px; left: 140px; text-align: right; border-bottom: 0;">
                                <span class="box-fornecedor-label" style="padding-right: 0;  font-weight: bold;" >{{formatRS($row->vlrtotaldiaria)}}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td style=" width: 280px; padding: 0 0 0 10px; margin: 0; ">

                    <div class="box-fornecedor">
                        <div class="box-fornecedor-row" >
                            <div class="box-fornecedor-field" style="width: 215px; text-align: left; ">
                                <span class="box-fornecedor-label"  ><b>Despesas</b></span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 120px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="font-size: 12px;"><b>Valor (R$)</b></span>
                            </div>
                        </div>
                        @if(count($row->despesas) <= 0)
                        <div class="box-fornecedor-row">
                            <div class="box-fornecedor-field" style="width: 215px; text-align: center; ">
                                <span class="box-fornecedor-label" style="font-size: 11px;">Nenhum despesa lançada</span>
                            </div>
                        </div>
                        @endif
                        @foreach ($row->despesas as $despesa)
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 215px; text-align: left;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">{{$despesa->despesaviagem ? $despesa->despesaviagem->descricao : '-'}}</span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 120px; left: 225px; text-align: right; border-left: 1px solid black;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($despesa->valor, 2, '')}}</span>
                            </div>
                        </div>
                        @endforeach
                        <div class="box-fornecedor-row min" style="border-bottom: 0;" >
                            <div class="box-fornecedor-row min" style="width: 150px; border-bottom: 0; text-align: left; ">
                                <span class="box-fornecedor-label" style="padding-left: 10px; font-weight: bold; text-align: left;">Total despesas</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 165px; left: 180px; text-align: right; ">
                                <span class="box-fornecedor-label" style="padding-right: 0;  font-weight: bold;" >{{formatRS($row->vlrtotaldespesas)}}</span>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        </table>
        {{-- diarias e despesas  --}}

        {{-- abastecimentos --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 440px">
                    <span class="box-fornecedor-label"><b>Abastecimentos</b></span>
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 70px; ">
                    <span class="box-fornecedor-label min" ><b>Data</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 130px; left: 85px; border-left: 1px solid; text-align: right; ">
                    <span class="box-fornecedor-label min" ><b>Média de consumo</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 80px; left: 230px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label min" ><b>Km total</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 90px; left: 325px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label min" ><b>Litros</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 100px; left: 430px;  border-left: 1px solid;  text-align: left;">
                    <span class="box-fornecedor-label min" ><b>Forma Pagto</b></span>
                </div>
                <div class="box-fornecedor-field min" style="width: 135px; left: 550px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label min" ><b>Valor</b></span>
                </div>
            </div>
            @if(count($row->abastecimentos) <= 0)
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 670px; text-align: center;">
                    <span class="box-fornecedor-label" style="font-size: 11px;">Nenhum abastecimento lançado</span>
                </div>
            </div>
            @endif
            @foreach ($row->abastecimentos as $abastec)
                <div class="box-fornecedor-row min">
                    <div class="box-fornecedor-field min" style="width: 70px;">
                        {{$abastec->data ? $abastec->data->format('d/m/Y') : '**'}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 130px; left: 85px; border-left: 1px solid; text-align: right;">
                        {{formatRS($abastec->media, 2, '') . ' km/l'}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 80px; left: 230px;  border-left: 1px solid; text-align: right;">
                        {{formatRS($abastec->kmtotal, 0, '')}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 90px; left: 325px; border-left: 1px solid; text-align: right;">
                        {{formatRS($abastec->litros, 2, '')}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 100px; left: 430px;  border-left: 1px solid;  text-align: left;">
                        {{$abastec->tipopagto == 'C' ? 'Conta' : ( $abastec->tipopagto == 'D' ? 'Dinheiro' : '-')}}
                    </div>
                    <div class="box-fornecedor-field min" style="width: 135px; left: 550px; border-left: 1px solid; text-align: right">
                        {{formatRS($abastec->vlrabastecimento)}}
                    </div>

                </div>
            @endforeach
            <?php
                $mediaconsumo = 0;
                $kmtotal = $row->abastecimentos()->sum('kmtotal');
                if(!$kmtotal) $kmtotal = 0;
                $litros = $row->abastecimentos()->sum('litros');
                if(!$litros) $litros = 0;

                if (($kmtotal === 0) || ($litros === 0)) {
                    $mediaconsumo = 0;
                } else {
                    $mediaconsumo = round(($kmtotal / $litros), 8);
                }

                $precomedio = 0;
                if (($row->vlrtotalabastecimento === 0) || ($litros === 0)) {
                    $precomedio = 0;
                } else {
                    $precomedio = round(($row->vlrtotalabastecimento / $litros), 8);
                }


            ?>
            <div class="box-fornecedor-row-total" style=" border-bottom: 0;">
                <div class="box-fornecedor-field-total" style="width: 70px; ">
                    <span class="box-fornecedor-label-total" ><b>TOTAL</b></span>
                </div>
                <div class="box-fornecedor-field-total" style="width: 130px; left: 85px; border-left: 1px solid; text-align: right; ">
                    <span class="box-fornecedor-label-total" ><b>{{formatRS($mediaconsumo, 2, '')}} km/l</b></span>
                </div>
                <div class="box-fornecedor-field-total" style="width: 80px; left: 230px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label-total" ><b>{{formatRS($kmtotal, 0, '')}}</b></span>
                </div>
                <div class="box-fornecedor-field-total" style="width: 90px; left: 325px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label-total" ><b>{{formatRS($row->totallitros, 2, '')}}</b></span>
                </div>
                <div class="box-fornecedor-field-total" style="width: 100px; left: 430px;  border-left: 1px solid;  text-align: left;">
                    @if($row->vlrtotalabastecimentodinheiro > 0)
                    <span class="box-fornecedor-label-total" style="font-size: 12px;" >* Em dinheiro</span>
                    <span class="box-fornecedor-label-total" >{{formatRS($row->vlrtotalabastecimentodinheiro)}}</span>
                    @else
                    <span class="box-fornecedor-label-total" style="font-size: 10px;  line-height: 12px; font-weight: normal;" >Sem abastecimento em dinheiro</span>
                    @endif
                </div>
                <div class="box-fornecedor-field-total" style="width: 135px; left: 550px; border-left: 1px solid; text-align: right;">
                    <span class="box-fornecedor-label-total" style="font-size: 15px;" >{{formatRS($row->vlrtotalabastecimento)}}</span>
                    <span class="box-fornecedor-label-total" style="font-size: 12px;  line-height: 14px; font-weight: normal;" >Média {{formatRS($precomedio)}} lt</span>
                </div>
            </div>
        </div>
        {{-- abastecimentos --}}

        {{-- final  --}}
        <table style="border: 0;">
            <tr>
                <td style="width: 350px; padding: 0; margin: 0;">
                    <div class="box-fornecedor">
                        <div class="box-fornecedor-row">
                            <div class="box-fornecedor-field" style="width: 305px;  ">
                                <span class="box-fornecedor-label"><b>Resumo</b></span>
                            </div>
                            <div class="box-fornecedor-field" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0; font-size: 9px;">Valores em R$</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">Adiantamento [a] </span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlradiantamento, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">Adicional [b]</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlradicional, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">( + ) Adiantamento total [a+b] </span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlradiantamentototal, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">( - ) Diárias</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlrtotaldiaria, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">( - ) Despesas</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlrtotaldespesas, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" >
                            <div class="box-fornecedor-row min" style="width: 220px;">
                                <span class="box-fornecedor-label" style="padding-left: 10px;">( - ) Abastecimentos em dinheiro</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;">{{formatRS($row->vlrtotalabastecimentodinheiro, 2, '')}}</span>
                            </div>
                        </div>
                        <div class="box-fornecedor-row min" style=" border-bottom: 0;" >
                            <div class="box-fornecedor-row min" style="width: 220px; border-bottom: 0;">
                                <span class="box-fornecedor-label" style="padding-left: 10px; font-weight: bold;">( = ) Saldo</span>
                            </div>
                            <div class="box-fornecedor-field min" style="width: 140px; left: 220px; text-align: right;">
                                <span class="box-fornecedor-label" style="padding-right: 0;  font-weight: bold;" >{{formatRS($row->vlrsaldofinal, 2, '')}}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td style=" width: 280px;  margin: 0; ">

                    <div class="box-texto-dissidio" style="text-align: left;" >
                        <div class="row">
                            <div class="col" style="width: 100%; font-size: 12px; text-align: justify; letter-spacing: 0; text-justify: inter-word; word-break: break-all; padding: 5px 2px 5px 0; ">
                                {!! nl2br(e($acertoinforelviagem)) !!}
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        {{-- final  --}}

        {{-- assinatura --}}
        <table style="border: 0;">
            <tr>
                <td style="width: 100px; padding: 5px 0 0 0; margin: 0;">
                <p style="text-align: justify; ">Eu, abaixo assinado, declaro ter recebido os valores a que tenho direito conforme indicado na convenção/dissídio coletivo, dando eu plena e total quitação dos valores dessa viagem.</p>
                <p>Assino o presente documento na data em {{\Carbon\Carbon::now()->format('d/m/Y  H:i:s')}}</p>
                <div style="border-top: 1px solid black; width: 350px; margin-top: 30px; padding-top: 5px;">
                    <div>{{$row->motorista->nome}}</div>
                    @if($row->motorista->cpf !== '')
                    <div>CPF: {{ formatCnpjCpf($row->motorista->cpf)}}</div>
                    @endif
                </div>
                </td>
            </tr>
        </table>
    </div>
    <footer style="width: 100%;" id="footer">
        <div style="font-size: 9px; text-align: right;">
            Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y  H:i:s') }} {{$usuario ? ' - por ' . $usuario->nome : ''}}
            @if(count($rows)>1)
            - Página {{$keyrow+1}} de {{count($rows)}}
            @endif
        </div>
    </footer>
    @if($keyrow !== (count($rows)-1))
    <div class="page_break"></div>
    @endif
@endforeach


</body>
</html>
