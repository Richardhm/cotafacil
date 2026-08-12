<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CotaFácil — Abrindo aplicativo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #f1f5f9;
            padding: 24px;
            text-align: center;
        }
        .logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        p { font-size: 14px; color: #94a3b8; margin-bottom: 32px; line-height: 1.6; }
        .spinner {
            width: 44px;
            height: 44px;
            border: 3px solid rgba(255,255,255,0.1);
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 24px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            margin-top: 8px;
        }
        .note { font-size: 12px; color: #64748b; margin-top: 20px; }
        .link-pagamento {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 20px;
            color: #cbd5e1;
            font-size: 14px;
            text-decoration: underline;
        }
        #msg { font-size: 13px; color: #94a3b8; min-height: 20px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="logo">CF</div>
    <h1>CotaFácil</h1>
    <p>O acesso pelo celular é feito exclusivamente<br>pelo aplicativo CotaFácil.</p>

    <div class="spinner" id="spinner"></div>
    <p id="msg">Abrindo o aplicativo...</p>

    <a href="https://app.bmsys.com.br/install" class="btn" id="btn-install" style="display:none">
        Instalar o Aplicativo
    </a>

    <p class="note">Caso o aplicativo não abra automaticamente,<br>clique no botão acima para instalar.</p>

    {{-- Saída para quem precisa pagar: leva ao login em modo pagamento, que entra
         sem registrar dispositivo e só alcança as telas de assinatura. Cancela o
         redirecionamento automático para o app, senão a página some antes do toque. --}}
    <a href="{{ route('login') }}?pagamento=1" class="link-pagamento" id="link-pagamento">
        Preciso regularizar minha assinatura
    </a>

    <script>
        var appScheme  = 'intent:#Intent;action=android.intent.action.MAIN;category=android.intent.category.LAUNCHER;package=bmsys.com.br.cotacao;S.browser_fallback_url=https%3A%2F%2Fapp.bmsys.com.br%2Finstall;end';
        var installUrl = 'https://app.bmsys.com.br/install';

        var ua = navigator.userAgent;
        var isAndroid = /Android/i.test(ua);
        var isIOS     = /iPhone|iPod/i.test(ua);
        var redirecionar = true;

        // Se o usuário tocar no link de pagamento, nenhum timer pode levá-lo embora
        document.getElementById('link-pagamento').addEventListener('click', function () {
            redirecionar = false;
        });

        function showInstallBtn() {
            document.getElementById('spinner').style.display = 'none';
            document.getElementById('msg').textContent = 'Aplicativo não encontrado.';
            document.getElementById('btn-install').style.display = 'inline-block';
        }

        if (isAndroid) {
            // Tenta abrir via Android Intent (redireciona para installUrl se não instalado)
            window.location.href = appScheme;
            setTimeout(showInstallBtn, 2500);
        } else if (isIOS) {
            // iOS: vai direto para install (2,5s dá tempo de tocar no link de pagamento)
            setTimeout(function() {
                if (redirecionar) window.location.href = installUrl;
            }, 2500);
        } else {
            // Outros (tablet, etc.) — vai para install
            setTimeout(function() {
                if (redirecionar) window.location.href = installUrl;
            }, 2500);
        }
    </script>
</body>
</html>
