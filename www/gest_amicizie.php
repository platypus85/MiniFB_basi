<?php

require("pg_conn.php");
$conn = db_connect();

if(isset($_GET['ut_el'])){
  $u1 = $_GET['ut1'];
  $u2 = $_GET['ut_el'];
  $query = "SELECT elimina_amic('$u1','$u2')";
  $result = pg_query($conn, $query);
  header("Location: home.php?action=amici");
}

if(isset($_GET['ut_no'])){
  $u1 = $_GET['ut1'];
  $u2 = $_GET['ut_no'];
  $query = "SELECT gest_amicizia('$u1','$u2','n')";
  $result = pg_query($conn, $query);
  header("Location: home.php?action=amici");
}

if(isset($_GET['ut_si'])){
  $u1 = $_GET['ut1'];
  $u2 = $_GET['ut_si'];
  $query = "SELECT gest_amicizia('$u1','$u2', 's')";
  $result = pg_query($conn, $query);
  header("Location: home.php?action=amici");
}

if(isset($_GET['richiesta_no'])){
  $u1 = $_GET['ut1'];
  $u2 = $_GET['richiesta_no'];
  echo $u1.$u2;
  $query = "DELETE FROM amicizia WHERE (invitato='$u1' AND richiedente='$u2') OR (invitato='$u2' AND richiedente= '$u1')";
  $result = pg_query($conn, $query);
  header("Location: home.php?action=amici");
}

if(isset($_GET['amico'])){
  $u1 = $_GET['amico'];
  $u2 = $_GET['amico1'];
  $query = "SELECT richiesta('$u2','$u1')";
  $result = pg_query($conn, $query);
  header("Location: home.php?action=amici");
}

?>