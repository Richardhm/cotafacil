<style>
    * {margin:0;padding:0;}
    table {border-collapse: separate !important;border-spacing: 0;}
    .bordered {border: solid #ccc 1px;-moz-border-radius: 6px;-webkit-border-radius: 6px;border-radius: 6px;}
    tbody td,tfoot td{width:14%;padding:0;}
</style>
<div class="flex justify-between items-center py-2 w-full mb-2 text-sm font-medium dark:text-white text-black bg-white rounded-lg border border-black dark:border-gray-200 dark:bg-gray-500 dark:bg-opacity-10">
    <img src="{{$imagem_plano}}" alt="Operadora" class="ml-2" style="width:100px;border-radius:5px;padding:2px;background-color: white;">
    <h4 class="text-black  dark:text-white">{{$plano_nome}}</h4>
    <p class="text-black dark:text-white text-center mr-2">{{$cidade_nome}}</p>
</div>

{{-- Monta os dados dos dois quadros ANTES de desenhar qualquer coisa.
     As chaves são "{acomodacao_id}_{com|sem}_copar" (1=APART, 2=ENFER) e já
     nascem zeradas — cidade sem alguma combinação (ex.: Maringá sem copar
     parcial) não estoura "Undefined array key". Os flags $tem* decidem quais
     grupos de colunas aparecem: grupo sem NENHUM dado é escondido inteiro. --}}
@php
    $dadosComOdonto = [];
    $dadosSemOdonto = [];
    $temComCoparOdonto = false;
    $temSemCoparOdonto = false;
    $temComCoparSemOdonto = false;
    $temSemCoparSemOdonto = false;

    foreach ($dados as $dado) {
        $faixaNome = $dado->faixaEtaria->nome;
        $index     = ($dado->coparticipacao == 1) ? 'com_copar' : 'sem_copar';

        if ($dado->odonto == 1) {
            $grupo = &$dadosComOdonto;
            if ($index === 'com_copar') { $temComCoparOdonto = true; } else { $temSemCoparOdonto = true; }
        } else {
            $grupo = &$dadosSemOdonto;
            if ($index === 'com_copar') { $temComCoparSemOdonto = true; } else { $temSemCoparSemOdonto = true; }
        }

        if (!isset($grupo[$faixaNome])) {
            $grupo[$faixaNome] = [
                '1_com_copar' => 0,
                '2_com_copar' => 0,
                '1_sem_copar' => 0,
                '2_sem_copar' => 0,
                'quantidade'  => $dado->quantidade,
            ];
        }

        $grupo[$faixaNome]["{$dado->acomodacao_id}_{$index}"] = $dado->valor ?? 0;
        unset($grupo);
    }

    $totalApartamento_com_copar = 0;
    $totalEnfermaria_com_copar = 0;
    $totalApartamento_sem_copar = 0;
    $totalEnfermaria_sem_copar = 0;
    $totalApartamentoSemOdonto_com_copar = 0;
    $totalEnfermariaSemOdonto_com_copar = 0;
    $totalApartamentoSemOdonto_sem_copar = 0;
    $totalEnfermariaSemOdonto_sem_copar = 0;
@endphp

@if($status_odonto)
    <div class="flex justify-center items-center w-full
    py-0.5 mb-1 text-sm font-medium
    text-white focus:outline-none bg-gray-500 bg-opacity-10 rounded-lg border
    border-gray-200 dark:bg-gray-500 dark:bg-opacity-10 text-white">
       Com Odonto
    </div>

<table class="min-w-full bg-gray-300 bg-opacity-20 rounded-lg bordered">
    <thead>
    <tr>
        <td rowspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white" style="vertical-align:middle;">Faixa Etária</td>
        @if($temComCoparOdonto)
        <td colspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white">Com Copar</td>
        @endif
        @if($temSemCoparOdonto)
        <td colspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white">Sem Copar</td>
        @endif
    </tr>
    <tr>
        @if($temComCoparOdonto)
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">APART</td>
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">ENFER</td>
        @endif
        @if($temSemCoparOdonto)
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">APART</td>
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">ENFER</td>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach($dadosComOdonto as $faixaEtaria => $valores)
        @for($i=0;$i<$valores['quantidade'];$i++)
            <tr style="margin: 0;padding:0;">
                <td class="dark:text-white text-white text-center text-xs">{{ $faixaEtaria }}</td>
                @if($temComCoparOdonto)
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valores['1_com_copar'], 2, ",", ".") }}</span>
                    @php
                        $totalApartamento_com_copar += $valores['1_com_copar'];
                    @endphp
                </td>

                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valores['2_com_copar'], 2, ",", ".") }}</span>
                    @php
                        $totalEnfermaria_com_copar += $valores['2_com_copar'];
                    @endphp
                </td>
                @endif

                @if($temSemCoparOdonto)
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valores['1_sem_copar'], 2, ",", ".") }}</span>
                    @php
                        $totalApartamento_sem_copar += $valores['1_sem_copar'];
                    @endphp
                </td>
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valores['2_sem_copar'], 2, ",", ".") }}</span>
                    @php
                        $totalEnfermaria_sem_copar += $valores['2_sem_copar'];
                    @endphp
                </td>
                @endif

            </tr>
        @endfor
    @endforeach


    </tbody>
