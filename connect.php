<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "notes_app";
$dbport = 3306;

$conn = new mysqli($dbhost,$dbuser,$dbpass,$dbname,$dbport);
if($conn->error){
    echo "errore connessione";
}