<?php

include 'includes/db.php';

//stergem toate variabilele de sesiune
$_SESSION = array();

//distrugem sesiunea
session_destroy();

//redirectionam la pagina principala
header("Location: index.php");
exit();
?>
