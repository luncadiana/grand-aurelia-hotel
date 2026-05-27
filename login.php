<?php

include 'includes/db.php';
include 'includes/functii.php';

//daca utilizatorul este deja logat, il redirectionam la pagina principala
if (esteAutentificat()) {
    header("Location: index.php");
    exit();
}

//variabila pentru mesajul de eroare
$mesaj_eroare = "";

//verificam daca formularul a fost trimis (metoda POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //preluam datele din formular si le curatam
    $email = curataInput($_POST["email"]);
    $parola = $_POST["parola"]; // parola nu o curatam ca sa nu modificam caracterele speciale
    
    //verificam ca ambele campuri sunt completate
    if (empty($email) || empty($parola)) {
        $mesaj_eroare = "Va rugam sa completati toate campurile!";
    } else {
        
        //cautam utilizatorul in baza de date dupa email
        //folosim prepared statements pentru a preveni SQL Injection
        $sql = "SELECT id, nume, email, parola, rol FROM utilizatori WHERE email = ?";
        $stmt = mysqli_prepare($conexiune, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $rezultat = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($rezultat) == 1) {
            //am gasit utilizatorul, verificam parola
            $utilizator = mysqli_fetch_assoc($rezultat);
            
            //verificam parola folosind password_verify (compara cu hash-ul din baza de date)
            if (password_verify($parola, $utilizator['parola'])) {
                
                //parola este corecta - cream sesiunea
                $_SESSION['utilizator_id'] = $utilizator['id'];
                $_SESSION['utilizator_nume'] = $utilizator['nume'];
                $_SESSION['utilizator_email'] = $utilizator['email'];
                $_SESSION['utilizator_rol'] = $utilizator['rol'];
                
                //redirectionam in functie de rol
                if ($utilizator['rol'] == 'admin') {
                    header("Location: admin.php");
                } elseif ($utilizator['rol'] == 'lucrator') {
                    header("Location: lucrator.php");
                } else {
                    header("Location: index.php");
                }
                exit();
                
            } else {
                $mesaj_eroare = "Parola este incorecta!";
            }
        } else {
            $mesaj_eroare = "Nu exista un cont cu acest email!";
        }
        
        mysqli_stmt_close($stmt);
    }
}

$titlu_pagina = "Autentificare - Grand Aurelia";
include 'includes/header.php';
?>

<div class="container">
    <div class="container-formular">
        <h2><i class="bi bi-person-circle"></i> Autentificare</h2>
        
        <!-- afisam mesajul de eroare daca exista -->
        <?php if (!empty($mesaj_eroare)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo $mesaj_eroare; ?>
            </div>
        <?php endif; ?>
        
        <!-- formularul de login -->
        <form method="POST" action="login.php" onsubmit="return validareFormularLogin()">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="exemplu@email.com" required>
            </div>
            <div class="mb-3">
                <label for="parola" class="form-label">Parola</label>
                <input type="password" class="form-control" id="parola" name="parola" 
                       placeholder="Introduceti parola" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Autentifica-te
            </button>
        </form>
        
        <hr class="my-4">
        
        <p class="text-center">
            Nu ai cont inca? <a href="inregistrare.php">Creeaza unul aici</a>
        </p>
        
        <!-- conturile de test -->
        <div class="alert alert-info mt-3" style="font-size: 0.85rem;">
            <strong>Conturi de test:</strong><br>
            Admin: admin@aurelia.ro / admin123<br>
            Lucrator: lucrator@aurelia.ro / lucrator123<br>
            Client: client@aurelia.ro / client123
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
