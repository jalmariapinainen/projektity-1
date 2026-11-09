<?php
include "yhteys.php";
header("Content-Type: application/json; charset=utf-8");
$metodi = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true);

if($metodi === "POST"){
    
    $tarkoitusPOST = $data["tarkoitus"] ?? "";

    if($tarkoitusPOST == "kayttajaLisays"){ //Kun rekisteröidään
        $kayttajatunnus = $data["kayttajatunnus"] ?? "";
        $salasana = $data["salasana"] ?? "";
        $puhelinnro = $data["puhelinnumero"] ?? "";

        $sql = "INSERT INTO Kayttajat (kayttajatunnus, salasana, puh)
        VALUES ($kayttajatunnus', '$salasana', '$puhelinnro)";
        }
    elseif($tarkoitusPOST == "varausLisays" && !empty($kayttajaID)){ //Kun lisätään varaus
        $huone = $data["huone"] ?? "";
        $varauspaiva = $data["varauspaiva"] ?? "";
        $varausaika = $data["varausaika"] ?? "";
        $varausloppuaika = $data["varausloppuaika"] ?? "";
        $kayttajaID = $data["kayttajaID"] ?? "";

        $sql = "INSERT INTO Varaukset (huone_id, varauspaiva, varausaika, varausloppuaika, kayttaja)
        VALUES ($huone', '$varauspaiva', '$varausaika', '$varausloppuaika', '$kayttajaID)";
    }
    elseif($tarkoitusPOST == "huoneLisäys"){
        $tunnus = $data["tunnus"] ?? "";
        $sijainti = $data["sijainti"] ?? "";

        $sql = "INSERT INTO Huoneet (tunnus, sijainti)
        VALUES ($tunnus', '$sijainti)";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode("Lisätty", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } else {
        echo json_encode("Error: " . $sql . "<br>" . $conn->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}





if($metodi === "PUT"){
    $id = $data["kayttaja"] ?? "";
    $huone_id = $data["huone"] ?? "";
    $varauspaiva = $data["varausPaiva"] ?? "";
    $varausaika = $data["varausAika"] ?? "";
    $varausloppuaika = $data["varausLoppu"] ?? "";

    $sql = "UPDATE Varaukset SET huone_id='.$huone_id.', varauspaiva='.$varauspaiva.', varausaika='.$varausaika.', varausloppuaika='.$varausloppuaika.' WHERE huone_id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode("Varaus on päivitetty", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode("Error varauksen päivittämisessä: " . $conn->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $conn->close();
}





if($metodi === "GET"){
    $tarkoitus = $_GET["tarkoitus"] ?? "";
    
    //toimii--
    if($tarkoitus == "huoneNakymat"){ //Näytetään kaikki huoneet, jotka voidaan varata

        $sql = "SELECT huone_id, tunnus, sijainti FROM Huoneet";
        // Execute the SQL query
        $result = $conn->query($sql);

        $huoneet = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($huoneet, JSON_UNESCAPED_UNICODE);
        
        exit;
    }

    elseif($tarkoitus == "Huoneen ajat"){ //Kun ollaan tietyn huoneen sivulla, niin näytetään sen huoneen mahdolliset ajat

        $huone_id = $_GET["huone_id"] ?? "";

        $sql = "SELECT Varaukset.varaus_id, Varaukset.varauspaiva, Varaukset.varausaika, Varaukset.varausloppuaika, Kayttajat.kayttajatunnus FROM Varaukset WHERE huone_id = '.$huone_id.'
        LEFT JOIN Kayttajat ON Varaukset.kayttaja = Kayttajat.kayttaja_id";
        // Execute the SQL query
        $result = $conn->query($sql);
        
        $huoneet = $result->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($huoneet, JSON_UNESCAPED_UNICODE);
        exit;
        
    }
    
    elseif($tarkoitus == "kayttajaHaku"){

        $kayttajatunnus = $_GET["kayttajatunnus"] ?? "";
        $salasana = $_GET["salasana"] ?? "";

        $sql = "SELECT kayttaja_id, kayttajatunnus, salasana FROM Kayttajat WHERE kayttajatunnus='.$kayttajatunnus.' AND salasana = '.$salasana.'";
        // Execute the SQL query
        $result = $conn->query($sql);

        $kayttaja = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($kayttaja, JSON_UNESCAPED_UNICODE);
        exit;
        
    }

    $conn->close();
}





if($metodi === "DELETE"){
    $id = $data["varaus_id"] ?? "";

    $sql = "DELETE FROM Varaukset WHERE varaus_id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode("Varaus on poistettu", JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } else {
        echo json_encode("Error varauksen poistamisessa: " . $conn->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
}





?>



