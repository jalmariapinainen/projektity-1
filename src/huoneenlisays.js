async function nayta() {
    const vastaus = await fetch("api.php?tarkoitus=huoneNakymat");
    const listapaikka = document.querySelector("#huoneet");

    if (!vastaus.ok) {
        throw new Error(`API-virhe: ${vastaus.status}`);
    }
    const huoneet = await vastaus.json();


    listapaikka.replaceChildren();

    for (const huone of huoneet) {
        const kohta = document.createElement("div");
        kohta.classList.add("HuoneidenKortit");

        const linkki = document.createElement("a");

        const huonetunnus = document.createElement("input");
        huonetunnus.type = "text";
        huonetunnus.value = huone.tunnus;
        huonetunnus.placeholder = huone.tunnus;

        const huonetietosijainti = document.createElement("input");
        huonetietosijainti.type = "text";
        huonetietosijainti.value = huone.sijainti;
        huonetietosijainti.placeholder = huone.sijainti;

        const huonetietopintaAla = document.createElement("input");
        huonetietopintaAla.type = "number";
        huonetietopintaAla.value = huone.pintaAla;
        huonetietopintaAla.placeholder = huonetietopintaAla+"m²";

        const hr = document.createElement("hr");

        const huoneId = document.createElement("input");
        huoneId.type = "hidden";
        huoneId.value = huone.huone_id;

        const Huonepaivitysnappi = document.createElement("button");
        Huonepaivitysnappi.textContent = "Edit";
        Huonepaivitysnappi.type = "button";
        Huonepaivitysnappi.classList.add("huonePaivitysNappi");
        Huonepaivitysnappi.addEventListener("click", () => {
            paivitus(
                huoneId.value,
                huonetunnus.value,
                huonetietosijainti.value,
                huonetietopintaAla.value
            );
        });

        const Huonepoistonappi = document.createElement("button");
        Huonepoistonappi.textContent = "Poista";
        Huonepoistonappi.type = "button";
        Huonepoistonappi.classList.add("huonePoistoNappi");
        Huonepoistonappi.addEventListener("click", () => {
            poisto(
                huoneId.value,
            );
        });

        linkki.href = `huone.php?id=${encodeURIComponent(huone.huone_id)}`;
        linkki.textContent = "linkki huoneeseen";
        linkki.classList.add("huoneKorttiLinkki");
        


        
        
        kohta.appendChild(huonetunnus);
        kohta.appendChild(linkki);
        kohta.appendChild(huoneId);
        kohta.appendChild(hr);
        kohta.appendChild(huonetietosijainti);
        kohta.appendChild(huonetietopintaAla);
        kohta.appendChild(Huonepaivitysnappi);
        kohta.appendChild(Huonepoistonappi);
        
        
        listapaikka.appendChild(kohta);

    }

}


nayta().catch(error => {
    console.error(error);
    document.querySelector("#huoneet").textContent =
        `Huoneiden haku epäonnistui: ${error.message}`;
});


//====================
//Huoneiden lisääminen
//====================
const message = document.getElementById("message");

async function huoneenLisays(){
    //muuttujat
    const huoneTunnus = document.getElementById("tunnus").value.trim();
    const huoneSijainti = document.getElementById("sijainti").value.trim();
    const huonePintaAlaString = document.getElementById("pintaAla").value.trim();

    if (!checkInput(huoneTunnus, huoneSijainti, huonePintaAlaString)) {
        return;
    }

    const huonepintaAla = Number(huonePintaAlaString);

    //Huoneen luonti
    try {
        const vastaus = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "huoneLisäys",
                tunnus: huoneTunnus,
                sijainti: huoneSijainti,
                pintaAla: huonepintaAla
            })
        });

        const data = await vastaus.json();

        //Virheen tarkistus
        if (!vastaus.ok) {
            message.style.color = "red";
            message.textContent = data.message || "Huoneen lisääminen epäonnistui.";
            return;
        }

        //Onnistuneen luonnin viesti
        message.style.color = "green";
        message.textContent = data.message || "Rekisteröityminen onnistui!";

        //inputtien resetointi
        document.getElementById("tunnus").value = "";
        document.getElementById("sijainti").value = "";
        document.getElementById("pintaAla").value = "";

        //Päivitetään näkymä
        await nayta();

    } catch (error) { //Virheen tarkistus
        message.style.color = "red";
        message.textContent = "Yhteys API:in epäonnistui. Avaa sivu palvelimen kautta (esim. http://localhost:8080/kirjautuminen.html).";
        console.error(error);
    }
}


function checkInput(tunnus, sijainti, pintaAla){
    const pintaAlanum = Number(pintaAla);

    if (!tunnus || !sijainti || !pintaAla) {
        message.style.color = "red";
        message.textContent = "Täytä kaikki huoneen tiedot.";
        return false;
    }
    else if (!Number.isInteger(pintaAlanum) || pintaAlanum < 1 || pintaAlanum > 999){
        message.style.color = "red";
        message.textContent = "Pinta-alan pitää olla kokonaisluku väliltä 1–999.";
        return false;
    }else{
        return true;
    }
}

//======================
//Huoneiden muokkaaminen
//======================

async function paivitus(huoneId, huonetunnus, huonesijainti, huonepintaAla){
    try {
        const huonepintaAlastring = String(huonepintaAla);
        if (!checkInput(huonetunnus, huonesijainti, huonepintaAlastring)) {
            return;
        }

        const vastaus = await fetch("api.php", {
                method: "PUT",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    tarkoitus: "editHuone",
                    huoneId: huoneId,
                    huonetunnus: huonetunnus,
                    huonesijainti: huonesijainti,
                    huonepintaAla: huonepintaAla
                })
            });
            const data = await vastaus.json();

            if (!vastaus.ok) {
                throw new Error(data.message || `API-virhe: ${vastaus.status}`);
            }

            message.textContent = data.message;
            await nayta();
    } catch (error) {
        console.error("Huoneen päivittäminen epäonnistui:", error);
        message.textContent = error.message || "Huoneen päivittäminen epäonnistui.";
    }
}

async function poisto(huoneId){
    try{
        //lähettää poiston tiedot
    const vastaus = await fetch("api.php", {
            method: "DELETE",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "poistaHuone",
                huoneId: huoneId
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
        await nayta();
    } catch (error) { //toinen virheentarkastus
        console.error("Huoneen poistaminen epäonnistui:", error);
        message.textContent = error.message || "Huoneen poistaminen epäonnistui.";
    }
}