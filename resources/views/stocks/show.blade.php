{{-- resources/views/stocks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des stocks - ' . $projet->nom)
@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-box-seam me-2" style="color:var(--yellow-keuf);"></i>
        Gestion des stocks
    </div>
@endsection

@push('styles')
<style>
    /* ========== KPI Cards ========== */
    .kpi-card {
        background: white;
        border-radius: 20px;
        padding: 1.25rem;
        position: relative;
        overflow: hidden;
        transition: all 0.2s;
        border: 1px solid #edf0f5;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .kpi-icon {
        position: absolute;
        right: 1.25rem;
        top: 1.25rem;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: rgba(0,0,0,0.05);
    }
    .kpi-icon.red { background: rgba(220,38,38,0.1); color: #DC2626; }
    .kpi-icon.green { background: rgba(5,150,105,0.1); color: #059669; }
    .kpi-icon.blue { background: rgba(37,99,235,0.1); color: #2563EB; }
    .kpi-icon.orange { background: rgba(217,119,6,0.1); color: #D97706; }
    .kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6B7280;
        margin-bottom: 0.5rem;
    }
    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        font-family: 'Syne', sans-serif;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    .kpi-sub {
        color: #9CA3AF;
        font-size: 0.7rem;
    }

    /* ========== Stock Table ========== */
    .stock-table-container {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        overflow: hidden;
    }
    .stock-table {
        margin-bottom: 0;
    }
    .stock-table th {
        background: #fafbfe;
        padding: 1rem 1rem;
        font-weight: 600;
        font-size: 0.8rem;
        color: #374151;
        border-bottom: 1px solid #edf0f5;
    }
    .stock-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f6;
    }
    .stock-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Progress bar */
    .prog-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        width: 120px;
    }
    .prog-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s;
    }
    
    /* Badges */
    .stock-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-weight: 500;
    }
    .stock-badge.alerte {
        background: rgba(220,38,38,0.1);
        color: #DC2626;
    }
    .stock-badge.ok {
        background: rgba(5,150,105,0.1);
        color: #059669;
    }
    .stock-badge.critique {
        background: rgba(220,38,38,0.2);
        color: #DC2626;
        font-weight: 600;
    }
    
    /* Type badges for movements */
    .type-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-weight: 500;
        display: inline-block;
    }
    .type-badge.entree, .type-badge.reception {
        background: rgba(5,150,105,0.1);
        color: #059669;
    }
    .type-badge.sortie, .type-badge.consommation {
        background: rgba(220,38,38,0.1);
        color: #DC2626;
    }
    .type-badge.transfert {
        background: rgba(37,99,235,0.1);
        color: #2563EB;
    }
    .type-badge.retour {
        background: rgba(217,119,6,0.1);
        color: #D97706;
    }
    
    /* Buttons */
    .btn-primary-custom {
        background: #2563EB;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .btn-primary-custom:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }
    .btn-outline-custom {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .btn-outline-custom:hover {
        background: #f9fafb;
        border-color: #2563EB;
        color: #2563EB;
    }
    .btn-link-custom {
        background: none;
        border: none;
        color: #2563EB;
        font-size: 0.75rem;
        text-decoration: none;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
    }
    .btn-link-custom:hover {
        text-decoration: underline;
    }
    
    /* Toast notification */
    .toast-custom {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: white;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s;
        z-index: 1050;
        border-left: 4px solid #059669;
    }
    .toast-custom.show {
        transform: translateY(0);
        opacity: 1;
    }
    .toast-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #059669;
    }
    .toast-icon.error {
        background: #DC2626;
    }
    
    /* Filter bar */
    .filter-bar {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        border: 1px solid #edf0f5;
        margin-bottom: 1.5rem;
    }
    .filter-select {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        background: white;
    }
    
    /* Material selector */
    .material-selector {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.5rem;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<nav class="mb-3" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none">Projets</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projets.show', $projet) }}" class="text-decoration-none">{{ $projet->nom }}</a></li>
        <li class="breadcrumb-item active">Gestion des stocks</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('projets.show', $projet) }}" class="btn btn-outline-custom">
            <i class="bi bi-arrow-left me-2"></i>Retour au projet
        </a>
        <div>
            <h2 class="fw-bold mb-1 font-syne" style="font-size:1.5rem;">{{ $projet->nom }}</h2>
            @if($projet->est_stock_central)
                <span class="badge bg-primary bg-opacity-10 text-primary mt-1">🏭 Stock Central KEUF</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-custom" onclick="openReceiveModal()">
            <i class="bi bi-plus-circle me-2"></i>Réceptionner
        </button>
        @if(!$projet->est_stock_central)
        <button class="btn btn-primary-custom" onclick="openConsumeModal()">
            <i class="bi bi-box-arrow-down me-2"></i>Consommer
        </button>
        @endif
        <button class="btn btn-outline-custom" onclick="openTransferModal()">
            <i class="bi bi-arrow-left-right me-2"></i>Transférer
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-label">En alerte</div>
            <div class="kpi-value" id="kpi-alerte">{{ $stats['alerte'] }}</div>
            <div class="kpi-sub">matériaux sous seuil</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-label">Stocks OK</div>
            <div class="kpi-value" id="kpi-ok">{{ $stats['ok'] }}</div>
            <div class="kpi-sub">au-dessus du seuil</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="bi bi-arrow-left-right"></i></div>
            <div class="kpi-label">Mouvements ce mois</div>
            <div class="kpi-value" id="kpi-mvts">{{ $stats['mouvements_mois'] }}</div>
            <div class="kpi-sub" id="kpi-mvts-detail"></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon orange"><i class="bi bi-box"></i></div>
            <div class="kpi-label">Matériaux total</div>
            <div class="kpi-value">{{ $materiaux->count() }}</div>
            <div class="kpi-sub">dans le catalogue</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <label class="small text-secondary mb-1">Filtrer par catégorie</label>
            <select class="form-select form-select-sm filter-select" id="filter-categorie">
                <option value="">Toutes les catégories</option>
                <option value="Gros œuvre">Gros œuvre</option>
                <option value="Finition">Finition</option>
                <option value="Électricité">Électricité</option>
                <option value="Plomberie">Plomberie</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="small text-secondary mb-1">Rechercher</label>
            <input type="text" class="form-control form-control-sm filter-select" id="filter-search" placeholder="Nom du matériau...">
        </div>
        <div class="col-md-4">
            <label class="small text-secondary mb-1">Afficher</label>
            <select class="form-select form-select-sm filter-select" id="filter-status">
                <option value="all">Tous les matériaux</option>
                <option value="alerte">En alerte uniquement</option>
                <option value="ok">OK uniquement</option>
            </select>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="stock-table-container">
    <div class="table-responsive">
        <table class="stock-table table">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Catégorie</th>
                    <th>Unité</th>
                    <th>Stock actuel</th>
                    <th>Seuil d'alerte</th>
                    <th>Niveau</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stock-tbody">
                @foreach($stock as $item)
                <tr data-categorie="{{ $item->materiau->categorie ?? '' }}" data-nom="{{ strtolower($item->materiau->nom) }}">
                    <td class="fw-medium">{{ $item->materiau->nom }}</td>
                    <td>{{ $item->materiau->categorie ?? 'Non catégorisé' }}</td>
                    <td>{{ $item->materiau->unite }}</td>
                    <td class="fw-bold {{ $item->quantite <= $item->seuil_alerte ? 'text-danger' : 'text-success' }}">
                        {{ number_format($item->quantite, 2) }}
                    </td>
                    <td>{{ number_format($item->seuil_alerte, 2) }}</td>
                    <td style="width:180px">
                        <div class="prog-bar">
                            <div class="prog-fill" style="width: {{ min(100, ($item->quantite / max(1, $item->seuil_alerte)) * 100) }}%; 
                                background: {{ $item->quantite <= $item->seuil_alerte ? '#DC2626' : '#059669' }};">
                            </div>
                        </div>
                        <div class="small text-secondary mt-1">
                            {{ $item->quantite <= $item->seuil_alerte ? round(($item->quantite / max(1, $item->seuil_alerte)) * 100, 0) : 100 }}% du seuil
                        </div>
                    </td>
                    <td>
                        @if($item->quantite <= 0)
                            <span class="stock-badge critique">⚠ Rupture</span>
                        @elseif($item->quantite <= $item->seuil_alerte)
                            <span class="stock-badge alerte">⚠ Alerte</span>
                        @else
                            <span class="stock-badge ok">✓ OK</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-link-custom me-2" onclick="openReceiveModalWithMaterial({{ $item->materiau_id }}, '{{ $item->materiau->nom }}')">
                            <i class="bi bi-plus-circle"></i> Approvisionner
                        </button>
                        @if(!$projet->est_stock_central)
                        <button class="btn btn-link-custom me-2" onclick="openConsumeModalWithMaterial({{ $item->materiau_id }}, '{{ $item->materiau->nom }}')">
                            <i class="bi bi-box-arrow-down"></i> Consommer
                        </button>
                        @endif
                        <button class="btn btn-link-custom" onclick="openTransferModalWithMaterial({{ $item->materiau_id }}, '{{ $item->materiau->nom }}')">
                            <i class="bi bi-arrow-left-right"></i> Transférer
                        </button>
                    </td>
                </tr>
                @endforeach
                @if($stock->isEmpty())
                <tr>
                    <td colspan="8" class="text-center py-4 text-secondary">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Aucun matériau en stock
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Movements -->
<div class="mt-4 stock-table-container">
    <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 font-syne"><i class="bi bi-clock-history me-2"></i>Historique des mouvements</h6>
        <button class="btn btn-link-custom" onclick="refreshHistory()">
            <i class="bi bi-arrow-repeat"></i> Actualiser
        </button>
    </div>
    <div class="table-responsive">
        <table class="stock-table table">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Source / Destination</th>
                    <th>Phase</th>
                    <th>Date</th>
                    <th>Saisi par</th>
                    <th>Référence</th>
                </tr>
            </thead>
            <tbody id="history-tbody">
                @forelse($recentMovements as $mouvement)
                <tr>
                    <td class="fw-medium">{{ $mouvement->materiau->nom }}</td>
                    <td>
                        <span class="type-badge {{ $mouvement->type }}">
                            @switch($mouvement->type)
                                @case('entree') <i class="bi bi-arrow-down-circle me-1"></i>Réception @break
                                @case('sortie') <i class="bi bi-arrow-up-circle me-1"></i>Sortie @break
                                @case('transfert') <i class="bi bi-arrow-left-right me-1"></i>Transfert @break
                                @case('consommation') <i class="bi bi-fire me-1"></i>Consommation @break
                                @case('retour') <i class="bi bi-arrow-return-left me-1"></i>Retour @break
                                @default {{ ucfirst($mouvement->type) }}
                            @endswitch
                        </span>
                    </td>
                    <td class="fw-bold">{{ number_format($mouvement->quantite, 2) }} {{ $mouvement->materiau->unite }}</td>
                    <td class="small">
                        @if($mouvement->source_projet_id && $mouvement->dest_projet_id)
                            {{ $mouvement->sourceProjet->nom ?? 'N/A' }} → {{ $mouvement->destProjet->nom ?? 'N/A' }}
                        @elseif($mouvement->source_projet_id)
                            {{ $mouvement->sourceProjet->nom ?? 'N/A' }}
                        @elseif($mouvement->dest_projet_id)
                            {{ $mouvement->destProjet->nom ?? 'N/A' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="small">{{ $mouvement->phase->nom ?? '—' }}</td>
                    <td class="small">{{ $mouvement->created_at->format('d/m/Y H:i') }}</td>
                    <td class="small text-secondary">{{ $mouvement->effectuePar->prenom ?? '' }} {{ $mouvement->effectuePar->nom ?? '' }}</td>
                    <td class="small">{{ $mouvement->reference_doc ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-secondary">
                        Aucun mouvement enregistré
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recentMovements->count() > 0)
    <div class="p-3 bg-white border-top text-center">
        <a href="#" class="text-decoration-none small" onclick="loadFullHistory()">Voir tout l'historique →</a>
    </div>
    @endif
</div>

<!-- ========== MODALS ========== -->

<!-- Modal: Réception -->
<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-syne fw-bold">Réceptionner un matériau</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="receiveForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Matériau *</label>
                        <select class="form-select" id="receive-materiau" required>
                            <option value="">Sélectionner un matériau</option>
                            @foreach($materiaux as $materiau)
                            <option value="{{ $materiau->id }}">{{ $materiau->nom }} ({{ $materiau->unite }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Quantité *</label>
                        <input type="number" class="form-control" id="receive-quantite" step="0.001" min="0.001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Prix unitaire (FCFA)</label>
                        <input type="number" class="form-control" id="receive-prix" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Fournisseur</label>
                        <select class="form-select" id="receive-fournisseur">
                            <option value="">Nouveau fournisseur</option>
                            @foreach($fournisseurs ?? [] as $fournisseur)
                            <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="new-fournisseur-container" style="display:none;">
                        <label class="form-label small fw-medium">Nom du fournisseur</label>
                        <input type="text" class="form-control" id="receive-fournisseur-nom" placeholder="Ex: CIMENCAM">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">N° document</label>
                        <input type="text" class="form-control" id="receive-reference" placeholder="Bon de livraison / Facture">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Note</label>
                        <textarea class="form-control" id="receive-note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Confirmer la réception</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Consommation -->
<div class="modal fade" id="consumeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-syne fw-bold">Consommer un matériau</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="consumeForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>La consommation sera déduite du stock du projet.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Matériau *</label>
                        <select class="form-select" id="consume-materiau" required>
                            <option value="">Sélectionner un matériau</option>
                            @foreach($stock as $item)
                            <option value="{{ $item->materiau_id }}" data-max="{{ $item->quantite }}">
                                {{ $item->materiau->nom }} (Stock: {{ number_format($item->quantite, 2) }} {{ $item->materiau->unite }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Quantité *</label>
                        <input type="number" class="form-control" id="consume-quantite" step="0.001" min="0.001" required>
                        <small class="text-secondary" id="consume-max-info"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Phase *</label>
                        <select class="form-select" id="consume-phase" required>
                            <option value="">Sélectionner une phase</option>
                            @foreach($phases as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->nom }} ({{ $phase->progression }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Note</label>
                        <textarea class="form-control" id="consume-note" rows="2" placeholder="Ex: Utilisation pour coulage dalle RDC"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Confirmer la consommation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Transfert -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-syne fw-bold">Transfert de matériaux</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>Le transfert déduira du stock source et ajoutera au stock destination.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Matériau *</label>
                        <select class="form-select" id="transfer-materiau" required>
                            <option value="">Sélectionner un matériau</option>
                            @foreach($stock as $item)
                            <option value="{{ $item->materiau_id }}" data-max="{{ $item->quantite }}">
                                {{ $item->materiau->nom }} (Stock: {{ number_format($item->quantite, 2) }} {{ $item->materiau->unite }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Quantité *</label>
                        <input type="number" class="form-control" id="transfer-quantite" step="0.001" min="0.001" required>
                        <small class="text-secondary" id="transfer-max-info"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Projet destination *</label>
                        <select class="form-select" id="transfer-dest-projet" required>
                            <option value="">Sélectionner un projet</option>
                            @foreach($activeProjects as $projetDest)
                            <option value="{{ $projetDest->id }}">{{ $projetDest->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Phase destination</label>
                        <select class="form-select" id="transfer-phase">
                            <option value="">Sélectionner une phase (optionnel)</option>
                            @foreach($phases as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Note</label>
                        <textarea class="form-control" id="transfer-note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Confirmer le transfert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Seuil d'alerte -->
<div class="modal fade" id="thresholdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-syne fw-bold">Modifier le seuil d'alerte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="thresholdForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Matériau</label>
                        <input type="text" class="form-control" id="threshold-materiau-nom" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Nouveau seuil d'alerte *</label>
                        <input type="number" class="form-control" id="threshold-value" step="0.001" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="threshold-stock-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-custom" id="toast">
    <div class="toast-icon"><i class="bi bi-check text-white small"></i></div>
    <span id="toast-msg">Action effectuée</span>
</div>

@endsection

@push('scripts')
<script>
// ========== Global Variables ==========
let currentMaterialId = null;
let currentMaterialName = null;

// ========== Modal Handlers ==========
function openReceiveModal() {
    document.getElementById('receiveForm').reset();
    document.getElementById('new-fournisseur-container').style.display = 'none';
    const modal = new bootstrap.Modal(document.getElementById('receiveModal'));
    modal.show();
}

function openReceiveModalWithMaterial(materiauId, materiauNom) {
    openReceiveModal();
    setTimeout(() => {
        document.getElementById('receive-materiau').value = materiauId;
    }, 100);
}

function openConsumeModal() {
    document.getElementById('consumeForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('consumeModal'));
    modal.show();
}

function openConsumeModalWithMaterial(materiauId, materiauNom) {
    openConsumeModal();
    setTimeout(() => {
        const select = document.getElementById('consume-materiau');
        select.value = materiauId;
        updateConsumeMaxInfo();
    }, 100);
}

function openTransferModal() {
    document.getElementById('transferForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    modal.show();
}

function openTransferModalWithMaterial(materiauId, materiauNom) {
    openTransferModal();
    setTimeout(() => {
        const select = document.getElementById('transfer-materiau');
        select.value = materiauId;
        updateTransferMaxInfo();
    }, 100);
}

function openThresholdModal(stockId, materiauNom, currentThreshold) {
    document.getElementById('threshold-stock-id').value = stockId;
    document.getElementById('threshold-materiau-nom').value = materiauNom;
    document.getElementById('threshold-value').value = currentThreshold;
    const modal = new bootstrap.Modal(document.getElementById('thresholdModal'));
    modal.show();
}

// ========== Form Submissions ==========
document.getElementById('receiveForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        materiau_id: document.getElementById('receive-materiau').value,
        quantite: document.getElementById('receive-quantite').value,
        prix_unitaire: document.getElementById('receive-prix').value,
        fournisseur_id: document.getElementById('receive-fournisseur').value,
        fournisseur_nom: document.getElementById('receive-fournisseur-nom').value,
        reference_doc: document.getElementById('receive-reference').value,
        note: document.getElementById('receive-note').value
    };
    
    if (!formData.materiau_id || !formData.quantite) {
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    fetch('{{ route("stocks.receive", $projet) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Erreur: ' + error.message, 'error');
    });
});

document.getElementById('consumeForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        materiau_id: document.getElementById('consume-materiau').value,
        quantite: document.getElementById('consume-quantite').value,
        phase_id: document.getElementById('consume-phase').value,
        note: document.getElementById('consume-note').value
    };
    
    if (!formData.materiau_id || !formData.quantite || !formData.phase_id) {
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    fetch('{{ route("stocks.consume", $projet) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Erreur: ' + error.message, 'error');
    });
});

document.getElementById('transferForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        materiau_id: document.getElementById('transfer-materiau').value,
        quantite: document.getElementById('transfer-quantite').value,
        dest_projet_id: document.getElementById('transfer-dest-projet').value,
        phase_id: document.getElementById('transfer-phase').value,
        note: document.getElementById('transfer-note').value
    };
    
    if (!formData.materiau_id || !formData.quantite || !formData.dest_projet_id) {
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    fetch('{{ route("stocks.transfer", $projet) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Erreur: ' + error.message, 'error');
    });
});

document.getElementById('thresholdForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const stockId = document.getElementById('threshold-stock-id').value;
    const seuil = document.getElementById('threshold-value').value;
    
    fetch(`/stock-projets/${stockId}/threshold`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ seuil_alerte: seuil })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Erreur: ' + error.message, 'error');
    });
});

// ========== Helper Functions ==========
function updateConsumeMaxInfo() {
    const select = document.getElementById('consume-materiau');
    const selected = select.options[select.selectedIndex];
    const maxQte = selected?.getAttribute('data-max') || 0;
    const info = document.getElementById('consume-max-info');
    info.textContent = `Stock disponible: ${parseFloat(maxQte).toFixed(2)} unités`;
    
    const input = document.getElementById('consume-quantite');
    input.max = maxQte;
    input.addEventListener('input', function() {
        if (parseFloat(this.value) > parseFloat(maxQte)) {
            this.value = maxQte;
            showToast('Quantité supérieure au stock disponible', 'error');
        }
    });
}

function updateTransferMaxInfo() {
    const select = document.getElementById('transfer-materiau');
    const selected = select.options[select.selectedIndex];
    const maxQte = selected?.getAttribute('data-max') || 0;
    const info = document.getElementById('transfer-max-info');
    info.textContent = `Stock disponible: ${parseFloat(maxQte).toFixed(2)} unités`;
    
    const input = document.getElementById('transfer-quantite');
    input.max = maxQte;
    input.addEventListener('input', function() {
        if (parseFloat(this.value) > parseFloat(maxQte)) {
            this.value = maxQte;
            showToast('Quantité supérieure au stock disponible', 'error');
        }
    });
}

// Fournisseur selection handler
document.getElementById('receive-fournisseur')?.addEventListener('change', function() {
    const newFournisseurContainer = document.getElementById('new-fournisseur-container');
    if (this.value === '') {
        newFournisseurContainer.style.display = 'block';
    } else {
        newFournisseurContainer.style.display = 'none';
    }
});

// Materiau selection handlers
document.getElementById('consume-materiau')?.addEventListener('change', updateConsumeMaxInfo);
document.getElementById('transfer-materiau')?.addEventListener('change', updateTransferMaxInfo);

// ========== Filters ==========
function applyFilters() {
    const categorie = document.getElementById('filter-categorie').value.toLowerCase();
    const search = document.getElementById('filter-search').value.toLowerCase();
    const status = document.getElementById('filter-status').value;
    
    const rows = document.querySelectorAll('#stock-tbody tr');
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty row
        
        const rowCategorie = (row.getAttribute('data-categorie') || '').toLowerCase();
        const rowNom = (row.getAttribute('data-nom') || '').toLowerCase();
        const rowQuantite = parseFloat(row.cells[3]?.textContent.replace(/\s/g, '') || 0);
        const rowSeuil = parseFloat(row.cells[4]?.textContent.replace(/\s/g, '') || 0);
        
        let show = true;
        
        if (categorie && rowCategorie !== categorie) show = false;
        if (search && !rowNom.includes(search)) show = false;
        if (status === 'alerte' && rowQuantite > rowSeuil) show = false;
        if (status === 'ok' && rowQuantite <= rowSeuil) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

document.getElementById('filter-categorie')?.addEventListener('change', applyFilters);
document.getElementById('filter-search')?.addEventListener('input', applyFilters);
document.getElementById('filter-status')?.addEventListener('change', applyFilters);

// ========== Toast Notification ==========
function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    const toastIcon = document.querySelector('.toast-icon');
    
    toastMsg.textContent = msg;
    
    if (type === 'error') {
        toastIcon.classList.add('error');
        toastIcon.style.background = '#DC2626';
        toastIcon.innerHTML = '<i class="bi bi-exclamation text-white small"></i>';
    } else {
        toastIcon.classList.remove('error');
        toastIcon.style.background = '#059669';
        toastIcon.innerHTML = '<i class="bi bi-check text-white small"></i>';
    }
    
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function refreshHistory() {
    fetch('{{ route("stocks.index", $projet) }}?ajax=1')
        .then(response => response.json())
        .then(data => {
            // Refresh history section
            showToast('Historique actualisé');
        });
}

function loadFullHistory() {
    // Load full history paginated
    showToast('Chargement de l\'historique complet...');
}

// Initialize any additional handlers
document.addEventListener('DOMContentLoaded', function() {
    // Update KPI details
    const totalReceptions = {{ $recentMovements->where('type', 'entree')->count() }};
    const totalTransfers = {{ $recentMovements->where('type', 'transfert')->count() }};
    document.getElementById('kpi-mvts-detail').textContent = 
        `${totalReceptions} réceptions · ${totalTransfers} transferts`;
});
</script>
@endpush