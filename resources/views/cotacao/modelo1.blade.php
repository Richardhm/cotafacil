?<!doctype html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"

          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Document</title>

    <style>



        @font-face {

            font-family: 'Roboto';

            src: url('{{ public_path("fonts/Roboto-Regular.ttf") }}') format('truetype');

            font-weight: normal;

            font-style: normal;

        }



        @font-face {

            font-family: 'Roboto';

            src: url('{{ public_path("fonts/Roboto-Bold.ttf") }}') format('truetype');

            font-weight: bold;

            font-style: normal;

        }


        .valor-copart-desconto-parcial {
            background: rgb(255,248,124);
            /*background:red;*/
            padding: 8px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: rgb(8,73,189);
            /*color:white;*/
            font-weight: bold;
            display: block;
            font-size:1.6em;
            text-align:center;
        }


        .valor-copart-desconto-parcial-desconto {
            /*background: rgb(255,248,124);*/
            background:red;
            padding: 8px !important;
            margin: 5px 3px;
            border-radius: 8px;
            /*color: rgb(8,73,189);*/
            color:white;
            font-weight: bold;
            display: block;
            font-size:1.6em;
            text-align:center;
        }






        .valor-copart-laranja-parcial {
            background: rgb(254,199,72);

            padding: 12px !important;

            margin: 5px 3px;

            border-radius: 0 0 55px 55px;

            color: rgb(8,73,189);

            font-weight: bold;

            display: block;

            font-size:1.6em;

            text-align:center;
        }



        .header-orange-parcial {
            background: rgb(254,199,72);

            color: rgb(8,73,189);

            padding: 20px;

            border-radius: 55px 55px 0 0;

            font-weight: bold;

            font-size: 1.6em;
            white-space: nowrap;
        }

        .valor-copart-parcial {
            background: rgb(255,248,124);
            padding: 12px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: rgb(8,73,189);
            font-weight: bold;
            display: block;
            font-size:1.6em;
            text-align:center;
        }




        html, body {

            width: 100%;

            height: 100%;

            margin: 0;

            padding: 0;

            font-family: 'Roboto', sans-serif !important;

        }

        tr {

            line-height: 1;

            vertical-align: top;

        }





        .container {

            position: absolute;

            top: 390px;

            left: 50%;

            transform: translateX(-50%);

            width: 97%;

            padding-bottom: 0 !important; /* Remove padding residual */

            margin-bottom: -10px; /* Compensa espaçamento vertical */

        }



        /* Novos estilos */

        .faixa-etaria {

            text-align: center;

            font-size: 1.5em;

            background-color: rgb(5,53,95);

            color:#FFF;

            font-weight:bold;

        }





        /* Estilos do footer */

        .footer {

            position: absolute;

            bottom: 0px;

            width: calc(100% - 40px); /* Ajusta a largura do footer */

            height: 200px; /* Define a altura do footer */

            padding: 0px;

            box-sizing: border-box;

        }



        .footer img {

            position: absolute;

            bottom:15px;

            right: 0px;

            width: 400px;

            height: 400px;

            border-radius: 50%;

        }



        .footer .middle, .footer .right {

            position: absolute;

            bottom:0px;





        }



        .footer .middle {

            bottom:30px;

            left: 10px;

            display:block;

            color: #0c0c0c;

            font-size:1.7em;

            line-height:1;

            padding:0;

        }



        .footer .middle p {

            margin: 0px;

            color:#FFFFFF;

            font-weight: bold;

            line-height:1;

            padding:0;

        }



        .footer .right {

            bottom:20px;

            right: 30px;

            text-align: left;

            display:block;

            font-size:2em;

        }



        .footer .right p {

            margin: 0;

            color:#FFFFFF;

            font-weight: bold;

        }



        .cidade_container {

            position:absolute;

            top:190px;

            left:0%;

            font-weight: bold;

            font-size: 2em;

            color:white;

            text-align:center;



        }



        .frase_container {

            position:absolute;

            top:260px;

            left:0%;

            font-weight: bold;

            font-size: 1.5em;

            color:white;

            text-align: center;

        }









        /**********************/

        .bloco-container {

            width: 100%;

            margin: 0 auto;

            border-spacing: 10px; /* Espaço entre blocos */

            border-collapse: separate;

        }

        .bloco-container-desconto {

            width: 100%;

            margin: 0 auto;

            border-spacing: 5px; /* Espaço entre blocos */

            border-collapse: separate;

        }

        .container table {

            border-collapse: separate;

            border-spacing: 0;

            margin-bottom: -5px; /* Compensa o espaçamento vertical residual */

        }



        .bloco {

            display: inline-table;

            background: white;

            border-radius: 60px;

            vertical-align: top;

            padding: 10px;

            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra suave */

        }



        .header-orange {

            background: #F88058;

            color: white;

            padding: 20px;

            border-radius: 55px 55px 0 0;

            font-weight: bold;

            font-size: 1.6em;

            white-space: nowrap;



        }



        .subheader-blue {

            background: white;

            color: white;

            padding: 8px;

            font-size: 1.2em;

            color: rgb(8,73,189);

            font-weight:bold;

            text-align:center;

        }



        .linha-faixa {

            background: #FFF3CD;

            padding: 12px;

            margin: 8px 3px;

            border-radius: 8px;

            color: #366EBF;

            font-weight: bold;

            display: block;

        }



        .coluna-azul {

            background: white;

            font-weight:bold;

            padding: 8px;

            width: 50%;

            font-size: 1.2em;

            color: rgb(8,73,189);

            text-align:center;



        }



        .valor-copart {

            background: rgb(254,199,72);
            /*background: red;*/

            padding: 12px !important;

            margin: 5px 3px;

            border-radius: 8px;

            color: rgb(8,73,189);
            /*color: white;*/

            font-weight: bold;

            display: block;

            font-size:1.6em;

            text-align:center;

        }

        .valor-copart-desconto {

            /*background: rgb(254,199,72);*/
            background: red;

            padding: 12px !important;

            margin: 5px 3px;

            border-radius: 8px;

            /*color: rgb(8,73,189);*/
            color: white;

            font-weight: bold;

            display: block;

            font-size:1.6em;

            text-align:center;

        }



        .valor-copart-laranja {

            background: #F88058;

            padding: 12px !important;

            margin: 5px 3px;

            border-radius: 0 0 55px 55px;

            color: white;

            font-weight: bold;

            display: block;

            font-size:1.6em;

            text-align:center;

        }



        .lista-coparticipacao {

            color: white;

            font-size: 1.2em;

            margin:0;

        }



        .lista-coparticipacao strong {

            display: block;

            margin: 10px 0;

            font-size:1.5em;

        }



        .procedimentos-container {

            width: 70%;

            margin-top: 1px; /* Contém os floats */

            background-color: #F88058;

            border-radius: 55px;

            padding: 15px;

        }

        .procedimentos-container-excecao {

            width: 70%;

            margin-top: 1px; /* Contém os floats */

            background-color: #F88058;

            border-radius: 55px;

            padding: 15px;

        }



        .procedimento-left {

            display:inline-block;

            width: 60%;

            vertical-align: top;

        }



        .procedimento-right {

            display:inline-block;

            width: 35%;

            vertical-align: top;

        }





        .linha-procedimento {

            margin: 0;

            padding:0;

        }



        .linha-procedimento span {

            font-size:1.3em;

            color: rgb(8,73,189);

            display:block;

            font-weight:bold;

            margin:0px;

            padding:0 0 0 30px;



        }





        .valor-procedimento {

            background: #FFF3CD;



            border-radius: 20px;

            display: inline-block;

            margin-left: 10px;

        }


        #valores_odonto {
            position: relative;
            z-index: 2;
            background-color: white !important;
            width: 95%;
            margin: 20px auto 0;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        #valores_odonto td {
            padding: 10px !important;
            text-align: center !important;
        }

        #valores_odonto span[style*="border: 4px solid"] {
            background: #FFF3CD;
            border-color: #366EBF !important;
            padding: 8px 15px !important;
            font-size: 1em !important;
        }

        @if(($mostrar_apartamento + $mostrar_enfermaria) <= 1)
        .header-orange {
            font-size: 1.4em !important;
            padding: 10px !important;
        }
        .header-orange-parcial {
            font-size: 1.4em !important;
            padding: 10px !important;
        }
        .coluna-azul {
            font-size: 1.4em !important;
            padding: 10px !important;
        }
        @endif
    </style>

