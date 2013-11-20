<?php
    $msg = "Attenzione:<br/>";
    $usr = $_SESSION['email'];
    $conn=db_connect();
    $nome = "";
    $tipo = "";
    $prov = "";
    $citta = "";
    $via = "";
    $data = "";
    
if(isset($_POST['aggiungievento'])){
    
    $nome = htmlentities(trim($_POST['nome']));
    $tipo = $_POST['tipo'];
    $prov = $_POST['prov'];
    $citta = $_POST['citta'];
    $via = htmlentities(trim($_POST['via']));
    $data = htmlentities(trim($_POST['data']));

    // Controllo compilazione form (esistenza citta)  
    $query_citta = "SELECT count(*) FROM citta WHERE nome = '$citta' AND provincia = '$prov'";
    $result_citta = pg_query($conn, $query_citta);
    $row_citta=pg_fetch_array($result_citta);
    
    // Controllo duplicazione evento 
    $query_eventi = "SELECT count(*) FROM evento WHERE nome = '$nome' AND organizzatore = '$usr' AND data = '$data' AND luogo = get_idcitta('$citta','$prov')";
    $result_eventi = pg_query($conn, $query_eventi);
    $row_eventi=pg_fetch_array($result_eventi);
    
    if($row_eventi[0]!=0){
      $msg .= "<br> - Hai gi&agrave creato questo evento<br/>";
    }
    
    if($row_citta[0]==0){
      $msg .= "<br> - La citt&agrave inserita non esiste <br/>";
    }
    
    if(strlen($nome)<4){
      $msg .= "<br> - Il nome dell'evento deve essere lungo almeno 4 caratteri <br/>";
    }
    
    if(strlen($via)<3){
      $msg .= "<br> - Il nome della via deve essere lungo almeno 4 caratteri <br/>";
    }
    
    // Form compilato correttamente
    // Form compilato correttamente
    if($msg=="Attenzione:<br/>"){
    $nome = pg_escape_string($nome);
    $via = strtolower(pg_escape_string($via));

	
    $query = "INSERT INTO evento (nome, organizzatore, tipo, data, via, luogo, ora_creazione, data_creazione) VALUES ('$nome', '$usr', '$tipo', '$data', '$via', get_idcitta('$citta','$prov'), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
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
    
    
?>


    <h4> Crea un nuovo evento </h4>
    <?php
    if($_SESSION['vip']==false) print "<div class = msgvip> *Devi avere almeno dieci amici per poter creare eventi* </div><br/>";
    ?>
    <form method="POST" >
        <label for="nome">Nome evento:</label> 
        <input type="text" id="nome" name="nome"  maxlength="25" required="required" value='<?php print "$nome" ?>' /><br />
        <label for="tipo">Tipo:</label>
        <select name="tipo">
          <?php
            while($row=pg_fetch_array($result_tipo)){
                print"<option> ".$row['tipo']." </option>";
            }
          ?>
        </select><br />
        <label for="prov"> Provincia:</label>
        <select name="prov">
          <?php
            while($row=pg_fetch_array($result_prov)) {
                print "<option  value='" . $row['provincia'] . "'> ".strtoupper($row['provincia'])." </option>";
            }
            pg_result_seek($result_prov,0);   
          ?>
        </select><input type='button' name='inseriscicitta' value='Nuova citt&agrave' onclick=location.href='home.php?action=inseriscicitta&ret=eventi' /><br />
        <label for="citta"> Citt&agrave:</label>
        <select name="citta">
          <?php
            while($row=pg_fetch_array($result_citta)) {
               print "<option  value='" . $row['nome'] . "'>  ".ucfirst($row['nome'])."</option>";
            }
            pg_result_seek($result_citta,0);
          ?>
        </select><br />
        <label for="via">Via:</label> 
        <input type="text" id="nome" name="via"  maxlength="15" required="required" value='<?php print "$via" ?>' /><br />
        <label for="data">Data:</label> 
        <input type="date" id="nome" name="data" required="required" value='<?php print "$data" ?>' /><br/><br />
        <?php
        if($msg!="Attenzione:<br/>") echo "<div class='error'><br/>".$msg."</div><br /><br /><br />";
        ($_SESSION['vip']==true)?
        print "<input type='submit' value='Crea Evento' align='right' name='aggiungievento' /><br /><br />":
        print "<input type='submit' value='Crea Evento' align='right' name='aggiungievento' disabled /><br /><br />";
        ?>
    </form><br/>
        
        
<?php

    $query_evento = "SELECT ora_creazione, data_creazione, idevento, organizzatore, nome, tipo, data, via, get_nomecitta(luogo) AS citta, get_provcitta(luogo) AS prov
                    FROM evento
                    WHERE organizzatore = '$usr'
                    ORDER BY data_creazione DESC, ora_creazione DESC";  
    $result_evento = pg_query($conn, $query_evento);


    $str_evento = "<br/><h4> Eventi Organizzati </h4>     
                    <table id='profilotable'>";
                    
    $i = 0;                
    while($row=pg_fetch_array($result_evento)){
    $i++;
        $idevento = $row['idevento'];                              
        $row['prov']= strtoupper($row['prov']);
        $row['citta']= ucfirst($row['citta']);
        $row['via']=ucfirst($row['via']);


        $str_evento .=  "<tr><td><br/></td></tr>
                         <tr><th>Nome:</th> <td>".$row['nome']."</td></tr> 
                         <tr><th>Tipo:</th> <td>".$row['tipo']."</td></tr> 
                         <tr><th>Organizzatore:</th> <td>".$row['organizzatore']."</td></tr> 
                         <tr><th>Provincia:</th> <td>".$row['prov']."</td></tr> 
                         <tr><th>Citta:</th> <td>".$row['citta']."</td></tr> 
                         <tr><th>Via:</th> <td>".$row['via']."</td></tr> 
                         <tr><th>Data:</th> <td>".$row['data']."</td></tr>" ;
                         
        // Cerco gli invitati all'evento
        $query_invitati = "SELECT invitato, stato FROM invito WHERE evento = '$idevento' ORDER BY stato, invitato";
        $result_invitati = pg_query($conn, $query_invitati);
                         
		$str_invitati_s = '<ul class="hobby">';
		$str_invitati_n = '<ul class="hobby">';
		$str_invitati_a = '<ul class="hobby">';
                
                
        while($row_invitati=pg_fetch_array($result_invitati)){
                switch($row_invitati['stato']){
                    
                    case ('s'): $str_invitati_s .= '<li>' . $row_invitati['invitato'] . '</li>';
                                break;
                    case ('a'): $str_invitati_a .= '<li>' . $row_invitati['invitato'] . '</li>';
                                break;
                    case ('n'): $str_invitati_n .= '<li>' . $row_invitati['invitato'] . '</li>';
                                break;
                    break;
                }
	}
        
         if($str_invitati_s == '<ul class="hobby">') $str_invitati_s = "Nessuno";
         if($str_invitati_n == '<ul class="hobby">') $str_invitati_n = "Nessuno";
         if($str_invitati_a == '<ul class="hobby">') $str_invitati_a = "Nessuno";


            $str_evento .= "<tr><th>Inviti</th></tr>
                            <tr><th>- accettati :</th><td>".$str_invitati_s."</td>
                            <tr><th>- in attesa :</th><td>".$str_invitati_a."</td>
                            <tr><th>- rifiutati :</th><td>".$str_invitati_n."</td>
                            <tr><td><form action='home.php?action=gestevento&orgnz=$usr&idevento=$idevento' method='post'> </td>
                            <tr><td><input type='submit' name='gestisci' value='Gestisci Evento' /></td>
                                <td> <input type='submit' name='eliminaevento' value='Elimina evento' /></form></td>
                                </tr><td><br/></td></tr>"; 
        
        

    }
    if($i==0) $str_evento .= "<tr><td> Non hai creato nessun evento </td></tr>";
    $str_evento .= "</table><br /><br /><br />";
    
    echo $str_evento."<br />";

?>