<?php
function connectBdd() {
    $dsn = 'mysql:dbname=formulairev0;host=127.0.0.1';
    $user = 'slam2023';
    $password = 'bastienmonier1701';

    $dbh = new PDO($dsn, $user, $password);
    return $dbh;
}


$bdd = connectBdd();

if ($bdd === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


