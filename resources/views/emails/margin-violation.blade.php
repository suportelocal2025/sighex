<x-mail::message>
# Alerta de Margem Orçamentária

Prezado(a) Superintendente,

Foram detectadas violações de margem orçamentária em {{ count($alertas) }} unidade(s) no ano de {{ $ano }}.

<x-mail::table>
| Unidade | Mês | Limite | Gasto | Excedente |
| ------- | --- | -----: | ----: | --------: |
@foreach($alertas as $alerta)
| {{ $alerta['unidade_nome'] }} | {{ $alerta['mes_nome'] }} | R$ {{ number_format($alerta['limite'], 2, ',', '.') }} | R$ {{ number_format($alerta['gasto'], 2, ',', '.') }} | R$ {{ number_format($alerta['excedente'], 2, ',', '.') }} |
@endforeach
</x-mail::table>

Por favor, acesse o sistema para mais detalhes.

<x-mail::button :url="config('app.url') . '/superintendente'">
Acessar SIGEEX
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
