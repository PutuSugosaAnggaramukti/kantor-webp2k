<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistem Informasi P2K</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

<div id="intro-loader">
    <div class="loader-content">
        <div class="spinner"></div>
        <h2>Selamat Datang</h2>
        <p>Sistem Informasi P2K</p>
    </div>
</div>

<div class="page-load" id="login-page" style="display: none;">
    <div class="login-container">

    <!-- LEFT -->
    <div class="login-left">
        <div class="brand">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo P2K">
            <h2>Sistem Informasi P2K</h2>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">
        <h3>Login</h3>

            {{-- Flash Success --}}
    @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Flash Error --}}
    @if (session('error'))
        <div class="alert error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Error --}}
    @if ($errors->any())
        <div class="alert error">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
@endif

       <form method="POST" action="{{ route('login.preview') }}">
             @csrf

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-login">
                Login
            </button>

            <a href="#" class="forgot">Lupa Password?</a>
        </form>
    </div>

    
</div>

<script>
window.addEventListener('load', () => {
    setTimeout(() => {
        const loader = document.getElementById('intro-loader');
        const login  = document.getElementById('login-page');

        loader.style.opacity = '0';

        setTimeout(() => {
            loader.style.display = 'none';
            login.style.display = 'block';
        }, 600);
    }, 1500);
});
</script>



</body>
</html>
