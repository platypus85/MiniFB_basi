<?php

  $nome ="";
  $cognome ="";
  $email ="";
  $psw="" ;
  $sesso = "u";
  $citta="" ;
  $prov ="";
  $nascita="" ;
  $msg = "Attenzione<br>";
  $conn=db_connect();
  $err_reg = false;

  
if(isset($_POST['registrami'])){
  
  ## Controlli sicurezza  campi di testo ##
  
  $nome = htmlentities(trim($_POST['nome']));
  $cognome =  htmlentities(trim($_POST['cognome']));
  $email = htmlentities(trim($_POST['email']));
  $sesso = $_POST['sesso'];
  $psw = htmlentities(trim($_POST['password']));
  $citta =  $_POST['citta'];
  $prov = $_POST['prov'];
  $nascita = $_POST['nascita'];
  $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";
  $patternpass = "/^([a-zA-Z0-9@*#_]{8,15})$/"; //accetta lettere minuscole,lettere maiuscole, numeri ed i caratteri speciali @ * # _ volendo ne aggiungi altri
  
  ## Controlli form registrazione ##
    
  $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta' AND provincia = '$prov'"; 
  $result_citta = pg_query($conn, $query_citta);
  $row_citta=pg_fetch_array($result_citta);
  
  $query_eta = "SELECT eta_reg('$nascita')";
  $result_eta = pg_query($conn, $query_eta); 
  $row_eta=pg_fetch_array($result_eta);
  
  $query_check = "SELECT email FROM utente WHERE email='$email'"; 
  $result_check = pg_query($conn, $query_check);
  
  $err_reg = false;

  // Maggiorenne  
  if($row_eta[0]<=17){
    ($row_eta[0]<0)?
     $msg = $msg."<br /> - Controlla la data di nascita, &egrave posteriore alla data odierna":
     $msg = $msg."<br /> - Mi dispiace ma sei minorenne e non puoi usufruire di questo servizio<br />";
	 $err_reg = true;
  }

  // Esistenza citta'  
  if($row_citta[0]==0 && ($citta != 'Non specificata' | $prov != 'Non specificata')){
     $msg = $msg."<br /> - La citt&agrave; inserita non esiste. Ricordarsi di associare ad una citt&agrave la giusta provincia <br />";
	 $err_reg = true;
  }

  // Esistenza mail  
  if($row=pg_fetch_array($result_check)){
     $msg = $msg."<br /> - La e-mail inserita risulta gi&agrave; registrata <br />";
	 $err_reg = true;
  }	 

  // Validita' E-mail
  if(!preg_match($pattern, $email)){
      $msg = $msg."<br /> - La mail inserita non &egrave; corretta <br />";
	  $err_reg = true;
  }	  
      
  // Validita' password
  if(!preg_match($patternpass, $psw)){
	$msg .= "<br /> - La password inserita non &egrave; corretta:<br />
					<ul><li>la lunghezza deve essere compresa tra 8 e 15.
					<li>Sono consentite lettere maiuscole e minuscole</li>
					<li>Sono consentiti numeri</li>
					<li>Sono consentiti i caratteri speciali @ * # _</li>
               <br />";
	$err_reg = true;	   
  }
//  if(!eregi($patternpass, $psw))
//      $msg = $msg."<br> - La password inserita non e' corretta: minimo 4 lettere, massimo 15.<br>Il primo carattere deve essere una lettera e gli unici caratteri
//                          ammessi sono lettere, numeri e l'underscore. <br>";

  if(!$err_reg){ // Form complitato correttamente
        ($sesso=='u')?      
	$query = "SELECT registra('" . pg_escape_string($email) . "','" .pg_escape_string($psw) . "','" . pg_escape_string($nome) . "','" . pg_escape_string($cognome) . "', null, '$citta','$prov','$nascita')":
	$query = "SELECT registra('" . pg_escape_string($email) . "','" .pg_escape_string($psw) . "','" . pg_escape_string($nome) . "','" . pg_escape_string($cognome) . "', '$sesso', '$citta','$prov','$nascita')";

	print $query;
	$controllologin = pg_query($conn, $query);  
	  
	// Registrazione completata, creazione variabili di sessione     
	if($controllologin){ 
	  session_start();
	  $_SESSION["email"]=$email;
	  $_SESSION["vip"]=false;
	  header("Location: home.php");  
	}
  }
} 

  $query_citta= "SELECT DISTINCT nome FROM citta ORDER BY nome ASC;";
  $result_citta= pg_query($conn, $query_citta);
  $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia ASC;";
  $result_prov=pg_query($conn, $query_prov);

?>


  <form method="POST" action= "<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="nome">Nome:</label>
      <input type="text" id="nome" name="nome" required='required' maxlength="20" value='<?php print "$nome" ?>'/><br />
    <label for="cognome">Cognome:</label>
      <input type="text" id="cognome" name="cognome" required='required' maxlength="20" value='<?php print "$cognome" ?>'/><br />
    <label for="sesso" >Sesso:</label>
      <input type="radio" id="sessom" name="sesso" value="m" <?php echo ($sesso=='m')?'checked':'' ?>>M 
      <input type="radio" id="sessof" name="sesso" value="f" <?php echo ($sesso=='f')?'checked':'' ?>>F
      <input type="radio" id="sessoe" name="sesso" value="u" <?php echo ($sesso=='u')?'checked':'' ?>>Non specificato<br/>
    <label for="email">Indirizzo e-mail</label>
       <input type="text" id="email" name="email" required='required' maxlength="35" value='<?php print "$email" ?>'/><br />
    <label for="password">Password</label>
      <input type="password" id="password" name="password" required='required' maxlength="15" value='<?php print "$psw" ?>'/><br />
    <label for="prov"> Provincia </label>
      <select name="prov">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row=pg_fetch_array($result_prov)) {
	  if($row['provincia']==$prov){
	    print "<option selected='selected' value='" . $row['provincia'] . "'>". strtoupper($row['provincia']) ."</option>";
	    continue;
	  }
	    print "<option value='" . $row['provincia'] . "'> ". strtoupper($row['provincia']) ." </option>";
	}
	?>
      </select><br />
    <label for="citta"> Citt&agrave di nascita </label>
      <select name="citta">
        <option selected='selected' value="Non specificata">Non specificata</option> 
	<?php
	while($row=pg_fetch_array($result_citta)) {
	  if($row['nome']==$citta){
	    print "<option selected='selected' value='" . $row['nome'] . "'>".ucfirst($row['nome'])."</option>";
	    continue;
	  }
	    print "<option value='" . $row['nome'] . "'>".ucfirst($row['nome'])."</option>";
	}
	?>
      </select><br/>
    <label for="nascita">Data di nascita:</label>
      <input type="date" id="nascita" name="nascita"  required='required' value='<?php print "$nascita" ?>'/><br /><br />
      <input type="submit" value="registra" align="right" name="registrami"  />
    </form><br><br>
	
<?php
// Stampa messaggio di errore
if($err_reg){
  print '<div class="error">' .$msg . '</div>';
}
?>