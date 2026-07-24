<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @page {
            margin: 0;
        }



        @font-face {font-family: 'Roboto';src: url('{{ public_path("fonts/Roboto-Regular.ttf") }}') format('truetype');font-weight: normal;font-style: normal;}
        @font-face {font-family: 'Roboto';src: url('{{ public_path("fonts/Roboto-Bold.ttf") }}') format('truetype');font-weight: bold;font-style: normal;}
        html, body {width: 100%;height: 100%;margin: 0;padding: 0;font-family: 'Roboto', sans-serif !important;background-color:#0849bd;}
        tr {line-height: 1;vertical-align: top;}
        .container {position: absolute;top: 380px;left: 50%;transform: translateX(-50%);width: 97%;padding-bottom: 0 !important;margin-bottom: -10px;}
        .faixa-etaria {text-align: center;font-size: 1.5em;background-color: rgb(5,53,95);color:#FFF;font-weight:bold;}

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

            bottom:10px;

            left: 0px;

            width: 390px;

            height: 390px;

            border-radius: 50%;

        }



        .footer .middle, .footer .right {

            position: absolute;

            bottom:0px;





        }



        .footer .middle {

            bottom:30px;

            right: 10px;

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

            color:rgb(8,73,189);

            font-weight: bold;

        }




        .cidade_container {

            position:absolute;

            top:190px;

            left:42%;

            font-weight: bold;

            font-size: 2em;

            color:rgb(12,77,193);

        }

        .frase_container {

            position:absolute;

            top:260px;

            left:0%;

            font-weight: bold;

            font-size: 1.5em;

            color:rgb(12,77,193);

            text-align: center;

        }




        .bloco-container {width: 100%;margin: 0 auto;border-spacing: 0px;border-collapse: separate;}
        .container table {border-collapse: separate;border-spacing: 0;margin-bottom: -5px;}
        .bloco {background: white;border-radius: 60px;vertical-align: top;padding: 15px;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .header-orange-parcial {background: #F88058;color: white;padding: 20px;border-radius: 55px 55px 0 0;font-weight: bold;font-size: 1.6em;}
        .header-orange {background: #F88058;color: white;padding: 20px;border-radius: 55px 55px 0 0;font-weight: bold;font-size: 1.6em;}

        .header-orange-blue {
            background: rgb(12,77,193);
            color: white;
            padding: 20px;
            border-radius: 55px 55px 0 0;
            font-weight: bold;
            font-size: 1.6em;
        }

        .header-orange-totais {
            background: rgb(65,123,231);
            color: white;
            padding: 20px;
            border-radius: 55px 55px 0 0;
            font-weight: bold;
            font-size: 1.6em;
        }

        .valor-copart-blue {
            background: rgb(65,123,231);
            padding: 12px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: white;

            display: block;
            font-size:1.4em;
            text-align:center;

        }




        .subheader-blue {background: white;color: white;padding: 8px;font-size: 1.3em;color: rgb(8,73,189);font-weight:bold;text-align:center;}
        .coluna-azul {background: white;font-weight:bold;padding: 8px;width: 50%;font-size: 1.5em;color: rgb(8,73,189);text-align:center;}
        .valor-copart-parcial {background: rgb(255,248,124);padding: 12px !important;margin: 5px 3px;border-radius: 8px;color: rgb(8,73,189);font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .valor-copart {background: rgb(254,199,72);padding:12px !important;margin: 5px 3px;border-radius:8px;color:rgb(8,73,189);font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .valor-copart-col {background: rgb(254,199,72);padding:8px !important;margin: 0px 3px;border-radius:8px;color:rgb(8,73,189);display: block;font-size:1.4em;text-align:center;}
        .valor-copart-col-totais {background: rgb(8,73,189);padding:8px !important;margin: 0px 3px;border-radius:8px;color:white;display: block;font-size:1.4em;text-align:center;}
        .valor-copart-col-totais-foot {background: rgb(8,73,189);padding:8px !important;margin: 0px 3px;border-radius:8px;color:white;font-weight: bold;display: block;font-size:1.6em;text-align:center;}

        .valor-copart-laranja-parcial {background:  rgb(65,123,231);padding: 8px !important;margin: 5px 3px;border-radius: 8px;color: white;font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .valor-copart-laranja {background: #F88058;padding: 12px !important;margin: 5px 3px;border-radius: 0 0 55px 55px;color: white;font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .lista-coparticipacao {color: white;font-size: 1.2em;margin:0;}
        .lista-coparticipacao strong {display: block;margin: 10px 0;font-size:1.5em;}
        .procedimentos-container {width: 90%;margin-top:1px;overflow: hidden;background-color: white;border-radius: 55px;padding: 12px;}
        .procedimentos-container-excecao {width: 70%;overflow: hidden;background-color: white;border-radius: 55px;padding: 12px;}
        .procedimento-left {float: left;width: 60%;vertical-align: top;}
        .procedimento-right {float: right;width: 35%;vertical-align: top;}
        .linha-procedimento {margin: 0;padding:0;}
        .linha-procedimento span {font-size:1.3em;color: white;display:block;font-weight:bold;margin:0px;padding:0 0 0 30px;}
        .bloco-desconto {display: inline-table;background: white;border-radius: 55px;vertical-align: top;padding: 1px;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .valor-copart-desconto-parcial {background: rgb(255,248,124);padding: 8px !important;margin: 5px 3px;border-radius: 8px;color: rgb(8,73,189);font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .valor-copart-desconto {background: rgb(254,199,72);padding: 8px !important;margin: 5px 3px;border-radius: 8px;color: rgb(8,73,189);font-weight: bold;display: block;font-size:1.6em;text-align:center;}
        .valor-procedimento {background: #FFF3CD;border-radius: 20px;display: inline-block;margin-left: 10px;}
        #valores_odonto {position: relative;z-index: 2;background-color: white !important;width: 95%;margin: 20px auto 0;padding: 15px;border-radius: 15px;box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        #valores_odonto td {padding: 10px !important;text-align: center !important;}
        #valores_odonto span[style*="border: 4px solid"] {background: #FFF3CD;border-color: #366EBF !important;padding: 8px 15px !important;font-size: 1em !important;}
        td {font-size:1.1em;}
    </style>
</head>
<body>

<img style="position: absolute;top: 0;left: 0;height: 100%;width: 100%;object-fit: cover;" src="layouts/modelo3.png" alt="Orçamento">

<p class="cidade_container" style="text-transform:uppercase;width:100%">{{ $cidade }}</p>
<p class="frase_container" style="text-transform:uppercase;width:100%;">{{ $label }}</p>

@php
    $com_coparticipacao = 1;
    $sem_coparticipacao = 1;
    $dadosComOdontoComCopar = [];
    $dadosComOdontoSemCopar = [];
    $dadosComOdonto = [];
    $total_apartamento = 0;
    $total_enfermaria = 0;
@endphp



@php
    $totalBlocos = 1;
    if($com_coparticipacao == 1) $totalBlocos++;
    if($sem_coparticipacao == 1) $totalBlocos++;
    $widths = [1 => '50%',2 => '38%',3 => '38%'];
    $margins = [1 => '0 auto',2 => '0 1%',3 => '0 1%'];

    $faixasEtariasMap = [
        'Faixa 1' => '0 a 18',
        'Faixa 2' => '19 a 23',
        'Faixa 3' => '24 a 28',
        'Faixa 4' => '29 a 33',
        'Faixa 5' => '34 a 38',
        'Faixa 6' => '39 a 43',
        'Faixa 7' => '44 a 48',
        'Faixa 8' => '49 a 53',
        'Faixa 9' => '54 a 58',
        'Faixa 10' => 'Acima de 59',
    ];


    foreach($dadosTabela as $row) {

        $faixa = match($row['faixa_etaria']) {
            'Faixa 1' => '0 a 18',
            'Faixa 2' => '19 a 23',
            'Faixa 3' => '24 a 28',
            'Faixa 4' => '29 a 33',
            'Faixa 5' => '34 a 38',
            'Faixa 6' => '39 a 43',
            'Faixa 7' => '44 a 48',
            'Faixa 8' => '49 a 53',
            'Faixa 9' => '54 a 58',
            'Faixa 10' => 'Acima 59+',
            default => '',
        };

        /* Quantidades (QTE) */
        $quantidades[] = $row['quantidade'];

        /* Valores Unitários */
        $unitarios[] = [
            'apartamento' => $row['valor_apartamento'],
            'enfermaria' => $row['valor_enfermaria'],
        ];

        $totais[] = [
           'apartamento' => $row['total_apartamento'],
           'enfermaria' => $row['total_enfermaria'],
        ];

    }

@endphp

<div class="container">
    <table class="bloco-container" align="center" cellpadding="0" cellspacing="0">

        <tr>
            <td class="bloco" style="width: 18%;">
                <table width="100%">
                    <tr>
                        <td class="header-orange-blue" style="text-align:center;">IDADE</td>
                    </tr>
                    <tr>
                        <td class="subheader-blue">FAIXA ETÁRIA</td>
                    </tr>
                    @foreach($dadosTabela as $f)
                        @php
                            // Substituímos a faixa pela sua descrição correspondente no mapeamento
                            $intervalo = isset($faixasEtariasMap[$f['faixa_etaria']]) ? $faixasEtariasMap[$f['faixa_etaria']] : $f['faixa_etaria'];
                        @endphp
                        <tr>
                            <td>
                                <div class="valor-copart-blue">
                                    {{ $intervalo }}
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </td>

            <td class="bloco" style="width: 15%;margin:0 20px;">
                <table width="100%">
                    <tr>
                        <td class="header-orange-blue" style="text-align:center;">TOTAL</td>
                    </tr>
                    <tr>
                        <td class="subheader-blue">QUANTIDADE</td>
                    </tr>
                    @foreach($dadosTabela as $q)

                        <tr>
                            <td>
                                <div class="valor-copart-blue">
                                    {{ $q['quantidade'] }}
                                </div>
                            </td>
                        </tr>

                    @endforeach

                </table>
            </td>

            <td class="bloco" style="width: 30%;margin-right: 20px;">
                <table width="100%">
                    <tr>
                        <td class="header-orange" style="text-align:center;" colspan="2">Preços Unitários</td>
                    </tr>
                    <!-- Linha para cabeçalhos "Apart" e "Enfer" -->
                    <tr>
                        <td class="subheader-blue" style="text-align: center; font-weight: bold;">APART</td>
                        <td class="subheader-blue" style="text-align: center; font-weight: bold;">ENFER</td>
                    </tr>
                    <!-- Iteração para Dados -->
                    @foreach($unitarios as $u)
                        <tr>
                            <!-- Dado para Apart -->
                            <td style="text-align: left; padding: 10px; color: rgb(8,73,189);">
                                <span class="valor-copart-col">{{ $u['apartamento'] }}</span>
                            </td>
                            <!-- Dado para Enfer -->
                            <td style="text-align: right;  padding: 10px; color: rgb(8,73,189);">

                                <span class="valor-copart-col">{{ $u['enfermaria'] }}</span>
                            </td>
                        </tr>
                    @endforeach





                </table>





            </td>

            <td class="bloco" style="width: 30%;">
                <table width="100%">
                    <tr>

                        <td class="header-orange-totais" style="text-align:center;" colspan="2">Preços Totais</td>
                    </tr>
                    <tr>
                        <td class="subheader-blue" style="text-align: center; font-weight: bold;">APART</td>
                        <td class="subheader-blue" style="text-align: center; font-weight: bold;">ENFER</td>
                    </tr>
                    @foreach($totais as $t => $tt)

                        <tr>
                            <!-- Dado para Apart -->
                            <td style="text-align: left; padding: 10px; color: rgb(8,73,189);">
                                <span class="valor-copart-col-totais">
                                    {{ $tt['apartamento'] }}
                                    @php
                                        $valor = str_replace(['.', ','], ['', '.'], $tt['apartamento']);
                                        $total_apartamento += (float) $valor;
                                    @endphp
                                </span>
                            </td>
                            <!-- Dado para Enfer -->
                            <td style="text-align: right;  padding: 10px; color: rgb(8,73,189);">
                                <span class="valor-copart-col-totais">
                                    {{ $tt['enfermaria'] }}
                                    @php
                                        $valor_e = str_replace(['.', ','], ['', '.'], $tt['enfermaria']);
                                        $total_enfermaria += (float) $valor_e;
                                    @endphp
                                </span>
                            </td>
                        </tr>
                    @endforeach





                </table>
            </td>
        </tr>


        <tr>
            <td colspan="3" style="padding:0px;">
                <div class="valor-copart-laranja-parcial">
            <span style="padding:3px;display: block;">
                Totais
            </span>
                </div>
            </td>

            <td class="bloco" style="padding:0;">
                <table width="100%" cellpadding="0" cellspacing="0" style="table-layout: fixed;">
                    <tr>
                        <td style="padding:3px;">
                    <span class="valor-copart-col-totais-foot" style="width: 83%;text-align: center;margin: 0 auto;">
                        {{ number_format($total_apartamento,2,",",".") }}
                    </span>
                        </td>
                        <td style="padding:3px;">
                    <span class="valor-copart-col-totais-foot" style="width: 83%;text-align: center;margin: 0 auto;">
                        {{ number_format($total_enfermaria,2,",",".") }}
                    </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


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
        <img src='{{$image}}' alt='ser Image'>
    @endif
</div>





</body>
</html>
