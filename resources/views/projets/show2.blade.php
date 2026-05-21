@extends('layouts.app')

@section('title', $projet->nom)
@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-building me-2" style="color:var(--yellow-keuf);"></i>
        Détail projet
    </div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>    
    :root {
        --yellow-keuf: #f5b042;
        --primary-blue: #2563EB;
        --success-green: #059669;
        --danger-red: #DC2626;
        --warning-amber: #D97706;
        --gray-bg: #f9fafc;
        --border-light: #edf0f5;
    }

    .hero-stat {
        padding-right: 2rem;
        border-right: 1px solid rgba(255,255,255,0.2);
    }
    .hero-stat:last-child {
        border-right: none;
    }
    .hero-stat-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255,255,255,0.7);
        margin-bottom: 0.25rem;
    }
    
    .quick-btns {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .quick-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .quick-btn:hover {
        background: rgba(255,255,255,0.25);
        color: white;
        transform: translateY(-1px);
        border-color: rgba(255,255,255,0.4);
    }
    .quick-btn i {
        font-size: 1rem;
    }
    .tab-badge {
        background: rgba(255,255,255,0.25);
        border-radius: 30px;
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 4px;
    }

    .section-card {
        background: white;
        border-radius: 24px;
        border: 1px solid var(--border-light);
        margin-bottom: 1.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: box-shadow 0.2s;
    }
    .section-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .section-card-head {
        padding: 1.25rem 1.5rem;
        background: var(--gray-bg);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .section-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        font-family: 'Syne', sans-serif;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-card-title i {
        font-size: 1.25rem;
    }
    
    /* Phases styling */
    .phase-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 16px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-light);
        transition: all 0.2s;
        cursor: pointer;
    }
    .phase-item:hover {
        border-color: var(--primary-blue);
        background: #fafcff;
        transform: translateX(2px);
    }
    .phase-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--primary-blue);
    }
    .phase-badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.8rem;
        border-radius: 40px;
        font-weight: 600;
        background: #eef2f6;
        color: #374151;
        white-space: nowrap;
    }
    .phase-badge.termine {
        background: #e0f2e9;
        color: var(--success-green);
    }
    .phase-badge.en-cours {
        background: #e0e7ff;
        color: var(--primary-blue);
    }
    .phase-badge.retard {
        background: #fee2e2;
        color: var(--danger-red);
    }
    .phase-badge.debut {
        background: #fff3e0;
        color: var(--warning-amber);
    }
    .prog-bar-sm {
        width: 80px;
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }
    .prog-fill-sm {
        height: 100%;
        background: var(--primary-blue);
        border-radius: 4px;
    }
    
    .remarque-item {
        padding: 1rem 1.25rem;
        background: white;
        border-radius: 16px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-light);
        transition: all 0.2s;
    }
    .remarque-item:hover {
        border-color: var(--primary-blue);
        box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    }
    
    /* Table styling */
    .stock-table {
        font-size: 0.875rem;
    }
    .stock-table th {
        background: var(--gray-bg);
        padding: 0.875rem 1rem;
        font-weight: 600;
        color: #1f2937;
        border-bottom: 1px solid var(--border-light);
    }
    .stock-table td {
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
    }
    .type-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 40px;
        font-weight: 500;
    }
    .type-badge.entree {
        background: #e0f2e9;
        color: var(--success-green);
    }
    .type-badge.sortie {
        background: #fee2e2;
        color: var(--danger-red);
    }
    .type-badge.transfert {
        background: #e0e7ff;
        color: var(--primary-blue);
    }
    
    /* Presence styling */
    .presence-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--border-light);
        transition: background 0.2s;
    }
    .presence-row:hover {
        background: var(--gray-bg);
    }
    .presence-row:last-child {
        border-bottom: none;
    }
    .p-ava {
        width: 40px;
        height: 40px;
        border-radius: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .status-badge-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.9rem;
        border-radius: 40px;
        font-weight: 500;
    }
    .status-badge-sm.present {
        background: #e0f2e9;
        color: var(--success-green);
    }
    .status-badge-sm.absent {
        background: #fee2e2;
        color: var(--danger-red);
    }
    .status-badge-sm.demi_journee {
        background: #fff3e0;
        color: var(--warning-amber);
    }
    
    .sig-card {
        background: white;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-light);
        transition: all 0.2s;
    }
    .sig-card:hover {
        border-color: #fbc4c4;
        background: #fffbfb;
    }
    
    .stat-pill-sm {
        font-size: 0.75rem;
        padding: 0.35rem 1rem;
        border-radius: 40px;
        background: var(--gray-bg);
        font-weight: 500;
    }
    .stat-pill-sm.green {
        background: #e0f2e9;
        color: var(--success-green);
    }
    
    .btn-grey-keuf {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
        border-radius: 40px;
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .btn-grey-keuf:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }
    .btn-primary-keuf {
        background: var(--primary-blue);
        border: none;
        color: white;
        border-radius: 40px;
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
    }
    .btn-primary-keuf:hover {
        background: #1d4ed8;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }
    
    /* DataTables override */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 40px;
        border: 1px solid var(--border-light);
        padding: 0.4rem 1rem;
        margin-left: 0.5rem;
    }
    table.dataTable thead th {
        border-bottom: 1px solid var(--border-light);
    }
    
    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<nav class="mb-4" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none text-secondary">Projets</a></li>
        <li class="breadcrumb-item active fw-semibold">{{ $projet->nom }}</li>
    </ol>
