<?php
 // include "jalmarik.html";



?>

<!DOCTYPE html>
<html>
    <head>
        <title>Huoneen tieto</title>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id="huone_rakenne">

            <div>
                <div id="huone_logo">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS5mt6J6WnLk-Byyoxjgp7_k37QTkj_dOaoX2cRZkobCWIjt5Iw80j3IE4&s=10" alt="Conferance room logo">
                </div>
                <div id="huone_kalenteri"><?php
                    include "jalmarik.html";
                ?></div>

            </div>

            <div id="huone_muu">

                <div id="huone_otsikko"><p>Otsikko</p></div>
<br>
<br>
                <div id="huone_ajat"><p>Aikadial</p></div>

            </div>


        </div>
    </body>
</html>