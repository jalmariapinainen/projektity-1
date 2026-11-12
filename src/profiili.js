// API:n osoite.
// Profiili.html on projektisi juuressa ja api.php src-kansiossa.
const API_URL = "api.php";

const message = document.getElementById("message");
const form = document.getElementById("passwordForm");


// AVAA SALASANAN VAIHTO

document.getElementById("change").addEventListener("click", function() {

    form.style.display = "block";

    message.textContent = "";

});


// PERUUTA SALASANAN VAIHTO

document.getElementById("cancel").addEventListener("click", function() {

    form.style.display = "none";

    form.reset();

    message.textContent = "";

});


// TALLENNA UUSI SALASANA

form.addEventListener("submit", async function(event) {

    event.preventDefault();

    const nykyinen = document.getElementById("oldPassword").value;
    const uusi = document.getElementById("newPassword").value;
    const vahvista = document.getElementById("confirmPassword").value;

    if (uusi.length < 8) {

        message.style.color = "red";
        message.textContent = "Salasanan pitää olla vähintään 8 merkkiä.";

        return;
    }

    if (uusi !== vahvista) {

        message.style.color = "red";
        message.textContent = "Salasanat eivät täsmää!";

        return;
    }

    try {

        const response = await fetch(API_URL, {

            method: "POST",

            credentials: "same-origin",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({

                tarkoitus: "salasanaVaihto",

                nykyinenSalasana: nykyinen,

                uusiSalasana: uusi

            })

        });

        const data = await response.json();

        if (response.ok) {

            message.style.color = "green";

            message.textContent = data.message;

            form.reset();

            form.style.display = "none";

        } else {

            message.style.color = "red";

            message.textContent = data.message || "Salasanan vaihtaminen epäonnistui.";

        }

    } catch (error) {

        message.style.color = "red";

        message.textContent = "Yhteys APIin epäonnistui.";

        console.error(error);

    }

});


// KIRJAUDU ULOS

document.getElementById("logout").addEventListener("click", async function() {

    try {

        const response = await fetch(API_URL, {

            method: "POST",

            credentials: "same-origin",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({

                tarkoitus: "ulos"

            })

        });

        if (response.ok) {

            window.location.href = "src/kirjautuminen.php";

        } else {

            message.style.color = "red";
            message.textContent = "Uloskirjautuminen epäonnistui.";

        }

    } catch (error) {

        message.style.color = "red";
        message.textContent = "Yhteys APIin epäonnistui.";

        console.error(error);

    }

});

//========================
//Käyttäjätunnuksen näyttö
//========================

async function kirjautumisnaytto(){
    const kayttajatunnuspaikka = document.getElementById("profiilinimi");

    try {
        const vastaus = await fetch("api.php?tarkoitus=omaKayttaja");

        if (vastaus.status === 401) {
            kayttajatunnuspaikka.textContent = "Et ole kirjautunut sisään.";
            return;
        }
        if (!vastaus.ok) {
            throw new Error(`API-virhe: ${vastaus.status}`);
        }

        const kayttaja = await vastaus.json();
        kayttajatunnuspaikka.textContent = kayttaja.kayttajatunnus;
    } catch (error) {
        console.error(error);
        kayttajatunnuspaikka.textContent =
            "Kirjautumistietojen haku epäonnistui.";
    }
}
kirjautumisnaytto();

//=====================
//Ajanvarauksien näyttö
//=====================

