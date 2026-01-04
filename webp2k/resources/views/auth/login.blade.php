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

<div id="loginLoading" class="login-loading">
    <div class="loading-card">
        <img src="/assets/logo.png" class="loading-logo" alt="P2K">
        <p>Memuat Dashboard</p>
        <div class="spinner"></div>
    </div>
</div>

<div class="page-load" id="login-page">
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

       <form onsubmit="previewLogin(event)">
        <div class="form-group">
            <label>Username</label>
            <input type="text" placeholder="Masukkan username">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" placeholder="Masukkan password">
        </div>

        <button type="submit" class="btn-login">
            Login
        </button>
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
            login.classList.add('active'); // ✅ bukan display block
        }, 600);
    }, 1500);
});
</script>

<script>
function previewLogin(e) {
    e.preventDefault(); // stop submit form

    // tampilkan animasi loading
    document.getElementById('loginLoading').classList.add('active');

    // simulasi loading
    setTimeout(() => {
        window.location.href = '/dashboard';
    }, 1200);
}
</script>


<script>
    function showLoading() {
        document.getElementById('loginLoading').classList.add('active');
    }
</script>



</body>
</html>
