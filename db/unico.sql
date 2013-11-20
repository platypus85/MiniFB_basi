 CREATE TABLE utente
    (
      email character varying(35) NOT NULL,
      psw character varying(15) NOT NULL,
      vip boolean NOT NULL,
      CONSTRAINT "IDutente" PRIMARY KEY (email)
    );


CREATE TABLE amicizia
    (
      richiedente character varying(35) NOT NULL,
      invitato character varying(35) NOT NULL,
      stato character varying NOT NULL,                   -- stato: s=accettata, n=rifiutata, a=attesa
      CONSTRAINT "IDamicizia" PRIMARY KEY (richiedente, invitato),
      CONSTRAINT check_invitato FOREIGN KEY (invitato)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_richiedente FOREIGN KEY (richiedente)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_stato CHECK (stato::text = ANY (ARRAY['a'::character varying::text, 's'::character varying::text, 'n'::character varying::text]))
 
    );
    

    CREATE TABLE citta
    (
      IDcitta serial NOT NULL,
      nome character varying(20) NOT NULL,
      provincia character varying(2) NOT NULL,
      CONSTRAINT "IDcitta" PRIMARY KEY (IDcitta),
      CONSTRAINT citta_nome_provincia_key UNIQUE (nome, provincia)

    );


    CREATE TABLE scuola
    (
      nome character varying(30) NOT NULL,
      tipo character varying(20),
      sede integer NOT NULL,
      IDscuola serial NOT NULL,
      CONSTRAINT "IDscuola" PRIMARY KEY (IDscuola),
      CONSTRAINT scuola_nome_sede_key UNIQUE (nome, sede, tipo),
      CONSTRAINT check_sede FOREIGN KEY (sede)
          REFERENCES citta (IDcitta) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE NO ACTION
    );


    CREATE TABLE profilo
    (
      utente character varying(35) NOT NULL,
      nome character varying(20) NOT NULL,
      cognome character varying(20) NOT NULL,
      sesso character(1),
      citta_residenza integer,
      citta_nascita integer,
      data_nascita date,
      CONSTRAINT "IDprofilo" PRIMARY KEY (utente),
      CONSTRAINT check_nascita FOREIGN KEY (citta_nascita)
          REFERENCES citta (IDcitta) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE SET NULL,
      CONSTRAINT check_residenza FOREIGN KEY (citta_residenza)
          REFERENCES citta (IDcitta) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE SET NULL,
      CONSTRAINT check_utente FOREIGN KEY (utente)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_sesso CHECK (sesso = ANY (ARRAY['m'::bpchar, 'f'::bpchar]))

    );


    CREATE TABLE frequenza
    (
      studente character varying(35) NOT NULL,
      scuola integer NOT NULL,
      data_inizio date NOT NULL,
      data_fine date,
      CONSTRAINT "IDfrequenza" PRIMARY KEY (studente, scuola, data_inizio),
      CONSTRAINT check_scuola FOREIGN KEY (scuola)
          REFERENCES scuola (IDscuola) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE NO ACTION,
      CONSTRAINT check_studente FOREIGN KEY (studente)
          REFERENCES profilo (utente) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE NO ACTION,
     CONSTRAINT date CHECK (data_fine > data_inizio)
    );


    CREATE TABLE hobby
    (
      nome character varying(15) NOT NULL,
      CONSTRAINT "IDhobby" PRIMARY KEY (nome)
    );


    CREATE TABLE interessi
    (
      nome_hobby character varying(15) NOT NULL,
      utente character varying(35) NOT NULL,
      CONSTRAINT "IDinteressi" PRIMARY KEY (nome_hobby, utente),
      CONSTRAINT check_hobby FOREIGN KEY (nome_hobby)
          REFERENCES hobby (nome) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_profilo FOREIGN KEY (utente)
          REFERENCES profilo (utente) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE
    );


    CREATE TABLE post
    (
      IDpost integer NOT NULL,
      utente character varying(35) NOT NULL,
      data date NOT NULL,
      ora time without time zone NOT NULL,
      testo text NOT NULL,
      tipo character(1) NOT NULL,  -- tipo: n=nota, m=messaggio di stato
      CONSTRAINT "IDpost" PRIMARY KEY (IDpost, utente),
      CONSTRAINT check_utente FOREIGN KEY (utente)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE NO ACTION,
      CONSTRAINT check_tipo CHECK (tipo::text = ANY (ARRAY['n'::character varying::text, 'm'::character varying::text]))

    );

    

    CREATE TABLE evento
    (
      IDevento serial NOT NULL,
      nome character varying(25) NOT NULL,
      organizzatore character varying(35) NOT NULL,
      tipo character(10) NOT NULL,   -- tipo: c=concerto, f=festa, s=sport
      data date NOT NULL,
      via character varying(25) NOT NULL,
      luogo integer NOT NULL,
      data_creazione date NOT NULL,
      ora_creazione time without time zone NOT NULL,
      CONSTRAINT "IDevento" PRIMARY KEY (IDevento),
      CONSTRAINT evento_nome_organizzatore_data_key UNIQUE (nome, organizzatore, data, luogo),
      CONSTRAINT check_organizzatore FOREIGN KEY (organizzatore)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_citta FOREIGN KEY (luogo)
          REFERENCES citta (IDcitta) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE NO ACTION,
      CONSTRAINT check_tipo CHECK (tipo::text = ANY (ARRAY['sport'::character varying::text, 'concerto'::character varying::text, 'festa'::character varying::text]))

    );


    CREATE TABLE invito
    (
      invitato character varying(35) NOT NULL,
      stato character(1) NOT NULL,   -- stato: s=accettato, n=rifiutato, a=attesa
      evento integer NOT NULL,
      CONSTRAINT "IDinvito" PRIMARY KEY (invitato, evento),
      CONSTRAINT check_evento FOREIGN KEY (evento)
          REFERENCES evento (IDevento) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_invitato FOREIGN KEY (invitato)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE
    );


    CREATE TABLE tag
    (
      taggante character varying(35) NOT NULL,
      IDpost integer NOT NULL,
      taggato character varying(35) NOT NULL,
      CONSTRAINT "IDtag" PRIMARY KEY (taggante, IDpost, taggato),
      CONSTRAINT check_taggante FOREIGN KEY (IDpost, taggante)
      REFERENCES post (IDpost, utente) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT check_taggato FOREIGN KEY (taggato)
          REFERENCES utente (email) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE
    );
    
    
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

