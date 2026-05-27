<?php

include 'includes/db.php';
include 'includes/functii.php';

//doar adminii pot accesa aceasta pagina
necesitaAutentificare(['admin']);

$mesaj_succes = "";
$mesaj_eroare = "";

//procesare actiuni POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //adaugare camera noua
    if (isset($_POST['adauga_camera'])) {
        $nume = curataInput($_POST['nume']);
        $descriere = curataInput($_POST['descriere']);
        $pret = (float)$_POST['pret'];
        $capacitate = (int)$_POST['capacitate'];
        $imagine = curataInput($_POST['imagine']);
        
        if (empty($imagine)) {
            $imagine = 'img/camera-default.jpg';
        }
        
        if (empty($nume) || empty($descriere) || $pret <= 0) {
            $mesaj_eroare = "Va rugam completati toate campurile corect!";
        } else {
            $sql = "INSERT INTO camere (nume, descriere, pret, capacitate, imagine) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexiune, $sql);
            mysqli_stmt_bind_param($stmt, "ssdis", $nume, $descriere, $pret, $capacitate, $imagine);
            
            if (mysqli_stmt_execute($stmt)) {
                $mesaj_succes = "Camera a fost adaugata cu succes!";
            } else {
                $mesaj_eroare = "Eroare la adaugarea camerei.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    //editare camera existenta
    if (isset($_POST['editeaza_camera'])) {
        $id = (int)$_POST['id_camera'];
        $nume = curataInput($_POST['nume']);
        $descriere = curataInput($_POST['descriere']);
        $pret = (float)$_POST['pret'];
        $capacitate = (int)$_POST['capacitate'];
        $imagine = curataInput($_POST['imagine']);
        
        $sql = "UPDATE camere SET nume = ?, descriere = ?, pret = ?, capacitate = ?, imagine = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conexiune, $sql);
        mysqli_stmt_bind_param($stmt, "ssdisi", $nume, $descriere, $pret, $capacitate, $imagine, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $mesaj_succes = "Camera a fost actualizata cu succes!";
        }
        mysqli_stmt_close($stmt);
    }
    
    //stergere camera
    if (isset($_POST['sterge_camera'])) {
        $id = (int)$_POST['id_camera'];
        $sql = "DELETE FROM camere WHERE id = ?";
        $stmt = mysqli_prepare($conexiune, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $mesaj_succes = "Camera a fost stearsa cu succes!";
        }
        mysqli_stmt_close($stmt);
    }
}

//preluam camera pentru editare 
$camera_de_editat = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $sql = "SELECT * FROM camere WHERE id = ?";
    $stmt = mysqli_prepare($conexiune, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $rezultat_edit = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($rezultat_edit) > 0) {
        $camera_de_editat = mysqli_fetch_assoc($rezultat_edit);
    }
    mysqli_stmt_close($stmt);
}

//preluam toate camerele
$camere_toate = mysqli_query($conexiune, "SELECT * FROM camere ORDER BY id");

//preluam toti utilizatorii
$utilizatori_toti = mysqli_query($conexiune, "SELECT id, nume, email, rol, data_inregistrare FROM utilizatori ORDER BY data_inregistrare DESC");

//statistici pentru dashboard
$total_camere = mysqli_num_rows(mysqli_query($conexiune, "SELECT id FROM camere"));
$total_utilizatori = mysqli_num_rows(mysqli_query($conexiune, "SELECT id FROM utilizatori"));
$total_rezervari = mysqli_num_rows(mysqli_query($conexiune, "SELECT id FROM rezervari"));
$rezervari_active = mysqli_num_rows(mysqli_query($conexiune, "SELECT id FROM rezervari WHERE status NOT IN ('anulata', 'check_out')"));

$titlu_pagina = "Administrare";
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1><i class="bi bi-gear"></i> Panou Administrator</h1>
        <p class="mb-0">Bine ai venit, <?php echo htmlspecialchars($_SESSION['utilizator_nume']); ?>!</p>
    </div>
</div>

<div class="container">
    
    <?php if (!empty($mesaj_succes)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> <?php echo $mesaj_succes; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($mesaj_eroare)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $mesaj_eroare; ?>
        </div>
    <?php endif; ?>
    
    <!-- carduri -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <i class="bi bi-house-door" style="font-size: 2rem; color: #c9a961;"></i>
                <h3><?php echo $total_camere; ?></h3>
                <p class="mb-0">Camere</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <i class="bi bi-people" style="font-size: 2rem; color: #c9a961;"></i>
                <h3><?php echo $total_utilizatori; ?></h3>
                <p class="mb-0">Utilizatori</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <i class="bi bi-bookmark" style="font-size: 2rem; color: #c9a961;"></i>
                <h3><?php echo $total_rezervari; ?></h3>
                <p class="mb-0">Rezervari total</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <i class="bi bi-check-circle" style="font-size: 2rem; color: #5cb85c;"></i>
                <h3><?php echo $rezervari_active; ?></h3>
                <p class="mb-0">Rezervari active</p>
            </div>
        </div>
    </div>
    
    <!-- tab uri -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-adauga">
                <i class="bi bi-plus-circle"></i> 
                <?php echo $camera_de_editat ? 'Editare camera' : 'Adauga camera'; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-camere">
                <i class="bi bi-list-ul"></i> Toate camerele
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-utilizatori">
                <i class="bi bi-people"></i> Utilizatori
            </a>
        </li>
    </ul>
    
    <div class="tab-content">
        
        <!-- formular adaugare/editare camera -->
        <div class="tab-pane fade show active" id="tab-adauga">
            <div class="card p-4">
                <h3>
                    <?php if ($camera_de_editat): ?>
                        Editare: <?php echo htmlspecialchars($camera_de_editat['nume']); ?>
                        <a href="admin.php" class="btn btn-sm btn-secondary float-end">Anuleaza editarea</a>
                    <?php else: ?>
                        Adauga camera noua
                    <?php endif; ?>
                </h3>
                
                <form method="POST" action="admin.php">
                    <?php if ($camera_de_editat): ?>
                        <input type="hidden" name="id_camera" value="<?php echo $camera_de_editat['id']; ?>">
                        <input type="hidden" name="editeaza_camera" value="1">
                    <?php else: ?>
                        <input type="hidden" name="adauga_camera" value="1">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nume" class="form-label">Nume camera *</label>
                            <input type="text" class="form-control" id="nume" name="nume" required
                                   value="<?php echo $camera_de_editat ? htmlspecialchars($camera_de_editat['nume']) : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pret" class="form-label">Pret (lei/noapte) *</label>
                            <input type="number" step="0.01" class="form-control" id="pret" name="pret" required
                                   value="<?php echo $camera_de_editat ? $camera_de_editat['pret'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="capacitate" class="form-label">Capacitate *</label>
                            <input type="number" class="form-control" id="capacitate" name="capacitate" required
                                   value="<?php echo $camera_de_editat ? $camera_de_editat['capacitate'] : '2'; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="imagine" class="form-label">URL imagine</label>
                        <input type="text" class="form-control" id="imagine" name="imagine"
                               placeholder="img/camera1.jpg sau link https://..."
                               value="<?php echo $camera_de_editat ? htmlspecialchars($camera_de_editat['imagine']) : ''; ?>">
                        <small class="text-muted">Lasa gol pentru imaginea implicita</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descriere" class="form-label">Descriere *</label>
                        <textarea class="form-control" id="descriere" name="descriere" rows="4" required><?php echo $camera_de_editat ? htmlspecialchars($camera_de_editat['descriere']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php if ($camera_de_editat): ?>
                            <i class="bi bi-save"></i> Salveaza modificarile
                        <?php else: ?>
                            <i class="bi bi-plus-circle"></i> Adauga camera
                        <?php endif; ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- toate camerele -->
        <div class="tab-pane fade" id="tab-camere">
            <div class="table-responsive">
                <table class="table tabel-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nume</th>
                            <th>Pret</th>
                            <th>Capacitate</th>
                            <th>Status</th>
                            <th>Actiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cam = mysqli_fetch_assoc($camere_toate)): ?>
                            <tr>
                                <td>#<?php echo $cam['id']; ?></td>
                                <td><?php echo htmlspecialchars($cam['nume']); ?></td>
                                <td><?php echo $cam['pret']; ?> lei</td>
                                <td><?php echo $cam['capacitate']; ?> pers</td>
                                <td>
                                    <span class="badge-status badge-<?php echo $cam['status']; ?>">
                                        <?php echo traduceStatus($cam['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="admin.php?edit=<?php echo $cam['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil"></i> Editeaza
                                    </a>
                                    <form method="POST" action="admin.php" class="d-inline"
                                          onsubmit="return confirm('Esti sigur ca vrei sa stergi aceasta camera? Toate rezervarile asociate vor fi sterse!');">
                                        <input type="hidden" name="id_camera" value="<?php echo $cam['id']; ?>">
                                        <input type="hidden" name="sterge_camera" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Sterge
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- utilizatori -->
        <div class="tab-pane fade" id="tab-utilizatori">
            <div class="table-responsive">
                <table class="table tabel-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nume</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Data inregistrare</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($utilizatori_toti)): ?>
                            <tr>
                                <td>#<?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['nume']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $u['rol'] == 'admin' ? 'danger' : ($u['rol'] == 'lucrator' ? 'warning' : 'primary'); 
                                    ?>">
                                        <?php echo ucfirst($u['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo formateazaData($u['data_inregistrare']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
