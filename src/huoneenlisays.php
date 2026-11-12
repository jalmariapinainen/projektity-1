<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Huoneiden hallinta</title>
    <link rel="stylesheet" href="huoneenlisays.css">
    
    
</head>

<body>
    <?php
    include "header.php";
?>

<div class="kaikkiKaikkenaan">
    <br>
<br>
<div id="huoneHallintaRakenne">

<div class="huoneidenlisaysLomake">

    <h1>Lisää huone</h1>
    <br>
    <hr>
    <br>
    <label for="tunnus">Huoneen tunnus:</label>
    <input type="text" id="tunnus" maxlength="25" placeholder="Huone 101">
    <br>
    <label for="sijainti">Sijainti:</label>
    <input type="text" id="sijainti" maxlength="50" placeholder="1. kerros, Voima">
    <br>
    <label for="pintaAla">Pinta-Ala:</label>
    <input type="number" id="pintaAla" max="999" step="1" min="1" placeholder="32 m²">
    <br>
    <br>
    <button onclick="huoneenLisays()">Lisää</button>
    <p id="message"></p>
</div>

<div class="huoneidenLista">
    <h1>Huoneet:</h1>
    <div id="huoneet"></div>

</div>
</div>
<script src="huoneenlisays.js"></script>
</div>
</body>
</html>