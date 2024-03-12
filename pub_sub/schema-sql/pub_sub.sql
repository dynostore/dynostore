--
-- PostgreSQL database dump
--

-- Dumped from database version 10.1
-- Dumped by pg_dump version 10.1

-- Started on 2018-02-18 18:17:30

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 1 (class 3079 OID 12924)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2975 (class 0 OID 0)
-- Dependencies: 1
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;


--
-- TOC entry 206 (class 1259 OID 26149)
-- Name: catalogs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE catalogs (
    keycatalog character varying(100) NOT NULL,
    tokencatalog character varying(100) NOT NULL,
    namecatalog character varying(1000),
    created_at timestamp DEFAULT NOW(),
    token_user character varying(100),
    dispersemode character varying(25),
    encryption boolean NOT NULL DEFAULT false,
    isprivate boolean NOT NULL DEFAULT false,
    father character varying(100),
    "group" character varying(100),
    "processed" boolean NOT NULL DEFAULT false,
    PRIMARY KEY (keycatalog),
    UNIQUE(tokencatalog)
);


--ALTER TABLE catalogs OWNER TO postgres;







--
-- TOC entry 198 (class 1259 OID 26117)
-- Name: groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE groups (
    keygroup character varying(100) NOT NULL,
    tokengroup character varying(100) NOT NULL,
    namegroup character varying(100),
    created_at timestamp DEFAULT NOW(),
    token_user character varying(100),
    isprivate boolean NOT NULL DEFAULT false,
    father character varying(100),
    PRIMARY KEY (keygroup),
    UNIQUE(tokengroup)
);


--ALTER TABLE groups OWNER TO postgres;

--
-- TOC entry 199 (class 1259 OID 26122)
-- Name: groups_catalogs; Type: TABLE; Schema: public; Owner: postgres
--


CREATE TABLE groups_catalogs (
    tokengroup character varying(100),
    tokencatalog character varying(100),
    status character varying(20),
    PRIMARY KEY(tokengroup,tokencatalog),
    CONSTRAINT Ref_groups_catalogs_to_catalogs FOREIGN KEY (tokencatalog) REFERENCES catalogs(tokencatalog) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT Ref_groups_catalogs_to_groups FOREIGN KEY (tokengroup) REFERENCES groups(tokengroup) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE groups_catalogs OWNER TO postgres;



CREATE TABLE groups_files (
    tokengroup character varying(100),
    token_file character varying(100),
    status character varying(20),
    PRIMARY KEY(tokengroup,token_file),
    CONSTRAINT Ref_groups_files_to_groups FOREIGN KEY (tokengroup) REFERENCES groups(tokengroup) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE groups_files OWNER TO postgres;


CREATE TABLE shared_files (
    shared_with character varying(100),
    shared_token character varying(100),
    token_file character varying(100),
    status character varying(20),
    PRIMARY KEY(shared_token,token_file)
);

--ALTER TABLE shared_files OWNER TO postgres;


CREATE TABLE catalogs_files (
    tokencatalog character varying(100),
    token_file character varying(100),
    status character varying(20),
    PRIMARY KEY(tokencatalog,token_file),
    CONSTRAINT Ref_catalogs_files_to_catalogs FOREIGN KEY (tokencatalog) REFERENCES catalogs(tokencatalog) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE catalogs_files OWNER TO postgres;



CREATE TABLE users_files (
    token_user character varying(100),
    token_file character varying(100),
    status character varying(20),
    PRIMARY KEY(token_user,token_file)
);


--ALTER TABLE users_files OWNER TO postgres;





CREATE TABLE subscriptions (
    id serial NOT NULL,
    token_user character varying(100),
    tokencatalog character varying(100),
    status character varying(20),
    CONSTRAINT Ref_subscriptions_to_catalogs FOREIGN KEY (tokencatalog) REFERENCES catalogs(tokencatalog) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE subscriptions OWNER TO postgres;




CREATE TABLE publications (
    id serial NOT NULL,
    token_user character varying(100),
    tokencatalog character varying(100),
    status character varying(20),
    CONSTRAINT Ref_publications_to_catalogs FOREIGN KEY (tokencatalog) REFERENCES catalogs(tokencatalog) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE publications OWNER TO postgres;


--
-- TOC entry 202 (class 1259 OID 26132)
-- Name: logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE logs (
    id serial NOT NULL,
    operation character varying(100),
    "table" character varying(100),
    token_user character varying(100),
    status character varying(100),
    created_at timestamp DEFAULT NOW(),
    PRIMARY KEY(id)
);


--ALTER TABLE logs OWNER TO postgres;




--
-- TOC entry 222 (class 1259 OID 26219)
-- Name: notification; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE notification (
    id serial NOT NULL,
    destination_token character varying(100),
    author_token character varying(100),
    status character varying(100),
    created_at timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    UNIQUE(destination_token,author_token)
);


--ALTER TABLE notification OWNER TO postgres;








--
-- TOC entry 214 (class 1259 OID 26183)
-- Name: users_catalogs; Type: TABLE; Schema: public; Owner: postgres
--


CREATE TABLE users_catalogs (
    token_user character varying(100),
    tokencatalog character varying(100),
    status character varying(20),
    PRIMARY KEY(token_user,tokencatalog),
    CONSTRAINT Ref_users_catalogs_to_catalogs FOREIGN KEY (tokencatalog) REFERENCES catalogs(tokencatalog) ON DELETE CASCADE ON UPDATE CASCADE
);


--ALTER TABLE users_catalogs OWNER TO postgres;





--
-- TOC entry 218 (class 1259 OID 26199)
-- Name: users_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE users_groups (
    token_user character varying(100),
    tokengroup character varying(100),
    status character varying(20),
    PRIMARY KEY(token_user,tokengroup),
    CONSTRAINT Ref_users_groups_to_groups FOREIGN KEY (tokengroup) REFERENCES groups (tokengroup) ON DELETE CASCADE ON UPDATE CASCADE
    
);

--ALTER TABLE users_groups OWNER TO postgres;

























-- Completed on 2018-02-18 18:17:35
--
-- PostgreSQL database dump complete
--

