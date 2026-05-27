<?php

// parametrii de conexiune la baza de date locala (XAMPP)
$server = "localhost";
$utilizator_db = "root";       // utilizator implicit XAMPP
$parola_db = "";               // parola implicita XAMPP (goala)
$nume_baza = "hotel";

// cream conexiunea cu mysqli 
$conexiune = mysqli_connect($server, $utilizator_db, $parola_db, $nume_baza);

// verificam daca s-a realizat conexiunea
if (!$conexiune) {
    die("Eroare la conectarea cu baza de date: " . mysqli_connect_error());
}

// setam codarea caracterelor pentru a afisa corect diacriticele
mysqli_set_charset($conexiune, "utf8mb4");

// pornim sesiunea PHP pentru a stoca informatii despre utilizatorul logat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
