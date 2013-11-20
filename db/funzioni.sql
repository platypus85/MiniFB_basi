-- ** Inserimento MESSAGGIO DI STATO ** ok

CREATE OR REPLACE FUNCTION ins_msg(IN u VARCHAR(35), IN t VARCHAR(100)) RETURNS void AS
$$

BEGIN

INSERT INTO post(idpost, utente, data, ora, testo, tipo) 
VALUES((max_idpost(u)+1), u, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, t, 'm');

   
END;
$$ LANGUAGE plpgsql; 


-- Es. SELECT ins_msg('alberto.cami@gmail.com','tengounaminchiapiccola');

  
----------------------------------------------------------------------------------------------------------------------------------------

-- ** Inserimento NOTA ** ok

CREATE OR REPLACE FUNCTION ins_nota(IN u VARCHAR(35), IN t VARCHAR(1000)) RETURNS void AS
$$

BEGIN

INSERT INTO post(idpost, utente, data, ora, testo, tipo) 
VALUES((max_idpost(u)+1), u, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, t, 'n');

   
END;
$$ LANGUAGE plpgsql; 


-- Es. SELECT ins_nota('alberto.cami@gmail.com','taggo');


----------------------------------------------------------------------------------------------------------------------------------------

--** Eliminazione di un POST **-- ok

CREATE OR REPLACE FUNCTION cancella_post(IN u VARCHAR(35), IN i INTEGER) RETURNS void AS
$$
BEGIN
DELETE FROM post
WHERE utente = u AND idpost = i;
END;

$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------------------------------------------------------------

--** Inserimento di un TAG (subito dopo aver creato un POST)** ok
-- Ritorna 's' nel caso in cui il tag sia stato inserito.

CREATE OR REPLACE FUNCTION ins_tag(IN taggante VARCHAR(35), IN taggato VARCHAR(35)) RETURNS CHAR AS
$$
DECLARE temp CHAR(1); 
        max INTEGER;

BEGIN

temp:=   get_samic(taggante,taggato);

IF temp='s'   THEN             
              INSERT INTO tag VALUES(taggante, max_idpost(taggante), taggato);
ELSE temp = 'n';

END IF;
RETURN temp;
END;
$$ LANGUAGE plpgsql; 


--SELECT ins_tag('alberto.cami@gmail.com','adalberto.muffa@gmail.com');

----------------------------------------------------------------------------------------------------------------------------------------

--** Interrogazione dell'ID massimo di un post di un utente** OK

CREATE OR REPLACE FUNCTION max_idpost(IN u VARCHAR(35)) RETURNS INTEGER AS
$$
DECLARE maxid INTEGER;
       temp INTEGER;

BEGIN
temp:= (SELECT count(*)
        FROM post
        WHERE utente = u
        );
        
IF temp = 0 THEN maxid = temp;
END IF;

IF temp != 0 THEN
maxid:= (SELECT max(idpost)
         FROM post 
         WHERE utente = u);
END IF;
RETURN maxid;
END;
$$ LANGUAGE plpgsql; 

--SELECT max_idpost('alberto.cami@gmail.com');

----------------------------------------------------------------------------------------------------------------------------------------

-- ** Registrazione di un UTENTE ** OK

CREATE OR REPLACE FUNCTION registra(IN mail VARCHAR(35), IN pass VARCHAR(15), IN nom VARCHAR(20), IN cogn VARCHAR(20), IN sex VARCHAR(1), IN citta CHAR(20), IN pr CHAR(2), IN nasc DATE) RETURNS void AS
$$

BEGIN
INSERT INTO utente (email,psw, vip) VALUES (mail,pass, false);


IF (citta = 'Non specificata') AND (pr = 'Non specificata')  
THEN
INSERT INTO profilo (utente ,nome , cognome, data_nascita, sesso) VALUES (mail, nom, cogn , nasc, sesso);
END IF;

IF (citta != 'Non specificata') AND (pr != 'Non specificata')  
THEN
INSERT INTO profilo (utente ,nome , cognome, citta_nascita, data_nascita, sesso) VALUES (mail, nom, cogn ,get_idcitta(citta,pr), nasc, sex);
END IF;

END;
$$ LANGUAGE plpgsql; 

----------------------------------------------------------------------------------------------------------------------------------------


