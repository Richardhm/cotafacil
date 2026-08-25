<!doctype html>
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
    
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif !important;
            background-color:rgb(8,73,189);
        }
        tr {
            line-height: 1;
            vertical-align: top;
        }

        
        .container {
            position: absolute;
            top: 315px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
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
            bottom:10px;
            left: 20px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
        }

        .footer .middle, .footer .right {
            position: absolute;
            bottom:0px;
            color: #0c0c0c;
        }

        .footer .middle {
            bottom:30px;
            left: 550px;
            display:block;
            color: #0c0c0c;
            font-size:2em;
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
            top:150px;
            left:42%;
            font-weight: bold;
            font-size: 2em;
            color:white;
        }

        .frase_container {
            position:absolute;
            top:205px;
            left:35%;
            font-weight: bold;
            font-size: 1.8em;
            color:white;
        }
        
        
        
        
        /**********************/
        .bloco-container {
            width: 100%;
            margin: 0 auto;
            border-spacing: 10px 0 !important; /* Só espaçamento horizontal */
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
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 0 0.5% 0 0.5%; /* Margem controlada */
            vertical-align: top;
            padding:5px;
        }

        .header-orange {
            background: #F88058;
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            font-weight: bold;
            font-size: 1em;
            
        }

        .subheader-blue {
            background: white;
            color: white;
            padding: 8px;
            font-size: 1.3em;
            color: rgb(8,73,189);
            font-weight:bold;
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
            font-size: 1.5em;
            color: rgb(8,73,189);
        }

        .valor-copart {
            background: rgb(254,199,72);
            padding: 12px !important;
            margin: 5px 3px;
            border-radius: 8px;
            color: rgb(8,73,189);
            font-weight: bold;
            display: block;
            font-size:1.2em;
            text-align:center;
        }
       
        .lista-coparticipacao {
            color: white;
            font-size: 1.2em;
            margin: 10px 0;
        }
    
        .lista-coparticipacao strong {
            display: block;
            margin: 10px 0;
            font-size:1.5em;
        }
    
        .procedimentos-container {
            width: 85%;
            margin-top: 1px;
        }
    
        .procedimento-left {
            display: inline-block;
            width: 60%;
            vertical-align: top;
            margin-right:4%;
            
        }
        
        .procedimento-right {
            display: inline-block;
            width: 35%;
            vertical-align: top;
            
        }
        
    
        .linha-procedimento {
             margin: 8px 0;
        }
        
        .linha-procedimento span {
             font-size:1.2em;
             color: rgb(8,73,189);
             display:block;
             font-weight:bold;
        }
        
    
        .valor-procedimento {
            background: #FFF3CD;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-left: 10px;
        }

    </style>
</head>
<body>
    <div class="header-container">
        <img style="position: absolute;top: 0;width:100%;height:300px;left: 0;object-fit: cover;" src="cabecalhos/cabecalho{{$cabecalho}}.png" alt="Orçamento">
    </div>    
    <p class="cidade_container">{{$cidade}}</p>
    <p class="frase_container">{{$frase}}</p>

    <p class="cidade_container">{{$cidade}}</p>
<p class="frase_container">{{$frase}}</p>

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





<div class="container">
   <table class="bloco-container" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Bloco 1 - Faixa Etária -->
            <td class="bloco" style="width: 22%;{{$totalBlocos <= 2 ? 'margin-left:20%;' : 'margin-left:0%;'}}">
                <table width="100%">
                    <tr>
                        <td class="header-orange" style="text-align:center;">{{ $apelido_plano ?? 'NOSSO PLANO' }}</td>
                    </tr>
                    <tr>
                        <td class="subheader-blue">FAIXA ETÁRIA</td>
                    </tr>
                    
                    @foreach($dadosComOdonto as $faixaEtaria => $valores)
                    <tr>
                        <td>
                            <div class="valor-copart">
                                {{ $faixaEtaria }}
                            </div>
                        </td>
                    </tr>
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
                        <td colspan="2" class="header-orange" style="text-align:center;">COM COPARTICIPAÇÃO</td>
                    </tr>
                    <tr>
                        <td class="coluna-azul">ENFER</td>
                        <td class="coluna-azul">APART</td>
                    </tr>
                    @foreach($dadosComOdonto as $faixaEtaria => $valores)
                    <tr>
                        <td>
                           <div class="valor-copart">
                           	@php
                                    $totalEnfermaria_com_copar += $valores['2_com_copar'];
                                @endphp
                                {{ number_format($valores['2_com_copar'], 2, ",", ".") }}   
                           </div> 
                        </td>
                        <td>
                           <div class="valor-copart">
                           	@php
                                    $totalApartamento_com_copar += $valores['1_com_copar'];
                                @endphp
                                {{ number_format($valores['1_com_copar'], 2, ",", ".") }}   
                           </div>   
                        </td>
                    </tr>
                    @endforeach
                    <tfoot>
            	    <tr>
                
                	<td>
                	     	<div class="valor-copart-laranja">
                        	{{number_format($totalEnfermaria_com_copar,2,",",".")}}
                    	   </div>
                	</td>
                	<td>
                    	<div class="valor-copart-laranja">
                        	{{number_format($totalApartamento_com_copar,2,",",".")}}                    
                        </div>
                       </td>
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
                        <td colspan="2" class="header-orange" style="text-align:center;">SEM COPARTICIPAÇÃO *</td>
                    </tr>
                    <tr>
                        <td class="coluna-azul">ENFER</td>
                        <td class="coluna-azul">APART</td>
                    </tr>
                    
                    @foreach($dadosComOdonto as $faixaEtaria => $valores)
                    <tr >
                        <td>
                           <div class="valor-copart">
                           	@php
                                    $totalEnfermaria_sem_copar += $valores['2_sem_copar'];
                                @endphp
                                {{ number_format($valores['2_sem_copar'], 2, ",", ".") }}   
                           </div> 
                        </td>
                        <td>
                           <div class="valor-copart">
                           	@php
                                    $totalApartamento_sem_copar += $valores['1_sem_copar'];
                                @endphp
                                {{ number_format($valores['1_sem_copar'], 2, ",", ".") }}   
                           </div>   
                        </td>
                    </tr>
                    @endforeach
                    <tfoot>
            <tr>
                
                <td>
                    <div class="valor-copart-laranja">
                        {{number_format($totalEnfermaria_sem_copar,2,",",".")}}
                    </div>
                </td>
                <td>
                    <div class="valor-copart-laranja">
                        {{number_format($totalApartamento_sem_copar,2,",",".")}}                    
                     </div>
                </td>
            </tr>
        </tfoot>	
                </table>
                <tr>
              </tr>  
            </td> 
            @endif
        </tr>
    </table> 
</div>    

<script>

</script>



</body>
</html>

