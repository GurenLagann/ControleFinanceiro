<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Extrato Financeiro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .resumo {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .resumo-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .resumo-item h3 {
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 3px;
            color: #666;
        }
        .resumo-item .valor {
            font-size: 14px;
            font-weight: bold;
        }
        .receitas .valor { color: #155724; background-color: #d4edda; }
        .despesas .valor { color: #721c24; background-color: #f8d7da; }
        .saldo .valor { color: #004085; background-color: #cce5ff; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .receita { color: #155724; }
        .despesa { color: #721c24; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Extrato Financeiro</h1>
        <p>{{ $dataInicio->format('d/m/Y') }} a {{ $dataFim->format('d/m/Y') }}</p>
    </div>

    <div class="resumo">
        <div class="resumo-item receitas">
            <h3>Total Receitas</h3>
            <div class="valor">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
        </div>
        <div class="resumo-item despesas">
            <h3>Total Despesas</h3>
            <div class="valor">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        </div>
        <div class="resumo-item saldo">
            <h3>Saldo</h3>
            <div class="valor">R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Data</th>
                <th style="width: 60px;">Tipo</th>
                <th>Descricao</th>
                <th style="width: 90px;">Categoria</th>
                <th style="width: 90px;" class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transacoes as $t)
                <tr>
                    <td>{{ $t->data ? $t->data->format('d/m/Y') : '-' }}</td>
                    <td class="{{ $t->tipo === 'Receita' ? 'receita' : 'despesa' }}">
                        {{ $t->tipo }}
                    </td>
                    <td>{{ $t->descricao }}</td>
                    <td>{{ $t->categoria ?: '-' }}</td>
                    <td class="text-right {{ $t->tipo === 'Receita' ? 'receita' : 'despesa' }}">
                        R$ {{ number_format($t->valor, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhuma transacao no periodo</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} - Controle Financeiro | Total: {{ $transacoes->count() }} transacoes
    </div>
</body>
</html>
