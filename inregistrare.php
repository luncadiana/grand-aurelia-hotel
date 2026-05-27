<?php

include 'includes/db.php';
include 'includes/functii.php';

//daca utilizatorul este deja logat, il redirectionam
if (esteAutentificat()) {
    header("Location: index.php");
    exit();
}

$mesaj_eroare = "";
$mesaj_succes = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //preluam datele din formular
    $nume = curataInput($_POST["nume"]);
    $email = curataInput($_POST["email"]);
    $parola = $_POST["parola"];
    $parola2 = $_POST["parola2"];
    
    //validari
    if (empty($nume) || empty($email) || empty($parola)) {
        $mesaj_eroare = "Va rugam sa completati toate campurile!";
    } elseif (strlen($parola) < 6) {
        $mesaj_eroare = "Parola trebuie sa aiba cel putin 6 caractere!";
    } elseif ($parola !== $parola2) {
        $mesaj_eroare = "Parolele nu se potrivesc!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj_eroare = "Adresa de email nu este valida!";
    } else {
        
        //verificam daca email-ul exista deja in baza de date
        $sql_verificare = "SELECT id FROM utilizatori WHERE email = ?";
        $stmt_verif = mysqli_prepare($conexiune, $sql_verificare);
        mysqli_stmt_bind_param($stmt_verif, "s", $email);
        mysqli_stmt_execute($stmt_verif);
        $rezultat_verif = mysqli_stmt_get_result($stmt_verif);
        
        if (mysqli_num_rows($rezultat_verif) > 0) {
            $mesaj_eroare = "Acest email este deja inregistrat!";
        } else {
            
            //hash-uim parola pentru securitate (folosim password_hash)
            $parola_hash = password_hash($parola, PASSWORD_DEFAULT);
            
            //inseram noul utilizator in baza de date
            $sql_insert = "INSERT INTO utilizatori (nume, email, parola, rol) VALUES (?, ?, ?, 'client')";
            $stmt_insert = mysqli_prepare($conexiune, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "sss", $nume, $email, $parola_hash);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $mesaj_succes = "Contul a fost creat cu succes! Te poti autentifica acum.";
            } else {
                $mesaj_eroare = "A aparut o eroare la inregistrare. Incearca din nou!";
            }
            
            mysqli_stmt_close($stmt_insert);
        }
        
        mysqli_stmt_close($stmt_verif);
    }
}

$titlu_pagina = "Inregistrare - Grand Aurelia";
include 'includes/header.php';
?>

<div class="container">
    <div class="container-formular">
        <h2><i class="bi bi-person-plus"></i> Inregistrare</h2>
        
        <?php if (!empty($mesaj_eroare)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo $mesaj_eroare; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($mesaj_succes)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <?php echo $mesaj_succes; ?>
                <br><a href="login.php" class="btn btn-sm btn-success mt-2">Mergi la autentificare</a>
            </div>
        <?php else: ?>
        
        <form method="POST" action="inregistrare.php" onsubmit="return validareFormularInregistrare()">
            <div class="mb-3">
                <label for="nume" class="form-label">Nume complet</label>
                <input type="text" class="form-control" id="nume" name="nume" 
                       placeholder="Ex: Ion Popescu" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="exemplu@email.com" required>
            </div>
            <div class="mb-3">
                <label for="parola" class="form-label">Parola</label>
                <input type="password" class="form-control" id="parola" name="parola" 
                       placeholder="Minim 6 caractere" required>
            </div>
            <div class="mb-3">
                <label for="parola2" class="form-label">Confirma parola</label>
                <input type="password" class="form-control" id="parola2" name="parola2" 
                       placeholder="Reintroduceti parola" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-check"></i> Creeaza cont
            </button>
        </form>
        
        <hr class="my-4">
        
        <p class="text-center">
            Ai deja cont? <a href="login.php">Autentifica-te aici</a>
        </p>
        
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
