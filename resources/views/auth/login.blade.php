<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #7c3aed, #a78bfa);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background-color: #ffffff;
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.9s ease;
        }

        .login-box h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #5b21b6;
        }

        .form-label {
            font-weight: 500;
            color: #444;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 8px rgba(124, 58, 237, 0.3);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 12px;
            background: #7c3aed;
            color: #fff;
            border: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-login:hover {
            background: #5b21b6;
            transform: translateY(-2px);
        }

        .password-toggle {
    position: relative;
}

.password-toggle .form-control {
    padding-right: 2.75rem; /* Enough space for the icon */
}

.toggle-icon {
    position: absolute;
    top: 72%;
    right: 1rem;
    transform: translateY(-50%);
    font-size: 1.2rem;
    cursor: pointer;
    color: #6c757d;
}


        @keyframes fadeInUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Secure Login</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
        </div>

     <div class="mb-4 password-toggle">
    <label for="password" class="form-label">Password</label>
    <input type="password" name="password" id="password" class="form-control" required>
    <i class="bi bi-eye-slash toggle-icon" id="togglePassword"></i>
</div>


        <button class="btn btn-login" type="submit">Login</button>
    </form>
</div>

<!-- Password toggle script -->
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>