--** Richiesta di amicizia ** OK
-- Restituisce 0 se hai inserito l'amicizia.
-- Restituisce 1 se l'amicizia e' gia' presente.

CREATE OR REPLACE FUNCTION richiesta(IN u1 VARCHAR(35), IN u2 VARCHAR(35)) RETURNS integer AS
$$

DECLARE temp INTEGER;

BEGIN

temp:=  (SELECT count(*)
         FROM amicizia
         WHERE (invitato = u1 AND richiedente = u2) OR (richiedente = u1 AND invitato = u2)
         );


IF temp = 0  -- non ci sono tuple dove gli amcici sono u1, e u2
THEN 
INSERT INTO amicizia VALUES (u1,u2,'a');
END IF;

RETURN temp;


END;
$$ LANGUAGE plpgsql; 

----------------------------------------------------------------------------------------------------------------------------------------

--** Accettazione/rifuto di una richiesta di amicizia. ** OK
-- Setta lo stato di un'amicizia a 's' o 'n'
CREATE OR REPLACE FUNCTION gest_amicizia(IN rich VARCHAR(35), IN inv VARCHAR(35), IN temp CHAR(1)) RETURNS void AS
$$
DECLARE controllo CHAR;
BEGIN

-- controllo
controllo:= get_samic(rich, inv);

IF (temp = 's' OR  temp ='n') AND (controllo!=temp) THEN
UPDATE amicizia
SET stato = temp
WHERE (richiedente = rich AND invitato = inv) OR (richiedente = inv AND invitato = rich);
END IF;


END;
$$ LANGUAGE plpgsql; 

----------------------------------------------------------------------------------------------------------------------------------------

-- ** Elimiazione di un UTENTE ** OK
--Restituisce 0 se l'utente e' stato eliminato, 1 altrimenti.

CREATE OR REPLACE FUNCTION elimina_ut(IN ut VARCHAR(35)) RETURNS INTEGER AS
$$

DECLARE temp INTEGER;

BEGIN


temp := (SELECT count(*)
         FROM amicizia
         WHERE (richiedente = ut OR invitato = ut) AND stato != 'n'
	);
		
IF temp = 0 THEN
DELETE FROM utente
WHERE email = ut;
END IF; 

RETURN temp;

END;
$$ LANGUAGE plpgsql; 
  
  
----------------------------------------------------------------------------------------------------------------------------------------

-- ** Elimiazione di un'AMICIZIA ** OK
-- Non elimina l'amicizia se lo stato e' "in attesa" (a)


CREATE OR REPLACE FUNCTION elimina_amic(IN u1 VARCHAR(35), IN u2 VARCHAR(35)) RETURNS void AS
$$

DECLARE temp CHAR;

BEGIN

temp := (SELECT stato
	 FROM amicizia
	 WHERE (richiedente = u1 AND invitato = u2) OR (invitato = u1 AND richiedente = u2)
	);
		
IF temp = 's' OR temp = 'n' THEN		

DELETE FROM amicizia
WHERE (richiedente = u1 AND invitato = u2) OR (invitato = u1 AND richiedente = u2);

END IF;


END;
$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------------------------------------------------------------

-- ** Calcola il nome della citta' dato un id ** OK

CREATE OR REPLACE FUNCTION get_nomecitta(IN temp INTEGER) RETURNS VARCHAR(20) AS
$$
DECLARE ris VARCHAR(20);

BEGIN
ris:= (SELECT nome
        FROM citta
        WHERE idcitta = temp
       );
RETURN ris;
END

$$ LANGUAGE plpgsql; 

-- SELECT get_nomecitta(2)




----------------------------------------------------------------------------------------------------------------------------------------

-- ** Calcola la provincia della citta' dato un id ** OK

CREATE OR REPLACE FUNCTION get_provcitta(IN temp INTEGER) RETURNS VARCHAR(2) AS
$$
DECLARE ris VARCHAR(2);

BEGIN
ris:= (SELECT provincia
        FROM citta
        WHERE idcitta = temp
       );
RETURN ris;
END

$$ LANGUAGE plpgsql; 

-- SELECT get_provcitta(2)


----------------------------------------------------------------------------------------------------------------------------------------

-- ** Calcola id della cittˆ dato nome e prov ** OK