</table>
    <table class="dark:bg-gray-700 w-full dark:bg-opacity-20 rounded-lg bordered mt-2 py-0.5">

        <tfoot>
        <tr>
            <td class="dark:text-white text-white text-xs py-0.5 text-center">Total</td>
            @if($temComCoparOdonto)
            <td class="dark:text-white text-white py-0.5 text-right mr-1 text-xs">
                <span class="mr-2 text-xs">{{ number_format($totalApartamento_com_copar, 2, ",", ".") }}</span>
            </td>
            <td class="dark:text-white text-white py-0.5 text-right mr-1 text-xs">
                <span class="mr-2 text-xs">{{ number_format($totalEnfermaria_com_copar, 2, ",", ".") }}</span>
            </td>
            @endif

            @if($temSemCoparOdonto)
            <td class="dark:text-white text-white py-0.5 text-xs text-right mr-1">
                <span class="mr-2 text-xs">{{ number_format($totalApartamento_sem_copar, 2, ",", ".") }}</span>
            </td>
            <td class="dark:text-white text-white py-0.5 text-xs text-right mr-1">
                <span class="mr-2 text-xs">{{ number_format($totalEnfermaria_sem_copar, 2, ",", ".") }}</span>
            </td>
            @endif

        </tr>
        </tfoot>

    </table>







<button data-odonto="1" class="downloadLink flex justify-center items-center w-full
py-0.5 mb-1 text-sm font-medium mt-2
text-white focus:outline-none bg-gray-700 rounded-lg border
border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10
focus:ring-4 focus:ring-gray-200 bg-red-400">
    Gerar Imagem
</button>

<div class="h-1 my-1 w-full dark:bg-white bg-white rounded-lg"></div>

@endif


@if($status)


{{--Sem Odotno--}}
{{-- Tabela sem Odonto --}}
<div class="flex justify-center items-center w-full
py-0.5 mb-1 text-sm font-medium
text-white focus:outline-none bg-gray-500 bg-opacity-10 rounded-lg border
border-gray-200 dark:bg-gray-500 dark:bg-opacity-10 text-black">
    Sem Odonto
</div>


