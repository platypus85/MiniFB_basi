<?php
    $usr = $_SESSION['email'];
    $idevento = $_GET['idevento'];
    $conn = db_connect();

    // controlla che l'utente loggato sia l'organizzatore dell'evento
    
    $controllo = "SELECT organizzatore FROM evento WHERE idevento = '$idevento'";
    $res_controllo = pg_query($conn, $controllo);
    $row_controllo = pg_fetch_array($res_controllo);
    if($row_controllo[0]==$usr){ //sei autorizzato fai tutto
        
    
   

    $msg = "Attenzione:<br/>";
    $nome = "";
    $tipo = "";
    $prov = "";
    $citta = "";
    $via = "";
    $data = "";
    
if(isset($_POST['salvaevento'])){
    
    $nome = htmlentities(trim($_POST['nome']));
    $tipo = $_POST['tipo'];
    $prov = $_POST['prov'];
    $citta = $_POST['citta'];
    $via = htmlentities(trim($_POST['via']));
    $data = htmlentities(trim($_POST['data']));
    
    
    
    $query_eventi = "SELECT count(*) FROM evento WHERE nome = '$nome' AND organizzatore = '$usr' AND data = '$data' AND luogo = get_idcitta('$citta','$prov') AND idevento!='$idevento'";
    $result_eventi = pg_query($conn, $query_eventi);
    $row_eventi=pg_fetch_array($result_eventi);
    
    if($row_eventi[0]!=0){
      $msg .= "<br> - Hai gi&agrave creato questo evento<br/>";
    }

    // Controllo compilazione form (esistenza citta)  
    $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta' AND provincia = '$prov'";
    $result_citta = pg_query($conn, $query_citta);
    $row_citta=pg_fetch_array($result_citta);
    if($row_citta[0]==0){
      $msg .= "<br> - La citt&agrave inserita non esiste <br/>";
    }
    
    if(strlen($nome)<4){
      $msg .= "<br> - Il nome dell'evento deve essere lungo almeno 4 caratteri <br/>";
    }
    
    if(strlen($via)<3){
      $msg .= "<br> - Il nome della via deve essere lungo almeno 3 caratteri <br/>";
    }

    
    // Form compilato correttamente
    if($msg == "Attenzione:<br/>"){
    $nome = pg_escape_string($nome);
    $via = pg_escape_string($via);

	
    $query = "UPDATE evento SET nome = '$nome', tipo= '$tipo', data='$data', via='$via', luogo=get_idcitta('$citta','$prov') WHERE idevento = '$idevento'";
    $result= pg_query($conn, $query);
    header("Location: home.php?action=eventi");
    }
}


    // Query creazione form
    $query_tipo = "SELECT DISTINCT tipo FROM evento ORDER BY tipo";
    $result_tipo = pg_query($conn, $query_tipo);
    $query_citta= "SELECT DISTINCT nome FROM citta ORDER BY nome;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia;";
    $result_prov=pg_query($conn, $query_prov);
    
    $query_eve="SELECT nome, organizzatore, tipo, data, via, get_nomecitta(luogo) AS citta, get_provcitta(luogo) AS prov FROM evento WHERE idevento = '$idevento';";
    $result_eve=pg_query($conn, $query_eve);
    $row_eve = pg_fetch_array($result_eve);
    
    
?>


    <h4> Modifica i dettagli dell'evento </h4>      
    <form method="POST" >
        <label for="nome">Nome evento:</label> 
        <input type="text" id="nome" name="nome"  maxlength="25" required="required" value='<?php print $row_eve['nome'] ?>' /><br />
        <label for="tipo">Tipo:</label>
        <select name="tipo">
          <?php
            while($row=pg_fetch_array($result_tipo)){
                if($row['tipo']==$row_eve['tipo'])
                    print"<option selected = 'selected' > ".$row['tipo']." </option>";
                else
                    print"<option> ".$row['tipo']." </option>";
            }
          ?>
        </select><br />
        <label for="prov"> Provincia:</label>
        <select name="prov">
          <?php
            while($row=pg_fetch_array($result_prov)) {
                if ($row['provincia']==$row_eve['prov'])
                    print "<option selected = 'selected' value= ".$row['provincia']." > ".strtoupper($row['provincia'])." </option>";
                else
                    print "<option value= ".$row['provincia']."> ".strtoupper($row['provincia'])." </option>";
            }
            pg_result_seek($result_prov,0);   
          ?>
        </select><br />
        <label for="citta"> Citta:</label>
        <select name="citta">
           <?php
            while($row=pg_fetch_array($result_citta)) {
                if ($row['nome']==$row_eve['citta'])
                    print "<option selected = 'selected' value= ".$row['nome']."> ".ucfirst($row['nome'])." </option>";
                else
                    print "<option value= ".$row['nome']."> ".ucfirst($row['nome'])." </option>";
            }
            pg_result_seek($result_citta,0);
          ?>
        </select><br />
        <label for="via">Via:</label> 
        <input type="text" id="nome" name="via"  maxlength="15" required="required" value='<?php print $row_eve['via'] ?>' /><br />
        <label for="data">Data:</label> 
        <input type="date" id="nome" name="data" required="required" value='<?php print $row_eve['data'] ?>' /> <br /><br />
        <?php if($msg!="Attenzione:<br/>") echo "<div class='error'>".$msg."</div>";"<br>" ?> <br />
        <input type="submit" value="Salva" align="right" name="salvaevento" ; /><br /><br /><br />
    </form>



<?php

if(isset($_POST['invita']) && isset($_POST['amici'])){
    $taggato = $_POST['amici'];
    $conn = db_connect();
    $query = "INSERT INTO tag VALUES ('$usr','$idpost', '$taggato')";
    $result= pg_query($conn, $query);   
}


// L'invitato accetta o rifiuta l'invito
if(isset($_POST['accettainvito'])){
    $query = "UPDATE invito SET stato ='s' WHERE invitato = '$usr' AND evento = '$idevento' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    header("Location:home.php?action=inviti");
}

if(isset($_POST['rifiutainvito'])){
    $query = "UPDATE invito SET stato ='n' WHERE invitato = '$usr' AND evento = '$idevento' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    header("Location:home.php?action=inviti");
}

// L'invitato elimina il rifiuto o l'accettazione di un invito precedentemente accettato/rifiutato
if(isset($_POST['eliminainvito'])){
    $query = "DELETE FROM invito WHERE invitato = '$usr' AND evento = '$idevento' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    header("Location:home.php?action=inviti");
}

if(isset($_POST['eliminarifiuto'])){
    $query = "DELETE FROM invito WHERE invitato = '$usr' AND evento = '$idevento' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    header("Location:home.php?action=inviti");
}




// Opzioni per il creatore dell'evento

if(isset($_POST['eliminainvitato']) && isset($_POST['invitato'])){         
    $invitato = $_POST['invitato'];
    $query = "DELETE FROM invito WHERE invitato = '$invitato' AND evento = '$idevento' ";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);  
}

