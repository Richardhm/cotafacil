<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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



        html, body {

            width: 100%;

            height: 100%;

            margin: 0;

            padding: 0;

            font-family: 'Roboto', sans-serif !important;

            background-color:#0849bd;

        }

        tr {

            line-height: 1;

            vertical-align: top;

        }





        .container {

            position: absolute;

            top: 580px;

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
        .footer {position: absolute;bottom: 0px;width: calc(100% - 40px);height: 200px;padding: 0px;box-sizing: border-box;}
        .footer img {position: absolute;bottom:15px;right: 0px;width: 400px;height: 400px;border-radius: 50%;}
        .footer .middle, .footer .right {position: absolute;bottom:0px;}
        .footer .middle {bottom:30px;left: 10px;display:block;color: #0c0c0c;font-size:1.7em;line-height:1;padding:0;}
        .footer .middle p {margin: 0px;color:#FFFFFF;font-weight: bold;line-height:1;padding:0;}
        .footer .right {bottom:20px;right: 30px;text-align: left;display:block;font-size:2em;}
        .footer .right p {margin: 0;color:#FFFFFF;font-weight: bold;}



        .cidade_container {position:absolute;top:190px;left:0%;font-weight: bold;font-size: 3em;color:white;text-align:center;}
        .cidade_container {position:absolute;top:190px;left:0%;font-weight: bold;font-size: 3em;color:white;text-align:center;}


        .frase_container {

            position:absolute;

            top:290px;

            left:0%;

            font-weight: bold;

            font-size: 2.5em;

            color:white;

            text-align: center;

        }









        /**********************/

        .bloco-container {

            width: 100%;

            margin: 0 auto;

            border-spacing: 20px; /* Espaço entre blocos */

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

            padding: 15px;

            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra suave */

        }

        .header-orange-parcial {
            background: #F88058;
            color: white;
            padding: 20px;
            border-radius: 55px 55px 0 0;
            font-weight: bold;
            font-size: 1.6em;
        }

        .header-orange {
            background: #F88058;
            color: white;
            padding: 20px;
            border-radius: 55px 55px 0 0;
            font-weight: bold;
            font-size: 1.6em;
        }

        .subheader-blue {
            background: white;
            color: white;
            padding: 8px;
            font-size: 1.5em;
            color: rgb(8,73,189);
            font-weight:bold;
            text-align:center;
        }






        .coluna-azul {

            background: white;

            font-weight:bold;

            padding: 8px;

            width: 50%;

            font-size: 1.5em;

            color: rgb(8,73,189);

            text-align:center;



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

        .valor-copart {background: rgb(254,199,72);padding:12px !important;margin: 5px 3px;border-radius:8px;color:rgb(8,73,189);font-weight: bold;
            display: block;font-size:1.6em;text-align:center;}

        .valor-copart-laranja-parcial {
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

            margin-top:1px;

            overflow: hidden; /* Contém os floats */
            background-color: white;
            border-radius: 55px;
            padding: 12px;

            /*min-height: 470px;*/

        }

        .procedimentos-container-excecao {

            width: 70%;



            overflow: hidden; /* Contém os floats */

            background-color: white;

            border-radius: 55px;

            padding: 12px;

            /*min-height: 380px;*/

        }



        .procedimento-left {

            float: left;

            width: 60%;

            vertical-align: top;

        }



        .procedimento-right {

            float: right;

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

        .bloco-desconto {
            display: inline-table;
            background: white;
            border-radius: 55px;
            vertical-align: top;
            padding: 1px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra suave */
        }

        .valor-copart-desconto-parcial {
            background: rgb(255,248,124);
            padding: 8px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: rgb(8,73,189);
            font-weight: bold;
            display: block;
            font-size:1.6em;
            text-align:center;
        }


        .valor-copart-desconto {
            background: rgb(254,199,72);
            padding: 8px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: rgb(8,73,189);
            font-weight: bold;
            display: block;
            font-size:1.6em;
            text-align:center;
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

        td {
            font-size:1.1em;
        }


    </style>
</head>
<body>
<img src="layouts/modelo2.png" style="position:absolute;top:0;left:0;object-fit: cover;width:100%" />
<p class="cidade_container" style="text-transform:uppercase;width:100%">Goiania</p>
<p class="frase_container" style="text-transform:uppercase;width:100%">{{ $frase }}</p>

@php
    $com_coparticipacao = 1;
    $sem_coparticipacao = 1;

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
        $acomodacao = 3;
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



    // Definir larguras baseado no número de blocos

    $widths = [

        1 => '23%',

        2 => '36%',

        3 => '39%'

    ];



    $margins = [

        1 => '0 auto',

        2 => '0 1%',

        3 => '0 1%'

    ];

@endphp


<div class="container">

    <table class="bloco-container" align="center" cellpadding="0" cellspacing="0">

        <tr>

            <!-- Bloco 1 - Faixa Etária -->

            <td class="bloco" style="width: 22%;">

                <table width="100%">
                    <tr>
                        <td class="header-orange" style="text-align:center;font-size:1.4em;">{{ $apelido_plano ?? 'NOSSO PLANO' }}</td>
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

                </table>

            </td>

            @if($com_coparticipacao == 1)

                <!-- Bloco 2 - Com Coparticipação -->

                <td class="bloco" style="width: 38%; margin: {{ $margins[$totalBlocos] }};">

                    <table width="100%">

                        <tr>

                            <td colspan="2" class="header-orange" style="text-align:center;font-size:1.4em;">


                                COM COPARTICIPAÇÃO

                            </td>

                        </tr>



                        @foreach($dadosComOdonto as $faixaEtaria => $valores)
                            @for($i=0;$i<$valores['quantidade'];$i++)
                                <tr>

                                    <td colspan="2">

                                        <div class="valor-copart">



                                            {{ number_format($valores['3_com_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>



                                </tr>
                            @endfor
                        @endforeach



                    </table>



                </td>

            @endif



            @if($sem_coparticipacao == 1)

                <!-- Bloco 3 - Sem Coparticipação -->

                <td class="bloco" style="width: 38%;{{$totalBlocos <= 2 ? 'margin-left:1%;' : 'margin-left:0%;'}} ">

                    <table width="100%">

                        <tr>
                            <td colspan="2" class="header-orange" style="text-align:center;font-size:1.4em;">


                                COM COPART PARCIAL *


                            </td>
                        </tr>

                        @foreach($dadosComOdonto as $faixaEtaria => $valores)
                            @for($i=0;$i<$valores['quantidade'];$i++)
                                <tr>

                                    <td colspan="2">

                                        <div class="valor-copart-parcial">



                                            {{ number_format($valores['3_sem_copar'], 2, ",", ".") }}

                                        </div>

                                    </td>



                                </tr>
                            @endfor
                        @endforeach



                    </table>



                </td>

            @endif

        </tr>

    </table>
























</div>



<div class="footer">
    @if($image != "")
        <img src='{{$image}}' alt=''>

    @endif
    <div class="middle">
        <p style="position:relative;">{{$nome}}</p>
        <p style="position:relative;">
            <span>{{$celular}}</span>
            <img src="whatsapp.png" alt="whatsapp" style="width:50px;height:50px;position:relative;top:7px;" />
        </p>
    </div>
</div>




</body>
</html>
