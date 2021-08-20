@component('mail::message')

# Código de redefinição de senha

Olá {{$token->usuario->nome}},

Use esse código para redefinir a senha da conta **{{$token->usuario->login}}** do sistema do sistema {{env('APP_NAME')}}.

@component('mail::panel')
### Aqui está o seu código:
# {{$token->codenumber}}
@endcomponent
<?php
$url = env('APP_URL_FRONT') . '/resetpwd/change?username=' . $token->usuario->login . '&email=' . $token->usuario->email . '&codenumber=' . $token->codenumber;
$url_revoke = env('APP_URL_FRONT') . '/resetpwd/revoke?username=' . $token->usuario->login . '&email=' . $token->usuario->email . '&codenumber=' . $token->codenumber;
?>
@component('mail::button', ['url' => $url, 'color' => 'blue'])
Alterar senha agora
@endcomponent

Se não reconhece essa solicitação, você pode clicar em revogar para cancelar o processo de alteração de senha.

@component('mail::button', ['url' => $url_revoke, 'color' => 'red'])
Revogar
@endcomponent

Obrigado,

Equipe de contas da {{env('APP_NAME')}}

@endcomponent
