
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profiili</title>
    <link rel="stylesheet" href="profiili.css">
    
    
</head>

<body>
    <?php
    include "header.php";
?>
<div id="proffa">
<main class="profile-card">

    <div class="profile-avatar" aria-hidden="true">P</div>

    <p class="eyebrow">OMA TILI</p>

    <h1 id="profiilinimi">Profiili</h1>

    <p class="description">
        Hallitse tiliäsi ja sen asetuksia.
    </p>

    <div class="actions">

        <button id="change" type="button">
            Vaihda salasana
        </button>

        <button id="logout" type="button">
            Kirjaudu ulos
        </button>

    </div>

    <!-- Salasanan vaihtolomake -->

    <form id="passwordForm">

        <h2>Vaihda salasana</h2>

        <label for="oldPassword">Nykyinen salasana</label>
        <input
            type="password"
            id="oldPassword"
            placeholder="Nykyinen salasana"
            required
        >

        <label for="newPassword">Uusi salasana</label>
        <input
            type="password"
            id="newPassword"
            placeholder="Uusi salasana"
            minlength="8"
            required
        >

        <label for="confirmPassword">Vahvista uusi salasana</label>
        <input
            type="password"
            id="confirmPassword"
            placeholder="Vahvista uusi salasana"
            required
        >

        <button type="submit" id="save">
            Tallenna uusi salasana
        </button>

        <button type="button" id="cancel">
            Peruuta
        </button>

    </form>

    <p id="message" role="status"></p>

</main>
</div>


<div class="ajanvaraukset">

    <h1 class="middle-Text">Sinun tekemät varaukset</h1>
    <br>
    <hr>
    <br>
    <div id="varatut_ajat"></div>

</div>
<script src="profiili.js">



</script>

</body>
</html>