</head>

<body>

<img style="position: absolute;top: 0;left: 0;height: 100%;width: 100%;object-fit: cover;" src="{{ $folder ? $folder . '/' : '' }}layouts/modelo1.png" alt="Orçamento">

<p class="cidade_container" style="text-transform:uppercase;width:100%">
    {{$cidade}}
</p>

<p class="frase_container" style="text-transform:uppercase;width:100%;">
    {{$frase}}
</p>



@php

    $dadosComOdontoComCopar = [];

    $dadosComOdontoSemCopar = [];

    $totalApartamento_com_copar = 0;

    $totalEnfermaria_com_copar = 0;

    $totalApartamento_sem_copar = 0;

    $totalEnfermaria_sem_copar = 0;

@endphp



@foreach($dados as $dado)

    @php

        $faixaEtaria = $dado->faixaEtaria->nome;

        $acomodacao = $dado->acomodacao_id;

        $valor = $dado->valor;

        $odonto = $dado->odonto;

        $coparticipacao = $dado->coparticipacao;

        $quantidade = $dado->quantidade;

        // Verifica se tem coparticipação

        $index = ($coparticipacao == 1) ? 'com_copar' : 'sem_copar';

        if (!isset($dadosComOdonto[$faixaEtaria])) {

            $dadosComOdonto[$faixaEtaria] = [

                'faixa_etaria_id' => $faixaEtaria,

                'apartamento_com_copar' => 0,

                'enfermaria_com_copar' => 0,

                'apartamento_sem_copar' => 0,

                'enfermaria_sem_copar' => 0,

                'quantidade' => $quantidade

            ];

        }

        $dadosComOdonto[$faixaEtaria]["{$acomodacao}_{$index}"] = $valor ?? 0;

    @endphp

