<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" sizes="75x76" href="{{ asset('icons/icone_bmsys.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="{{asset('jquery.min.js')}}"></script>
    <script src="{{asset('jquery.inputmask.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('toastr.min.css')}}">
    <script src="{{asset('toastr.min.js')}}"></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 5000, // Tempo de exibição (5 segundos)
            positionClass: "toast-top-center", // Ainda usamos uma posição padrão
            onShown: function() {
                $(".toast").css("width", "500px");
                $("#toast-container").addClass("custom-toast-center");
            }
        };
    </script>
    <style>
        .container_formulario {

        }
        .container_formulario.login {
            width: 390px;
        }
        .container_formulario.cadastro {
            width:950px;
            background:rgba(254,254,254,0.18);
            backdrop-filter: blur(15px);
        }
        #toast-container {
            width: 500px !important; /* Ajuste conforme necessário */
            max-width: 100%; /* Evita que fique maior que a tela */
            word-wrap: break-word; /* Quebra texto longo */
        }
        #toast-container > .toast {
            width: 500px !important; /* Ajuste conforme necessário */
            max-width: 100%; /* Evita que fique maior que a tela */
            word-wrap: break-word; /* Quebra texto longo */
        }
        #toast-container > div {
            min-width: 500px !important; /* Aumenta a largura mínima */
            max-width: 600px !important; /* Ajuste conforme necessário */
            word-wrap: break-word; /* Quebra o texto longo */
        }
        .ajax_load {display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;}
        .ajax_load_box{margin:auto;text-align:center;color:#fff;font-weight:var(700);text-shadow:1px 1px 1px rgba(0,0,0,.5)}
        .ajax_load_box_circle{border:16px solid #e3e3e3;border-top:16px solid #61DDBC;border-radius:50%;margin:auto;width:80px;height:80px;-webkit-animation:spin 1.2s linear infinite;-o-animation:spin 1.2s linear infinite;animation:spin 1.2s linear infinite}
        @-webkit-keyframes spin{0%{-webkit-transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg)}}
        @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}

        @media (max-width: 768px) {
            .container_formulario.cadastro {
                width: 100% !important;
                margin: 0 10px;
                padding: 10px;
            }

            fieldset {
                margin-bottom: 1rem;
            }

            input, select {
                font-size: 14px !important;
                padding: 8px !important;
            }

            #cartao {
                height: 180px !important;
                margin-top: 1rem !important;
            }
        }

        .whatsapp-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            z-index: 1000;
        }

        .suporte-texto {
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);

            padding: 2px 8px;
            border-radius: 5px;
        }

        .whatsapp-button {
            background-color: #25D366;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .whatsapp-button:hover {
            transform: scale(1.1);
        }

        .jumping-dots-loader {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            gap: 8px;
        }

        .jumping-dots-loader .dot {
            width: 15px;
            height: 15px;
            background: orange;
            border-radius: 50%;
            margin: 0 3px;
            animation: jumping 0.6s infinite alternate;
            display: inline-block;
        }

        .jumping-dots-loader .dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .jumping-dots-loader .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes jumping {
            to {
                transform: translateY(-16px);
            }
        }

        .loading-dots {
            display: flex;
            align-items: center;
        }

        .loading-dots div {
            width: 8px;
            height: 8px;
            margin: 0 2px;
            border-radius: 50%;
            background-color: #000;
            animation: bounce 1.5s infinite;
        }

        .loading-dots div:nth-child(1) {
            animation-delay: 0s;
        }
        .loading-dots div:nth-child(2) {
            animation-delay: 0.3s;
        }
        .loading-dots div:nth-child(3) {
            animation-delay: 0.6s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
        }


    </style>
    @php
        $gnHost      = config('gerencianet.is_sandbox') ? 'cobrancas-h.api.efipay.com.br' : 'cobrancas.api.efipay.com.br';
        $gnAccountId = config('gerencianet.cdn_account_id');
    @endphp
    <script type='text/javascript'>var s=document.createElement('script');s.type='text/javascript';var v=parseInt(Math.random()*1000000);s.src='https://{{ $gnHost }}/v1/cdn/{{ $gnAccountId }}/'+v;s.async=false;s.id='{{ $gnAccountId }}';if(!document.getElementById('{{ $gnAccountId }}')){document.getElementsByTagName('head')[0].appendChild(s);};$gn={validForm:true,processed:false,done:{},ready:function(fn){$gn.done=fn;}};</script>
