<?php

    $usr = $_GET['amico'];
    $usr_sessione = $_SESSION['email'];
    $conn = db_connect();
    $str_bacheca = "<h4> Bacheca di ".$usr."</h4><br>";
    
    // Controlla che siano effettivamente amici
    $query_amicizia = "SELECT get_samic('$usr','$usr_sessione')";
    $result_amicizia = pg_query($conn, $query_amicizia);
    $row_amicizia = pg_fetch_array($result_amicizia);


    $query="SELECT idpost, utente, data as datanull, data, ora, testo, tipo FROM post WHERE utente = '$usr'
             UNION
            SELECT idpost, utente, data as datanull, data, ora, testo, tipo FROM post
             WHERE (idpost, utente) IN (SELECT idpost, taggante FROM tag WHERE taggato = '$usr')
             UNION
            SELECT idevento , organizzatore AS utente, data as dataevento,  data_creazione as data, ora_creazione AS ora, nome AS testo, tipo FROM evento WHERE organizzatore = '$usr'
             UNION
            SELECT idevento, organizzatore AS utente, data as dataevento, data_creazione as data, ora_creazione AS ora,  nome AS testo, tipo FROM evento 
             WHERE (idevento) IN (SELECT evento invitato FROM invito WHERE invitato = '$usr')
             ORDER BY data DESC, ora ASC";
    $num_res = pg_query($conn, $query);
    $resnum = pg_numrows($num_res);
    
        
    switch($row_amicizia[0]){
        case('s'): {
            
            if(isset($_POST['next10'])){
                    $limit = $_GET['limit'] + 8;
                    $query .=" LIMIT $limit OFFSET 0";
                    $result = pg_query($conn, $query);
            }else{
                    $limit = 8;
                    $query .=" LIMIT $limit OFFSET 0";
                    $result = pg_query($conn, $query);
            }
    
            
                $query_nc = "SELECT nome, cognome FROM profilo WHERE utente = '$usr'";
                $result_nc = pg_query($conn, $query_nc);
                $row_nc = pg_fetch_array($result_nc);
            
                $str_bacheca = "<h4> BACHECA DI ".strtoupper($row_nc['nome'])." ".strtoupper($row_nc['cognome'])." </h4><br />";
                $num = pg_numrows($result);
               if($num == 0)  $str_bacheca .= "L'utente non ha ancora scritto nessun messaggio in bacheca."; // Non ci sono post in bacheca
    
    
    
    while($row=pg_fetch_array($result)){ // Ci sono post in bacheca
        $utente = $row['utente'];
        $idpost = $row['idpost'];
        
        # header post #  
        ($row['utente']==$usr)?
        $str_bacheca .= "<div id='headerpost'>[".$row['data']."] ".$usr." ha scritto: </div>": // Il post e dell'utente loggato
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
                                break;        

       }
       $str_bacheca .= "<br/><br/><br/>";

    } // end while
   if($resnum>$limit)
   $str_bacheca .= "<center><form action ='home.php?action=bachecaamico&amico=$usr&limit=$limit' method='post'><input type = 'submit' name='next10' value = 'Visualizza altri post'></form></center>";
   echo $str_bacheca;
       $str_bacheca .="<br/><br/>";
   
   // Stampo la bacheca 
        
                    }
                    break;
        case ('a'): $str_bacheca = "<br />Non e' possibile visualizzare la bacheca poiche' la richiesta di amicizia e' ancora in attesa";
                    break;
        case ('n'): $str_bacheca = "<br />Non e' possibile visualizzare la bacheca poiche' la richiesta di amicizia e'stata rifiutata";
                    break;
        case (null): $str_bacheca = "<br /> Non e' possibile visualizzare questa bacheca";
                    break;
        }
        
        
        
        


?>

   

   



