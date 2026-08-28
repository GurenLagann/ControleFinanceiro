@extends('layouts.app')

@section('page-title', 'Importar Extrato — Pré-visualização')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card mb-3" style="opacity: 1 !important;">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <div>
                    <span class="badge bg-success">{{ count($linhas) }} linha(s) válida(s)</span>
                    @if($erros > 0)
                        <span class="badge bg-danger">{{ $erros }} linha(s) com erro (serão ignoradas)</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('importacao.cancelar') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Cancelar</button>
                    </form>
                    <form action="{{ route('importacao.confirmar') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" {{ count($linhas) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-check-lg"></i> Confirmar Importação
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card" style="opacity: 1 !important;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Categoria</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($linhas as $linha)
                            <tr>
                                <td>{{ $linha['data']->format('d/m/Y') }}</td>
                                <td>
                                    @if($linha['tipo'] === 'receita')
                                        <span class="badge bg-success">Receita</span>
                                    @else
                                        <span class="badge bg-danger">Despesa</span>
                                    @endif
                                </td>
                                <td>{{ $linha['descricao'] }}</td>
                                <td class="{{ $linha['tipo'] === 'receita' ? 'valor-positivo' : 'valor-negativo' }}">
                                    R$ {{ number_format($linha['valor'], 2, ',', '.') }}
                                </td>
                                <td>{{ $linha['categoria'] ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="bi bi-file-earmark-x" aria-hidden="true"></i>
                                        Nenhuma linha válida encontrada no arquivo.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