CREATE OR REPLACE FUNCTION get_idcitta(IN c VARCHAR(20), IN p VARCHAR(2)) RETURNS INTEGER AS
$$
DECLARE ris INTEGER;

BEGIN
ris:=   (SELECT idcitta
        FROM citta
        WHERE nome = c AND provincia = p
        );
RETURN ris;
END

$$ LANGUAGE plpgsql; 

--SELECT get_idcitta('milano','mi')

----------------------------------------------------------------------------------------------------------------------------------------

--OK

CREATE OR REPLACE FUNCTION get_idscuola(n character varying, t character varying, c character varying, p character varying)
  RETURNS integer AS
$$

DECLARE ris INTEGER;

BEGIN

ris:=   (SELECT idscuola

        FROM scuola

        WHERE nome = n AND sede = get_idcitta(c,p) AND tipo = t

        );

RETURN ris;

END

$$LANGUAGE plpgsql;
----------------------------------------------------------------------------------------------------------------------------------------

--OK

CREATE OR REPLACE FUNCTION eta_reg(temp DATE)
  RETURNS integer AS
$$

DECLARE

ris INTEGER;





BEGIN

ris:= date_part('year',age(CURRENT_DATE, temp));        

RETURN ris;



END;
$$ LANGUAGE plpgsql; 

  
  
 
---------------------------------------------------------------------------------------------------------------------------------------- 
 
-- ** Crea un invito ** --  OK

CREATE OR REPLACE FUNCTION invito(IN inv VARCHAR(35), IN eve integer) RETURNS void AS
$$
DECLARE temp CHAR(1);
DECLARE org CHAR(25);

BEGIN
org:=  (SELECT organizzatore
        FROM evento
        WHERE IDevento = eve
       );
       
temp:=   get_samic(inv,org);
         
IF temp='s'   THEN             
              INSERT INTO invito VALUES(inv, 'a', eve);

END IF;   
END;
$$ LANGUAGE plpgsql; 

--SELECT invito('francesca.mad@gmail.com', 2);


----------------------------------------------------------------------------------------------------------------------------------------

--** Definizione dell'ID massimo di un evento di un utente**  OK

CREATE OR REPLACE FUNCTION max_idevento() RETURNS INTEGER AS
$$
DECLARE maxid INTEGER;
       temp INTEGER;
BEGIN
temp:= (SELECT count(*)
        FROM evento
       );
IF temp = 0 THEN maxid = temp;
END IF;
IF temp != 0 THEN
maxid:= (SELECT max(IDevento)
      FROM evento 
      );    
END IF;
RETURN maxid;   
END;
$$ LANGUAGE plpgsql; 

--SELECT max_idevento();


----------------------------------------------------------------------------------------------------------------------------------------

--** Accetta/Rifiuta un invito **  OK

CREATE OR REPLACE FUNCTION gest_invito(IN u VARCHAR(35), IN eve integer, IN st CHAR) RETURNS void AS
$$
BEGIN
UPDATE invito
SET stato = st
WHERE evento = eve AND invitato = u; 
END;
$$ LANGUAGE plpgsql; 

-- SELECT gest_invito(4);


----------------------------------------------------------------------------------------------------------------------------------------

-- ** Stato dell'amicizia tra due utenti *  OK

CREATE OR REPLACE FUNCTION get_samic(IN u1 VARCHAR(35), IN u2 VARCHAR(35)) RETURNS CHAR AS
$$
DECLARE  temp CHAR;
BEGIN
temp:=   (SELECT stato
          FROM amicizia
          WHERE (richiedente = u1 AND invitato = u2) 
             OR (richiedente = u2 AND invitato = u1)
         );
RETURN TEMP;
END;
$$ LANGUAGE plpgsql;


----------------------------------------------------------------------------------------------------------------------------------------

--** Eta' di un utente **--  OK

CREATE OR REPLACE FUNCTION eta(IN u VARCHAR(35)) RETURNS INTEGER AS
$$
DECLARE
ris INTEGER;
temp DATE;

BEGIN
temp:=  (SELECT data_nascita
         FROM profilo
         WHERE utente = u
        );
        
ris:= date_part('year',age(CURRENT_DATE, temp));        
RETURN ris;

END;
$$ LANGUAGE plpgsql;

