@component('mail::message')
Um novo dispositivo foi cadastrado e requisita a liberação no aplicativo {{ config('app.name') }}.

Confira os dados do novo dispositivo:

@component('mail::panel')
UUID : {{ $dispositivo->uuid }}<br>
Descrição: {{ $dispositivo->descricao }}<br>
Modelo: {{ $dispositivo->model }}<br>
Fabricante: {{ $dispositivo->fabricante }}<br>
Plataforma: {{ $dispositivo->platform }}<br>
Versão: {{ $dispositivo->version }}
@endcomponent

Dados da requisição:

@component('mail::panel')
Data : {{ $link->created_at }}<br>
E-mail: {{ $link->email }}<br>
IP: {{ $link->ip }}
@endcomponent

@component('mail::button', ['url' => $url])
Avaliar solicitação
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
