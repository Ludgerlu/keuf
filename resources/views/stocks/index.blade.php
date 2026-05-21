{{-- resources/views/stocks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des stocks — ' . $projet->nom)

@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-box-seam me-2" style="color:var(--yellow-keuf);"></i>Gestion des Stocks
    </div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>
    /* ── Action bar ── */
    .action-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* 4 action buttons – each with its own personality */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.2rem;
        border-radius: 30px;
        font-size: 0.82rem;
        font-weight: 600;
        border: none;
        transition: all 0.18s ease;
        text-decoration: none;
        white-space: nowrap;
        font-family: 'DM Sans', sans-serif;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }

    .btn-action.receive  { background: #10b981; color: white; }
    .btn-action.receive:hover  { background: #059669; color: white; }

    .btn-action.transfer { background: var(--blue-1); color: white; }
    .btn-action.transfer:hover { background: var(--blue-dark); color: white; }

    .btn-action.consume  { background: var(--orange); color: white; }
    .btn-action.consume:hover  { background: #d97706; color: white; }

    .btn-action.new-mat  { background: white; color: var(--black-keuf);
                            border: 1.5px solid #dee2ed; }
    .btn-action.new-mat:hover  { border-color: var(--yellow-keuf); color: var(--black-keuf); }

    /* ── KPI cards ── */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
    @media(max-width:768px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width:480px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: white;
        border-radius: 20px;
        padding: 1.2rem 1.4rem;
        border: 1px solid #eef1f7;
        position: relative;
        overflow: hidden;
        transition: all 0.2s;
        cursor: pointer;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.06); }
    .kpi-card.active {
        border-left: 4px solid var(--yellow-keuf);
        background: #fefce8;
    }
    .kpi-card::after {
        content: '';
        position: absolute;
        right: -10px; top: -10px;
        width: 70px; height: 70px;
        border-radius: 50%;
        opacity: 0.07;
    }
    .kpi-card.red::after   { background: var(--red); }
    .kpi-card.green::after { background: var(--green); }
    .kpi-card.blue::after  { background: var(--blue-1); }
    .kpi-card.orange::after{ background: var(--orange); }

    .kpi-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 0.8rem; }
    .kpi-icon.red    { background: #ffe2e2; color: var(--red); }
    .kpi-icon.green  { background: #e2f3e4; color: var(--green); }
    .kpi-icon.blue   { background: #e9ecfc; color: var(--blue-1); }
    .kpi-icon.orange { background: #fff0d6; color: var(--orange); }
    .kpi-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }
    .kpi-value { font-size: 1.9rem; font-weight: 700; font-family: 'Syne', sans-serif; line-height: 1.1; }
    .kpi-sub   { font-size: 0.7rem; color: #9ca3af; margin-top: 0.15rem; }

    /* ── Filter bar enhancements ── */
    .filter-bar {
        background: white;
        border-radius: 16px;
        padding: 0.9rem 1.25rem;
        border: 1px solid #edf0f5;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-bar .filter-group { display: flex; flex-direction: column; gap: 0.25rem; flex: 1; min-width: 160px; }
    .filter-bar label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.4px; color: #9ca3af; font-weight: 600; }
    .filter-bar select,
    .filter-bar input {
        border: 1px solid #dee2ed;
        border-radius: 10px;
        padding: 0.5rem 0.9rem;
        font-size: 0.85rem;
        background: white;
        outline: none;
        transition: all 0.15s;
    }
    .filter-bar select:focus, .filter-bar input:focus { border-color: var(--yellow-keuf); box-shadow: 0 0 0 3px rgba(255,193,7,0.1); }
    .filter-bar .btn-reset {
        background: #f3f4f6;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.15s;
    }
    .filter-bar .btn-reset:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    /* ── Stock table ── */
    .stock-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        margin-bottom: 2rem;
    }
    .stock-card-head {
        padding: 1rem 1.5rem;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .stock-card-head .table-info {
        display: flex;
        gap: 1rem;
        align-items: center;
        font-size: 0.8rem;
    }
    .stock-table { width: 100% !important; border-collapse: collapse; }
    .stock-table thead th {
        padding: 0.9rem 1rem;
        font-size: 0.73rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6b7280;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
    }
    .stock-table td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #f3f4f8;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    .stock-table tr:last-child td { border-bottom: none; }
    .stock-table tbody tr { transition: background 0.1s; cursor: pointer; }
    .stock-table tbody tr:hover { background: #fafbfe; }

    .prog-bar { width: 110px; height: 6px; background: #e9ecf2; border-radius: 20px; overflow: hidden; }
    .prog-fill { height: 100%; border-radius: 20px; transition: width 0.3s; }

    .stock-badge {
        font-size: 0.68rem;
        padding: 0.25rem 0.7rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-block;
    }
    .stock-badge.alerte   { background: #ffe2e2; color: var(--red); }
    .stock-badge.rupture  { background: #fecaca; color: #7f1d1d; }
    .stock-badge.ok       { background: #e2f3e4; color: var(--green); }

    /* Row action links */
    .row-actions { display: flex; gap: 0.35rem; flex-wrap: wrap; }
    .row-btn {
        font-size: 0.7rem;
        font-weight: 500;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        border: 1px solid transparent;
        text-decoration: none;
        transition: all 0.15s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .row-btn.receive  { color: var(--green);   border-color: #d1fae5; background: #f0fdf4; }
    .row-btn.receive:hover  { background: var(--green); color: white; transform: translateY(-1px); }
    .row-btn.transfer { color: var(--blue-1);  border-color: #bfdbfe; background: #eff6ff; }
    .row-btn.transfer:hover { background: var(--blue-1); color: white; transform: translateY(-1px); }
    .row-btn.consume  { color: var(--orange);  border-color: #fde68a; background: #fffbeb; }
    .row-btn.consume:hover  { background: var(--orange); color: white; transform: translateY(-1px); }

    /* DataTables customization */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2ed;
        border-radius: 10px;
        padding: 0.4rem 0.9rem;
        margin-left: 0.5rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px;
        margin: 0 2px;
        padding: 0.4rem 0.8rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--yellow-keuf);
        color: white !important;
        border: none;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.8rem;
        color: #6b7280;
    }

    /* ── History table ── */
    .type-badge {
        font-size: 0.68rem;
        padding: 0.2rem 0.65rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .type-badge.reception,  .type-badge.entree  { background: #e2f3e4; color: var(--green); }
    .type-badge.transfert                        { background: #e9ecfc; color: var(--blue-1); }
    .type-badge.consommation, .type-badge.sortie { background: #ffe2e2; color: var(--red); }
    .type-badge.retour                           { background: #fff0d6; color: var(--orange); }

    /* ── Empty state ── */
    .empty-state { padding: 3rem 1rem; text-align: center; color: #9ca3af; }
    .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.5; }

    /* ── Quick action modal ── */
    .quick-action-modal .modal-content {
        border-radius: 20px;
        border: none;
    }
    .quick-action-modal .modal-header {
        border-bottom: 1px solid #edf0f5;
        padding: 1.25rem 1.5rem;
    }
    .quick-action-modal .modal-body {
        padding: 1.5rem;
    }
    .quick-action-modal .modal-footer {
        border-top: 1px solid #edf0f5;
        padding: 1rem 1.5rem;
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="mb-3" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none">Projets</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projets.show', $projet) }}" class="text-decoration-none">{{ $projet->nom }}</a></li>
        <li class="breadcrumb-item active">Stocks</li>
    </ol>
</nav>

{{-- Page header --}}
<div class="d-flex align-items-start justify-content-between mb-4 gap-3 flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('projets.show', $projet) }}" class="btn btn-grey-keuf">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <div>
            <h2 class="fw-bold mb-0 font-syne" style="font-size:1.5rem;">{{ $projet->nom }}</h2>
            @if($projet->est_stock_central)
                <span class="badge mt-1" style="background:#e9ecfc;color:var(--blue-1);font-size:.65rem;">
                    🏭 Stock Central KEUF
                </span>
            @endif
        </div>
    </div>

    {{-- ═══ 4 ACTION BUTTONS ═══ --}}
    <div class="action-bar">
        <a href="{{ route('stocks.receive.form', $projet) }}" class="btn-action receive">
            <i class="bi bi-box-arrow-in-down"></i>Réceptionner
        </a>
        <a href="{{ route('stocks.transfer.form', $projet) }}" class="btn-action transfer">
            <i class="bi bi-arrow-left-right"></i>Transférer
        </a>
        @if(!$projet->est_stock_central)
        <a href="{{ route('stocks.consume.form', $projet) }}" class="btn-action consume">
            <i class="bi bi-fire"></i>Consommer
        </a>
        @endif
        <a href="{{ route('stocks.materiau.create') }}" class="btn-action new-mat">
            <i class="bi bi-plus-circle"></i>Nouveau matériau
        </a>
    </div>
</div>

{{-- ═══ KPIs ═══ --}}
<div class="kpi-grid">
    <div class="kpi-card red" data-filter="alerte">
        <div class="kpi-icon red"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="kpi-label">En alerte</div>
        <div class="kpi-value">{{ $stats['alerte'] }}</div>
        <div class="kpi-sub">matériaux sous seuil</div>
    </div>
    <div class="kpi-card green" data-filter="ok">
        <div class="kpi-icon green"><i class="bi bi-check-circle"></i></div>
        <div class="kpi-label">Stocks OK</div>
        <div class="kpi-value">{{ $stats['ok'] }}</div>
        <div class="kpi-sub">au-dessus du seuil</div>
    </div>
    <div class="kpi-card blue" data-filter="all">
        <div class="kpi-icon blue"><i class="bi bi-arrow-left-right"></i></div>
        <div class="kpi-label">Mouvements ce mois</div>
        <div class="kpi-value">{{ $stats['mouvements_mois'] }}</div>
        <div class="kpi-sub" id="kpi-mvts-detail">—</div>
    </div>
    <div class="kpi-card orange" data-filter="all">
        <div class="kpi-icon orange"><i class="bi bi-box"></i></div>
        <div class="kpi-label">Matériaux total</div>
        <div class="kpi-value">{{ $materiaux->count() }}</div>
        <div class="kpi-sub">dans le catalogue</div>
    </div>
</div>

{{-- ═══ Stock Table with DataTable ═══ --}}
<div class="stock-card">
    <div class="stock-card-head">
        <h6 class="fw-bold mb-0 font-syne">📦 Inventaire</h6>
        <div class="table-info">
            <span class="small text-secondary" id="table-info"></span>
            <button class="btn btn-sm btn-light" id="export-csv" title="Exporter en CSV">
                <i class="bi bi-download"></i> CSV
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="stock-table" id="stock-table">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Catégorie</th>
                    <th>Unité</th>
                    <th>Stock actuel</th>
                    <th>Seuil alerte</th>
                    <th>Niveau</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stock-tbody">
                @foreach($stock as $item)
                @php
                    $pct = $item->seuil_alerte > 0
                        ? min(100, ($item->quantite / $item->seuil_alerte) * 100)
                        : 100;
                    $color = $item->quantite <= 0
                        ? '#7f1d1d'
                        : ($item->quantite <= $item->seuil_alerte ? 'var(--red)' : 'var(--green)');
                    $status = $item->quantite <= 0 ? 'rupture' : ($item->quantite <= $item->seuil_alerte ? 'alerte' : 'ok');
                @endphp
                <tr data-id="{{ $item->id }}"
                    data-materiau-id="{{ $item->materiau_id }}"
                    data-categorie="{{ strtolower($item->materiau->categorie ?? '') }}"
                    data-nom="{{ strtolower($item->materiau->nom) }}"
                    data-quantite="{{ $item->quantite }}"
                    data-seuil="{{ $item->seuil_alerte }}"
                    data-status="{{ $status }}">
                    <td class="fw-semibold">
                        <span class="d-inline-block">{{ $item->materiau->nom }}</span>
                    </td>
                    <td class="text-secondary small">{{ $item->materiau->categorie ?? '—' }}</td>
                    <td class="text-secondary small">{{ $item->materiau->unite }}</td>
                    <td class="fw-bold {{ $item->quantite <= $item->seuil_alerte ? 'text-danger' : 'text-success' }}">
                        {{ number_format($item->quantite, 2) }}
                    </td>
                    <td class="text-secondary small">{{ number_format($item->seuil_alerte, 2) }}</td>
                    <td>
                        <div class="prog-bar">
                            <div class="prog-fill" style="width:{{ $pct }}%; background:{{ $color }};"></div>
                        </div>
                        <div class="small text-secondary mt-1">{{ round($pct) }}%</div>
                    </td>
                    <td>
                        @if($item->quantite <= 0)
                            <span class="stock-badge rupture">⚠ Rupture</span>
                        @elseif($item->quantite <= $item->seuil_alerte)
                            <span class="stock-badge alerte">⚠ Alerte</span>
                        @else
                            <span class="stock-badge ok">✓ OK</span>
                        @endif
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('stocks.receive.form', [$projet, 'materiau_id' => $item->materiau_id]) }}"
                               class="row-btn receive" title="Réceptionner">
                                <i class="bi bi-box-arrow-in-down"></i>
                                <span class="d-none d-md-inline">Réceptionner</span>
                            </a>
                            <a href="{{ route('stocks.transfer.form', [$projet, 'materiau_id' => $item->materiau_id]) }}"
                               class="row-btn transfer" title="Transférer">
                                <i class="bi bi-arrow-left-right"></i>
                                <span class="d-none d-md-inline">Transférer</span>
                            </a>
                            @if(!$projet->est_stock_central)
                            <a href="{{ route('stocks.consume.form', [$projet, 'materiau_id' => $item->materiau_id]) }}"
                               class="row-btn consume" title="Consommer">
                                <i class="bi bi-fire"></i>
                                <span class="d-none d-md-inline">Consommer</span>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($stock->isEmpty())
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            Aucun matériau en stock.
                            <a href="{{ route('stocks.materiau.create') }}" class="d-block mt-2 text-decoration-none small" style="color:var(--blue-1);">
                                + Ajouter un premier matériau
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ History with DataTable ═══ --}}
<div class="stock-card">
    <div class="stock-card-head">
        <h6 class="fw-bold mb-0 font-syne"><i class="bi bi-clock-history me-2"></i>Historique des mouvements</h6>
        <div class="table-info">
            <span class="small text-secondary" id="history-info"></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="stock-table" id="history-table">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Source / Destination</th>
                    <th>Phase</th>
                    <th>Date</th>
                    <th>Saisi par</th>
                    <th>Réf.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMovements as $m)
                <tr>
                    <td class="fw-semibold">{{ $m->materiau->nom }}</td>
                    <td>
                        <span class="type-badge {{ $m->type }}">
                            @switch($m->type)
                                @case('reception')    <i class="bi bi-arrow-down-circle"></i>Réception    @break
                                @case('entree')       <i class="bi bi-arrow-down-circle"></i>Entrée       @break
                                @case('transfert')    <i class="bi bi-arrow-left-right"></i>Transfert    @break
                                @case('consommation') <i class="bi bi-fire"></i>Consommation @break
                                @case('sortie')       <i class="bi bi-arrow-up-circle"></i>Sortie        @break
                                @case('retour')       <i class="bi bi-arrow-return-left"></i>Retour      @break
                                @default              {{ ucfirst($m->type) }}
                            @endswitch
                        </span>
                    </td>
                    <td class="fw-semibold">
                        {{ number_format($m->quantite, 2) }}
                        <span class="text-secondary small">{{ $m->materiau->unite }}</span>
                    </td>
                    <td class="small text-secondary">
                        @if($m->source_projet_id && $m->dest_projet_id)
                            {{ $m->sourceProjet->nom ?? '?' }} → {{ $m->destProjet->nom ?? '?' }}
                        @elseif($m->fournisseur_id)
                            {{ $m->fournisseur->nom ?? '?' }}
                        @elseif($m->source_projet_id)
                            {{ $m->sourceProjet->nom ?? '?' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="small">{{ $m->phase->nom ?? '—' }}</td>
                    <td class="small" data-order="{{ $m->created_at }}">
                        {{ $m->created_at }}
                    </td>
                    <td class="small text-secondary">
                        {{ $m->effectuePar->prenom ?? '' }} {{ $m->effectuePar->nom ?? '' }}
                    </td>
                    <td class="small">{{ $m->reference_doc ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-clock-history"></i>
                            Aucun mouvement enregistré
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Quick Action Modal --}}
<div class="modal fade quick-action-modal" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Action rapide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <i class="bi bi-box-seam" style="font-size: 3rem; color: var(--yellow-keuf);"></i>
                    <p class="mt-3" id="quick-action-message">Chargement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable for stock
    let stockTable = $('#stock-table').DataTable({
        responsive: true,
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "Tous"]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
            search: "Rechercher:",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Aucune entrée à afficher",
            infoFiltered: "(filtré sur _MAX_ entrées totales)",
            zeroRecords: "Aucun matériau trouvé",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier"
            }
        },
        order: [[3, 'desc']], // Sort by stock quantity by default
        columnDefs: [
            { orderable: false, targets: [7] } // Disable sorting on actions column
        ],
        drawCallback: function() {
            $('#table-info').text($(this).DataTable().page.info().recordsDisplay + ' matériau(x) affiché(s)');
        }
    });

    // Initialize DataTable for history
    let historyTable = $('#history-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        order: [[5, 'desc']], // Sort by date descending
        columnDefs: [
            { type: 'date', targets: [5] }
        ],
        drawCallback: function() {
            $('#history-info').text($(this).DataTable().page.info().recordsDisplay + ' mouvement(s) affiché(s)');
        }
    });

    // Attach filter events
    $('#quick-search').on('keyup', applyAdvancedFilters);
    $('#category-filter').on('change', applyAdvancedFilters);
    $('#status-filter').on('change', applyAdvancedFilters);
    $('#min-stock, #max-stock').on('input', applyAdvancedFilters);

    // Reset filters
    $('#reset-filters').on('click', function() {
        $('#quick-search').val('');
        $('#category-filter').val('');
        $('#status-filter').val('all');
        $('#min-stock').val('');
        $('#max-stock').val('');
        applyAdvancedFilters();
        
        // Remove active class from KPIs
        $('.kpi-card').removeClass('active');
        
        // Show toast
        showToast('Filtres réinitialisés', 'success');
    });

    // KPI click filtering
    $('.kpi-card').on('click', function() {
        const filter = $(this).data('filter');
        
        if (filter === 'alerte') {
            $('#status-filter').val('alerte').trigger('change');
        } else if (filter === 'ok') {
            $('#status-filter').val('ok').trigger('change');
        } else if (filter === 'all') {
            $('#status-filter').val('all').trigger('change');
        }
        
        $('.kpi-card').removeClass('active');
        $(this).addClass('active');
    });

    // Export to CSV
    $('#export-csv').on('click', function() {
        const data = [];
        const headers = ['Matériau', 'Catégorie', 'Unité', 'Stock actuel', 'Seuil alerte', 'Niveau (%)', 'État'];
        data.push(headers);
        
        $('#stock-table tbody tr').each(function() {
            const row = $(this);
            if (row.css('display') !== 'none') {
                const rowData = [
                    row.find('td:eq(0)').text().trim(),
                    row.find('td:eq(1)').text().trim(),
                    row.find('td:eq(2)').text().trim(),
                    row.find('td:eq(3)').text().trim(),
                    row.find('td:eq(4)').text().trim(),
                    row.find('td:eq(5) .small').text().trim(),
                    row.find('td:eq(6) .stock-badge').text().trim()
                ];
                data.push(rowData);
            }
        });
        
        const csvContent = data.map(row => row.join(';')).join('\n');
        const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'stock_{{ $projet->nom }}.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Export CSV effectué', 'success');
    });

    // Row click to show details
    $('#stock-table tbody').on('click', 'tr', function(e) {
        // Don't trigger if clicking on action buttons
        if ($(e.target).closest('.row-actions, .row-btn').length) return;
        
        const materiau = $(this).find('td:first').text().trim();
        const quantite = $(this).data('quantite');
        const seuil = $(this).data('seuil');
        
        $('#quick-action-message').html(`
            <strong>${materiau}</strong><br>
            Stock actuel: <span class="fw-bold">${Number(quantite).toFixed(2)}</span><br>
            Seuil d'alerte: ${Number(seuil).toFixed(2)}<br>
            ${quantite <= seuil ? '<span class="text-danger">⚠ Stock bas, veuillez réapprovisionner</span>' : '<span class="text-success">✓ Stock suffisant</span>'}
        `);
        
        $('#quickActionModal').modal('show');
    });

    // Flash messages
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
    @if(session('warning'))
        showToast('{{ session('warning') }}', 'warning');
    @endif

    // Initialize KPI detail
    const total = {{ $stats['mouvements_mois'] }};
    $('#kpi-mvts-detail').text(total + ' mouvement(s)');
    
    // Trigger initial filter
    applyAdvancedFilters();
});

function showToast(msg, type = 'success') {
    // Check if toast container exists, if not create it
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgColor = type === 'error' ? 'danger' : (type === 'warning' ? 'warning' : 'success');
    const icon = type === 'error' ? 'exclamation-triangle' : (type === 'warning' ? 'exclamation-circle' : 'check-circle');
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${bgColor} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${icon} me-2"></i>${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
</script>
@endpush