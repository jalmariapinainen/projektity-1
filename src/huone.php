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
        <style>
            .varausaika-osio {
                align-self: start;
                box-sizing: border-box;
                width: 100%;
                min-width: 0;
                padding: 1rem;
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
                background: #fff;
            }

            .varausaika-valinnat {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.75rem;
                margin-top: 1rem;
            }

            .varausaika-kentta {
                min-width: 0;
            }

            .varausaika-kentta label {
                display: block;
                margin-bottom: 0.4rem;
                font-weight: 700;
            }

            .varausaika-kentta select {
                box-sizing: border-box;
                width: 100%;
                min-height: 3rem;
                padding: 0.6rem;
                border: 2px solid #64748b;
                border-radius: 0.4rem;
                background: #fff;
                color: #111827;
                font: inherit;
            }

        </style>
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
            <form id="varauslomake" class="varausaika-osio">
                <input type="hidden" id="varauspaiva" name="varauspaiva">
                <h2 id="varausaika-otsikko">Valitse varauksen aika</h2>
                <div class="varausaika-valinnat">
                    <div class="varausaika-kentta">
                        <label for="uusiAlku">Alkamisaika</label>
                        <select id="uusiAlku" name="varausaika" required disabled>
                            <option value="">Valitse alkamisaika</option>
                            <?php for ($minuutit = 6 * 60; $minuutit < 20 * 60; $minuutit += 30):
                                $aika = sprintf("%02d:%02d", intdiv($minuutit, 60), $minuutit % 60);
                            ?>
                                <option value="<?= $aika ?>"><?= $aika ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="varausaika-kentta">
                        <label for="uusiLoppu">Päättymisaika</label>
                        <select id="uusiLoppu" name="varausloppuaika" required disabled>
                            <option value="">Valitse päättymisaika</option>
                            <?php for ($minuutit = 6 * 60 + 30; $minuutit <= 20 * 60; $minuutit += 30):
                                $aika = sprintf("%02d:%02d", intdiv($minuutit, 60), $minuutit % 60);
                            ?>
                                <option value="<?= $aika ?>"><?= $aika ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <button id="varaaPaivat" type="submit" disabled>Varaa päivät</button>
                <p id="varausTila" role="status" aria-live="polite"></p>
            </form>

            <div id="huone_muu">
                
                <div id="huone_ajat"><p>Aikadial</p>
            
                     
            
                </div>

            </div>

            <div id="lisäys_lomake">
                
            </div>

            <script src="ajat.js">

                
            </script>
            <script>
                const varauslomake = document.getElementById("varauslomake");
                const varauspaivaKentta = document.getElementById("varauspaiva");
                const aloitusaika = document.getElementById("uusiAlku");
                const paattymisaika = document.getElementById("uusiLoppu");
                const varaaPaivatNappi = document.getElementById("varaaPaivat");
                const varausTila = document.getElementById("varausTila");

                function aikaMinuutteina(aika) {
                    const [tunnit, minuutit] = aika.split(":").map(Number);
                    return tunnit * 60 + minuutit;
                }

                function aikaMerkkijonoksi(minuutit) {
                    const tunnit = String(Math.floor(minuutit / 60)).padStart(2, "0");
                    const minuutitOsuus = String(minuutit % 60).padStart(2, "0");
                    return `${tunnit}:${minuutitOsuus}`;
                }

                function paivitaPaattumisajat() {
                    const alku = aloitusaika.value;
                    paattymisaika.replaceChildren(
                        new Option("Valitse päättymisaika", "")
                    );

                    if (!alku) {
                        paattymisaika.disabled = true;
                        varaaPaivatNappi.disabled = true;
                        return;
                    }

                    const alkuMinuutteina = aikaMinuutteina(alku);
                    for (let minuutit = alkuMinuutteina + 30; minuutit <= 20 * 60; minuutit += 30) {
                        const aika = aikaMerkkijonoksi(minuutit);
                        paattymisaika.add(new Option(aika, aika));
                    }

                    paattymisaika.disabled = false;
                    varaaPaivatNappi.disabled = true;
                }

                aloitusaika.addEventListener("change", paivitaPaattumisajat);
                paattymisaika.addEventListener("change", () => {
                    varaaPaivatNappi.disabled =
                        !varauspaivaKentta.value || !paattymisaika.value;
                });

                document.addEventListener("click", tapahtuma => {
                    if (!tapahtuma.target.closest("#kalenteri button")) {
                        return;
                    }

                    setTimeout(() => {
                        if (typeof valinta === "undefined" || !valinta) {
                            return;
                        }

                        const vuosi = valinta.getFullYear();
                        const kuukausi = String(valinta.getMonth() + 1).padStart(2, "0");
                        const paiva = String(valinta.getDate()).padStart(2, "0");
                        varauspaivaKentta.value = `${vuosi}-${kuukausi}-${paiva}`;
                        aloitusaika.disabled = false;
                        aloitusaika.value = "";
                        paivitaPaattumisajat();
                        varausTila.textContent = "";
                    }, 0);
                }, true);

                varauslomake.addEventListener("submit", async tapahtuma => {
                    tapahtuma.preventDefault();

                    if (!varauspaivaKentta.value || !aloitusaika.value || !paattymisaika.value) {
                        varausTila.textContent = "Valitse ensin päivä, alkamis- ja päättymisaika.";
                        return;
                    }

                    varaaPaivatNappi.disabled = true;
                    varausTila.textContent = "Tallennetaan varausta...";

                    try {
                        const vastaus = await fetch("api.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({
                                tarkoitus: "varausLisays",
                                huone: new URLSearchParams(location.search).get("id"),
                                varauspaiva: varauspaivaKentta.value,
                                varausaika: aloitusaika.value,
                                varausloppuaika: paattymisaika.value
                            })
                        });
                        const data = await vastaus.json();

                        if (!vastaus.ok) {
                            throw new Error(data.message || "Varauksen tallennus epäonnistui.");
                        }

                        varausTila.textContent = data.message || "Varaus tallennettiin.";
                        aloitusaika.value = "";
                        paivitaPaattumisajat();
                    } catch (virhe) {
                        varausTila.textContent = virhe.message;
                        varaaPaivatNappi.disabled = false;
                    }
                });
            </script>
        </div>
    </body>
</html>