
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Rekisteröityminen</title>
<link rel="stylesheet" href="tyyli1.css">

</head>

<body>
<?php 
include "header.php";
?>

<div class="login">
    <h2>Rekisteröidy</h2>

    <input id="user" type="text" placeholder="Käyttäjätunnus" class="rek_input">
    <input id="email" type="email" placeholder="Sähköposti" class="rek_input">
    <input id="password" type="password" placeholder="Salasana" class="rek_input">
    <input id="phonenumber" type="text" placeholder="Puhelinnumero" class="rek_input">

    <button id="register"  class="rek_button">Rekisteröidy</button>
    

    <p id="message"></p>
</div>


<script>
const message = document.getElementById("message");

// REKISTERÖITYMINEN
document.getElementById("register").onclick = async () => {

    const kayttajatunnus =
        document.getElementById("user").value;

    const sahkoposti =
        document.getElementById("email").value;

    const salasana =
        document.getElementById("password").value;

    const puhelinnumero =
        document.getElementById("phonenumber").value;

    try {
        const vastaus = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "kayttajaLisays",
                kayttajatunnus: kayttajatunnus,
                sahkoposti: sahkoposti,
                salasana: salasana,
                puhelinnumero: puhelinnumero
            })
        });

        const data = await vastaus.json();

        if (!vastaus.ok) {
            message.style.color = "red";
            message.textContent = data.message || "Rekisteröityminen epäonnistui.";
            return;
        }

        message.style.color = "green";
        message.textContent = data.message || "Rekisteröityminen onnistui!";

        document.getElementById("password").value = "";

    } catch (error) {
        message.style.color = "red";
        message.textContent = "Yhteys API:in epäonnistui. Avaa sivu palvelimen kautta (esim. http://localhost:8080/kirjautuminen.html).";
        console.error(error);
    }
};
</script>


</body>
</html>
