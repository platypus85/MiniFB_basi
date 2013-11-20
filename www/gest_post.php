<?php
     $usr = $_SESSION['email'];
     $conn = db_connect();
     $idpost = $_GET['postid'];


if(isset($_POST['eliminapost'])){
    $query_del= "DELETE FROM post WHERE idpost = '$idpost' AND utente = '$usr' ";
    $result_del = pg_query($conn,$query_del);
    header("Location: home.php?action=bacheca");
}


if(isset($_POST['aggiungitag'])){
    $taggato = $_POST['amici'];
    $conn = db_connect();
    $query = "INSERT INTO tag VALUES ('$usr','$idpost', '$taggato')";
    $result= pg_query($conn, $query);
}

if(isset($_POST['eliminatag'])){         
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




    
    // query amici
    $query_amici="SELECT invitato AS amico, stato, nome, cognome 
                  FROM amicizia JOIN profilo ON invitato = utente
                  WHERE richiedente = '$usr'  AND stato = 's'
                  UNION
                  SELECT richiedente AS amico, stato, nome, cognome
                  FROM amicizia JOIN profilo ON richiedente = utente
                  WHERE invitato = '$usr' AND stato = 's'
                  ORDER BY stato";
    $result_amici=pg_query($conn,$query_amici);
    
    $query_amicitag="SELECT taggato FROM tag WHERE taggante = '$usr' AND idpost= '$idpost'"; //amici taggati nella nota
    $result_amicitag=pg_query($conn,$query_amicitag);

?>
        <form method="post">
            <label for="tag">Modifica tag:</label><br/><br/>
	    <p> Elimina un utente taggato nella nota o aggiungi un nuovo tag.
	    <br> Salva hai finito. </p>
	    <select name="amicitag">
	      <?php
	        while($row_amicitag= pg_fetch_array($result_amicitag)) {
                   print "<option>".$row_amicitag[0]."</option>";
	        }
                pg_result_seek($result_amicitag,0);
	      ?>
	    </select>
	    <input type='submit' name='eliminatag' value='Elimina'><br><br>
	    <select name="amici">
	      <?php
                $inserisci = true;
	        while($row_amici= pg_fetch_array($result_amici)) {
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
	    <input type='submit' name='aggiungitag' value='Aggiungi'></input><br /><br />
	    <input type='submit' name='salvatag' value='Salva'></input><br />
        </form>
        
