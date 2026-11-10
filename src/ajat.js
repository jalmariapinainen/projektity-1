let aika = new Date();
let valinta = null;
let valintapaiva = "";

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
        b.textContent = p;

        if (valinta && valinta.getFullYear() === v &&
            valinta.getMonth() === k && valinta.getDate() === p)
            b.className = "valittu";

        b.onclick = () => {
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
    nayta();
}

function vaihda(suunta) {
    aika.setMonth(aika.getMonth() + suunta);
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

async function nayta() {
    const huoneid = $_GET["id"];
    const hakuparametrit = new URLSearchParams({
        tarkoitus: "Huoneen_ajat",
        huone_id: huoneid
    });
    const vastaus = await fetch(`api.php?${hakuparametrit}`);

    const paikka = document.querySelector("#huone_ajat"); //Tähän tulee kaikki ajat

    if (!vastaus.ok) {//virheen tarkistus
        throw new Error(`API-virhe: ${vastaus.status}`);
    }

    const ajat = await vastaus.json();

    paikka.replaceChildren();

    for (let i = 12; i < 41; i++) {

        const aikakortti = document.createElement("div");
        const aikateksti = document.createElement("p");



        const kello = aika_muutto(i);

        if (Array.isArray(ajat)) {
            ajat.forEach(element => {
                if (valinta && paivaSQL(valinta) === element.varauspaiva && aikacheck(element, kello)) {

                    aikateksti.textContent = kello + " | " + element.kayttajatunnus;
                    aikakortti.classList.add("varattu");
                } else {
                    aikateksti.textContent = kello;
                }
            });
        } else {
            aikateksti.textContent = kello;
        }

        aikakortti.classList.add("aikakortti");



        aikakortti.appendChild(aikateksti);
        paikka.appendChild(aikakortti);
    }
}

nayta().catch(error => {
    console.error(error);
    document.querySelector("#huone_ajat").textContent =
        `Varauksien haku epäonnistui: ${error.message}`;
});


function paivaSQL(paiva) {
    const vuosi = paiva.getFullYear();
    const kuukausi = String(paiva.getMonth() + 1).padStart(2, "0");
    const paivanumero = String(paiva.getDate()).padStart(2, "0");

    return `${vuosi}-${kuukausi}-${paivanumero}`;
}

function aikacheck(array, kello) {
    function minuutteina(aika) {
        const [tunnit, minuutit] = aika.split(":").map(Number);
        return tunnit * 60 + minuutit;
    }

    const kellonAika = minuutteina(kello);
    const varauksenAlku = minuutteina(array.varausaika);
    const varauksenLoppu = minuutteina(array.varausloppuaika);

    return kellonAika >= varauksenAlku && kellonAika <= varauksenLoppu;
}