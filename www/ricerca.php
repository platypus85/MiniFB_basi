<?php
   $usr = $_SESSION['email'];
   $conn=db_connect();
   $msg = "Attenzione:<br>";
   $stampa = false;

   $nome = "";
   $cognome = "";
   $email = "";
   $provres = "";
   $cittares = "";
   $sesso = null;
   $provnasc = "";
   $cittanasc = "";
   
   
if(isset($_POST['cerca'])){
   
   # Controllo campi di testo del form #
   
   $nome = htmlentities(trim($_POST['nome']));
   $cognome = htmlentities(trim($_POST['cognome']));
   $email = htmlentities(trim($_POST['email']));
   $sesso = $_POST['sesso'];  
   $provres = $_POST['provres'];  
   $cittares = $_POST['cittares'];
   $provnasc = $_POST['provnasc']; 
   $cittanasc = $_POST['cittanasc'];
   $pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
   
   

    // Controllo esistenza citta
    $query_cittanasc = "SELECT count(*) FROM citta WHERE nome = '$cittanasc' AND provincia = '$provnasc'";
    $result_cittanasc = pg_query($conn, $query_cittanasc);
    $row_cittanasc=pg_fetch_array($result_cittanasc);

    $query_cittares = "SELECT count(*) FROM citta WHERE nome = '$cittares' AND provincia = '$provres'";
    $result_cittares = pg_query($conn, $query_cittares);
    $row_cittares=pg_fetch_array($result_cittares);


    if($cittares != 'Qualsiasi' | $provres!='Qualsiasi'){  // La citta di residenza è stata selezionata   
      if($row_cittares[0]==0)
        $msg .= "<br> - La citt&agrave di residenza inserita non &egrave corretta. Ricorda che sono obbligatorie sia la citt&agrave che la provincia <br>";      
    }
    
    if($cittanasc != 'Qualsiasi' | $provnasc!='Qualsiasi'){  // La citta di nascita è stata selezionata   
      if($row_cittanasc[0]==0)
        $msg .= "<br> - La citt&agrave di nascita inserita non &egrave corretta. Ricorda che sono obbligatorie sia la citt&agrave che la provincia <br>";      
    }
    
    if($email!="" && !eregi($pattern, $email)){
      $msg .= "<br> - La mail inserita non &egrave corretta<br>";
    }
    if($msg == "Attenzione:<br>"){  
      $stampa = true; // se true stampa gli utenti cercati sotto il form di ricerca
    } //end if "tutto corretto"
} //end if "cerca"

    $query_citta= "SELECT nome FROM citta ORDER BY nome ASC;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia ASC;";
    $result_prov=pg_query($conn, $query_prov);
    $query_scuola= "SELECT DISTINCT nome AS scuola, idscuola, tipo FROM scuola";
    $result_scuola=pg_query($conn, $query_scuola);
    $query_tipo = "SELECT DISTINCT tipo FROM scuola";
    $result_tipo = pg_query($conn, $query_tipo);
    
    
    #Form ricerca uente #
