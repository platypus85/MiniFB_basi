    <table id="passwodrec">
    <form method="POST" action= "recovery.php">
        <tr><th>Inserisci l'e-mail di registrazione:</th></tr>
        <td><input type="text" id="email" name="email" maxlength="35" required='required'/></td></tr>
        <tr><td><br /><input type="submit" value="invia" name="recovery"/></td></tr>
    </form>
    </table><br>   
    
    <?php
    $msg = "";
$res = "";

    
    if(isset($_POST['recovery'])){
    require("pg_conn.php");
    $conn = db_connect();
        $email = $_POST['email'];
       
        $query = "SELECT email, psw FROM utente WHERE email = '$email'";
        $result = pg_query($conn, $query);
        $row = pg_fetch_array($result);
        
        if($row[0]==$email){
            $password = $row[1];
            $res = mail($email, 'Recupero password MiniFB', 'Gentile utente, come richiesto ecco la password dimenticata: '.$password, 'From:  recovery@miniFB.com');
            $msg = "si";
            print $res;
        }
        
        else $msg = "no";
    header("Location: index.php?recovery=$msg");
    }

    
        
    
    
    
    ?>
    
    



    
