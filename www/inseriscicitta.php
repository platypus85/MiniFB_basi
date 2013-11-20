<?php


    $msg = "<br/>Attenzione:<br/>";
    $conn=db_connect();
    

if(isset($_POST['aggiungicitta'])){
    
    $citta = strtolower(pg_escape_string(htmlentities(trim($_POST['citta']))));
    $prov = strtolower(pg_escape_string(htmlentities(trim($_POST['provincia']))));


    // Controllo compilazione form (esistenza citta)  
    $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta' AND provincia = '$prov'";
    $result_citta = pg_query($conn, $query_citta);
    $row_citta=pg_fetch_array($result_citta);
    
    if($row_citta[0]>=1){
      $msg .= "<br> - La citt&agrave inserita esiste gi&agrave <br/>"; 
    }
    
    if(strlen($citta)<=1){
      $msg .= "<br> - Il nome della citt&agrave non pu&ograve essere di un solo carattere <br/>"; 
    }
    
    if(strlen($prov)!=2){
      $msg .= "<br> - La sigla della provincia deve essere comporta da due caratteri <br/>"; 
    }
    
    // Form compilato correttamente
    if ($msg=="<br/>Attenzione:<br/>") { 
    
    $query = "INSERT INTO citta (nome, provincia) VALUES ('$citta','$prov')";
    $result= pg_query($conn, $query);
    
    print $_GET['ret'];
    
    switch($_GET['ret']){
        case('eventi'):  header("Location: home.php?action=eventi");
        break;
        case('modifprofilo'):  header("Location: home.php?action=modifprofilo");
        break;
        case('inserisciscuola'):  header("Location: home.php?action=inserisciscuola");
        break;

    }
 //   header("Location: home.php?action=modifprofilo");
    }
}
    
    
    $query_tipo = "SELECT DISTINCT tipo FROM scuola ORDER BY tipo";
    $result_tipo = pg_query($conn, $query_tipo);
    $query_citta= "SELECT nome FROM citta ORDER BY nome;";
    $result_citta= pg_query($conn, $query_citta);
    $query_prov="SELECT provincia FROM citta ORDER BY provincia;";
    $result_prov=pg_query($conn, $query_prov);
?>

    <h4> Inserisci una nuova citt&agrave </h4>      
    <form method="POST" >
        <label for="citta">Nome citt&agrave:</label> 
        <input type="text" name="citta"  maxlength="20" required="required"/><br />
        <label for="provincia"> Nome provincia:</label>
        <input type="text"  name="provincia"  maxlength="2" required="required"/><br />
        <?php if($msg!="<br/>Attenzione:<br/>") echo "<div class='error'>".$msg."</div>";"<br>" ?> <br />
        <input type="submit" value="aggiungi" align="right" name="aggiungicitta" ; />
	    




