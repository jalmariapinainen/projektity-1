let aika = new Date();
let valinta = new Date();
let valintapaiva = valinta.toLocaleDateString("fi-FI");
let valittujaAikoja = 0;
let primarygridrow = 0;
let secondarygridrow = 0;

function kalenteri() {
    let v = aika.getFullYear();
    let k = aika.getMonth();

    document.getElementById("kuukausi").textContent =
        aika.toLocaleDateString("fi-FI", {
            month: "long", year: "numeric"
        });

    let kalenteriElementti = document.getElementById("kalenteri");
    kalenteriElementti.innerHTML = "";

    ["Ma", "Ti", "Ke", "To", "Pe", "La", "Su"].forEach(p => {
        kalenteriElementti.innerHTML += `<div>${p}</div>`;
    });

    let alku = (new Date(v, k, 1).getDay() + 6) % 7;
    let maara = new Date(v, k + 1, 0).getDate();

    for (let i = 0; i < alku; i++)
        kalenteriElementti.innerHTML += "<div></div>";

    for (let p = 1; p <= maara; p++) {
        let b = document.createElement("button");
        b.classList.add("kalenterinappi");
        b.textContent = p;


        if (valinta && valinta.getFullYear() === v &&
            valinta.getMonth() === k && valinta.getDate() === p)
            b.classList.add("valittu");

        b.onclick = () => {
            tyhjennaAjanvalinta();
            valinta = new Date(v, k, p);
            valintapaiva = valinta.toLocaleDateString("fi-FI");
            document.getElementById("valittu").textContent =
                valintapaiva;
            document.getElementById("valittu_paivateksti").textContent =
                "Valittu päivä: " + valintapaiva;
                document.getElementById("valittu_paivateksti_form").textContent =
                "Valittu päivä: " + valintapaiva;
            kalenteri();
        };

        kalenteriElementti.appendChild(b);
    }

    document.getElementById("valittu").textContent = valintapaiva;
    document.getElementById("valittu_paivateksti").textContent =
        "Valittu päivä: " + valintapaiva;
    document.getElementById("valittu_paivateksti_form").textContent =
        "Varauksen päivä: " + valintapaiva;

    lataaAjat();
}

function vaihda(suunta) {
    aika.setMonth(aika.getMonth() + suunta);
    tyhjennaAjanvalinta();
    kalenteri();
}

//////////////////////////////////////////////////////////////////////////////////////
//code from https://www.ideasandpixels.com/articles/get-post-variables-with-javascript/
var $_GET = {};

document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
    function decode(s) {
        return decodeURIComponent(s.split("+").join(" "));
    }

    $_GET[decode(arguments[1])] = decode(arguments[2]);
});
///////////////////////////////////////////////////////////////////////////////////////

kalenteri();




//=======
//Otsikko
//=======
async function otsikko() {
    const huoneid = $_GET["id"]; //haetaan nykyinen get ja laitetaan se api hakuun
    const hakuparametrit = new URLSearchParams({
        tarkoitus: "Huoneen_tiedot",
        huone_id: huoneid
    });
    const vastaus = await fetch(`api.php?${hakuparametrit}`); //lähetetään vastaus

    if (!vastaus.ok) {//virheen tarkistus
        throw new Error(`API-virhe: ${vastaus.status}`);
    }

    const tiedot = await vastaus.json(); //otetaan vastaus

    const huone_tunnus = document.getElementById("Huonetunnus");
    huone_tunnus.textContent = tiedot.tunnus;

    const huone_sijainti = document.getElementById("Huonesijainti");
    huone_sijainti.textContent = tiedot.sijainti;
}



function aika_muutto(num) {
    let hours = 0;
    let minutes = 0;
    if (num % 2 == 0) {
        const tunti = num / 2;
        hours = tunti.toString();
    }
    else {
        hours = (num - 1) / 2;
        minutes = "30";
    }

    return hours + ":" + minutes;
}


//=================================
//Ajanvarauksen kellonajat
//=================================
const kellonajat = [];
for (let tunti = 6; tunti <= 20; tunti++) { //Luo taulun, missä on aikoja 6:00, 6:30, 7:00 ..... 20:00 asti. Helposti muokattava
    kellonajat.push(tunti + ":00");
    if (tunti < 20) {
        kellonajat.push(tunti + ":30");
    }
}
const rowCount = kellonajat.length + 1;