async function AjanvarauksienNaytto() {
    //mihin kaikki laitetaan
    const main = document.getElementById("varatut_ajat");

    //otetaan käyttäjän varaukset
    const vastaus = await fetch("api.php?tarkoitus=kayttajan_ajat");

    if (!vastaus.ok) { //virhe tarkistus
        throw new Error(`API-virhe: ${vastaus.status}`);
    }
    const varaukset = await vastaus.json();

    main.replaceChildren();

    //jos ei ole varauksia
    if (varaukset.length === 0) {
        main.textContent = "Sinulla ei ole varattuja aikoja";
        return;
    }

    //luodaan jokaiselle varaukselle oma kortti, missä on sen tiedot ja muokkaus ja poisto mahdollisuudet
    for (const varaus of varaukset) {

        const divi = document.createElement("div");
        divi.classList.add("ajanvaraus");

        const varausId = document.createElement("input");
        varausId.type = "hidden";
        varausId.value = varaus.varaus_id;

        const huoneId = document.createElement("input");
        huoneId.type = "hidden";
        huoneId.value = varaus.huone_id;

        const huonetunnus = document.createElement("input");
        huonetunnus.type = "text";
        huonetunnus.value = varaus.tunnus;
        huonetunnus.readOnly = "true";

        const huonesijainti = document.createElement("input");
        huonesijainti.type = "text";
        huonesijainti.value = varaus.sijainti;
        huonesijainti.readOnly = "true";

        const huonepaiva = document.createElement("input");
        huonepaiva.type = "text";
        huonepaiva.value = varaus.varauspaiva;
        huonepaiva.placeholder = "2026-11-12";

        const huoneaika = document.createElement("input");
        huoneaika.type = "text";
        huoneaika.value = varaus.varausaika;
        huoneaika.placeholder = "8:00";

        const huoneloppuaika = document.createElement("input");
        huoneloppuaika.type = "text";
        huoneloppuaika.value = varaus.varausloppuaika;
        huoneloppuaika.placeholder = "20:00";

        const huonepaivitusnappi = document.createElement("button");
        huonepaivitusnappi.textContent = "Edit";
        huonepaivitusnappi.type = "button";
        huonepaivitusnappi.addEventListener("click", () => {
            paivitus(
                varausId.value,
                huoneId.value,
                huonepaiva.value,
                huoneaika.value,
                huoneloppuaika.value
            );
        });

        const huonepoistonappi = document.createElement("button");
        huonepoistonappi.textContent = "Poista";
        huonepoistonappi.type = "button";
        huonepoistonappi.classList.add("poisto-nappi")
        huonepoistonappi.addEventListener("click", () => {
            poisto(
                varausId.value
            );
        });

        divi.appendChild(varausId);
        divi.appendChild(huoneId);
        divi.appendChild(huonetunnus);
        divi.appendChild(huonesijainti);
        divi.appendChild(huonepaiva);
        divi.appendChild(huoneaika);
        divi.appendChild(huoneloppuaika);
        divi.appendChild(huonepaivitusnappi);
        divi.appendChild(huonepoistonappi);
        main.appendChild(divi);
        
    }
}
AjanvarauksienNaytto().catch(error => {
    console.error(error);
    document.querySelector("#huoneet").textContent =
        `Huoneiden haku epäonnistui: ${error.message}`;
});

async function paivitus(varausId, huoneId, paiva, alku, loppu) {
    try {
        if (!checkday(paiva)) {
            message.textContent = "Anna päivämäärä muodossa YYYY-MM-DD.";
            return;
        }

        const vastaus = await fetch(API_URL, {
            method: "PUT",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "editVaraus",
                varaus_id: varausId,
                huone: huoneId,
                varausPaiva: paiva,
                varausAika: alku,
                varausLoppu: loppu
            })
        });
        const data = await vastaus.json();

        if (!vastaus.ok) {
            throw new Error(data.message || `API-virhe: ${vastaus.status}`);
        }

        message.textContent = data.message;
        await AjanvarauksienNaytto();
    } catch (error) {
        console.error("Varauksen päivittäminen epäonnistui:", error);
        message.textContent = error.message || "Varauksen päivittäminen epäonnistui.";
    }
}

function checkday(day){
    //tarkistaa onko day ensin 4 numeroa ja 2 numero ja 2 numeroa "-" merkillä väleissä
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(day);
    if (!match) return false;

    const [, vuosi, kuukausi, paiva] = match;
    const date = new Date(Number(vuosi), Number(kuukausi) - 1, Number(paiva));

    //tarkistaa onko numerot oikeita aikoja, ei esim päivä 900
    return date.getFullYear() === Number(vuosi) &&
        date.getMonth() === Number(kuukausi) - 1 &&
        date.getDate() === Number(paiva);
}

async function poisto(varausId){
    try{
        //lähettää poiston tiedot
    const vastaus = await fetch(API_URL, {
            method: "DELETE",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "poistaVaraus",
                varaus_id: varausId
            })
        });
        //ottaa palautteen
        const data = await vastaus.json();

        //virheentarkistus
        if (!vastaus.ok) {
            throw new Error(data.message || `API-virhe: ${vastaus.status}`);
        }

        //tulostaa palautteen
        message.textContent = data.message;
        await AjanvarauksienNaytto();
    } catch (error) { //toinen virheentarkastus
        console.error("Varauksen poistaminen epäonnistui:", error);
        message.textContent = error.message || "Varauksen poistaminen epäonnistui.";
    }

}