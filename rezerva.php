<?php

include 'includes/db.php';
include 'includes/functii.php';

//doar clientii pot rezerva (sau ii redirectionam la login)
if (!esteAutentificat()) {
    header("Location: login.php");
    exit();
}

//lucratorii si adminii nu rezerva, ii redirectionam
if (!areRol('client')) {
    header("Location: index.php");
    exit();
}

//verificam ca avem ID-ul camerei in URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_camera = (int)$_GET['id'];

//preluam detaliile camerei din baza de date
$sql_camera = "SELECT * FROM camere WHERE id = ? AND status = 'disponibila'";
$stmt = mysqli_prepare($conexiune, $sql_camera);
mysqli_stmt_bind_param($stmt, "i", $id_camera);
mysqli_stmt_execute($stmt);
$rezultat = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($rezultat) == 0) {
    //camera nu exista sau nu e disponibila
    header("Location: index.php");
    exit();
}

$camera = mysqli_fetch_assoc($rezultat);
mysqli_stmt_close($stmt);

//variabile pentru mesaje
$mesaj_eroare = "";
$mesaj_succes = "";

// procesam formularul de rezervare
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $data_check_in = $_POST['data_check_in'];
    $data_check_out = $_POST['data_check_out'];
    
    //validari de baza
    if (empty($data_check_in) || empty($data_check_out)) {
        $mesaj_eroare = "Va rugam sa selectati ambele date!";
    } elseif (strtotime($data_check_in) < strtotime(date('Y-m-d'))) {
        $mesaj_eroare = "Data de check-in nu poate fi in trecut!";
    } elseif (strtotime($data_check_out) <= strtotime($data_check_in)) {
        $mesaj_eroare = "Data de check-out trebuie sa fie dupa data de check-in!";
    } else {
        
        //verificam daca camera este libera in perioada selectata
        //(nu exista alta rezervare activa care sa se suprapuna)
        $sql_verif = "SELECT id FROM rezervari 
                      WHERE id_camera = ? 
                      AND status NOT IN ('anulata', 'check_out')
                      AND (
                          (data_check_in <= ? AND data_check_out > ?)
                          OR (data_check_in < ? AND data_check_out >= ?)
                          OR (data_check_in >= ? AND data_check_out <= ?)
                      )";
        $stmt_verif = mysqli_prepare($conexiune, $sql_verif);
        mysqli_stmt_bind_param($stmt_verif, "issssss", 
            $id_camera, 
            $data_check_in, $data_check_in,
            $data_check_out, $data_check_out,
            $data_check_in, $data_check_out
        );
        mysqli_stmt_execute($stmt_verif);
        $rezultat_verif = mysqli_stmt_get_result($stmt_verif);
        
        if (mysqli_num_rows($rezultat_verif) > 0) {
            $mesaj_eroare = "Camera nu este disponibila in perioada selectata. Va rugam alegeti alte date!";
        } else {
            
            //salvam rezervarea in baza de date
            $id_utilizator = $_SESSION['utilizator_id'];
            $sql_insert = "INSERT INTO rezervari (id_utilizator, id_camera, data_check_in, data_check_out, status) 
                           VALUES (?, ?, ?, ?, 'in_asteptare')";
            $stmt_insert = mysqli_prepare($conexiune, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iiss", $id_utilizator, $id_camera, $data_check_in, $data_check_out);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $mesaj_succes = "Rezervarea ta a fost inregistrata cu succes! Status: In asteptare confirmare.";
            } else {
                $mesaj_eroare = "A aparut o eroare la salvarea rezervarii.";
            }
            
            mysqli_stmt_close($stmt_insert);
        }
        
        mysqli_stmt_close($stmt_verif);
    }
}

$titlu_pagina = "Rezerva " . $camera['nume'];
include 'includes/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <h1 class="text-center mb-4">Rezervare Camera</h1>
    
    <?php if (!empty($mesaj_succes)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <?php echo $mesaj_succes; ?>
            <br>
            <a href="rezervarile-mele.php" class="btn btn-sm btn-success mt-2">Vezi rezervarile mele</a>
            <a href="index.php" class="btn btn-sm btn-secondary mt-2">Inapoi la camere</a>
        </div>
    <?php else: ?>
    
    <div class="row">
        <!-- detalii camera (coloana stanga) -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <img src="<?php echo htmlspecialchars($camera['imagine']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($camera['nume']); ?>"
                     style="height: 300px; object-fit: cover;"
                     onerror="this.src='https://via.placeholder.com/600x300/c9a961/ffffff?text=Grand+Aurelia'">
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($camera['nume']); ?></h3>
                    <p>
                        <i class="bi bi-people"></i> Capacitate: <?php echo $camera['capacitate']; ?> persoane
                    </p>
                    <p><?php echo htmlspecialchars($camera['descriere']); ?></p>
                    <h4 class="text-warning">
                        <?php echo $camera['pret']; ?> lei / noapte
                    </h4>
                </div>
            </div>
        </div>
        
        <!-- formular rezervare (coloana dreapta) -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h4>Detalii rezervare</h4>
                
                <?php if (!empty($mesaj_eroare)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php echo $mesaj_eroare; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="rezerva.php?id=<?php echo $id_camera; ?>" 
                      onsubmit="return validareRezervare()">
                    
                    <!-- camp ascuns cu pretul -->
                    <input type="hidden" id="pret_camera" value="<?php echo $camera['pret']; ?>">
                    
                    <div class="mb-3">
                        <label for="data_check_in" class="form-label">Data check-in</label>
                        <input type="date" class="form-control" id="data_check_in" name="data_check_in"
                               onchange="calculeazaPret()" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="data_check_out" class="form-label">Data check-out</label>
                        <input type="date" class="form-control" id="data_check_out" name="data_check_out"
                               onchange="calculeazaPret()" required>
                    </div>
                    
                    <!-- zona unde se afiseaza pretul total (calculat de JS) -->
                    <div class="alert alert-info" id="total_pret">
                        <em>Selectati datele pentru a vedea pretul</em>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-bookmark-check"></i> Confirma rezervarea
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
