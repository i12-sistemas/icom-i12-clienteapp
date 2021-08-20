@component('mail::message')

# Código de redefinição de senha

Olá, {{$token->clienteusuario->nome}}

Use esse código para redefinir a senha da conta no Painel do Cliente :: {{env('APP_NAME')}}.

@component('mail::panel')
### Aqui está o seu código:
# {{$token->codenumber}}
@endcomponent
@php
$username = ($token->celular ? $token->celular !== '' : false) ? $token->celular : $token->email;
$url = env('APP_URL_FRONT_PAINELCLIENTE') . '/resetpwd/change?username=' . $username . '&email=' . $token->clienteusuario->email . '&codenumber=' . $token->codenumber;
@endphp

@component('mail::button', ['url' => $url, 'color' => 'blue'])
Alterar senha agora
@endcomponent

Obrigado,

Equipe de contas da {{env('APP_NAME')}}

@endcomponent
