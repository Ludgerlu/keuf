<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEUF ENGINEERING — @yield('title', 'Tableau de bord')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 + Icons + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar p-0" id="sidebar">
            <div class="p-4 pb-3 border-bottom border-white border-opacity-10">
                <div class="d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/logo/logo.jpg') }}" alt="KEUF" width="100" />
                </div>
            </div>
            <nav class="p-3 flex-grow-1">
                <div class="small text-uppercase px-3 pb-2" style="font-size: 0.625rem; letter-spacing: 1.5px">
                    Principal
                </div>
                <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }} d-flex align-items-center gap-2 mb-2 text-decoration-none" href="{{ route('dashboard') }}">
                    <i class="bi bi-house-door"></i><span>Tableau de bord</span>
                </a>
                
                @if(Auth::user()->hasPermission('projets', 'view'))
                <a class="nav-item d-flex align-items-center gap-2 mb-2 text-decoration-none" href="#">
                    <i class="bi bi-building"></i><span>Projets</span>
                </a>
                @endif

                <div class="small text-uppercase px-3 pb-2 mt-3" style="font-size: 0.625rem; letter-spacing: 1.5px">
                    Opérations
                </div>
                
                @if(Auth::user()->hasPermission('employes', 'view'))
                <a class="nav-item d-flex align-items-center gap-2 text-decoration-none" href="#">
                    <i class="bi bi-people"></i><span>Employés</span>
                </a>
                @endif

                @if(Auth::user()->hasPermission('stocks', 'view'))
                <a class="nav-item d-flex align-items-center gap-2 text-decoration-none" href="#">
                    <i class="bi bi-box"></i><span>Stocks</span>
                    @if(isset($stockAlertes) && $stockAlertes > 0)
                    <span class="nav-badge" id="nb-stocks">{{ $stockAlertes }}</span>
                    @endif
                </a>
                @endif

                @if(Auth::user()->hasPermission('presences', 'view'))
                <a class="nav-item d-flex align-items-center gap-2 text-decoration-none" href="#">
                    <i class="bi bi-calendar-check"></i><span>Présences</span>
                </a>
                @endif

                @if(Auth::user()->hasPermission('signalements', 'view'))
                <a class="nav-item d-flex align-items-center gap-2 text-decoration-none" href="#">
                    <i class="bi bi-exclamation-circle"></i><span>Signalements</span>
                </a>
                @endif

                <div class="small text-uppercase px-3 pb-2 mt-3" style="font-size: 0.625rem; letter-spacing: 1.5px">
                    Administration
                </div>
                
                @if(Auth::user()->role === 'admin')
                <a class="nav-item d-flex align-items-center gap-2 text-decoration-none" href="#">
                    <i class="bi bi-gear"></i><span>Paramètres</span>
                </a>
                @endif
            </nav>
            <div class="p-3 border-top d-flex align-items-center gap-2">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->prenom, 0, 1) . substr(Auth::user()->nom, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <div class="small fw-medium">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</div>
                    <div class="small text-secondary">
                        @switch(Auth::user()->role)
                            @case('admin') Administrateur @break
                            @case('directeur_technique') Directeur Technique @break
                            @case('controleur') Contrôleur @break
                            @case('gestionnaire_stock') Gestionnaire de Stock @break
                            @default {{ Auth::user()->role }}
                        @endswitch
                    </div>
                </div>
                <div class="text-secondary" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="cursor: pointer" title="Déconnexion">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </aside>

        <div class="main-area flex-grow-1">
            <header class="bg-white py-3 px-4 border-bottom sticky-top d-flex align-items-center justify-content-between">
                <button class="btn btn-link d-md-none p-0 text-dark" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="fw-bold font-syne" style="font-size: 1.125rem">@yield('page-title', 'Tableau de bord')</div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-secondary small" id="topbar-date">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                    <div class="position-relative">
                        <button class="btn btn-light border p-2 rounded position-relative" id="notificationsBtn">
                            <i class="bi bi-bell"></i>
                            @if(isset($notificationsCount) && $notificationsCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px"></span>
                            @endif
                        </button>
                    </div>
                </div>
            </header>

            <div class="p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <div class="toast-custom" id="toast">
        <div class="toast-icon"><i class="bi bi-check text-white small"></i></div>
        <span id="toast-msg">Action effectuée</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle && sidebar && overlay) {
                function toggleSidebar() { 
                    sidebar.classList.toggle('show'); 
                    overlay.classList.toggle('show'); 
                }
                
                sidebarToggle.addEventListener('click', toggleSidebar);
                overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>