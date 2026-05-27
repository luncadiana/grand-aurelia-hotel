# Grand Aurelia Hotel - Platforma Web de Administrare Hoteliera

Proiect realizat in cadrul cursului **Tehnologii de Programare in Internet** (TPI).

## Tehnologii utilizate

- **HTML5** + **CSS3** - structura si stilul paginilor
- **Bootstrap 5.1** - framework CSS pentru aspect responsive
- **JavaScript** - validari de formulare si interactivitate
- **PHP 8** - logica server-side
- **MySQL** - baza de date relationala

## Structura proiectului

```
hotel-php/
├── index.php                  - Pagina principala (lista camere + filtrare)
├── login.php                  - Autentificare utilizatori
├── inregistrare.php           - Cont nou pentru clienti
├── logout.php                 - Deconectare
├── rezerva.php                - Formular rezervare camera
├── rezervarile-mele.php       - Lista rezervari client + anulare
├── lucrator.php               - Dashboard receptioner
├── admin.php                  - Dashboard administrator
├── hotel.sql                  - Script creare baza de date
│
├── includes/
│   ├── db.php                 - Conexiunea la baza de date
│   ├── functii.php            - Functii ajutatoare
│   ├── header.php             - Header comun (navbar)
│   └── footer.php             - Footer comun
│
├── css/
│   └── style.css              - Stiluri personalizate
│
├── js/
│   └── script.js              - Cod JavaScript (validari)
│
└── img/                       - Imagini (incarcate de utilizatori)
```

## Instalare si rulare

### Pasul 1: Instaleaza XAMPP

Descarca XAMPP de la: https://www.apachefriends.org/
Instaleaza si porneste **Apache** + **MySQL** din panoul XAMPP.

### Pasul 2: Copiaza proiectul

Copiaza intreg folderul `hotel-php` in directorul:
- Windows: `C:\xampp\htdocs\hotel-php\`
- Mac/Linux: `/Applications/XAMPP/htdocs/hotel-php/`

### Pasul 3: Creeaza baza de date

1. Deschide browserul si mergi la: http://localhost/phpmyadmin
2. Click pe tab-ul **Import**
3. Selecteaza fisierul `hotel.sql` din folderul proiectului
4. Click pe **Go** pentru a executa scriptul
5. Baza de date `hotel` va fi creata automat cu 3 tabele si date de test

### Pasul 4: Acceseaza site-ul

Deschide browserul si mergi la: http://localhost/hotel-php

## Conturi de test

| Rol | Email | Parola |
|-----|-------|--------|
| Administrator | admin@aurelia.ro | admin123 |
| Lucrator (receptie) | lucrator@aurelia.ro | lucrator123 |
| Client | client@aurelia.ro | client123 |

## Functionalitati implementate

Toate cele 7 cazuri de utilizare din documentul de cerinte:

- **UC 3.1** - Autentificare cu 3 roluri (admin, lucrator, client)
- **UC 3.2** - Vizualizare si rezervare camere
- **UC 3.3** - Gestionare rezervari (lucrator)
- **UC 3.4** - Administrare inventar camere (CRUD - admin)
- **UC 3.5** - Cautare si filtrare camere (dupa pret si capacitate)
- **UC 3.6** - Anulare rezervare (client)
- **UC 3.7** - Marcare camera in curatenie (lucrator)

## Diagrama Entitate-Relatie

```
┌─────────────────┐        ┌──────────────────┐        ┌─────────────┐
│  utilizatori    │        │    rezervari     │        │   camere    │
├─────────────────┤        ├──────────────────┤        ├─────────────┤
│ id (PK)         │◄──────┤│ id (PK)          │├──────►│ id (PK)     │
│ nume            │   1:N  │ id_utilizator(FK)│ N:1    │ nume        │
│ email           │        │ id_camera (FK)   │        │ descriere   │
│ parola          │        │ data_check_in    │        │ pret        │
│ rol             │        │ data_check_out   │        │ capacitate  │
│ data_inreg      │        │ status           │        │ imagine     │
└─────────────────┘        │ data_creare      │        │ status      │
                           └──────────────────┘        └─────────────┘
```

## Securitate

- Parolele sunt criptate cu `password_hash()` (algoritm bcrypt)
- Toate interogarile SQL folosesc **prepared statements** (impotriva SQL Injection)
- Datele de la utilizator sunt curatate cu `htmlspecialchars()` (impotriva XSS)
- Sesiunile PHP sunt folosite pentru autentificare
- Verificari de roluri pe fiecare pagina cu acces restrictionat
