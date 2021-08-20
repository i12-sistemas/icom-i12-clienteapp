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
?>
@foreach ($coletas as $key => $coleta)
    <?php
        $count += 1;
        $volumes += $coleta->qtde;
        $peso += $coleta->peso;
    ?>

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
                    <span class="box-info-l">Ordem de Coleta</span>
                    <span class="box-info-r">{{$coleta->id}}</span>
                </div>
                <div class="box-info-row">
                    <span class="box-info-l">Coletar em</span>
                    <span class="box-info-r">{{$coleta->dhcoleta->format('d/m/Y')}}</span>
                </div>

                <div class="box-info-row-2">
                    <span class="box-info-l-2">Motorista</span>
                    <span class="box-info-r-2">{{$coleta->motorista ? $coleta->motorista->nome : 'INDEFINIDO'}}</span>
                </div>
                <div class="box-info-row-2">
                    <span class="box-info-l-2">CPF</span>
                    <span class="box-info-r-2">{{$coleta->motorista ? $coleta->motorista->cpf : ' '}}</span>
                </div>
                <div class="box-info-row-2">
                    <span class="box-info-l-2">Placa</span>
                    <span class="box-info-r-2">{{$coleta->motorista ? ($coleta->motorista->veiculo ? $coleta->motorista->veiculo->placa : ' ') : ' '}}</span>
                </div>
            </div>
        </div>
        {{-- peso --}}
        <div class="box-peso">
            <div class="box-peso-item" style="width: 150px;"><span class="box-peso-label">Peso:</span> {{formatMoney($coleta->peso)}}</div>
            <div class="box-peso-item" style="width: 150px; left: 170px;"><span class="box-peso-label">Volumes:</span> {{formatMoney($coleta->qtde, 0)}}</div>
            <div class="box-peso-item" style="border-right: 0px solid; left: 330px; width: 360px;"><span class="box-peso-label">Espécie:</span> {{$coleta->especie}}</div>
        </div>
        {{-- fornecedor origem --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="width: 485px; border-right: 1px solid;">
                    <span class="box-fornecedor-label">Fornecedor:</span> {{$coleta->clienteorigem ? $coleta->clienteorigem->razaosocial : 'NÃO INFORMADO'}}
                </div>
                <div class="box-fornecedor-field" style="left: 505px; border: 0; width: 180px;">
                    <span class="box-fornecedor-label">CNPJ:</span> {{$coleta->clienteorigem ? formatCnpjCpf($coleta->clienteorigem->cnpj) : ''}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 570px; ">
                    <span class="box-fornecedor-label min" >Endereço:</span> {{utf8_decode2($coleta->enderecoenumero) . ($coleta->endcoleta_complemento == '' ? '' : ' - ' . $coleta->endcoleta_complemento)}}
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                <span class="box-fornecedor-label min">CEP: </span> {{formatFloat("##.###-###", $coleta->endcoleta_cep)}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 270px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Bairro:</span> {{$coleta->endcoleta_bairro}}
                </div>
                <div class="box-fornecedor-field min" style="width: 337px; right: 61px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Cidade:</span> <span style="text-transform: uppercase;">{{$coleta->coletacidade->cidade}}</span>
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0; width: 45px;">
                    <span class="box-fornecedor-label min" >UF:</span> {{$coleta->coletacidade->uf}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="width: 270px;left: 0px; border-right: 1px solid;">
                    <span class="box-fornecedor-label min" >Telefones:</span> {{$coleta->clienteorigem ? $coleta->clienteorigem->fone1 . ($coleta->clienteorigem->fone1 == '' || $coleta->clienteorigem->fone2 == '' ? '' : ' - ' . $coleta->clienteorigem->fone2) : ''}}
                </div>
                <div class="box-fornecedor-field min" style="width: 400px; left: 287px;">
                    <span class="box-fornecedor-label min" >Contato:</span> {{$coleta->contatonome  . ($coleta->contatonome == ''  ? '' : ' - ') . $coleta->contatoemail}}
                </div>
            </div>
            <div class="box-fornecedor-row min" style="border: 0px;">
                <div class="box-fornecedor-field min" style="width: 680px; left: 0px; white-space: normal;">
                    <span class="box-fornecedor-label min" >Horários:</span>
                    @if($coleta->clienteorigem)
                    <span>{{$coleta->clienteorigem->horariosegqui == '' ? '' : 'Qui à Sex: ' . $coleta->clienteorigem->horariosegqui}}</span>
                    <span>{{$coleta->clienteorigem->horariosex == '' ? '' : 'Sexta: ' . $coleta->clienteorigem->horariosex}}</span>
                    <span>{{$coleta->clienteorigem->horarioportaria == '' ? '' : 'Portaria: ' . $coleta->clienteorigem->horarioportaria}}</span>
                    @endif
                </div>
            </div>
        </div>
        {{-- fornecedor origem --}}

        {{-- fornecedor destino --}}
        <div class="box-fornecedor">
            <div class="box-fornecedor-row">
                <div class="box-fornecedor-field" style="min-width: 100px;">
                    <span class="box-fornecedor-label">Destinatário:</span> {{$coleta->clientedestino ? $coleta->clientedestino->razaosocial : 'CLIENTE INDEFINIDO'}}
                </div>
                <div class="box-fornecedor-field" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                    <span class="box-fornecedor-label">CNPJ:</span> {{$coleta->clientedestino ? formatCnpjCpf($coleta->clientedestino->cnpj) : ''}}
                </div>
            </div>
            <div class="box-fornecedor-row min">
                <div class="box-fornecedor-field min" style="min-width: 100px;">
                    <span class="box-fornecedor-label min" >Endereço:</span> {{$coleta->clientedestino ? utf8_decode2($coleta->clientedestino->enderecoenumero) . ($coleta->clientedestino->complemento == '' ? '' : ' - ' . $coleta->clientedestino->complemento) : ''}}
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 190px; border-left: 1px solid;">
                    <span class="box-fornecedor-label min">CEP:</span> {{$coleta->clientedestino ? formatFloat("##.###-###",$coleta->clientedestino->cep) : ''}}
                </div>
            </div>
            <div class="box-fornecedor-row min" style="border: 0px;">
                <div class="box-fornecedor-field min" style="width: 250px;left: 0px;">
                    <span class="box-fornecedor-label min" >Bairro:</span> {{$coleta->clientedestino ? $coleta->clientedestino->bairro : ''}}
                </div>
                <div class="box-fornecedor-field min" style="width: 330px;right: 72px;border-left: 1px solid;">
                    <span class="box-fornecedor-label min" >Cidade:</span> <span style="text-transform: uppercase;">{{$coleta->clientedestino ? $coleta->clientedestino->cidade->cidade : ''}}</span>
                </div>
                <div class="box-fornecedor-field min" style="right: 0;border: 0;min-width: 60px;border-left: 1px solid;">
                    <span class="box-fornecedor-label min" >UF:</span> {{$coleta->clientedestino ? utf8_decode2($coleta->clientedestino->cidade->uf) : ''}}
                </div>
            </div>
        </div>
        {{-- fornecedor destino --}}
        {{-- itens --}}
        @if($coleta->itens)
            @if(count($coleta->itens) > 0)
                <div class="text-emergencia">
                    {!! nl2br(e($infoprintprodperigosos)) !!}
                </div>
                <div style="border: 1px solid; border-radius: 6px;">
                <table cellpadding="0" cellspacing="0">
                    <tr class="heading" >
                        <td style="width: 50px">ONU</td>
                        <td style="width: 50px">Risco</td>
                        <td style="width: 60px">Sub-Risco</td>
                        <td>Produto</td>
                        <td style="width: 80px; text-align: right">Qtde</td>
                        <td style="width: 110px; border-right: 0px solid;">Embalagem</td>
                    </tr>
                    @foreach ($coleta->itens as $key => $item)
                    <tr class="details">
                        <td style="width: 50px">{{ $item->produto ? $item->produto->onu : ''}}</td>
                        <td style="width: 50px">{{ $item->produto ? $item->produto->numrisco : ''}}</td>
                        <td style="width: 60px">{{ $item->produto ? $item->produto->riscosubs : ''}}</td>
                        <td>{{ $item->produto ? $item->produto->nome : $item->produtodescricao}}</td>

                        <td style="width: 80px; text-align: right">{{formatMoney($item->qtde,2)}}</td>
                        <td style="width: 110px; border-right: 0px solid;">{{$item->embalagem}}</td>
                    </tr>
                    @endforeach
                    <tr >
                        <td colspan="3" style="border-right: 0px solid;"></td>
                        <td colspan="3" style="text-align: left; font-size: 10px; border-bottom: 0; border-right: 0px solid;">{{ count($coleta->itens) . ' itens' }}</td>
                    </tr>
                </table>
                </div>
        @endif
        @endif
        {{-- itens --}}


        {{-- obs --}}
        <div style="border: 1px solid; border-radius: 6px; margin-top: 15px;">
            <table cellpadding="0" cellspacing="0">
                <tr class="details">
                    <td style="min-height: 200px;">
                        <div style="text-align: justify; letter-spacing: 0; text-justify: inter-word;">
                            <span class="box-fornecedor-label">Obs.:</span>
                            {{$coleta->obs}}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        {{-- obs --}}
    </div>
    @if($key !== (count($coleta->itens)-1))
        <div class="line-separator"></div>
    @endif
@endforeach

{{-- totalização --}}
@if(count($coleta->itens) > 0 )
<div class="box-peso" style="font-size: 20px; background: #e0e0e0;">
    <div class="box-peso-item" style="width: 250px; font-size: 18px;"><span class="box-peso-label">Quantidade de coletas:</span> {{$count}}</div>
    <div class="box-peso-item" style="width: 250px; left: 270px; font-size: 18px;"><span class="box-peso-label">Volumes:</span> {{formatMoney($volumes, 0)}}</div>
    <div class="box-peso-item" style="border-right: 0px solid; left: 530px; min-width: 250px; font-size: 18px;"><span class="box-peso-label">Espécie:</span> {{formatMoney($peso, 0)}}</div>
</div>
@endif
{{-- totalização --}}
</body>
</html>