let huone_id = 0;

function muunnaKellonaikaTietokantaan(aika) {
    const [tunnit, minuutit] = aika.split(":");
    return `${tunnit.padStart(2, "0")}:${minuutit}:00`;
}

function minuutitKellonajasta(kellonaika) {
    const [tunnit, minuutit] = kellonaika.split(":").map(Number);
    return tunnit * 60 + minuutit;
}

function paivamuutto() {
    const vuosi = valinta.getFullYear();
    const kuukausi = String(valinta.getMonth() + 1).padStart(2, "0");
    const paivanumero = String(valinta.getDate()).padStart(2, "0");
    return `${vuosi}-${kuukausi}-${paivanumero}`;
}

//=================================
//Ajanvarauksen nappien lisääminen
//=================================

async function nayta() {
    //REST APIIN yhdistys
    huoneid = $_GET["id"]; //haetaan nykyinen get ja laitetaan se api hakuun
    const hakuparametrit = new URLSearchParams({
        tarkoitus: "Huoneen_ajat",
        huone_id: huoneid
    });
    const vastaus = await fetch(`api.php?${hakuparametrit}`); //lähetetään vastaus

    if (!vastaus.ok) {//virheen tarkistus
        throw new Error(`API-virhe: ${vastaus.status}`);
    }

    const ajat = await vastaus.json(); //otetaan vastaus

    //aikojen paikan kutsuminen
    const paikka = document.querySelector("#huone_ajat"); //Tähän tulee kaikki ajat
    paikka.replaceChildren();
    paikka.style.gridTemplateRows = "repeat("+kellonajat.length+")";



    kellonajat.forEach((aika, index) => {
        const nykyinenRivi = index + 1;
        const aikakortti = document.createElement("button");
        const aikakorttiteksti = document.createElement("p");

        aikakortti.classList.add("aikakortti");

        aikakorttiteksti.textContent = aika;

        aikakortti.appendChild(aikakorttiteksti);
        //tarkistetaan onko ajanvarausta tähän aikaan
        //paikka.innerHTML += "<div class='empty-cell' style='grid-row: "+gridrow+"; grid-column: 2;'></div>";
        const gridrow = index + 1;

        aikakortti.style.gridRow = gridrow;
        aikakortti.classList.add(gridrow.toString());


        const varauksetPaivalle = [];
        if (Array.isArray(ajat)) {
            ajat.forEach(element => {
                if (paivamuutto() === element.varauspaiva) {
                    varauksetPaivalle.push(element);
                }
            });
        }
        const varausAlkavaNyt = varauksetPaivalle.find(
            element => element.varausaika === muunnaKellonaikaTietokantaan(aika)
        );
        const minuutitNyt = minuutitKellonajasta(aika);
        const varausPaalleNyt = varauksetPaivalle.some(element =>
            minuutitKellonajasta(element.varausaika) <= minuutitNyt &&
            minuutitKellonajasta(element.varausloppuaika) > minuutitNyt
        );

        const varauskortti = document.createElement("button");
        varauskortti.classList.add(gridrow.toString());
        varauskortti.style.gridColumn = "2";

        if (varausAlkavaNyt) {
            varauskortti.classList.add("varattu");
            const varauspara = document.createElement("p");
            varauspara.textContent = varausAlkavaNyt.kayttajatunnus;
            const loppuGridRow = ajanvarauksenSuuruus(
                varausAlkavaNyt.varausaika,
                varausAlkavaNyt.varausloppuaika,
                gridrow
            );
            varauskortti.style.gridRow = gridrow + " / " + loppuGridRow;
            varauskortti.appendChild(varauspara);
        } else if (varausPaalleNyt) {
            varauskortti.classList.add("true-tyhjä");
            varauskortti.style.gridRow = gridrow;
        } else {
            varauskortti.classList.add("empty-cell");
            varauskortti.style.gridRow = gridrow;
        }

        varauskortti.addEventListener("click", () => {
            ajanvalinta(varauskortti, aikakortti, nykyinenRivi, aika);
        });
        aikakortti.addEventListener("click", () => {
            ajanvalinta(varauskortti, aikakortti, nykyinenRivi, aika);
        });

        paikka.appendChild(aikakortti);
        paikka.appendChild(varauskortti);

    });

}
    //========================
    //Funktioita ajoille
    //========================

    function ajanvarauksenSuuruus(alku, loppu, alkuRow) { //tähän menee apin palautuksen aloitus ja lopetusaika, jotta saadaan varatun ajan divin pituus.
        const minuutteina = aika => {
            const [tunnit, minuutit] = aika.split(":").map(Number);
            return tunnit * 60 + minuutit;
        };

        const kestoRiveina = (minuutteina(loppu) - minuutteina(alku)) / 30;
        return alkuRow + kestoRiveina;
    }


