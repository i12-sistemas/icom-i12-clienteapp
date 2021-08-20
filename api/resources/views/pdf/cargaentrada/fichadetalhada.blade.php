<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impressão de Carga de Entrada</title>
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
        height: 28px;
        border-radius: 5px;
        font-size: 14px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #000;
    }

    .box-origem .box-origem-item {
        position: absolute;
        padding-top: 0;
        padding-bottom: 0;
        padding-left: 10px;
        border-right: 1px solid;
        height: 28px;
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
        height: 147px;
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


    .box-checklist {
        border: 1px solid;
        margin-top: 15px;
        border-radius: 6px;
        font-size: 13px;
        line-height: 19px;
        position: relative;
    }
    .box-checklist-row {
        font-weight: bold;
        line-height: 18px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 19px;
    }
    .box-checklist-field {
      padding-top: 0;
      padding-bottom: 0;
      padding-left: 5px;
      padding-right: 10px;
      font-weight: normal;
      font-size: 11px;
      position: absolute;
      height: 19px;
      top: 0;
      white-space: pre-line;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .box-checklist-label {
        font-size: 13px;
        font-weight: normal;
        padding-right: 5px;
    }


    .box-fornecedor {
        border: 1px solid;
        margin-top: 15px;
        border-radius: 6px;
        font-size: 14px;
        line-height: 19px;
        position: relative;
    }

    .box-fornecedor-row {
        font-weight: bold;
        line-height: 22px;
        border-bottom: 1px solid;
        width: 100%;
        position: relative;
        height: 24px;
    }

    .box-fornecedor-field {
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


    .box-fornecedor-label {
        font-size: 14px;
        font-weight: normal;
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
                <span class="box-info-l">Carga de Entrada</span>
                <span class="box-info-r">{{$carga->id}}</span>
            </div>
            {{-- <div class="box-info-row">
                <span class="box-info-l">Adiantamento</span>
                <span class="box-info-r">{{formatRS($carga->vlradiantamento)}}</span>
            </div> --}}

            <div class="box-info-row-2">
                <span class="box-info-l-2">Status</span>
                <span class="box-info-r-2">{{$carga->status}}</span>
            </div>
            <div class="box-info-row-2">
                <span class="box-info-l-2">Unidade de Entrada</span>
                <span class="box-info-r-2">-</span>
            </div>
            <div class="box-info-row-2">
                <span class="box-info-l-2">Criado em</span>
                <span class="box-info-r-2">{{$carga->created_at->format('d/m/Y H:i')}}</span>
            </div>
        </div>
    </div>
    {{-- origem --}}
    {{-- <div class="box-origem">
        <div class="box-origem-item" style="width: 350px;">
            <span class="box-origem-label">Origem:</span> {{ $carga->cidadeorigem ? $carga->cidadeorigem->cidade . ' / ' . $carga->cidadeorigem->uf : ' - ' }}
        </div>
        <div class="box-origem-item" style="width: auto; left: 355px; border-right: 0px solid;">
            <span class="box-origem-label">Destino:</span> {{ $carga->cidadedestino ? $carga->cidadedestino->cidade . ' / ' . $carga->cidadedestino->uf : ' - ' }}
        </div>
    </div> --}}
    {{-- origem --}}
    <div style="height: 5px; font-size: 28px; color: red;">
    EM DESENVOLVIMENTO
    </div>
    {{-- KM --}}
    {{-- <div class="box-origem">
        <div class="box-origem-item" style="width: 220px;">
            <span class="box-origem-label">KM inicial:</span> {{ $carga->kmini > 0 ? formatRS($carga->kmini, 0, '') : '' }}
        </div>
        <div class="box-origem-item" style="width: 220px; left: 225px;">
            <span class="box-origem-label">KM final:</span> {{ $carga->kmfim > 0 ? formatRS($carga->kmfim, 0, '') : '' }}
        </div>
        <div class="box-origem-item" style="width: auto; left: 450px; border-right: 0px solid;">
            <span class="box-origem-label">KM total:</span> {{ $carga->kmtotal > 0 ? formatRS($carga->kmtotal, 0, '') : '' }}
        </div>
    </div> --}}
    {{-- KM --}}
    {{-- periodos --}}
    {{-- @if($quadroperiodosqtdelinha > 0)
    <div class="box-fornecedor">
        <div class="box-fornecedor-row">
            <div class="box-fornecedor-field" style="width: 440px; border-right: 1px solid;">
                <span class="box-fornecedor-label"><b>Períodos de viagem</b></span>
            </div>
            <div class="box-fornecedor-field" style="left: 455px; border: 0; width: auto;">
                <span class="box-fornecedor-label">Quantidade CT-e:</span>
            </div>
        </div>
        <div class="box-fornecedor-row min">
            <div class="box-fornecedor-field min" style="width: 150px">
                <span class="box-fornecedor-label min" ><b>Data e hora de início</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 150px; left: 165px; border-left: 1px solid; border-right: 1px solid">
                <span class="box-fornecedor-label min" ><b>Data e hora de término</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 150px; left: 380px; border-left: 1px solid">
                <span class="box-fornecedor-label min" ><b>Data e hora de início</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 150px; left: 545px; border-left: 1px solid">
                <span class="box-fornecedor-label min" ><b>Data e hora de término</b></span>
            </div>
        </div>
        @for ($i = 0; $i < $quadroperiodosqtdelinha; $i++)
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 150px">
                    <span class="box-fornecedor-label min" >
                        <span style="padding: 0 7px 0 0; color: white">__</span><span style="padding: 0 3px 0 3px;">/</span><span style="padding: 0 3px 0 3px; color: white">__</span><span style="">/</span><span style="padding: 0 10px 0 3px; color: white">____ __ </span><span style=""> : </span><span style="padding: 0 0 0 3px; color: white">__</span>
                    </span>
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 165px; border-left: 1px solid; border-right: 1px solid">
                    <span style="padding: 0 7px 0 0; color: white">__</span><span style="padding: 0 3px 0 3px;">/</span><span style="padding: 0 3px 0 3px; color: white">__</span><span style="">/</span><span style="padding: 0 10px 0 3px; color: white">____ __ </span><span style=""> : </span><span style="padding: 0 0 0 3px; color: white">__</span>
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 380px; border-left: 1px solid">
                    <span style="padding: 0 7px 0 0; color: white">__</span><span style="padding: 0 3px 0 3px;">/</span><span style="padding: 0 3px 0 3px; color: white">__</span><span style="">/</span><span style="padding: 0 10px 0 3px; color: white">____ __ </span><span style=""> : </span><span style="padding: 0 0 0 3px; color: white">__</span>
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 545px; border-left: 1px solid">
                    <span style="padding: 0 7px 0 0; color: white">__</span><span style="padding: 0 3px 0 3px;">/</span><span style="padding: 0 3px 0 3px; color: white">__</span><span style="">/</span><span style="padding: 0 10px 0 3px; color: white">____ __ </span><span style=""> : </span><span style="padding: 0 0 0 3px; color: white">__</span>
                </div>
            </div>
        @endfor
    </div>
    @endif --}}
    {{-- periodos --}}
    {{-- abastecimentos --}}
    {{-- @if($quadroabastqtdelinha > 0)
    <div class="box-fornecedor">
        <div class="box-fornecedor-row">
            <div class="box-fornecedor-field" style="width: 440px">
                <span class="box-fornecedor-label"><b>Abastecimentos</b></span>
            </div>
        </div>
        <div class="box-fornecedor-row min">
            <div class="box-fornecedor-field min" style="width: 90px">
                <span class="box-fornecedor-label min" ><b>Data</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 90px; left: 105px; border-left: 1px solid; text-align: right;">
                <span class="box-fornecedor-label min" ><b>KM inicial</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 90px; left: 210px; border-left: 1px solid; text-align: right;">
                <span class="box-fornecedor-label min" ><b>Km final</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 90px; left: 315px; border-left: 1px solid; text-align: right;">
                <span class="box-fornecedor-label min" ><b>= Km total</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 125px; left: 420px;  border-left: 1px solid;  text-align: right;">
                <span class="box-fornecedor-label min" ><b>Litros</b></span>
            </div>
            <div class="box-fornecedor-field min" style="width: 125px; left: 560px; border-left: 1px solid; text-align: right">
                <span class="box-fornecedor-label min" ><b>Valor</b></span>
            </div>
        </div>
        @for ($i = 0; $i < $quadroabastqtdelinha; $i++)
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 90px;">
                    <span class="box-fornecedor-label min" >
                        <span style="padding: 0 7px 0 0; color: white">__</span><span style="padding: 0 3px 0 3px;">/</span><span style="padding: 0 3px 0 3px; color: white">__</span><span style="">/</span>
                    </span>
                </div>
                <div class="box-fornecedor-field min" style="width: 90px; left: 105px; border-left: 1px solid; text-align: right;">
                </div>
                <div class="box-fornecedor-field min" style="width: 90px; left: 210px; border-left: 1px solid; text-align: right;">
                </div>
                <div class="box-fornecedor-field min" style="width: 90px; left: 315px; border-left: 1px solid; text-align: right;">
                </div>
                <div class="box-fornecedor-field min" style="width: 125px; left: 420px;  border-left: 1px solid;  text-align: right;">
                </div>
                <div class="box-fornecedor-field min" style="width: 125px; left: 560px; border-left: 1px solid; text-align: right">
                </div>

            </div>
        @endfor
    </div>
    @endif --}}
    {{-- abastecimentos --}}
    {{-- manutenção --}}
    {{-- @if($quadromanutencaoqtdelinha > 0)
    <div class="box-fornecedor">
        <div class="box-fornecedor-row">
            <div class="box-fornecedor-field" style="width: 440px">
                <span class="box-fornecedor-label"><b>Manutenção</b></span>
            </div>
        </div>
        @for ($i = 0; $i < $quadromanutencaoqtdelinha; $i++)
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 220px;">
                </div>
                <div class="box-fornecedor-field min" style="width: 220px; left: 225px; border-left: 1px solid; text-align: right;">
                </div>
                <div class="box-fornecedor-field min" style="width: 220px; left: 450px; border-left: 1px solid; text-align: right">
                </div>
            </div>
        @endfor
    </div>
    @endif --}}
    {{-- manutenção --}}

    {{-- final check list --}}
    {{-- <table style="border: 0;">
        <tr>
            <td style="width: 400px; padding: 0; margin: 0;">
                <div class="box-checklist" >
                    <div class="box-checklist-row">
                        <div class="box-checklist-field" style="width: 305px;  ">
                            <span class="box-checklist-label"><b>Check list de liberação de veículo</b></span>
                        </div>
                        <div class="box-checklist-field" style="width: 20px; left: 320px; text-align: center; border-left: 1px solid black; ">
                            <span class="box-checklist-label" style="font-size: 10px; text-align: center;">OK</span>
                        </div>
                        <div class="box-checklist-field" style="width: 30px; left: 360px;  border-left: 1px solid black;  ">
                            <span class="box-checklist-label" style="font-size: 10px; text-align: center;">Defeito</span>
                        </div>
                    </div>
                    @foreach ($checklist as $nitem => $item)
                        <div class="box-checklist-row min" >
                            <div class="box-checklist-field" style="width: 305px;">
                                <div style="padding-left: 1px;">{{($nitem+1) . ') ' . $item}}</div>
                            </div>
                            <div class="box-checklist-field" style="width: 20px; left: 320px; text-align: center; border-left: 1px solid black;">
                            </div>
                            <div class="box-checklist-field" style="width: 30px; left: 360px;  border-left: 1px solid black;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </td>
            <td style=" width: 280px; padding: 0 0 0 10px; margin: 0;">

                <div class="box-checklist" style="text-align: left;" >
                    <div class="box-checklist-row">
                        <div class="box-checklist-field" style="width: 250px; ">
                            <span class="box-checklist-label"><b>Roteiro de viagem</b></span>
                        </div>
                    </div>
                    <div class="box-checklist-row min">
                        <div class="box-checklist-field min" style="width: 250px;">
                            <b>Origem: </b>{{ ($carga->cidadeorigem ? $carga->cidadeorigem->cidade . ' / ' . $carga->cidadeorigem->uf : ' - ') }}
                        </div>
                    </div>
                    @foreach ($carga->roteiro as $key => $rota)
                        <div class="box-checklist-row min">
                            <div class="box-checklist-field min" style="width: 250px;">
                                {{ ($key+1) . ') ' . (($rota->cidade ?$rota->cidade->id > 0 : false) ? $rota->cidade->cidade . ' / ' . $rota->cidade->uf : '') . ($rota->rota != '' ? ' - ' . $rota->rota : '') }}
                            </div>
                        </div>
                    @endforeach
                    <div class="box-checklist-row min">
                        <div class="box-checklist-field min" style="width: 250px;">
                            <b>Destino: </b>{{ ($carga->cidadedestino ? $carga->cidadedestino->cidade . ' / ' . $carga->cidadedestino->uf : ' - ') }}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table> --}}
    {{-- final check list --}}

</div>
<footer style="width: 100%;" id="footer">
    <div style="font-size: 9px; text-align: right;">
        Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y  H:i:s') }} {{$usuario ? ' - por ' . $usuario->nome : ''}}
    </div>
</footer>


</body>
</html>
