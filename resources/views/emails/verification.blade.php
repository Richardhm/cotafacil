<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de E-mail</title>
</head>
<body>
<h2>Olá, {{ $user->name }}</h2>
<p>Obrigado por se cadastrar! Clique no botão abaixo para verificar seu e-mail:</p>
<a href="{{ $verificationUrl }}"
   style="background-color:#4CAF50;color:white;padding:10px 20px;text-decoration:none;">
    Verificar E-mail
</a>
<p>Se você não se registrou em nosso sistema, ignore esta mensagem.</p>
</body>
</html>
