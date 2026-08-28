@extends('layouts.app')

@section('page-title', 'Relatório de Fluxo de Caixa')

@section('content')

<div class="row g-3">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <small><i class="bi bi-graph-up"></i> Fluxo de Caixa Mensal</small>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="{{ route('relatorios.fluxoCaixa') }}" id="formAnoFluxoCaixa" class="mb-0">
                        <select name="ano" class="form-select form-select-sm" aria-label="Selecionar ano do fluxo de caixa" onchange="document.getElementById('formAnoFluxoCaixa').submit()">
                            @foreach ($anosDisponiveis as $anoOpcao)
                                <option value="{{ $anoOpcao }}" {{ $anoOpcao === $anoFluxo ? 'selected' : '' }}>{{ $anoOpcao }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('exportar.pdf.fluxoCaixa', ['ano' => $anoFluxo]) }}" class="btn btn-sm btn-outline-secondary" title="Exportar PDF" aria-label="Exportar fluxo de caixa em PDF">
                        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('exportar.csv.fluxoCaixa', ['ano' => $anoFluxo]) }}" class="btn btn-sm btn-outline-secondary" title="Exportar CSV" aria-label="Exportar fluxo de caixa em CSV">
                        <i class="bi bi-filetype-csv" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-3">
                <canvas id="chartFluxoCaixa" height="90" role="img" aria-label="Gráfico de fluxo de caixa: receitas e despesas em barras, saldo em linha, mês a mês de {{ $anoFluxo }}"></canvas>
                <div class="table-responsive mt-4">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th class="text-end">Receitas</th>
                                <th class="text-end">Despesas</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end">Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fluxoCaixa['labels'] as $i => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-end valor-positivo">R$ {{ number_format($fluxoCaixa['receitas'][$i], 2, ',', '.') }}</td>
                                    <td class="text-end valor-negativo">R$ {{ number_format($fluxoCaixa['despesas'][$i], 2, ',', '.') }}</td>
                                    <td class="text-end {{ $fluxoCaixa['saldo'][$i] >= 0 ? 'valor-positivo' : 'valor-negativo' }}">R$ {{ number_format($fluxoCaixa['saldo'][$i], 2, ',', '.') }}</td>
                                    <td class="text-end {{ $fluxoCaixa['saldoAcumulado'][$i] >= 0 ? 'valor-positivo' : 'valor-negativo' }}">R$ {{ number_format($fluxoCaixa['saldoAcumulado'][$i], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.color = 'rgba(255,255,255,0.5)';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
    Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";

    const fluxoCaixaMeses = @json($fluxoCaixa['labels']);
    const fluxoCaixaReceitas = @json($fluxoCaixa['receitas']);
    const fluxoCaixaDespesas = @json($fluxoCaixa['despesas']);
    const fluxoCaixaSaldo = @json($fluxoCaixa['saldo']);

    new Chart(document.getElementById('chartFluxoCaixa'), {
        data: {
            labels: fluxoCaixaMeses,
            datasets: [
                { type: 'bar', label: 'Receitas', data: fluxoCaixaReceitas, backgroundColor: 'rgba(0,255,136,0.6)', borderRadius: 4, order: 2 },
                { type: 'bar', label: 'Despesas', data: fluxoCaixaDespesas, backgroundColor: 'rgba(255,71,87,0.6)', borderRadius: 4, order: 2 },
                { type: 'line', label: 'Saldo', data: fluxoCaixaSaldo, borderColor: '#3742fa', borderWidth: 2, tension: 0.4, fill: false, pointRadius: 2, pointHoverRadius: 5, borderDash: [5, 5], order: 1 }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, boxHeight: 10, padding: 14 } } },
            scales: { y: { beginAtZero: false, grid: { color: 'rgba(255,255,255,0.05)' } } }
        }
    });
</script>
@endsection