?>
   <h4> Ricerca utenti </h4>      
   <form method="POST" action= "<?php $_SERVER['PHP_SELF']; ?>">
      <label for="nome">Nome:</label>
         <input type="text" id="nome" name="nome" maxlength="20" value="<?php print $nome; ?>" /><br/>
      <label for="cognome">Cognome:</label>
         <input type="text" id="cognome" name="cognome" maxlength="20" value="<?php print $cognome; ?>" /><br/>
      <label for="email">Indirizzo e-mail:</label>
         <input type="text" id="email" name="email" maxlength="35" value="<?php print $email; ?>" /><br/>
      <label for="sesso">Sesso:</label>
         <input type="radio" id="sessom" name="sesso" value="m" <?php echo $sesso=='m'?'checked':''; ?> />M 
         <input type="radio" id="sessof" name="sesso" value="f"<?php echo $sesso=='f'?'checked':''; ?> />F 
         <input type="radio" id="sessoe" name="sesso" value="<?php null ?>" <?php echo ($sesso==null)?'checked':'' ?> />Entrambi<br />
      <label for="provnasc"> Provincia di nascita: </label>
         <select name="provnasc">
         <option selected = 'selected'> Qualsiasi </option>
	 <?php
	 while($row=pg_fetch_array($result_prov)) {
	 if($row['provincia']==$provnasc){
	    print "<option selected = 'selected' value='".$provnasc."'>".strtoupper($provnasc)."</option>";
	    continue;
         }
         print "<option value='".$row['provincia']."'> ".strtoupper($row['provincia'])." </option>";
         }
         pg_result_seek($result_prov,0);   
	 ?>
         </select><br />
      <label for="cittanasc"> Citta di nascita: </label>
         <select name="cittanasc">
         <option selected = 'selected'> Qualsiasi </option>
	 <?php
	 while($row=pg_fetch_array($result_citta)) {
	 if($row['nome']==$cittanasc){
	    print "<option selected = 'selected' value='".$cittanasc."'>".ucfirst($cittanasc)."</option>";
	    continue;
	 }
	 print "<option value='".$row['nome']."'> ".ucfirst($row['nome'])." </option>";
	 }
         pg_result_seek($result_citta,0);   
	 ?>
         </select><br />
      <label for="provres"> Provincia di residenza: </label>
         <select name="provres">
         <option selected = 'selected'> Qualsiasi </option>
	 <?php
	 while($row=pg_fetch_array($result_prov)) {
	 if($row['provincia']==$provres){
	    print "<option selected = 'selected' value='".$provres."'>".strtoupper($provres)."</option>";
	    continue;
	 }
	 print "<option value='".$row['provincia']."'> ".strtoupper($row['provincia'])." </option>";
	 }
         pg_result_seek($result_prov,0);   
	 ?>
         </select><br />
      <label for="cittares"> Citta di residenza: </label>
         <select name="cittares">
         <option selected = 'selected'> Qualsiasi </option>
	 <?php
	 while($row=pg_fetch_array($result_citta)) {
         if($row['nome']==$cittares){
	    print "<option selected = 'selected' value='".$cittares."'>".ucfirst($cittares)."</option>";
	    continue;
	 }
	 print "<option value='".$row['nome']."'> ".ucfirst($row['nome'])." </option>";
	 }
	 ?>
         </select><br /><br />
         <input type="submit" value="cerca" align="right" name="cerca"  /><br /><br />
         <?php if($msg!="Attenzione:<br>") echo "<div class='error'>".$msg ."</div>";"<br>"; ?><br />
   </form><br /><br />
        
        
<?php

