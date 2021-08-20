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

    .box-peso {
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

    .box-peso .box-peso-item {
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

    .box-peso .box-peso-item .box-peso-label {
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
        font-size: 19px;
        padding: 2px 10px;
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
    }

    .box-info-row-2 {
        font-weight: bold;
        font-size: 14px;
        padding: 0px 10px;
        direction: rtl;
        line-height: 22px;
    }
    span.box-info-l-2 {
        font-size: 14px;
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
        font-size: 14px;
        font-weight: bold;
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
    </style>
</head>

<body>
<?php
    $count = 0;
    $volumes = 0;
    $peso = 0;
    $vlrfrete = 0;
?>
@foreach ($rows as $key => $row)
    <?php
        $count += 1;
        $volumes += $row->qtde;
        $peso += $row->peso;
        $vlrfrete += $row->vlrfrete;
    ?>

<div class="invoice-box" >
        {{-- header --}}
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
                    <span class="box-info-l">Orçamento de Frete</span>
                    <span class="box-info-r">{{$row->id}}</span>
                </div>
                <div class="box-info-row">
                    <span class="box-info-l">Coleta prevista p/</span>
                    <span class="box-info-r">{{$row->dhcoleta->format('d/m/Y')}}</span>
                </div>

                <div class="box-info-row-2">
                    <span class="box-info-l-2" style="width: 150px;">Data orçamento</span>
                    <span class="box-info-r-2">{{$row->created_at->format('d/m/Y')}}</span>
                </div>

                <div class="box-info-row" style="border-bottom: 0; border-top: 1px solid;">
                    <span class="box-info-l">Valor do frete</span>
                    <span class="box-info-r">R$ {{formatMoney($row->vlrfrete, 2)}}</span>
                </div>
            </div>
        </div>
        {{-- header --}}

        {{-- fornecedor origem - remetente --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 485px; border-right: 1px solid;">
                    <span class="box-fornecedor-label">Rementente:</span> {{$row->clienteorigem ? $row->clienteorigem->razaosocial : 'NÃO INFORMADO'}}
                </div>
                <div class="box-fornecedor-field" style="left: 505px; border: 0; width: 180px;">
                    <span class="box-fornecedor-label">CNPJ:</span> {{$row->clienteorigem ? formatCnpjCpf($row->clienteorigem->cnpj) : ''}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 570px; ">
                    <span class="box-fornecedor-label min" >Endereço:</span> {{utf8_decode2($row->clienteorigem->enderecoenumero) . ($row->clienteorigem->complemento == '' ? '' : ' - ' . $row->clienteorigem->complemento)}}
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                <span class="box-fornecedor-label min">CEP: </span> {{formatFloat("##.###-###", $row->clienteorigem->cep)}}
                </div>
            </div>
            <div class="box-fornecedor-row min" style="border-bottom: 0px">
                <div class="box-fornecedor-field min" style="width: 270px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Bairro:</span> {{$row->clienteorigem->bairro}}
                </div>
                <div class="box-fornecedor-field min" style="width: 337px; right: 61px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Cidade:</span> <span style="text-transform: uppercase;">{{$row->clienteorigem->cidade->cidade}}</span>
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0; width: 45px;">
                    <span class="box-fornecedor-label min" >UF:</span><span style="text-transform: uppercase;">{{$row->clienteorigem->cidade->uf}}</span>
                </div>
            </div>
        </div>
        {{-- fornecedor origem - remetente --}}


        {{-- fornecedor destino --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 485px; border-right: 1px solid;">
                    <span class="box-fornecedor-label">Destinatário:</span> {{$row->clientedestino ? $row->clientedestino->razaosocial : 'NÃO INFORMADO'}}
                </div>
                <div class="box-fornecedor-field" style="left: 505px; border: 0; width: 180px;">
                    <span class="box-fornecedor-label">CNPJ:</span> {{$row->clientedestino ? formatCnpjCpf($row->clientedestino->cnpj) : ''}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 570px; ">
                    <span class="box-fornecedor-label min" >Endereço:</span> {{utf8_decode2($row->clientedestino->enderecoenumero) . ($row->clientedestino->complemento == '' ? '' : ' - ' . $row->clientedestino->complemento)}}
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                <span class="box-fornecedor-label min">CEP: </span> {{formatFloat("##.###-###", $row->clientedestino->cep)}}
                </div>
            </div>
            <div class="box-fornecedor-row min" style="border-bottom: 0px">
                <div class="box-fornecedor-field min" style="width: 270px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Bairro:</span> {{$row->clientedestino->bairro}}
                </div>
                <div class="box-fornecedor-field min" style="width: 337px; right: 61px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Cidade:</span> <span style="text-transform: uppercase;">{{$row->clientedestino->cidade->cidade}}</span>
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0; width: 45px;">
                    <span class="box-fornecedor-label min" >UF:</span><span style="text-transform: uppercase;">{{$row->clientedestino->cidade->uf}}</span>
                </div>
            </div>
        </div>
        {{-- fornecedor destino --}}


        {{-- local da coleta --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 485px;">
                    LOCAL DA COLETA
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 570px; ">
                    <span class="box-fornecedor-label min" >Endereço:</span> {{utf8_decode2($row->enderecoenumero) . ($row->endcoleta_complemento == '' ? '' : ' - ' . $row->endcoleta_complemento)}}
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                <span class="box-fornecedor-label min">CEP: </span> {{formatFloat("##.###-###", $row->endcoleta_cep)}}
                </div>
            </div>
            <div class="box-fornecedor-row min" style="border-bottom: 0px">
                <div class="box-fornecedor-field min" style="width: 270px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Bairro:</span> {{$row->endcoleta_bairro}}
                </div>
                <div class="box-fornecedor-field min" style="width: 337px; right: 61px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Cidade:</span> <span style="text-transform: uppercase;">{{$row->coletacidade->cidade}}</span>
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0; width: 45px;">
                    <span class="box-fornecedor-label min" >UF:</span><span style="text-transform: uppercase;">{{$row->coletacidade->uf}}</span>
                </div>
            </div>
        </div>
        {{-- local da coleta --}}

        {{-- dados da coleta--}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 485px; border-right: 0">
                    DADOS DA COLETA
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 150px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Peso:</span> {{formatMoney($row->peso)}}
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 170px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Volumes:</span> {{formatMoney($row->qtde, 0)}}
                </div>
                <div class="box-fornecedor-field min" style="left: 340px; width: 360px;">
                    <span class="box-fornecedor-label min" >Espécie:</span> {{$row->especie}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 150px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Carga Urgente:</span> {!! $row->cargaurgente == 1 ? nl2br('<b>* SIM *</b>') : 'Não' !!}
                </div>
                <div class="box-fornecedor-field min" style="width: 150px; left: 170px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Exclusiva:</span> {!! $row->veiculoexclusico == 1 ? nl2br('<b>* SIM *</b>') : 'Não' !!}
                </div>
                <div class="box-fornecedor-field min" style="left: 340px; width: 360px;">
                    <span class="box-fornecedor-label min" >Contém produtos perigosos:</span> {!! $row->produtosperigosos == 1 ? nl2br('<b>* SIM *</b>') : 'Não' !!}
                </div>
            </div>
            @if($row->obscoleta !== '')
            {{-- obs --}}
            <div style="margin-top: 0px;">
                <table cellpadding="0" cellspacing="0">
                    <tr class="details">
                        <td style="min-height: 200px;">
                            <div style="text-align: justify; letter-spacing: 0; text-justify: inter-word; word-break: break-all; font-size: 12px; ">
                                <span class="box-fornecedor-label">Observação:</span>
                                {!! nl2br(e($row->obscoleta)) !!}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            {{-- obs --}}
            @endif
        </div>
        {{-- dados da coleta--}}



        {{-- obs --}}
        <div style="border: 1px solid; border-radius: 6px; margin-top: 15px;">
            <table cellpadding="0" cellspacing="0">
                <tr class="details">
                    <td style="min-height: 200px;">
                        <div style="text-align: justify; letter-spacing: 0; text-justify: inter-word; word-break: break-all; ">
                            <span class="box-fornecedor-label">Observação do orçamento: </span>
                            {!! nl2br(e($row->obsorcamento)) !!}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        {{-- obs --}}
    </div>
    @if($key !== (count($rows)-1))
        <div class="line-separator"></div>
    @endif
@endforeach

{{-- totalização --}}
@if(count($rows) > 0 )
<div class="box-peso" style="font-size: 20px; background: #e0e0e0;">
    <div class="box-peso-item" style="width: 250px; font-size: 18px;"><span class="box-peso-label">Quantidade de orçamentos:</span> {{$count}}</div>
    <div class="box-peso-item" style="border-right: 0px solid; right: 0px; width: 400px; font-size: 18px; text-align: right; padding-right: 10px;">
        <span class="box-peso-label">Valor total:</span> R$ {{formatMoney($vlrfrete, 2)}}
    </div>
</div>
<div class="box-peso" style="font-size: 20px; background: #eeecec; margin-top: 5px;">
    <div class="box-peso-item" style="width: 250px; font-size: 18px;"><span class="box-peso-label">Volumes:</span> {{formatMoney($volumes, 0)}}</div>
    <div class="box-peso-item" style="border-right: 0px solid; right: 0px; width: 400px; font-size: 18px; text-align: right; padding-right: 10px;">
        <span class="box-peso-label">Peso:</span> {{formatMoney($peso, 0)}}
    </div>
</div>
<div style="border: 1px solid; border-radius: 6px; margin-top: 15px; padding: 5px; background: black;">
    <table cellpadding="0" cellspacing="0">
        <tr class="details">
            <td style="min-height: 200px; font-size: 13px; font-weight: bold; color: white;">
                <div style="text-align: justify; letter-spacing: 0; text-justify: inter-word; word-break: break-all; ">
                    <div>Em caso de aprovação do orçamento, ao agendar coleta, favor informar o número do orçamento.</div>
                    <div>Coletas agendadas sem número do orçamento serão cobradas conforme o valor da tabela.</div>
                </div>
            </td>
        </tr>
    </table>
</div>

@endif
{{-- totalização --}}
</body>
</html>