function lataaAjat() {
    nayta().catch(error => {
        console.error(error);
        document.querySelector("#huone_ajat").textContent =
            `Varauksien haku epäonnistui: ${error.message}`;
    });
}


//=================================
//ajanvalinta varauksen lisäämiseen
//=================================

function ajanvalinta(tyhja, kellokortti, gridRow, aika) {
    if (tyhja.classList.contains("varattu") ||
        tyhja.classList.contains("true-tyhjä")) {
        return;
    }

    if (tyhja.dataset.selected === "true") {
        tyhjennaAjanvalinta();
        return;
    }

    if (valittujaAikoja === 2) {
        tyhjennaAjanvalinta();
    }

    if (valittujaAikoja === 0) {
        primarygridrow = gridRow;
        merkitseValituksi([tyhja, kellokortti], true);
        document.getElementById("aloitusAika").value = aika;
        document.getElementById("lopetusAika").value = "";
        valittujaAikoja = 1;
        return;
    }

    if (onkoValissaVarattu(primarygridrow, gridRow)) {
        alert("Et voi luoda varausta toisen varauksen päälle");
        return;
    }
    if (gridRow === primarygridrow) {
        alert("Lopetusajan pitää olla eri kuin aloitusaika.");
        return;
    }

    const aloitusAika = document.getElementById("aloitusAika");
    const lopetusAika = document.getElementById("lopetusAika");

    if (gridRow < primarygridrow) {
        secondarygridrow = primarygridrow;
        primarygridrow = gridRow;
        lopetusAika.value = aloitusAika.value;
        aloitusAika.value = aika;
    } else {
        secondarygridrow = gridRow;
        lopetusAika.value = aika;
    }

    merkitseValituksi([tyhja, kellokortti], true);
    Valimaalaus(primarygridrow, secondarygridrow);
    valittujaAikoja = 2;
}

function merkitseValituksi(elementit, valittu) {
    for (const element of elementit) {
        element.dataset.selected = valittu ? "true" : "false";
        element.style.backgroundColor = valittu
            ? "rgb(17, 0, 255)"
            : "";
    }
}

function tyhjennaAjanvalinta() {
    for (const element of document.querySelectorAll('[data-selected="true"]')) {
        element.dataset.selected = "false";
        element.style.backgroundColor = "";
    }
    document.querySelectorAll(".ajanvalinta-vali-naytto").forEach(element => {
        element.classList.remove("ajanvalinta-vali-naytto");
    });

    document.getElementById("aloitusAika").value = "";
    document.getElementById("lopetusAika").value = "";
    valittujaAikoja = 0;
    primarygridrow = 0;
    secondarygridrow = 0;
}

function onkoValissaVarattu(a, b) {
    const alku = Math.min(a, b);
    const loppu = Math.max(a, b);

    for (let rivi = alku; rivi < loppu; rivi++) {
        const gridrivit = document.querySelectorAll(`[class~="${rivi}"]`);

        for (const rivi of gridrivit) {
            if (rivi.classList.contains("varattu") ||
                rivi.classList.contains("true-tyhjä")) {
                return true;
            }
        }
    }

    return false;
}

function Valimaalaus(a, b) {
    const alku = Math.min(a, b);
    const loppu = Math.max(a, b);

    for (let rivi = alku; rivi < loppu; rivi++) {
        const gridrivit = document.querySelectorAll(`[class~="${rivi}"]`);

        for (const rivi of gridrivit) {
            if (!rivi.classList.contains("varattu") &&
                !rivi.classList.contains("true-tyhjä")) {
                rivi.classList.add("ajanvalinta-vali-naytto");
            }
        }
    }

    return;
}


