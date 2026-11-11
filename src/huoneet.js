//////////////////////////////////////////////////////////////////////
//Kaikki huoneet tuleva tulostettua elementtiin, jossa id on #huoneet
//////////////////////////////////////////////////////////////////////
//juuri nyt ilmoitan taulu li elementillä, mutta voi vaihtaa sen rivillä 19

async function nayta() {
    const vastaus = await fetch("api.php?tarkoitus=huoneNakymat");
    const listapaikka = document.querySelector("#huoneet");

    if (!vastaus.ok) {
        throw new Error(`API-virhe: ${vastaus.status}`);
    }
    const huoneet = await vastaus.json();


    listapaikka.replaceChildren();

    for (const huone of huoneet) {
        const kohta = document.createElement("li");
        const linkki = document.createElement("a");
        const kortti = document.createElement("div");
        const huonetunnus = document.createElement("div");
        const huonetieto = document.createElement("div");
        const huonetietosijainti = document.createElement("p");
        const huonetietopintaAla = document.createElement("span");
        const huonetietovali = document.createElement("hr");

        linkki.href = `huone.php?id=${encodeURIComponent(huone.huone_id)}`;
        linkki.classList.add("huoneKorttiLinkki");
        kortti.classList.add("huoneKortti");
        huonetunnus.classList.add("huoneen_tunnus");
        huonetieto.classList.add("huoneen_tieto");

        huonetunnus.textContent = huone.tunnus;
        huonetietosijainti.textContent = huone.sijainti;
        huonetietopintaAla.textContent = `${huone.pintaAla} m²`;

        huonetieto.appendChild(huonetietosijainti);
        huonetieto.appendChild(huonetietopintaAla);
        kortti.appendChild(huonetunnus);
        kortti.appendChild(huonetietovali);
        kortti.appendChild(huonetieto);

        linkki.appendChild(kortti);
        kohta.appendChild(linkki);
        listapaikka.appendChild(kohta);

    }

}


nayta().catch(error => {
    console.error(error);
    document.querySelector("#huoneet").textContent =
        `Huoneiden haku epäonnistui: ${error.message}`;
});

async function kirjautumisnaytto(){
    const kayttajatunnuspaikka = document.getElementById("kirjautumistarkistus");

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
        kayttajatunnuspaikka.textContent =
            `Kirjautunut käyttäjä: ${kayttaja.kayttajatunnus}`;
    } catch (error) {
        console.error(error);
        kayttajatunnuspaikka.textContent =
            "Kirjautumistietojen haku epäonnistui.";
    }
}

kirjautumisnaytto();

async function huonehaku() {
    const hakukentta = document.getElementById("huone-haku-kenttä");
    const hakuhuoneet = document.getElementById("haku-huoneet");

    try {
        const hakuparametrit = new URLSearchParams({
            tarkoitus: "Huoneen_haku",
            haku: hakukentta.value.trim()
        });
        const vastaus = await fetch(`api.php?${hakuparametrit}`);

        if (!vastaus.ok) {
            throw new Error(`API-virhe: ${vastaus.status}`);
        }

        const huoneet = await vastaus.json();
        hakuhuoneet.replaceChildren();

        if (huoneet.length === 0) {
            hakuhuoneet.textContent = "Hakuehdoilla ei löytynyt huoneita.";
            return;
        }

        for (const huone of huoneet) {
        const kohta = document.createElement("li");
        const linkki = document.createElement("a");
        const kortti = document.createElement("div");
        const huonetunnus = document.createElement("div");
        const huonetieto = document.createElement("div");
        const huonetietosijainti = document.createElement("p");
        const huonetietopintaAla = document.createElement("span");
        const huonetietovali = document.createElement("hr");

        linkki.href = `huone.php?id=${encodeURIComponent(huone.huone_id)}`;
        linkki.classList.add("huoneKorttiLinkki");
        kortti.classList.add("huoneKortti");
        huonetunnus.classList.add("huoneen_tunnus");
        huonetieto.classList.add("huoneen_tieto");

        huonetunnus.textContent = huone.tunnus;
        huonetietosijainti.textContent = huone.sijainti;
        huonetietopintaAla.textContent = `${huone.pintaAla} m²`;

        huonetieto.appendChild(huonetietosijainti);
        huonetieto.appendChild(huonetietopintaAla);
        kortti.appendChild(huonetunnus);
        kortti.appendChild(huonetietovali);
        kortti.appendChild(huonetieto);

        linkki.appendChild(kortti);
        kohta.appendChild(linkki);
        hakuhuoneet.appendChild(kohta);

    }
    } catch (error) {
        console.error(error);
        hakuhuoneet.textContent =
            `Huoneiden haku epäonnistui: ${error.message}`;
    }
}
