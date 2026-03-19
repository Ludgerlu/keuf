{{-- resources/views/stocks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Stock - ' . $projet->nom)
@section('page-title', 'Gestion des Stocks')

@push('styles')
<style>
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .kpi-icon.red { background: #fee2e2; color: var(--red); }
    .kpi-icon.green { background: #e2f3e4; color: var(--green); }
    .kpi-icon.blue { background: #e2eafc; color: var(--blue-1); }
    
    .kpi-content {
        flex: 1;
    }
    
    .kpi-label {
        font-size: 0.75rem;
        color: #6B7280;
        margin-bottom: 0.25rem;
    }
    
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    
    .stock-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    
    .stock-table th {
        text-align: left;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stock-table td {
        padding: 1rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .progress-bar-custom {
        width: 120px;
        height: 6px;
        background: #eef2f6;
        border-radius: 30px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: 30px;
    }
    
    .progress-fill.red { background: var(--red); }
    .progress-fill.green { background: var(--green); }
    .progress-fill.yellow { background: #F59E0B; }
    
    .badge-status {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-weight: 500;
    }
    
    .badge-status.alerte {
        background: #fee2e2;
        color: var(--red);
    }
    
    .badge-status.ok {
        background: #e2f3e4;
        color: var(--green);
    }
    
    .action-btn {
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        font-size: 0.75rem;
        border: 1px solid #e5e7eb;
        background: white;
        color: #374151;
        transition: all 0.2s;
    }
    
    .action-btn:hover {
        background: #f9fafb;
        border-color: var(--yellow-keuf);
    }
    
    .action-btn i {
        margin-right: 0.25rem;
    }
    
    .mouvement-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid #edf0f5;
    }
    
    .mouvement-item:last-child {
        border-bottom: none;
    }
    
    .type-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
        border-radius: 30px;
        text-transform: uppercase;
    }
    
    .type-badge.reception {
        background: #e2f3e4;
        color: var(--green);
    }
    
    .type-badge.transfert {
        background: #e2eafc;
        color: var(--blue-1);
    }
    
    .type-badge.consommation {
        background: #fee2e2;
        color: var(--red);
    }
    
    .modal-content-custom {
        border-radius: 20px;
        border: none;
    }
    
    .modal-header-custom {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #edf0f5;
    }
    
    .modal-body-custom {
        padding: 1.5rem;
    }
    
    .modal-footer-custom {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #edf0f5;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-label-custom {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.6rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.9rem;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: var(--yellow-keuf);
    }
    
    .btn-primary-custom {
        background: var(--yellow-keuf);
        border: none;
        color: #1a1a1a;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
    }
    
    .btn-primary-custom:hover {
        background: #e6b800;
    }
    
    .btn-outline-custom {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
    }
    
    .btn-outline-custom:hover {
        background: #f9fafb;
    }
</style>
@endpush

@section('content')
<!-- Header with actions -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('projets.show', $projet) }}" class="btn btn-grey-keuf me-3">
            <i class="bi bi-arrow-left me-2"></i>Retour
        </a>
        <h2 class="fw-bold mb-0 font-syne">{{ $projet->nom }}</h2>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#receptionModal">
            <i class="bi bi-plus-circle me-2"></i>Réceptionner
        </button>
        <button class="btn btn-outline-custom" data-bs-toggle="modal" data-bs-target="#transfertModal">
            <i class="bi bi-arrow-up-right-circle me-2"></i>Transférer
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon red">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">En alerte</div>
            <div class="kpi-value">{{ $stats['alerte'] }}</div>
            <div class="small text-secondary">matériaux sous seuil</div>
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon green">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Stocks OK</div>
            <div class="kpi-value">{{ $stats['ok'] }}</div>
            <div class="small text-secondary">au-dessus du seuil</div>
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon blue">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Mouvements</div>
            <div class="kpi-value">{{ $stats['mouvements_mois'] }}</div>
            <div class="small text-secondary">ce mois-ci</div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 rounded-top-4" style="border-bottom: 1px solid #eef1f7;">
        <h6 class="fw-bold mb-0 font-syne">Stock actuel</h6>
        <button class="btn btn-link text-decoration-none p-0 small" data-bs-toggle="modal" data-bs-target="#newMaterialModal">
            <i class="bi bi-plus-circle me-1"></i>Nouveau matériau
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Matériau</th>
                    <th>Unité</th>
                    <th>Quantité</th>
                    <th>Seuil alerte</th>
                    <th>Niveau</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materiaux as $materiau)
                    @php
                        $stockItem = $stock[$materiau->id] ?? null;
                        $quantite = $stockItem ? $stockItem->quantite : 0;
                        $seuil = $stockItem ? $stockItem->seuil_alerte : 10;
                        $pourcentage = $seuil > 0 ? min(100, ($quantite / $seuil) * 100) : 0;
                        $estAlerte = $stockItem ? $stockItem->est_en_alerte : false;
                    @endphp
                    
                    <tr>
                        <td class="fw-medium">{{ $materiau->nom }}</td>
                        <td>{{ $materiau->unite }}</td>
                        <td class="fw-bold {{ $estAlerte ? 'text-danger' : 'text-success' }}">
                            {{ number_format($quantite, 2) }}
                        </td>
                        <td>{{ number_format($seuil, 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-bar-custom">
                                    <div class="progress-fill {{ $pourcentage < 50 ? 'red' : ($pourcentage < 80 ? 'yellow' : 'green') }}" 
                                         style="width: {{ $pourcentage }}%"></div>
                                </div>
                                <span class="small text-secondary">{{ number_format($pourcentage, 0) }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($estAlerte)
                                <span class="badge-status alerte">⚠ Alerte</span>
                            @else
                                <span class="badge-status ok">✓ OK</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="action-btn" onclick="openApprovisionnement('{{ $materiau->id }}', '{{ $materiau->nom }}')">
                                    <i class="bi bi-plus"></i>Appro
                                </button>
                                <button class="action-btn" onclick="openTransfertMaterial('{{ $materiau->id }}', '{{ $materiau->nom }}')">
                                    <i class="bi bi-arrow-right"></i>Transférer
                                </button>
                                @if($stockItem)
                                <button class="action-btn" onclick="openSeuilModal('{{ $stockItem->id }}', '{{ $materiau->nom }}', {{ $seuil }})">
                                    <i class="bi bi-gear"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">
                            Aucun matériau en stock
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Movements -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 rounded-top-4" style="border-bottom: 1px solid #eef1f7;">
        <h6 class="fw-bold mb-0 font-syne">Mouvements récents</h6>
    </div>
    
    <div class="p-3">
        @forelse($recentMovements as $mouvement)
            <div class="mouvement-item">
                <div>
                    <span class="type-badge {{ $mouvement->type }}">
                        {{ $mouvement->type }}
                    </span>
                </div>
                
                <div class="flex-grow-1">
                    <div class="fw-medium">{{ $mouvement->materiau->nom }}</div>
                    <div class="small text-secondary">
                        @if($mouvement->type == 'reception')
                            De: {{ $mouvement->fournisseur->nom ?? 'Fournisseur' }}
                        @elseif($mouvement->type == 'transfert')
                            @if($mouvement->source_projet_id == $projet->id)
                                Vers: {{ $mouvement->destProjet->nom }}
                            @else
                                De: {{ $mouvement->sourceProjet->nom }}
                            @endif
                        @else
                            Phase: {{ $mouvement->phase->nom ?? 'N/A' }}
                        @endif
                    </div>
                </div>
                
                <div class="text-end">
                    <div class="fw-bold {{ $mouvement->type == 'consommation' ? 'text-danger' : 'text-success' }}">
                        {{ $mouvement->type == 'consommation' ? '-' : '+' }}{{ number_format($mouvement->quantite, 2) }} {{ $mouvement->materiau->unite }}
                    </div>
                    <div class="small text-secondary">{{ $mouvement->created_at }}</div>
                </div>
                
                <div class="small text-secondary">
                    {{ $mouvement->effectuePar->prenom ?? '' }} {{ $mouvement->effectuePar->nom ?? '' }}
                </div>
            </div>
        @empty
            <p class="text-center text-secondary py-3">Aucun mouvement récent</p>
        @endforelse
    </div>
</div>

<!-- Reception Modal -->
<div class="modal fade" id="receptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-custom">
            <form id="receptionForm">
                @csrf
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title font-syne fw-bold">Réceptionner des matériaux</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body modal-body-custom">
                    <div class="form-group">
                        <label class="form-label-custom">Matériau *</label>
                        <select name="materiau_id" class="form-control-custom" required>
                            <option value="">Sélectionner un matériau</option>
                            @foreach($materiaux as $materiau)
                                <option value="{{ $materiau->id }}">{{ $materiau->nom }} ({{ $materiau->unite }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Quantité reçue *</label>
                        <input type="number" name="quantite" class="form-control-custom" step="0.001" min="0.001" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Prix unitaire (optionnel)</label>
                        <input type="number" name="prix_unitaire" class="form-control-custom" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Fournisseur</label>
                        <select name="fournisseur_id" class="form-control-custom" id="fournisseurSelect">
                            <option value="">Sélectionner un fournisseur</option>
                            @foreach($fournisseurs ?? [] as $fournisseur)
                                <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom }}</option>
                            @endforeach
                            <option value="new">+ Nouveau fournisseur</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="newFournisseurGroup" style="display: none;">
                        <label class="form-label-custom">Nom du fournisseur</label>
                        <input type="text" name="fournisseur_nom" class="form-control-custom" placeholder="ex: CIMENCAM">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Référence document</label>
                        <input type="text" name="reference_doc" class="form-control-custom" placeholder="N° bon de livraison...">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Date de réception</label>
                        <input type="date" name="date_reception" class="form-control-custom" value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Note</label>
                        <textarea name="note" class="form-control-custom" rows="2" placeholder="Informations complémentaires..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Confirmer la réception</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfert Modal -->
<div class="modal fade" id="transfertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-custom">
            <form id="transfertForm">
                @csrf
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title font-syne fw-bold">Transférer vers un chantier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body modal-body-custom">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Le stock sera déduit de {{ $projet->nom }} et ajouté au chantier destinataire.
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Matériau *</label>
                        <select name="materiau_id" class="form-control-custom" required>
                            <option value="">Sélectionner un matériau</option>
                            @foreach($materiaux as $materiau)
                                @if(isset($stock[$materiau->id]) && $stock[$materiau->id]->quantite > 0)
                                    <option value="{{ $materiau->id }}" 
                                            data-dispo="{{ $stock[$materiau->id]->quantite }}">
                                        {{ $materiau->nom }} - {{ number_format($stock[$materiau->id]->quantite, 2) }} {{ $materiau->unite }} dispo.
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Quantité à transférer *</label>
                        <input type="number" name="quantite" class="form-control-custom" step="0.001" min="0.001" required>
                        <small class="text-secondary" id="dispoLabel"></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Chantier destinataire *</label>
                        <select name="dest_projet_id" class="form-control-custom" required>
                            <option value="">Sélectionner un chantier</option>
                            @foreach($activeProjects as $project)
                                <option value="{{ $project->id }}">{{ $project->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Phase concernée (optionnel)</label>
                        <select name="phase_id" class="form-control-custom">
                            <option value="">Sélectionner une phase</option>
                            @foreach($phases as $phase)
                                <option value="{{ $phase->id }}">{{ $phase->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Note</label>
                        <textarea name="note" class="form-control-custom" rows="2" placeholder="Motif du transfert..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Effectuer le transfert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Material Modal -->
<div class="modal fade" id="newMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-custom">
            <form id="newMaterialForm">
                @csrf
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title font-syne fw-bold">Nouveau matériau</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body modal-body-custom">
                    <div class="form-group">
                        <label class="form-label-custom">Nom du matériau *</label>
                        <input type="text" name="nom" class="form-control-custom" placeholder="ex: Ciment Portland" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Unité de mesure *</label>
                        <select name="unite" class="form-control-custom" required>
                            <option value="">Sélectionner une unité</option>
                            <option value="sac">Sac</option>
                            <option value="barre">Barre</option>
                            <option value="m³">Mètre cube (m³)</option>
                            <option value="m²">Mètre carré (m²)</option>
                            <option value="mètre">Mètre linéaire</option>
                            <option value="pièce">Pièce</option>
                            <option value="kg">Kilogramme (kg)</option>
                            <option value="tonne">Tonne</option>
                            <option value="litre">Litre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Catégorie (optionnel)</label>
                        <input type="text" name="categorie" class="form-control-custom" placeholder="ex: Ciment, Fer, Bois...">
                    </div>
                </div>
                
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Créer le matériau</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Seuil Modal -->
<div class="modal fade" id="seuilModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-custom">
            <form id="seuilForm">
                @csrf
                @method('PUT')
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title font-syne fw-bold">Modifier le seuil d'alerte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body modal-body-custom">
                    <p class="mb-3">Matériau: <span id="seuilMaterialName" class="fw-medium"></span></p>
                    
                    <div class="form-group">
                        <label class="form-label-custom">Nouveau seuil d'alerte *</label>
                        <input type="number" name="seuil_alerte" id="seuilValue" class="form-control-custom" step="0.001" min="0" required>
                        <small class="text-secondary">Le stock passera en alerte en-dessous de cette valeur</small>
                    </div>
                </div>
                
                <div class="modal-footer modal-footer-custom">
                    <input type="hidden" name="stock_id" id="seuilStockId">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-custom">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-custom" id="toast">
    <div class="toast-icon"><i class="bi bi-check text-white small"></i></div>
    <span id="toast-msg">Action effectuée</span>
</div>
@endsection

@push('scripts')
<script>
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Fournisseur toggle
        const fournisseurSelect = document.getElementById('fournisseurSelect');
        if (fournisseurSelect) {
            fournisseurSelect.addEventListener('change', function() {
                const newGroup = document.getElementById('newFournisseurGroup');
                newGroup.style.display = this.value === 'new' ? 'block' : 'none';
            });
        }
        
        // Update disponibility label in transfert modal
        const materiauSelect = document.querySelector('select[name="materiau_id"]');
        if (materiauSelect) {
            materiauSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const dispo = selected.getAttribute('data-dispo');
                const dispoLabel = document.getElementById('dispoLabel');
                if (dispoLabel && dispo) {
                    dispoLabel.textContent = `Quantité disponible: ${dispo}`;
                }
            });
        }
    });

    // Reception form submit
    document.getElementById('receptionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("stocks.receive", $projet) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    });

    // Transfert form submit
    document.getElementById('transfertForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("stocks.transfer", $projet) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    });

    // New material form submit
    document.getElementById('newMaterialForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('#', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    });

    // Open approvisionnement modal
    window.openApprovisionnement = function(materiauId, materiauNom) {
        const modal = new bootstrap.Modal(document.getElementById('receptionModal'));
        
        // Pre-select material
        const select = document.querySelector('#receptionModal select[name="materiau_id"]');
        if (select) {
            select.value = materiauId;
        }
        
        modal.show();
    };

    // Open transfert modal for specific material
    window.openTransfertMaterial = function(materiauId, materiauNom) {
        const modal = new bootstrap.Modal(document.getElementById('transfertModal'));
        
        // Pre-select material
        const select = document.querySelector('#transfertModal select[name="materiau_id"]');
        if (select) {
            select.value = materiauId;
            // Trigger change to show disponibility
            const event = new Event('change');
            select.dispatchEvent(event);
        }
        
        modal.show();
    };

    // Open seuil modal
    window.openSeuilModal = function(stockId, materiauNom, seuilActuel) {
        document.getElementById('seuilMaterialName').textContent = materiauNom;
        document.getElementById('seuilValue').value = seuilActuel;
        document.getElementById('seuilStockId').value = stockId;
        
        const modal = new bootstrap.Modal(document.getElementById('seuilModal'));
        modal.show();
    };

    // Seuil form submit
    document.getElementById('seuilForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const stockId = document.getElementById('seuilStockId').value;
        const formData = new FormData(this);
        
        fetch(`/stocks/${stockId}/threshold`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-METHOD-OVERRIDE': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    });

    // Toast function
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        const toastIcon = toast.querySelector('.toast-icon');
        
        toastMsg.textContent = message;
        
        if (type === 'error') {
            toastIcon.style.background = 'var(--red)';
            toastIcon.innerHTML = '<i class="bi bi-exclamation text-white small"></i>';
        } else {
            toastIcon.style.background = 'var(--green)';
            toastIcon.innerHTML = '<i class="bi bi-check text-white small"></i>';
        }
        
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }
</script>
@endpush