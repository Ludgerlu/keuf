{{-- resources/views/presences/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Présences - ' . $projet->nom)
@section('page-title', 'Gestion des Présences')

@push('styles')
<style>
    .presence-container {
        display: flex;
        gap: 1.5rem;
        height: calc(100vh - 180px);
        min-height: 600px;
    }
    
    .pool-panel {
        width: 350px;
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .presence-panel {
        flex: 1;
        background: white;
        border-radius: 20px;
        border: 1px solid #edf0f5;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .panel-header {
        padding: 1rem 1.25rem;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .panel-title {
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .panel-count {
        background: #eef2f6;
        color: #374151;
        padding: 0.25rem 0.75rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .search-box {
        padding: 0.75rem;
        border-bottom: 1px solid #edf0f5;
    }
    
    .search-input {
        width: 100%;
        padding: 0.6rem 1rem 0.6rem 2.2rem;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        font-size: 0.85rem;
        background: #f9fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E") no-repeat 10px center;
        background-size: 16px;
    }
    
    .persons-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
    }
    
    .section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9CA3AF;
        padding: 0.5rem 0.5rem 0.25rem;
    }
    
    .person-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem;
        background: #f9fafc;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    
    .person-card:hover {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }
    
    .person-card.dragging {
        opacity: 0.5;
    }
    
    .person-ava {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    
    .person-ava.square {
        border-radius: 8px;
    }
    
    .person-info {
        flex: 1;
        min-width: 0;
    }
    
    .person-name {
        font-weight: 500;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .person-role {
        font-size: 0.7rem;
        color: #6B7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .add-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: white;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #374151;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .add-btn:hover {
        background: var(--yellow-keuf);
        color: white;
        border-color: var(--yellow-keuf);
    }
    
    .present-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem;
        background: #f9fafc;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        border-left: 4px solid var(--green);
    }
    
    .present-card.is-prest {
        border-left-color: #F59E0B;
    }
    
    .status-sel {
        padding: 0.25rem 0.5rem;
        border-radius: 30px;
        border: 1px solid #e5e7eb;
        font-size: 0.7rem;
        background: white;
        min-width: 100px;
    }
    
    .status-sel.present { border-left: 3px solid var(--green); }
    .status-sel.en_retard { border-left: 3px solid #F59E0B; }
    .status-sel.absent_justifie { border-left: 3px solid #3b82f6; }
    .status-sel.absent { border-left: 3px solid var(--red); }
    
    .time-input {
        width: 70px;
        padding: 0.25rem 0.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        font-size: 0.7rem;
        text-align: center;
    }
    
    .hx-input {
        width: 60px;
        padding: 0.25rem 0.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        font-size: 0.7rem;
        text-align: center;
    }
    
    .remove-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: white;
        border: 1px solid #fee2e2;
        color: var(--red);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        cursor: pointer;
        flex-shrink: 0;
    }
    
    .remove-btn:hover {
        background: var(--red);
        color: white;
    }
    
    .prest-header-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem;
        background: #f3f4f6;
        border-radius: 12px;
        margin-bottom: 0.25rem;
    }
    
    .prest-status-badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.5rem;
        border-radius: 30px;
        font-weight: 500;
    }
    
    .prest-status-badge.present {
        background: #e2f3e4;
        color: var(--green);
    }
    
    .prest-status-badge.absent {
        background: #fee2e2;
        color: var(--red);
    }

    .context-bar {
        background: white;
        border-radius: 16px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        border: 1px solid #edf0f5;
    }
    
    .date-input {
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
    }
    
    .btn-save {
        background: var(--yellow-keuf);
        border: none;
        color: #1a1a1a;
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .btn-save:hover {
        background: #e6b800;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #9CA3AF;
    }
    
    .drop-active {
        border: 2px dashed var(--yellow-keuf);
        background: #fefae6;
    }
    
    .stats-pills {
        display: flex;
        gap: 0.5rem;
    }
    
    .stat-pill {
        background: #f3f4f6;
        padding: 0.25rem 1rem;
        border-radius: 30px;
        font-size: 0.8rem;
    }
    
    .stat-pill.green {
        background: #e2f3e4;
        color: var(--green);
    }

    .time-group {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }
    
    .time-label {
        font-size: 0.6rem;
        color: #6B7280;
        margin-right: 0.1rem;
    }
</style>
@endpush

@section('content')
<!-- Context Bar -->
<div class="context-bar">
    <div>
        <div class="small text-secondary mb-1">Date</div>
        <input type="date" id="presence-date" class="date-input" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
    </div>
    <div class="vr"></div>
    <div>
        <div class="small text-secondary mb-1">Chantier</div>
        <div class="fw-medium">{{ $projet->nom }}</div>
    </div>
    <div class="vr"></div>
    <div class="stats-pills">
        <span class="stat-pill green">
            <i class="bi bi-check-circle me-1"></i>{{ $stats['present'] }} présents
        </span>
        <span class="stat-pill">
            <i class="bi bi-people me-1"></i>{{ $stats['total'] }} total
        </span>
    </div>
    <button class="btn-save ms-auto" id="savePresences">
        <i class="bi bi-save me-2"></i>Enregistrer
    </button>
</div>

<!-- Dual Panel Presence Management -->
<div class="presence-container">
    <!-- LEFT: Employee Pool -->
    <div class="pool-panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="bi bi-people"></i>
                Pool d'employés
            </div>
        </div>
        
        <div class="search-box">
            <input type="text" class="search-input" id="poolSearch" placeholder="Rechercher par nom ou rôle...">
        </div>
        
        <div class="persons-list" id="poolList">
            <!-- Internal Employees -->
            <div class="section-label">Employés internes</div>
            @forelse($assignedEmployees as $employe)
            <div class="person-card" 
                 draggable="true" 
                 ondragstart="dragStart(event, {{ $employe->id }})"
                 onclick="addToPresence({{ $employe->id }})">
                <div class="person-ava" style="background: {{ '#' . substr(md5($employe->nom), 0, 6) }}">
                    {{ $employe->initials }}
                </div>
                <div class="person-info">
                    <div class="person-name">{{ $employe->prenom }} {{ $employe->nom }}</div>
                    <div class="person-role">{{ $employe->profession ?? 'Employé' }}</div>
                </div>
                <button class="add-btn" onclick="event.stopPropagation(); addToPresence({{ $employe->id }})">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            @empty
            <div class="empty-state">
                Aucun employé assigné à ce chantier
            </div>
            @endforelse

            <!-- Prestataires -->
            @foreach($prestataireEmployees as $prestataireNom => $employees)
            <div class="section-label mt-2">Prestataire: {{ $prestataireNom }}</div>
            @foreach($employees as $affectation)
            <div class="person-card prest-emp"
                 draggable="true"
                 ondragstart="dragStart(event, {{ $affectation->employe->id }})"
                 onclick="addToPresence({{ $affectation->employe->id }})">
                <div class="person-ava" style="background: #F59E0B; width:30px; height:30px; font-size:0.7rem;">
                    {{ $affectation->employe->initials }}
                </div>
                <div class="person-info">
                    <div class="person-name">{{ $affectation->employe->prenom }} {{ $affectation->employe->nom }}</div>
                    <div class="person-role">{{ $affectation->employe->profession ?? 'Employé' }}</div>
                </div>
                <button class="add-btn" onclick="event.stopPropagation(); addToPresence({{ $affectation->employe->id }})">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>

    <!-- RIGHT: Today's Presences -->
    <div class="presence-panel" 
         id="presencePanel"
         ondragover="event.preventDefault(); this.classList.add('drop-active')"
         ondragleave="this.classList.remove('drop-active')"
         ondrop="dropOnPresence(event)">
        
        <div class="panel-header">
            <div class="panel-title">
                <i class="bi bi-check-circle" style="color: var(--green);"></i>
                Sur ce chantier aujourd'hui
            </div>
            <span class="panel-count" id="presentCount">{{ $todayPresences->count() }}</span>
        </div>
        
        <div class="persons-list" id="presenceList">
            @php
                $currentPrestataire = null;
            @endphp
            
            @forelse($todayPresences as $presence)
                @if($presence->employe->prestataire_nom != $currentPrestataire)
                    @if($currentPrestataire)
                        </div>
                    @endif
                    @if($presence->employe->prestataire_nom)
                        @php $currentPrestataire = $presence->employe->prestataire_nom; @endphp
                        <div class="section-label mt-2">
                            <i class="bi bi-building-gear me-1"></i>{{ $currentPrestataire }}
                        </div>
                    @else
                        @php $currentPrestataire = null; @endphp
                        <div class="section-label">Employés internes</div>
                    @endif
                @endif
                
                <div class="present-card {{ $presence->employe->prestataire_nom ? 'is-prest' : '' }}" 
                     id="presence_{{ $presence->employe_id }}"
                     data-employe-id="{{ $presence->employe_id }}">
                    
                    <div class="person-ava" style="background: {{ '#' . substr(md5($presence->employe->nom), 0, 6) }}; width:32px; height:32px;">
                        {{ $presence->employe->initials }}
                    </div>
                    
                    <div class="person-info">
                        <div class="person-name">{{ $presence->employe->prenom }} {{ $presence->employe->nom }}</div>
                        <div class="person-role">{{ $presence->employe->profession ?? 'Employé' }}</div>
                    </div>
                    
                    <select class="status-sel {{ $presence->statut }}" 
                            onchange="updatePresenceStatus('{{ $presence->employe_id }}', this.value)">
                        <option value="present" {{ $presence->statut == 'present' ? 'selected' : '' }}>Présent</option>
                        <option value="en_retard" {{ $presence->statut == 'en_retard' ? 'selected' : '' }}>En retard</option>
                        <option value="absent_justifie" {{ $presence->statut == 'absent_justifie' ? 'selected' : '' }}>Absent justifié</option>
                        <option value="absent" {{ $presence->statut == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                    
                    <div class="time-group">
                        <input type="time" class="time-input" 
                               value="{{ $presence->heure_debut ? $presence->heure_debut->format('H:i') : '08:00' }}"
                               onchange="updatePresenceTime('{{ $presence->employe_id }}', 'heure_debut', this.value)"
                               title="Heure de début">
                        <span class="time-label">-</span>
                        <input type="time" class="time-input" 
                               value="{{ $presence->heure_fin ? $presence->heure_fin->format('H:i') : '17:00' }}"
                               onchange="updatePresenceTime('{{ $presence->employe_id }}', 'heure_fin', this.value)"
                               title="Heure de fin">
                    </div>
                    
                    <input type="number" class="hx-input" 
                           value="{{ $presence->heures_sup }}"
                           onchange="updatePresenceHx('{{ $presence->employe_id }}', this.value)"
                           placeholder="H+" 
                           min="0"
                           max="24"
                           step="0.5"
                           title="Heures supplémentaires">
                    
                    <button class="remove-btn" onclick="removeFromPresence('{{ $presence->employe_id }}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @empty
            <div class="empty-state" id="emptyPresenceState">
                <div class="empty-icon mb-2">👈</div>
                <div class="empty-txt">
                    Cliquez sur un employé ou<br>glissez-le ici pour l'ajouter
                </div>
            </div>
            @endforelse
            
            @if($currentPrestataire)
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Hidden form to submit data -->
<form id="presenceForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="presences" id="presencesData">
</form>
@endsection

@push('scripts')
<script>
    // ═══════════════════════════════════════════════════════
    // PRESENCE DATA — initialised from server
    // ═══════════════════════════════════════════════════════
    let presenceData = {!! $todayPresences->mapWithKeys(function($p) {
        return [$p->employe_id => [
            'employe_id' => $p->employe_id,
            'statut' => $p->statut,
            'heure_debut' => $p->heure_debut ? $p->heure_debut->format('H:i') : '08:00',
            'heure_fin' => $p->heure_fin ? $p->heure_fin->format('H:i') : '17:00',
            'heures_sup' => $p->heures_sup,
            'note' => $p->note
        ]];
    })->toJson() !!};

    let currentDate = '{{ date('Y-m-d') }}';

    // ═══════════════════════════════════════════════════════
    // DRAG & DROP
    // ═══════════════════════════════════════════════════════
    function dragStart(event, employeId) {
        event.dataTransfer.setData('text/plain', employeId);
    }

    function dropOnPresence(event) {
        event.preventDefault();
        document.getElementById('presencePanel').classList.remove('drop-active');
        
        const employeId = event.dataTransfer.getData('text/plain');
        if (employeId) {
            addToPresence(employeId);
        }
    }

    // ═══════════════════════════════════════════════════════
    // ADD EMPLOYEE
    // ═══════════════════════════════════════════════════════
    function addToPresence(employeId) {
        if (presenceData[employeId]) {
            showToast('Cet employé est déjà dans la liste', 'error');
            return;
        }

        // Find employee card in pool
        const sourceCard = document.querySelector(`.person-card[onclick*="${employeId}"]`);
        if (!sourceCard) return;

        // Get employee data
        const name  = sourceCard.querySelector('.person-name').textContent;
        const role  = sourceCard.querySelector('.person-role').textContent;
        const ava   = sourceCard.querySelector('.person-ava').textContent;
        const avaBg = sourceCard.querySelector('.person-ava').style.background;
        const isPrest = sourceCard.classList.contains('prest-emp');

        // Add to presence data
        presenceData[employeId] = {
            employe_id: parseInt(employeId),
            statut: 'present',
            heure_debut: '08:00',
            heure_fin: '17:00',
            heures_sup: 0,
        };

        // Hide empty state
        const emptyState = document.getElementById('emptyPresenceState');
        if (emptyState) emptyState.style.display = 'none';

        // Create presence card
        const presenceList = document.getElementById('presenceList');
        const card = document.createElement('div');
        card.className = `present-card ${isPrest ? 'is-prest' : ''}`;
        card.id = `presence_${employeId}`;
        card.setAttribute('data-employe-id', employeId);
        
        card.innerHTML = `
            <div class="person-ava" style="${avaBg}; width:32px; height:32px;">${ava}</div>
            <div class="person-info">
                <div class="person-name">${name}</div>
                <div class="person-role">${role}</div>
            </div>
            <select class="status-sel present" onchange="updatePresenceStatus('${employeId}', this.value)">
                <option value="present" selected>Présent</option>
                <option value="en_retard">En retard</option>
                <option value="absent_justifie">Absent justifié</option>
                <option value="absent">Absent</option>
            </select>
            <div class="time-group">
                <input type="time" class="time-input" value="08:00" onchange="updatePresenceTime('${employeId}', 'heure_debut', this.value)" title="Heure de début">
                <span class="time-label">-</span>
                <input type="time" class="time-input" value="17:00" onchange="updatePresenceTime('${employeId}', 'heure_fin', this.value)" title="Heure de fin">
            </div>
            <input type="number" class="hx-input" value="0" onchange="updatePresenceHx('${employeId}', this.value)" placeholder="H+" min="0" max="24" step="0.5" title="Heures supplémentaires">
            <button class="remove-btn" onclick="removeFromPresence('${employeId}')">
                <i class="bi bi-x"></i>
            </button>
        `;

        presenceList.appendChild(card);
        updatePresentCount();
        showToast(`${name} ajouté(e) aux présences`);
    }

    // ═══════════════════════════════════════════════════════
    // REMOVE EMPLOYEE
    // ═══════════════════════════════════════════════════════
    function removeFromPresence(employeId) {
        const card = document.getElementById(`presence_${employeId}`);
        if (card) {
            card.remove();
            delete presenceData[employeId];
            updatePresentCount();
            
            if (Object.keys(presenceData).length === 0) {
                const es = document.getElementById('emptyPresenceState');
                if (es) es.style.display = 'block';
            }
        }
    }

    // ═══════════════════════════════════════════════════════
    // UPDATE STATUS
    // ═══════════════════════════════════════════════════════
    function updatePresenceStatus(employeId, status) {
        if (presenceData[employeId]) {
            presenceData[employeId].statut = status;

            const card = document.getElementById(`presence_${employeId}`);
            if (card) {
                const select = card.querySelector('.status-sel');
                select.className = `status-sel ${status}`;
            }
        }
    }

    // ═══════════════════════════════════════════════════════
    // UPDATE TIME
    // ═══════════════════════════════════════════════════════
    function updatePresenceTime(employeId, field, value) {
        if (presenceData[employeId]) {
            presenceData[employeId][field] = value;
        }
    }

    // ═══════════════════════════════════════════════════════
    // UPDATE OVERTIME HOURS
    // ═══════════════════════════════════════════════════════
    function updatePresenceHx(employeId, value) {
        const parsed = parseFloat(value) || 0;
        if (presenceData[employeId]) {
            presenceData[employeId].heures_sup = parsed;
        }
    }

    // ═══════════════════════════════════════════════════════
    // COUNT BADGE
    // ═══════════════════════════════════════════════════════
    function updatePresentCount() {
        const count = Object.keys(presenceData).length;
        document.getElementById('presentCount').textContent = count;
    }

    // ═══════════════════════════════════════════════════════
    // SEARCH POOL
    // ═══════════════════════════════════════════════════════
    document.getElementById('poolSearch')?.addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        const cards  = document.querySelectorAll('#poolList .person-card');
        cards.forEach(card => {
            card.style.display = card.textContent.toLowerCase().includes(search) ? 'flex' : 'none';
        });
    });

    // ═══════════════════════════════════════════════════════
    // DATE CHANGE
    // ═══════════════════════════════════════════════════════
    document.getElementById('presence-date')?.addEventListener('change', function(e) {
        currentDate = e.target.value;
        window.location.href = `{{ route('presences.index', $projet) }}?date=${currentDate}`;
    });

    // ═══════════════════════════════════════════════════════
    // SAVE
    // ═══════════════════════════════════════════════════════
    document.getElementById('savePresences')?.addEventListener('click', function() {
        const presences = Object.values(presenceData);

        if (presences.length === 0) {
            showToast('Aucune présence à enregistrer', 'error');
            return;
        }

        // Convert string IDs to integers
        const formattedPresences = presences.map(p => ({
            ...p,
            employe_id: parseInt(p.employe_id)
        }));

        const payload = { 
            presences: formattedPresences, 
            date: currentDate 
        };

        fetch(`{{ route('presences.store', $projet) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
            } else {
                showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    });

    // ═══════════════════════════════════════════════════════
    // TOAST
    // ═══════════════════════════════════════════════════════
    function showToast(message, type = 'success') {
        let toast = document.getElementById('dynamicToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'dynamicToast';
            toast.className = 'toast-custom';
            toast.innerHTML = `
                <div class="toast-icon"><i class="bi bi-check text-white small"></i></div>
                <span id="toast-msg"></span>
            `;
            document.body.appendChild(toast);
        }
        
        const toastMsg  = document.getElementById('toast-msg');
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