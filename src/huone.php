<?php
 // include "jalmarik.html";
$huoneid = $_GET["id"];
$url = "api.php?huone_id=$huoneid";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Palauttaa tuloksen muuttujaan tulostamisen sijaan
curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Aikakatkaisu 10 sekuntia

$response = curl_exec($ch);

curl_close($ch);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Huoneen tieto</title>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id="huone_otsikko"><h1>Otsikko</h1></div>
        <br>
        <br>
        <div id="huone_rakenne">

            <div>
                <div id="valittu_paiva">
                        <h2 id="valittu_paivateksti">Valittu päivä: </h2>
                </div>
                <br><br>
                <div id="huone_kalenteri">
                    
                    <div id="paiva_kalenteri">
                        <h2>
                        <button onclick="vaihda(-1)">←</button>
                        <span id="kuukausi"></span>
                        <button onclick="vaihda(1)">→</button>
                        </h2>

                        <div class="paivat" id="kalenteri"></div>
                        <p>Valittu päivä: <span id="valittu">Ei valittu</span></p>
                    </div>
                </div>

            </div>

            <div id="huone_muu">
                
                <div id="huone_ajat"><p>Aikadial</p>
            
                     
            
                </div>

            </div>

            <div id="lisäys_lomake">
                
            </div>

            <script src="ajat.js">

                
            </script>
        </div>
    </body>
</html>