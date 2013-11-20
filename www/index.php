<?php require("pg_conn.php"); ?>

<html>
<head>
    <title >MiniFB</title> 
    <link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
    
<div id="minifb">
        
    <div id="header"> <h1>Mini FB </h1>
        <div class ="login"> <?php  require("login.php"); ?> </div>
    </div>
              
    <div id="wrapperindex">
        <div id="menu"></div>
        <div id="main">
        <?php require("registrazione.php");
        
        if(isset($_GET['action']) && $_GET['action']== 'recovery')
        require("recovery.php");
        
        if(isset($_GET['recovery'])){ 
            if($_GET['recovery']=='si')
            echo "<div class='error'>La password e' stata inviata alla mail da te indicata.</div>";
            else
            echo "<div class='error'> Attenzione: la mail inserita non risulta registrata.</div>";
        }
        
         ?>                 
        </div>
    </div>
    
    <div id="footer"><img src='logo.png'></div>
        
</div>

</body>
</html>


