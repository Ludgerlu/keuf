{{-- resources/views/stocks/transfer.blade.php --}}
@extends('layouts.app')

@section('title', 'Transférer — ' . $projet->nom)

@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-arrow-left-right me-2" style="color:var(--blue-1);"></i>Transférer des matériaux
    </div>
@endsection

@push('styles')
<style>
    .form-page { max-width: 780px; margin: 0 auto; }

    /* Hero */
    .action-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
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

    /* Transfer flow diagram */
    .transfer-flow {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
    }
    .flow-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.2rem;
        flex: 1;
        text-align: center;
    }
    .flow-node .label { font-size: 0.65rem; text-transform: uppercase; color: #6b7280; letter-spacing: 0.4px; }
    .flow-node .value { font-weight: 700; color: var(--blue-1); font-size: 0.85rem; }
    .flow-arrow { color: var(--blue-1); font-size: 1.2rem; flex-shrink: 0; }

    /* Form card */
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
    .form-section-title i { font-size: 0.9rem; color: var(--blue-1); }

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
        border-color: var(--blue-1);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }

    /* Stock availability */
    .avail-bar-wrap { margin-top: 0.6rem; }
    .avail-label { font-size: 0.73rem; color: #6b7280; margin-bottom: 0.3rem; }
    .avail-bar { height: 8px; background: #e5e7eb; border-radius: 30px; overflow: hidden; }
    .avail-fill { height: 100%; border-radius: 30px; transition: width 0.3s; }
    .avail-numbers { display: flex; justify-content: space-between; font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem; }

    /* Warning chip */
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

    /* Footer */
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
        background: var(--blue-1);
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
    .btn-submit:hover { background: var(--blue-dark); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.25); }
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
        <li class="breadcrumb-item active">Transférer</li>
    </ol>
</nav>

<div class="form-page">

    <div class="action-hero">
        <div class="action-hero-icon"><i class="bi bi-arrow-left-right"></i></div>
        <div>
            <h3 class="fw-bold font-syne">Transférer des matériaux</h3>
            <p>Déplacer du stock de <strong>{{ $projet->nom }}</strong> vers un autre chantier</p>
        </div>
    </div>

    {{-- Flow preview --}}
    <div class="transfer-flow">
        <div class="flow-node">
            <span class="label">Source</span>
            <span class="value">{{ Str::limit($projet->nom, 22) }}</span>
        </div>
        <i class="bi bi-arrow-right flow-arrow"></i>
        <div class="flow-node">
            <span class="label">Matériau</span>
            <span class="value" id="flow-mat">—</span>
        </div>
        <i class="bi bi-arrow-right flow-arrow"></i>
        <div class="flow-node">
            <span class="label">Destination</span>
            <span class="value" id="flow-dest">—</span>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Veuillez corriger les erreurs :</strong>
        <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('stocks.transfer', $projet) }}" id="transferForm">
        @csrf

        <div class="form-card">

            {{-- Matériau & Quantité --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-box"></i> Matériau & Quantité</div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Matériau <span class="req">*</span></label>
                        <select class="form-select @error('materiau_id') is-invalid @enderror"
                                name="materiau_id" id="materiau_id" required
                                onchange="onMaterialChange(this)">
                            <option value="">Sélectionner un matériau…</option>
                            @foreach($stock as $item)
                            <option value="{{ $item->materiau_id }}"
                                    data-max="{{ $item->quantite }}"
                                    data-seuil="{{ $item->seuil_alerte }}"
                                    data-unite="{{ $item->materiau->unite }}"
                                    {{ old('materiau_id', $preselectedMateriau ?? '') == $item->materiau_id ? 'selected' : '' }}>
                                {{ $item->materiau->nom }}
                                (Dispo: {{ number_format($item->quantite, 2) }} {{ $item->materiau->unite }})
                            </option>
                            @endforeach
                        </select>
                        @error('materiau_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                        {{-- Availability bar --}}
                        <div class="avail-bar-wrap" id="avail-wrap" style="display:none;">
                            <div class="avail-label">Stock disponible</div>
                            <div class="avail-bar">
                                <div class="avail-fill" id="avail-fill" style="width:100%; background:var(--green);"></div>
                            </div>
                            <div class="avail-numbers">
                                <span id="avail-current">0</span>
                                <span id="avail-seuil">Seuil : 0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Quantité à transférer <span class="req">*</span></label>
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
                            <i class="bi bi-exclamation-triangle"></i>
                            Quantité supérieure au stock disponible
                        </div>
                        @error('quantite') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Destination --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-geo-alt"></i> Chantier destinataire</div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Projet destination <span class="req">*</span></label>
                        <select class="form-select @error('dest_projet_id') is-invalid @enderror"
                                name="dest_projet_id" id="dest_projet"
                                required onchange="onDestChange(this)">
                            <option value="">Sélectionner un chantier…</option>
                            @foreach($activeProjects as $p)
                            <option value="{{ $p->id }}" {{ old('dest_projet_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('dest_projet_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Phase destinataire</label>
                        <select class="form-select" name="phase_id" id="phase_id">
                            <option value="">Toutes phases (optionnel)</option>
                            @foreach($phases as $ph)
                            <option value="{{ $ph->id }}" {{ old('phase_id') == $ph->id ? 'selected' : '' }}>
                                {{ $ph->nom }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-chat-left-text"></i> Note (optionnel)</div>
                <textarea class="form-control" name="note" rows="2"
                          placeholder="Motif du transfert, informations complémentaires…">{{ old('note') }}</textarea>
            </div>

            <div class="form-footer">
                <a href="{{ route('stocks.index', $projet) }}" class="btn-cancel">
                    <i class="bi bi-x"></i>Annuler
                </a>
                <button type="submit" class="btn-submit" id="btn-submit">
                    <i class="bi bi-arrow-left-right"></i>Confirmer le transfert
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
    const max   = parseFloat(opt.getAttribute('data-max')) || 0;
    const seuil = parseFloat(opt.getAttribute('data-seuil')) || 0;
    const unite = opt.getAttribute('data-unite') || 'unité(s)';
    currentMax  = max;

    document.getElementById('unite-label').textContent = unite;
    document.getElementById('flow-mat').textContent    = sel.value ? opt.text.split('(')[0].trim() : '—';

    // Availability bar
    const wrap = document.getElementById('avail-wrap');
    const fill = document.getElementById('avail-fill');
    if (sel.value) {
        wrap.style.display = 'block';
        const pct = seuil > 0 ? Math.min(100, (max / seuil) * 100) : 100;
        fill.style.width      = pct + '%';
        fill.style.background = max <= seuil ? 'var(--red)' : 'var(--green)';
        document.getElementById('avail-current').textContent = max.toFixed(2) + ' ' + unite;
        document.getElementById('avail-seuil').textContent   = 'Seuil : ' + seuil.toFixed(2);
    } else {
        wrap.style.display = 'none';
    }

    checkQty();
}

function checkQty() {
    const qty  = parseFloat(document.getElementById('quantite').value) || 0;
    const warn = document.getElementById('qty-warn');
    warn.classList.toggle('show', currentMax > 0 && qty > currentMax);
}

function onDestChange(sel) {
    const opt  = sel.options[sel.selectedIndex];
    document.getElementById('flow-dest').textContent = sel.value ? opt.text.trim() : '—';
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('materiau_id');
    if (sel.value) onMaterialChange(sel);
    const dest = document.getElementById('dest_projet');
    if (dest.value) onDestChange(dest);
});

document.getElementById('transferForm').addEventListener('submit', function(e) {
    const qty = parseFloat(document.getElementById('quantite').value) || 0;
    if (currentMax > 0 && qty > currentMax) { e.preventDefault(); return; }
    const btn = document.getElementById('btn-submit');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Transfert…';
});
</script>
@endpush