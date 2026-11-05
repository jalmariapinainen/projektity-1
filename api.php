<?php
include "yhteys.php";

$metodi = $_SERVER["REQUEST_METHOD"];

if($metodi === "POST"){
    $tunnus = "";
    $varauspaiva = "";
    $varausaika = "";
    $varausloppuaika = "";
    $kayttajaID = "";
    
    $kayttajatunnus = "";
    $salasana = "";

    $tarkoitusPOST = "";

    if($tarkoitusPOST == "kayttajaLisays"){ //Kun rekisteröidään

        $sql = "INSERT INTO Kayttajat (kayttajatunnus, salasana)
        VALUES ($kayttajatunnus", "$salasana)";
        }
    elseif($tarkoitusPOST == "varausLisays" && !empty($kayttajaID)){ //Kun lisätään varaus
        $sql = "INSERT INTO Huoneet (tunnus, varauspaiva, varausaika, varausloppuaika, kayttaja)
        VALUES ($tunnus", "$varauspaiva", "$varausaika", "$varausloppuaika", "$kayttajaID)";
    }

    if ($conn->query($sql) === TRUE) {
        json_encode("Lisätty", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } else {
            json_encode("Error: " . $sql . "<br>" . $conn->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}





if($metodi === "PUT"){
    $id ="";
    $tunnus = "";
    $varauspaiva = "";
    $varausaika = "";
    $varausloppuaika = "";

    $sql = "UPDATE Huoneet SET tunnus='.$tunnus.', varauspaiva='.$varauspaiva.', varausaika='.$varausaika.', varausloppuaika='.$varausloppuaika.' WHERE huone_id='$id'";

    if ($conn->query($sql) === TRUE) {
        json_encode("Varaus on päivitetty", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } else {
        json_encode("Error varauksen päivittämisessä: " . $conn->error;, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $conn->close();
}





if($metodi === "GET"){
    $varattu = "" ?? "";
    $tarkoitus = "";
    $id = "";

    $kayttajatunnus = "";
    $salasana = "";

    if($tarkoitus == "huoneNakymat"){ //Näytetään kaikki huoneet, jotka voidaan varata

        $sql = "SELECT huone_id, tunnus FROM (SELECT DISTINCT tunnus FROM Huoneet) ";
        // Execute the SQL query
        $result = $conn->query($sql);
    }

    elseif($tarkoitus == "Huoneen ajat" && isset($id)){ //Kun ollaan tietyn huoneen sivulla, niin näytetään sen huoneen mahdolliset ajat
        if($varattu === "false"){ //Jos suodatuksissa halutaan nähdä vain varaamattomat ajat

            $sql = "SELECT huone_id, tunnus, varattu FROM Huoneet WHERE huone_id = '.$id.' AND varattu = false";
        // Execute the SQL query
        $result = $conn->query($sql);
        }
        
        else{//Jos suodatusta ei ole lisätty
        $sql = "SELECT Huoneet.huone_id, Huoneet.tunnus, Huoneet.varattu, Huoneet.varauspaiva, Huoneet.varausaika, Huoneet.varausloppuaika, Kayttajat.kayttajatunnus FROM Huoneet WHERE huone_id = '.$id.'
        LEFT JOIN Kayttajat ON kayttaja = kayttaja_id
        ";
        // Execute the SQL query
        $result = $conn->query($sql);
        }
    }
    
    elseif($tarkoitus == "kayttajaHaku"){
        $sql = "SELECT kayttaja_id, kayttajatunnus, salasana FROM Kayttajat WHERE kayttajatunnus='.$kayttajatunnus.' AND salasana = '.$salasana.'";
        // Execute the SQL query
        $result = $conn->query($sql);
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $conn->close();
}





if($metodi === "DELETE"){
    $id ="";

    $sql = "DELETE FROM Huoneet WHERE huone_id='$id'";

    if ($conn->query($sql) === TRUE) {
        json_encode("Varaus on poistettu", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } else {
            json_encode("Error varauksen poistamisessa: " . $conn->error;, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
}





?>