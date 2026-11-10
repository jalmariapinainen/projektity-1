<?php

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
        $salasana = $data["salasana"] ?? "";
        $puhelin = trim($data["puhelinnumero"] ?? $data["puh"] ?? "");

        if ($tunnus === "" || strlen($tunnus) > 254 || strlen($salasana) < 8) {
            vastaus("Käyttäjätunnuksen enimmäispituus on 254 merkkiä ja salasanan vähimmäispituus 8 merkkiä.", 400);
        }

        $tarkistus = $conn->prepare(
            "SELECT kayttaja_id FROM Kayttajat WHERE kayttajatunnus = ?"
        );
        $tarkistus->execute([$tunnus]);

        if ($tarkistus->fetch()) {
            vastaus("Käyttäjätunnus on jo käytössä.", 409);
        }

        $hash = password_hash($salasana, PASSWORD_DEFAULT);

        $sql = $conn->prepare(
            "INSERT INTO Kayttajat (kayttajatunnus, salasana, puh)
             VALUES (?, ?, ?)"
        );

        try {
            $sql->execute([$tunnus, $hash, $puhelin]);
        } catch (PDOException $e) {
            error_log("Käyttäjän rekisteröinti epäonnistui: " . $e->getMessage());
            vastaus("Rekisteröityminen epäonnistui. Tarkista tiedot ja yritä uudelleen.", 500);
        }

        vastaus("Rekisteröityminen onnistui!");
    }


    // KIRJAUTUMINEN

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

        vastaus("Kirjautuminen onnistui!");
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

        vastaus("Varaus lisätty!");
    }

    else {
        vastaus("Tuntematon toiminto.", 400);
    }
}


// ============================
// GET
// ============================

elseif ($metodi === "GET") {

    $tarkoitus = $_GET["tarkoitus"] ?? "";


    // KAIKKI HUONEET

    if ($tarkoitus === "huoneNakymat") {

        $sql = $conn->query(
            "SELECT huone_id, tunnus, sijainti FROM Huoneet"
        );

        echo json_encode(
            $sql->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // HUONEEN VARAUSAJAT

    elseif ($tarkoitus === "Huoneen ajat") {

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

    else {
        vastaus("Tuntematon GET-toiminto.", 400);
    }
}


// ============================
// PUT
// ============================

elseif ($metodi === "PUT") {

    if (empty($_SESSION["kayttaja_id"])) {
        vastaus("Kirjaudu ensin sisään.", 401);
    }

    $id = $data["varaus_id"] ?? "";
    $huone = $data["huone"] ?? "";
    $paiva = $data["varausPaiva"] ?? "";
    $alku = $data["varausAika"] ?? "";
    $loppu = $data["varausLoppu"] ?? "";

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

    vastaus("Varaus päivitetty!");
}


// ============================
// DELETE
// ============================

elseif ($metodi === "DELETE") {

    if (empty($_SESSION["kayttaja_id"])) {
        vastaus("Kirjaudu ensin sisään.", 401);
    }

    $id = $data["varaus_id"] ?? "";

    $sql = $conn->prepare(
        "DELETE FROM Varaukset
         WHERE varaus_id = ? AND kayttaja = ?"
    );

    $sql->execute([$id, $_SESSION["kayttaja_id"]]);

    vastaus("Varaus poistettu!");
}

else {
    vastaus("Metodia ei tueta.", 405);
}

?>
