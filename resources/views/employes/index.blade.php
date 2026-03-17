@extends('layouts.app')

@section('title', 'Gestion des Employés')
@section('page-title', 'Employés')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .emp-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: white;
        flex-shrink: 0;
    }
    
    .statut-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        font-weight: 500;
    }
    
    .statut-badge.actif {
        background: #e2f3e4;
        color: var(--green);
    }
    
    .statut-badge.inactif {
        background: #e9e9ef;
        color: var(--grey-keuf);
    }
    
    .filter-pill {
        background: white;
        border: 1px solid #dee2ed;
        border-radius: 30px;
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
    }
    
    .filter-pill:hover {
        border-color: var(--yellow-keuf);
        background: #fefae6;
    }
    
    .filter-pill.active {
        background: var(--yellow-keuf);
        border-color: var(--yellow-keuf);
        color: black;
        font-weight: 500;
    }
    
    .btn-primary-custom {
        background: var(--blue-1);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary-custom:hover {
        background: var(--blue-2);
        color: white;
        transform: translateY(-1px);
    }
    
    .type-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 30px;
        background: #eef2f6;
        color: var(--grey-keuf);
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1 font-syne" style="font-size:1.5rem;">Employés</h2>
        <p class="text-secondary mb-0 small">Gérez les employés et leurs accès</p>
    </div>
    <a href="{{ route('employes.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-circle me-2"></i>Nouvel employé
    </a>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-people"></i></div>
            <div class="kpi-label">Total employés</div>
            <div class="kpi-value">{{ $stats['total'] }}</div>
            <div class="kpi-sub small text-secondary">{{ $stats['internes'] }} internes · {{ $stats['sous_traitants'] }} presta</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-person-check"></i></div>
            <div class="kpi-label">Présents aujourd'hui</div>
            <div class="kpi-value">{{ $stats['presences_aujourdhui'] }}</div>
            <div class="kpi-sub small text-secondary">{{ round(($stats['presences_aujourdhui'] / max($stats['actifs'], 1)) * 100) }}% des actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card orange">
            <div class="kpi-icon orange"><i class="bi bi-person-badge"></i></div>
            <div class="kpi-label">Employés actifs</div>
            <div class="kpi-value">{{ $stats['actifs'] }}</div>
            <div class="kpi-sub small text-secondary">disponibles</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-label">En congé / Inactifs</div>
            <div class="kpi-value">{{ $stats['total'] - $stats['actifs'] }}</div>
            <div class="kpi-sub small text-secondary">dont {{ $stats['total'] - $stats['actifs'] }} inactifs</div>
        </div>
    </div>
</div>
<!-- Employees Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <table id="employesDatatable" class="table table-hover align-middle" style="width:100%">
            <thead class="small text-secondary">
                <tr>
                    <th>Employé</th>
                    <th>Contact</th>
                    <th>Rôle</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employes as $employe)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="background: {{ $employe->avatar_couleur ?? '#2563EB' }};">
                                {{ $employe->avatar_initials ?? strtoupper(substr($employe->prenom, 0, 1) . substr($employe->nom, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $employe->prenom }} {{ $employe->nom }}</div>
                                @if($employe->user_id)
                                    <div class="small text-secondary">
                                        <i class="bi bi-person-check"></i> Compte actif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($employe->telephone)
                            <div class="small">{{ $employe->telephone }}</div>
                        @endif
                        @if($employe->email)
                            <div class="small text-secondary">{{ $employe->email }}</div>
                        @endif
                    </td>
                    <td>{{ $employe->role_metier ?? '-' }}</td>
                    <td>
                        <span class="type-badge">
                            {{ $employe->type === 'interne' ? 'Interne' : 'Sous-traitant' }}
                        </span>
                    </td>
                    <td>
                        <span class="statut-badge {{ $employe->actif ? 'actif' : 'inactif' }}">
                            {{ $employe->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('employes.show', $employe) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('employes.edit', $employe) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="deleteEmploye({{ $employe->id }})" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-syne fw-bold">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment supprimer cet employé ?</p>
                <p class="small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    let deleteId = null;
    let dataTable;
    
    $(document).ready(function() {
        // Initialize DataTable
        dataTable = $('#employesDatatable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 5 }
            ]
        });
    });

    // Delete employee
    function deleteEmploye(id) {
        deleteId = id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (!deleteId) return;

        fetch(`/employes/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Erreur lors de la suppression', 'error');
        });
    });

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        const toastIcon = document.querySelector('.toast-icon');
        
        toastMsg.innerText = msg;
        
        if (type === 'error') {
            toastIcon.style.background = 'var(--red)';
            toastIcon.innerHTML = '<i class="bi bi-exclamation text-white small"></i>';
            toast.style.borderLeftColor = 'var(--red)';
        } else {
            toastIcon.style.background = 'var(--green)';
            toastIcon.innerHTML = '<i class="bi bi-check text-white small"></i>';
            toast.style.borderLeftColor = 'var(--green)';
        }
        
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }
</script>
@endpush