//============================
//Ajan lisäys funktio
//============================

async function ajanlisays(){
    //muuttujat
    const message = document.getElementById("huone-lisays-message");
    const alkuSyote = document.getElementById("aloitusAika").value.trim();
    const loppuSyote = document.getElementById("lopetusAika").value.trim();
    const huoneId = $_GET["id"];

    //tarkistetaan jos kentät on tyhjät
    if (!huoneId || !alkuSyote || !loppuSyote) {
        message.textContent = "Valitse huone sekä varauksen alku- ja loppuaika.";
        return;
    }

    //tarkistetaan kentän kelpoisuus
    const aikaMuoto = /^(?:[01]?\d|2[0-3]):[0-5]\d$/;
    if (!aikaMuoto.test(alkuSyote) || !aikaMuoto.test(loppuSyote)) {
        message.textContent = "Anna ajat muodossa esimerkiksi 8:00 tai 13:30.";
        return;
    }

    //muutetaan ajat tietokannan kelpoisiksi
    const alku = muunnaKellonaikaTietokantaan(alkuSyote);
    const loppu = muunnaKellonaikaTietokantaan(loppuSyote);
    const aikavali = /^(?:0[6-9]|1\d):(?:00|30):00$|^20:00:00$/;
    if (!aikavali.test(alku) || !aikavali.test(loppu)) {
        message.textContent = "Valitse ajat puolen tunnin välein väliltä 6:00–20:00.";
        return;
    }

    if (minuutitKellonajasta(alku) >= minuutitKellonajasta(loppu)) {
        message.textContent = "Loppuajan pitää olla aloitusajan jälkeen.";
        return;
    }

    //lähetetään asiat tietokantaan. Lisäksi on tarkistus ajanvarauksien päällekyyteen
    const varauspaiva = paivamuutto();
    try {
        const vastaus = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "varausLisays",
                huone: huoneId,
                varauspaiva,
                varausaika: alku,
                varausloppuaika: loppu
            })
        });
        const data = await vastaus.json();

        //virheen ilmoitus
        if (!vastaus.ok) {
            throw new Error(data.message || `API-virhe: ${vastaus.status}`);
        }

        //syötetään vastaus, jos lähetys meni läpi
        message.textContent = data.message;

        //resetoidaan forms kentät
        document.getElementById("aloitusAika").value = "";
        document.getElementById("lopetusAika").value = "";
        tyhjennaAjanvalinta();//tyhjentää aikataulun valinnat
        await nayta();//reloadaa aikataulun

        //virheen ilmoitus
    } catch (error) {
        console.error("Varauksen lisääminen epäonnistui:", error);
        message.textContent = error.message || "Varauksen lisääminen epäonnistui.";
    }
}


//===================================================
//Tarkistaa jos käyttäjä on kirjautunut
//Jos ei ole, niin ei anna tehdä varauksen lisäystä
//===================================================
async function kayttajatunnus(){
    const lomakevirhesyy = document.getElementById("lomakevirhesyy");
    const lisayslomake = document.getElementById("lisäys_lomake");
    const virhediv = document.getElementById("kirjautuminen_peite");
    try {
        const vastaus = await fetch("api.php?tarkoitus=omaKayttaja");

        if (vastaus.status === 401) {
            lisayslomake.style.filter = "blur(10px)";
            virhediv.removeAttribute('hidden');
            lomakevirhesyy.textContent = "Kirjaudu sisään tehdäksesi varauksen.";
            
            return;
        }
        if (!vastaus.ok) {
            throw new Error("API-virhe: "+vastaus.status);
        }

        const kayttaja = await vastaus.json();
        kirjautumisviesti.textContent = "Kirjautunut käyttäjä: "+kayttaja.kayttajatunnus;
    } catch (error) {
        console.error(error);
        lomakevirhesyy.textContent =
            "Kirjautumistietojen haku epäonnistui. Tarkista API.";
    }
}
kayttajatunnus();

