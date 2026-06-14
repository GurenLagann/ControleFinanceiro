{{--
    $categorias : Collection<Categoria> com campos nome, cor, icone
    $inputId    : ID do <input type="hidden"> que armazenará o valor selecionado
--}}
<input type="hidden" name="categoria" id="{{ $inputId }}">
<div class="categoria-pills-grid row g-1" style="max-height: 180px; overflow-y: auto; padding-right: 2px;">
    @forelse($categorias as $cat)
        <div class="col-6 col-sm-4">
            <button
                type="button"
                class="categoria-pill w-100 d-flex align-items-center gap-2 position-relative"
                role="button"
                tabindex="0"
                aria-pressed="false"
                aria-label="Categoria: {{ $cat->nome }}"
                data-nome="{{ $cat->nome }}"
                data-cor="{{ $cat->cor }}"
                data-target="{{ $inputId }}"
                style="background:{{ $cat->cor }}26; border:1px solid {{ $cat->cor }}66; border-radius:8px; min-height:44px; padding:6px 10px; cursor:pointer; color:#F8FAFC; font-size:11px; transition:all 150ms ease; text-align:left;"
            >
                <i class="bi bi-{{ $cat->icone }}" style="font-size:14px; color:{{ $cat->cor }}; flex-shrink:0;"></i>
                <span style="line-height:1.2;">{{ $cat->nome }}</span>
                <i class="bi bi-check-lg categoria-check" style="display:none; position:absolute; top:3px; right:5px; font-size:10px; color:{{ $cat->cor }};"></i>
            </button>
        </div>
    @empty
        <div class="col-12">
            <small class="text-muted">Nenhuma categoria cadastrada. <a href="{{ route('categorias.index') }}">Criar categorias</a></small>
        </div>
    @endforelse
</div>
