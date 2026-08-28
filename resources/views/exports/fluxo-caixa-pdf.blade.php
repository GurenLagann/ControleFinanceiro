<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fluxo de Caixa {{ $ano }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #333; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td { font-size: 11px; }
        .text-right { text-align: right; }
        .receita { color: #155724; }
        .despesa { color: #721c24; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            text-align: center; font-size: 10px; color: #999;
            padding: 10px; border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fluxo de Caixa Mensal</h1>
        <p>{{ $ano }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th class="text-right">Receitas</th>
                <th class="text-right">Despesas</th>
                <th class="text-right">Saldo</th>
                <th class="text-right">Saldo Acumulado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fluxo['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-right receita">R$ {{ number_format($fluxo['receitas'][$i], 2, ',', '.') }}</td>
                    <td class="text-right despesa">R$ {{ number_format($fluxo['despesas'][$i], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($fluxo['saldo'][$i], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($fluxo['saldoAcumulado'][$i], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} - Controle Financeiro
    </div>
</body>
</html>
