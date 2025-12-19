<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escala - {{ $unidade->nome ?? 'Unidade' }} - {{ $meses[$mes] }}/{{ $ano }}</title>
    <style>
        @page { margin: 1.5cm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11pt; 
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a4480;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            color: #1a4480;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
            color: #666;
        }
        .grupo {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .grupo-header {
            background: #1a4480;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11pt;
            border-radius: 4px 4px 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10pt;
        }
        td { font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .lider { color: #d4a00a; font-weight: bold; }
        .dias { font-family: monospace; font-size: 9pt; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14pt; cursor: pointer;">
            Imprimir / Salvar PDF
        </button>
    </div>
    
    <div class="header">
        <h1>{{ $unidade->nome ?? 'Unidade' }}</h1>
        <h2>Escala Extraordinária - {{ $meses[$mes] }}/{{ $ano }}</h2>
    </div>
    
    @if(empty($agrupado))
        <p style="text-align: center; color: #666;">Nenhuma alocação encontrada para esta escala.</p>
    @else
        @foreach($agrupado as $grupo)
        <div class="grupo">
            <div class="grupo-header">
                {{ $grupo['modulo'] }} - {{ $grupo['equipe'] }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Nome</th>
                        <th style="width: 15%">Matrícula</th>
                        <th style="width: 35%">Dias</th>
                        <th style="width: 15%" class="text-center">Total Horas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupo['servidores'] as $srv)
                    <tr>
                        <td>
                            {{ $srv['nome'] }}
                            @if($srv['is_lider'])
                                <span class="lider">★</span>
                            @endif
                        </td>
                        <td>{{ $srv['matricula'] }}</td>
                        <td class="dias">{{ implode(', ', $srv['dias']) }}</td>
                        <td class="text-center">{{ number_format($srv['horas'], 0) }}h</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    @endif
    
    <div class="footer">
        Documento gerado em {{ date('d/m/Y H:i') }} - SIGEEX - Sistema de Gestão de Escalas Extraordinárias
    </div>
</body>
</html>
