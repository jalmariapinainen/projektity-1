let aika = new Date();
let valinta = new Date();
let valintapaiva = valinta.toLocaleDateString("fi-FI");

let valittu = 0;

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
            valittu = 0;
            valinta = new Date(v, k, p);
            valintapaiva = valinta.toLocaleDateString("fi-FI");
            document.getElementById("valittu").textContent =
                valintapaiva;
            document.getElementById("valittu_paivateksti").textContent =
                "Valittu päivä: " + valintapaiva;
            kalenteri();
        };

        kalenteriElementti.appendChild(b);
    }

    document.getElementById("valittu").textContent = valintapaiva;
    document.getElementById("valittu_paivateksti").textContent =
        "Valittu päivä: " + valintapaiva;

    nayta();
}

function vaihda(suunta) {
    aika.setMonth(aika.getMonth() + suunta);
    valittu = 0;
    kalenteri();
}

kalenteri();




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






//=================================
//Ajanvarauksen nappien lisääminen
//=================================

async function nayta() {
    let loppuGridRow = 0;
    //REST APIIN yhdistys
    const huoneid = $_GET["id"]; //haetaan nykyinen get ja laitetaan se api hakuun
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
    paikka.style.gridTemplateRows = "repeat(" + rowCount + ")"; //Luo grid näkymän, johon tulee ajat ja varaukset



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


        aikakortti.classList.add(gridrow.toString());


        //
        //Laittaa tieynlaisen kortin lisääminen ajanvarauksille ja tyhjille napeille
        //
        if (Array.isArray(ajat)) {
            ajat.forEach(element => {

                const varauskortti = document.createElement("button");
                varauskortti.classList.add(gridrow.toString());
                varauskortti.addEventListener("click", () => {
                    ajanvalinta(varauskortti, aikakortti, nykyinenRivi, aika);
                });
                aikakortti.addEventListener("click", () => {
                    ajanvalinta(varauskortti, aikakortti, nykyinenRivi, aika);
                });
                if (valinta && paivamuutto(element.varauspaiva) && muunnaKellonaika(element.varausaika) == aika) {
                    varauskortti.classList.add("varattu");
                    const varauspara = document.createElement("p");

                    varauspara.textContent = element.kayttajatunnus

                    loppuGridRow = ajanvarauksenSuuruus(element.varausaika, element.varausloppuaika, gridrow)

                    varauskortti.style.gridRow = gridrow + " / " + loppuGridRow;
                    varauskortti.appendChild(varauspara);
                } else if (gridrow >= loppuGridRow) {
                    varauskortti.classList.add("empty-cell");
                    varauskortti.style.gridRow = gridrow;
                } else {

                    varauskortti.classList.add("true-tyhjä");
                    varauskortti.style.gridRow = gridrow;

                }

                paikka.appendChild(aikakortti);
                paikka.appendChild(varauskortti);

            });
        } else {

        }

    });


    //========================
    //Funktioita ajoille
    //========================

    function muunnaKellonaika(aika) { //muuttaa kellonajan tietokannassa, samanlaiseksi kuin on kellonajat arrayssa
        const [tunnit, minuutit] = aika.split(":");

        return Number(tunnit) + ":" + minuutit;
    }

    function paivamuutto(paiva) {//muuttaa päivän tietokannassa, samanlaiseksi kuin on valittu aika kalenterissa
        const vuosi = valinta.getFullYear();
        const kuukausi = String(valinta.getMonth() + 1).padStart(2, "0");
        const paivanumero = String(valinta.getDate()).padStart(2, "0");

        const paivastring = vuosi + "-" + kuukausi + "-" + paivanumero;

        return paivastring == paiva;
    }

    function ajanvarauksenSuuruus(alku, loppu, alkuRow) { //tähän menee apin palautuksen aloitus ja lopetusaika, jotta saadaan varatun ajan divin pituus.
        const minuutteina = aika => {
            const [tunnit, minuutit] = aika.split(":").map(Number);
            return tunnit * 60 + minuutit;
        };

        const kestoRiveina = (minuutteina(loppu) - minuutteina(alku)) / 30;
        return alkuRow + kestoRiveina + 1;
    }
}

nayta().catch(error => {
    console.error(error);
    document.querySelector("#huone_ajat").textContent =
        `Varauksien haku epäonnistui: ${error.message}`;
});


//=================================
//ajanvalinta varauksen lisäämiseen
//=================================

let primarygridrow = 0;
let secondarygridrow = 0;

let primaryelements = [];

function ajanvalinta(tyhja, kellokortti, gridRow, aika) {
    if (tyhja.classList.contains("varattu") ||
        tyhja.classList.contains("true-tyhjä")) {
        return;
    }


    if (tyhja.dataset.selected === "true") {

        valittu = valittu - 1;
        tyhja.dataset.selected = "false";
        tyhja.style.backgroundColor = "";
        kellokortti.style.backgroundColor = "";
        document.querySelectorAll(".ajanvalinta-vali-naytto").forEach(element => {
            element.classList.remove("ajanvalinta-vali-naytto");
        });

    } else if (valittu < 2) {
        if (valittu === 0) {
            primarygridrow = gridRow;

            tyhja.style.backgroundColor = "rgb(17, 0, 255)";
            kellokortti.style.backgroundColor = "rgb(17, 0, 255)";

            primaryelements = [tyhja, kellokortti];

            valittu++;
            tyhja.dataset.selected = "true";
        } else if (valittu === 1) {
            if (onkoValissaVarattu(primarygridrow, gridRow)) {
                alert("Et voi luoda varausta toisen varauksen päälle");
                return;
            }
            if (gridRow < primarygridrow) {

                //unset last primary elements
                primaryelements[0].style.backgroundColor = "rgba(126, 186, 255, 0.69)";
                primaryelements[1].style.backgroundColor = "rgba(126, 186, 255, 0.69)";
                primaryelements[0].dataset.selected = "true";

                //set new primary elements
                tyhja.style.backgroundColor = "rgb(17, 0, 255)";
                kellokortti.style.backgroundColor = "rgb(17, 0, 255)";

                Valimaalaus(primarygridrow, gridRow);

                tyhja.dataset.selected = "true";
                valittu++;
                return;
            }
            secondarygridrow = gridRow;

            tyhja.style.backgroundColor = "rgba(126, 186, 255, 0.69)";
            kellokortti.style.backgroundColor = "rgba(126, 186, 255, 0.69)";

            Valimaalaus(primarygridrow, gridRow);

            valittu++;
            tyhja.dataset.selected = "true";
        }
    }

}

function onkoValissaVarattu(a, b) {
    const alku = Math.min(a, b);
    const loppu = Math.max(a, b);

    for (let rivi = alku; rivi <= loppu; rivi++) {
        const solut = document.querySelectorAll(`[class~="${rivi}"]`);

        for (const solu of solut) {
            if (solu.classList.contains("varattu") ||
                solu.classList.contains("true-tyhjä")) {
                return true;
            }
        }
    }

    return false;
}

function Valimaalaus(a, b) {
    const alku = Math.min(a, b);
    const loppu = Math.max(a, b);

    for (let rivi = alku; rivi <= loppu; rivi++) {
        const solut = document.querySelectorAll(`[class~="${rivi}"]`);

        for (const solu of solut) {
            if (solu.classList.contains("varattu") ||
                solu.classList.contains("true-tyhjä")) {
                return;
            }
            else {
                solu.classList.add("ajanvalinta-vali-naytto");
            }
        }
    }

    return;
}