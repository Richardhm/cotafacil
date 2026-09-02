<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }


        .container {
            position: absolute;
            top: 395px;
            left: 50%;
            transform: translateX(-50%);
            width: 95%;
        }

        .tabela-container {
            overflow-y: auto;

        }

        table {
            width: 100%;
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 5px 5px;
        }

        td, th {
            padding: 5px;
            text-align: center;
            text-transform: capitalize;

        }


        th {
            background-color: rgb(5,53,95);
            color: white;
        }

        tfoot td {
            font-weight: bold;
            background-color: rgb(5,53,95);
            color: white;
        }

        tbody .tbody_com_copart {
            background-color:#366EBF;
            color:#FFF;
        }

        tbody .tbody_sem_copart {
            background-color:#F88058;
            color:#FFF;
        }

        tbody .tbody_faixa_etaria,
        thead .title {
            background-color: rgb(5,53,95);
            color: white;
        }
        thead .title {
            text-transform: capitalize;
        }

        .lembretes {
            padding: 0;
            text-align: left;


        }

        .lembretes p {
            margin: 2px 0;
            color:rgb(19,82,135);
            font-size: 0.8em;
        }

        /* Novos estilos */
        .faixa-etaria {
            text-align: center;
            font-size: 0.875em;
            background-color: rgb(5,53,95);
            color:#FFF;
        }

        .apart-enfer {
            text-align: center;
            font-size: 0.875em;
            color: rgb(5,53,95);
        }

        .apart {
            background-color: rgb(5,53,95);
            color:white;
        }

        .copart-parcial {
            background-color: rgb(255,89,33);
            color:white;
        }

        #valores_coparticipacao {

            width: 100%;

        }

        #container_coparticipacao {
            width: 50%;
            background-color: green;
            box-sizing: border-box;
            float:left;
            /*display: inline-block;*/

        }
        #container_carencias {
            width: 30%;
            background-color: purple;
            float:left;
            box-sizing: border-box;
            /*display: inline-block;*/
        }


        #valores_coparticipacao h3 {
            margin:15px 0 10px 0;
            width:100%;
            color:rgb(19,82,135);

        }



        #valores_coparticipacao > div {
            width:100%;
            box-sizing: border-box;
        }

        #valores_coparticipacao .section-title {
            background-color: rgb(19,82,135);
            font-size: 0.8em;
            color: #FFF;
            margin: 0;
            padding:7px 3px;

        }

        #valores_coparticipacao .section-content {
            background-color: rgb(230,231,233);
            margin: 0;
            padding: 0;
        }

        #valores_coparticipacao .section-content p {
            color: rgb(19,82,135);
            font-size: 0.8em;
            margin: 0 0 5px 0;
            padding: 0;
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
            bottom:0px;
            left: 20px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
        }

        .footer .middle, .footer .right {
            position: absolute;
            bottom:0px;
            color: #0c0c0c;
        }

        .footer .middle {
            bottom:20px;
            left: 250px;
            color: #0c0c0c;
        }

        .footer .middle p {
            margin: 0px;
            color:#FFFFFF;
            font-weight: bold;
        }

        .footer .right {
            bottom:20px;
            right: 30px;
            text-align: left;
        }

        .footer .right p {
            margin: 0;
            color:#FFFFFF;
            font-weight: bold;
        }

        .cidade_container {
            position:absolute;
            top:210px;
            left:42%;
            font-weight: bold;
            font-size: 1.7em;
            color:#366EBF;
        }

        .frase_container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -1210%);
            text-align: center;
            font-weight: bold;
            font-size: 1.5em;
            color: white;
            width:60%;border-radius:50%;margin:0 auto;background-color:#1a56db;color:white;text-align:center;padding:0;
        }

    </style>
</head>
<body>

<img style="position: absolute;top: 0;left: 0;height: 100%;width: 100%;object-fit: cover;" src="{{$layout->imagem}}" alt="">

