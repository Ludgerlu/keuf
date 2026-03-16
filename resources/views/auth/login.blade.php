<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEUF ENGINEERING — Connexion</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --blue-1: #757575;
            --blue-dark: #6b6b6b;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f8f9ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            background: white;
            pointer-events: none;
        }
        
        .login-shape.s1 {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }
        
        .login-shape.s2 {
            width: 250px;
            height: 250px;
            bottom: -50px;
            left: -80px;
        }
        
        .login-shape.s3 {
            width: 150px;
            height: 150px;
            top: 40%;
            left: 10%;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2.75rem;
            width: 420px;
            max-width: 90%;
            position: relative;
            z-index: 2;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.25);
            animation: fadeUp 0.5s ease both;
        }
        
        .login-logo-icon {
            width: 52px;
            height: 52px;
            background: var(--blue-1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.125rem;
            color: white;
        }
        
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-shape s1"></div>
    <div class="login-shape s2"></div>
    <div class="login-shape s3"></div>
    
    <div class="login-card">
        <div class="d-flex align-items-center gap-3 mb-4 justify-content-center">
            <!-- Make sure to place your logo in public/images/logo.jpg -->
            <img src="{{ asset('assets/logo/logo.jpg') }}" alt="KEUF Engineering" width="100" onerror="this.style.display='none'">
            <div class="login-logo-icon" style="display: none;">KE</div>
        </div>
        
        <h2 class="text-center fw-bold mb-1" style="font-family: 'Syne', sans-serif; font-size: 1.5rem;">Connexion</h2>
        <p class="text-center text-muted mb-4 small">Espace réservé au personnel KEUF</p>
        
        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                @foreach ($errors->all() as $error)
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Success Message (for logout, etc.) -->
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('status') }}
                </div>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label small fw-medium text-uppercase">
                    <i class="bi bi-envelope me-1"></i> Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    placeholder="admin@keuf.cm" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                >
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-medium text-uppercase">
                    <i class="bi bi-lock me-1"></i> Mot de passe
                </label>
                <div class="position-relative">
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="••••••••"
                        required
                        id="password"
                    >
                    <button 
                        type="button" 
                        class="btn btn-link position-absolute end-0 top-0 mt-2 me-2 text-muted"
                        onclick="togglePasswordVisibility()"
                        style="text-decoration: none;"
                    >
                        <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="remember">
                        Se souvenir de moi
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: var(--blue-1);">
                        Mot de passe oublié?
                    </a>
                @endif
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                Se connecter <i class="bi bi-arrow-right ms-2"></i>
            </button>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    &copy; {{ date('Y') }} KEUF ENGINEERING. Tous droits réservés.
                </small>
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
    </script>

    <!-- Bootstrap JS (optional, for additional features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>