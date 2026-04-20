<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" sizes="75x76" href="{{ asset('icons/icone_bmsys.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="{{asset('jquery.min.js')}}"></script>
    <script src="{{asset('sweetalert2@11.js')}}"></script>
    <link rel="stylesheet" href="{{asset('toastr.min.css')}}">
    <script src="{{asset('toastr.min.js')}}"></script>
    <script src="{{asset('jquery.inputmask.min.js')}}"></script>
    <script src="{{asset('jquery.mask.min.js')}}"></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('css')



    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 5000, // Tempo de exibição (5 segundos)
            positionClass: "toast-top-center", // Ainda usamos uma posição padrão
            onShown: function() {
                $("#toast-container").addClass("custom-toast-center");
            }
        };
    </script>
    <style>

        .border-blue-500 {
            border-bottom: 2px !important;
            border-top: 2px !important;
            border-left: 2px !important;
            border-right: 2px !important;
            border-color: #3b82f6 !important; /* Usa Tailwind blue-500 com prioridade */
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
            background: rgba(0,0,0,0.3);
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

        #user-modal {
            opacity: 0; /* Inicialmente invisível */
            transform: translateX(100%);
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        }
        #user-modal.translate-x-0 {
            opacity: 1; /* Tornar visível */
            transform: translateX(0);
        }

        #toast-container {
            top: 10% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width:500px !important;
            position: fixed !important;
        }
        .ajax_load {display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;}
        .ajax_load_box{margin:auto;text-align:center;color:#fff;font-weight:var(700);text-shadow:1px 1px 1px rgba(0,0,0,.5)}
        .ajax_load_box_circle{border:16px solid #e3e3e3;border-top:16px solid #61DDBC;border-radius:50%;margin:auto;width:80px;height:80px;-webkit-animation:spin 1.2s linear infinite;-o-animation:spin 1.2s linear infinite;animation:spin 1.2s linear infinite}
        @-webkit-keyframes spin{0%{-webkit-transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg)}}
        @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}

        @keyframes handClick {
            0%, 100% { transform: translateX(0) scale(1); }
            50% { transform: translateX(8px) scale(1.1); }
        }

        @keyframes textBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .animate-handClick {
            animation: handClick 1s infinite ease-in-out;
        }

        .animate-textBlink {
            animation: textBlink 1.5s infinite ease-in-out;
        }

        .switch {position: relative;display: flex;width: 40px;height: 20px;margin:0 0 0 10px;padding:0;justify-content: center;}
        .switch input {opacity: 0;width: 0;height: 0;}
        .slider {position: absolute;cursor: pointer;top: 0;left: 0;right: 0;bottom: 0;background-color: #ccc;transition: .4s;border-radius: 20px;}
        .slider:before {position: absolute;content: "";height: 16px;width: 16px;left: 2px;bottom: 2px;background-color: white;transition: .4s;border-radius: 50%;}
        input:checked + .slider {background-color: #4caf50;}
        input:checked + .slider:before {transform: translateX(20px);}

        .toast {
            min-width: 400px !important;
            max-width: 90vw;
            width: auto !important;
            font-size: 14px;
        }

        body {
            background: url('{{ asset('fred01.jpeg') }}') no-repeat center center fixed;
            background-size: cover;
        }

        @media (max-width: 768px) {
            body {
                background-position: top center;
                background-size: contain;
                background-repeat: no-repeat;
                background-color: #ffffff; /* fundo branco para não ficar vazio */
            }
        }

        /* Estilização da barra de rolagem vertical */
        .custom-scrollbar {
            scrollbar-width: thin; /* Para navegadores Firefox */
            scrollbar-color: #add8e6 transparent; /* Barra azul claro com fundo transparente */
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px; /* Barra mais fina */
            background-color: transparent; /* Fundo transparente */
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #add8e6; /* Azul claro */
            border-radius: 4px; /* Bordas arredondadas */
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #87ceeb; /* Azul escuro ao passar o mouse */
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent; /* Remove track de fundo para discrição */
        }

    </style>
</head>
<body class="font-sans antialiased">
<div class="ajax_load">
    <div class="ajax_load_box">
        <div class="ajax_load_box_circle"></div>
        <p class="ajax_load_box_title">Aguarde, carregando...</p>
    </div>
</div>
<!-- Container principal -->
<div id="app-container" class="min-h-screen transition-all duration-300">
    @include('layouts.navigation')

    <!-- Conteúdo da página -->
    <main>
        {{ $slot }}
    </main>
</div>

<!-- Script para aplicar lógica de tema -->
<script>


    // Verifica se o tema é dark ou light e aplica a lógica
    const htmlElement = document.getElementById('html');
    const appContainer = document.getElementById('app-container');

    function applyThemeBackground() {
        if (htmlElement.classList.contains('dark')) {
            // Modo dark: fundo preto
            appContainer.style.background = '#000';
        } else {
            // Modo light: fundo com imagem
            appContainer.style.background = "url('{{ asset('fred01.jpeg') }}') no-repeat center center fixed";
            appContainer.style.backgroundSize = 'cover';
        }
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
