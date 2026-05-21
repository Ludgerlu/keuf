{{-- resources/views/stocks/receive.blade.php --}}
@extends('layouts.app')

@section('title', 'Réceptionner — ' . $projet->nom)

@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-box-arrow-in-down me-2" style="color:var(--green);"></i>Réceptionner des matériaux
    </div>
@endsection

@push('styles')
<style>
    /* ── Layout ── */
    .form-page { max-width: 760px; margin: 0 auto; }

    /* ── Hero band ── */
    .action-hero {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
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
        font-size: 1.8rem;
        flex-shrink: 0;
    }
    .action-hero h3 { font-size: 1.25rem; margin-bottom: 0.15rem; }
    .action-hero p  { font-size: 0.8rem; opacity: 0.75; margin: 0; }

    /* ── Form card ── */
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
    .form-section-title i { font-size: 0.9rem; color: var(--green); }

    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .form-label .req { color: var(--red); margin-left: 2px; }

    .form-control, .form-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.6rem 0.9rem;
        font-size: 0.875rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--green);
        box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
    }

    /* stock chip shown when material is selected */
    .stock-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #ecfdf5;
        color: #065f46;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        margin-top: 0.5rem;
    }
    .stock-chip.alerte { background: #ffe2e2; color: var(--red); }

    /* Prix breakdown */
    .prix-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .prix-row .total-label { font-size: 0.78rem; color: #6b7280; }
    .prix-row .total-value { font-size: 1rem; font-weight: 700; color: var(--black-keuf); }

    /* Form footer */
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
        background: var(--green);
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
    .btn-submit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16,185,129,0.25); }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .btn-cancel {
        background: white;
        color: #374151;
        border: 1.5px solid #dee2ed;
        border-radius: 30px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-cancel:hover { border-color: #9ca3af; color: #374151; }

    /* Fournisseur new input */
    #new-fournisseur-wrap {
        margin-top: 0.75rem;
        padding: 1rem;
        background: #f9fafc;
        border-radius: 12px;
        border: 1px dashed #dee2ed;
        display: none;
    }
    #new-fournisseur-wrap.show { display: block; }

    /* Validation styles */
    .form-control.is-invalid, .form-select.is-invalid { border-color: var(--red); }
    .invalid-feedback { font-size: 0.75rem; color: var(--red); margin-top: 0.25rem; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="mb-3" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none">Projets</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projets.show', $projet) }}" class="text-decoration-none">{{ $projet->nom }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stocks.index', $projet) }}" class="text-decoration-none">Stocks</a></li>
        <li class="breadcrumb-item active">Réceptionner</li>
    </ol>
</nav>

<div class="form-page">

    {{-- Hero --}}
    <div class="action-hero">
        <div class="action-hero-icon"><i class="bi bi-box-arrow-in-down"></i></div>
        <div>
            <h3 class="fw-bold font-syne">Réceptionner des matériaux</h3>
            <p>Enregistrez une entrée de stock pour <strong>{{ $projet->nom }}</strong></p>
        </div>
    </div>

    {{-- Flash errors --}}
    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('stocks.receive', $projet) }}" id="receiveForm">
        @csrf

        <div class="form-card">

            {{-- Section 1 : Matériau & quantité --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-box"></i> Matériau & Quantité
                </div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Matériau <span class="req">*</span></label>
                        <select class="form-select @error('materiau_id') is-invalid @enderror"
                                name="materiau_id" id="materiau_id" required onchange="onMaterialChange(this)">
                            <option value="">Sélectionner un matériau…</option>
                            @foreach($materiaux as $mat)
                            <option value="{{ $mat->id }}"
                                    data-unite="{{ $mat->unite }}"
                                    data-stock="{{ $stock[$mat->id]->quantite ?? 0 }}"
                                    data-seuil="{{ $stock[$mat->id]->seuil_alerte ?? 0 }}"
                                    {{ old('materiau_id', $preselectedMateriau ?? '') == $mat->id ? 'selected' : '' }}>
                                {{ $mat->nom }} ({{ $mat->unite }})
                            </option>
                            @endforeach
                        </select>
                        <div id="stock-chip-container"></div>
                        @error('materiau_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Quantité reçue <span class="req">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('quantite') is-invalid @enderror"
                                   name="quantite" id="quantite"
                                   step="0.001" min="0.001"
                                   value="{{ old('quantite') }}"
                                   required oninput="calcTotal()">
                            <span class="input-group-text" id="unite-label"
                                  style="border-radius:0 12px 12px 0; background:#f9fafc; font-size:.8rem; color:#6b7280;">
                                unité(s)
                            </span>
                        </div>
                        @error('quantite') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Section 2 : Prix & document --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-receipt"></i> Prix & Document
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prix unitaire (FCFA)</label>
                        <input type="number"
                               class="form-control @error('prix_unitaire') is-invalid @enderror"
                               name="prix_unitaire" id="prix_unitaire"
                               step="1" min="0"
                               value="{{ old('prix_unitaire') }}"
                               placeholder="0" oninput="calcTotal()">
                        <div class="prix-row" id="total-row" style="display:none;">
                            <span class="total-label">Total estimé :</span>
                            <span class="total-value" id="total-value">—</span>
                        </div>
                        @error('prix_unitaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">N° document (BL / Facture)</label>
                        <input type="text"
                               class="form-control @error('reference_doc') is-invalid @enderror"
                               name="reference_doc"
                               value="{{ old('reference_doc') }}"
                               placeholder="ex: BL-2026-0042">
                        @error('reference_doc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de réception</label>
                        <input type="date"
                               class="form-control"
                               name="date_reception"
                               value="{{ old('date_reception', now()->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            {{-- Section 3 : Fournisseur --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-truck"></i> Fournisseur
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Sélectionner un fournisseur existant</label>
                        <select class="form-select" name="fournisseur_id" id="fournisseur_id"
                                onchange="toggleNewFournisseur(this)">
                            <option value="">→ Saisir un nouveau fournisseur</option>
                            @foreach($fournisseurs ?? [] as $f)
                            <option value="{{ $f->id }}" {{ old('fournisseur_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->nom }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div id="new-fournisseur-wrap" class="{{ old('fournisseur_nom') ? 'show' : '' }}">
                            <label class="form-label">Nom du nouveau fournisseur <span class="req">*</span></label>
                            <input type="text"
                                   class="form-control @error('fournisseur_nom') is-invalid @enderror"
                                   name="fournisseur_nom"
                                   value="{{ old('fournisseur_nom') }}"
                                   placeholder="ex: CIMENCAM, FERRO Cameroun…"
                                   id="fournisseur_nom">
                            @error('fournisseur_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4 : Note --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-chat-left-text"></i> Note interne (optionnel)
                </div>
                <textarea class="form-control" name="note" rows="2"
                          placeholder="Informations complémentaires sur cette réception…">{{ old('note') }}</textarea>
            </div>

            {{-- Footer --}}
            <div class="form-footer">
                <a href="{{ route('stocks.index', $projet) }}" class="btn-cancel">
                    <i class="bi bi-x"></i>Annuler
                </a>
                <button type="submit" class="btn-submit" id="btn-submit">
                    <i class="bi bi-check-lg"></i>Confirmer la réception
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
// ── Material change → show stock chip ──
const stockData = @json($stock->keyBy('materiau_id')->map(fn($s) => ['qte' => $s->quantite, 'seuil' => $s->seuil_alerte, 'unite' => $s->materiau->unite]));

function onMaterialChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const unite = opt.getAttribute('data-unite') || 'unité(s)';
    document.getElementById('unite-label').textContent = unite;

    const matId = sel.value;
    const chip  = document.getElementById('stock-chip-container');
    if (matId && stockData[matId]) {
        const d     = stockData[matId];
        const alert = d.qte <= d.seuil;
        chip.innerHTML = `<span class="stock-chip ${alert ? 'alerte' : ''}">
            <i class="bi bi-${alert ? 'exclamation-triangle' : 'check-circle'}"></i>
            Stock actuel : ${parseFloat(d.qte).toFixed(2)} ${unite}${alert ? ' (⚠ sous seuil)' : ''}
        </span>`;
    } else {
        chip.innerHTML = '';
    }
    calcTotal();
}

function calcTotal() {
    const qte   = parseFloat(document.getElementById('quantite').value)       || 0;
    const prix  = parseFloat(document.getElementById('prix_unitaire').value)  || 0;
    const row   = document.getElementById('total-row');
    const val   = document.getElementById('total-value');
    if (qte > 0 && prix > 0) {
        row.style.display = 'flex';
        val.textContent   = (qte * prix).toLocaleString('fr-FR') + ' FCFA';
    } else {
        row.style.display = 'none';
    }
}

function toggleNewFournisseur(sel) {
    const wrap = document.getElementById('new-fournisseur-wrap');
    const input = document.getElementById('fournisseur_nom');
    if (sel.value === '') {
        wrap.classList.add('show');
        input.required = true;
    } else {
        wrap.classList.remove('show');
        input.required = false;
    }
}

// ── Init if preselected ──
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('materiau_id');
    if (sel.value) onMaterialChange(sel);
    if (!document.getElementById('fournisseur_id').value) {
        document.getElementById('fournisseur_nom').required = true;
    }
});

// ── Prevent double submit ──
document.getElementById('receiveForm').addEventListener('submit', () => {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
});
</script>
@endpush