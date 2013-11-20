<?php
   $usr = $_SESSION['email'];
   $idpost = $_GET['postid'];
   $conn = db_connect();

if(isset($_POST['eliminapost'])){
    $query_del= "DELETE FROM post WHERE idpost = '$idpost' AND utente = '$usr' ";
    $result_del = pg_query($conn,$query_del);
    header("Location: home.php?action=bacheca");
}


if(isset($_POST['aggiungitag']) && isset($_POST['amici'])){
    $taggato = $_POST['amici'];
    $conn = db_connect();
    $query = "INSERT INTO tag VALUES ('$usr','$idpost', '$taggato')";
    $result= pg_query($conn, $query);   
}

if(isset($_POST['eliminatag']) && isset($_POST['amicitag'])){         
    $conn = db_connect();
    $taggato = $_POST['amicitag'];
    $query = "DELETE FROM tag WHERE taggante = '$usr' AND taggato='$taggato' AND idpost = '$idpost' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);  
}

if(isset($_POST['eliminatagricevuto'])){
    $conn = db_connect();
    $taggante = $_GET['tgnt'];
    $query = "DELETE FROM tag WHERE taggante = '$taggante' AND taggato='$usr' AND idpost = '$idpost' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    header("Location:home.php?action=bacheca");
}

if(isset($_POST['salvatag'])){
    header("Location:home.php?action=bacheca");
}

    # Query per la creazione del menu a tendina (inserisci o elmina tag solo per gli amici) #
    
    // Selezione amici
    $query_amici="SELECT invitato AS amico, stato, nome, cognome 
                  FROM amicizia JOIN profilo ON invitato = utente
                  WHERE richiedente = '$usr'  AND stato = 's'
                  UNION
                  SELECT richiedente AS amico, stato, nome, cognome
                  FROM amicizia JOIN profilo ON richiedente = utente
                  WHERE invitato = '$usr' AND stato = 's'
                  ORDER BY amico";
    $result_amici=pg_query($conn,$query_amici);
    
    // Selezione amici taggati nella nota
    $query_amicitag="SELECT taggato FROM tag WHERE taggante = '$usr' AND idpost= '$idpost'"; 
    $result_amicitag=pg_query($conn,$query_amicitag);
    
    if(pg_numrows($result_amici) == 0) echo "<div class = error> Attenzione: <br/> <br/> devi avere degli amici per poterli taggare. </div>";
    else{
?>
     <form method="post">
       <label for="tag">Modifica tag:</label><br/><br/>
       <p> Elimina un utente taggato nella nota o aggiungi un nuovo tag.<br> Salva hai finito. </p>
       <select name="amicitag">
	 <?php
	 while($row_amicitag= pg_fetch_array($result_amicitag)) {
          print "<option>".$row_amicitag[0]."</option>";
	 }
         pg_result_seek($result_amicitag,0);
	 ?>
       </select>
       <input type='submit' name='eliminatag' value='Elimina' /><br /><br />
       <select name="amici">
	 <?php
         $inserisci = true;
	 while($row_amici= pg_fetch_array($result_amici)) {
	  $i++;
            while($row_amicitag= pg_fetch_array($result_amicitag)){
               if ($row_amici[0] == $row_amicitag[0])
                  $inserisci = false;
            }
         if($inserisci == true) print "<option> ".$row_amici[0]." </option>";
               $inserisci = true;
               pg_result_seek($result_amicitag,0);
	 }
         pg_result_seek($result_amici,0);
	 ?>
       </select>
       <input type='submit' name='aggiungitag' value='Aggiungi' /><br /><br />
       <input type='submit' name='salvatag' value='Salva' /><br />
     </form>
     
<?php
}
?>
        
