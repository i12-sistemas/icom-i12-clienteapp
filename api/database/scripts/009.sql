CREATE TABLE `clienteusuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clienteid` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `email` varchar(255) NULL,
  `celular` varchar(30) NULL,
  `senha` varchar(255) DEFAULT NULL,
  `ativo` int(11) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fotostorage` ENUM('local', 'S3') NOT NULL,
  `fotofilename` varchar(1500) DEFAULT NULL,
  `fotoext` varchar(10) DEFAULT NULL,
  `fotosize` int(11) DEFAULT 0,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_clienteusuario_celular` (`celular`),
  UNIQUE KEY `unique_clienteusuarioo_email` (`email`),
  KEY `idx_clienteusuario_clienteid` (`clienteid`),
  KEY `idx_clienteusuario_created_usuarioid` (`created_usuarioid`),
  KEY `idx_clienteusuario_cupdated_usuarioid` (`updated_usuarioid`),
  KEY `idx_clienteusuario_celular` (`celular`),
  KEY `idx_clienteusuario_email` (`email`),
  KEY `idx_clienteusuario_ativo` (`ativo`),
  CONSTRAINT `fk_clienteusuario_clienteid` FOREIGN KEY (`clienteid`) REFERENCES `cliente` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_clienteusuario_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_clienteusuario_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `clienteusuariotokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clienteusuarioid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `celular` varchar(30) NOT NULL,
  `token` varchar(255) NOT NULL,
  `accesscode` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clienteusuariotokens_clienteusuarioid` (`clienteusuarioid`),
  KEY `idx_clienteusuariotokens_email` (`email`),
  KEY `idx_clienteusuariotokens_celular` (`celular`),
  KEY `idx_usuariotokens_token` (`token`),
  CONSTRAINT `fk_clienteusuariotokens_clienteusuarioid` FOREIGN KEY (`clienteusuarioid`) REFERENCES `clienteusuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `clienteusuarioresetpwdtokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clienteusuarioid` int(11) NOT NULL,
  `celular` varchar(30) NULL,
  `email` varchar(255) NULL,
  `codenumber` varchar(6) NOT NULL,
  `token` varchar(255) NOT NULL,
  `processado` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clienteusuarioresetpwdtokens_clienteusuarioid` (`clienteusuarioid`),
  KEY `idx_clienteusuarioresetpwdtokens_celular` (`celular`),
  KEY `idx_clienteusuarioresetpwdtokens_email` (`email`),
  KEY `idx_clienteusuarioresetpwdtokens_processado` (`processado`),
  KEY `idx_clienteusuarioresetpwdtokens_token` (`token`),
  CONSTRAINT `fk_clienteusuarioresetpwdtokens_clienteusuario` FOREIGN KEY (`clienteusuarioid`) REFERENCES `clienteusuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;

ALTER TABLE `clienteusuariotokens` 
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`;


ALTER TABLE `coletas_nota` 
ADD COLUMN `baixanfedhproc` DATETIME NULL AFTER `baixanfestatus`;


CREATE TABLE `coletas_nota_xml` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(44) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `fileblob` LONGBLOB,
  `filestorage` ENUM('db', 'local', 's3') NOT NULL,
  `filename` VARCHAR(5000) NOT NULL,
  `fileext` VARCHAR(10) NOT NULL,
  `filesize` INT(11) NOT NULL DEFAULT 0,
  `filemd5` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `chave_UNIQUE` (`chave` ASC),
  UNIQUE INDEX `filemd5_UNIQUE` (`filemd5` ASC)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `coletas_nota_xml_token` (
  `token` varchar(255) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `chave` varchar(255) NOT NULL,
  `tipo` ENUM('email', 'whatsapp', 'sms', 'url') NOT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `access_at` datetime DEFAULT NULL,
  PRIMARY KEY (`token`),
  KEY `idx_coletas_nota_xml_token_cnpj` (`cnpj`),
  KEY `idx_coletas_nota_xml_token_chave` (`chave`),
  KEY `idx_coletas_nota_xml_token_tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `coletas_nota_xml_token` 
ADD COLUMN `accesscode` VARCHAR(255) NULL AFTER `token`,
ADD INDEX `idx_coletas_nota_xml_token_accesscode` (`accesscode` ASC);


ALTER TABLE `coletas_nota_xml_token` 
ADD COLUMN `to` VARCHAR(5000) NULL AFTER `access_at`,
ADD COLUMN `cc` VARCHAR(5000) NULL AFTER `to`,
ADD COLUMN `assunto` VARCHAR(255) NULL AFTER `cc`,
ADD COLUMN `mensagem` VARCHAR(5000) NULL AFTER `assunto`;


ALTER TABLE `coletas_nota_xml_token` 
ADD COLUMN `usuarioid` INT(11) NULL AFTER `mensagem`,
ADD INDEX `fk-coletas_nota_xml_token_usuario_idx` (`usuarioid` ASC);

ALTER TABLE `coletas_nota_xml_token` 
ADD CONSTRAINT `fk-coletas_nota_xml_token_usuario`
  FOREIGN KEY (`usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `coletas_nota_xml_token` 
ADD COLUMN `origem` ENUM('1', '2') NOT NULL DEFAULT '1' COMMENT '1-Manual usuário, 2=Lembrete automativo' AFTER `accesscode`,
ADD INDEX `idx_coletas_nota_xml_token_origem` (`origem` ASC);

ALTER TABLE `formulariodoc` 
ADD COLUMN `ocrresult` MEDIUMTEXT NULL AFTER `obs`;

ALTER TABLE `formulariodoc` 
ADD COLUMN `ocrresultprocessado` MEDIUMTEXT NULL AFTER `ocrresult`;

ALTER TABLE `formulariodoc` 
CHANGE COLUMN `ocrresult` `ocrresultraw` MEDIUMTEXT NULL DEFAULT NULL ,
CHANGE COLUMN `ocrresultprocessado` `ocrresult` MEDIUMTEXT NULL DEFAULT NULL ;

ALTER TABLE `formulariodoc` 
ADD UNIQUE INDEX `formulariodoc_unique` (`formid` ASC, `tipodoc` ASC);

ALTER TABLE `coletas_nota_xml_token` 
ADD COLUMN `notas` VARCHAR(5000) NULL AFTER `created_at`;


ALTER TABLE `clienteusuario` 
ADD COLUMN `ultimoacesso` DATETIME NULL AFTER `ativo`;