-- ** Calcola id della città dato nome e prov ** OK

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

---------------------------------------------------------------------

INSERT INTO utente (email, psw, vip) VALUES ('alberto.rossi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('alberto.verdi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('giacomo.astori@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('matteo.baroni@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('niccolo.bassani@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('marco.bruno@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('damiano.verdi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('marco.fabrizio@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('marta.ferrari@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('mirko.leoni@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('sergio.mascie@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('alessandro.vale@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('martina.verqui@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('tonio.chiedici@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('maria.cosisia@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('natalia.porta@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('giada.testi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('luca.principe@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('gatto.pixel@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('stefano.ilvalto@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('fede.gnuccio@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('cinzia.burpo@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('adalberto.muffa@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('alberto.bianchi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('alberto.cami@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('alessia.guidi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('chad.smizio@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('carla.terone@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('claudia.struzzi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('danny.leccio@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('francesca.mad@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('giulia.castori@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('marco.allevi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('francesca.rossi@gmail.com', 'password', false);
INSERT INTO utente (email, psw, vip) VALUES ('michele.balzari@gmail.com', 'password', false);

INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'alberto.bianchi@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'alberto.cami@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'alessia.guidi@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'chad.smizio@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'carla.terone@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'claudia.struzzi@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'danny.leccio@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'francesca.mad@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'giulia.castori@gmail.com', 's');
INSERT INTO amicizia (richiedente, invitato, stato) VALUES ('adalberto.muffa@gmail.com', 'marco.allevi@gmail.com', 's');


INSERT INTO citta (nome, provincia) VALUES ('roma', 'rm');
INSERT INTO citta (nome, provincia) VALUES ('milano', 'mi');
INSERT INTO citta (nome, provincia) VALUES ('napoli', 'na');
INSERT INTO citta (nome, provincia) VALUES ('torino', 'to');
INSERT INTO citta (nome, provincia) VALUES ('bari', 'ba');
INSERT INTO citta (nome, provincia) VALUES ('palermo', 'pa');
INSERT INTO citta (nome, provincia) VALUES ('brescia', 'bs');
INSERT INTO citta (nome, provincia) VALUES ('salerno', 'sa');
INSERT INTO citta (nome, provincia) VALUES ('catania', 'ct');
INSERT INTO citta (nome, provincia) VALUES ('bergamo', 'bg');
INSERT INTO citta (nome, provincia) VALUES ('firenze', 'fi');
INSERT INTO citta (nome, provincia) VALUES ('bologna', 'bo');
INSERT INTO citta (nome, provincia) VALUES ('padova', 'pd');
INSERT INTO citta (nome, provincia) VALUES ('verona', 'vr');
INSERT INTO citta (nome, provincia) VALUES ('caserta', 'ce');
INSERT INTO citta (nome, provincia) VALUES ('treviso', 'tv');
INSERT INTO citta (nome, provincia) VALUES ('genova', 'ge');
INSERT INTO citta (nome, provincia) VALUES ('varese', 'va');
INSERT INTO citta (nome, provincia) VALUES ('vicenza', 'vi');
INSERT INTO citta (nome, provincia) VALUES ('venezia', 've');
INSERT INTO citta (nome, provincia) VALUES ('lecce', 'lc');
INSERT INTO citta (nome, provincia) VALUES ('cosenza', 'cs');
INSERT INTO citta (nome, provincia) VALUES ('modena', 'mo');
INSERT INTO citta (nome, provincia) VALUES ('alghero', 'ss');
INSERT INTO citta (nome, provincia) VALUES ('perugia', 'pg');
INSERT INTO citta (nome, provincia) VALUES ('sassari', 'ss');
INSERT INTO citta (nome, provincia) VALUES ('cagliari', 'ca');
INSERT INTO citta (nome, provincia) VALUES ('oristano', 'or');
INSERT INTO citta (nome, provincia) VALUES ('nuoro', 'nu');
INSERT INTO citta (nome, provincia) VALUES ('stintino', 'ss');
INSERT INTO citta (nome, provincia) VALUES ('rho', 'mi');
INSERT INTO citta (nome, provincia) VALUES ('corsico', 'mi');







INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alberto', 'bianchi', 'm', 4, 5, '1986-09-24', 'alberto.bianchi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('adalberto', 'muffa', 'm', 3, 3, '1975-06-26', 'adalberto.muffa@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alberto', 'cami', 'm', 16, 16, '1985-01-18', 'alberto.cami@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alberto', 'rossi', 'm', 21, 21, '1983-12-23', 'alberto.rossi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alberto', 'verdi', 'm', 1, 1, '1968-11-04', 'alberto.verdi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alessandro', 'vale', 'm', 13, 25, '1945-02-01', 'alessandro.vale@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('alessia', 'guidi', 'f', 7, 7, '1988-04-30', 'alessia.guidi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('carla', 'terone', 'f', 20, 20, '1981-05-19', 'carla.terone@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('chad', 'smizio', 'm', 9, 12, '1962-07-16', 'chad.smizio@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('cinzia', 'burpo', 'f', 2, 2, '1985-08-22', 'cinzia.burpo@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('claudia', 'struzzi', 'f', 5, 5, '1987-03-11', 'claudia.struzzi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('damiano', 'verdi', 'm', 19, 19, '1955-02-28', 'damiano.verdi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('danny', 'leccio', 'm', 2, 3, '1938-04-21', 'danny.leccio@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('fede', 'gnuccio', 'm', 2, 2, '1966-04-23', 'fede.gnuccio@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('francesca', 'mad', 'f', 2, 16, '1985-11-29', 'francesca.mad@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('francesca', 'rossi', 'f', 4, 24, '1980-10-10', 'francesca.rossi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('gatto', 'pixel', 'm', 2, 3, '1985-01-01', 'gatto.pixel@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('giacomo', 'astori', 'm', 9, 9, '1970-09-12', 'giacomo.astori@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('giada', 'testi', 'f', 1, 1, '1990-11-11', 'giada.testi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('giulia', 'castori', 'f', 18, 18, '1991-06-30', 'giulia.castori@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('luca', 'prince', 'm', 2, 1, '1987-09-11', 'luca.principe@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('marco', 'allevi', 'm', 20, 20, '1982-06-06', 'marco.allevi@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('marco', 'bruno', 'm', 7, 7, '1990-01-31', 'marco.bruno@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('maria', 'cosisia', 'f', 1, 9, '1989-04-04', 'maria.cosisia@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('marta', 'ferrari', 'f', 8, 8, '1984-12-25', 'marta.ferrari@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('marco', 'fabrizio', 'm', 2, 2, '1980-03-12', 'marco.fabrizio@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('martina', 'verqui', 'f', 18, 18, '1988-10-23', 'martina.verqui@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('matteo', 'baroni', 'm', 8, 2, '1955-07-27', 'matteo.baroni@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('michele', 'balzari', 'm', 1, 1, '1989-09-09', 'michele.balzari@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('mirko', 'leoni', 'm', 12, 13, '1990-02-02', 'mirko.leoni@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('natalia', 'porta', 'f', 2, 2, '1985-12-16', 'natalia.porta@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('niccolo', 'bassani', 'm', 11, 17, '1981-01-31', 'niccolo.bassani@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('sergio', 'mascie', 'm', 2, 2, '1980-04-30', 'sergio.mascie@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('stefano', 'ilvalto', 'm', 3, 4, '1969-03-18', 'stefano.ilvalto@gmail.com');
INSERT INTO profilo (nome, cognome, sesso, citta_residenza, citta_nascita, data_nascita, utente) VALUES ('tonio', 'chiedici', 'm', 23, 23, '1957-09-21', 'tonio.chiedici@gmail.com');


INSERT INTO scuola (nome, tipo, sede) VALUES ('primo levi', 'superiore', 16);
INSERT INTO scuola (nome, tipo, sede) VALUES ('gianni rossi', 'elementare', 12);
INSERT INTO scuola (nome, tipo, sede) VALUES ('degli studi', 'universit&agrave;', 2);
INSERT INTO scuola (nome, tipo, sede) VALUES ('albert einstein', 'superiore', 1);
INSERT INTO scuola (nome, tipo, sede) VALUES ('degli studi', 'universit&agrave;', 3);
INSERT INTO scuola (nome, tipo, sede) VALUES ('carlo marzo', 'elementare', 18);
INSERT INTO scuola (nome, tipo, sede) VALUES ('gianni rodari', 'superiore', 2);
INSERT INTO scuola (nome, tipo, sede) VALUES ('politecnico', 'universit&agrave;', 1);
INSERT INTO scuola (nome, tipo, sede) VALUES ('john lennon', 'superiore', 10);
INSERT INTO scuola (nome, tipo, sede) VALUES ('normale', 'universit&agrave;', 1);
INSERT INTO scuola (nome, tipo, sede) VALUES ('azuni', 'medie', 1);
INSERT INTO scuola (nome, tipo, sede) VALUES ('sinervis', 'altro', 2);
INSERT INTO scuola (nome, tipo, sede) VALUES ('comunicazione digitale', 'universit&agrave;', 2);






INSERT INTO frequenza (studente, scuola, data_inizio, data_fine) VALUES ('adalberto.muffa@gmail.com', 5, '2003-09-18', '2004-06-30');
INSERT INTO frequenza (studente, scuola, data_inizio, data_fine) VALUES ('adalberto.muffa@gmail.com', 5, '2005-10-10', '2006-06-15');
INSERT INTO frequenza (studente, scuola, data_inizio, data_fine) VALUES ('alessandro.vale@gmail.com', 5, '2004-09-01', NULL);



INSERT INTO hobby (nome) VALUES ('tennis');
INSERT INTO hobby (nome) VALUES ('pallavolo');
INSERT INTO hobby (nome) VALUES ('calcio');
INSERT INTO hobby (nome) VALUES ('ciclismo');
INSERT INTO hobby (nome) VALUES ('sci');
INSERT INTO hobby (nome) VALUES ('nuoto');
INSERT INTO hobby (nome) VALUES ('equitazione');
INSERT INTO hobby (nome) VALUES ('vela');
INSERT INTO hobby (nome) VALUES ('cinema');
INSERT INTO hobby (nome) VALUES ('teatro');
INSERT INTO hobby (nome) VALUES ('film');
INSERT INTO hobby (nome) VALUES ('concerti');
INSERT INTO hobby (nome) VALUES ('mostre');
INSERT INTO hobby (nome) VALUES ('internet');
INSERT INTO hobby (nome) VALUES ('telefonia');
INSERT INTO hobby (nome) VALUES ('tv');
INSERT INTO hobby (nome) VALUES ('computer');
INSERT INTO hobby (nome) VALUES ('lettura');
INSERT INTO hobby (nome) VALUES ('collezionismo');



INSERT INTO interessi (nome_hobby, utente) VALUES ('tennis', 'francesca.mad@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('pallavolo', 'francesca.mad@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('computer', 'francesca.mad@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('film', 'alberto.cami@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('internet', 'alberto.cami@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('nuoto', 'alberto.cami@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('calcio', 'adalberto.muffa@gmail.com');
INSERT INTO interessi (nome_hobby, utente) VALUES ('vela', 'adalberto.muffa@gmail.com');

INSERT INTO evento (nome, organizzatore, tipo, data, via, luogo, data_creazione, ora_creazione) VALUES ('radiohead live', 'adalberto.muffa@gmail.com', 'concerto', '2011-07-07', 'arena', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO evento (nome, organizzatore, tipo, data, via, luogo, data_creazione, ora_creazione) VALUES ('compleanno', 'adalberto.muffa@gmail.com', 'festa', '2011-03-07', 'via roma 32', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO evento (nome, organizzatore, tipo, data, via, luogo, data_creazione, ora_creazione) VALUES ('partita', 'adalberto.muffa@gmail.com', 'sport', '2011-04-07', 'san siro', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);


INSERT INTO post (idpost, utente, data,  ora, testo, tipo) VALUES ('1','adalberto.muffa@gmail.com', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'sono triste', 'm');
INSERT INTO post (idpost, utente, data,  ora, testo, tipo) VALUES ('2','adalberto.muffa@gmail.com', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'ciao ai miei amici', 'n');
INSERT INTO post (idpost, utente, data,  ora, testo, tipo) VALUES ('1','giacomo.astori@gmail.com', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'vado al mare', 'm');
INSERT INTO post (idpost, utente, data,  ora, testo, tipo) VALUES ('1','danny.leccio@gmail.com', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'che caldo!', 'm');
INSERT INTO post (idpost, utente, data,  ora, testo, tipo) VALUES ('1','francesca.rossi@gmail.com', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'studio', 'm');


INSERT INTO invito (invitato, stato, evento) VALUES ('giacomo.astori@gmail.com', 's', 1);
INSERT INTO invito (invitato, stato, evento) VALUES ('francesca.rossi@gmail.com', 's', 1);
INSERT INTO invito (invitato, stato, evento) VALUES ('giulia.castori@gmail.com', 'a', 1);
INSERT INTO invito (invitato, stato, evento) VALUES ('claudia.struzzi@gmail.com', 'n', 1);


INSERT INTO tag (taggante, IDpost, taggato) VALUES ('adalberto.muffa@gmail.com', 2, 'alberto.bianchi@gmail.com');
INSERT INTO tag (taggante, IDpost, taggato) VALUES ('adalberto.muffa@gmail.com', 2, 'carla.terone@gmail.com');
INSERT INTO tag (taggante, IDpost, taggato) VALUES ('adalberto.muffa@gmail.com', 2, 'danny.leccio@gmail.com');
INSERT INTO tag (taggante, IDpost, taggato) VALUES ('adalberto.muffa@gmail.com', 2, 'marco.allevi@gmail.com');


 



