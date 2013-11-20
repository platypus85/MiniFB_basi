<?php
    $msg = "";

if(isset($_POST['loggami'])){
  
    $usr = htmlentities(trim($_POST['email']));
    $psw = htmlentities(trim($_POST['password']));
    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";
    $conn = db_connect();
    
    ## Controlli form log-in ##

    $querymail = "SELECT count(*) FROM utente WHERE email = '$usr'";
    $resultmail = pg_query($conn, $querymail);
    $rowmail = pg_fetch_array($resultmail);
    
    $query = "SELECT email FROM utente WHERE email='$usr' AND psw = '$psw'";
    $result = pg_query($conn, $query);
    $row = pg_fetch_array($result);
    

    //  E-mail 
    if($rowmail[0]==0) 
      $msg = "La mail inserita non &egrave registrata.";
      
    // Controllo Password    
    if($rowmail[0]==1 && $row[0] != $usr)
      $msg = "La password inserita non &egrave corretta";
    
   // Controllo formato E-mail
    if(!preg_match($pattern, $usr))
      $msg = "Il formato della mail non &egrave corretto";
  
    if($msg==""){ // Form compilato correttamente
      $usr = pg_escape_string($usr);
      $queryvip = "SELECT count(*) FROM amicizia WHERE (richiedente= '$usr' OR invitato='$usr') AND stato='s' ";
      $resultvip = pg_query($conn, $queryvip);
      $rowvip=pg_fetch_array($resultvip);
    
         if($rowvip[0]>=10){
	    $vip = true;
            $queryvip = "UPDATE utente SET vip = true WHERE email = '$usr'"; 
         }
         else{ 
            $queryvip = "UPDATE utente SET vip = false WHERE email = '$usr'"; 
	    $vip = false;
         }
         $resultvip = pg_query($conn, $queryvip);

      session_start();
      $_SESSION['email']=$usr;
      $_SESSION['password']=$psw;
      $_SESSION['vip']=$vip;
    
      header("Location: home.php");  
    }
} //end isset($_POST['loggami']){

?>

    <table id="logintable">
    <tr><form method="POST" action= "<?php echo $_SERVER['PHP_SELF']; ?>">  
        <td>E-mail:
        <input type="text" id="email" name="email" maxlength="35" required='required'/></td>
        <td>Password
        <input type="password" id="password" name="password"  maxlength="25" required='required'/></td>
        <td><input type="submit" value="login" name="loggami"/></td>
    </form></tr><td> <?php if($msg!="") print "<div class='error'>" .$msg . "</div>";?></td>
    <td><a href=?action=recovery> <div class = 'pass'> Password dimenticata?</div></a></td></td></tr><br />
    </table>


	
