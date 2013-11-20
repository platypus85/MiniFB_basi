<?php require("sezioni.php");?>

<html>
<head>
    <title>MiniFB</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
    

<div id="minifb">
        
    <div id="header"><h1>MiniFB</h1>
        <div class = 'login'> <?php sessione_utente();  logout(); ?> </div>
    </div>
    
    <div id="wrapper"> 
        <div id="menu"> <?php menu();?> </div>
        <div id="main">
        <?php
        
        if(!isset($_GET['action'])) {
        welcome();
        } 
                
        if(isset($_GET['action']) && $_GET['action']== 'profilo'){
        require("profilo.php");
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'modifprofilo'){
        require("gest_profilo.php");
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'inserisciscuola'){
        require("inserisciscuola.php");
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'inseriscicitta'){
        require("inseriscicitta.php");
        }
                
        if(isset($_GET['action']) && $_GET['action']== 'amici'){      
        require("amici.php");      
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'profiloamico'){      
        require("profiloamico.php");      
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'bachecaamico'){      
        require("bachecaamico.php");      
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'richiediamicizia'){      
        require("richiediamicizia.php");      
        }
                          
        if(isset($_GET['action']) && $_GET['action']== 'bacheca'){
        require("bacheca.php");
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'gestpost'){
        require("gest_bacheca.php");
        }
        
        if(isset($_GET['action']) && $_GET['action']== 'ricerca'){
        require("ricerca.php"); }
        
        if(isset($_GET['action']) && $_GET['action']== 'eventi'){
        require("eventi.php"); }
        
        if(isset($_GET['action']) && $_GET['action']== 'gestevento'){
        require("gest_eventi.php"); }
        
        
        if(isset($_GET['action']) && $_GET['action']== 'inviti'){
        require("inviti.php"); }
        
        if(isset($_GET['action']) && $_GET['action']== 'gestinviti'){
        require("gest_eventi.php"); }
        ?>
        
        </div>
    </div>
    
    <div id="footer"></div>
</div>


</body>
</html>
