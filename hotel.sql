
-- stergem baza de date daca exista deja 
DROP DATABASE IF EXISTS hotel;

-- cream baza de date noua
CREATE DATABASE hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel;

-- stocheaza conturile pentru clienti, lucratori si administratori
CREATE TABLE utilizatori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    parola VARCHAR(255) NOT NULL,
    rol ENUM('client', 'lucrator', 'admin') NOT NULL DEFAULT 'client',
    data_inregistrare TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- stocheaza inventarul de camere al hotelului
CREATE TABLE camere (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    descriere TEXT NOT NULL,
    pret DECIMAL(10, 2) NOT NULL,
    capacitate INT NOT NULL DEFAULT 2,
    imagine VARCHAR(255) DEFAULT 'img/camera-default.jpg',
    status ENUM('disponibila', 'curatenie') NOT NULL DEFAULT 'disponibila'
);

-- stocheaza rezervarile facute de clienti
-- relatii: utilizatori (1) -> rezervari (N) -> camere (1)
CREATE TABLE rezervari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    id_camera INT NOT NULL,
    data_check_in DATE NOT NULL,
    data_check_out DATE NOT NULL,
    status ENUM('in_asteptare', 'confirmata', 'check_in', 'check_out', 'anulata') 
        NOT NULL DEFAULT 'in_asteptare',
    data_creare TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_camera) REFERENCES camere(id) ON DELETE CASCADE
);

-- conturi predefinite (parolele sunt hash-uite cu password_hash() in PHP)
-- parole originale: admin123, lucrator123, client123
INSERT INTO utilizatori (nume, email, parola, rol) VALUES
('Administrator Principal', 'admin@aurelia.ro', '$2y$10$32ZRHpEjIT3dMyYuNRjsbeOJmP9hlRo5uaIe1J1P21Bl6KykGPVIi', 'admin'),
('Ion Popescu', 'lucrator@aurelia.ro', '$2y$10$W301GlBgWCTM6Q/n9Hi/P.V9Ty97HbZDcUmMdpuWlSTb5JckYc9Fi', 'lucrator'),
('Maria Constantin', 'client@aurelia.ro', '$2y$10$Pv0m7pEybcD0dyB5243XS.Z5a19QG4fEQSbvfUTGkF4nvsmMn/z6m', 'client');

-- camere predefinite
INSERT INTO camere (nume, descriere, pret, capacitate, imagine, status) VALUES
('Camera Standard', 'Camera confortabila cu pat dublu, baie privata, Wi-Fi gratuit si televizor cu ecran plat. Ideala pentru sederi scurte de afaceri sau weekend-uri romantice.', 280.00, 2, 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=800&q=80', 'disponibila'),

('Camera Deluxe', 'Camera spatioasa cu vedere la gradina hotelului. Include pat king-size, birou de lucru si mic dejun inclus. Perfecta pentru cupluri.', 420.00, 2, 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&q=80', 'disponibila'),

('Junior Suite', 'Suita eleganta cu living separat si dormitor. Dotata cu masina espresso, baie de marmura si toate facilitatile moderne pentru un sejur de neuitat.', 620.00, 2, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800&q=80', 'disponibila'),

('Camera Familie', 'Camera spatioasa pentru familii, cu doua paturi duble si zona de relaxare. Include Netflix, mini frigider si zona dedicata pentru copii.', 560.00, 4, 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800&q=80', 'disponibila'),

('Suite Imperiala', 'O experienta regala in inima orasului. Suita oferta priveliste panoramica, mobilier de epoca, jacuzzi si servicii personalizate 24/7.', 850.00, 2, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&q=80', 'disponibila'),

('Penthouse Aurelia', 'Summum-ul luxului. Penthouse-ul ocupa intregul etaj superior, cu terasa privata, dining privat, butler 24/7 si servicii de concierge dedicate.', 2200.00, 4, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&q=80', 'disponibila');

