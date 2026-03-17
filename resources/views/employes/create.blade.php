@extends('layouts.app')

@section('title', 'Nouvel employé')
@section('page-title', 'Employés')

@push('styles')
<style>
    .avatar-preview {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        color: white;
        transition: all 0.3s;
    }
    
    .color-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .color-dot:hover {
        transform: scale(1.1);
    }
    
    .color-dot.selected {
        border-color: #000;
        box-shadow: 0 0 0 2px var(--yellow-keuf);
    }
    
    .form-section {
        background: #f9fafc;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-section-title {
        font-weight: 600;
        font-size: 1rem;
        font-family: 'Syne', sans-serif;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2ed;
    }
    
    .permission-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid #eef1f7;
    }
    
    .permission-module {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        color: var(--blue-1);
    }
    
    .permission-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    
    .permission-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #f8fafc;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-size: 0.8rem;
    }
    
    .permission-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .projet-select {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        border: 1px solid #dee2ed;
        margin-left: 0.5rem;
    }
    
    .btn-primary-custom {
        background: var(--yellow-keuf);
        border: none;
        color: black;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        transition: 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary-custom:hover {
        background: #c29e36;
        color: black;
    }
    
    .btn-secondary-custom {
        background: white;
        border: 1px solid #dee2ed;
        color: #374151;
        font-weight: 500;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        transition: 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-secondary-custom:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<nav class="mb-4" style="--bs-breadcrumb-divider:'›';">
    <ol class="breadcrumb" style="font-size:.8125rem;">
        <li class="breadcrumb-item"><a href="{{ route('employes.index') }}" class="text-decoration-none">Employés</a></li>
        <li class="breadcrumb-item active">Nouvel employé</li>
    </ol>
</nav>

<!-- Main Form -->
<div class="section-card">
    <div class="section-card-head">
        <div class="section-card-title">
            <i class="bi bi-person-plus" style="color:var(--yellow-keuf);"></i>
            Nouvel employé
        </div>
    </div>
    
    <div class="p-4">
        <form id="employeForm" method="POST" action="{{ route('employes.store') }}">
            @csrf
            <!-- Identité -->
            <div class="form-section">
                <div class="form-section-title">Identité</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Prénom *</label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                               name="prenom" id="prenom" value="{{ old('prenom') }}" required 
                               oninput="updatePreview()">
                        @error('prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Nom *</label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                               name="nom" id="nom" value="{{ old('nom') }}" required 
                               oninput="updatePreview()">
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Type *</label>
                        <select class="form-select @error('type') is-invalid @enderror" name="type" id="type" required>
                            <option value="interne" {{ old('type') == 'interne' ? 'selected' : '' }}>Interne (KEUF)</option>
                            <option value="sous_traitant" {{ old('type') == 'sous_traitant' ? 'selected' : '' }}>Sous-traitant / Prestataire</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Rôle / Métier</label>
                        <input type="text" class="form-control @error('role_metier') is-invalid @enderror" 
                               name="role_metier" value="{{ old('role_metier') }}" 
                               placeholder="ex: Chef de chantier, Maçon, Électricien">
                        @error('role_metier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Statut</label>
                        <select class="form-select" name="actif">
                            <option value="1" selected>Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="form-section">
                <div class="form-section-title">Contact</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Téléphone</label>
                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                               name="telephone" value="{{ old('telephone') }}" 
                               placeholder="+237 6XX XXX XXX">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" 
                               placeholder="email@exemple.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Adresse -->
            <div class="form-section">
                <div class="form-section-title">Adresse</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label-custom">Adresse</label>
                        <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                               name="adresse" value="{{ old('adresse') }}" 
                               placeholder="Adresse complète">
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact d'urgence -->
            <div class="form-section">
                <div class="form-section-title">Contact d'urgence</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Nom du contact</label>
                        <input type="text" class="form-control @error('urgence_nom') is-invalid @enderror" 
                               name="urgence_nom" value="{{ old('urgence_nom') }}">
                        @error('urgence_nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Téléphone d'urgence</label>
                        <input type="tel" class="form-control @error('urgence_telephone') is-invalid @enderror" 
                               name="urgence_telephone" value="{{ old('urgence_telephone') }}">
                        @error('urgence_telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Permissions (visible seulement pour les internes) -->
            <div class="form-section" id="permissions-section">
                <div class="form-section-title">
                    <i class="bi bi-shield-lock me-2"></i>
                    Permissions d'accès à l'application
                </div>
                <p class="small text-secondary mb-3">
                    Définissez les actions que cet employé peut effectuer dans l'application.
                    Les permissions peuvent être globales ou limitées à des projets spécifiques.
                </p>

                <div class="row g-4">
                    @foreach($modules as $module)
                    <div class="col-md-6">
                        <div class="permission-card">
                            <div class="permission-module">
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
                            </div>
                            <div class="permission-actions">
                                @foreach($actions as $action)
                                <label class="permission-item">
                                    <input type="checkbox" class="permission-checkbox" 
                                           name="permissions[{{ $module }}][{{ $action }}][autorise]" 
                                           value="1"
                                           data-module="{{ $module }}"
                                           data-action="{{ $action }}">
                                    <span>
                                        @switch($action)
                                            @case('creer') Créer @break
                                            @case('lire') Lire @break
                                            @case('modifier') Modifier @break
                                            @case('supprimer') Supprimer @break
                                            @case('exporter') Exporter @break
                                            @default {{ ucfirst($action) }}
                                        @endswitch
                                    </span>
                                </label>
                                @endforeach
                            </div>
                            <div class="mt-2" id="projet-select-{{ $module }}" style="display: none;">
                                <select class="form-select form-select-sm projet-select" 
                                        name="permissions[{{ $module }}][projet_id]">
                                    <option value="">Tous les projets</option>
                                    @foreach($projets as $projet)
                                        <option value="{{ $projet->id }}">{{ $projet->nom }}</option>
                                    @endforeach
                                </select>
                                <small class="text-secondary">Limiter à un projet spécifique</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            <div class="form-section">
                <div class="form-section-title">Notes</div>
                <div class="row g-3">
                    <div class="col-12">
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  name="notes" rows="3" 
                                  placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-lg"></i> Créer l'employé
                </button>
                <a href="{{ route('employes.index') }}" class="btn btn-secondary-custom">
                    <i class="bi bi-x"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    
    // Show/hide permissions section based on type
    document.getElementById('type')?.addEventListener('change', function(e) {
        const section = document.getElementById('permissions-section');
        if (e.target.value === 'interne') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });

    // Show project selector when permission is checked
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            const module = this.dataset.module;
            const selector = document.getElementById(`projet-select-${module}`);
            
            // Check if any permission is checked for this module
            const anyChecked = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:checked`).length > 0;
            selector.style.display = anyChecked ? 'block' : 'none';
        });
    });

    // Form submission
    document.getElementById('employeForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("employes.index") }}';
                }, 1500);
            } else {
                if (data.errors) {
                    Object.values(data.errors).forEach(error => {
                        showToast(error[0], 'error');
                    });
                } else {
                    showToast(data.message || 'Erreur lors de la création', 'error');
                }
            }
        })
        .catch(error => {
            showToast('Erreur lors de la création', 'error');
            console.error(error);
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