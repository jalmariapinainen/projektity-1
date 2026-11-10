<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tyyli.css">
    <title>Projekti</title>
</head>
<body>
<header id="otsikko" class="header">
    <h1>Huoneen varaus järjestelmä</h1>

</header>
<nav id="nav" class="nav">

        <a class="varaa" id="varaa" href="">Varaa huone</a>
        <?php //Tarkistaa jos käyttäjä on kirjautunut ?>
        <?php if (isset($_GET['user'])): ?>
            <span class="kayttaja">Käyttäjä: <?php echo htmlspecialchars($_GET['user'], ENT_QUOTES, 'UTF-8'); ?></span>
            <a class="ulos" id="ulos" href="logout.php">Kirjaudu ulos</a>
        <?php else: ?>
            <a class="kirjaudu" id="kirjaudu" href="kirjautuminen.html">Kirjaudu sisään</a>
            <a id="rekisteroidy" class="rekisteroidy" href="kirjautuminen.html">Rekisteröidy</a>
        <?php endif; ?>
    
</nav>

<h3>Tervetuloa huoneiden varausjärjestelmään. Tämä sivu näyttää huoneita ja niiden varaustilannetta. Jos olet uusi käyttäjä, käy rekisteröitymässä. Jos sinulla on jo käyttäjä, kirjaudu sisään.</h3>
<?php
// Tarkistaa jos käyttäjä on kirjautunut.
if (isset($_GET['user'])) {
    $user = $_GET['user'];
    echo "<h3>Olet kirjautunut sisään, tervetuloa  $user!</h3>";
}
    else{
    echo "<h3>Et ole kirjautunut sisään, käy kirjautumassa tai jos et ole käyttäjä, käy rekisteröitymässä.</h3>";
    }

?>

<table>
<h2>Huoneiden varaustilanne:</h2>
<ul id="huoneet"></ul>
<script src="huoneet.js"></script>
</table>


</body>
</html>