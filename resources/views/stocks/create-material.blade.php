{{-- resources/views/stocks/create-material.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouveau matériau')

@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-plus-circle me-2" style="color:var(--yellow-keuf);"></i>Nouveau matériau
    </div>
@endsection

@push('styles')
<style>
    .form-page { max-width: 680px; margin: 0 auto; }

    .action-hero {
        background: var(--black-keuf);
        color: white;
        border-radius: 24px;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .action-hero-icon {
        width: 60px; height: 60px;
        background: rgba(215,174,64,0.18);
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; flex-shrink: 0;
    }
    .action-hero h3 { font-size: 1.25rem; margin-bottom: 0.15rem; }
    .action-hero p  { font-size: 0.8rem; opacity: 0.75; margin: 0; }

    .form-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f2f6;
    }
    .form-section:last-child { border-bottom: none; }
    .form-section-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #9ca3af;
        font-weight: 700;
        margin-bottom: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i { font-size: 0.9rem; color: var(--yellow-keuf); }

    .form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
    .form-label .req { color: var(--red); }

    .form-control, .form-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.6rem 0.9rem;
        font-size: 0.875rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--yellow-keuf);
        box-shadow: 0 0 0 3px rgba(215,174,64,0.15);
    }

    /* Unite pills */
    .unite-pills { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.6rem; }
    .unite-pill {
        padding: 0.3rem 0.9rem;
        border-radius: 30px;
        border: 1.5px solid #dee2ed;
        font-size: 0.78rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        background: white;
        color: #374151;
    }
    .unite-pill:hover { border-color: var(--yellow-keuf); color: var(--black-keuf); }
    .unite-pill.active { background: var(--yellow-keuf); border-color: var(--yellow-keuf); color: black; font-weight: 700; }

    /* Preview card */
    .preview-card {
        background: #f9fafc;
        border: 1px solid #edf0f5;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-top: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .preview-icon {
        width: 44px; height: 44px;
        background: #e9ecfc;
        color: var(--blue-1);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }
    .preview-nom { font-weight: 700; font-size: 0.95rem; color: var(--black-keuf); }
    .preview-meta { font-size: 0.75rem; color: #9ca3af; }

    /* Seuil helper */
    .seuil-hint {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 0.6rem 0.9rem;
        font-size: 0.75rem;
        color: #78350f;
        margin-top: 0.5rem;
        display: flex;
        gap: 0.4rem;
        align-items: flex-start;
    }

    /* Catégorie toggle */
    .cat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    @media(max-width:480px) { .cat-grid { grid-template-columns: repeat(2, 1fr); } }

    .cat-option {
        border: 1.5px solid #dee2ed;
        border-radius: 12px;
        padding: 0.65rem 0.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .cat-option:hover { border-color: var(--yellow-keuf); background: #fefae6; }
    .cat-option.selected { border-color: var(--yellow-keuf); background: var(--yellow-keuf); color: black; font-weight: 700; }
    .cat-option i { display: block; font-size: 1.25rem; margin-bottom: 0.25rem; }

    .form-footer {
        padding: 1.25rem 1.5rem;
        background: #fafbfe;
        border-top: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .btn-submit {
        background: var(--yellow-keuf);
        color: black;
        border: none;
        border-radius: 30px;
        padding: 0.6rem 1.75rem;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.18s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-submit:hover { background: #c29e36; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(215,174,64,0.3); }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .btn-cancel {
        background: white; color: #374151; border: 1.5px solid #dee2ed;
        border-radius: 30px; padding: 0.6rem 1.5rem; font-weight: 600;
        font-size: 0.875rem; text-decoration: none; transition: all 0.15s;
        display: inline-flex; align-items: center; gap: 0.5rem;
    }
    .btn-cancel:hover { border-color: #9ca3af; color: #374151; }
</style>
@endpush

@section('content')

<nav class="mb-3" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none">Projets</a></li>
        @isset($projet)
        <li class="breadcrumb-item"><a href="{{ route('stocks.index', $projet) }}" class="text-decoration-none">Stocks</a></li>
        @endisset
        <li class="breadcrumb-item active">Nouveau matériau</li>
    </ol>
</nav>

<div class="form-page">

    <div class="action-hero">
        <div class="action-hero-icon"><i class="bi bi-plus-circle" style="color:var(--yellow-keuf);"></i></div>
        <div>
            <h3 class="fw-bold font-syne">Nouveau matériau</h3>
            <p>Ajoutez un matériau au catalogue global KEUF Engineering</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Erreurs :</strong>
        <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('stocks.materiau.store') }}" id="matForm">
        @csrf

        <div class="form-card">

            {{-- Identité --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-tag"></i> Identification</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom du matériau <span class="req">*</span></label>
                        <input type="text"
                               class="form-control @error('nom') is-invalid @enderror"
                               name="nom" id="nom"
                               value="{{ old('nom') }}"
                               placeholder="ex: Ciment Portland CEM II, Fer à béton Ø12…"
                               required oninput="updatePreview()">
                        @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Unité --}}
                    <div class="col-12">
                        <label class="form-label">Unité de mesure <span class="req">*</span></label>
                        <select class="form-select @error('unite') is-invalid @enderror"
                                name="unite" id="unite" required onchange="onUniteChange(this)">
                            <option value="">Sélectionner…</option>
                            <option value="sac"    {{ old('unite') == 'sac'    ? 'selected' : '' }}>Sac</option>
                            <option value="barre"  {{ old('unite') == 'barre'  ? 'selected' : '' }}>Barre</option>
                            <option value="m³"     {{ old('unite') == 'm³'     ? 'selected' : '' }}>m³ (mètre cube)</option>
                            <option value="mètre"  {{ old('unite') == 'mètre'  ? 'selected' : '' }}>Mètre linéaire</option>
                            <option value="pièce"  {{ old('unite') == 'pièce'  ? 'selected' : '' }}>Pièce</option>
                            <option value="kg"     {{ old('unite') == 'kg'     ? 'selected' : '' }}>Kg</option>
                            <option value="tonne"  {{ old('unite') == 'tonne'  ? 'selected' : '' }}>Tonne</option>
                            <option value="litre"  {{ old('unite') == 'litre'  ? 'selected' : '' }}>Litre</option>
                            <option value="rouleau"{{ old('unite') == 'rouleau'? 'selected' : '' }}>Rouleau</option>
                        </select>
                        <div class="unite-pills" id="unite-pills">
                            <button type="button" class="unite-pill {{ old('unite')=='sac'   ?'active':'' }}" onclick="quickUnite('sac')">Sac</button>
                            <button type="button" class="unite-pill {{ old('unite')=='barre' ?'active':'' }}" onclick="quickUnite('barre')">Barre</button>
                            <button type="button" class="unite-pill {{ old('unite')=='m³'    ?'active':'' }}" onclick="quickUnite('m³')">m³</button>
                            <button type="button" class="unite-pill {{ old('unite')=='mètre' ?'active':'' }}" onclick="quickUnite('mètre')">Mètre</button>
                            <button type="button" class="unite-pill {{ old('unite')=='pièce' ?'active':'' }}" onclick="quickUnite('pièce')">Pièce</button>
                            <button type="button" class="unite-pill {{ old('unite')=='kg'    ?'active':'' }}" onclick="quickUnite('kg')">Kg</button>
                        </div>
                        @error('unite') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Live preview --}}
                <div class="preview-card" id="preview-card" style="{{ old('nom') ? '' : 'display:none;' }}">
                    <div class="preview-icon"><i class="bi bi-box"></i></div>
                    <div>
                        <div class="preview-nom" id="preview-nom">{{ old('nom', '—') }}</div>
                        <div class="preview-meta" id="preview-meta">
                            {{ old('unite') ? 'Unité : ' . old('unite') : 'Sélectionnez une unité' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catégorie --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-grid-3x3-gap"></i> Catégorie</div>
                <input type="hidden" name="categorie" id="categorie" value="{{ old('categorie') }}">
                <div class="cat-grid">
                    @php
                        $cats = [
                            ['value' => 'Gros œuvre',  'icon' => 'bi-bricks',           'label' => 'Gros œuvre'],
                            ['value' => 'Structure',   'icon' => 'bi-columns-gap',       'label' => 'Structure'],
                            ['value' => 'Finition',    'icon' => 'bi-paint-bucket',      'label' => 'Finition'],
                            ['value' => 'Électricité', 'icon' => 'bi-lightning-charge',  'label' => 'Électricité'],
                            ['value' => 'Plomberie',   'icon' => 'bi-pipe',              'label' => 'Plomberie'],
                            ['value' => 'Autre',       'icon' => 'bi-three-dots',        'label' => 'Autre'],
                        ];
                    @endphp
                    @foreach($cats as $cat)
                    <div class="cat-option {{ old('categorie') == $cat['value'] ? 'selected' : '' }}"
                         onclick="selectCat(this, '{{ $cat['value'] }}')">
                        <i class="bi {{ $cat['icon'] }}"></i>
                        {{ $cat['label'] }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Stock initial & seuil --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-graph-up"></i> Stock & Seuil</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Quantité initiale</label>
                        <input type="number"
                               class="form-control"
                               name="quantite_initiale"
                               value="{{ old('quantite_initiale', 0) }}"
                               step="0.001" min="0">
                        <div class="seuil-hint">
                            <i class="bi bi-info-circle-fill" style="color:var(--orange); flex-shrink:0;"></i>
                            Laissez à 0 si aucune réception n'a encore eu lieu.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seuil d'alerte <span class="req">*</span></label>
                        <input type="number"
                               class="form-control @error('seuil_alerte') is-invalid @enderror"
                               name="seuil_alerte"
                               value="{{ old('seuil_alerte') }}"
                               step="0.001" min="0"
                               required placeholder="ex: 50">
                        <div class="seuil-hint">
                            <i class="bi bi-bell-fill" style="color:var(--orange); flex-shrink:0;"></i>
                            Une alerte sera déclenchée quand le stock passe sous ce seuil.
                        </div>
                        @error('seuil_alerte') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ isset($projet) ? route('stocks.index', $projet) : route('projets.index') }}"
                   class="btn-cancel">
                    <i class="bi bi-x"></i>Annuler
                </a>
                <button type="submit" class="btn-submit" id="btn-submit">
                    <i class="bi bi-check-lg"></i>Créer le matériau
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updatePreview() {
    const nom   = document.getElementById('nom').value.trim();
    const unite = document.getElementById('unite').value;
    const card  = document.getElementById('preview-card');
    if (nom) {
        card.style.display = 'flex';
        document.getElementById('preview-nom').textContent  = nom;
        document.getElementById('preview-meta').textContent = unite ? 'Unité : ' + unite : 'Sélectionnez une unité';
    } else {
        card.style.display = 'none';
    }
}

function onUniteChange(sel) {
    // Sync pills
    document.querySelectorAll('.unite-pill').forEach(p => {
        p.classList.toggle('active', p.textContent.trim().toLowerCase() === sel.value.toLowerCase());
    });
    updatePreview();
}

function quickUnite(val) {
    const sel = document.getElementById('unite');
    sel.value = val;
    document.querySelectorAll('.unite-pill').forEach(p => {
        p.classList.toggle('active', p.textContent.trim().toLowerCase() === val.toLowerCase());
    });
    updatePreview();
}

function selectCat(el, val) {
    document.querySelectorAll('.cat-option').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('categorie').value = val;
}

document.getElementById('matForm').addEventListener('submit', () => {
    const btn = document.getElementById('btn-submit');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création…';
});
</script>
@endpush