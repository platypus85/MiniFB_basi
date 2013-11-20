    
    <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>">
      <label for="tipo">Scegli il tipo di post: </label>
        <input type="radio" id="tipo" name="tipo" value="m" checked /> Messaggio di stato </input>
        <input type="radio" id="tipo" name="tipo" value="n" /> Nota </input>
      <textarea name="post" maxlength="1000" required="required"></textarea><br />
	<input type="submit" name="inseriscipost" value='Inserisci' /><br />
    </form>
    <br /><br />

        
<?php

    $usr = $_SESSION['email'];
    $conn = db_connect();
    
    $query="SELECT idpost, utente, data as datanull, data, ora, testo, tipo FROM post WHERE utente = '$usr'
             UNION
            SELECT idpost, utente, data as datanull, data, ora, testo, tipo FROM post
             WHERE (idpost, utente) IN (SELECT idpost, taggante FROM tag WHERE taggato = '$usr')
             UNION
            SELECT idevento , organizzatore AS utente, data as dataevento,  data_creazione as data, ora_creazione AS ora, nome AS testo, tipo FROM evento WHERE organizzatore = '$usr'
             UNION
            SELECT idevento, organizzatore AS utente, data as dataevento, data_creazione as data, ora_creazione AS ora,  nome AS testo, tipo FROM evento 
             WHERE (idevento) IN (SELECT evento invitato FROM invito WHERE invitato = '$usr')
             ORDER BY data DESC, ora DESC";
    $num_res = pg_query($conn, $query);
    $resnum = pg_numrows($num_res);
    


    

if(isset($_POST['inseriscipost'])){
    $post = htmlentities(trim($_POST['post']));    
    $len_post =  strlen($post);
    $tipo = $_POST['tipo'];
    $str_error = "";
    
    
    # Controlli compilazione textarea #

    if($len_post==0)
        $str_error =  "Attenzione. Il messaggio non puo' contenere solo spazi vuoti. <br /><br />";
    
    if($len_post>=100 && $tipo=='m')
        $str_error =  "Attenzione. I messaggi di stato hanno lunghezza massima di 100 caratteri. <br /><br />";
    
    if($len_post>=1000 && $tipo=='n')
        $str_error =  "Attenzione. Le note hanno lunghezza massima di 1000 caratteri. <br /><br />";
     
    if($_POST['post']=="")
        $str_error =  "Attenzione! Compila il form prima di inviare. <br /><br />"; 
    
    if($str_error == ""){ // Textarea compilata correttamente
        $testo = pg_escape_string($post);
  
        switch($tipo){
          case('m'): $query_msg= "SELECT ins_msg('$usr','$testo')";
          break;
          case('n'): $query_msg= "SELECT ins_nota('$usr','$testo')";
          break;
        }
        
        $result_msg = pg_query($conn,$query_msg);
        header("Location: home.php?action=bacheca");
        }
        
    else echo "<div class='error'>".$str_error ."</div>";"<br />"; 
}


if(isset($_POST['next10'])){

    $limit = $_GET['limit'] + 8;
    
    $query .=" LIMIT $limit OFFSET 0";
    $result = pg_query($conn, $query);
    
}