@endforeach



@php

    // Calcular quantidade de blocos ativos

    $totalBlocos = 1; // Bloco Faixa Etária sempre existe

    if($com_coparticipacao == 1) $totalBlocos++;

    if($sem_coparticipacao == 1) $totalBlocos++;



    // Calcular colunas de acomodação ativas
    $colunas_acomodacao = $mostrar_apartamento + $mostrar_enfermaria;

    // Definir larguras baseado no número de blocos

    $widths = [

        1 => '50%',

        2 => '38%',

        3 => '38%'

    ];



    // Ajustar largura dos blocos quando só há 1 coluna de acomodação
    if ($colunas_acomodacao <= 1) {
        $widths = [
            1 => '35%',
            2 => '30%',
            3 => '30%'
        ];
    }

    $margins = [

        1 => '0 auto',

        2 => '0 1%',

        3 => '0 1%'

    ];

    $marginLeft = ($totalBlocos <= 2) ? (($colunas_acomodacao <= 1) ? '27%' : '20%') : (($colunas_acomodacao <= 1) ? '9%' : '0%');

@endphp







<div class="container">

    <table class="bloco-container" align="center" cellpadding="0" cellspacing="0">

        <tr>

            <!-- Bloco 1 - Faixa Etária -->

            <td class="bloco" style="width: 22%;margin-left:{{$marginLeft}};">

                <table width="100%">

                    <tr>

                        <td class="header-orange" style="text-align:center;">{{ $apelido_plano ?? 'NOSSO PLANO' }}</td>

                    </tr>

                    <tr>

                        <td class="subheader-blue">FAIXA ETÁRIA</td>

                    </tr>



                    @foreach($dadosComOdonto as $faixaEtaria => $valores)
                        @for($i=0;$i<$valores['quantidade'];$i++)
                            <tr>

                                <td>

                                    <div class="valor-copart">

                                        {{ $faixaEtaria }}

                                    </div>

                                </td>

                            </tr>
                        @endfor
                    @endforeach








                    <tr>
                        <td>
                            <div class="valor-copart-laranja">TOTAL</div>
                        </td>

                    </tr>






                </table>

            </td>

            @if($com_coparticipacao == 1)

                <!-- Bloco 2 - Com Coparticipação -->

                <td class="bloco" style="width: {{ $widths[$totalBlocos] }}; margin: {{ $margins[$totalBlocos] }};">

                    <table width="100%">

                        <tr>

                            <td colspan="{{ $mostrar_enfermaria + $mostrar_apartamento }}" class="header-orange" style="text-align:center;">{{ $rotulo_com_copart ?? 'COM COPARTICIPAÇÃO' }}</td>

                        </tr>

                        <tr>

                            @if($mostrar_enfermaria)<td class="coluna-azul">

                                ENFER

                                @if(!empty($codigo) && isset($codigo['coparticipacao_enfermaria']))
                                    {{ $codigo['coparticipacao_enfermaria'] }}
                                @endif

                            </td>@endif

                            @if($mostrar_apartamento)<td class="coluna-azul">

                                APART

                                @if(!empty($codigo) && isset($codigo['coparticipacao_apartamento']))
                                    {{ $codigo['coparticipacao_apartamento'] }}
                                @endif

                            </td>@endif

                        </tr>

                        @foreach($dadosComOdonto as $faixaEtaria => $valores)
                            @for($i=0;$i<$valores['quantidade'];$i++)
                                <tr>

                                    @if($mostrar_enfermaria)<td>

                                        <div class="valor-copart">

                                            @php

                                                $totalEnfermaria_com_copar += $valores['2_com_copar'];

                                            @endphp

                                            {{ number_format($valores['2_com_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>@endif

                                    @if($mostrar_apartamento)<td>

                                        <div class="valor-copart">

                                            @php

                                                $totalApartamento_com_copar += $valores['1_com_copar'];

                                            @endphp

                                            {{ number_format($valores['1_com_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>@endif

                                </tr>
                            @endfor
                        @endforeach

                        <tfoot>
                        <tr>
                            @if($mostrar_enfermaria)<td>
                                <div class="valor-copart-laranja">
                                    {{number_format($totalEnfermaria_com_copar,2,",",".")}}
                                </div>
                            </td>@endif
                            @if($mostrar_apartamento)<td>
                                <div class="valor-copart-laranja">
                                    {{number_format($totalApartamento_com_copar,2,",",".")}}
                                </div>
                            </td>@endif
                        </tr>
                        </tfoot>

                    </table>



                </td>

            @endif



            @if($sem_coparticipacao == 1)

                <!-- Bloco 3 - Sem Coparticipação -->

                <td class="bloco" style="width: {{ $widths[$totalBlocos] }};{{$totalBlocos <= 2 ? 'margin-left:1%;' : 'margin-left:0%;'}} ">

                    <table width="100%">

                        <tr>

                            <td colspan="{{ $mostrar_enfermaria + $mostrar_apartamento }}" class="header-orange-parcial" style="text-align:center;">
                                {{ $rotulo_copart_parcial ?? 'COM COPART PARCIAL *' }}
                                

                            </td>

                        </tr>

                        <tr>

                            @if($mostrar_enfermaria)<td class="coluna-azul">

                                ENFER

                                @if(!empty($codigo) && isset($codigo['parcial_enfermaria']))
                                    {{ $codigo['parcial_enfermaria'] }}
                                @endif

                            </td>@endif

                            @if($mostrar_apartamento)<td class="coluna-azul">

                                APART

                                @if(!empty($codigo) && isset($codigo['parcial_apartamento']))
                                    {{ $codigo['parcial_apartamento'] }}
                                @endif

                            </td>@endif

                        </tr>



                        @foreach($dadosComOdonto as $faixaEtaria => $valores)
                            @for($i=0;$i<$valores['quantidade'];$i++)
                                <tr >

                                    @if($mostrar_enfermaria)<td>

                                        <div class="valor-copart-parcial">

                                            @php

                                                $totalEnfermaria_sem_copar += $valores['2_sem_copar'];

                                            @endphp

                                            {{ number_format($valores['2_sem_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>@endif

                                    @if($mostrar_apartamento)<td>

                                        <div class="valor-copart-parcial">

                                            @php

                                                $totalApartamento_sem_copar += $valores['1_sem_copar'];

                                            @endphp

                                            {{ number_format($valores['1_sem_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>@endif

                                </tr>
                            @endfor
                        @endforeach

                        <tfoot>

                        <tr>
                            @if($mostrar_enfermaria)<td>
                                <div class="valor-copart-laranja-parcial">
                                    {{number_format($totalEnfermaria_sem_copar,2,",",".")}}
                                </div>
                            </td>@endif
                            @if($mostrar_apartamento)<td>
                                <div class="valor-copart-laranja-parcial">
                                    {{number_format($totalApartamento_sem_copar,2,",",".")}}
                                </div>
                            </td>@endif
                        </tr>


                        </tfoot>

                    </table>

        <tr>

        </tr>

        </td>

        @endif

        </tr>

    </table>

    @if($desconto == 1)
        <table class="bloco-container-desconto" style="margin-top:10px;" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="bloco" style="width: 22%;margin-left:{{$marginLeft}};">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <td>
                            <div class="valor-copart" style="border-radius:50px;font-size: 1.4em;">
                                {{ $texto_desconto }}
                            </div>

                        </td>
                    </table>

                </td>


                @if($com_coparticipacao == 1)
                    <td class="bloco" style="width: {{ $widths[$totalBlocos] }}; {{$totalBlocos <= 2 ? 'margin-left:1%;' : 'margin-left:1%;'}}">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                @if($mostrar_enfermaria)<td>
                                    <div class="valor-copart" style="border-radius:50px;font-size: 1.4em;">
                                        {{ number_format($totalEnfermaria_com_copar * (1 - ($valor_desconto / 100)), 2, ",", ".") }}
                                    </div>
                                </td>@endif
                                @if($mostrar_apartamento)<td>
                                    <div class="valor-copart" style="border-radius:50px;font-size: 1.4em;">
                                        {{ number_format($totalApartamento_com_copar * (1 - ($valor_desconto / 100)), 2, ",", ".") }}
                                    </div>
                                </td>@endif
                            </tr>
                        </table>
                    </td>
                @endif
                @if($sem_coparticipacao == 1)
                    <td class="bloco" style="width: {{ $widths[$totalBlocos] }}; {{$totalBlocos <= 2 ? 'margin-left:1%;' : 'margin-left:1%;'}}">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                @if($mostrar_enfermaria)<td>
                                    <div class="valor-copart-desconto-parcial" style="border-radius:50px;font-size: 1.4em;">
                                        {{ number_format($totalEnfermaria_sem_copar * (1 - ($valor_desconto / 100)), 2, ",", ".") }}
                                    </div>
                                </td>@endif
                                @if($mostrar_apartamento)<td>
                                    <div class="valor-copart-desconto-parcial" style="border-radius:50px;font-size: 1.4em;">
                                        {{ number_format($totalApartamento_sem_copar * (1 - ($valor_desconto / 100)), 2, ",", ".") }}
                                    </div>
                                </td>@endif
                            </tr>
                        </table>
                    </td>
                @endif




            </tr>

        </table>

    @endif

    @php

        if($linhas <= 5) {

            echo '<div style="margin-bottom:50px"></div>';

        } else {

            echo '<div style="margin-bottom:30px"></div>';

        }

    @endphp



    <div style="width: 100%; margin-top: 1px;padding:0;">



        <div style="{{($com_coparticipacao == 1 || $sem_coparticipacao == 1) && $carencia == 1 && $quantidade_carencia >= 1 ? 'width: 50%;float:left' : 'width:50%;float:left;margin-left:490px;' }}">
            <!-- Primeiro Bloco -->

            <div>

                <div class="lista-coparticipacao" style="margin: 0 0 0 50px; padding: 0; line-height: 1;">

                    @if(isset($pdf->linha03) && !$apenas_valores)

                        <p style="font-size:1.5em;margin:0;padding:0;font-weight:bold;color:white;">** {{$pdf->linha03}}</p>

                    @endif

                </div>

            </div>



            <!-- Segundo Bloco -->
            @if($quantidade_copar == 1 && $com_coparticipacao == 1)
                <div class="" style="margin-top:50px;margin-left:50px;">
                    <div class="{{$status_excecao == 0 ? 'procedimentos-container' : 'procedimentos-container-excecao'}}"


                    >
                        @if(($com_coparticipacao == 1) && !empty($pdf->consultas_eletivas_total) && $status_excecao == 0)





                            <div class="procedimento-left">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:30px;">Procedimentos</span>

                                <div style="background-color:white;border-radius:45px;">

                                    <div class="linha-procedimento">

                                        <span>Consultas Eletivas</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Consultas Urgência</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Exames Simples</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Exames Complexos</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Terapias Especiais</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Demais Terapias</span>

                                    </div>

                                    @if($pdf->internacoes_total && $pdf->internacoes_total != 'Isento')

                                        <div class="linha-procedimento">

                                            <span>Internações</span>

                                        </div>

                                    @endif

                                    @if($pdf->cirurgia_total && $pdf->cirurgia_total != 'Isento')

                                        <div class="linha-procedimento">

                                            <span>Cirurgias</span>

                                        </div>

                                    @endif

                                </div>

                            </div>



                            <div class="procedimento-right">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:25px;">Copart Total</span>



                                <div style="background-color:white;border-radius:45px;">
                                    @if($pdf->consultas_eletivas_total)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->consultas_eletivas_total}}</span>

                                        </div>

                                    @endif

                                    @if($pdf->consultas_de_urgencia_total)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->consultas_de_urgencia_total}}</span>

                                        </div>

                                    @endif

                                    @if($pdf->exames_simples_total)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->exames_simples_total}}</span>

                                        </div>
                                    @endif


                                    @if($pdf->exames_complexos_total)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->exames_complexos_total}}</span>

                                        </div>
                                    @endif

                                    @if($pdf->terapias_especiais_total)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->terapias_especiais_total}}</span>

                                        </div>
                                    @endif

                                    @if($pdf->demais_terapias_total)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->demais_terapias_total}}</span>

                                        </div>
                                    @endif


                                    @if($pdf->internacoes_total && $pdf->internacoes_total != 'Isento')
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->internacoes_total}}</span>

                                        </div>
                                    @endif

                                    @if($pdf->cirurgia_total && $pdf->cirurgia_total != 'Isento')
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->cirurgia_total}}</span>

                                        </div>
                                    @endif

                                </div>



                            </div>

                        @elseif(($sem_coparticipacao == 1 && $com_coparticipacao == 0) && !empty($pdf->consultas_eletivas_total) && $status_excecao == 0)
                            <div class="procedimento-left">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:30px;">Procedimentos</span>

                                <div style="background-color:white;border-radius:45px;">

                                    <div class="linha-procedimento">

                                        <span>Terapias Especiais</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Demais Terapias</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Internações</span>

                                    </div>

                                    @if($pdf->cirurgia_total && $pdf->cirurgia_total != 'Isento')

                                        <div class="linha-procedimento">

                                            <span>Cirurgia</span>

                                        </div>

                                    @endif



                                </div>

                            </div>



                            <div class="procedimento-right">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:25px;">Valor</span>



                                <div style="background-color:white;border-radius:45px;">


                                    <div class="linha-procedimento">

                                        <span>{{explode("-",$linha_01)[1]}}</span>

                                    </div>





                                    <div class="linha-procedimento">

                                        <span>{{explode("-",$linha_02)[1]}}</span>

                                    </div>




                                    <div class="linha-procedimento">

                                        <span>Isento</span>

                                    </div>




                                    <div class="linha-procedimento">

                                        <span>Isento</span>

                                    </div>




                                </div>



                            </div>









                            <!----Exceção-------->
                        @else

                            <div class="procedimento-left">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:30px;">Procedimentos</span>

                                <div style="background-color:white;border-radius:45px;">

                                    <div class="linha-procedimento">

                                        <span>Consultas Eletivas</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Pronto Atendimento</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Faixa 1</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Faixa 2</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Faixa 3</span>

                                    </div>

                                    <div class="linha-procedimento">

                                        <span>Faixa 4</span>

                                    </div>



                                </div>

                            </div>



                            <div class="procedimento-right">

                                <span style="display:block;font-size:1.2em;color: white;font-weight:bold;margin-bottom:15px;margin-left:25px;">Copart Total</span>



                                <div style="background-color:white;border-radius:45px;">
                                    @if($pdf->consultas_eletivas_total)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->consultas_eletivas_total}}</span>

                                        </div>

                                    @endif

                                    @if($pdf->pronto_atendimento)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->pronto_atendimento}}</span>

                                        </div>

                                    @endif

                                    @if($pdf->faixa_1)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->faixa_1}}</span>

                                        </div>
                                    @endif


                                    @if($pdf->faixa_2)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->faixa_2}}</span>

                                        </div>
                                    @endif

                                    @if($pdf->faixa_3)
                                        <div class="linha-procedimento">

                                            <span>{{$pdf->faixa_3}}</span>

                                        </div>
                                    @endif

                                    @if($pdf->faixa_4)

                                        <div class="linha-procedimento">

                                            <span>{{$pdf->faixa_4}}</span>

                                        </div>
                                    @endif




                                </div>



                            </div>

                            <!----Fim Exceção----->




                        @endif

                        <div style="clear:both;height:0;font-size:0;"></div>
                    </div>

                </div>

            @endif            @if(isset($pdf->linha01) && !$apenas_valores && $sem_coparticipacao == 1)
                <div style="margin-top:15px;margin-left:50px;">
                    <div style="width:73%;border-radius:35px;overflow:hidden;">
                        <div style="background:rgb(254,199,72);color:rgb(8,73,189);padding:10px 16px;font-weight:bold;font-size:1.2em;line-height:1.2;">{{$pdf->linha01}}</div>
                        @if(!empty($linha_01))
                            @php $parts = explode(' - ', $linha_01, 2); $lbl = $parts[0]; $val = count($parts) > 1 ? $parts[1] : ''; @endphp
                            <table style="width:100%;border-collapse:collapse;margin:0;background:white;"><tr>
                                    <td style="padding:8px 14px;color:rgb(8,73,189);font-weight:bold;font-size:1.1em;line-height:1.2;">{{$lbl}}</td>
                                    @if($val)<td style="padding:8px 14px;color:rgb(8,73,189);font-weight:bold;font-size:1.1em;line-height:1.2;white-space:nowrap;text-align:right;">{{$val}}</td>@endif
                                </tr></table>
                        @endif
                        @if(!empty($linha_02))
                            @php $parts2 = explode(' - ', $linha_02, 2); $lbl2 = $parts2[0]; $val2 = count($parts2) > 1 ? $parts2[1] : ''; @endphp
                            <table style="width:100%;border-collapse:collapse;margin:0;background:white;"><tr>
                                    <td style="padding:8px 14px;color:rgb(8,73,189);font-weight:bold;font-size:1.1em;line-height:1.2;">{{$lbl2}}</td>
                                    @if($val2)<td style="padding:8px 14px;color:rgb(8,73,189);font-weight:bold;font-size:1.1em;line-height:1.2;white-space:nowrap;text-align:right;">{{$val2}}</td>@endif
                                </tr></table>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        @if(($com_coparticipacao == 1 || $sem_coparticipacao == 1) && $carencia == 1 && $quantidade_carencia >= 1))

        <div style="width: 40%;float:right;">

            <div style="margin-left:30px;">

                <h3 style="color: white; font-size: 1.5em; margin: 0 0 10px 0;">CARÊNCIAS DE SAÚDE</h3>



                <!-- 1º Bloco -->

                <div style="padding-bottom: 10px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[0]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[0]->detalhe}}</span>

                                </div>

                            </td>

                            <td style="vertical-align: middle;">

                                    <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">

                                        Urgência, Emergência e<br>

                                        Acidentes Pessoais

                                    </span>

                            </td>

                        </tr>

                    </table>

                </div>



                <!-- 2º Bloco -->

                <div style="padding-bottom: 10px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[1]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[1]->detalhe}}</span>

                                </div>

                            </td>

                            <td style="vertical-align: middle;">

                                    <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">

                                        Consultas Médicas,<br>

                                        Exames Médicos Simples

                                    </span>

                            </td>

                        </tr>

                    </table>

                </div>



                <!-- 3º Bloco -->

                <div style="padding-bottom: 10px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[2]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[2]->detalhe}}</span>

                                </div>

                            </td>

                            <td>

                                    <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">

                                        Exames Cardiol., Exames Imagem<br>

                                        Oftalmol., Otorrino Simples,<br>

                                        Ultrassonografia

                                    </span>

                            </td>

                        </tr>

                    </table>

                </div>



                <!-- 4º Bloco -->

                <div style="padding-bottom: 10px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[3]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[3]->detalhe}}</span>

                                </div>

                            </td>

                            <td>

                                    <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">

                                        Cirurgias, Internações, Exames<br>

                                        Alto Custo, Trat. Psicológico,<br>

                                        Terapia Ocupacional, Fisioterapia<br>

                                    </span>

                            </td>

                        </tr>

                    </table>

                </div>



                <!-- 5º Bloco -->

                <div style="padding-bottom: 10px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[4]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[4]->detalhe}}</span>

                                </div>

                            </td>

                            <td style="vertical-align: middle;">

                                <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">Parto</span>

                            </td>

                        </tr>

                    </table>

                </div>



                <!-- 6º Bloco -->

                <div style="margin-bottom: 5px;">

                    <table>

                        <tr>

                            <td style="vertical-align: top; padding-right: 10px;">

                                <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                    <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[5]->tempo}}</span><br>

                                    <span style="font-size: 1.2em;">{{$carencia_texto[5]->detalhe}}</span>

                                </div>

                            </td>

                            <td style="vertical-align: middle;">

                                    <span style="font-size: 1.3em; color: white;text-align:left;display: block;font-weight: bold;">

                                        Doenças e Lesões<br>

                                        Pré-Existentes

                                    </span>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

        @endif

        @if($com_coparticipacao == 0 && $sem_coparticipacao == 0 && $carencia == 1 && $quantidade_carencia >= 1)

            <div style="width:100%;display:block;padding:0;clear:both;margin:0;">

                <div style="width:70%;margin:0 auto;height:100%;padding:0px;">
                    <div style="width:50%;float:left;">



                        <table style="width:100%; border-collapse: collapse; border-spacing: 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[0]->tempo}}</span><br>

                                        <span style="font-size: 1.2em;">{{$carencia_texto[0]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">

                                        Urgência, Emergência e<br>

                                        Acidentes Pessoais

                                    </span>

                                </td>

                            </tr>

                        </table>



                        <table style="width:100%; border-collapse: collapse; border-spacing: 0;margin:20px 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[1]->tempo}}</span><br>

                                        <span style="font-size: 1.2em;">{{$carencia_texto[1]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">

                                        Consultas Médicas,<br>

                                        Exames Médicos Simples

                                    </span>

                                </td>

                            </tr>

                        </table>





                        <table style="width:100%;border-collapse: collapse; border-spacing: 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[2]->tempo}}</span><br>

                                        <span style="font-size: 1.2em;">{{$carencia_texto[2]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">

                                        Doenças e Lesões<br>

                                        Pré-Existentes

                                    </span>

                                </td>

                            </tr>

                        </table>















                    </div>



                    <div style="width:50%;float:right;">



                        <table style="width:100%; border-collapse: collapse; border-spacing: 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[3]->tempo}}</span><br>

                                        <span style="font-size: 1.2em;">{{$carencia_texto[3]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">

                                        Cirurgias, Internações, Exames<br>

                                        Alto Custo, Trat. Psicológico,<br>

                                        Terapia Ocupacional, Fisioterapia<br>

                                    </span>

                                </td>

                            </tr>

                        </table>



                        <table style="width:100%; border-collapse: collapse; border-spacing: 0;margin:20px 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[4]->tempo}}</span><br>

                                        <span style="font-size: 1.2em;">{{$carencia_texto[4]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">Parto</span>

                                </td>

                            </tr>

                        </table>





                        <table style="width:100%; border-collapse: collapse; border-spacing: 0;">

                            <tr>

                                <td style="vertical-align: top; padding:0; width:1%;">

                                    <div style="border: 8px solid transparent;background-color:white;border-radius:10%;color:blue; width: 80px; text-align: center; line-height: 1; padding: 12px;height:80px;">

                                        <span style="font-size: 1.4em; font-weight: bold;">{{$carencia_texto[5]->tempo}}</span><br>
                                        <span style="font-size: 1.2em;">{{$carencia_texto[5]->detalhe}}</span>

                                    </div>

                                </td>

                                <td style="padding:0 0 0 10px; vertical-align: middle; white-space: nowrap;">

                                    <span style="font-size: 1.3em; color: yellow; font-weight: bold;">

                                        Exames Cardiol.,Exames Imagem<br>

                                        Oftalmol., Otorrino Simples,<br>

                                        Ultrassonografia

                                    </span>

                                </td>

                            </tr>

                        </table>







                    </div>







                </div>



            </div>

        @endif

    </div>
    {{--    //Aqui e o fim--}}






    <!-- Nova Tabela -->














</div>








<div class="footer">

    <div class="middle">

        <p style="position:relative;">{{$nome}}</p>

        <p style="position:relative;">

            <span>{{$celular}}</span>

            <img src="whatsapp.png" alt="whatsapp" style="width:50px;height:50px;position:relative;top:7px;" />

        </p>

    </div>

    @if($image != "")

        <img src='{{$image}}' alt=''>

    @endif







</div>















</body>

</html>



