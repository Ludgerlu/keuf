@extends('layouts.app')

@section('title', 'Gestion des Projets')
@section('page-title')
    <div class="fw-bold font-syne">
        <i class="bi bi-kanban me-2" style="color:var(--yellow-keuf);"></i>Gestion des Projets
    </div>
@endsection

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        padding: 1.5rem;
        transition: all 0.2s;
    }
    .stat-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.02);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-icon.blue { background: #e2eafc; color: #2563EB; }
    .stat-icon.yellow { background: #fef3c7; color: #D97706; }
    .stat-icon.green { background: #e2f3e4; color: #059669; }
    .stat-icon.red { background: #fee2e2; color: #DC2626; }
    
    .badge {
        padding: 0.35rem 0.9rem;
        border-radius: 30px;
        font-weight: 500;
        font-size: 0.7rem;
    }
    .badge.en-cours { background: #e2eafc; color: #2563EB; }
    .badge.retard { background: #fee2e2; color: #DC2626; }
    .badge.verification { background: #fef3c7; color: #D97706; }
    .badge.debut { background: #e2f3e4; color: #059669; }
    .badge.termine { background: #e5e7eb; color: #374151; }
    
    .badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .badge.en-cours .badge-dot { background: #2563EB; }
    .badge.retard .badge-dot { background: #DC2626; }
    .badge.verification .badge-dot { background: #D97706; }
    .badge.debut .badge-dot { background: #059669; }
    .badge.termine .badge-dot { background: #374151; }
    
    .prog-bar {
        width: 100px;
        height: 6px;
        background: #edf0f5;
        border-radius: 30px;
        overflow: hidden;
    }
    .prog-fill {
        height: 100%;
        border-radius: 30px;
    }
    
    .btn-primary-custom {
        background: var(--yellow-keuf);
        border: none;
        color: black;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 12px;
        transition: all 0.2s;
    }
    .btn-primary-custom:hover {
        background: #c29e36;
        color: black;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin: 1rem 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 3px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--yellow-keuf) !important;
        color: black !important;
        border: none !important;
    }
</style>
@endpush

@section('content')
<!-- Statistiques -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center h-100">
            <div class="stat-icon blue me-3"><i class="bi bi-building"></i></div>
            <div>
                <span class="text-secondary text-uppercase small fw-semibold">Projets actifs</span>
                <h3 class="fw-bold mb-0 font-syne">{{ $stats['actifs'] }}</h3>
                <!-- <small class="text-success">
                    <i class="bi bi-arrow-up"></i> 
                    {{ $stats['actifs'] - ($stats['termines'] ?? 0) }} en cours
                </small> -->
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center h-100">
            <div class="stat-icon yellow me-3"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <span class="text-secondary text-uppercase small fw-semibold">En retard</span>
                <h3 class="fw-bold mb-0 font-syne">{{ $stats['en_retard'] }}</h3>
                <!-- <small class="text-danger"><i class="bi bi-clock"></i> Action requise</small> -->
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center h-100">
            <div class="stat-icon green me-3"><i class="bi bi-check-circle"></i></div>
            <div>
                <span class="text-secondary text-uppercase small fw-semibold">Terminés</span>
                <h3 class="fw-bold mb-0 font-syne">{{ $stats['termines'] }}</h3>
                <!-- <small class="text-secondary">depuis création</small> -->
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center h-100">
            <div class="stat-icon red me-3"><i class="bi bi-currency-exchange"></i></div>
            <div>
                <span class="text-secondary text-uppercase small fw-semibold">Budget total</span>
                <h3 class="fw-bold mb-0 font-syne">{{ number_format($stats['budget_total'], 0, ',', ' ') }}</h3>
                <small class="text-secondary">FCFA</small>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des projets -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-0 font-syne d-flex align-items-center">
                    <i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--yellow-keuf);"></i>
                    Liste des projets
                </h2>
                <!-- <p class="text-secondary small mt-1">{{ $projets->total() }} projet(s) au total</p> -->
            </div>
            <a class="btn btn-primary-custom" href="{{ route('projets.create') }}">
                <i class="bi bi-plus-circle me-2"></i>Nouveau projet
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        <table id="projetsDatatable" class="table table-hover align-middle" style="width:100%">
            <thead class="small text-secondary">
                <tr>
                    <th class="px-3">Projet</th>
                    <th>Client</th>
                    <th>Phases</th>
                    <th>Statut</th>
                    <th>Avancement</th>
                    <th>Date fin</th>
                    <th>Budget FCFA</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projets as $projet)
                <tr>
                    <td class="px-3">
                        <div class="fw-medium">{{ $projet->nom }}</div>
                        <div class="small text-secondary">{{ $projet->reference }}</div>
                    </td>
                    <td class="small">{{ $projet->client_nom }}</td>
                    <td class="small text-secondary">{{ $projet->phases_count }} phase(s)</td>
                    <td>
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
                        <span class="badge {{ $statutClass }}">
                            <span class="badge-dot"></span>{{ $statutText }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="prog-bar">
                                <div class="prog-fill" style="width:{{ $projet->progression }}%; 
                                    background: {{ $projet->statut == 'retard' ? '#DC2626' : ($projet->statut == 'verification' ? '#D97706' : '#2563EB') }};">
                                </div>
                            </div>
                            <span class="small text-secondary">{{ $projet->progression }}%</span>
                        </div>
                        @if($projet->phaseActuelle)
                            <div class="small text-secondary mt-1">
                                Phase: {{ $projet->phaseActuelle->nom }}
                            </div>
                        @endif
                    </td>
                    <td class="small {{ $projet->statut == 'retard' ? 'text-danger' : '' }}">
                        {{ $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') : 'N/A' }}
                        @if($projet->statut == 'retard')
                            <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="En retard"></i>
                        @endif
                    </td>
                    <td class="fw-semibold small">{{ number_format($projet->budget, 0, ',', ' ') }}</td>
                    <td>
                        <a href="{{ route('projets.show', $projet) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Détail <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="text-secondary mb-0">Aucun projet trouvé</p>
                        <a href="{{ route('projets.create') }}" class="btn btn-primary-custom btn-sm mt-2">
                            Créer le premier projet
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $projets->withQueryString()->links() }}
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialisation de DataTable avec options françaises
        $('#projetsDatatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 7 }
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            initComplete: function() {
                $('.dataTables_filter input').attr('placeholder', 'Rechercher...');
            }
        });
    });

    // Pour les toasts (si nécessaire)
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        const toastIcon = document.querySelector('.toast-icon');
        
        toastMsg.innerText = message;
        
        if (type === 'error') {
            toastIcon.style.background = '#DC2626';
            toastIcon.innerHTML = '<i class="bi bi-exclamation text-white small"></i>';
            toast.style.borderLeftColor = '#DC2626';
        } else if (type === 'info') {
            toastIcon.style.background = '#2563EB';
            toastIcon.innerHTML = '<i class="bi bi-info text-white small"></i>';
            toast.style.borderLeftColor = '#2563EB';
        } else {
            toastIcon.style.background = '#059669';
            toastIcon.innerHTML = '<i class="bi bi-check text-white small"></i>';
            toast.style.borderLeftColor = '#059669';
        }
        
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
</script>
@endpush