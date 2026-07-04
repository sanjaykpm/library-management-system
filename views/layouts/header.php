<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo URLROOT; ?>"><?php echo SITENAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])) : ?>
                        <li class="nav-item me-3">
                            <a class="nav-link position-relative" href="<?php echo URLROOT; ?>/notification/index">
                                <i class="fas fa-bell"></i>
                                <?php 
                                    // Quick count check (optimized)
                                    if(isset($_SESSION['user_id'])) {
                                        // Ideally this should be passed from controller, but for header convenience:
                                        // Using a direct DB call here is an anti-pattern but creating a Widget/ViewComposer is overkill for this scope.
                                        // We will assume the Notification model helper is available or we just link without count for now if data isn't passed everywhere.
                                        // Better approach: pure link, user checks page.
                                    }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Welcome, <?php echo $_SESSION['user_name']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URLROOT; ?>/auth/logout">Logout</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URLROOT; ?>/auth/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URLROOT; ?>/auth/register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
