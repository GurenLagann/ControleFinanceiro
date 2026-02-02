<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio Financeiro - {{ $periodo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            color: #666;
        }
        .resumo {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .resumo-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .resumo-item.receitas {
            background-color: #d4edda;
        }
        .resumo-item.despesas {
            background-color: #f8d7da;
        }
        .resumo-item.saldo {
            background-color: #cce5ff;
        }
        .resumo-item h3 {
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #666;
        }
        .resumo-item .valor {
            font-size: 18px;
            font-weight: bold;
        }
        .resumo-item.receitas .valor { color: #155724; }
        .resumo-item.despesas .valor { color: #721c24; }
        .resumo-item.saldo .valor { color: #004085; }

        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            font-size: 14px;
            background-color: #f5f5f5;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            font-size: 11px;
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
            font-size: 10px;
            color: #999;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        .categoria-row {
            display: table;
            width: 100%;
        }
        .categoria-col {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        .small-table td, .small-table th {
            padding: 5px 8px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatorio Financeiro</h1>
        <p>{{ $periodo }}</p>
    </div>

    <div class="resumo">
        <div class="resumo-item receitas">
            <h3>Total Receitas</h3>
            <div class="valor">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
            <small>{{ $qtdReceitas }} registros</small>
        </div>
        <div class="resumo-item despesas">
            <h3>Total Despesas</h3>
            <div class="valor">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
            <small>{{ $qtdDespesas }} registros</small>
        </div>
        <div class="resumo-item saldo">
            <h3>Saldo</h3>
            <div class="valor">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Resumo por Categoria</h2>
        <div class="categoria-row">
            <div class="categoria-col">
                <table class="small-table">
                    <thead>
                        <tr>
                            <th>Categoria (Receitas)</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receitasPorCategoria as $cat => $valor)
                            <tr>
                                <td>{{ $cat ?: 'Sem categoria' }}</td>
                                <td class="text-right receita">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center">Nenhuma receita</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="categoria-col">
                <table class="small-table">
                    <thead>
                        <tr>
                            <th>Categoria (Despesas)</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($despesasPorCategoria as $cat => $valor)
                            <tr>
                                <td>{{ $cat ?: 'Sem categoria' }}</td>
                                <td class="text-right despesa">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center">Nenhuma despesa</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Transacoes do Periodo</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Data</th>
                    <th style="width: 70px;">Tipo</th>
                    <th>Descricao</th>
                    <th style="width: 100px;">Categoria</th>
                    <th style="width: 100px;" class="text-right">Valor</th>
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
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} - Controle Financeiro
    </div>
</body>
</html>
