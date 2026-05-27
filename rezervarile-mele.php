<?php

include 'includes/db.php';
include 'includes/functii.php';

//doar clientii pot accesa aceasta pagina
necesitaAutentificare(['client']);

$id_utilizator = $_SESSION['utilizator_id'];
$mesaj_succes = "";
$mesaj_eroare = "";

//procesam anularea rezervarii (daca a fost trimis acel formular)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['anuleaza_id'])) {
    $id_rezervare = (int)$_POST['anuleaza_id'];
    
    //verificam ca rezervarea este a clientului logat
    //(un client nu poate anula rezervarile altcuiva)
    $sql_verif = "SELECT id, data_check_in, status FROM rezervari 
                  WHERE id = ? AND id_utilizator = ?";
    $stmt_verif = mysqli_prepare($conexiune, $sql_verif);
    mysqli_stmt_bind_param($stmt_verif, "ii", $id_rezervare, $id_utilizator);
    mysqli_stmt_execute($stmt_verif);
    $rezultat_verif = mysqli_stmt_get_result($stmt_verif);
    
    if (mysqli_num_rows($rezultat_verif) == 1) {
        $rezervare = mysqli_fetch_assoc($rezultat_verif);
        
        //verificam ca rezervarea este valida pentru anulare
        //(nu este deja anulata si data check-in este in viitor)
        if ($rezervare['status'] == 'anulata') {
            $mesaj_eroare = "Aceasta rezervare este deja anulata!";
        } elseif (strtotime($rezervare['data_check_in']) < strtotime(date('Y-m-d'))) {
            $mesaj_eroare = "Nu poti anula o rezervare din trecut!";
        } else {
            //anulam rezervarea (UPDATE in baza de date)
            $sql_anulare = "UPDATE rezervari SET status = 'anulata' WHERE id = ?";
            $stmt_anulare = mysqli_prepare($conexiune, $sql_anulare);
            mysqli_stmt_bind_param($stmt_anulare, "i", $id_rezervare);
            
            if (mysqli_stmt_execute($stmt_anulare)) {
                $mesaj_succes = "Rezervarea a fost anulata cu succes!";
            }
            mysqli_stmt_close($stmt_anulare);
        }
    }
    mysqli_stmt_close($stmt_verif);
}

//luam toate rezervarile clientului
//folosim JOIN ca sa avem si detalii despre camera (numele si pretul)
$sql_rezervari = "SELECT r.*, c.nume AS nume_camera, c.pret AS pret_camera, c.imagine 
                  FROM rezervari r 
                  JOIN camere c ON r.id_camera = c.id 
                  WHERE r.id_utilizator = ? 
                  ORDER BY r.data_creare DESC";
$stmt = mysqli_prepare($conexiune, $sql_rezervari);
mysqli_stmt_bind_param($stmt, "i", $id_utilizator);
mysqli_stmt_execute($stmt);
$rezultate = mysqli_stmt_get_result($stmt);

$titlu_pagina = "Rezervarile mele";
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1><i class="bi bi-bookmark-heart"></i> Rezervarile mele</h1>
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
    
    <?php if (mysqli_num_rows($rezultate) == 0): ?>
        <div class="alert alert-info text-center p-5">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <h4>Nu ai nicio rezervare inca!</h4>
            <p>Exploreaza camerele noastre si fa-ti prima rezervare.</p>
            <a href="index.php#camere" class="btn btn-primary">Vezi camerele</a>
        </div>
    <?php else: ?>
        
        <div class="row">
            <?php while ($r = mysqli_fetch_assoc($rezultate)): ?>
                <?php
                    //calculam pretul total
                    $nopti = calculeazaNopti($r['data_check_in'], $r['data_check_out']);
                    $total = $nopti * $r['pret_camera'];
                    
                    //verificam daca rezervarea poate fi anulata
                    $poate_anula = ($r['status'] != 'anulata' 
                                   && $r['status'] != 'check_out'
                                   && strtotime($r['data_check_in']) >= strtotime(date('Y-m-d')));
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-4">
                                <img src="<?php echo htmlspecialchars($r['imagine']); ?>" 
                                     class="img-fluid h-100" 
                                     style="object-fit: cover;"
                                     alt="Camera"
                                     onerror="this.src='https://via.placeholder.com/200x300/c9a961/ffffff?text=Camera'">
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <h5><?php echo htmlspecialchars($r['nume_camera']); ?></h5>
                                    <span class="badge-status badge-<?php echo $r['status']; ?>">
                                        <?php echo traduceStatus($r['status']); ?>
                                    </span>
                                    <p class="mb-1 mt-2">
                                        <i class="bi bi-calendar-event"></i>
                                        <?php echo formateazaData($r['data_check_in']); ?>
                                        →
                                        <?php echo formateazaData($r['data_check_out']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-moon"></i>
                                        <?php echo $nopti; ?> nopti
                                    </p>
                                    <p class="mb-2">
                                        <strong>Total: <?php echo $total; ?> lei</strong>
                                    </p>
                                    
                                    <?php if ($poate_anula): ?>
                                        <form method="POST" action="rezervarile-mele.php" 
                                              onsubmit="return confirmaActiune('Esti sigur ca vrei sa anulezi aceasta rezervare?');">
                                            <input type="hidden" name="anuleaza_id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> Anuleaza
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
