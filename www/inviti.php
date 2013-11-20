<?php

    $usr = $_SESSION['email'];
    $conn = db_connect();

    $query_evento = " SELECT stato, idevento, organizzatore, nome, tipo, data, via, get_nomecitta(luogo) AS citta, get_provcitta(luogo) AS prov
                      FROM evento JOIN invito ON evento = idevento
                      WHERE invitato = '$usr'
                      ORDER BY stato";              
    $result_evento = pg_query($conn, $query_evento);
    
    $str_evento_s = "<br/><h4> Inviti accettati </h4>     
                   <table id='profilotable'>";
    $str_evento_n = "<br/><h4> Inviti Rifiutati </h4>     
                   <table id='profilotable'>";
    $str_evento_a = "<br/><h4> Inviti in attesa </h4>     
                   <table id='profilotable'>";
  
    $si = 0; $no = 0; $a = 0;                         
    while($row=pg_fetch_array($result_evento)){
        $row['prov']= strtoupper($row['prov']);
        $row['citta']= ucfirst($row['citta']);
        $row['via']= ucfirst($row['via']);
             
    
        if($row['stato'] == 's'){ // INVITI ACCETTATI
                $idevento = $row['idevento'];
                $si++;
            
                $str_evento_s .=" <tr><td></td></tr>
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

                $str_evento_s .= "<tr><th>Inviti </th><td></td></tr>
                                  <tr><th>- accettati :</th><td>".$str_invitati_s."</td>
                                  <tr><th>- in attesa :</th><td>".$str_invitati_a."</td>
                                  <tr><th>- rifiutati :</th><td>".$str_invitati_n."</td>
                                  <tr><td><br/></td></tr>
                                  <tr><td><form action='home.php?action=gestevento&idevento=$idevento' method='post'>
                                  <input type='submit' name='eliminainvito' value='Elimina partecipazione' /></form></td></tr>
                                  <tr><td><br/></td><td><br/></td></tr>";                
        }//END if SI
        
        if($row['stato'] == 'n'){ // INVITI RIFIUTATI
                $idevento = $row['idevento'];
                $no++;
            
                $str_evento_n .=  "<tr><td><br/></td></tr>
                         <tr><th>Nome:</th> <td>".$row['nome']."</td></tr> 
                         <tr><th>Tipo:</th> <td>".$row['tipo']."</td></tr> 
                         <tr><th>Organizzatore:</th> <td>".$row['organizzatore']."</td></tr> 
                         <tr><th>Provincia:</th> <td>".$row['prov']."</td></tr> 
                         <tr><th>Citta:</th> <td>".$row['citta']."</td></tr> 
                         <tr><th>Via:</th> <td>".$row['via']."</td></tr> 
                         <tr><th>Data:</th> <td>".$row['data']."</td></tr>" ;
                         
                $str_evento_n .= "<tr><td><form action='home.php?action=gestevento&idevento=$idevento' method='post'><td></td></tr>
                                <tr><td><input type='submit' name='eliminarifiuto' value='Elimina rifiuto' /></form></td></tr>
                                <tr><td><br/></td><td><br/></td></tr>";
                
	}// END if NO
        
        
        if($row['stato'] == 'a'){ // INVITI IN ATTESA
                $idevento = $row['idevento'];
                $a++;
            
                $str_evento_a .=  "<tr><td><br/></td></tr>
                         <tr><th>Nome:</th> <td>".$row['nome']."</td></tr> 
                         <tr><th>Tipo:</th> <td>".$row['tipo']."</td></tr> 
                         <tr><th>Organizzatore:</th> <td>".$row['organizzatore']."</td></tr> 
                         <tr><th>Provincia:</th> <td>".$row['prov']."</td></tr> 
                         <tr><th>Citta:</th> <td>".$row['citta']."</td></tr> 
                         <tr><th>Via:</th> <td>".$row['via']."</td></tr> 
                         <tr><th>Data:</th> <td>".$row['data']."</td></tr>" ;
                         
                $str_evento_a .= "<tr><td><form action='home.php?action=gestevento&idevento=$idevento' method='post'></td></tr>
                                <tr><td><input type='submit' name='accettainvito' value='Accetta invito' /></td> 
                                <td><input type='submit' name='rifiutainvito' value='Rifiuta invito' /></form></tr>
                                <tr><td><br/></td><td><br/></td></tr>";
                
	}// END if ATTESA
        

    }
    if($si==0) $str_evento_s .= "<tr><td> Non hai inviti accettati. </td></tr>";
    $str_evento_s .= "</table><br /><br />";
    
    if($no==0) $str_evento_n .= "<tr><td> Non hai inviti rifiutati. </td></tr>";
    $str_evento_n .= "</table><br /><br />";
    
    if($a==0) $str_evento_a .= "<tr><td> Non hai inviti in attesa. </td></tr>";
    $str_evento_a .= "</table><br /><br />";

    
    echo $str_evento_s."<br />";
    echo $str_evento_n."<br />";

    echo $str_evento_a."<br />";



?>