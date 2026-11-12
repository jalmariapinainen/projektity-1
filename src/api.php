<?php

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "httponly" => true,
    "samesite" => "Lax"
]);
session_start();

header("Content-Type: application/json; charset=utf-8");

function vastaus($teksti, $koodi = 200, $extra = []) {
    http_response_code($koodi);
    echo json_encode(
        array_merge(["message" => $teksti], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    require_once __DIR__ . "/yhteys.php";
} catch (PDOException $e) {
    error_log("Tietokantayhteyden muodostus epäonnistui: " . $e->getMessage());
    vastaus("Tietokantaan ei saatu yhteyttä. Tarkista, että palvelut ovat käynnissä.", 500);
}

$metodi = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true) ?? [];


// ============================
// POST
// ============================

if ($metodi === "POST") {

    $tarkoitus = $data["tarkoitus"] ?? "";


    // REKISTERÖITYMINEN

    if ($tarkoitus === "kayttajaLisays") {

        $tunnus = trim($data["kayttajatunnus"] ?? "");
        $sahkoposti = trim($data["sahkoposti"] ?? "");
        $salasana = $data["salasana"] ?? "";
        $puhelin = trim($data["puhelinnumero"] ?? $data["puh"] ?? "");

        if ($tunnus === "" || strlen($tunnus) > 254 || strlen($salasana) < 8) {
            vastaus("Käyttäjätunnuksen enimmäispituus on 254 merkkiä ja salasanan vähimmäispituus 8 merkkiä.", 400);
        }
        
        //tarkistetaan onko käyttäjää jo tietokannassa
        $tarkistus = $conn->prepare(
            "SELECT kayttaja_id FROM Kayttajat WHERE kayttajatunnus = ?"
        );
        $tarkistus->execute([$tunnus]);

        if ($tarkistus->fetch()) {
            vastaus("Käyttäjätunnus on jo käytössä.", 409);
            
        } //poistuu takaisin sivulle, jos käyttäjä on jo olemassa

        $hash = password_hash($salasana, PASSWORD_DEFAULT);

        $sql = $conn->prepare(
            "INSERT INTO Kayttajat (kayttajatunnus, sahkoposti, salasana, puh)
             VALUES (?, ?, ?, ?)"
        );

        try {
            $sql->execute([$tunnus, $sahkoposti, $hash, $puhelin]);
        } catch (PDOException $e) {
            error_log("Käyttäjän rekisteröinti epäonnistui: " . $e->getMessage());
            vastaus("Rekisteröityminen epäonnistui. Tarkista tiedot ja yritä uudelleen.", 500);
        }

        vastaus("Rekisteröityminen onnistui!");
    }


    


    // SALASANAN VAIHTO

    elseif ($tarkoitus === "salasanaVaihto") {

        if (empty($_SESSION["kayttaja_id"])) {
            vastaus("Kirjaudu ensin sisään.", 401);
        }

        $nykyinen = $data["nykyinenSalasana"] ?? "";
        $uusi = $data["uusiSalasana"] ?? "";

        if (strlen($uusi) < 8) {
            vastaus("Salasanan pitää olla vähintään 8 merkkiä.", 400);
        }

        $sql = $conn->prepare(
            "SELECT salasana FROM Kayttajat WHERE kayttaja_id = ?"
        );

        $sql->execute([$_SESSION["kayttaja_id"]]);
        $kayttaja = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$kayttaja || !password_verify($nykyinen, $kayttaja["salasana"])) {
            vastaus("Nykyinen salasana on väärin.", 401);
        }

        $hash = password_hash($uusi, PASSWORD_DEFAULT);

        $paivitys = $conn->prepare(
            "UPDATE Kayttajat SET salasana = ? WHERE kayttaja_id = ?"
        );

        $paivitys->execute([$hash, $_SESSION["kayttaja_id"]]);

        vastaus("Salasana vaihdettu onnistuneesti!");
    }


    // KIRJAUTUMINEN ULOS

    elseif ($tarkoitus === "ulos") {

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        vastaus("Kirjauduit ulos.");
    }


    // HUONEEN LISÄÄMINEN

    elseif ($tarkoitus === "huoneLisäys") {

        $tunnus = $data["tunnus"] ?? "";
        $sijainti = $data["sijainti"] ?? "";

        $sql = $conn->prepare(
            "INSERT INTO Huoneet (tunnus, sijainti) VALUES (?, ?)"
        );

        $sql->execute([$tunnus, $sijainti]);

        vastaus("Huone lisätty!");
    }


    // VARAUKSEN LISÄÄMINEN

    elseif ($tarkoitus === "varausLisays") {

        if (empty($_SESSION["kayttaja_id"])) {
            vastaus("Kirjaudu ensin sisään.", 401);
        }

        $huone = $data["huone"] ?? "";
        $paiva = $data["varauspaiva"] ?? "";
        $alku = $data["varausaika"] ?? "";
        $loppu = $data["varausloppuaika"] ?? "";

        $kelvollinenAika = '/^(?:0[6-9]|1[0-9]):(00|30):00$|^20:00:00$/';
        if (
            $huone === "" ||
            $paiva === "" ||
            !preg_match($kelvollinenAika, $alku) ||
            !preg_match($kelvollinenAika, $loppu) ||
            $alku >= $loppu
        ) {
            vastaus("Tarkista varauksen huone, päivä ja kellonajat.", 400);
        }

        try {
            $conn->beginTransaction();

            $lukitseHuone = $conn->prepare(
                "SELECT huone_id FROM Huoneet WHERE huone_id = ? FOR UPDATE"
            );
            $lukitseHuone->execute([$huone]);

            if ($lukitseHuone->fetchColumn() === false) {
                $conn->rollBack();
                vastaus("Huonetta ei löytynyt.", 404);
            }

            $tarkistus = $conn->prepare(
                "SELECT 1
                 FROM Varaukset
                 WHERE huone_id = ?
                   AND varauspaiva = ?
                   AND varausaika < ?
                   AND varausloppuaika > ?
                 LIMIT 1"
            );
            $tarkistus->execute([$huone, $paiva, $loppu, $alku]);

            if ($tarkistus->fetchColumn() !== false) {
                $conn->rollBack();
                vastaus("Valittu aika on jo varattu.", 409);
            }

            $sql = $conn->prepare(
                "INSERT INTO Varaukset
                (huone_id, varauspaiva, varausaika, varausloppuaika, kayttaja)
                VALUES (?, ?, ?, ?, ?)"
            );
            $sql->execute([
                $huone,
                $paiva,
                $alku,
                $loppu,
                $_SESSION["kayttaja_id"]
            ]);

            $conn->commit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Varauksen lisääminen epäonnistui: " . $e->getMessage());
            vastaus("Varauksen lisääminen epäonnistui tietokantavirheen vuoksi.", 500);
        }

        vastaus("Varaus lisätty!");
    }

    elseif($tarkoitus == "Huoneen_ajat"){ //Kun ollaan tietyn huoneen sivulla, niin näytetään sen huoneen mahdolliset ajat
    }

    elseif ($tarkoitus === "kirjautuminen") {

        $tunnus = trim($data["kayttajatunnus"] ?? "");
        $salasana = $data["salasana"] ?? "";

        $sql = $conn->prepare(
            "SELECT kayttaja_id, kayttajatunnus, salasana
             FROM Kayttajat
             WHERE kayttajatunnus = ?"
        );

        $sql->execute([$tunnus]);
        $kayttaja = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$kayttaja) {
            vastaus("Virheellinen käyttäjätunnus tai salasana.", 401);
        }

        $oikein = password_verify($salasana, $kayttaja["salasana"]);

        // Vanhojen testitunnusten käsittely
        if (!$oikein && hash_equals($kayttaja["salasana"], $salasana)) {

            $hash = password_hash($salasana, PASSWORD_DEFAULT);

            $paivitys = $conn->prepare(
                "UPDATE Kayttajat SET salasana = ? WHERE kayttaja_id = ?"
            );

            $paivitys->execute([$hash, $kayttaja["kayttaja_id"]]);

            $oikein = true;
        }

        if (!$oikein) {
            vastaus("Virheellinen käyttäjätunnus tai salasana.", 401);
        }

        session_regenerate_id(true);

        $_SESSION["kayttaja_id"] = $kayttaja["kayttaja_id"];
        $_SESSION["kayttajatunnus"] = $kayttaja["kayttajatunnus"];

        vastaus("Kirjautuminen onnistui!");
    }
    
}
// ============================
// GET
// ============================

