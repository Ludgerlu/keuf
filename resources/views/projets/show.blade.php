@extends('layouts.app')

@section('title', $projet->nom)
@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-building me-2" style="color:var(--yellow-keuf);"></i>
        Détail projet
    </div>
@endsection

@push('styles')
<style>    
   
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
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .quick-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .quick-btn:hover {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .quick-btn i {
        margin-right: 6px;
    }

    .section-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .section-card-head {
        padding: 1rem 1.25rem;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .section-card-title {
        font-weight: 700;
        font-size: 1rem;
        font-family: 'Syne', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .phase-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-left: 4px solid transparent;
        background: #f9fafc;
        border-radius: 12px;
        margin-bottom: 0.5rem;
    }
    .phase-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        color: #374151;
    }
    .phase-badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.6rem;
        border-radius: 30px;
        font-weight: 500;
        background: #eef2f6;
        color: #374151;
    }
    .phase-badge.termine {
        background: #e2f3e4;
        color: #059669;
    }
    .phase-badge.en-cours {
        background: #e2eafc;
        color: #2563EB;
    }
    .phase-badge.retard {
        background: #fee2e2;
        color: #DC2626;
    }
    .phase-badge.debut {
        background: #fef3c7;
        color: #D97706;
    }
    
    .remarque-item {
        padding: 1rem;
        border-left: 4px solid transparent;
        background: #f9fafc;
        border-radius: 12px;
        margin-bottom: 0.75rem;
    }
    
    .stock-table {
        font-size: 0.875rem;
    }
    .stock-table th {
        background: #f9fafc;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: #374151;
    }
    .stock-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #edf0f5;
    }
    .type-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 30px;
    }
    .type-badge.entree {
        background: #e2f3e4;
        color: #059669;
    }
    .type-badge.sortie {
        background: #fee2e2;
        color: #DC2626;
    }
    .type-badge.transfert {
        background: #e2eafc;
        color: #2563EB;
    }
    
    .presence-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid #edf0f5;
    }
    .presence-row:last-child {
        border-bottom: none;
    }
    .p-ava {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 0.8rem;
    }
    .status-badge-sm {
        font-size: 0.7rem;
        padding: 0.2rem 0.8rem;
        border-radius: 30px;
    }
    .status-badge-sm.present {
        background: #e2f3e4;
        color: #059669;
    }
    .status-badge-sm.absent {
        background: #fee2e2;
        color: #DC2626;
    }
    .status-badge-sm.demi_journee {
        background: #fef3c7;
        color: #D97706;
    }
    
    .sig-card {
        background: #f9fafc;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .photo-card {
        background: #f9fafc;
        border-radius: 12px;
        overflow: hidden;
    }
    .photo-thumb {
        height: 100px;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    
    .btn-grey-keuf {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
    }
    .btn-grey-keuf:hover {
        background: #f9fafb;
    }
    
    .stat-pill-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        background: #eef2f6;
    }
    .stat-pill-sm.green {
        background: #e2f3e4;
        color: #059669;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<nav class="mb-3" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('projets.index') }}" class="text-decoration-none">Projets</a></li>
        <li class="breadcrumb-item active">{{ $projet->nom }}</li>
    </ol>
</nav>

<!-- Hero Section -->
<div class="detail-hero">
    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-3">
        <div>
            <h3 class="fw-bold mb-1 font-syne">{{ $projet->nom }}</h3>
            <div class="hero-meta">
                Client : {{ $projet->client_nom }}
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
                'verification' => 'verification',
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
            {{ $statutText }}
        </span>
    </div>
    
    <div class="d-flex gap-3 flex-wrap mb-3">
        <div class="hero-stat">
            <div class="hero-stat-label">Budget</div>
            <div class="fw-medium">{{ number_format($projet->budget, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Début</div>
            <div class="fw-medium">{{ $projet->date_debut ? \Carbon\Carbon::parse($projet->date_debut)->format('d/m/Y') : 'N/A' }}</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Fin prévue</div>
            <div class="fw-medium">{{ $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') : 'N/A' }}</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Avancement</div>
            <div class="fw-medium">{{ $projet->progression }}%</div>
        </div>
    </div>
    
    <!-- Quick actions -->
    <div class="quick-btns">
        <a href="#" class="quick-btn">
            <i class="bi bi-person-check"></i>Présences 
            <span class="tab-badge">{{ $statsPresences['aujourdhui'] ?? 0 }}</span>
        </a>
        <a href="#" class="quick-btn">
            <i class="bi bi-box-seam"></i>Stock 
            <span class="tab-badge">{{ $stock->count() }}</span>
        </a>
        <a href="#" class="quick-btn">
            <i class="bi bi-exclamation-triangle"></i>Signalements 
            <span class="tab-badge">{{ $projet->signalements->count() }}</span>
        </a>
        <a href="#" class="quick-btn">
            <i class="bi bi-camera"></i>Photos 
            <span class="tab-badge">3</span>
        </a>
        @if($peutSaisirPresences)
        <a href="#" class="quick-btn">
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
        <button class="btn btn-grey-keuf btn-sm" onclick="ajouterRemarque()">
            <i class="bi bi-plus-circle"></i>Ajouter remarque
        </button>
    </div>
    <div class="p-3">
        <div class="row g-3">
            <!-- Colonne phases -->
            <div class="col-md-5">
                <div id="phases-list">
                    @forelse($projet->phases as $phase)
                    <div class="phase-item" style="border-left-color: 
                        @if($phase->progression >= 100) #059669
                        @elseif($phase->id == $projet->phase_actuelle_id) #2563EB
                        @else #9CA3AF
                        @endif">
                        <div class="phase-num">{{ $phase->ordre }}</div>
                        <div class="flex-grow-1">
                            <div class="fw-medium" style="font-size:.875rem;">{{ $phase->nom }}</div>
                            <div style="font-size:.75rem;color:#9CA3AF;">
                                @if($phase->date_debut_prevue)
                                    {{ \Carbon\Carbon::parse($phase->date_debut_prevue)->format('d/m/Y') }}
                                @endif
                                →
                                @if($phase->date_fin_prevue)
                                    {{ \Carbon\Carbon::parse($phase->date_fin_prevue)->format('d/m/Y') }}
                                @endif
                            </div>
                            @if($phase->progression > 0)
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="prog-bar" style="width:80px;">
                                    <div class="prog-fill" style="width:{{ $phase->progression }}%; background:#2563EB;"></div>
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
                    <p class="text-secondary text-center py-3">Aucune phase définie</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Colonne remarques -->
            <div class="col-md-7">
                <div id="remarques-list">
                    @forelse($remarques as $remarque)
                    <div class="remarque-item" style="border-left-color: #2563EB;">
                        <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">
                            <div class="fw-medium" style="font-size:.8125rem;">{{ $remarque->phase->nom }}</div>
                            <div style="font-size:.75rem;color:#9CA3AF;">
                                {{ $remarque->auteur->prenom ?? '' }} {{ $remarque->auteur->nom ?? '' }} · 
                                {{ $remarque->created_at }}
                            </div>
                        </div>
                        <div style="font-size:.8125rem;color:#374151;">{{ $remarque->contenu }}</div>
                    </div>
                    @empty
                    <p class="text-secondary text-center py-3">Aucune remarque</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock chantier -->
@if($peutGererStock || $stock->isNotEmpty())
<div class="section-card">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-box-seam" style="color:var(--yellow-keuf);"></i>
            Mouvements de stock — Ce chantier
        </div>
        <a href="{{ route('stocks.index', $projet) }}" class="btn btn-grey-keuf btn-sm">
            Gérer les stocks <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="stock-table w-100">
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
                    <td class="fw-medium">{{ $mouvement->materiau->nom ?? 'N/A' }}</td>
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
                            {{ ucfirst($mouvement->type) }}
                        </span>
                    </td>
                    <td>{{ $mouvement->quantite }}</td>
                    <td style="color:#6B7280;">{{ $mouvement->phase ?? 'N/A' }}</td>
                    <td>{{ $mouvement->created_at }}</td>
                    <td>{{ $mouvement->effectuePar->prenom ?? '' }} {{ $mouvement->effectuePar->nom ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-3 text-secondary">
                        Aucun mouvement de stock
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Présences -->
<div class="section-card">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-person-lines-fill" style="color:var(--yellow-keuf);"></i>
            Présences du chantier
        </div>
        <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
            <div id="pres-stats">
                <span class="stat-pill-sm green me-1">
                    <i class="bi bi-check-circle me-1"></i>{{ $statsPresences['present_aujourdhui'] ?? 0 }} présents
                </span>
                <span class="stat-pill-sm">
                    <i class="bi bi-people me-1"></i>{{ $statsPresences['total'] ?? 0 }} total
                </span>
            </div>
            @if($peutSaisirPresences)
            <a href="{{ route('presences.index', $projet) }}" class="btn btn-sm" style="background:#2563EB;color:white;border:none;border-radius:8px;font-size:.8rem;padding:.45rem 1rem;">
                <i class="bi bi-pencil-square me-1"></i>Modifier les présences
            </a>
            @endif
        </div>
    </div>
    <div class="p-3">
        <div id="pres-content">
            @forelse($projet->presences as $presence)
            <div class="presence-row">
                <div class="p-ava" style="background: {{ '#' . substr(md5($presence->employe->nom ?? ''), 0, 6) }}">
                    {{ strtoupper(substr($presence->employe->prenom ?? '', 0, 1) . substr($presence->employe->nom ?? '', 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <span style="font-weight:500;">
                        {{ $presence->employe->prenom ?? '' }} {{ $presence->employe->nom ?? '' }}
                    </span>
                    <div class="small text-secondary">
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
                @endphp
                <span class="status-badge-sm {{ $statutClass }}">
                    {{ ucfirst($presence->statut) }}
                </span>
            </div>
            @empty
            <p class="text-secondary text-center py-3">Aucune présence enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Signalements -->
<div class="section-card">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-exclamation-triangle" style="color:var(--red);"></i>
            Signalements — Ce chantier
        </div>
        <a href="#" class="btn btn-grey-keuf btn-sm">
            Ajouter <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="p-3" id="sig-list">
        @forelse($projet->signalements as $signalement)
        <div class="sig-card">
            <div class="d-flex align-items-center justify-content-between">
                <div class="fw-medium" style="font-size:.875rem;">{{ $signalement->type }}</div>
                @php
                    $prioriteClass = match($signalement->priorite) {
                        'critique' => 'bg-danger',
                        'haute' => 'bg-warning',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $prioriteClass }} bg-opacity-10 text-dark">{{ ucfirst($signalement->priorite) }}</span>
            </div>
            <div style="font-size:.8rem;color:#374151;margin:.25rem 0;">{{ $signalement->description }}</div>
            <div class="d-flex gap-2" style="font-size:.7rem;color:#9CA3AF;">
                <span><i class="bi bi-calendar3 me-1"></i>{{ $signalement->created_at }}</span>
                @if($signalement->auteur)
                <span><i class="bi bi-person me-1"></i>{{ $signalement->auteur->prenom ?? '' }}</span>
                @endif
                <span class="badge bg-light text-dark">{{ ucfirst($signalement->statut) }}</span>
            </div>
        </div>
        @empty
        <p class="text-secondary text-center py-3">Aucun signalement</p>
        @endforelse
    </div>
</div>

<!-- Modal pour ajouter une remarque -->
<div class="modal fade" id="remarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une remarque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="remarkForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="phase_id" class="form-label">Phase</label>
                        <select class="form-select" id="phase_id" name="phase_id" required>
                            <option value="">Sélectionner une phase</option>
                            @foreach($projet->phases as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contenu" class="form-label">Remarque</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn" style="background:var(--yellow-keuf);">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function ajouterRemarque() {
        const modal = new bootstrap.Modal(document.getElementById('remarkModal'));
        modal.show();
    }

    document.getElementById('remarkForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const phaseId = document.getElementById('phase_id').value;
        const contenu = document.getElementById('contenu').value;
        
        if (!phaseId || !contenu) return;
        
        fetch(`/phases/${phaseId}/remarques`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ contenu: contenu })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('remarkModal'));
                modal.hide();
                
                // Rafraîchir la page ou ajouter dynamiquement
                location.reload();
            }
        });
    });
</script>
@endpush