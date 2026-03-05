<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: auto; }
        .code { font-size: 36px; font-weight: bold; color: #4F46E5; letter-spacing: 8px; text-align: center; padding: 20px; background: #EEF2FF; border-radius: 8px; margin: 20px 0; }
        .footer { color: #888; font-size: 12px; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Código de Verificación</h2>
        <p>Hola, usa este código para completar tu inicio de sesión:</p>
        <div class="code">{{ $otp }}</div>
        <p>Este código <strong>expira en 10 minutos</strong>.</p>
        <p>Si no solicitaste este código, ignora este correo.</p>
        <div class="footer">API 2FA — Sistema de autenticación seguro</div>
    </div>
</body>
</html>