</head>
<body class="font-sans text-gray-900 antialiased">

<div class="whatsapp-container">
    <span class="suporte-texto">Suporte</span>
    <a href="https://wa.me/5562982686918" target="_blank" class="whatsapp-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40">
            <path fill="white" d="M19.077 4.928C17.191 3.041 14.683 2.001 12.011 2c-5.506 0-9.987 4.479-9.989 9.985-.001 1.76.459 3.478 1.333 4.992L2 22l5.233-1.237c1.37.751 2.945 1.148 4.565 1.148h.004c5.505 0 9.986-4.48 9.989-9.985.001-2.677-1.057-5.215-2.964-7.062zm-7.066 14.31h-.004c-1.37 0-2.697-.37-3.84-1.075l-.275-.163-2.865.758.764-2.853-.18-.278a8.3 8.3 0 0 1-1.274-4.383c.002-4.584 3.735-8.317 8.317-8.317 2.223 0 4.313.866 5.885 2.437a8.284 8.284 0 0 1 2.433 5.89c-.002 4.583-3.735 8.316-8.317 8.316zm4.538-6.213c-.246-.123-1.457-.719-1.684-.8-.226-.084-.392-.123-.557.123-.166.247-.643.8-.788.968-.145.164-.29.185-.538.062-.247-.123-1.043-.384-1.987-1.226-.734-.654-1.23-1.462-1.374-1.71-.144-.247-.015-.381.108-.504.112-.112.247-.289.37-.434.124-.145.165-.247.247-.412.082-.164.041-.309-.009-.434-.05-.123-.557-1.345-.763-1.84-.204-.495-.408-.428-.557-.433-.144-.005-.31-.005-.476-.005-.164 0-.431.061-.657.308-.226.246-.864.845-.864 2.058 0 1.213.883 2.387 1.006 2.55.123.164 1.746 2.666 4.236 3.727.59.255 1.05.409 1.407.522.59.185 1.126.159 1.551.097.473-.069 1.457-.594 1.662-1.168.204-.574.204-1.066.143-1.168-.062-.102-.226-.164-.472-.287z"/>
        </svg>
    </a>
</div>




<div class="ajax_load">
    <div class="ajax_load_box">
        <div class="ajax_load_box_circle"></div>
        <p class="ajax_load_box_title">Aguarde, carregando...</p>
    </div>
</div>
<div id="app-container" class="min-h-screen flex flex-col sm:justify-center items-center sm:pt-0">
    <div class="container_formulario {{ (request()->routeIs('login')) ? 'login' : ((request()->routeIs('assinaturas.individual.create') || request()->routeIs('assinaturas.empresarial.create') || request()->routeIs('assinaturas.promocional.create') || request()->routeIs('assinatura.edit')) ? 'cadastro' : '') }} w-full mt-1 px-2 py-1 dark:bg-[rgba(254,254,254,0.18)] bg-transparent dark:backdrop-blur-[15px] dark:border-white dark:border-2 shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
<script>
    // Verifica se o tema é dark ou light e aplica a lógica
    const htmlElement = document.getElementById('html');
    const appContainer = document.getElementById('app-container');
    function applyThemeBackground() {
        appContainer.style.background = "url('{{ asset('fred01.jpeg') }}') no-repeat center center";
        appContainer.style.backgroundSize = 'cover';
    }
    // Executa ao carregar a página
    applyThemeBackground();
    // Observa mudanças no tema (se for implementado com toggle)
    const observer = new MutationObserver(applyThemeBackground);
    observer.observe(htmlElement, { attributes: true, attributeFilter: ['class'] });
</script>
@yield('scripts')
</body>
</html>
