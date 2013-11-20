<?php

    $msg = "<br/>Attenzione:<br/>";
    $conn=db_connect();
    

if(isset($_POST['aggiungiscuola'])){
    
    $nome = htmlentities(trim($_POST['nome']));
    $tipo = pg_escape_string(htmlentities($_POST['tipo']));
    $prov = $_POST['provsede'];
    $citta = $_POST['cittasede'];

    // Controllo compilazione form (esistenza citta)  
    $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta' AND provincia = '$prov'";
    $result_citta = pg_query($conn, $query_citta);
    $row_citta=pg_fetch_array($result_citta);
    
    if($row_citta[0]==0){
      $msg .= "<br> - La citt&agrave inserita non esiste <br/>";
    }
    
    if(strlen($nome)<4){
      $msg .= "<br> - Il nome della scuola deve essere lungo almeno 4 caratteri <br/>";
    }
    
    // Form compilato correttamente
    if($msg=="<br/>Attenzione:<br/>"){
    $nome = strtolower(pg_escape_string($nome));
	
    $query = "INSERT INTO scuola (nome, tipo, sede) VALUES ('$nome', '$tipo', get_idcitta('$citta','$prov'))";
    $result= pg_query($conn, $query);
    header("Location: home.php?action=modifprofilo");
    }
}
    
    
    $query_tipo = "SELECT DISTINCT tipo FROM scuola ORDER BY tipo";
    $result_tipo = pg_query($conn, $query_tipo);
    $query_citta= "SELECT DISTINCT nome FROM citta ORDER BY nome;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT DISTINCT provincia FROM citta ORDER BY provincia;";
    $result_prov=pg_query($conn, $query_prov);
?>

    <h4> Inserisci una nuova scuola </h4>      
    <form method="POST" >
        <label for="nome">Nome scuola:</label> 
        <input type="text" id="nome" name="nome"  maxlength="30" required="required"/><br />
        <label for="tipo">Tipo:</label>
        <select name="tipo">
          <?php
            while($row=pg_fetch_array($result_tipo)){
                print"<option> ".$row['tipo']." </option>";
            }
          ?>
        </select><br />
        <label for="provsede"> Provincia:</label>
        <select name="provsede">
          <?php
            while($row=pg_fetch_array($result_prov)) {
                print "<option  value='" . $row['provincia'] . "'> ".strtoupper($row['provincia'])." </option>";
            }
            pg_result_seek($result_prov,0);   
          ?>
        </select><input type='button' name='inseriscicitta' value='Nuova citt&agrave' onclick=location.href='home.php?action=inseriscicitta&ret=inserisciscuola' /><br />
        <label for="cittasede"> Citt&agrave:</label>
        <select name="cittasede">
          <?php
            while($row=pg_fetch_array($result_citta)) {
               print "<option  value='" . $row['nome'] . "'>  ".ucfirst($row['nome'])."</option>";
            }
            pg_result_seek($result_citta,0);
          ?>
        </select>
	<br>
        <?php if($msg!="<br/>Attenzione:<br/>") echo "<div class='error'>".$msg."</div>";"<br>" ?> <br />
        <input type="submit" value="Aggiungi" align="right" name="aggiungiscuola" ; />
	    
