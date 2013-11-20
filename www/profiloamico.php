<?php
 
    $usr = $_GET['amico'];
    $conn = db_connect();
    
    $query = "SELECT nome, vip, cognome, utente, data_nascita, sesso,
              get_nomecitta(citta_nascita) AS citta_nascita, get_provcitta(citta_nascita) AS prov_nascita,
              get_nomecitta(citta_residenza) AS citta_residenza, get_provcitta(citta_residenza) AS prov_residenza
              FROM utente JOIN profilo ON email = utente
              WHERE utente = '$usr'";
    $query_hobby = "SELECT nome_hobby FROM interessi WHERE utente = '$usr'";
   
    $result = pg_query($conn, $query);
    $result_hobby = pg_query($conn, $query_hobby);

    # Profilo #  
    $str_profilo = "<h4> Profilo utente </h4>     
                    <table id='profilotable'>
                    <tr><th colspan='11'><h4> Informazioni personali: </h4></th></tr>";
    while($row=pg_fetch_array($result)){
        ($row['vip']=='f')?$vip='no': $vip='si';
        ($row['prov_nascita']=='')?$row['prov_nascita']='Non specificata': $row['prov_nascita']= strtoupper($row['prov_nascita']);
        ($row['citta_nascita']=='')?$row['citta_nascita']='Non specificata': $row['citta_nascita']= ucfirst($row['citta_nascita']);
        ($row['prov_residenza']=='')?$row['prov_residenza']='Non specificata': $row['prov_residenza']= strtoupper($row['prov_residenza']);
        ($row['citta_residenza']=='')?$row['citta_residenza']='Non specificata': $row['citta_residenza']= ucfirst($row['citta_residenza']);
        ($row['sesso']=='')?$row['sesso']='Non specificato': '';

        $str_profilo .=  "<tr><th>Nome:</th> <td>".$row['nome']."</td></tr> 
                         <tr><th>Cognome:</th> <td>".$row['cognome']."</td></tr> 
                         <tr><th>E-mail:</th> <td>".$row['utente']."</td></tr> 
                         <tr><th>Vip:</th> <td> ".$vip."</td></tr>      
                         <tr><th>Sesso:</th> <td>".$row['sesso']."</td></tr> 
                         <tr><th>Provincia di nascita:</th> <td>".$row['prov_nascita']."</td></tr> 
                         <tr><th>Citt&agrave di nascita:</th> <td>".$row['citta_nascita']."</td></tr> 
                         <tr><th>Provincia di residenza:</th> <td>".$row['prov_residenza']."</td></tr> 
                         <tr><th>Citt&agrave di residenza:</th> <td>".$row['citta_residenza']."</td> <td></tr> 
                         <tr><th>Data di nascita:</th> <td>".$row['data_nascita']."</td></tr>" ;
                         
		$str_hobby = '<ul class="hobby">';
		//$i_hobby = pg_num_rows($result_hobby);
		$i = 0;
        while($row_hobby=pg_fetch_array($result_hobby)){
			$str_hobby .= '<li>' . $row_hobby['nome_hobby'] . '</li>';
			/*if($i+1 < $i_hobby) {
				$str_hobby .= ",";
			}
			$str_hobby .= '</span>';
			$i++;*/
                        $i++;
		}
        $str_profilo .= "<tr><th>Hobby :</th><td>".$str_hobby."</td></tr>";

    }
    $str_profilo .= "</table><br /><br /><br />"; 
    
    if($i==0) $str_hobby = "Non specificati";
    # Scuole #  

    $query = "SELECT nome, tipo, get_nomecitta(sede) AS citta,  get_provcitta(sede) AS prov, data_inizio, data_fine
              FROM frequenza JOIN scuola ON scuola = idscuola
              WHERE studente = '$usr'"; 
      
    $result = pg_query($conn, $query);
    $num_scuole = pg_numrows($result);
    
    
    $str_scuole = "<h4> Scuole frequentate </h4>     
                   <table id = 'scuolatable'>";
    if($num_scuole == 0) $str_scuole .= "<tr> <td> Non ci sono frequentazioni.</td></tr> ";
    else{
      while($row=pg_fetch_array($result)){
        ($row['data_fine']=='')?$row['data_fine']='Non specificata': '';
           $str_scuole .= "<tr><th> Nome:</th> <td>".ucfirst($row['nome'])."</td> <th>Tipo:</th> <td>".$row['tipo']."</td> <th>Citt&agrave:</th> <td>".ucfirst($row['citta'])."</td>
                           <th>Prov:</th> <td>".strtoupper($row['prov'])." </td> <th>Inizio:</th> <td>".$row['data_inizio']."</td> <th>Fine:</th> <td>".$row['data_fine']."</td></tr> ";     
      }
    }
    $str_scuole .= "</table><br /><br /><br />";
    
    
    # Stampa di tutto #
    
    echo $str_profilo.$str_scuole;
    
  
?>