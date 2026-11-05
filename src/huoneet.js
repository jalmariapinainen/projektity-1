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

        linkki.href = `huone.php?id=${encodeURIComponent(huone.huone_id)}`;
        kortti.classList.add("huoneKortti");
        kortti.textContent = huone.tunnus;

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