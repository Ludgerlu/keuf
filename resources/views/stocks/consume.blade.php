{{-- resources/views/stocks/consume.blade.php --}}
@extends('layouts.app')

@section('title', 'Consommer — ' . $projet->nom)

@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-fire me-2" style="color:var(--orange);"></i>Consommer des matériaux
    </div>
@endsection

@push('styles')
<style>
    .form-page { max-width: 720px; margin: 0 auto; }

    .action-hero {
        background: linear-gradient(135deg, #78350f 0%, #b45309 100%);
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
        background: rgba(255,255,255,0.12);
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; flex-shrink: 0;
    }
    .action-hero h3 { font-size: 1.25rem; margin-bottom: 0.15rem; }
    .action-hero p  { font-size: 0.8rem; opacity: 0.75; margin: 0; }

    .info-banner {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 0.85rem 1.1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: #78350f;
    }
    .info-banner i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; color: var(--orange); }

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
    .form-section-title i { font-size: 0.9rem; color: var(--orange); }

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
        border-color: var(--orange);
        box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
    }

    .stock-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        margin-top: 0.5rem;
    }
    .stock-pill.ok     { background: #ecfdf5; color: var(--green); }
    .stock-pill.alerte { background: #ffe2e2; color: var(--red); }
    .stock-pill.vide   { background: #f1f5f9; color: #64748b; }

    .qty-warn {
        display: none;
        align-items: center;
        gap: 0.4rem;
        background: #ffe2e2;
        color: var(--red);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        margin-top: 0.5rem;
    }
    .qty-warn.show { display: inline-flex; }

    /* Phase cards */
    .phase-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.15s;
        margin-bottom: 0.5rem;
        background: white;
    }
    .phase-option:hover { border-color: var(--orange); background: #fffbeb; }
    .phase-option input[type="radio"] { accent-color: var(--orange); flex-shrink: 0; }
    .phase-option.selected { border-color: var(--orange); background: #fffbeb; }
    .phase-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .phase-label { font-size: 0.85rem; font-weight: 500; }
    .phase-badge {
        font-size: 0.65rem; font-weight: 600; padding: 0.15rem 0.6rem;
        border-radius: 20px; margin-left: auto; white-space: nowrap;
    }
    .phase-badge.en-cours    { background: #e2eafd; color: var(--blue-1); }
    .phase-badge.termine     { background: #e2f3e4; color: var(--green); }
    .phase-badge.non-demarre { background: #f1f5f9; color: #64748b; }

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
        background: var(--orange);
        color: white;
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
    .btn-submit:hover { background: #d97706; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(245,158,11,0.25); }
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
        <li class="breadcrumb-item"><a href="{{ route('projets.show', $projet) }}" class="text-decoration-none">{{ $projet->nom }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stocks.index', $projet) }}" class="text-decoration-none">Stocks</a></li>
        <li class="breadcrumb-item active">Consommer</li>
    </ol>
</nav>

<div class="form-page">

    <div class="action-hero">
        <div class="action-hero-icon"><i class="bi bi-fire"></i></div>
        <div>
            <h3 class="fw-bold font-syne">Consommer des matériaux</h3>
            <p>Enregistrez une utilisation de stock pour <strong>{{ $projet->nom }}</strong></p>
        </div>
    </div>

    <div class="info-banner">
        <i class="bi bi-info-circle-fill"></i>
        <span>La consommation sera <strong>déduite du stock du chantier</strong>. Assurez-vous que la quantité est correcte avant de confirmer.</span>
    </div>

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Erreurs :</strong>
        <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('stocks.consume', $projet) }}" id="consumeForm">
        @csrf

        <div class="form-card">

            {{-- Matériau & Quantité --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-box"></i> Matériau & Quantité</div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Matériau <span class="req">*</span></label>
                        <select class="form-select @error('materiau_id') is-invalid @enderror"
                                name="materiau_id" id="materiau_id"
                                required onchange="onMaterialChange(this)">
                            <option value="">Sélectionner un matériau…</option>
                            @foreach($stock as $item)
                            <option value="{{ $item->materiau_id }}"
                                    data-max="{{ $item->quantite }}"
                                    data-seuil="{{ $item->seuil_alerte }}"
                                    data-unite="{{ $item->materiau->unite }}"
                                    {{ old('materiau_id', $preselectedMateriau ?? '') == $item->materiau_id ? 'selected' : '' }}>
                                {{ $item->materiau->nom }}
                                ({{ number_format($item->quantite, 2) }} {{ $item->materiau->unite }})
                            </option>
                            @endforeach
                        </select>
                        <div id="stock-pill-container"></div>
                        @error('materiau_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Quantité consommée <span class="req">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('quantite') is-invalid @enderror"
                                   name="quantite" id="quantite"
                                   step="0.001" min="0.001"
                                   value="{{ old('quantite') }}"
                                   required oninput="checkQty()">
                            <span class="input-group-text" id="unite-label"
                                  style="border-radius:0 12px 12px 0; background:#f9fafc; font-size:.8rem; color:#6b7280;">
                                unité(s)
                            </span>
                        </div>
                        <div class="qty-warn" id="qty-warn">
                            <i class="bi bi-exclamation-triangle"></i>Stock insuffisant
                        </div>
                        @error('quantite') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Phase --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-diagram-3"></i> Phase concernée</div>
                @if($phases->isEmpty())
                    <p class="text-secondary small">Aucune phase disponible pour ce projet.</p>
                    <input type="hidden" name="phase_id" value="">
                @else
                    @foreach($phases as $phase)
                    @php
                        $phClass = match($phase->statut ?? 'non_demarre') {
                            'en_cours'  => ['label' => 'En cours',    'badge' => 'en-cours',    'color' => 'var(--blue-1)'],
                            'termine'   => ['label' => 'Terminé',     'badge' => 'termine',     'color' => 'var(--green)'],
                            default     => ['label' => 'Non démarré', 'badge' => 'non-demarre', 'color' => '#cbd5e1'],
                        };
                    @endphp
                    <label class="phase-option {{ old('phase_id') == $phase->id ? 'selected' : '' }}"
                           onclick="selectPhase(this)">
                        <input type="radio" name="phase_id" value="{{ $phase->id }}"
                               {{ old('phase_id') == $phase->id ? 'checked' : '' }}
                               required>
                        <div class="phase-dot" style="background: {{ $phClass['color'] }};"></div>
                        <span class="phase-label">{{ $phase->nom }}</span>
                        <span class="phase-badge {{ $phClass['badge'] }}">{{ $phClass['label'] }}</span>
                    </label>
                    @endforeach
                    @error('phase_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @endif
            </div>

            {{-- Note --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-chat-left-text"></i> Note (optionnel)</div>
                <textarea class="form-control" name="note" rows="2"
                          placeholder="ex: Coulage dalle RDC, utilisation prévue dans les plans…">{{ old('note') }}</textarea>
            </div>

            <div class="form-footer">
                <a href="{{ route('stocks.index', $projet) }}" class="btn-cancel">
                    <i class="bi bi-x"></i>Annuler
                </a>
                <button type="submit" class="btn-submit" id="btn-submit">
                    <i class="bi bi-fire"></i>Confirmer la consommation
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let currentMax = 0;

function onMaterialChange(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const max   = parseFloat(opt.getAttribute('data-max'))   || 0;
    const seuil = parseFloat(opt.getAttribute('data-seuil')) || 0;
    const unite = opt.getAttribute('data-unite') || 'unité(s)';
    currentMax  = max;

    document.getElementById('unite-label').textContent = unite;

    const container = document.getElementById('stock-pill-container');
    if (sel.value) {
        let cls = max <= 0 ? 'vide' : (max <= seuil ? 'alerte' : 'ok');
        let ico = max <= 0 ? 'slash-circle' : (max <= seuil ? 'exclamation-triangle' : 'check-circle');
        container.innerHTML = `<span class="stock-pill ${cls}">
            <i class="bi bi-${ico}"></i> Stock : ${max.toFixed(2)} ${unite}${max <= seuil ? ' (⚠ sous seuil)' : ''}
        </span>`;
    } else {
        container.innerHTML = '';
    }
    checkQty();
}

function checkQty() {
    const qty  = parseFloat(document.getElementById('quantite').value) || 0;
    const warn = document.getElementById('qty-warn');
    warn.classList.toggle('show', currentMax > 0 && qty > currentMax);
}

function selectPhase(label) {
    document.querySelectorAll('.phase-option').forEach(l => l.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input').checked = true;
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('materiau_id');
    if (sel.value) onMaterialChange(sel);
});

document.getElementById('consumeForm').addEventListener('submit', function(e) {
    const qty = parseFloat(document.getElementById('quantite').value) || 0;
    if (currentMax > 0 && qty > currentMax) { e.preventDefault(); return; }
    const btn = document.getElementById('btn-submit');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
});
</script>
@endpush