@component('mail::message')

# Solicitação de envio de arquivo XML

Prezado cliente,

Precisamos dos arquivos XML indicados abaixo.

@php
    $notas = null;
    if ($token->notas) $notas = json_decode($token->notas);
@endphp
@if($notas)
@component('mail::table')
| Número | Série | Chave de acesso |
| :-------------: | :-------------: | ------------- |
@foreach ($notas as $nota)
| {{$nota->numero}} |  {{str_pad($nota->serie, 3, "0", STR_PAD_LEFT)}} | {{$nota->chave}} |
@endforeach
@endcomponent
@endif

Por alguma razão, não tivemos acesso ao XML. Entre as possíveis causas estão:
- Não informar a transportadora no campo indicado da NF-e;
- Demora nos sites dos órgãos em processar o XML.

Por favor, clique no link abaixo para enviar os arquivos!

@php
    $url = env('APP_URL_FRONT_PAINELCLIENTE', '') . '/p/notas/empresa/xmlpendente?token=' . $token->token;
@endphp
@component('mail::button', ['url' => $url, 'color' => 'blue'])
Enviar arquivo XML
@endcomponent

ou copie esse link [{{$url}}]({{$url}})

🔴 *IMPORTANTE: SEM OS ARQUIVOS XML DAS NOTAS, OS PROCESSOS LIGADOS À ESSAS INFORMAÇÕES PERMANECERÃO PARADOS.* 🔴

@if ($token->mensagem ? $token->mensagem !== '' : false)
@component('mail::panel')
*Mensagem do operador*

{!!$token->mensagem!!}
@endcomponent
@endif


*Este link estará disponível até {{$token->expire_at->format('d/m/Y - h:i')}}*


Obrigado,

@if($token->usuario)
**{{$token->usuario->nome}}**<br>
*{{$token->usuario->email}}*<br>
@endif
{{env('APP_NAME')}}
@endcomponent
