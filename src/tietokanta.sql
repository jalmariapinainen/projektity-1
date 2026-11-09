
----------------------
-- PHPMyADMIN koodi --
----------------------





-- Luodaan tietokanta (voit halutessasi vaihtaa nimen)
CREATE DATABASE IF NOT EXISTS Projekti1;
USE Projekti1;

-- 1. Luodaan ensin Kayttajat-taulu, koska Huoneet viittaa siihen[cite: 4]
CREATE TABLE Kayttajat (
    kayttaja_id INT AUTO_INCREMENT PRIMARY KEY,
    kayttajatunnus VARCHAR(50),
    salasana VARCHAR(50)
);

-- 2. Luodaan sen jälkeen Huoneet-taulu, jossa on vierasavain viittauksella Kayttajiin[cite: 4]
CREATE TABLE Huoneet (
    huone_id INT AUTO_INCREMENT PRIMARY KEY,
    tunnus VARCHAR(25),
    varattu VARCHAR(10),
    varauspaiva DATE,
    varausaika TIME,
    varausloppuaika TIME,
    kayttaja INT,
    CONSTRAINT fk_huoneet_kayttaja 
        FOREIGN KEY (kayttaja) 
        REFERENCES Kayttajat(kayttaja_id)
);


--Testi dataa (Ensin Kayttajat, muuten virhe tulee lisäämisessä) --
INSERT INTO Kayttajat (kayttajatunnus, salasana) 
VALUES 
('matti_meikalainen', 'salasana123'),
('maija_teikalainen', 'turvallinen456');


INSERT INTO Huoneet (tunnus, varattu, varauspaiva, varausaika, varausloppuaika, kayttaja) 
VALUES 
('Huone 101', 'kyllä', '2026-06-06', '12:00:00', '14:00:00', 1),
('Huone 102', 'ei', '2026-06-07', '15:00:00', '16:30:00', 2);

