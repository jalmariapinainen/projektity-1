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
    <body onload="otsikko().catch(error => console.error('Huoneen otsikon haku epäonnistui:', error))">
        <?php
        include "header.php";
        ?>
        <br>
        <br>
        <div id="huone_otsikko"><h1 id="Huonetunnus">Otsikko</h1> <h2 id="Huonesijainti"></h2></div>
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
                        <button onclick="vaihda(-1)" class="kalenterinappi">←</button>
                        <span id="kuukausi"></span>
                        <button onclick="vaihda(1)" class="kalenterinappi">→</button>
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

            <div id="lisays_sisalto">
                <div id="lisäys_lomake">
                    <h1>Lisää ajanvaraus: </h1>
                    <br>
                    <hr>
                    <br>
                    <p id="kirjautumistarkistus" role="status"></p>
                    <h2 id="valittu_paivateksti_form"></h2>
                    <br>
                    <div id="aijanvaraus_aika_inputs">
                        <div id="aloitus_aika_input">
                            <label for="aloitusAika" id="aloitusTeksti">Start</label>
                            <input type="text" id="aloitusAika" placeholder="8:00" required>
                        </div>
                        <p>:</p>
                        <div id="lopetus_aika_input">
                            <label for="lopetusAika" id="lopetusTeksti">End</label>
                            <input type="text" id="lopetusAika" placeholder="9:30" required>
                        </div>
                    </div>
                    <button onclick="ajanlisays()">Lisää</button>
                    <div id="huone-lisays-message-div">
                    <p id="huone-lisays-message"></p>
                    </div>
                </div>
                <div id="kirjautuminen_peite" role="status" hidden>
                    <h3 id="lomakevirhesyy"></h3>
                </div>
            </div>
            
            
            <script src="ajat.js">

                
            </script>
        </div>
    </body>
</html>