elseif ($metodi === "GET") {

    $tarkoitus = $_GET["tarkoitus"] ?? "";

    //KIRJAUTUNEEN KÄYTTÄJÄTUNNUKSEN HAKU
    if ($tarkoitus === "omaKayttaja") {
        if (empty($_SESSION["kayttaja_id"])) {
            vastaus("Et ole kirjautunut sisään.", 401);
        }

        vastaus("Kirjautunut käyttäjä.", 200, [
            "kayttajatunnus" => $_SESSION["kayttajatunnus"]
        ]);
    }


    // KAIKKI HUONEET

    if ($tarkoitus === "huoneNakymat") {

        $sql = $conn->query(
            "SELECT huone_id, tunnus, sijainti, pintaAla FROM Huoneet"
        );

        echo json_encode(
            $sql->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    //HUONEEN HAKU
    elseif ($tarkoitus === "Huoneen_haku") {
        $haku = "%" . ($_GET["haku"] ?? "") . "%";

        $sql = $conn->prepare(
            "SELECT huone_id, tunnus, sijainti
             FROM Huoneet
             WHERE tunnus LIKE ? OR sijainti LIKE ?"
        );
        $sql->execute([$haku, $haku]);

        echo json_encode(
            $sql->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    // HUONEEN VARAUSAJAT

    elseif ($tarkoitus === "Huoneen_ajat") {

        $huone = $_GET["huone_id"] ?? "";

        $sql = $conn->prepare(
            "SELECT Varaukset.varaus_id,
                    Varaukset.varauspaiva,
                    Varaukset.varausaika,
                    Varaukset.varausloppuaika,
                    Kayttajat.kayttajatunnus
             FROM Varaukset
             LEFT JOIN Kayttajat
             ON Varaukset.kayttaja = Kayttajat.kayttaja_id
             WHERE Varaukset.huone_id = ?"
        );

        $sql->execute([$huone]);

        echo json_encode(
            $sql->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    //TIETYN HUONEEN TIEDOT

    elseif ($tarkoitus === "Huoneen_tiedot") {
        $huone = $_GET["huone_id"] ?? "";

        $sql = $conn->prepare(
            "SELECT tunnus, sijainti FROM Huoneet WHERE huone_id = ?"
        );

        $sql->execute([$huone]);
        $tiedot = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$tiedot) {
            vastaus("Huonetta ei löytynyt.", 404);
        }

        echo json_encode($tiedot, JSON_UNESCAPED_UNICODE);
        exit;
    }

    elseif($tarkoitus === "kayttajan_ajat"){
        if (empty($_SESSION["kayttaja_id"])) {
            vastaus("Kirjaudu ensin sisään.", 401);
        }

        $kayttaja = $_SESSION["kayttaja_id"];

        $sql = $conn->prepare(
            "SELECT Varaukset.varaus_id, Varaukset.huone_id, Huoneet.tunnus, Huoneet.sijainti, Varaukset.varauspaiva, Varaukset.varausaika, Varaukset.varausloppuaika FROM Varaukset
            LEFT JOIN Huoneet
            ON Varaukset.huone_id = Huoneet.huone_id
            WHERE Varaukset.kayttaja = ?"
        );

        $sql->execute([$kayttaja]);
        $tiedot = $sql->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($tiedot, JSON_UNESCAPED_UNICODE);
        exit;
    }

    else {
        vastaus("Tuntematon GET-toiminto.", 400);
    }

    
}


// ============================
// PUT
// ============================

elseif ($metodi === "PUT") {
$tarkoitus = $data["tarkoitus"] ?? "";

    if($tarkoitus === "editVaraus"){

        if (empty($_SESSION["kayttaja_id"])) {
            vastaus("Kirjaudu ensin sisään.", 401);
        }

        $id = $data["varaus_id"] ?? "";
        $huone = $data["huone"] ?? "";
        $paiva = $data["varausPaiva"] ?? "";
        $alku = $data["varausAika"] ?? "";
        $loppu = $data["varausLoppu"] ?? "";


        $kelvollinenAika = '/^(?:0[6-9]|1[0-9]):(00|30):00$|^20:00:00$/';

        if ( //virheen tarkistus inputeille
            $id === "" ||
            $huone === "" ||
            $paiva === "" ||
            !preg_match($kelvollinenAika, $alku) ||
            !preg_match($kelvollinenAika, $loppu) ||
            $alku >= $loppu
        ) {
            vastaus("Tarkista varauksen huone, päivä ja kellonajat.", 400);
        }

        try {
            //varmistaa huoneen silti olemassaolon
            $conn->beginTransaction();

            $lukitseHuone = $conn->prepare(
                "SELECT huone_id FROM Huoneet WHERE huone_id = ? FOR UPDATE"
            );
            $lukitseHuone->execute([$huone]);

            if ($lukitseHuone->fetchColumn() === false) {
                $conn->rollBack();
                vastaus("Huonetta ei löytynyt.", 404);
            }

            //katsoo jos se on lähettäjän varaus
            $omaVaraus = $conn->prepare(
                "SELECT varaus_id
                FROM Varaukset
                WHERE varaus_id = ? AND kayttaja = ?
                FOR UPDATE"
            );
            $omaVaraus->execute([$id, $_SESSION["kayttaja_id"]]);

            if ($omaVaraus->fetchColumn() === false) {
                $conn->rollBack();
                vastaus("Varausta ei löytynyt tai se ei kuulu sinulle.", 404);
            }

            //tarkistaa ettei ole varauksia uuden ajan välillä
            $tarkistus = $conn->prepare(
                "SELECT 1
                FROM Varaukset
                WHERE huone_id = ?
                AND varauspaiva = ?
                AND varausaika < ?
                AND varausloppuaika > ?
                AND varaus_id <> ?
                LIMIT 1"
            );
            $tarkistus->execute([$huone, $paiva, $loppu, $alku, $id]);

            if ($tarkistus->fetchColumn() !== false) {
                $conn->rollBack();
                vastaus("Valittu aika on jo varattu.", 409);
            }

            //Jos ei ole aikoja välillä ja kaikki on hyvin, niin päivitetään huoneen varaus
            $sql = $conn->prepare(
                "UPDATE Varaukset
                SET huone_id = ?, varauspaiva = ?,
                    varausaika = ?, varausloppuaika = ?
                WHERE varaus_id = ? AND kayttaja = ?"
            );
            $sql->execute([
                $huone,
                $paiva,
                $alku,
                $loppu,
                $id,
                $_SESSION["kayttaja_id"]
            ]);

            $conn->commit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Varauksen päivittäminen epäonnistui: " . $e->getMessage());
            vastaus("Varauksen päivittäminen epäonnistui tietokantavirheen vuoksi.", 500);
        }

        vastaus("Varaus päivitetty!");
    }
}


// ============================
// DELETE
// ============================

elseif ($metodi === "DELETE") {
    //ainoastaan kirjautunut pystyy yrittää poistaa
    if (empty($_SESSION["kayttaja_id"])) {
        vastaus("Kirjaudu ensin sisään.", 401);
    }
    //mitä poistetaan
    $tarkoitus = $data["tarkoitus"] ?? "";

    if ($tarkoitus === "poistaVaraus") {

        //muuttuja
        $id = $data["varaus_id"] ?? "";

        //virheen tarkastus
        if ($id === "") {
            vastaus("Varauksen tunnus puuttuu.", 400);
        }

        //poisto komento
        $sql = $conn->prepare(
            "DELETE FROM Varaukset
            WHERE varaus_id = ? AND kayttaja = ?"
        );
        $sql->execute([$id, $_SESSION["kayttaja_id"]]);

        //Jos ei ole varausta, missä id on haettu ja sinun käyttäjä on varauksen tekijä, niin virhe ilmoitetaan
        if ($sql->rowCount() === 0) {
            vastaus("Varausta ei löytynyt tai se ei kuulu sinulle.", 404);
        }

        //onnistuneesta poistosta ilmoitetaan
        vastaus("Varaus poistettu!");
    }
}
else {
    vastaus("Metodia ei tueta.", 405);
}
?>
