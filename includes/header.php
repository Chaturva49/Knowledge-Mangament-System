<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelliverse</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/knowledge-portal/css/style.css" rel="stylesheet">
    <style>
        .navbar-nav .nav-item {
            margin-right: 1.5rem;
        }
        .navbar-nav .nav-item:last-child {
            margin-right: 0;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: 1px;
        }
        .navbar-nav .nav-link {
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            border-radius: 0.5rem;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            background: rgba(255,255,255,0.18);
            color: #ffe082 !important;
            box-shadow: 0 2px 8px rgba(80, 120, 255, 0.10);
            text-decoration: none;
        }
        @media (max-width: 991px) {
            .navbar-nav .nav-item {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/knowledge-portal/">
                <i class="fas fa-graduation-cap me-2"></i>Intelliverse
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/knowledge-portal/">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/knowledge-portal/search.php">
                            <i class="fas fa-search me-1"></i>Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/knowledge-portal/forums.php">
                            <i class="fas fa-comments me-1"></i>Forums
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/knowledge-portal/faqs.php">
                            <i class="fas fa-question-circle me-1"></i>FAQs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/knowledge-portal/articles.php">
                            <i class="fas fa-newspaper me-1"></i>Articles
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasPermission('user')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/knowledge-portal/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/knowledge-portal/upload.php">
                                    <i class="fas fa-upload me-1"></i>Upload
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/knowledge-portal/dashboard.php">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars(getCurrentUsername()); ?>
                                <span class="badge bg-light text-dark ms-1">
                                    <?php echo ucfirst(getCurrentUserRole()); ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/knowledge-portal/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/knowledge-portal/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/knowledge-portal/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container my-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 