<?php

function esteAutentificat() {
    return isset($_SESSION['utilizator_id']); //returneaza true daca userul este logat, false altfel
}

function areRol($rol) { //verifica daca userul logat are rolul specificat
    if (!esteAutentificat()) {
        return false;
    }
    return $_SESSION['utilizator_rol'] === $rol;
}


//redirectioneaza la pagina de login daca utilizatorul nu este logat
//sau daca nu are un rol permis
function necesitaAutentificare($roluri_permise = []) {
    if (!esteAutentificat()) {
        header("Location: login.php");
        exit();
    }
    if (!empty($roluri_permise) && !in_array($_SESSION['utilizator_rol'], $roluri_permise)) {
        header("Location: index.php");
        exit();
    }
}

//elimina spatiile si caracterele speciale dintr-un text introdus de utilizator
//previne atacuri de tip XSS (Cross-Site Scripting)
function curataInput($text) {
    $text = trim($text);
    $text = stripslashes($text);
    $text = htmlspecialchars($text);
    return $text;
}

//transforma data din format YYYY-MM-DD in format DD.MM.YYYY 
function formateazaData($data) {
    return date("d.m.Y", strtotime($data));
}

//calculeaza numarul de nopti intre doua date
function calculeazaNopti($check_in, $check_out) {
    $data1 = new DateTime($check_in);
    $data2 = new DateTime($check_out);
    $diferenta = $data1->diff($data2);
    return $diferenta->days;
}

//transforma statusul intern (in_asteptare) in text afisabil (In asteptare)
function traduceStatus($status) {
    $traduceri = [
        'in_asteptare' => 'In asteptare',
        'confirmata' => 'Confirmata',
        'check_in' => 'Check-in efectuat',
        'check_out' => 'Check-out efectuat',
        'anulata' => 'Anulata',
        'disponibila' => 'Disponibila',
        'curatenie' => 'In curatenie'
    ];
    return isset($traduceri[$status]) ? $traduceri[$status] : $status;
}
?>
