SET @@session.sql_mode = '';

REPLACE INTO oxvendor2shop (OXSHOPID, OXMAPOBJECTID) VALUES
(1, 902), (1, 903), (1, 904);

INSERT INTO oxconfig (OXID, OXSHOPID, OXVARNAME, OXVARTYPE, OXVARVALUE) SELECT
MD5(RAND()), 2, OXVARNAME, OXVARTYPE, OXVARVALUE from oxconfig;

REPLACE INTO oxselectlist2shop (OXSHOPID, OXMAPOBJECTID) VALUES
(1, 1);
