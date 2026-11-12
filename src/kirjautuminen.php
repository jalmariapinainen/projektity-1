
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Kirjautuminen</title>
<link rel="stylesheet" href="tyyli1.css">

</head>

<body>
<?php 
include "header.php";
?>
<div class="login">
    <h2>Kirjaudu</h2>

    <input id="user" type="text" placeholder="Käyttäjätunnus" class="rek_input">

    <input id="password" type="password" placeholder="Salasana" class="rek_input">

    

    <button id="login" class="rek_button">Kirjaudu sisään</button>
    

    <p id="message"></p>
    
</div>

<script>
const message = document.getElementById("message");

// REKISTERÖITYMINEN
document.getElementById("login").onclick = async () => {

    const kayttajatunnus =
        document.getElementById("user").value;

    const salasana =
        document.getElementById("password").value;


    try {
        const vastaus = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                tarkoitus: "kirjautuminen",
                kayttajatunnus: kayttajatunnus,
                salasana: salasana
                
            })
        });

        const data = await vastaus.json();

        if (!vastaus.ok) {
            message.style.color = "red";
            message.textContent = data.message || "Kirjautuminen epäonnistui.";
            return;
        }

        message.style.color = "green";
        message.textContent = data.message || "Kirjautuminen onnistui!";

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
