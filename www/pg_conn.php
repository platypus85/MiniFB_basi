<?php

function db_connect(){
    
  $conn = pg_connect("host=localhost port=5432 dbname=miniFB user=postgres password=moody");
  if(!$conn){
    die('Connessione fallita! <br/>');
  }
  return $conn;
}

?>