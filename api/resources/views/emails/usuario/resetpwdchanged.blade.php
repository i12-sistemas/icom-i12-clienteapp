@component('mail::message')

# Aviso de alteração de senha


Olá {{$token->usuario->nome}},


A senha da conta **{{$token->usuario->login}}** foi alterada!


Caso não reconheça essa alteração, procure o suporte técnico do sistema.


Equipe de contas da {{env('APP_NAME')}}

@endcomponent
