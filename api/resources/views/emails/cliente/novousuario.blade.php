@component('mail::message')

# Olá, {{$usuario->nome}}

Você foi registrado pela empresa *{{$usuario->cliente->razaosocial}}* no painel do cliente da **{{env('APP_NAME')}}**!

Estamos felizes em tê-lo aqui 😀

Faça login no [Painel do Cliente]({{env('APP_URL_FRONT_PAINELCLIENTE', '')}}) usando seu login e senha e tenha acesso as informações de coletas e entregas!

@component('mail::panel')
# Sua credencial de acesso

E-mail de acesso: **{{$usuario->email}}**

Senha: **{{$password}}**
@endcomponent


@component('mail::button', ['url' => env('APP_URL_FRONT_PAINELCLIENTE', ''), 'color' => 'blue'])
Painel do Cliente
@endcomponent


Atenciosamente,

@if($usuario->created_usuario)
**{{$usuario->created_usuario->nome}}**<br>
*{{$usuario->created_usuario->email}}*<br>
@endif
Equipe {{env('APP_NAME')}}
@endcomponent
