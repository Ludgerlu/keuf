@extends('layouts.app')

@section('title', 'Tableau de bord - Gestion des stocks')
@section('page-title', 'Gestion des stocks')

@section('content')
<!-- KPIS Stock -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-label">Stock central</div>
            <div class="kpi-value">{{ $stockCentral->stocks->count() ?? 0 }}</div>
            <div class="kpi-sub small text-secondary">matériaux référencés</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-label">Alertes stock</div>
            <div class="kpi-value">{{ $stockAlerts->count() }}</div>
            <div class="kpi-sub small text-secondary">sous le seuil critique</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-truck"></i></div>
            <div class="kpi-label">Fournisseurs</div>
            <div class="kpi-value">{{ $fournisseursActifs }}</div>
            <div class="kpi-sub small text-secondary">actifs</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card orange">
            <div class="kpi-icon orange"><i class="bi bi-arrow-left-right"></i></div>
            <div class="kpi-label">Mouvements</div>
            <div class="kpi-value">{{ $mouvementsRecents->count() }}</div>
            <div class="kpi-sub small text-secondary">30 derniers jours</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Alertes stock critiques -->
    <div class="col-md-6">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Alertes stock critiques</h6>
                <a href="#" class="btn btn-grey-keuf btn-sm">
                    Gérer les stocks
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-secondary bg-light">
                            <tr>
                                <th>Matériau</th>
                                <th>Projet</th>
                                <th>Quantité</th>
                                <th>Seuil</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockAlerts as $stock)
                            <tr>
                                <td>
                                    <div class="fw-medium small">{{ $stock->materiau->nom }}</div>
                                    <div class="small text-secondary">{{ $stock->materiau->categorie ?? 'Non catégorisé' }}</div>
                                </td>
                                <td>{{ $stock->projet->nom }}</td>
                                <td class="fw-bold text-danger">{{ number_format($stock->quantite, 0) }} {{ $stock->materiau->unite }}</td>
                                <td>{{ number_format($stock->seuil_alerte, 0) }} {{ $stock->materiau->unite }}</td>
                                <td>
                                    <span class="badge bg-danger">Urgent</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-check-circle fs-1"></i>
                                    <p class="mt-2">Aucune alerte stock</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Mouvements récents -->
    <div class="col-md-6">
        <div class="card border">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 font-syne">Mouvements récents</h6>
                <button class="btn btn-grey-keuf btn-sm" data-bs-toggle="modal" data-bs-target="#newMovementModal">
                    <i class="bi bi-plus"></i> Nouveau mouvement
                </button>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($mouvementsRecents as $mvt)
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-medium small">{{ $mvt->materiau_nom }}</span>
                            @switch($mvt->type)
                                @case('entree')
                                    <span class="badge bg-success">Entrée</span>
                                    @break
                                @case('sortie')
                                    <span class="badge bg-danger">Sortie</span>
                                    @break
                                @case('transfert')
                                    <span class="badge bg-info">Transfert</span>
                                    @break
                                @case('retour')
                                    <span class="badge bg-warning">Retour</span>
                                    @break
                                @case('consommation')
                                    <span class="badge bg-secondary">Consommation</span>
                                    @break
                            @endswitch
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-secondary">
                                <i class="bi bi-box"></i> {{ number_format($mvt->quantite, 0) }} {{ $mvt->unite }}
                            </span>
                            <span class="text-secondary">
                                @if($mvt->source_nom)
                                    <i class="bi bi-arrow-right"></i> {{ $mvt->source_nom }}
                                @endif
                                @if($mvt->dest_nom)
                                    <i class="bi bi-arrow-left"></i> {{ $mvt->dest_nom }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between small text-secondary mt-1">
                            <span><i class="bi bi-person"></i> {{ $mvt->user_prenom }} {{ $mvt->user_nom }}</span>
                            <span><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($mvt->created_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-arrow-left-right fs-1"></i>
                        <p class="mt-2">Aucun mouvement récent</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par catégorie -->
    <div class="col-md-6 mt-3">
        <div class="card border">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 font-syne">Répartition par catégorie</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Commandes en cours / Imports -->
    <div class="col-md-6 mt-3">
        <div class="card border">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 font-syne">Commandes en cours</h6>
            </div>
            <div class="card-body">
                @php
                    $importsEnCours = \App\Models\ImportInternational::whereIn('statut', ['en_transit', 'dedouanement'])->with('fournisseur')->get();
                @endphp

                @forelse($importsEnCours as $import)
                <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
                    <div>
                        <div class="fw-medium small">{{ $import->fournisseur->nom }}</div>
                        <div class="small text-secondary">
                            {{ $import->pays_origine }} · {{ number_format($import->montant_devise, 0) }} {{ $import->devise }}
                        </div>
                        <div class="small text-secondary">
                            Arrivée prévue: {{ $import->date_arrivee_prevue ? \Carbon\Carbon::parse($import->date_arrivee_prevue)->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>
                    <span class="badge bg-warning">{{ $import->statut === 'en_transit' ? 'En transit' : 'Dédouanement' }}</span>
                </div>
                @empty
                <div class="text-center text-secondary py-3">
                    <i class="bi bi-truck"></i>
                    <p class="mt-2 small">Aucune commande en cours</p>
                </div>
                @endforelse

                <a href="#" class="btn btn-grey-keuf btn-sm w-100 mt-2">
                    Voir toutes les commandes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouveau Mouvement -->
<div class="modal fade" id="newMovementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau mouvement de stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type de mouvement</label>
                        <select name="type" class="form-select" required>
                            <option value="entree">Entrée (fournisseur)</option>
                            <option value="sortie">Sortie</option>
                            <option value="transfert">Transfert entre projets</option>
                            <option value="consommation">Consommation</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Matériau</label>
                        <select name="materiau_id" class="form-select" required>
                            @foreach(\App\Models\Materiau::all() as $materiau)
                                <option value="{{ $materiau->id }}">{{ $materiau->nom }} ({{ $materiau->unite }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantité</label>
                        <input type="number" step="0.001" name="quantite" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Projet source</label>
                        <select name="source_projet_id" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            @foreach(\App\Models\Projet::all() as $projet)
                                <option value="{{ $projet->id }}">{{ $projet->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Projet destination</label>
                        <select name="dest_projet_id" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            @foreach(\App\Models\Projet::all() as $projet)
                                <option value="{{ $projet->id }}">{{ $projet->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Référence document</label>
                        <input type="text" name="reference_doc" class="form-control" placeholder="N° BL / Facture">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des catégories
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    const categories = @json($statsParCategorie);
    const labels = categories.map(c => c.categorie || 'Non catégorisé');
    const data = categories.map(c => c.quantite_totale);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#2b6f9e',
                    '#28a745',
                    '#fd7e14',
                    '#dc3545',
                    '#6c757d',
                    '#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection