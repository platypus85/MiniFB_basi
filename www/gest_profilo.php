<?php

    $usr = $_SESSION['email'];
    $patternpass = "/^([a-zA-Z0-9@*#_]{8,15})$/";
    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/"; 
    $msg_errore = "Attenzione:<br>";
    $msg_frequenza = "Attenzione:<br>";
    $msg_frequenza_upd = "Attenzione:<br>";
    date_default_timezone_set ('Europe/Rome');
    $date =  date("Y-m-d");
    $conn=db_connect();
    $data_inizio = "";
    $data_fine = "";
    $data_iniziof = "";
    $data_finef = "";



if(isset($_POST['salvaprof'])){
    
    # Controllo dei campi di testo del form #
    
    $nome = pg_escape_string(htmlentities(trim($_POST['nome'])));
    $cognome = pg_escape_string(htmlentities(trim($_POST['cognome'])));
    $email = pg_escape_string(htmlentities(trim($_POST['email'])));
    $sesso = $_POST['sesso'];
    $psw = pg_escape_string(htmlentities(trim($_POST['password'])));
    $citta_nascita = $_POST['cittanasc'];
    $prov_nascita = $_POST['provnasc'];
    $citta_residenza = $_POST['cittares'];
    $prov_residenza = $_POST['provres'];
    $nascita = $_POST['nascita'];
    
     // Controllo E-mail
    $query_check = "SELECT email FROM utente WHERE email!='$usr'";
    $result_check = pg_query($conn, $query_check);
    
    // Controllo esistenza citta
    $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta_nascita' AND provincia = '$prov_nascita'";
    $result_citta = pg_query($conn, $query_citta);
    $row_citta=pg_fetch_array($result_citta);
    
    $query_cittares = "SELECT count(*) FROM citta WHERE nome = '$citta_residenza' AND provincia = '$prov_residenza'";
    $result_cittares = pg_query($conn, $query_cittares);
    $row_cittares = pg_fetch_array($result_cittares);


    // Controllo maggiore eta
    $query_eta = "SELECT eta_reg('$nascita')";
    $result_eta = pg_query($conn, $query_eta); 
    $row_eta=pg_fetch_array($result_eta);
    
    # Esecuazione controlli #
   
    while($row_check=pg_fetch_array($result_check)){
      if($row_check[0]==$email)
         $msg_errore .= "<br> - La mail &egrave gi&agrave registrata <br>";
    }

    if(!preg_match($pattern, $email))
      $msg_errore .= "<br> - La mail inserita non &egrave corretta<br>";

      
    if(!preg_match($patternpass, $psw))
      $msg_errore .= "<br> - La password inserita non &egrave corretta: minimo 4 lettere, massimo 15.<br>Il primo carattere deve essere una lettera e gli unici caratteri
                          ammessi sono lettere, numeri e l'underscore. <br>";
    
    if($row_eta[0]<=17){
       if($row_eta[0]<=$date)
          $msg_errore .= "<br> - Hai inserito una data di nascita posteriore alla data odierna <br>";
       else
        $msg_errore .= "<br> - Mi dispiace ma sei minorenne e non puoi usufruire di questo servizio<br>";
    }
    
    if($row_citta[0] == 0 AND ($citta_nascita != 'Non specificata' | $prov_nascita != 'Non specificata')) {
                $msg_errore .= "<br> - La citt&agrave di nascita non esiste. Ricordarsi di associare ad una citt&agrave la giusta provincia. <br>";
             }
    
        
    if($row_cittares[0] == 0 AND ($citta_residenza != 'Non specificata' | $prov_residenza != 'Non specificata')) {
                $msg_errore .= "<br> - La citt&agrave di residenza non esiste. Ricordarsi di associare ad una citt&agrave la giusta provincia. <br>";
             }
             
    // Tutti i controlli sono andati a buon fine      
    if($msg_errore=='Attenzione:<br>'){
      $query1a = "UPDATE utente SET email = '$email'  WHERE email = '$usr'";
      $query1b = "UPDATE utente SET psw = '$psw' WHERE email = '$email'";
      $query2 = "UPDATE profilo SET nome = '$nome', cognome = '$cognome',citta_nascita = get_idcitta('$citta_nascita','$prov_nascita'),
                 citta_residenza = get_idcitta('$citta_residenza','$prov_residenza'), data_nascita = '$nascita',";
      ($sesso==null)?
      $query2 .= " sesso = null ":
      $query2 .= " sesso = '$sesso' ";
      $query2 .= " WHERE utente = '$email'";
  
                 
    // Gestione della modifica della E-mail
    if($email!=$usr){
        $_SESSION['email'] = $email;
        $result1= pg_query($conn, $query1a);
        $result2= pg_query($conn, $query2);

    }else{
        $result1= pg_query($conn, $query1b);
        $result2= pg_query($conn, $query2);
  


    }
    header("Location: home.php?action=profilo");
    } //end salvataggio profilo
} // end if(isset($_POST['salvaprof'])){



if(isset($_POST['rimuoviprof'])){
               
    $query = "SELECT elimina_ut('$usr')";
    $result= pg_query($conn, $query);
    $row = pg_fetch_array($result);
    
    if($row[0]==0){ // L'utente ha zero amici 
    session_unset();
    header("Location: index.php");
    }
    else // L'utente ha amici o richieste di amicizia in sospeso: non puo' essere eliminato
    print "L'utente non pu&ograve essere eliminato";
}


if(isset($_POST['eliminahobby']) && isset($_POST['mieihobby'])){
    //nb. la seconda condizione previene dallo schiacchiare elimina quando non c' nessun hobby da eliminare

    $email = $_POST['email'];
    $hobby = $_POST['mieihobby'];
    $query = "DELETE FROM interessi WHERE nome_hobby = '$hobby' AND utente = '$email' ";
    $result= pg_query($conn, $query);
}


if(isset($_POST['aggiungihobby']) && isset($_POST['hobby'])){
    //nb. la seconda condizione previene dallo schiacchiare aggiungi quando non c' nessun hobby da aggiungere
      
    $email = pg_escape_string(htmlentities(trim($_POST['email'])));
    $hobby = $_POST['hobby'];
    $conn = db_connect();
    $query = "INSERT INTO interessi VALUES ('$hobby','$email')";
    $result= pg_query($conn, $query);
}


if(isset($_POST['aggiungifreq'])){
        
        
    $nomef = $_POST['scuola'];
    $tipof = pg_escape_string(htmlentities($_POST['tipo']));
    $provsedef = $_POST['provsede'];
    $cittasedef = $_POST['cittasede'];
    $data_iniziof = $_POST['data_inizio'];
    $data_finef = $_POST['data_fine'];
    
    $query_check = "SELECT count(*) FROM scuola WHERE idscuola = get_idscuola('$nomef', '$tipof' ,'$cittasedef','$provsedef')";
    $result_check = pg_query($conn, $query_check);
    $row_check = pg_fetch_array($result_check);

    # Controlli compilazione form #
    
    //Controllo esistenza
    $query_ins = "SELECT data_inizio, data_fine FROM frequenza WHERE scuola = get_idscuola('$nomef', '$tipof' , '$cittasedef' , '$provsedef') AND studente = '$usr'";
    $res_ins = pg_query($conn,$query_ins);
    while($row_ins = pg_fetch_array($res_ins)){
            if($row_ins['data_fine']!=null & $data_finef >= $row_ins['data_inizio'] & $data_finef <= $row_ins['data_fine'] ){
                    $msg_frequenza .= "<br> - Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
            if($row_ins['data_fine']!=null & $data_iniziof <= $row_ins['data_inizio'] & $data_finef >= $row_ins['data_fine'] ){
                    $msg_frequenza .= "<br> - Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
            if($row_ins['data_fine']!=null & $data_iniziof >= $row_ins['data_inizio'] & $data_iniziof <= $row_ins['data_fine'] ){
                    $msg_frequenza .= "<br> - Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
    }
    
    // Controllo scuola    
    if($row_check[0]==0)
        $msg_frequenza .= "<br> - La scuola inserita non esiste. Controllare che il tipo e la sede siano corretti<br>";     

    // Controllo antecedenza date      
    if($data_iniziof >= $data_finef && $data_finef != null)
        $msg_frequenza .= "<br> - La data di fine frequenza non pu&ograve essere antecedente alla data di inizio o coincidere.<br>";     
 
     
    if($msg_frequenza == 'Attenzione:<br>'){ // Nessun errore di compilazione
       // Sceglie la query a seconda che sia inserita o meno la data di fine 
       if(!$data_finef=="")
         $query = "INSERT INTO frequenza (studente, scuola, data_inizio, data_fine) VALUES ('$usr', get_idscuola('$nomef', '$tipof' , '$cittasedef','$provsedef') ,'$data_iniziof' ,'$data_finef' )";
       else
         $query = "INSERT INTO frequenza (studente, scuola, data_inizio) VALUES ('$usr', get_idscuola('$nomef', '$tipof' , '$cittasedef','$provsedef') ,'$data_iniziof')";
       $result= pg_query($conn, $query);

    header("Location: home.php?action=profilo");
    }
} 


if(isset($_POST['salvafreq'])){
    
    $nome = $_POST['scuola'];
    $tipo = pg_escape_string(htmlentities($_POST['tipo']));
    $citta_sede = $_POST['cittasede'];
    $prov_sede = $_POST['provsede'];
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    
    
    //Controllo esistenza
    $query_ins = "SELECT data_inizio, data_fine FROM frequenza WHERE scuola = get_idscuola('$nome', '$tipo' , '$citta_sede' , '$prov_sede') AND studente = '$usr' AND data_inizio != '$data_inizio'";
    $res_ins = pg_query($conn,$query_ins);
    while($row_ins = pg_fetch_array($res_ins)){
        if($row_ins['data_fine']!=null){
            if($data_fine >= $row_ins['data_inizio'] && $data_fine <= $row_ins['data_fine'] ){
                    $msg_frequenza_upd .= "<br> - 1Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
            if($data_inizio <= $row_ins['data_inizio'] && $data_fine >= $row_ins['data_fine'] ){
                    $msg_frequenza_upd .= "<br> - 2Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
            if($data_inizio >= $row_ins['data_inizio'] && $data_inizio <= $row_ins['data_fine'] ){
                    $msg_frequenza_upd .= "<br> - 3Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }
        }
        if($row_ins['data_fine']==null){
            if($data_inizio <= $row_ins['data_inizio'] && $data_fine >= $row_ins['data_inizio'] ){
                    $msg_frequenza_upd .= "<br> - 2Hai gi&agrave inserito una frequenza in questo periodo.<br>";
                    break;
            }    
        }
    }
        
    // Controllo antecedenza date      
    if($data_inizio >= $data_fine && $data_fine != null)
        $msg_frequenza_upd .= "<br> - La data di fine frequenza non pu&ograve essere antecedente alla data di inizio o coincidere.<br>";     

     
    if($msg_frequenza_upd == 'Attenzione:<br>'){ // Nessun errore di compilazione
        
       ($data_fine==null)? 
       $query = "UPDATE frequenza SET data_fine = null WHERE  studente = '$usr' AND scuola = get_idscuola('$nome', '$tipo' ,'$citta_sede','$prov_sede') AND data_inizio = '$data_inizio'":
       $query = "UPDATE frequenza SET data_fine = '$data_fine' WHERE  studente = '$usr' AND scuola = get_idscuola('$nome', '$tipo' ,'$citta_sede','$prov_sede') AND data_inizio = '$data_inizio'";
       $result= pg_query($conn, $query);

       header("Location: home.php?action=profilo");
    }
    
}


if(isset($_POST['rimuovifreq'])){
    
    $nome = $_POST['scuola'];
    $tipo = pg_escape_string(htmlentities($_POST['tipo']));
    $citta_sede = $_POST['cittasede'];
    $prov_sede = $_POST['provsede'];
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    
    $query = "DELETE FROM frequenza 
              WHERE studente = '$usr' AND data_inizio = '$data_inizio' AND scuola = get_idscuola('$nome', '$tipo' ,'$citta_sede','$prov_sede') ";
              
    $result= pg_query($conn, $query);
    header("Location: home.php?action=profilo");
}
   
    // Query per le informazioni dell'utente da mettere come valori predefiniti nel form di moficia
    $query = "SELECT nome, cognome, utente, psw, data_nascita, sesso, 
              get_nomecitta(citta_nascita) AS citta_nascita, get_provcitta(citta_nascita) AS prov_nascita,
              get_nomecitta(citta_residenza) AS citta_residenza, get_provcitta(citta_residenza) AS prov_residenza
              FROM utente JOIN profilo ON email = utente
              WHERE utente = '$usr'"; 
     
    $result = pg_query($conn, $query);
    $row=pg_fetch_array($result);    
	
    $query_citta= "SELECT DISTINCT nome FROM citta ORDER BY nome;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia;";
    $result_prov=pg_query($conn, $query_prov);
    $query_hobby="SELECT nome FROM hobby ORDER BY nome";
    $result_hobby=pg_query($conn,$query_hobby);
    $query_mieihobby="SELECT nome_hobby FROM interessi WHERE utente = '$usr' ORDER BY nome_hobby";
    $result_mieihobby=pg_query($conn,$query_mieihobby);
    
     
?>
    <h4> Modifica le informazioni personali </h4><br />      
    <form method="POST">
      <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php print "$row[nome]"?>"  maxlength="20" required='required' /><br />
      <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome" value="<?php print "$row[cognome]"?>"  maxlength="20" required='required' /><br />
      <label for="email">Indirizzo e-mail:</label>
        <input type="text" id="email" name="email" value="<?php print "$row[utente]"?>"  maxlength="35" required='required' /><br/>
      <label for="sesso" required='required'>Sesso:</label>
        <?php $sesso = $row['sesso'];?>
        <input type="radio" id="sesso" name="sesso" value="m" <?php echo ($sesso=='m')?'checked':'' ?> />M 
        <input type="radio" id="sesso" name="sesso" value="f" <?php echo ($sesso=='f')?'checked':'' ?> />F
        <input type="radio" id="sessoe" name="sesso" value="<?php null ?>" <?php echo ($sesso==null)?'checked':'' ?> />Non specificato<br />
      <label for="password">Password:</label>
        <input type="password" id="password" name="password" value="<?php print "$row[psw]" ?>"  maxlength="25" required='required' /><br />
      <label for="provnasc"> Provincia di nascita: </label>
	<select name="provnasc">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row1=pg_fetch_array($result_prov)) {
            if($row1['provincia']==$row['prov_nascita']){
 	        print "<option selected='selected' value='".$row1['provincia']."'>".strtoupper($row1['provincia'])."</option>";
                   }
	    else print"<option value='".$row1['provincia']."'>".strtoupper($row1['provincia'])."</option>";
	}
        pg_result_seek($result_prov,0);
	?>
        </select><input type='button' name='inseriscicitta' value='Nuova citt&agrave' onclick=location.href='home.php?action=inseriscicitta&ret=modifprofilo' /><br />
      <label for="cittanasc"> Citt&agrave di nascita:</label>
	<select name="cittanasc">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row1=pg_fetch_array($result_citta)) {
            if($row1['nome']==$row['citta_nascita']){
	        print"<option selected='selected' value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
            }
	    else print "<option value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
	}
        pg_result_seek($result_citta,0);
        ?>
	</select><br />         
      <label for="provres"> Provincia di residenza:</label>
	<select name="provres">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row1=pg_fetch_array($result_prov)) {
            if($row1['provincia']==$row['prov_residenza']){
 	        print "<option selected='selected' value='".$row1['provincia']."'>".strtoupper($row1['provincia'])."</option>";
            }
	    else print"<option value='".$row1['provincia']."'>".strtoupper($row1['provincia'])."</option>";
	}
        pg_result_seek($result_prov,0);
	?>
        </select><input type='button' name='inseriscicitta' value='Nuova citt&agrave' onclick=location.href='home.php?action=inseriscicitta&ret=modifprofilo' /><br />
      <label for="cittares"> Citt&agrave di residenza:</label>
	<select name="cittares">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row1=pg_fetch_array($result_citta)) {
            if($row1['nome']==$row['citta_residenza']){
	        print"<option selected='selected' value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
            }
	    else print "<option value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
	}
        pg_result_seek($result_citta,0);
	?>
	</select><br />
      <label for="nascita">Data di nascita:</label>
        <input type="date" id="nascita" name="nascita" value="<?php print $row['data_nascita'] ?>" required='required' /><br />
        <label for="hobby">Hobby:</label>
	<select name="mieihobby">
	<?php
	while($row_mieihobby= pg_fetch_array($result_mieihobby)) {
            print "<option>".$row_mieihobby[0]."</option>";
	}
        pg_result_seek($result_mieihobby,0);
	?>
	</select>
	<input type='submit' name='eliminahobby' value='Elimina' />
	<select name="hobby">
	<?php
        $inserisci = true;
	while($row_hobby= pg_fetch_array($result_hobby)) {
            while($row_mieihobby= pg_fetch_array($result_mieihobby)){ // Inserisci solo i miei hobby
                if ($row_hobby[0] == $row_mieihobby[0])
                    $inserisci = false;
                }
        if($inserisci == true) print "<option> ".$row_hobby[0]." </option>";
            $inserisci = true;
        pg_result_seek($result_mieihobby,0);
	}
        pg_result_seek($result_hobby,0);
	?>
	</select>
	<input type='submit' name='aggiungihobby' value='Aggiungi' /><br /><br />
        <?php if($msg_errore!="Attenzione:<br>")  echo "<div class='error'>".$msg_errore."</div>";"<br>" ?><br />
        <input type="submit" value="Salva" align="right" name="salvaprof"/>
        <input type="submit" value="Elimina utente" name="rimuoviprof"></br>
    </form><br /><br />
<?php
        //stampa gli errori del profilo sotto il profilo
      


    $query = "SELECT DISTINCT nome, tipo, get_nomecitta(sede) AS citta, get_provcitta(sede)  AS prov, data_inizio, data_fine
              FROM frequenza JOIN scuola ON scuola = idscuola
              WHERE studente = '$usr'
              ORDER BY data_inizio"; 
    $result = pg_query($conn, $query);
    
    $query_citta= "SELECT DISTINCT nome FROM citta ORDER BY nome;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia;";
    $result_prov=pg_query($conn, $query_prov);
    $query_scuola= "SELECT DISTINCT nome AS scuola, idscuola, tipo FROM scuola ORDER BY nome";
    $result_scuola=pg_query($conn, $query_scuola);
    $query_tipo = "SELECT DISTINCT tipo FROM scuola ORDER BY tipo";
    $result_tipo = pg_query($conn, $query_tipo);
    $query_nome = "SELECT DISTINCT nome FROM scuola ORDER BY nome";
    $result_nome = pg_query($conn, $query_nome);

    
    print "<h4> Modifica le scuole frequentate </h4>";
    if($msg_frequenza_upd != 'Attenzione:<br>') echo "<div class='error'><br/>".$msg_frequenza_upd."</div><br/><br/>";

    $count = 0;
    while($row=pg_fetch_array($result)){
    $count++;
?>
    <form method="POST">
      <label for="scuola">Scuola:</label>
	<select name="scuola">
	<?php
	while($row1=pg_fetch_array($result_scuola)) {
            if($row1['scuola']==$row['nome']){
	         print"<option selected='selected' value = '".$row1['scuola']."'> ".ucwords($row1['scuola'])." </option>"; 
                 break; // Non scrive due volte due scuole che hanno lo stesso nom
            }
        }
        pg_result_seek($result_scuola,0);
	?>
	</select><br />
      <label for="tipo">Tipo:</label>
	<select name="tipo">
	<?php
	while($row1=pg_fetch_array($result_scuola)) {
            if($row1['tipo']==$row['tipo']){
	        print"<option selected='selected'> ".$row1['tipo']." </option>";
                break;
            }
        }
        pg_result_seek($result_scuola,0);
        pg_result_seek($result_tipo,0);
	?>
	</select><br />
      <label for="provsede"> Provincia:</label>
	<select name="provsede">
	<?php
	while($row1=pg_fetch_array($result_prov)) {
            if($row1['provincia']==$row['prov']){
	         print"<option selected='selected' value='".$row1['provincia']."'> ".strtoupper($row1['provincia'])." </option>";
            }
	}
        pg_result_seek($result_prov,0);
	?>
	</select><br />
      <label for="cittasede"> Citt&agrave:</label>
	<select name="cittasede">
	<?php
	    while($row1=pg_fetch_array($result_citta)) {
                if($row1['nome']==$row['citta']){
	            print"<option selected='selected' value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
                }
	    }
        pg_result_seek($result_citta,0);
        ?>
        </select><br/>
      <label for="data_inizio">Data inizio:</label>
        <?php $_POST['data_inizio']=$row['data_inizio']; ?>
        <input type="date" id="data_inizio" name="data_inizio" value="<?php print "$row[data_inizio]"?>" readonly=true' /></input><br />
      <label for="data_fine">Data fine:</label>
        <input type="date" id="data_fine" name="data_fine" value="<?php print "$row[data_fine]"?>" /><br/>
        <br/> 
        <input type="submit" value="Salva" align="right" name="salvafreq"  />
        <input type="submit" value="Rimuovi" name="rimuovifreq" />
    </form><br />
<?php
    pg_result_seek($result_citta,0);
    pg_result_seek($result_prov,0);
    }
    if($count == 0) print "Ancora non hai inserito nessuna frequentazione <br><br>"
?>

    <h4> Inserisci una nuova frequenza </h4>      
    <form method="POST">
      <label for="scuola">Nome scuola:</label> 
	<select name="scuola">
	<?php
	while($row1=pg_fetch_array($result_nome)) {
            if($row1['nome']==$nomef){ 
	        print"<option selected='selected' value = '".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
            }
	    else print"<option value = '".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
	    }
        pg_result_seek($result_scuola,0);
	?>
	</select><input type='button' name='inserisciscuola' value='Nuova scuola' onclick=location.href='home.php?action=inserisciscuola' /></br>
      <label for="tipo">Tipo:</label>
	<select name="tipo">
	<?php
	while($row1=pg_fetch_array($result_tipo)) { // seleziona come predefinito quello precedente passato a post
            if($row1['tipo']==$tipof) 
	        print"<option selected='selected' > ".$row1['tipo']." </option>";
            else print "<option  > ".$row1['tipo']." </option>";
        }
        pg_result_seek($result_scuola,0);
        pg_result_seek($result_tipo,0);
	?>
	</select><br />
      <label for="provsede"> Provincia:</label>
	<select name="provsede">
	<?php
	while($row1=pg_fetch_array($result_prov)) {
            if($row1['provincia']==$provsedef){
	        print"<option selected='selected' value='".$row1['provincia']."'> ".strtoupper($row1['provincia'])." </option>";
            }
            else print "<option value='".$row1['provincia']."'> ".strtoupper($row1['provincia'])." </option>";
        }
        pg_result_seek($result_prov,0);   
	?>
	</select><br />
      <label for="cittasede"> Citt&agrave:</label>
	<select name="cittasede">
	<?php
	while($row1=pg_fetch_array($result_citta)) {
            if($row1['nome']==$cittasedef){
	        print"<option selected='selected' value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
            }
	    else print "<option value='".$row1['nome']."'> ".ucwords($row1['nome'])." </option>";
	}
        pg_result_seek($result_citta,0);   
        ?>
        </select><br />
       <label for="data_inizio">Data inizio:</label>
        <input type="date" id="data_inizio" name="data_inizio" value="<?php print "$data_iniziof"?>" required='required' /><br />
       <label for="data_fine">Data fine:</label>
        <input type="date" id="data_fine" name="data_fine" value="<?php print "$data_finef"?>" /><br /><br />
        <?php if($msg_frequenza != 'Attenzione:<br>') echo "<div class='error'>".$msg_frequenza."</div><br />" ?><br />    
        <input type="submit" value="Aggiungi" align="right" name="aggiungifreq"  />
    </form>
        