<p class="cidade_container">{{$cidade}}</p>
<p class="frase_container">{{$frase}}</p>
<div class="container">
    <div class="tabela-container">
        <table>
            <thead>
                <tr>
                    <td class="text-center title">{{$administradora}}</td>
                    <td colspan="2" class="apart">Com Coparticipação</td>
                    <td colspan="2" class="copart-parcial">Coparticipação Parcial</td>
                </tr>
                <tr>
                    <td class="faixa-etaria">Faixa Etária</td>
                    <td class="apart-enfer apart">
                        Apart
                        @if($odonto_frase == " c/ Odonto" && $plano_nome == "Individual")
                            11820
                        @elseif($odonto_frase == " c/ Odonto" && $plano_nome == "Super Simples")
                            11170
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Individual")
                            11173
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Super Simples")
                            11170
                        @endif
                    </td>
                    <td class="apart-enfer apart">
                        Enfer
                        @if($odonto_frase == " c/ Odonto" && $plano_nome == "Individual")
                            11819
                        @elseif($odonto_frase == " c/ Odonto" && $plano_nome == "Super Simples")
                            11162
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Individual")
                            11165
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Super Simples")
                            11162
                        @endif


                    </td>
                    <td class="apart-enfer copart-parcial">
                        Apart
                        @if($odonto_frase == " c/ Odonto" && $plano_nome == "Individual")
                            21070
                        @elseif($odonto_frase == " c/ Odonto" && $plano_nome == "Super Simples")
                            21224
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Individual")
                            21071
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Super Simples")
                            21224
                        @endif

                    </td>
                    <td class="apart-enfer copart-parcial">
                        Enfer
                        @if($odonto_frase == " c/ Odonto" && $plano_nome == "Individual")
                            21068
                        @elseif($odonto_frase == " c/ Odonto" && $plano_nome == "Super Simples")
                            21223
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Individual")
                            21069
                        @elseif($odonto_frase == " s/ Odonto" && $plano_nome == "Super Simples")
                            21223
                        @endif
                    </td>
                </tr>
            </thead>
            <tbody>
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

                    //if($odonto == 1) {
                        // Verifica se tem coparticipação
                        $index = ($coparticipacao == 1) ? 'com_copar' : 'sem_copar';

                        if (!isset($dadosComOdonto[$faixaEtaria])) {
                            $dadosComOdonto[$faixaEtaria] = [
                                'faixa_etaria_id' => $faixaEtaria,
                                'apartamento_com_copar' => 0, '1_com_copar' => 0, '2_com_copar' => 0, 'com_copar' => 0,
                                'enfermaria_com_copar' => 0,
                                'apartamento_sem_copar' => 0, '1_sem_copar' => 0, '2_sem_copar' => 0, 'sem_copar' => 0,
                                'enfermaria_sem_copar' => 0,
                                'quantidade' => $quantidade
                            ];
                        }
                        $dadosComOdonto[$faixaEtaria]["{$acomodacao}_{$index}"] = $valor ?? 0;
                    //}
                @endphp
            @endforeach
            @foreach($dadosComOdonto as $faixaEtaria => $valores)
                @for($i=0;$i<$valores['quantidade'];$i++)
                    <tr>
                        <td class="tbody_faixa_etaria">{{ $faixaEtaria }}</td>
                        <td class="tbody_com_copart">
                            {{ number_format($valores['1_com_copar'], 2, ",", ".") }}
                            @php
                                $totalApartamento_com_copar += $valores['1_com_copar'];
                            @endphp
                        </td>
                        <td class="tbody_com_copart">
                            {{ number_format($valores['2_com_copar'], 2, ",", ".") }}
                            @php
                                $totalEnfermaria_com_copar += $valores['2_com_copar'];
                            @endphp
                        </td>
                        <td class="tbody_sem_copart">
                            {{ number_format($valores['1_sem_copar'], 2, ",", ".") }}
                            @php
                                $totalApartamento_sem_copar += $valores['1_sem_copar'];
                            @endphp
                        </td>
                        <td class="tbody_sem_copart">
                            {{ number_format($valores['2_sem_copar'], 2, ",", ".") }}
                            @php
                                $totalEnfermaria_sem_copar += $valores['2_sem_copar'];
                            @endphp
                        </td>
                    </tr>
                @endfor
            @endforeach

            </tbody>
            <tfoot>
            <tr>
                <td>Total</td>
                <td>{{number_format($totalApartamento_com_copar,2,",",".")}}</td>
                <td>{{number_format($totalEnfermaria_com_copar,2,",",".")}}</td>
                <td>{{number_format($totalApartamento_sem_copar,2,",",".")}}</td>
                <td>{{number_format($totalEnfermaria_sem_copar,2,",",".")}}</td>
            </tr>
            @if($status_desconto == 1)
            <tr>
                <td style="text-align:center;background: rgb(250,181,25);font-weight:bold;color:white;">Desc.15% / 3 meses</td>
                <td style="text-align:center;background: rgb(250,181,25);font-weight:bold;color:white;">{{ number_format($totalApartamento_com_copar * 0.85,2,",",".")  }}</td>
                <td style="text-align:center;background: rgb(250,181,25);font-weight:bold;color:white;">{{ number_format($totalEnfermaria_com_copar * 0.85,2,",",".")  }}</td>
                <td style="text-align:center;background: rgb(250,181,25);font-weight:bold;color:white;">{{ number_format($totalApartamento_sem_copar * 0.85,2,",",".")  }}</td>
                <td style="text-align:center;background: rgb(250,181,25);font-weight:bold;color:white;">{{ number_format($totalEnfermaria_sem_copar * 0.85,2,",",".")  }}</td>
            </tr>
            @endif
            </tfoot>
        </table>

    </div>
    <div style="margin:10px 0;padding:0;">
    @if($status_desconto == 1)
        <div style="width:60%;border-radius:50%;margin:0 auto;background-color:red;color:white;text-align:center;padding:0;">
            <p style="font-size:1.2em;margin:0;padding:0;">Valores válidos por tempo limitado</p>
        </div>
    @endif
    </div>

    <div class="lembretes">
        <p>{{$pdf->linha01 ?? ''}}</p>
        <p>{{$pdf->linha02 ?? ''}}</p>
        <p>{{$pdf->linha03 ?? ''}}</p>
    </div>

    @php
      if($linhas <= 4) {
          echo '<div style="margin-bottom:100px"></div>';
      } else {
          echo '<div style="margin-bottom:0px"></div>';
      }
    @endphp


    @if(!$status_excecao)
        <table id="valores_coparticipacao" style="{{$status_carencia == 0 ? 'width:50%;' : 'width:100%;' }}border-collapse: collapse;margin:0;padding:0;">
            <tr>
                <td style="{{$status_carencia == 0 ? 'width:100%;' : 'width:50%;' }}vertical-align:top;padding:0;margin:0;">
                    <h3 style="margin: 0;text-align:left;">Coparticipação:</h3>

                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; vertical-align: top;text-align:left;">
                                <p class="section-title">Procedimentos</p>
                                <div class="section-content" style="text-align:left;">
                                    @if($pdf->consultas_eletivas_total || $pdf->consultas_eletivas_parcial)
                                        <p>Consultas Eletivas</p>
                                    @endif
                                    @if($pdf->consultas_de_urgencia_total || $pdf->consultas_de_urgencia_parcial)
                                        <p>Consultas de Urgência</p>
                                    @endif
                                    @if($pdf->exames_simples_total || $pdf->exames_simples_parcial)
                                        <p>Exames Simples</p>
                                    @endif
                                    @if($pdf->exames_complexos_total || $pdf->exames_complexos_parcial)
                                        <p>Exames Complexos</p>
                                    @endif
                                    @if($pdf->terapias_especiais_total || $pdf->terapias_especiais_parcial)
                                        <p>Terapias Especiais</p>
                                    @endif
                                    @if($pdf->demais_terapias_total || $pdf->demais_terapias_parcial)
                                        <p>Demais Terapias</p>
                                    @endif
                                    @if($pdf->internacoes_total || $pdf->internacoes_parcial)
                                        <p>Internações</p>
                                    @endif
                                    @if($pdf->internacoes_total || $pdf->internacoes_parcial)
                                        <p>Cirurgia</p>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 30%; vertical-align: top;text-align:left;">
                                <p class="section-title">Copart Total</p>
                                <div class="section-content" style="text-align:left;">
                                    <p>{{$pdf->consultas_eletivas_total ?? ''}}</p>
                                    <p>{{$pdf->consultas_de_urgencia_total ?? ''}}</p>
                                    <p>{{$pdf->exames_simples_total ?? ''}}</p>
                                    <p>{{$pdf->exames_complexos_total ?? ''}}</p>
                                    <p>{{$pdf->terapias_especiais_total ?? ''}}</p>
                                    <p>{{$pdf->demais_terapias_total ?? ''}}</p>
                                    <p>{{$pdf->internacoes_total ?? ''}}</p>
                                    <p>{{$pdf->cirurgia_total ?? ''}}</p>
                                </div>
                            </td>
                            <td style="width: 30%; vertical-align: top;text-align:left;">
                                <p class="section-title">Copart Parcial</p>
                                <div class="section-content" style="text-align:left;">
                                    <p>{{$pdf->consultas_eletivas_parcial ?? ''}}</p>
                                    <p>{{$pdf->consultas_de_urgencia_parcial ?? ''}}</p>
                                    <p>{{$pdf->exames_simples_parcial ?? ''}}</p>
                                    <p>{{$pdf->exames_complexos_parcial ?? ''}}</p>
                                    <p>{{$pdf->terapias_especiais_parcial ?? ''}}</p>
                                    <p>{{$pdf->demais_terapias_parcial ?? ''}}</p>
                                    <p>{{$pdf->internacoes_parcial ?? ''}}</p>
                                    <p>{{$pdf->cirurgia_parcial ?? ''}}</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                @if($status_carencia == 1)
                    <td style="width: 50%; vertical-align: top; padding-left: 2%; margin: 0;color:#366EBF;">
                        <h3 style="margin: 0;text-align:left;color:#366EBF;">Carências de Saúde:</h3>
                        <table style="width: 100%;border-spacing: 0; padding: 0;">
                            <tr style="padding: 0;">
                                <!-- Coluna da Esquerda (70%) -->
                                <td style="width: 60%; vertical-align: top; padding: 0; margin: 0;text-align:left;">
                                    <p style="margin: 0;padding:0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px;">
                                    24<br />horas
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;">
                                    Urgência, Emergência e<br />Acidentes Pessoais
                                </span>
                                    </p>
                                    <p style="margin: 10px 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    30<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;">
                                    Consultas Médicas,<br />Exames Médicos Simples
                                </span>
                                    </p>
                                    <p style="margin: 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    90<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: top; margin: 0;font-size:0.7em;">
                                    Exames Cardiológicos, Exames<br /> de Imagem, Oftalmológicos,<br />Otorrino Simples, Raio X,<br />Ultrassonografia
                                </span>
                                    </p>
                                    <p style="margin:10px 0px 0px 0px;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    180<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: top; margin: 0;font-size:0.7em;">
                                    Cirurgias, Internações, Exames<br />de Alto Custo, Tratamento<br />Psicológico, Terapia Ocupacional,<br />Fisioterapia
                                </span>
                                    </p>
                                </td>
                                <!-- Coluna da Direita (30%) -->
                                <td style="width: 40%; vertical-align: top; padding: 0; margin: 0;text-align:left;">
                                    <p style="margin: 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    300<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.8em;">
                                    Parto
                                </span>
                                    </p>
                                    <p style="margin: 10px 0 0 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    720<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;;">
                                    Doenças e Lesões<br />Pré-existentes
                                </span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif


            </tr>
        </table>
    @else

        <table id="valores_coparticipacao" style="{{$status_carencia == 0 ? 'width:50%;' : 'width:100%;' }}border-collapse: collapse;margin:0;padding:0;">
            <tr>
                <td style="{{$status_carencia == 0 ? 'width:100%;' : 'width:50%;' }}vertical-align:top;padding:0;margin:0;">
                    <h3 style="margin: 0;text-align:left;">Coparticipação:</h3>

                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; vertical-align: top;text-align:left;">
                                <p class="section-title">Procedimentos</p>
                                <div class="section-content" style="text-align:left;">
                                    @if($pdf->consultas_eletivas_total)
                                        <p>Consultas Eletivas</p>
                                    @endif
                                    @if($pdf->pronto_atendimento)
                                        <p>Pronto Atendimento</p>
                                    @endif
                                    @if($pdf->faixa_1)
                                        <p>Faixa 1</p>
                                    @endif
                                    @if($pdf->faixa_2)
                                         <p>Faixa 2</p>
                                    @endif
                                    @if($pdf->faixa_3)
                                        <p>Faixa 3</p>
                                    @endif
                                    @if($pdf->faixa_4)
                                        <p>Faixa 4</p>
                                    @endif


                                </div>
                            </td>
                            <td style="width: 30%; vertical-align: top;text-align:left;">
                                <p class="section-title">Copart Total</p>
                                <div class="section-content" style="text-align:left;">
                                    <p>{{$pdf->consultas_eletivas_total ?? ''}}</p>
                                    <p>{{$pdf->pronto_atendimento ?? ''}}</p>
                                    <p>{{$pdf->faixa_1 ?? ''}}</p>
                                    <p>{{$pdf->faixa_2 ?? ''}}</p>
                                    <p>{{$pdf->faixa_3 ?? ''}}</p>
                                    <p>{{$pdf->faixa_4 ?? ''}}</p>
                                </div>
                            </td>

                        </tr>
                    </table>
                </td>






                @if($status_carencia == 1)
                    <td style="width: 50%; vertical-align: top; padding-left: 2%; margin: 0;color:#366EBF;">
                        <h3 style="margin: 0;text-align:left;color:#366EBF;">Carências de Saúde:</h3>
                        <table style="width: 100%;border-spacing: 0; padding: 0;">
                            <tr style="padding: 0;">
                                <!-- Coluna da Esquerda (70%) -->
                                <td style="width: 60%; vertical-align: top; padding: 0; margin: 0;text-align:left;">
                                    <p style="margin: 0;padding:0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px;">
                                    24<br />horas
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;">
                                    Urgência, Emergência e<br />Acidentes Pessoais
                                </span>
                                    </p>
                                    <p style="margin: 10px 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    30<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;">
                                    Consultas Médicas,<br />Exames Médicos Simples
                                </span>
                                    </p>
                                    <p style="margin: 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    90<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: top; margin: 0;font-size:0.7em;">
                                    Exames Cardiológicos, Exames<br /> de Imagem, Oftalmológicos,<br />Otorrino Simples, Raio X,<br />Ultrassonografia
                                </span>
                                    </p>
                                    <p style="margin:10px 0px 0px 0px;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    180<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: top; margin: 0;font-size:0.7em;">
                                    Cirurgias, Internações, Exames<br />de Alto Custo, Tratamento<br />Psicológico, Terapia Ocupacional,<br />Fisioterapia
                                </span>
                                    </p>
                                </td>
                                <!-- Coluna da Direita (30%) -->
                                <td style="width: 40%; vertical-align: top; padding: 0; margin: 0;text-align:left;">
                                    <p style="margin: 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    300<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.8em;">
                                    Parto
                                </span>
                                    </p>
                                    <p style="margin: 10px 0 0 0;text-align:left;">
                                <span style="border: 4px solid #366EBF;font-size:0.8em;border-radius:5px; display: inline-block; vertical-align: top; margin: 0;text-align:center;padding:5px 12px;">
                                    720<br />dias
                                </span>
                                        <span style="display: inline-block; vertical-align: middle; margin: 0;font-size:0.7em;;">
                                    Doenças e Lesões<br />Pré-existentes
                                </span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif

            </tr>
        </table>









    @endif








    @if($odonto == 1 && $status_carencia == 1)
    <!-- Nova Tabela -->
    <table id="valores_odonto" style="width: 100%; border-collapse: collapse; color:#366EBF;">
        <!-- Título -->
        <tr>
            <td colspan="3" style="text-align: left; padding: 1px 0;">
                <h3 style="margin: 0; text-align: left; width: 100%;">Carências Odonto</h3>
            </td>
        </tr>
        <!-- Conteúdo das Colunas -->
        <tr>
            <!-- Coluna 1 -->
            <td style="width: 33%; vertical-align: top; padding: 0 10px;">
                <p style="margin: 0; text-align: left;">
                <span style="border: 4px solid #366EBF; font-size: 0.8em; border-radius: 5px; display: inline-block; vertical-align: top; text-align: center; padding: 5px 12px;">
                    24<br />horas
                </span>
                    <span style="display: inline-block; vertical-align: middle; margin-left: 10px; font-size: 0.8em;">
                    Atendimento Urgência e<br />Emergência
                </span>
                </p>
            </td>
            <!-- Coluna 2 -->
            <td style="width: 33%; vertical-align: top; padding: 0 10px;">
                <p style="margin: 0; text-align: left;">
                <span style="border: 4px solid #366EBF; font-size: 0.8em; border-radius: 5px; display: inline-block; vertical-align: top; text-align: center; padding: 5px 12px;">
                    60<br />dias
                </span>
                    <span style="display: inline-block; vertical-align: middle; margin-left: 10px; font-size: 0.8em;">
                    Diagnóstico, Prevenção<br />em Saúde Bucal e Dentista
                </span>
                </p>
            </td>
            <!-- Coluna 3 -->
            <td style="width: 33%; vertical-align: top; padding: 0 10px;">
                <p style="margin: 0; text-align: left;">
                <span style="border: 4px solid #366EBF; font-size: 0.8em; border-radius: 5px; display: inline-block; vertical-align: top; text-align: center; padding: 5px 12px;">
                    180<br />dias
                </span>
                    <span style="display: inline-block; vertical-align: middle; margin-left: 10px; font-size: 0.8em;">
                    Demais Procedimentos<br />Odontológicos
                </span>
                </p>
            </td>
        </tr>
    </table>
    @endif


</div>


<div class="footer">
    @if($image != "")
        <img src='{{$image}}' alt='ser Image'>
    @endif
    <div class="middle">
        <p>{{$nome}}</p>
        <p>{{$celular}}</p>

    </div>


    <div class="right">
        @if($corretora == 1)
            <p>Escritório</p>
            <p>(062) 9999-9999</p>

        @else
            <p>Escritório</p>
            <p>(062) 9999-9999</p>

        @endif

    </div>


</div>



</body>
</html>

