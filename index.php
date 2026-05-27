<?php

//includem fisierele necesare pentru baza de date si functii
include 'includes/db.php';
include 'includes/functii.php';

//variabile pentru filtre - daca nu sunt setate, folosim valori implicite
$filtru_pret_max = isset($_GET['pret_max']) ? (int)$_GET['pret_max'] : 0;
$filtru_capacitate = isset($_GET['capacitate']) ? (int)$_GET['capacitate'] : 0;

//construim interogarea SQL in functie de filtrele aplicate
//aratam doar camerele care nu sunt in curatenie (status = 'disponibila')
$sql = "SELECT * FROM camere WHERE status = 'disponibila'";

if ($filtru_pret_max > 0) {
    $sql .= " AND pret <= " . $filtru_pret_max;
}
if ($filtru_capacitate > 0) {
    $sql .= " AND capacitate >= " . $filtru_capacitate;
}

$sql .= " ORDER BY pret ASC";

//executam interogarea
$rezultat_camere = mysqli_query($conexiune, $sql);

//titlul paginii
$titlu_pagina = "Grand Aurelia Hotel - Camere si Suite";

//includem header-ul (navbar)
include 'includes/header.php';
?>

<!-- banner principal -->
<section class="hero-section">
    <div class="container">
        <h1>Grand Aurelia Hotel</h1>
        <p>Eleganta, confort si experiente de neuitat</p>
        <a href="#camere" class="btn btn-aurelia btn-lg">Vezi camerele</a>
    </div>
</section>

<!-- "despre noi" -->
<section class="sectiune bg-light">
    <div class="container">
        <div class="titlu-sectiune">
            <h2>Despre Noi</h2>
            <div class="linie"></div>
        </div>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <i class="bi bi-stars" style="font-size: 3rem; color: #c9a961;"></i>
                <h4 class="mt-3">Servicii Premium</h4>
                <p>Personal dedicat si servicii la cele mai inalte standarde, disponibile 24/7.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="bi bi-cup-hot" style="font-size: 3rem; color: #c9a961;"></i>
                <h4 class="mt-3">Mic Dejun Gourmet</h4>
                <p>Bufet bogat cu preparate proaspete si o varietate de optiuni internationale.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="bi bi-wifi" style="font-size: 3rem; color: #c9a961;"></i>
                <h4 class="mt-3">Wi-Fi Gratuit</h4>
                <p>Conexiune rapida la internet in toate camerele si spatiile comune.</p>
            </div>
        </div>
    </div>
</section>

<!-- camere (cu filtrare) -->
<section class="sectiune" id="camere">
    <div class="container">
        <div class="titlu-sectiune">
            <h2>Camerele Noastre</h2>
            <div class="linie"></div>
            <p class="mt-3">Alege camera potrivita pentru sederea ta</p>
        </div>
        
        <!-- formular de filtrare -->
        <div class="card mb-4 p-3" style="background-color: #f8f9fa;">
            <form method="GET" action="index.php#camere">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="pret_max" class="form-label">Pret maxim (lei/noapte)</label>
                        <input type="number" class="form-control" id="pret_max" name="pret_max" 
                               placeholder="Ex: 500" value="<?php echo $filtru_pret_max > 0 ? $filtru_pret_max : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="capacitate" class="form-label">Numar persoane (minim)</label>
                        <select class="form-select" id="capacitate" name="capacitate">
                            <option value="0">Oricate</option>
                            <option value="2" <?php if ($filtru_capacitate == 2) echo 'selected'; ?>>2 persoane</option>
                            <option value="3" <?php if ($filtru_capacitate == 3) echo 'selected'; ?>>3 persoane</option>
                            <option value="4" <?php if ($filtru_capacitate == 4) echo 'selected'; ?>>4 persoane</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Aplica filtre
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- lista cu camere -->
        <div class="row">
            <?php
            //verificam daca avem rezultate
            if (mysqli_num_rows($rezultat_camere) > 0) {
                //parcurgem fiecare camera din baza de date
                while ($camera = mysqli_fetch_assoc($rezultat_camere)) {
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-camera">
                            <img src="<?php echo htmlspecialchars($camera['imagine']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($camera['nume']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x240/c9a961/ffffff?text=Grand+Aurelia'">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($camera['nume']); ?></h5>
                                <p class="capacitate">
                                    <i class="bi bi-people"></i> Pana la <?php echo $camera['capacitate']; ?> persoane
                                </p>
                                <p class="card-text">
                                    <?php 
                                    //afisam doar primele 100 caractere din descriere
                                    $descriere = htmlspecialchars($camera['descriere']);
                                    echo strlen($descriere) > 100 ? substr($descriere, 0, 100) . '...' : $descriere;
                                    ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="pret">
                                        <?php echo $camera['pret']; ?> lei<span> / noapte</span>
                                    </span>
                                    <a href="rezerva.php?id=<?php echo $camera['id']; ?>" class="btn btn-aurelia">
                                        Rezerva
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                //daca nu avem niciun rezultat
                echo '<div class="col-12">';
                echo '<div class="alert alert-info text-center">';
                echo '<i class="bi bi-info-circle"></i> Nu am gasit camere disponibile cu filtrele selectate.';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<?php 
//includem footer-ul
include 'includes/footer.php'; 
?>
