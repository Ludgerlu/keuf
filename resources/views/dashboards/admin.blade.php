@extends('layouts.app')

@section('title', 'Tableau de bord - Administration')
@section('page-title', 'Tableau de bord')

@section('content')
<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-building"></i></div>
            <div class="kpi-label">Projets actifs</div>
            <div class="kpi-value" id="kpi-projets">{{ $projetsActifs }}</div>
            <div class="kpi-sub small text-secondary" id="kpi-projets-sub">
                {{ $projetsParStatut['en_cours'] ?? 0 }} en cours · 
                {{ $projetsParStatut['verification'] ?? 0 }} en vérification
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-people"></i></div>
            <div class="kpi-label">Présences aujourd'hui</div>
            <div class="kpi-value" id="kpi-presences">{{ $presencesToday }}</div>
            <div class="kpi-sub small text-secondary" id="kpi-presences-sub">
                sur {{ $presencesToday + $absentsToday }} · {{ $absentsToday }} absent(s)
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-box"></i></div>
            <div class="kpi-label">Alertes stocks</div>
            <div class="kpi-value" id="kpi-stocks">{{ $stockAlertsCount }}</div>
            <div class="kpi-sub small text-secondary">matériaux sous le seuil</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card orange">
            <div class="kpi-icon orange"><i class="bi bi-exclamation-circle"></i></div>
            <div class="kpi-label">Signalements</div>
            <div class="kpi-value" id="kpi-sigs">{{ $signalementsCount }}</div>
            <div class="kpi-sub small text-secondary">en attente de traitement</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Alertes & Notifications -->
    <div class="col-md-12">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Alertes &amp; Notifications</h6>
                <a href="#" class="btn btn-grey-keuf btn-sm">
                    Stocks <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2" id="alertes-list">
                    @forelse($alertes as $alerte)
                    <div class="d-flex align-items-start gap-3 p-2 bg-light rounded" style="cursor: pointer;" onclick="window.location='{{ $alerte['link'] }}'">
                        <div class="alert-icon {{ $alerte['color'] }}"><i class="bi bi-{{ $alerte['icon'] }}"></i></div>
                        <div>
                            <div class="fw-medium small">{{ $alerte['title'] }}</div>
                            <div class="small text-secondary">{{ $alerte['description'] }}</div>
                            <div class="small text-secondary">
                                {{ $alerte['projet'] }} · {{ $alerte['date'] }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-check-circle fs-1"></i>
                        <p class="mt-2">Aucune alerte pour le moment</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Projets en cours -->
    <div class="col-md-12">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Projets en cours</h6>
                <a href="#" class="btn btn-grey-keuf btn-sm">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="p-3">
                <table class="table table-hover align-middle">
                    <thead class="small text-secondary bg-light">
                        <tr>
                            <th>Projet / Client</th>
                            <th>Statut</th>
                            <th>Avancement</th>
                            <th>Budget FCFA</th>
                        </tr>
                    </thead>
                    <tbody id="projets-tbody">
                        @foreach($projetsEnCours as $projet)
                        <tr onclick="window.location='#'" style="cursor: pointer">
                            <td>
                                <div class="fw-medium text-dark small">{{ $projet->nom }}</div>
                                <div class="small text-secondary">{{ $projet->client_nom ?? 'Client non spécifié' }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($projet->statut) {
                                        'en_cours' => 'en-cours',
                                        'retard' => 'retard',
                                        'verification' => 'verification',
                                        'debut' => 'debut',
                                        'termine' => 'termine',
                                        default => 'debut'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    <span class="badge-dot me-1"></span>
                                    @switch($projet->statut)
                                        @case('en_cours') En cours @break
                                        @case('retard') En retard @break
                                        @case('verification') En vérification @break
                                        @case('debut') Début @break
                                        @case('termine') Terminé @break
                                        @default {{ $projet->statut }}
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <div class="prog-bar-custom">
                                    @php
                                        $progressColor = match(true) {
                                            $projet->progression < 30 => 'var(--red)',
                                            $projet->progression < 70 => 'var(--orange)',
                                            default => 'var(--green)'
                                        };
                                    @endphp
                                    <div class="prog-fill" style="width: {{ $projet->progression }}%; background: {{ $progressColor }}"></div>
                                </div>
                                <div class="small text-secondary mt-1">{{ number_format($projet->progression, 0) }}%</div>
                            </td>
                            <td class="fw-semibold small">{{ number_format($projet->budget, 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Footer stats -->
            <div class="card-footer bg-light d-flex justify-content-around py-3">
                <div class="text-center">
                    <div class="fw-bold font-syne" style="color: var(--blue-1); font-size: 1.125rem">
                        {{ $projetsParStatut['en_cours'] ?? 0 }}
                    </div>
                    <div class="small text-uppercase text-secondary">En cours</div>
                </div>
                <div class="text-center">
                    <div class="fw-bold font-syne" style="color: var(--orange); font-size: 1.125rem">
                        {{ $projetsParStatut['verification'] ?? 0 }}
                    </div>
                    <div class="small text-uppercase text-secondary">Vérif.</div>
                </div>
                <div class="text-center">
                    <div class="fw-bold font-syne" style="color: var(--red); font-size: 1.125rem">
                        {{ $projetsParStatut['retard'] ?? 0 }}
                    </div>
                    <div class="small text-uppercase text-secondary">Retard</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="fw-bold font-syne mb-3">Chiffre d'affaires (mois)</h6>
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon green me-3"><i class="bi bi-currency-exchange"></i></div>
                        <div>
                            <div class="fs-4 fw-bold">{{ number_format($chiffreAffairesMois, 0, ',', ' ') }} FCFA</div>
                            <small class="text-secondary">Facturation du mois</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="fw-bold font-syne mb-3">Factures en attente</h6>
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon orange me-3"><i class="bi bi-receipt"></i></div>
                        <div>
                            <div class="fs-4 fw-bold">{{ $facturesEnAttente }}</div>
                            <small class="text-secondary">En attente de paiement</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="fw-bold font-syne mb-3">Budget total projets</h6>
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon blue me-3"><i class="bi bi-pie-chart"></i></div>
                        <div>
                            <div class="fs-4 fw-bold">{{ number_format($budgetTotal, 0, ',', ' ') }} FCFA</div>
                            <small class="text-secondary">Tous projets confondus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh KPIs every 5 minutes
    setInterval(function() {
        fetch('#')
            .then(response => response.json())
            .then(data => {
                document.getElementById('kpi-projets').textContent = data.projets_actifs;
                document.getElementById('kpi-presences').textContent = data.presences_aujourdhui;
                document.getElementById('kpi-stocks').textContent = data.alertes_stock;
                document.getElementById('kpi-sigs').textContent = data.signalements_ouverts;
                document.getElementById('nb-stocks').textContent = data.alertes_stock;
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }, 300000); // 5 minutes
</script>
@endpush