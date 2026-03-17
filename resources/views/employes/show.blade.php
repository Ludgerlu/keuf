@extends('layouts.app')

@section('title', $employe->prenom . ' ' . $employe->nom)
@section('page-title', 'Détail employé')

@push('styles')
<style>
    .emp-detail-header {
        background: linear-gradient(135deg, {{ $employe->avatar_couleur ?? '#2563EB' }} 0%, #1a1a1a 100%);
        color: white;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .emp-detail-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 2rem;
        color: white;
        border: 4px solid var(--yellow-keuf);
        background: rgba(255,255,255,0.2);
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .info-item {
        background: #f9fafc;
        border-radius: 16px;
        padding: 1rem;
    }
    
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-weight: 500;
        font-size: 1rem;
    }
    
    .detail-section {
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .detail-section-head {
        padding: 1rem 1.25rem;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .detail-section-title {
        font-weight: 700;
        font-size: 1rem;
        font-family: 'Syne', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .permission-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 30px;
        background: #eef2f6;
        display: inline-block;
        margin: 0.1rem;
    }
    
    .permission-badge.autorise {
        background: #e2f3e4;
        color: var(--green);
    }
    
    .permission-badge.refuse {
        background: #ffe2e2;
        color: var(--red);
    }
    
    .statut-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        font-weight: 500;
    }
    
    .statut-badge.actif {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }
    
    .statut-badge.inactif {
        background: rgba(0,0,0,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb & Actions -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <nav style="--bs-breadcrumb-divider:'›';">
        <ol class="breadcrumb" style="font-size:.8125rem;">
            <li class="breadcrumb-item"><a href="{{ route('employes.index') }}" class="text-decoration-none">Employés</a></li>
            <li class="breadcrumb-item active">{{ $employe->prenom }} {{ $employe->nom }}</li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <a href="{{ route('employes.edit', $employe) }}" class="btn btn-outline-primary-custom">
            <i class="bi bi-pencil me-2"></i>Modifier
        </a>
        <button class="btn btn-outline-danger" onclick="deleteEmploye({{ $employe->id }})">
            <i class="bi bi-trash me-2"></i>Supprimer
        </button>
    </div>
</div>

<!-- Employee Header -->
<div class="emp-detail-header">
    <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="emp-detail-avatar">
            {{ $employe->avatar_initials ?? strtoupper(substr($employe->prenom, 0, 1) . substr($employe->nom, 0, 1)) }}
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                <h2 class="fw-bold mb-0">{{ $employe->prenom }} {{ $employe->nom }}</h2>
                <span class="statut-badge {{ $employe->actif ? 'actif' : 'inactif' }}">
                    {{ $employe->actif ? 'Actif' : 'Inactif' }}
                </span>
                @if($employe->user_id)
                    <span class="statut-badge actif">
                        <i class="bi bi-person-check"></i> Compte actif
                    </span>
                @endif
            </div>
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <span class="badge en-cours">{{ $employe->role_metier ?? 'Non spécifié' }}</span>
                <span class="small text-white-50">
                    <i class="bi bi-briefcase me-1"></i>
                    {{ $employe->type === 'interne' ? 'Interne KEUF' : 'Sous-traitant' }}
                </span>
                @if($employe->date_embauche)
                <span class="small text-white-50">
                    <i class="bi bi-calendar3 me-1"></i>
                    Embauché le {{ \Carbon\Carbon::parse($employe->date_embauche)->format('d/m/Y') }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Contact Info Grid -->
<div class="info-grid mb-4">
    @if($employe->telephone)
    <div class="info-item">
        <div class="info-label">Téléphone</div>
        <div class="info-value">{{ $employe->telephone }}</div>
    </div>
    @endif
    
    @if($employe->email)
    <div class="info-item">
        <div class="info-label">Email</div>
        <div class="info-value">{{ $employe->email }}</div>
    </div>
    @endif
    
    @if($employe->date_naissance)
    <div class="info-item">
        <div class="info-label">Date de naissance</div>
        <div class="info-value">{{ \Carbon\Carbon::parse($employe->date_naissance)->format('d/m/Y') }}</div>
    </div>
    @endif
    
    @if($employe->adresse)
    <div class="info-item">
        <div class="info-label">Adresse</div>
        <div class="info-value">{{ $employe->adresse }}</div>
    </div>
    @endif
</div>

<!-- Contact d'urgence si existant -->
@if($employe->urgence_nom || $employe->urgence_telephone)
<div class="info-grid mb-4">
    @if($employe->urgence_nom)
    <div class="info-item">
        <div class="info-label">Contact d'urgence</div>
        <div class="info-value">{{ $employe->urgence_nom }}</div>
    </div>
    @endif
    
    @if($employe->urgence_telephone)
    <div class="info-item">
        <div class="info-label">Téléphone d'urgence</div>
        <div class="info-value">{{ $employe->urgence_telephone }}</div>
    </div>
    @endif
</div>
@endif

<div class="row g-4">
    <!-- Présences récentes -->
    <div class="col-md-12">
        <div class="detail-section">
            <div class="detail-section-head">
                <div class="detail-section-title">
                    <i class="bi bi-calendar-check" style="color:var(--yellow-keuf);"></i>
                    Présences récentes
                </div>
                <a href="#" class="btn btn-sm btn-outline-primary-custom">
                    Voir tout
                </a>
            </div>
            <div class="p-3">
                @forelse($employe->presences->take(5) as $presence)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="small">{{ \Carbon\Carbon::parse($presence->date)->format('l d/m/Y') }}</span>
                    <span class="badge {{ $presence->statut === 'present' ? 'en-cours' : ($presence->statut === 'absent' ? 'retard' : 'verification') }}">
                        {{ ucfirst($presence->statut) }}
                    </span>
                </div>
                @empty
                <p class="text-secondary text-center py-3">Aucune présence récente</p>
                @endforelse
                
                <div class="d-flex justify-content-between mt-3 small">
                    <span>Total présences: {{ $statsPresences['present'] }}</span>
                    <span>Absences: {{ $statsPresences['absent'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    @if($employe->id && !empty($permissions))
    <div class="col-md-12">
        <div class="detail-section">
            <div class="detail-section-head">
                <div class="detail-section-title">
                    <i class="bi bi-shield-lock" style="color:var(--yellow-keuf);"></i>
                    Permissions d'accès
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    @foreach($permissions as $module => $perms)
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-2">
                            @switch($module)
                                @case('projets') 📋 Projets @break
                                @case('employes') 👥 Employés @break
                                @case('stocks') 📦 Stocks @break
                                @case('presences') ⏱️ Présences @break
                                @case('signalements') ⚠️ Signalements @break
                                @case('facturation') 💰 Facturation @break
                                @case('imports') 🌍 Imports @break
                                @default {{ ucfirst($module) }}
                            @endswitch
                        </h6>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($perms as $perm)
                            <span class="permission-badge {{ $perm->autorise ? 'autorise' : 'refuse' }}">
                                @switch($perm->action)
                                    @case('creer') Créer @break
                                    @case('lire') Lire @break
                                    @case('modifier') Modifier @break
                                    @case('supprimer') Supprimer @break
                                    @case('exporter') Exporter @break
                                    @default {{ ucfirst($perm->action) }}
                                @endswitch
                                @if($perm->projet)
                                    <small>({{ $perm->projet->nom }})</small>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($employe->notes)
    <div class="col-md-12">
        <div class="detail-section">
            <div class="detail-section-head">
                <div class="detail-section-title">
                    <i class="bi bi-chat-text" style="color:var(--yellow-keuf);"></i>
                    Notes
                </div>
            </div>
            <div class="p-3">
                <p class="mb-0">{{ $employe->notes }}</p>
            </div>
        </div>
    </div>
    @endif
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
                <p>Voulez-vous vraiment supprimer {{ $employe->prenom }} {{ $employe->nom }} ?</p>
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
<script>
    let deleteId = {{ $employe->id }};

    function deleteEmploye(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
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
                setTimeout(() => {
                    window.location.href = '{{ route("employes.index") }}';
                }, 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Erreur lors de la suppression', 'error');
        });
    });

    function assignProject() {
        // Implémenter l'affectation à un projet
        showToast('Fonctionnalité à venir', 'info');
    }

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        const toastIcon = document.querySelector('.toast-icon');
        
        toastMsg.innerText = msg;
        
        if (type === 'error') {
            toastIcon.style.background = 'var(--red)';
            toastIcon.innerHTML = '<i class="bi bi-exclamation text-white small"></i>';
            toast.style.borderLeftColor = 'var(--red)';
        } else if (type === 'info') {
            toastIcon.style.background = 'var(--blue-1)';
            toastIcon.innerHTML = '<i class="bi bi-info text-white small"></i>';
            toast.style.borderLeftColor = 'var(--blue-1)';
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