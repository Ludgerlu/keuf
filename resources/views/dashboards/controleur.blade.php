@extends('layouts.app')

@section('title', 'Tableau de bord - Contrôle')
@section('page-title', 'Tableau de bord contrôle')

@section('content')
<!-- KPIs Contrôleur -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-label">Présences aujourd'hui</div>
            <div class="kpi-value">{{ $presencesCount }}</div>
            <div class="kpi-sub small text-secondary">
                <span class="text-danger">{{ $absentsCount }} absent(s)</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="kpi-card orange">
            <div class="kpi-icon orange"><i class="bi bi-exclamation-circle"></i></div>
            <div class="kpi-label">Signalements ouverts</div>
            <div class="kpi-value">{{ $signalementsOuverts->count() }}</div>
            <div class="kpi-sub small text-secondary">
                @if(isset($signalementsByPriority['critique']))
                    <span class="text-danger">{{ $signalementsByPriority['critique'] }} critique(s)</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-label">Projets à vérifier</div>
            <div class="kpi-value">{{ $projetsAVerifier->count() }}</div>
            <div class="kpi-sub small text-secondary">en phase de vérification</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Pointage du jour -->
    <div class="col-md-6">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Pointage du jour</h6>
                <span class="small text-secondary">{{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-secondary bg-light">
                            <tr>
                                <th>Employé</th>
                                <th>Projet</th>
                                <th>Arrivée</th>
                                <th>Départ</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($presencesAujourdhui as $presence)
                            <tr>
                                <td>
                                    <div class="fw-medium small">{{ $presence->employe->prenom }} {{ $presence->employe->nom }}</div>
                                    <div class="small text-secondary">{{ $presence->employe->role_metier }}</div>
                                </td>
                                <td>{{ $presence->projet->nom }}</td>
                                <td>{{ $presence->heure_debut ? \Carbon\Carbon::parse($presence->heure_debut)->format('H:i') : '-' }}</td>
                                <td>{{ $presence->heure_fin ? \Carbon\Carbon::parse($presence->heure_fin)->format('H:i') : '-' }}</td>
                                <td>
                                    @switch($presence->statut)
                                        @case('present')
                                            <span class="badge bg-success">Présent</span>
                                            @break
                                        @case('absent')
                                            <span class="badge bg-danger">Absent</span>
                                            @break
                                        @case('demi_journee')
                                            <span class="badge bg-warning">Demi-journée</span>
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-clock fs-1"></i>
                                    <p class="mt-2">Aucun pointage pour aujourd'hui</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <a href="#" class="btn btn-grey-keuf btn-sm w-100">
                    Voir tous les pointages
                </a>
            </div>
        </div>
    </div>

    <!-- Signalements prioritaires -->
    <div class="col-md-6">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Signalements prioritaires</h6>
                <a href="#" class="btn btn-grey-keuf btn-sm">
                    Voir tous
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($signalementsOuverts->whereIn('priorite', ['critique', 'haute'])->take(5) as $sig)
                    <div class="list-group-item px-3 py-3" onclick="window.location='#'" style="cursor: pointer">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium small">
                                @switch($sig->type)
                                    @case('avancement') <i class="bi bi-graph-up me-1"></i> Avancement @break
                                    @case('probleme') <i class="bi bi-exclamation-triangle me-1"></i> Problème @break
                                    @case('materiaux') <i class="bi bi-box me-1"></i> Matériaux @break
                                    @case('securite') <i class="bi bi-shield me-1"></i> Sécurité @break
                                    @case('livraison') <i class="bi bi-truck me-1"></i> Livraison @break
                                @endswitch
                            </span>
                            @switch($sig->priorite)
                                @case('critique')
                                    <span class="badge bg-danger">Critique</span>
                                    @break
                                @case('haute')
                                    <span class="badge bg-warning">Haute</span>
                                    @break
                            @endswitch
                        </div>
                        <p class="small mb-2">{{ Str::limit($sig->description, 100) }}</p>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span><i class="bi bi-building me-1"></i> {{ $sig->projet->nom }}</span>
                            <span><i class="bi bi-person me-1"></i> {{ $sig->auteur->prenom }}</span>
                            <span><i class="bi bi-clock me-1"></i> {{ $sig->created_at }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-check-circle fs-1"></i>
                        <p class="mt-2">Aucun signalement prioritaire</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Projets en vérification -->
    <div class="col-md-12 mt-3">
        <div class="card border">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 font-syne">Projets en phase de vérification</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($projetsAVerifier as $projet)
                    <div class="col-md-4 mb-3">
                        <div class="card border h-100">
                            <div class="card-body">
                                <h6 class="fw-bold">{{ $projet->nom }}</h6>
                                <p class="small text-secondary mb-2">Client: {{ $projet->client_nom ?? 'N/A' }}</p>
                                
                                @if($projet->phaseActuelle)
                                <div class="mb-2">
                                    <span class="small text-secondary">Phase actuelle:</span>
                                    <div class="fw-medium">{{ $projet->phaseActuelle->nom }}</div>
                                </div>
                                @endif

                                <div class="mb-2">
                                    <span class="small text-secondary">Avancement total:</span>
                                    <div class="prog-bar-custom mt-1">
                                        <div class="prog-fill" style="width: {{ $projet->progression }}%; background: var(--blue-1)"></div>
                                    </div>
                                    <div class="small text-secondary mt-1">{{ number_format($projet->progression, 0) }}%</div>
                                </div>

                                <div class="d-flex justify-content-between small text-secondary mb-2">
                                    <span><i class="bi bi-calendar"></i> Début: {{ $projet->date_debut ? \Carbon\Carbon::parse($projet->date_debut)->format('d/m/Y') : '-' }}</span>
                                    <span><i class="bi bi-calendar-check"></i> Fin prévue: {{ $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') : '-' }}</span>
                                </div>

                                <a href="#" class="btn btn-grey-keuf btn-sm w-100 mt-2">
                                    Vérifier le projet
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-secondary py-4">
                        <i class="bi bi-check-circle fs-1"></i>
                        <p class="mt-2">Aucun projet en phase de vérification</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection