@extends('layouts.app')

@section('page-title', 'Importar Extrato')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card" style="opacity: 1 !important;">
            <div class="card-header bg-light py-2">
                <i class="bi bi-upload"></i> Importar Extrato (CSV)
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Envie um arquivo CSV no formato <strong>Data;Tipo;Descricao;Valor;Categoria</strong>
                    (o mesmo formato gerado em <a href="{{ route('exportar.csv.transacoes') }}">Exportar &rarr; Transações CSV</a>).
                    Exemplo de linha: <code>28/08/2026;Receita;"Salário";3000,00;"Salário"</code>
                </p>

                <form action="{{ route('importacao.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small" for="arquivoImportacao">Arquivo CSV</label>
                        <input type="file" id="arquivoImportacao" name="arquivo" class="form-control" accept=".csv,text/csv" required>
                        @error('arquivo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-eye"></i> Pré-visualizar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
