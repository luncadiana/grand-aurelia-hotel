<?php
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titlu_pagina) ? $titlu_pagina : 'Grand Aurelia Hotel'; ?></title>
    
    <!-- Bootstrap 5.1 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- Fonturi Google -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- CSS personalizat -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
    <!-- Bara de navigare (Navbar Bootstrap) -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="navbar-principal">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i>
                Grand Aurelia
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#meniuNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="meniuNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Acasa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#camere">Camere</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
                    
                    <?php if (esteAutentificat()): ?>
                        <!-- Meniu pentru utilizatori autentificati -->
                        
                        <?php if (areRol('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">
                                    <i class="bi bi-gear"></i> Administrare
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (areRol('lucrator')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="lucrator.php">
                                    <i class="bi bi-clipboard-check"></i> Receptie
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (areRol('client')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="rezervarile-mele.php">
                                    <i class="bi bi-bookmark-heart"></i> Rezervarile mele
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <span class="nav-link text-warning">
                                <i class="bi bi-person-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['utilizator_nume']); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Iesire</a>
                        </li>
                    <?php else: ?>
                        <!-- Meniu pentru vizitatori -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Autentificare</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-rezerva" href="inregistrare.php">Inregistrare</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
