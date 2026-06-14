<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-image: url('{{ ($facility instanceof \App\Models\Facility && $facility->logo) ? asset('storage/' . $facility->logo) : asset('images/logo.png') }}');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.75) 0%, rgba(118, 75, 162, 0.75) 100%);
            z-index: 0;
        }

        .login-card {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: stretch;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 820px;
            margin: 20px;
        }

        /* Left panel — logo */
        .login-logo-panel {
            width: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-logo-panel img {
            width: 100%;
            height: 100%;
            max-height: 340px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 4px 24px rgba(0,0,0,0.18));
        }

        /* Right panel — form */
        .login-form-panel {
            width: 50%;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .login-header {
            background: white;
            color: #333;
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .login-header h3 {
            margin: 0;
            font-weight: 700;
            color: #667eea;
        }

        .login-header p {
            margin: 0.5rem 0 0 0;
            color: #888;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 2rem;
            flex: 1;
        }

        .login-body .mb-3 {
            margin-bottom: 1.5rem;
        }

        .login-body .mb-3 label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .form-check {
            margin: 1rem 0;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        @media (max-width: 600px) {
            .login-card {
                flex-direction: column;
            }
            .login-logo-panel,
            .login-form-panel {
                width: 100%;
            }
            .login-logo-panel img {
                max-height: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">

        <!-- Left: Logo panel -->
        <div class="login-logo-panel">
            <img src="{{ ($facility instanceof \App\Models\Facility && $facility->logo) ? asset('storage/' . $facility->logo) : asset('images/logo.png') }}" alt="{{ $facility->name ?? config('app.name') }}">
        </div>

        <!-- Right: Form panel -->
        <div class="login-form-panel">
            <div class="login-header">
                <h3><i class="fas fa-user-circle me-2"></i>Login</h3>
                <p>Welcome back! Please sign in to your account.</p>
            </div>

            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="login">{{ __('Email or Username') }}</label>
                        <input id="login"
                               type="text"
                               class="form-control @error('login') is-invalid @enderror"
                               name="login"
                               value="{{ old('login', $rememberedLogin ?? '') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="Enter your email or username">
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password">{{ __('Password') }}</label>
                        <input id="password"
                               type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="Enter your password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me" {{ !empty($rememberedLogin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        @if (Route::has('password.request'))
                            <a class="forgot-password" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>{{ __('Sign In') }}
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
