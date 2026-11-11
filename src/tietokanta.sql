
----------------------
-- PHPMyADMIN koodi --
----------------------





CREATE DATABASE IF NOT EXISTS Projekti1New;
USE Projekti1New;

-- 1. Luodaan Kayttajat-taulu
CREATE TABLE Kayttajat (
    kayttaja_id INT AUTO_INCREMENT PRIMARY KEY,
    kayttajatunnus VARCHAR(50),
    sahkoposti VARCHAR(50),
    salasana VARCHAR(255),
    puh VARCHAR(20)
);

-- 2. Luodaan Huoneet-taulu (kuvan mukainen rakenne)
CREATE TABLE Huoneet (
    huone_id INT AUTO_INCREMENT PRIMARY KEY,
    tunnus VARCHAR(25),
    sijainti VARCHAR(50),
    pintaAla INT(3)
);

-- 3. Luodaan Varaukset-taulu, joka viittaa sekä huoneisiin että käyttäjiin
CREATE TABLE Varaukset (
    varaus_id INT AUTO_INCREMENT PRIMARY KEY,
    huone_id INT,
    varauspaiva DATE,
    varausaika TIME,
    varausloppuaika TIME,
    kayttaja INT,
    CONSTRAINT fk_varaukset_huone 
        FOREIGN KEY (huone_id) 
        REFERENCES Huoneet(huone_id),
    CONSTRAINT fk_varaukset_kayttaja 
        FOREIGN KEY (kayttaja) 
        REFERENCES Kayttajat(kayttaja_id)
);


--Testi dataa (Ensin Kayttajat, muuten virhe tulee lisäämisessä) --

INSERT INTO Kayttajat (kayttajatunnus, salasana, puh) 
VALUES 
('matti_meikalainen', 'matti.meikalainen@example.com', 'salasana123', '040 123 4567'),
('maija_teikalainen', 'maija.teikalainen@example.com', 'turvallinen456', '045 876 5432');


INSERT INTO Huoneet (tunnus, sijainti, pintaAla) 
VALUES 
('Huone 101', '1. kerros, Voima', '32'),
('Huone 102', '2. kerros, Voima', '24');


INSERT INTO Varaukset (huone_id, varauspaiva, varausaika, varausloppuaika, kayttaja) 
VALUES 
(1, '2026-06-06', '12:00:00', '14:00:00', 1),
(2, '2026-06-07', '15:00:00', '16:30:00', 2);
