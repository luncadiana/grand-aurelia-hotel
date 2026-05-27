<?php

include 'includes/db.php';
include 'includes/functii.php';

//doar lucratori si admini pot accesa pagina
necesitaAutentificare(['lucrator', 'admin']);

$mesaj_succes = "";

//procesare actiuni POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //schimbare status rezervare
    if (isset($_POST['schimba_status'])) {
        $id_rezervare = (int)$_POST['id_rezervare'];
        $status_nou = $_POST['status_nou'];
        
        //verificam ca statusul este valid (pentru securitate)
        $statusuri_valide = ['confirmata', 'check_in', 'check_out', 'anulata'];
        if (in_array($status_nou, $statusuri_valide)) {
            $sql = "UPDATE rezervari SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexiune, $sql);
            mysqli_stmt_bind_param($stmt, "si", $status_nou, $id_rezervare);
            if (mysqli_stmt_execute($stmt)) {
                $mesaj_succes = "Statusul rezervarii a fost actualizat!";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    //schimbare status camera (UC 3.7 - curatenie)
    if (isset($_POST['schimba_status_camera'])) {
        $id_camera = (int)$_POST['id_camera'];
        $status_nou = $_POST['status_camera_nou'];
        
        if (in_array($status_nou, ['disponibila', 'curatenie'])) {
            $sql = "UPDATE camere SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexiune, $sql);
            mysqli_stmt_bind_param($stmt, "si", $status_nou, $id_camera);
            if (mysqli_stmt_execute($stmt)) {
                $mesaj_succes = "Statusul camerei a fost actualizat!";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

//preluam toate rezervarile cu detalii despre client si camera
$filtru_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql_rezervari = "SELECT r.*, u.nume AS nume_client, u.email AS email_client, 
                         c.nume AS nume_camera
                  FROM rezervari r
                  JOIN utilizatori u ON r.id_utilizator = u.id
                  JOIN camere c ON r.id_camera = c.id";

if (!empty($filtru_status)) {
    $sql_rezervari .= " WHERE r.status = '" . mysqli_real_escape_string($conexiune, $filtru_status) . "'";
}

$sql_rezervari .= " ORDER BY r.data_creare DESC";

$rezultate_rezervari = mysqli_query($conexiune, $sql_rezervari);

//preluam toate camerele pentru gestiune curatenie
$rezultate_camere = mysqli_query($conexiune, "SELECT * FROM camere ORDER BY id");

$titlu_pagina = "Dashboard Receptie";
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1><i class="bi bi-clipboard-check"></i> Dashboard Receptie</h1>
        <p class="mb-0">Bine ai venit, <?php echo htmlspecialchars($_SESSION['utilizator_nume']); ?>!</p>
    </div>
</div>

<div class="container">
    
    <?php if (!empty($mesaj_succes)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> <?php echo $mesaj_succes; ?>
        </div>
    <?php endif; ?>
    
    <!-- tab-uri pentru navigare intre sectiuni -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-rezervari">
                <i class="bi bi-list-ul"></i> Rezervari
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-camere">
                <i class="bi bi-house-door"></i> Status Camere
            </a>
        </li>
    </ul>
    
    <div class="tab-content">
        
        <!-- tab 1: rezervari -->
        <div class="tab-pane fade show active" id="tab-rezervari">
            
            <!-- filtre dupa status -->
            <div class="mb-3">
                <a href="lucrator.php" class="btn btn-sm <?php echo empty($filtru_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">Toate</a>
                <a href="lucrator.php?status=in_asteptare" class="btn btn-sm <?php echo $filtru_status == 'in_asteptare' ? 'btn-primary' : 'btn-outline-primary'; ?>">In asteptare</a>
                <a href="lucrator.php?status=confirmata" class="btn btn-sm <?php echo $filtru_status == 'confirmata' ? 'btn-primary' : 'btn-outline-primary'; ?>">Confirmate</a>
                <a href="lucrator.php?status=check_in" class="btn btn-sm <?php echo $filtru_status == 'check_in' ? 'btn-primary' : 'btn-outline-primary'; ?>">Check-in</a>
                <a href="lucrator.php?status=anulata" class="btn btn-sm <?php echo $filtru_status == 'anulata' ? 'btn-primary' : 'btn-outline-primary'; ?>">Anulate</a>
            </div>
            
            <div class="table-responsive">
                <table class="table tabel-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Camera</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Actiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($rez = mysqli_fetch_assoc($rezultate_rezervari)): ?>
                            <tr>
                                <td>#<?php echo $rez['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($rez['nume_client']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($rez['email_client']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($rez['nume_camera']); ?></td>
                                <td><?php echo formateazaData($rez['data_check_in']); ?></td>
                                <td><?php echo formateazaData($rez['data_check_out']); ?></td>
                                <td>
                                    <span class="badge-status badge-<?php echo $rez['status']; ?>">
                                        <?php echo traduceStatus($rez['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($rez['status'] != 'anulata' && $rez['status'] != 'check_out'): ?>
                                        <form method="POST" action="lucrator.php" class="d-inline">
                                            <input type="hidden" name="id_rezervare" value="<?php echo $rez['id']; ?>">
                                            <input type="hidden" name="schimba_status" value="1">
                                            <select name="status_nou" class="form-select form-select-sm d-inline-block" 
                                                    style="width: auto;"
                                                    onchange="if(confirm('Schimbi statusul?')) this.form.submit()">
                                                <option value="">Schimba...</option>
                                                <?php if ($rez['status'] == 'in_asteptare'): ?>
                                                    <option value="confirmata">Confirma</option>
                                                <?php endif; ?>
                                                <?php if ($rez['status'] == 'confirmata'): ?>
                                                    <option value="check_in">Check-in</option>
                                                <?php endif; ?>
                                                <?php if ($rez['status'] == 'check_in'): ?>
                                                    <option value="check_out">Check-out</option>
                                                <?php endif; ?>
                                                <option value="anulata">Anuleaza</option>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($rezultate_rezervari) == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <em>Nu exista rezervari de afisat.</em>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- tab 2: status camere -->
        <div class="tab-pane fade" id="tab-camere">
            <p class="text-muted">Marcheaza camerele pentru curatenie. Camerele marcate astfel nu mai apar in lista publica.</p>
            
            <div class="row">
                <?php while ($cam = mysqli_fetch_assoc($rezultate_camere)): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card p-3">
                            <h5><?php echo htmlspecialchars($cam['nume']); ?></h5>
                            <p>
                                Status curent: 
                                <span class="badge-status badge-<?php echo $cam['status']; ?>">
                                    <?php echo traduceStatus($cam['status']); ?>
                                </span>
                            </p>
                            
                            <form method="POST" action="lucrator.php#tab-camere">
                                <input type="hidden" name="id_camera" value="<?php echo $cam['id']; ?>">
                                <input type="hidden" name="schimba_status_camera" value="1">
                                
                                <?php if ($cam['status'] == 'disponibila'): ?>
                                    <input type="hidden" name="status_camera_nou" value="curatenie">
                                    <button type="submit" class="btn btn-sm btn-warning w-100">
                                        <i class="bi bi-droplet"></i> Marcheaza pentru curatenie
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="status_camera_nou" value="disponibila">
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Marcheaza ca disponibila
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