</nav>

<!-- Hero Section -->
<div class="detail-hero bg-dark rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);">
    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-3">
        <div>
            <h3 class="fw-bold mb-2 font-syne text-white">{{ $projet->nom }}</h3>
            <div class="text-white-50 small">
                <i class="bi bi-building me-1"></i> Client : {{ $projet->client_nom }}
                @if($projet->client_telephone)
                    · <i class="bi bi-telephone"></i> {{ $projet->client_telephone }}
                @endif
                @if($projet->reference)
                    · Réf: {{ $projet->reference }}
                @endif
            </div>
        </div>
        @php
            $statutClass = match($projet->statut) {
                'en_cours' => 'en-cours',
                'retard' => 'retard',
                'verification' => 'en-cours',
                'debut' => 'debut',
                'termine' => 'termine',
                default => 'en-cours'
            };
            $statutText = match($projet->statut) {
                'en_cours' => 'En cours',
                'retard' => 'En retard',
                'verification' => 'En vérification',
                'debut' => 'Début',
                'termine' => 'Terminé',
                default => $projet->statut
            };
        @endphp
        <span class="phase-badge {{ $statutClass }}" style="background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.25);">
            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
            {{ $statutText }}
        </span>
    </div>
    
    <div class="d-flex gap-4 flex-wrap mb-4">
        <div class="hero-stat">
            <div class="hero-stat-label">Budget</div>
            <div class="fw-bold fs-5 text-white">{{ number_format($projet->budget, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Début</div>
            <div class="fw-medium text-white">{{ $projet->date_debut ? \Carbon\Carbon::parse($projet->date_debut)->format('d/m/Y') : 'N/A' }}</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Fin prévue</div>
            <div class="fw-medium text-white">{{ $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') : 'N/A' }}</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Avancement</div>
            <div class="fw-medium text-white d-flex align-items-center gap-2">
                <span>{{ $projet->progression }}%</span>
                <div class="prog-bar-sm">
                    <div class="prog-fill-sm" style="width: {{ $projet->progression }}%; background: #f5b042;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick actions -->
    <div class="quick-btns">
        <a href="#" class="quick-btn" data-section="presences">
            <i class="bi bi-person-check"></i>Présences 
            <span class="tab-badge">{{ $statsPresences['aujourdhui'] ?? 0 }}</span>
        </a>
        <a href="#" class="quick-btn" data-section="stock">
            <i class="bi bi-box-seam"></i>Stock 
            <span class="tab-badge">{{ $mouvementsStock->count() }}</span>
        </a>
        <a href="#" class="quick-btn" data-section="signalements">
            <i class="bi bi-exclamation-triangle"></i>Signalements 
            <span class="tab-badge">{{ $projet->signalements->count() }}</span>
        </a>
        @if($peutSaisirPresences)
        <a href="{{ route('presences.index', $projet) }}" class="quick-btn">
            <i class="bi bi-pencil-square"></i>Saisir présences
        </a>
        @endif
        @if($peutModifier)
        <a href="{{ route('projets.edit', $projet) }}" class="quick-btn">
            <i class="bi bi-gear"></i>Modifier projet
        </a>
        @endif
    </div>
</div>

<!-- Phases & Remarques -->
<div class="section-card">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-list-check" style="color:var(--yellow-keuf);"></i>
            Phases & Remarques
        </div>
        <button class="btn-grey-keuf btn" onclick="ajouterRemarque()">
            <i class="bi bi-plus-circle me-1"></i>Ajouter remarque
        </button>
    </div>
    <div class="p-4">
        <div class="row g-4">
            <!-- Colonne phases -->
            <div class="col-md-5">
                <div class="small text-secondary mb-2 fw-semibold">
                    <i class="bi bi-diagram-3 me-1"></i>Phases du projet
                </div>
                <div id="phases-list">
                    @forelse($projet->phases->sortBy('ordre') as $phase)
                    <div class="phase-item" data-phase-id="{{ $phase->id }}" onclick="scrollToRemarques({{ $phase->id }})">
                        <div class="phase-num">{{ $phase->ordre }}</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.875rem;">{{ $phase->nom }}</div>
                            <div class="small text-secondary mt-1">
                                @if($phase->date_debut_prevue)
                                    {{ \Carbon\Carbon::parse($phase->date_debut_prevue)->format('d/m/Y') }}
                                @endif
                                →
                                @if($phase->date_fin_prevue)
                                    {{ \Carbon\Carbon::parse($phase->date_fin_prevue)->format('d/m/Y') }}
                                @endif
                            </div>
                            @if($phase->progression > 0)
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <div class="prog-bar-sm">
                                    <div class="prog-fill-sm" style="width: {{ $phase->progression }}%;"></div>
                                </div>
                                <span class="small text-secondary">{{ $phase->progression }}%</span>
                            </div>
                            @endif
                        </div>
                        @php
                            $phaseStatut = $phase->progression >= 100 ? 'termine' : 
                                          ($phase->id == $projet->phase_actuelle_id ? 'en-cours' : 'debut');
                        @endphp
                        <span class="phase-badge {{ $phaseStatut }}">
                            {{ $phase->progression >= 100 ? 'Terminé' : 
                               ($phase->id == $projet->phase_actuelle_id ? 'En cours' : 'À venir') }}
                        </span>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="bi bi-diagram-3 fs-1"></i>
                        <p class="mt-2">Aucune phase définie</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Colonne remarques -->
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="small text-secondary fw-semibold">
                        <i class="bi bi-chat-text me-1"></i>Remarques
                    </div>
                    <span class="badge bg-light text-secondary">{{ $remarques->count() }} remarque(s)</span>
                </div>
                <div id="remarques-list">
                    @forelse($remarques as $remarque)
                    <div class="remarque-item" data-phase-id="{{ $remarque->phase_id }}">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                            <div class="fw-semibold small">
                                <span class="badge bg-light text-dark">{{ $remarque->phase->nom }}</span>
                            </div>
                            <div class="small text-secondary">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ $remarque->auteur->prenom ?? '' }} {{ $remarque->auteur->nom ?? '' }} · 
                                {{ $remarque->created_at}}
                            </div>
                        </div>
                        <div class="text-secondary" style="font-size:.875rem;">{{ $remarque->contenu }}</div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="bi bi-chat-dots fs-1"></i>
                        <p class="mt-2">Aucune remarque</p>
                        <button class="btn btn-sm btn-grey-keuf mt-2" onclick="ajouterRemarque()">
                            <i class="bi bi-plus-circle me-1"></i>Ajouter une remarque
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock chantier -->
@if($peutGererStock || $stock->isNotEmpty())
<div class="section-card" id="stock-section">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-box-seam" style="color:var(--yellow-keuf);"></i>
            Mouvements de stock — Ce chantier
        </div>
        <a href="{{ route('stocks.index', $projet) }}" class="btn-grey-keuf btn">
            <i class="bi bi-arrow-right me-1"></i>Gérer les stocks
        </a>
    </div>
    <div class="p-0">
        <table class="stock-table w-100" id="stockTable">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Type</th>
                    <th>Qté</th>
                    <th>Phase</th>
                    <th>Date</th>
                    <th>Saisi par</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvementsStock as $mouvement)
                <tr>
                    <td class="fw-semibold">{{ $mouvement->materiau->nom ?? 'N/A' }}</td>
                    <td>
                        @php
                            $typeClass = match($mouvement->type) {
                                'entree' => 'entree',
                                'sortie', 'consommation' => 'sortie',
                                'transfert', 'retour' => 'transfert',
                                default => 'transfert'
                            };
                        @endphp
                        <span class="type-badge {{ $typeClass }}">
                            <i class="bi bi-{{ $typeClass == 'entree' ? 'arrow-down' : ($typeClass == 'sortie' ? 'arrow-up' : 'arrow-left-right') }} me-1"></i>
                            {{ ucfirst($mouvement->type) }}
                        </span>
                    </td>
                    <td class="fw-medium">{{ number_format($mouvement->quantite, 0, ',', ' ') }}</td>
                    <td class="text-secondary">{{ $mouvement->phase ?? 'N/A' }}</td>
                    <td class="text-secondary">{{ \Carbon\Carbon::parse($mouvement->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-secondary">{{ $mouvement->effectuePar->prenom ?? '' }} {{ $mouvement->effectuePar->nom ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-secondary">
                        <i class="bi bi-inbox fs-3"></i>
                        <p class="mt-2 mb-0">Aucun mouvement de stock</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Présences -->
<div class="section-card" id="presences-section">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-person-lines-fill" style="color:var(--yellow-keuf);"></i>
            Présences du chantier
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div id="pres-stats">
                <span class="stat-pill-sm green me-2">
                    <i class="bi bi-check-circle me-1"></i>{{ $statsPresences['present_aujourdhui'] ?? 0 }} présents aujourd'hui
                </span>
                <span class="stat-pill-sm">
                    <i class="bi bi-people me-1"></i>{{ $statsPresences['total'] ?? 0 }} employés
                </span>
            </div>
            @if($peutSaisirPresences)
            <a href="{{ route('presences.index', $projet) }}" class="btn-primary-keuf btn">
                <i class="bi bi-pencil-square me-1"></i>Modifier les présences
            </a>
            @endif
        </div>
    </div>
    <div class="p-0">
        <div id="pres-content">
            @forelse($projet->presences->take(10) as $presence)
            <div class="presence-row">
                <div class="p-ava" style="background: {{ '#' . substr(md5($presence->employe->nom ?? ''), 0, 6) }}">
                    {{ strtoupper(substr($presence->employe->prenom ?? '', 0, 1) . substr($presence->employe->nom ?? '', 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold">
                        {{ $presence->employe->prenom ?? '' }} {{ $presence->employe->nom ?? '' }}
                    </span>
                    <div class="small text-secondary">
                        <i class="bi bi-clock me-1"></i>
                        {{ $presence->heure_debut ? \Carbon\Carbon::parse($presence->heure_debut)->format('H:i') : '--' }} - 
                        {{ $presence->heure_fin ? \Carbon\Carbon::parse($presence->heure_fin)->format('H:i') : '--' }}
                    </div>
                </div>
                @php
                    $statutClass = match($presence->statut) {
                        'present' => 'present',
                        'absent' => 'absent',
                        'demi_journee' => 'demi_journee',
                        default => 'present'
                    };
                    $statutIcon = match($presence->statut) {
                        'present' => 'check-circle',
                        'absent' => 'x-circle',
                        'demi_journee' => 'sun',
                        default => 'question-circle'
                    };
                @endphp
                <span class="status-badge-sm {{ $statutClass }}">
                    <i class="bi bi-{{ $statutIcon }} me-1"></i>
                    {{ $presence->statut == 'demi_journee' ? 'Demi-journée' : ucfirst($presence->statut) }}
                </span>
            </div>
            @empty
            <div class="empty-state">
                <i class="bi bi-calendar-x fs-1"></i>
                <p class="mt-2">Aucune présence enregistrée</p>
                @if($peutSaisirPresences)
                <a href="{{ route('presences.index', $projet) }}" class="btn btn-sm btn-primary-keuf mt-2">
                    <i class="bi bi-pencil-square me-1"></i>Saisir les présences
                </a>
                @endif
            </div>
            @endforelse
            @if($projet->presences->count() > 10)
            <div class="text-center p-3 border-top">
                <a href="{{ route('presences.index', $projet) }}" class="text-decoration-none small">Voir toutes les présences <i class="bi bi-arrow-right"></i></a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Signalements -->
<div class="section-card" id="signalements-section">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-exclamation-triangle" style="color:var(--danger-red);"></i>
            Signalements — Ce chantier
        </div>
        <button class="btn-grey-keuf btn" onclick="ajouterSignalement()">
            <i class="bi bi-plus-circle me-1"></i>Ajouter un signalement
        </button>
    </div>
    <div class="p-3" id="sig-list">
        @forelse($projet->signalements->sortByDesc('created_at') as $signalement)
        <div class="sig-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="fw-semibold">
                    <i class="bi bi-flag-fill me-1" style="color: var(--danger-red);"></i>
                    {{ $signalement->type }}
                </div>
                @php
                    $prioriteClass = match($signalement->priorite) {
                        'critique' => 'bg-danger text-white',
                        'haute' => 'bg-warning text-dark',
                        default => 'bg-secondary bg-opacity-10 text-dark'
                    };
                @endphp
                <span class="badge {{ $prioriteClass }} px-3 py-1">{{ ucfirst($signalement->priorite) }}</span>
            </div>
            <div class="text-secondary mb-2" style="font-size:.875rem;">{{ $signalement->description }}</div>
            <div class="d-flex gap-3 align-items-center small text-secondary">
                <span><i class="bi bi-calendar3 me-1"></i>{{ $signalement->created_at }}</span>
                @if($signalement->auteur)
                <span><i class="bi bi-person me-1"></i>{{ $signalement->auteur->prenom ?? '' }}</span>
                @endif
                <span class="badge bg-light text-dark">
                    <i class="bi bi-{{ $signalement->statut == 'resolu' ? 'check-circle' : 'clock' }} me-1"></i>
                    {{ ucfirst($signalement->statut) }}
                </span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="bi bi-shield-check fs-1 text-success"></i>
            <p class="mt-2">Aucun signalement actif</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal pour ajouter une remarque -->
<div class="modal fade" id="remarkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Ajouter une remarque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="remarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="phase_id" class="form-label fw-semibold">Phase concernée</label>
                        <select class="form-select" id="phase_id" name="phase_id" required>
                            <option value="">Sélectionner une phase</option>
                            @foreach($projet->phases->sortBy('ordre') as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contenu" class="form-label fw-semibold">Remarque</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="4" required placeholder="Saisissez votre remarque ici..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn" style="background:var(--yellow-keuf);color:#1f2937;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un signalement (simple) -->
<div class="modal fade" id="signalementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Ajouter un signalement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="signalementForm" method="POST" action="#">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold">Type de signalement</label>
                        <input type="text" class="form-control" id="type" name="type" required placeholder="Ex: Sécurité, Matériel, Retard...">
                    </div>
                    <div class="mb-3">
                        <label for="priorite" class="form-label fw-semibold">Priorité</label>
                        <select class="form-select" id="priorite" name="priorite" required>
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Décrivez le problème..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn" style="background:var(--danger-red);color:white;">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
    // Initialize DataTable for stock movements
    @if($mouvementsStock->count() > 0)
    $(document).ready(function() {
        $('#stockTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[4, 'desc']],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "Tous"]]
        });
    });
    @endif
    
    // Smooth scroll to sections when clicking quick buttons
    document.querySelectorAll('.quick-btn[data-section]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            const targetId = section + '-section';
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.add('animate-in');
                setTimeout(() => target.classList.remove('animate-in'), 300);
            }
        });
    });
    
    // Scroll to phase remarks
    function scrollToRemarques(phaseId) {
        const remarques = document.querySelectorAll('.remarque-item');
        let found = false;
        remarques.forEach(rem => {
            if (rem.dataset.phaseId == phaseId) {
                rem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                rem.style.backgroundColor = '#fef9e6';
                setTimeout(() => rem.style.backgroundColor = '', 1500);
                found = true;
            }
        });
        if (!found) {
            // Show toast or alert
            const toastMsg = document.createElement('div');
            toastMsg.className = 'alert alert-info position-fixed bottom-0 end-0 m-3';
            toastMsg.innerHTML = 'Aucune remarque pour cette phase. Cliquez sur "Ajouter remarque" pour en créer une.';
            document.body.appendChild(toastMsg);
            setTimeout(() => toastMsg.remove(), 3000);
        }
    }
    
    // Modal functions
    function ajouterRemarque() {
        const modal = new bootstrap.Modal(document.getElementById('remarkModal'));
        modal.show();
    }
    
    function ajouterSignalement() {
        const modal = new bootstrap.Modal(document.getElementById('signalementModal'));
        modal.show();
    }
    
    // Handle remark form submission with AJAX
    document.getElementById('remarkForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const phaseId = document.getElementById('phase_id').value;
        const contenu = document.getElementById('contenu').value;
        
        if (!phaseId || !contenu) return;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Envoi...';
        submitBtn.disabled = true;
        
        fetch(`/phases/${phaseId}/remarques`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ contenu: contenu })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('remarkModal'));
                modal.hide();
                
                // Reload to show new remark
                location.reload();
            } else {
                alert('Erreur lors de l\'ajout de la remarque');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Handle signalement form submission
    document.getElementById('signalementForm')?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Envoi...';
        submitBtn.disabled = true;
        
        // Form will submit normally, but we show loading state
        // The page will reload on success
    });
    
    // Add tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
    
    // Keyboard shortcut: Ctrl + R to open remark modal
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            ajouterRemarque();
        }
    });
</script>
@endpush