else{

    $limit = 8;
    $query .=" LIMIT $limit OFFSET 0";
    $result = pg_query($conn, $query);
    
}
    

    $query_nc = "SELECT nome, cognome FROM profilo WHERE utente = '$usr'";
    $result_nc = pg_query($conn, $query_nc);
    $row_nc = pg_fetch_array($result_nc);
    
    $str_bacheca = "<h3> Bacheca di ".ucwords($row_nc['nome'])." ".ucwords($row_nc['cognome'])." </h3><br />";
    $num = pg_numrows($result);
    if($num == 0)  $str_bacheca .= "Non hai ancora nessun messaggio in bacheca."; // Non ci sono post in bacheca
    
    
    
    while($row=pg_fetch_array($result)){ // Ci sono post in bacheca
        $utente = $row['utente'];
        $idpost = $row['idpost'];
        
        # header post #  
        ($row['utente']==$usr)?
        $str_bacheca .= "<div id='headerpost'>[".$row['data']."] ".$usr." ha scritto: </div> ": // Il post e dell'utente loggato
        $str_bacheca .= "<div id='headerpost'>[".$row['data']."] ".$row['utente']." ha scritto: </div>"; // Il post e' di un altro utente
     
        # body post #
        switch(trim($row['tipo'])){
            case('m'):          $str_bacheca .= "<div id='bodymsg'>".$row['testo']." <br/> 
                                                 </div>";
                                break;
            case('n'):          $str_bacheca .= "<div id='bodynota'>".$row['testo']." <br/> 
                                                 <div id='taggati'>";
                                         
                                $query_taggati = "SELECT taggato FROM tag WHERE taggante = '$usr' AND idpost='$idpost'";
                                $result_taggati = pg_query($conn,$query_taggati);
            
			        if (pg_num_rows($result_taggati) > 0) {
				    $str_bacheca .= '<hr /><br />Utenti taggati: <br />';
				    $str_bacheca .= '<ul class="utentitag">';
				    while($row_taggati=pg_fetch_array($result_taggati)){
					$str_bacheca .= '<li>' . $row_taggati['taggato'] . '</li>';
				    }
				    $str_bacheca .= '</ul>';
			        }
                                $str_bacheca .="</div></div>";
                                break;
           default:             $row['utente']==$usr?
                                $testo = "Ho creato l'evento \"".$row['testo']."\" ( ".$row['tipo'].") che si terr&agrave il ".$row['data'].".":
                                $testo = "Ciao sei invitato per partecipare all'evento \"".$row['testo']."\" ( ".$row['tipo'].") che si terr&agrave il ".$row['data'].".";
                                $str_bacheca .= "<div id='bodyevento'>".$testo." <br/> 
                                                 </div>";
                                break;        }

     
        
        # Pulsanti #
        
        // Creo le variabili per gestire la stringa str_bachceca
        $utente = $row['utente'];
        $idpost = $row['idpost'];

        if($row['utente']==$usr){
           switch($row['tipo']){
              // Nota dell'utente
              case('n'):    $str_bacheca .= "<form action='home.php?action=gestpost&tgnt=$utente&postid=$idpost' method='post'>
                                             <input type='submit' name='tagga' value='Gestisci Tag' /> 
                                             <input type='submit' name='eliminapost' value='Elimina Nota' /></form><br /><br /><br /><br />"; 
                            break;
              // Msg dell'utente 
              case('m'):    $str_bacheca .= "<form action='home.php?action=gestpost&tgnt=$utente&postid=$idpost'  method='post'>
                                            <input type='submit' name='eliminapost' value='Elimina Messaggio' /></form><br /><br /><br /><br />";
                            break;
              default:      $str_bacheca .= "<form action='home.php?action=eventi'  method='post'>
                                            <input type='submit' name='eventi' value='Vedi evento' /></form><br /><br /><br /><br />";
                            break;
           }
       }else{ // Nota NON dell'utente
           switch($row['tipo']){
              case('n'):    $str_bacheca .= "<form action='home.php?action=gestpost&tgnt=$utente&postid=$idpost'  method='post'>
                            <input type='submit' name='eliminatagricevuto' value='Elimina Tag' /></form><br /><br /><br /><br />";
                            break;
              default:      $str_bacheca .= "<form action='home.php?action=inviti'  method='post'>
                            <input type='submit' name='inviti' value='Visualizza invito' /></form><br /><br /><br /><br />";
                            break;
           }
       }
    } // end while
   
   // Stampo la bacheca
  if($resnum>$limit)
   $str_bacheca .= "<center><form action ='home.php?action=bacheca&limit=$limit' method='post'><input type = 'submit' name='next10' value = 'Visualizza altri post'></form></center>";
   echo $str_bacheca;
 
?>