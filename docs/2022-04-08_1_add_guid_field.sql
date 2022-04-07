ALTER TABLE tickets ADD guid varchar(36) DEFAULT '' NULL COMMENT 'ticket GUID';
CREATE INDEX guid USING BTREE ON tickets (guid);