<table class="min-w-full bg-gray-300 bg-opacity-20 rounded-lg bordered">
    <thead>

    <tr>
        <td rowspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white" style="vertical-align:middle;">Faixa Etária</td>
        @if($temComCoparSemOdonto)
        <td colspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white">Com Copar</td>
        @endif
        @if($temSemCoparSemOdonto)
        <td colspan="2" class="text-center text-sm border dark:border-white border-r border-b border-white text-white dark:text-white">Sem Copar</td>
        @endif
    </tr>
    <tr>
        @if($temComCoparSemOdonto)
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">APART</td>
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">ENFER</td>
        @endif

        @if($temSemCoparSemOdonto)
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">APART</td>
        <td class="border dark:border-white border-r border-b border-white text-sm text-white dark:text-white text-center">ENFER</td>
        @endif

    </tr>
    </thead>
    <tbody>
    @foreach($dadosSemOdonto as $faixaEtariaSemOdonto => $valorSemOdonto)
        @for($ii=0;$ii<$valorSemOdonto['quantidade'];$ii++)
            <tr>
                <td class="dark:text-white text-white text-center text-xs">{{ $faixaEtariaSemOdonto }}</td>
                @if($temComCoparSemOdonto)
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valorSemOdonto['1_com_copar'], 2, ",", ".") }}</span>
                    @php $totalApartamentoSemOdonto_com_copar += $valorSemOdonto['1_com_copar'];@endphp
                </td>
                <td class="dark:text-white text-white text-right text-xs">
                    <span class="mr-2">{{ number_format($valorSemOdonto['2_com_copar'], 2, ",", ".") }}</span>
                    @php $totalEnfermariaSemOdonto_com_copar += $valorSemOdonto['2_com_copar'];@endphp
                </td>
                @endif
                @if($temSemCoparSemOdonto)
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valorSemOdonto['1_sem_copar'], 2, ",", ".") }}</span>
                    @php $totalApartamentoSemOdonto_sem_copar += $valorSemOdonto['1_sem_copar'];@endphp
                </td>
                <td class="dark:text-white text-white text-xs text-right">
                    <span class="mr-2">{{ number_format($valorSemOdonto['2_sem_copar'], 2, ",", ".") }}</span>
                    @php
                        $totalEnfermariaSemOdonto_sem_copar += $valorSemOdonto['2_sem_copar'];
                    @endphp
                </td>
                @endif

            </tr>
        @endfor
    @endforeach


    </tbody>
</table>
    <table class="dark:bg-gray-700 w-full dark:bg-opacity-20 rounded-lg bordered mt-2 py-0.5">
        <tfoot>
        <tr>
            <td class="dark:text-white text-white text-xs py-0.5 text-center">Total</td>
            @if($temComCoparSemOdonto)
            <td class="dark:text-white text-white py-0.5 text-xs text-right mr-1">
                <span class="mr-2">{{ number_format($totalApartamentoSemOdonto_com_copar, 2, ",", ".") }}</span>
            </td>
            <td class="dark:text-white text-white py-0.5 text-right text-xs mr-1">
                <span class="mr-2">{{ number_format($totalEnfermariaSemOdonto_com_copar, 2, ",", ".") }}</span>
            </td>
            @endif

            @if($temSemCoparSemOdonto)
            <td class="dark:text-white text-white py-0.5 text-right mr-1 text-xs">
                <span class="mr-2">{{ number_format($totalApartamentoSemOdonto_sem_copar, 2, ",", ".") }}</span>
            </td>
            <td class="dark:text-white text-white py-0.5 text-right mr-1 text-xs">
                <span class="mr-2">{{ number_format($totalEnfermariaSemOdonto_sem_copar, 2, ",", ".") }}</span>
            </td>
            @endif

        </tr>
        </tfoot>

    </table>


<button data-odonto="0" class="downloadLink flex justify-center items-center w-full
py-0.5 mb-1 text-sm font-medium mt-2
text-white focus:outline-none bg-gray-700 rounded-lg border
border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10
focus:ring-4 focus:ring-gray-200 bg-red-400">
    Gerar Imagem
</button>

@endif


<div class="flex justify-around items-center w-full mt-4 py-2">
    <label for="status_carencia">
        <input type="checkbox" checked="checked" name="status_carencia" id="status_carencia" class="w-6 h-6 text-teal-600 bg-white border border-2 border-black rounded dark:bg-white dark:border-purple-900">
        <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 text-2xl text-white">Com Carências</span>
    </label>

    @if($status_desconto)
        <label for="status_desconto">
            <input type="checkbox" name="status_desconto" id="status_desconto" class="w-6 h-6 text-teal-600 bg-white border border-2 border-black rounded dark:bg-white dark:border-purple-900">
            <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 text-2xl text-white">Desconto</span>
        </label>
    @endif

</div>

</div>
