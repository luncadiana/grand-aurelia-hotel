//verifica daca email-ul si parola sunt completate corect
//inainte de trimiterea formularului de login
function validareFormularLogin() {
    var email = document.getElementById("email").value;
    var parola = document.getElementById("parola").value;
    
    //verificam daca email ul este completat si are formatul corect
    if (email == "") {
        alert("Va rugam sa introduceti email-ul!");
        return false;
    }
    
    //verificam daca email-ul contine @ si .
    if (email.indexOf("@") == -1 || email.indexOf(".") == -1) {
        alert("Email-ul introdus nu este valid!");
        return false;
    }
    
    //verificam daca parola este completata
    if (parola == "") {
        alert("Va rugam sa introduceti parola!");
        return false;
    }
    
    //verificam daca parola are cel putin 6 caractere
    if (parola.length < 6) {
        alert("Parola trebuie sa aiba cel putin 6 caractere!");
        return false;
    }
    
    return true; //toate verificarile au trecut, se poate trimite formularul
}

//verifica daca toate campurile sunt completate corect
//inainte de inregistrarea unui cont nou
function validareFormularInregistrare() {
    var nume = document.getElementById("nume").value;
    var email = document.getElementById("email").value;
    var parola = document.getElementById("parola").value;
    var parola2 = document.getElementById("parola2").value;
    
    //verificam numele
    if (nume == "" || nume.length < 3) {
        alert("Numele trebuie sa aiba cel putin 3 caractere!");
        return false;
    }
    
    //verificam email ul
    if (email == "" || email.indexOf("@") == -1) {
        alert("Email-ul introdus nu este valid!");
        return false;
    }
    
    //verificam parola
    if (parola.length < 6) {
        alert("Parola trebuie sa aiba cel putin 6 caractere!");
        return false;
    }
    
    //verificam daca cele doua parole se potrivesc
    if (parola != parola2) {
        alert("Parolele introduse nu se potrivesc!");
        return false;
    }
    
    return true;
}

//verifica datele de check-in si check-out la rezervare
function validareRezervare() {
    var checkIn = document.getElementById("data_check_in").value;
    var checkOut = document.getElementById("data_check_out").value;
    
    //verificam ca ambele date sunt completate
    if (checkIn == "" || checkOut == "") {
        alert("Va rugam sa selectati ambele date!");
        return false;
    }
    
    //transformam textele in obiecte de tip Date pentru comparatie
    var dataIn = new Date(checkIn);
    var dataOut = new Date(checkOut);
    var azi = new Date();
    azi.setHours(0, 0, 0, 0); // Setam ora la 00:00 pentru comparatie corecta
    
    //verificam ca data de check-in nu este in trecut
    if (dataIn < azi) {
        alert("Data de check-in nu poate fi in trecut!");
        return false;
    }
    
    //verificam ca data de check-out este dupa data de check-in
    if (dataOut <= dataIn) {
        alert("Data de check-out trebuie sa fie dupa data de check-in!");
        return false;
    }
    
    return true;
}

//calculeaza pretul total in functie de numarul de nopti
//este apelata cand se schimba datele in formularul de rezervare
function calculeazaPret() {
    var checkIn = document.getElementById("data_check_in").value;
    var checkOut = document.getElementById("data_check_out").value;
    var pretCamera = document.getElementById("pret_camera").value;
    var afiseazaTotal = document.getElementById("total_pret");
    
    //daca lipseste vreo data, nu calculeaza nimic
    if (checkIn == "" || checkOut == "") {
        afiseazaTotal.innerHTML = "<em>Selectati datele pentru a vedea pretul</em>";
        return;
    }
    
    //calculam diferenta in zile
    var dataIn = new Date(checkIn);
    var dataOut = new Date(checkOut);
    var diferenta = dataOut - dataIn; // diferenta in milisecunde
    var nopti = diferenta / (1000 * 60 * 60 * 24); // transformam in zile
    
    //daca diferenta este negativa, afisam mesaj de eroare
    if (nopti <= 0) {
        afiseazaTotal.innerHTML = "<em>Datele introduse nu sunt valide</em>";
        return;
    }
    
    //calculam si afisam totalul
    var total = nopti * pretCamera;
    afiseazaTotal.innerHTML = "<strong>" + nopti + " nopti × " + pretCamera + " lei = " + total + " lei</strong>";
}


//afiseaza un dialog de confirmare inainte de o actiune importanta
function confirmaActiune(mesaj) {
    return confirm(mesaj);
}

/* ------------------------------------------------------------
   La incarcarea paginii setam data minima pentru check-in (azi)
   ------------------------------------------------------------ */
window.onload = function() {
    var inputCheckIn = document.getElementById("data_check_in");
    var inputCheckOut = document.getElementById("data_check_out");
    
    if (inputCheckIn) {
        //construim data de azi in format YYYY-MM-DD
        var azi = new Date();
        var an = azi.getFullYear();
        var luna = String(azi.getMonth() + 1).padStart(2, '0');
        var zi = String(azi.getDate()).padStart(2, '0');
        var dataAzi = an + '-' + luna + '-' + zi;
        
        inputCheckIn.setAttribute("min", dataAzi);
        if (inputCheckOut) {
            inputCheckOut.setAttribute("min", dataAzi);
        }
    }
}
