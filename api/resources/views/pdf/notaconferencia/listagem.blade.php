<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Impressão</title>
    <style>
    body {
        position: relative;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }
    .page_break { page-break-before: always; }

    .invoice-box {
        height: auto;
        font-size: 10px;
        line-height: 15px;
        color: #000;
        margin: 0;
        position: relative;

    }

    .invoice-box table {
        width: 100%;
        text-align: left;
        font-size: 10px;
    }

    .invoice-box table td {
        padding: 1px 2px;
        vertical-align: top;
    }

    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }

    /* .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 10px;
    } */

    .invoice-box table tr.heading td {
        border-top: 1px solid rgb(0, 0, 0);
        border-bottom: 1px solid rgb(0, 0, 0);
        font-weight: bold;
        border-radius: 0;
        font-size: 12px;
        border-right: 1px solid;
        vertical-align: middle;
        page-break-after: always;
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
        /* border-top: 2px solid #eee; */
        font-weight: bold;
    }

    .text-infoempresa {
        font-size: 14px;
        line-height: 16px;
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

    .box-info-hearderdireita {
        width: 510px;
        direction: rtl;
        position: absolute;
        top: 0;
        right: 0;
        padding: 5px;
    }

    .box-info-row {
        font-weight: bold;
        font-size: 20px;
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



    .box-line {
        margin-top: 5px;
        font-size: 12px;
        position: relative;
    }
    .box-line-row {
        font-weight: bold;
        line-height: 16px;
        border-bottom: 1px solid black;
        width: 100%;
        position: relative;
        height: 20px;
    }

    .box-field {
      padding-top: 0;
      padding-bottom: 0;
      padding-left: 5px;
      padding-right: 10px;
      font-weight: bold;
      position: absolute;
      height: 20px;
      top: 0;
      white-space: pre-line;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .box-label {
        font-weight: normal;
        padding-right: 5px;
    }

    .box-line-row .min {
        font-weight: normal;
    }





    .box-field .min {
        font-size: 12px;
        font-weight: normal;
    }
    .box-label .min {
        font-size: 10px;
        font-weight: bold !important;
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
        <div class="box-info-hearderdireita">
            <div class="box-info-row">
                <span class="box-info-r">Listagem de entrada de notas - chaves</span>
            </div>
            {{-- <div class="box-info-row-2" style="margin-bottom: 5px">
                <span class="box-info-l-2">Veículo</span>
                <span class="box-info-r-2">{{$row->veiculo ? $row->veiculo->placa : ' - '}}</span>
            </div> --}}
        </div>
    </div>
    {{-- origem --}}

    @if(count($rows) <= 0)
        <table>
            <tr>
                <td>
                    <div class="box-line-row">
                        <div class="box-field" style="width: 1020px; text-align: center;">
                            <span class="box-label" style="font-size: 11px;">Nenhum registro encontrado</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    @else
    <?php
        $countitem = 1;
        $page = 0;
        $perpagefirst = 22;
        $perpage = 30;
        $peso = 0;
        $qvol = 0;
        $pagecount = intval(count($rows) / $perpage);
        if (count($rows) % $perpage !== 0) $pagecount = $pagecount+1;
    ?>

    @foreach ($rows as $keyrow => $row)
        @if($countitem == 1)
        <table>
        <tr class="heading">
            <td style="width: 20px; text-align: left; ">#</td>
            <td style="width: 270px; text-align: left; overflow: hidden;">Cliente</td>
            <td style="width: 95px; text-align: center; ">CNPJ</td>
            <td style="width: 55px; text-align: center; ">Número</td>
            <td style="width: 65px; text-align: right; ">Peso</td>
            <td style="width: 35px; text-align: center; ">Qtde</td>
            <td style="width: 250px; text-align: center; ">Chave</td>
            <td style="width: 55px; text-align: center; text-size: 10px; ">Cadastro</td>
            <td style="width: 55px; text-align: left; ">Usuário</td>
            <td style="width: 40px; text-align: center; border-right: 0px">id</td>
        </tr>
        @endif
        <tr class="details">
            <td>{{ ($keyrow+1) }}</td>
            <td style="text-align: left;">
                <div style="width: 270px; text-align: left; text-overflow: string; white-space: nowrap; overflow: hidden; ">
                    {{ $row->cliente ? ellipsis($row->cliente->razaosocial, 42) : 'Não identificado'}}
                </div>
            </td>
            <td style="text-align: center;">{{ formatCnpjCpf($row->notacnpj) }}</td>
            <td style="text-align: center;">{{ $row->notanumero }}</td>
            <td style="text-align: right;">{{ formatRS($row->peso, 3, false) }}</td>
            <td style="text-align: center;">{{ $row->qtde }}</td>
            <td style="text-align: center;">{{ $row->notachave }}</td>
            <td style="text-align: center;">{{ $row->created_at ? $row->created_at->format('d/m/Y') : ''}}</td>
            <td>
                <div style="width: 80px; text-align: left; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                    {{ $row->created_usuario ? ellipsis($row->created_usuario->nome, 20) : 'Não identificado'}}
                </div>
            </td>
            <td style="border-right: 0px; text-align: center;">
                {{ $row->id }}
            </td>
        </tr>
        <?php
        $showfooter = false;
        $peso = $peso + $row->peso;
        $qvol = $qvol + $row->qtde;
        $countitem = $countitem + 1;
        if (($countitem > $perpage) || (($countitem > $perpagefirst) && ($page == 0)) || (count($rows) === ($keyrow+1))) {
            $page = $page + 1;
            $countitem = 1;
            $showfooter = true;
            $pagecount = intval(count($rows) / $perpage);
            if (count($rows) % $perpage !== 0) $pagecount = $pagecount+1;
        }
        ?>

        @if($showfooter)
        </table>
        <footer style="width: 100%;" id="footer">
            <div style="font-size: 9px; text-align: right;">
                Impresso em {{ \Carbon\Carbon::now()->format('d/m/Y  H:i:s') }} {{$usuario ? ' - por ' . $usuario->nome : ''}}
                - Página {{$page}} de {{$pagecount}}
            </div>
        </footer>
        @if($page<$pagecount)
        <div class="page_break"></div>
        @endif
        @endif
    @endforeach
        <table>
            <tr>
                <td>
                    <div style="font-size: 13px; margin-top: 60px;">
                    <div  style="font-size: 13px; padding: 10px 0;">Eu, ______________________________________, declaro ter recebido de ______________________________________ a relação de notas fiscais acima.</div>
                    <div style="margin: 10px 0; padding: 5px; width: 400px; border: 1px solid black; border-radius: 5px;">
                        <table style="font-size: 13px;">
                            <tr>
                                <td style="width: 180px">Quantidade de notas</td>
                                <td style="text-align: left">{{ count($rows) }}</td>
                            </tr>
                            <tr>
                                <td>Peso total</td>
                                <td style="text-align: left">{{ formatRS($peso, 3, false) }} Kg</td>
                            </tr>
                            <tr>
                                <td>Quantidade de volumes</td>
                                <td style="text-align: left">{{ $qvol }}</td>
                            </tr>
                        </table>
                    </div>
                    <div style="padding: 5px 0;">Total de páginas: {{ $pagecount }}</div>
                    <div style="padding: 5px 0;">Data: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                    </div>

                </td>
            </tr>
        </table>
    @endif
    {{-- origem --}}


</div>






</body>
</html>
