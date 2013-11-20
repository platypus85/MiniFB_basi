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
    
    
