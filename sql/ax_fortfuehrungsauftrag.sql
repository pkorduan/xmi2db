CREATE TABLE ax_fortfuehrungsauftrag (
	direkt boolean,
	ausgabeform character varying,
	crs character varying,
	anzahlDerNachkommastellen integer,
	standard boolean,
	profilkennung character varying,
	antragsnummer integer,
	auftragsnummer integer,
	impliziteloeschungderreservierung integer,
	verarbeitungsart integer,
	geometriebehandlung boolean,
	mittemporaeremarbeitsbereich boolean,
	mitobjektenimfortfuehrungsgebiet boolean,
	mitfortfuehrungsnachweis boolean,
	id serial NOT NULL,
	jahr integer NOT NULL DEFAULT date_part('year'::text, now()),
	gemkgnr integer NOT NULL,
	lfdnr integer NOT NULL,
	bemerkung text,
	auftragsdatei character varying,
	created_at timestamp without time zone NOT NULL DEFAULT now(),
	updated_at timestamp without time zone NOT NULL DEFAULT now(),
	user_name character varying NOT NULL
);
