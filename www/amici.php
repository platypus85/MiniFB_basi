<?php
        
    $usr = $_SESSION['email'];
    $conn = db_connect();
    
    // Selezione degli amici
    $query = "SELECT invitato AS amico, stato, nome, cognome
              FROM amicizia JOIN profilo ON invitato = utente
              WHERE richiedente = '$usr' 
              UNION
              SELECT richiedente AS amico, stato, nome, cognome
              FROM amicizia JOIN profilo ON richiedente = utente
              WHERE invitato = '$usr'
              ORDER BY stato, amico";
    $result = pg_query($conn, $query);
    
    
    # Amicizie accettate #
    
    $str_amici = "<table id='amicitable' cellspacing='0' cellpadding='3' border='0'>
		  <tr><th colspan='7'><h4> Amicizie accettate: </h4></th></tr>";
                  
    $check = 0;
    $str_amici_row = '';

    while($row=pg_fetch_array($result)){
        if($row['stato']=='s'){
         $check++; 
         $str_amici_row .= "<tr>
			    <td>".$row['nome']."</td>
			    <td>".$row['cognome']."</td>
			    <td>".$row['amico']."</td>
			    <td> <form action='home.php?action=profiloamico&amico=".$row['amico']."' method='post'> <input type='submit' value='Profilo'></form> </td>
			    <td> <form action='home.php?action=bachecaamico&amico=".$row['amico']."' method='post'> <input type='submit' value='Bacheca'></form> </td>
			    <td> <form action='gest_amicizie.php?ut_el=".$row['amico']."&ut1=$usr' method='post'> <input type='submit' value='Elimina'></form>  </td>
                           </tr>";    
        }
    }
	
        if($check == 0){
		$str_amici .= "<tr><td colspan='7'>Non hai nessun amico. Vai alla sezione 'Ricerca' per trovare altri utenti.</td></tr>";
         }else{
		$str_amici .= "<tr><th>Nome:</th><th>Cognome:</th><th>E-mail:</th></tr>" . $str_amici_row;
	}
	
    pg_result_seek($result,0);    
   
    # Amicizie in in attesa #
    
    $str_amici .= "<tr><th colspan='7'><h4> Amicizie in attesa: </h4></th></tr>";
    $check = 0;
    $str_amici_row = '';
	
    while($row=pg_fetch_array($result)){
        if($row['stato']=='a'){
         $check++; 
         $str_amici_row .= "<tr>
			    <td>".$row['nome']."</td>
			    <td>".$row['cognome']."</td>
			    <td> ".$row['amico']." </td>";
                               
         $amico = $row['amico'];
         $query_check = "SELECT richiedente, stato FROM amicizia WHERE richiedente = '$usr' AND invitato = '$amico'";
         $result_check = pg_query($conn, $query_check);
         $row_check = pg_fetch_array($result_check);
         ($row_check['richiedente'] == $usr )? //l'utente loggato è chi richiede l'amicizia?
         // si mostra il tasto disabilitato + eliminazione richiesta
         $str_amici_row  .= "<td> <input type='button' name='richiestaattesa' value='Richiesta in attesa' disabled='disabled'> </td>
                             <td> <form action='gest_amicizie.php?ut1=".$row['amico']."&richiesta_no=$usr' method='post'> <input type='submit' value='Emilina richiesta'></form>":
        // altrimenti mostra accetta+rifiuta
         $str_amici_row  .= "<td> <form action='gest_amicizie.php?ut1=".$row['amico']."&ut_si=$usr' method='post'> <input type='submit' value='Accetta'></form>  </td> 
                             <td> <form action='gest_amicizie.php?ut1=".$row['amico']."&ut_no=$usr' method='post'> <input type='submit' value='Rifiuta'></form>  </td> ";
	 $str_amici_row  .= "</tr>";							   
         }
    }
	
	if($check == 0){
		$str_amici .= "<tr><td colspan='7'>Non hai richieste di amicizia in sospeso.</td></tr>";
	}else{
		$str_amici .= "<tr><th>Nome:</th><th>Cognome:</th><th>E-mail:</th></tr>" . $str_amici_row;
	}
	
    pg_result_seek($result,0);
	
    # Amicizie rifiutate #

    $str_amici .= "<tr><th colspan='7'><h4> Amicizie rifiutate: </h4></th></tr>";
    $check = 0;
	$str_amici_row = '';
	
    while($row=pg_fetch_array($result)){
        if($row['stato']=='n'){
         $check++; 
         $str_amici_row .= "<tr>
                           <th>Nome:</th> 
			   <td>".$row['nome']."</td>
                           <th>Cognome:</th> 
			   <td>".$row['cognome']."</td>
                           <th>E-mail:</th> 
			   <td> <a href> ".$row['amico']." </a> </td>";
         $amico = $row['amico'];
         $query_check = "SELECT richiedente, stato FROM amicizia WHERE richiedente = '$usr' AND invitato = '$amico'";
         $result_check = pg_query($conn, $query_check);
         $row_check = pg_fetch_array($result_check);
         ($row_check['richiedente'] == $usr )? // l'utente loggato ha richiesto l'amicizia rifiutata?
         // si: non fare niente
         $str_amici_row .= "<td> </td>":
         // no: mostra il tasto : elimina rifiuto
         $str_amici_row .= "<td> <form action='gest_amicizie.php?ut1=".$row['amico']."&richiesta_no=$usr' method='post'> <input type='submit' value='Emilina rifiuto'></form></td>";
		 
	 $str_amici_row .= "</tr>";
         }
    }
	
    if($check == 0){  
	    $str_amici .= "<tr><td colspan='7'>Non hai richieste di amicizie rifiutate.</td></tr>";
    }else{
	    $str_amici .= "<tr><th>Nome:</th><th>Cognome:</th><th>E-mail:</th></tr>" . $str_amici_row;
    }
	
    $str_amici .= "</table>";
    echo  $str_amici;     
?>

