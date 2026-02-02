<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $alerta->titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #e9ecef;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-radius: 0 0 5px 5px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-box.urgent {
            background-color: #f8d7da;
            border-color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIGEEX - Alerta de Prazo</h1>
    </div>
    <div class="content">
        <h2>{{ $alerta->titulo }}</h2>
        
        <div class="alert-box {{ str_contains($alerta->tipo, '5dias') || str_contains($alerta->tipo, '6horas') ? 'urgent' : '' }}">
            <p><strong>Mensagem:</strong></p>
            <p>{{ $alerta->mensagem }}</p>
        </div>
        
        @if($alerta->prazo_limite)
        <p><strong>Prazo Limite:</strong> {{ $alerta->prazo_limite->format('d/m/Y H:i') }}</p>
        @endif
        
        <p><strong>Unidade:</strong> {{ $alerta->unidade?->nome ?? 'N/A' }}</p>
        <p><strong>Periodo:</strong> {{ $alerta->mes }}/{{ $alerta->ano }}</p>
        
        <p>Por favor, acesse o sistema para tomar as providencias necessarias.</p>
    </div>
    <div class="footer">
        <p>Este e-mail foi enviado automaticamente pelo SIGEEX.</p>
        <p>Sistema de Gestao de Escalas Extraordinarias</p>
    </div>
</body>
</html>