if(isset($_POST['sfoglia'])) $stampa = true;
if($stampa){
   
   if(isset($_POST['sfoglia']) && $_POST['sfoglia'] == 'Avanti'){
      $_POST = unserialize($_GET['dati']);
      $_POST['sfoglia'] = 'Avanti';

   }
   
   if(isset($_POST['sfoglia']) && $_POST['sfoglia'] == 'Indietro'){
      $_POST = unserialize($_GET['dati']);
      $_POST['sfoglia'] = 'Indietro';

   }
         

   $nome = pg_escape_string($_POST['nome']);
   $cognome = pg_escape_string($_POST['cognome']);
   $email = pg_escape_string($_POST['email']);
   $provres = $_POST['provres'];  
   $cittares = $_POST['cittares'];
   $provnasc = $_POST['provnasc']; 
   $cittanasc = $_POST['cittanasc'];
   $sesso = $_POST['sesso'];

   
   # Creazione query di ricerca #  
   $query = "SELECT  nome, cognome, utente, get_nomecitta(citta_residenza) AS cittares, get_provcitta(citta_residenza) AS provres,
                         get_nomecitta(citta_nascita) AS cittanasc, get_provcitta(citta_nascita) AS provnasc, data_nascita, sesso
                         FROM profilo
                         WHERE (";
   if($nome != ""){
      $query .= "  nome LIKE '%$nome%' AND ";
   }
   if($cognome != ""){
      $query .= " cognome LIKE '%$cognome%' AND ";
   }
   if($email != ""){
      $query .= " utente LIKE '%$email%' AND ";
   }
   if($cittares != 'Quaslasi' & $provres != 'Qualsiasi'){
      $query .= " citta_residenza=get_idcitta('$cittares', '$provres') AND ";
   }
   if($cittanasc != 'Quaslasi' & $provnasc != 'Qualsiasi'){
      $query .= " citta_nascita=get_idcitta('$cittanasc', '$provnasc') AND ";
   }                                        
                         
   switch ($sesso){
      case 'm': //solo m 
         $query .=  "  sesso = 'm' AND ";
         break;
      case 'f': //solo f
         $query .=  "  sesso = 'f' AND ";
         break;
      default:
         $query .= "";
         break;
   }
   $query .= "  utente!='$usr'
               ) AND utente NOT IN (SELECT invitato FROM amicizia WHERE  richiedente = '$usr' AND stato='n')";
   $res_query = pg_query($conn, $query);            
   $temp = pg_numrows($res_query);
   
   # RIS SUCCESSIVI #
   
               
      if(isset($_POST['sfoglia'])){
         if($_POST['sfoglia'] == 'Avanti'){
            $offset = $_GET['offset'] + 8;
            $query .=" LIMIT 8 OFFSET $offset";
             $result = pg_query($conn, $query);
         }
         else if($_POST['sfoglia'] == 'Indietro'){
           $offset = $_GET['offset'] - 8;
           $query .=" LIMIT 8 OFFSET $offset";
           $result = pg_query($conn, $query);  
         }  
      }
      else{
        $offset = 0;
        $query .=" LIMIT 8 OFFSET $offset";
        $result = pg_query($conn, $query); 
      }


  # Risultato ricerca #

  $str_ricerca = " <h4>  Risutato ricerca: </h4>
                   <table id = 'ricercatable'>";


   if($offset+8>$temp){
      $offsetnew = $temp-8;
      $countricerca = ($offsetnew+8)." di ".$temp;
   }
   else
   $countricerca = ($offset+8)." di ".$temp;
  ($temp==0)? $str_ricerca .= "La tua ricerca non ha prodotto risultati. Prova a ridurre i parametri di ricerca <tr><td><br/></td></tr>" : $str_ricerca .= " Trovati ".$countricerca." riscontri<tr><td><br></td></tr>";


  while($row=pg_fetch_array($result)){
   
        ($row['provres']=='')?$row['provres']='Non specificata': $row['provres']= strtoupper($row['provres']);
        ($row['cittares']=='')?$row['cittares']='Non specificata': $row['cittares'] = ucfirst($row['cittares']);
        ($row['provnasc']=='')?$row['provnasc']='Non specificata': $row['provnasc']= strtoupper($row['provnasc']);
        ($row['cittanasc']=='')?$row['cittanasc']='Non specificata': $row['cittanasc'] = ucfirst($row['cittanasc']);
        ($row['sesso']=='')?$row['sesso']='Non specificato': '';
   
         $str_ricerca .= "<tr><th>Nome:</th> <td>".$row['nome']."</td></tr> 
                          <tr><th>Cognome:</th> <td>".$row['cognome']."</td></tr> 
                          <tr><th>E-mail:</th> <td>".$row['utente']."</td></tr> 
                          <tr><th>Citta residenza:</th> <td>".$row['cittares']."</td></tr> 
                          <tr><th>Provincia residenza:</th> <td>".$row['provres']."</td></tr> 
                          <tr><th>Citta di nascita:</th> <td>".$row['cittanasc']."</td></tr> 
                          <tr><th>Provincia di nascita:</th> <td>".$row['provnasc']."</td></tr> 
                          <tr><th>Data nascita:</th> <td>".$row['data_nascita']."</td></tr> 
                          <tr><th>Sesso:</th> <td>".$row['sesso']."</td></tr> 
                          <tr><td><br></td></tr> ";

         # Pulsanti #                          
         $query_check = "SELECT get_samic('$usr','$row[utente]')";
         $result_check = pg_query($conn, $query_check);
         $row_check = pg_fetch_array($result_check);
         
         $ut= $row['utente']; // Creo la variabile $ut per $str_ricerca
         switch($row_check[0]){
              case(null): $str_ricerca .= "<tr><td> <form action='gest_amicizie.php?amico=$ut&amico1=$usr' method='post'> <input type='submit' value='Richiedi amicizia'></form></td></tr> ";
              break;
              case('a'):  $str_ricerca .= "<tr><td> <input type='button' name='richiestaattesa' value='Richiesta in attesa' disabled='disabled'> </td></tr> ";
              break;
              case('s'): $str_ricerca .= "<tr><td> <form action='home.php?action=profiloamico&amico=$ut' method='post'> <input type='submit' value='Profilo'></form> </td>
                                          <td><form action='home.php?action=bachecaamico&amico=$ut' method='post'> <input type='submit' value='Bacheca'></form> <td/></tr> ";
              break;
              case('n'): $str_ricerca .= "<tr><td><form action='gest_amicizie.php?ut1=$ut&richiesta_no=$usr' method='post'> <input type='submit' value='Emilina rifiuto'></form><td></tr> ";
              break;
           }
         $str_ricerca .= "<tr><td><br/><br/><br></td>";
         } // end while
    $str_ricerca .= "</table></br>";
    
    $oldPOST = serialize($_POST);
    
    # Stampa dei risultati della ricerca #

   $str_ricerca .= "<center><form action ='home.php?action=ricerca&offset=$offset&dati=$oldPOST' method='post'>";
 
 
  
   if($offset>=8)
    $str_ricerca .=  "<input type = 'submit' name='sfoglia' value = 'Indietro'>";
    
   if($temp>$offset+8)
    $str_ricerca .= "<input type = 'submit' name='sfoglia' value = 'Avanti'>";
    
    $str_ricerca .= "</form></center>";
 
    echo $str_ricerca;
   
}


        

