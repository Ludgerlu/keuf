@extends('layouts.app')

@section('title', 'Tableau de bord - Direction Technique')
@section('page-title', 'Tableau de bord technique')

@section('content')
<!-- KPIs spécifiques à la direction technique -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-building"></i></div>
            <div class="kpi-label">Projets en cours</div>
            <div class="kpi-value">{{ $projets->count() }}</div>
            <div class="kpi-sub small text-secondary">
                <span class="text-danger">{{ $projetsRetard }} en retard</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-label">Progression moyenne</div>
            <div class="kpi-value">{{ number_format($progressionMoyenne, 0) }}%</div>
            <div class="kpi-sub small text-secondary">tous projets confondus</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card orange">
            <div class="kpi-icon orange"><i class="bi bi-gear"></i></div>
            <div class="kpi-label">Phases en cours</div>
            <div class="kpi-value">{{ $phasesEnCours }}</div>
            <div class="kpi-sub small text-secondary">chantiers actifs</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-label">Signalements tech.</div>
            <div class="kpi-value">{{ $signalementsTechniques->count() }}</div>
            <div class="kpi-sub small text-secondary">à traiter</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Graphique d'avancement des projets -->
    <div class="col-md-8">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Avancement des projets</h6>
                <select class="form-select form-select-sm w-auto" id="projectFilter">
                    <option value="all">Tous les projets</option>
                    @foreach($projets as $projet)
                        <option value="{{ $projet->id }}">{{ $projet->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <canvas id="progressChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Phases critiques -->
    <div class="col-md-4">
        <div class="card border">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 font-syne">Phases critiques</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($projets->take(5) as $projet)
                        @if($projet->phaseActuelle)
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-medium small">{{ $projet->nom }}</span>
                                @if($projet->statut === 'retard')
                                    <span class="badge bg-danger">Retard</span>
                                @endif
                            </div>
                            <div class="small text-secondary mb-2">{{ $projet->phaseActuelle->nom }}</div>
                            <div class="prog-bar-custom mb-1">
                                <div class="prog-fill" style="width: {{ $projet->phaseActuelle->progression }}%; background: {{ $projet->phaseActuelle->progression < 50 ? 'var(--orange)' : 'var(--blue-1)' }}"></div>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-secondary">Progression: {{ $projet->phaseActuelle->progression }}%</span>
                                @if($projet->phaseActuelle->date_fin_prevue)
                                    <span class="text-secondary">
                                        <i class="bi bi-calendar"></i>
                                        {{ \Carbon\Carbon::parse($projet->phaseActuelle->date_fin_prevue)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Calendrier des phases -->
    <div class="col-md-12 mt-3">
        <div class="card border">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 font-syne">Planning des phases à venir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="small text-secondary bg-light">
                            <tr>
                                <th>Projet</th>
                                <th>Phase</th>
                                <th>Début prévu</th>
                                <th>Fin prévue</th>
                                <th>Responsable</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $phasesAVenir = collect();
                                foreach($projets as $projet) {
                                    foreach($projet->phases as $phase) {
                                        if(!$phase->date_debut_reelle && $phase->date_debut_prevue >= now()) {
                                            $phasesAVenir->push([
                                                'projet' => $projet->nom,
                                                'phase' => $phase->nom,
                                                'debut' => $phase->date_debut_prevue,
                                                'fin' => $phase->date_fin_prevue,
                                                'projet_id' => $projet->id
                                            ]);
                                        }
                                    }
                                }
                                $phasesAVenir = $phasesAVenir->sortBy('debut')->take(10);
                            @endphp

                            @forelse($phasesAVenir as $phase)
                            <tr onclick="window.location='#'" style="cursor: pointer">
                                <td>
                                    <div class="fw-medium text-dark small">{{ $phase['projet'] }}</div>
                                </td>
                                <td>{{ $phase['phase'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($phase['debut'])->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($phase['fin'])->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">À attribuer</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">Planifiée</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-calendar-check fs-1"></i>
                                    <p class="mt-2">Aucune phase planifiée</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Signalements techniques récents -->
    <div class="col-md-12 mt-3">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Signalements techniques récents</h6>
                <a href="#" class="btn btn-grey-keuf btn-sm">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-secondary bg-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Projet</th>
                                <th>Auteur</th>
                                <th>Priorité</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($signalementsTechniques as $sig)
                            <tr onclick="window.location='#'" style="cursor: pointer">
                                <td>
                                    @switch($sig->type)
                                        @case('avancement')
                                            <span class="badge bg-info">Avancement</span>
                                            @break
                                        @case('materiaux')
                                            <span class="badge bg-warning">Matériaux</span>
                                            @break
                                        @case('securite')
                                            <span class="badge bg-danger">Sécurité</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $sig->type }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="small">{{ Str::limit($sig->description, 50) }}</div>
                                </td>
                                <td>{{ $sig->projet->nom }}</td>
                                <td>{{ $sig->auteur->prenom }} {{ $sig->auteur->nom }}</td>
                                <td>
                                    @switch($sig->priorite)
                                        @case('critique')
                                            <span class="badge bg-danger">Critique</span>
                                            @break
                                        @case('haute')
                                            <span class="badge bg-warning">Haute</span>
                                            @break
                                        @case('normale')
                                            <span class="badge bg-info">Normale</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Basse</span>
                                    @endswitch
                                </td>
                                <td>{{ $sig->created_at }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-check-circle fs-1"></i>
                                    <p class="mt-2">Aucun signalement technique</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique d'avancement
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    // Préparer les données
    const projets = @json($projets);
    const labels = projets.map(p => p.nom.substring(0, 15) + '...');
    const progressData = projets.map(p => p.progression);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Avancement (%)',
                data: progressData,
                backgroundColor: progressData.map(p => 
                    p < 30 ? '#dc3545' : (p < 70 ? '#fd7e14' : '#28a745')
                ),
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        display: true,
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush
@endsection