if(isset($_POST['aggiungiinvito']) && isset($_POST['amico'])){         
    $amico = $_POST['amico'];
    $query = "INSERT INTO invito (invitato, evento, stato) VALUES ('$amico','$idevento', 'a')";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);  
}

if(isset($_POST['eliminaevento'])){
    $query_del= "DELETE FROM evento WHERE idevento = '$idevento'";
    // Si cancellano in automatico tutti gli inviti
    $result_del = pg_query($conn,$query_del);
    header("Location: home.php?action=eventi");
}






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
    $query_inviti="SELECT invitato FROM invito WHERE evento = '$idevento'"; 
    $result_inviti=pg_query($conn,$query_inviti);

?>


     <form method="post">
       <label for="tag">Modifica tag:</label><br/><br/>
       <p> Invita un amico all'evento o eliminalo dalla lista.<br> Salva hai quando hai finito. </p>
       <select name="invitato">
	 <?php
	 while($row_inviti= pg_fetch_array($result_inviti)) {
          print "<option>".$row_inviti[0]."</option>";
	 }
         pg_result_seek($result_inviti,0);
	 ?>
       </select>
       <input type='submit' name='eliminainvitato' value='Elimina' /><br /><br />
       <select name="amico">
	 <?php
         $inserisci = true;
	 while($row_amici= pg_fetch_array($result_amici)) {
            while($row_inviti= pg_fetch_array($result_inviti)){
               if ($row_amici[0] == $row_inviti[0])
                  $inserisci = false;
            }
         if($inserisci == true) print "<option> ".$row_amici[0]." </option>";
               $inserisci = true;
               pg_result_seek($result_inviti,0);
	 }
         pg_result_seek($result_amici,0);
	 ?>
       </select>
       <input type='submit' name='aggiungiinvito' value='Aggiungi' /><br /><br />
     </form>
     <form action='home.php?action=eventi' method='post'> <input type='submit' value='Salva'/></form> 

     
<?php
}

else{
    print "Non sei autorizzato a vedere questa pagina";
}
