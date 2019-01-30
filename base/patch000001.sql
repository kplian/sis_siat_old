/***********************************I-SCP-MMV-SIAT-0-30/01/2019****************************************/
CREATE TABLE siat.tfirma_usuario (
  id_firma_usuario SERIAL,
  id_usuario INTEGER,
  url_firma VARCHAR(100),
  extencion VARCHAR(20),
  password VARCHAR(20),
  CONSTRAINT tfirma_usuario_pkey PRIMARY KEY(id_firma_usuario)
) INHERITS (pxp.tbase)
WITH (oids = false);

ALTER TABLE siat.tfirma_usuario
  ALTER COLUMN url_firma SET STATISTICS 0;

ALTER TABLE siat.tfirma_usuario
  ALTER COLUMN extencion SET STATISTICS 0;
/***********************************F-SCP-MMV-SIAT-0-30/01/2019****************************************/