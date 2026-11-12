
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tyyli1.css">
    <title>Projekti</title>
</head>
<body>

<?php
include "header.php";
?>
<div id="kaikki">
    <header id="otsikko" class="header">
        <h1>Huoneen varaus järjestelmä</h1>

    </header>

    <div id="Huoneiden-haku">
        
    </div>
    <p id="kirjautumistarkistus"></p>

    <div id="haetuthuoneet">
        <input type="search" id="huone-haku-kenttä" placeholder="Hae tunnuksella"> <button onclick="huonehaku()">Hae</button>
        <ul id="haku-huoneet"></ul>
    </div>
    <hr>
    <div id="huonenakyma">
        <ul id="huoneet"></ul>
    </div>
    <script src="huoneet.js"></script>


</div>
</body>
</html>