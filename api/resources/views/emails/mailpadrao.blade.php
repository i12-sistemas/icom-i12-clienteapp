@component('mail::message')
Olá{{ $remetenteola === '' ? '' : ', ' . $remetenteola }}

@if (isset($mensagem) ? $mensagem !== ''  : false)
<div style="white-space: pre-line">
{{ $mensagem }}
</div>

@endif
@if ($anexos)
@component('mail::panel')
    @if(count($anexos)==1)
    Existe um arquivos em anexo a este e-mail!
    @else
    Existem {{count($anexos) }} arquivo(s) anexos a este e-mail!
    @endif
@endcomponent
@endif

Atenciosamente,<br><br>
@if(isset($usuario))
    {{$usuario->nome}}<br>
    <i>{{$usuario->email}}</i>
@endif
<br>
<b>{{ config('app.name') }}</b>

@if(!$replyOK)
<br><br><br>
<b><i>IMPORTANTE: E-mail não monitorado - Favor não respondê-lo!</i></b>
@endif
@endcomponent
