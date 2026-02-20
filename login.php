<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        redirect('index.php');
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IMS Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: radial-gradient(circle at top right, #0ea5e9, transparent),
                        radial-gradient(circle at bottom left, #10b981, transparent),
                        #0f172a;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-sky);
            color: white;
            box-shadow: none;
        }
        .btn-login {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            border: none;
            padding: 0.8rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(14, 165, 233, 0.5);
        }
    </style>
</head>
<body>

<div class="login-card animate-fade-in">
    <div class="text-center mb-5">
        <div class="bg-primary-subtle d-inline-flex p-3 rounded-4 mb-3" style="background: rgba(14, 165, 233, 0.1) !important;">
            <i class="bi bi-box-seam-fill text-accent-sky fs-2"></i>
        </div>
        <h2 class="text-white fw-bold">IMS <span class="fw-light">Pro</span></h2>
        <p class="text-muted small">Secure Inventory Management System</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 small mb-4" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border-radius: 12px;">
            <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="form-label text-white small opacity-75 fw-medium">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@system.com" required>
        </div>
        <div class="mb-5">
            <label class="form-label text-white small opacity-75 fw-medium">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-login">Sign In</button>
    </form>

    <div class="mt-5 text-center">
        <p class="text-muted small mb-0">© <?php echo date('Y'); ?> Yasir Ismail. All rights reserved.</p>
    </div>
</div>

</body>
</html>
