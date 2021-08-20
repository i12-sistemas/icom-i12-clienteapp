CREATE TABLE `etiquetas` (
  `ean13` VARCHAR(13) NOT NULL,
  `dataref` DATETIME NOT NULL,
  `numero` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `useridcreated` INT(11) NOT NULL,
  `volnum` INT(11) NOT NULL,
  `voltotal` INT(11) NOT NULL,
  `pesototal` DOUBLE NOT NULL,
  `status` ENUM('1', '2', '3', '4', '5') NOT NULL COMMENT '1=EmDeposito, 2=EmTransferencia, 3=EmEntrega, 4=Entregue, 5=Extraviado\n',
  PRIMARY KEY (`ean13`),
  UNIQUE INDEX `unique_etiquetas_datarefnumero` (`dataref` ASC, `numero` ASC))
COMMENT = 'Etiquetas de controle de volume';


CREATE TABLE `cargaentrada` (
  `id` int(11) NOT NULL,
  `tipo` ENUM('1', '2') NOT NULL COMMENT '1=Motorista,2=Cliente entregou',
  `unidadeentradaid` INT(11) NOT NULL,
  `motoristaid` INT(11) NOT NULL,
  `veiculoid` INT(11) NOT NULL,
  `dhentrada` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `useridcreated` INT(11) NOT NULL,
  `useridupdated` INT(11) NOT NULL,
  `volqtde` INT(11) NOT NULL DEFAULT 0,
  `peso` DOUBLE NOT NULL  DEFAULT 0,
  `erroqtde` INT(11) NOT NULL DEFAULT 0,  
  `erromsg` VARCHAR(5000), 
  `editadomanualmente` INT(1) NOT NULL DEFAULT 0, 
  PRIMARY KEY (`id`)
);



ALTER TABLE `cargaentrada` 
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `motoristaid` `motoristaid` INT(11) NULL ,
CHANGE COLUMN `veiculoid` `veiculoid` INT(11) NULL ,
ADD INDEX `fk_cargaentrada_unidade_idx` (`unidadeentradaid` ASC),
ADD INDEX `fk_cargaentrada_usuariocreated_idx` (`useridcreated` ASC),
ADD INDEX `fk_cargaentrada_usuarioupdate_idx` (`useridupdated` ASC),
ADD INDEX `fk_cargaentrada_motorista_idx` (`motoristaid` ASC),
ADD INDEX `fk_cargaentrada_veiculo_idx` (`veiculoid` ASC);

ALTER TABLE `cargaentrada` 
ADD CONSTRAINT `fk_cargaentrada_unidade`
  FOREIGN KEY (`unidadeentradaid`)
  REFERENCES `unidade` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargaentrada_usuariocreated`
  FOREIGN KEY (`useridcreated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargaentrada_usuarioupdate`
  FOREIGN KEY (`useridupdated`)
  REFERENCES `unidade` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargaentrada_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargaentrada_veiculo`
  FOREIGN KEY (`veiculoid`)
  REFERENCES `veiculo` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;




CREATE TABLE `cargaentradaitem` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cargaentradaid` INT(11) NOT NULL,
  `nfechave` VARCHAR(44) NULL,
  `coletaid` INT(11) NULL,
  `nfenumero` INT(11) NULL,
  `nfecnpj` VARCHAR(14) NULL,
  `nfevol` INT(11) NULL DEFAULT 0,
  `nfepeso` DOUBLE NULL DEFAULT 0,
  `tipoprocessamento` ENUM('1', '2') NOT NULL DEFAULT '1' COMMENT '1=Auto, 2=Manual\n',
  `manualuserid` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cargaentradaitem_cargaentrada_idx` (`cargaentradaid` ASC),
  INDEX `fk_cargaentradaitem_usermanual_idx` (`manualuserid` ASC),
  INDEX `fk_cargaentradaitem_coleta_idx` (`coletaid` ASC),
  CONSTRAINT `fk_cargaentradaitem_cargaentrada`
    FOREIGN KEY (`cargaentradaid`)
    REFERENCES `cargaentrada` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentradaitem_usermanual`
    FOREIGN KEY (`manualuserid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentradaitem_coleta`
    FOREIGN KEY (`coletaid`)
    REFERENCES `coletas` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE);

ALTER TABLE `etiquetas` 
ADD COLUMN `cargaentradaitem` INT(11) NOT NULL AFTER `ean13`,
ADD INDEX `fk_etiquetas_usuario_idx` (`useridcreated` ASC),
ADD INDEX `fk_etiquetas_cargaentrada_idx` (`cargaentradaitem` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_usuario`
  FOREIGN KEY (`useridcreated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_etiquetas_cargaentrada`
  FOREIGN KEY (`cargaentradaitem`)
  REFERENCES `cargaentradaitem` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



ALTER TABLE `cargaentrada` 
DROP FOREIGN KEY `fk_cargaentrada_usuarioupdate`;

ALTER TABLE `cargaentrada` 
ADD INDEX `fk_cargaentrada_usuarioupdate_idx` (`useridupdated` ASC),
DROP INDEX `fk_cargaentrada_usuarioupdate_idx` ;

ALTER TABLE `cargaentrada` 
ADD CONSTRAINT `fk_cargaentrada_usuarioupdate`
  FOREIGN KEY (`useridupdated`)
  REFERENCES `usuario` (`id`)
  ON UPDATE CASCADE;


ALTER TABLE `cargaentrada` 
ADD COLUMN `status` ENUM('1', '2') NULL COMMENT '1=Em aberto/edição, 2=Encerrado';

ALTER TABLE `cargaentrada` 
CHANGE COLUMN `status` `status` ENUM('1', '2') NOT NULL DEFAULT '1' COMMENT '1=Em aberto/edição, 2=Encerrado' ;

ALTER TABLE `cargaentradaitem` 
CHANGE COLUMN `nfechave` `nfechave` VARCHAR(44) NOT NULL ,
ADD UNIQUE INDEX `nfechave_UNIQUE` (`nfechave` ASC);



ALTER TABLE `cargaentradaitem` 
ADD COLUMN `updated_at` DATETIME NULL AFTER `manualuserid`,
ADD COLUMN `created_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `useridcreated` INT(11) NULL AFTER `created_at`,
ADD COLUMN `useridupdated` INT(11) NULL AFTER `useridcreated`,
ADD INDEX `fk_cargaentradaitem_usercreated_idx` (`useridcreated` ASC),
ADD INDEX `fk_cargaentradaitem_userupdated_idx` (`useridupdated` ASC);

ALTER TABLE `cargaentradaitem` 
ADD CONSTRAINT `fk_cargaentradaitem_usercreated`
  FOREIGN KEY (`useridcreated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargaentradaitem_userupdated`
  FOREIGN KEY (`useridupdated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `cargaentradaitem` 
ADD COLUMN `errors` VARCHAR(500) NULL AFTER `useridupdated`;




ALTER TABLE `usuario` 
ADD COLUMN `unidadeprincipalid` INT(11) NULL AFTER `login`,
ADD INDEX `fk_usuario_unidade_idx` (`unidadeprincipalid` ASC);

ALTER TABLE `usuario` 
ADD CONSTRAINT `fk_usuario_unidade`
  FOREIGN KEY (`unidadeprincipalid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `etiquetas` 
ADD COLUMN `updated_at` DATETIME NOT NULL AFTER `created_at`,
ADD COLUMN `useridupdated` INT(11) NOT NULL AFTER `useridcreated`,
ADD INDEX `fk_etiquetas_usuarioupdated_idx` (`useridupdated` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_usuarioupdated`
  FOREIGN KEY (`useridupdated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `etiquetas` 
ADD UNIQUE INDEX `unique_etiquetas_carga_seq` (`cargaentradaitem` ASC, `volnum` ASC);


CREATE TABLE `cargatransfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` ENUM('1', '2', '3', '4') NOT NULL COMMENT '1=aberto, 2=Liberado carregamento e Transferencia, 3=Em transito, 4=Encerrado',
  `unidadesaidaid` INT(11) NOT NULL,
  `unidadeentradaid` INT(11) NOT NULL,
  `motoristaid` INT(11) NOT NULL,
  `veiculoid` INT(11) NOT NULL,
  `saidadh` DATETIME NULL,
  `saidauserid` INT(11) NOT NULL,
  `entradadh` DATETIME NULL,
  `entradauserid` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `useridcreated` INT(11) NOT NULL,
  `useridupdated` INT(11) NOT NULL,
  `volqtde` INT(11) NOT NULL DEFAULT 0,
  `peso` DOUBLE NOT NULL  DEFAULT 0,
  `erroqtde` INT(11) NOT NULL DEFAULT 0,  
  `erromsg` VARCHAR(5000),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `cargatransfer` 
ADD INDEX `fk_cargatransfer_unidadeentrada_idx` (`unidadeentradaid` ASC),
ADD INDEX `fk_cargatransfer_unidadesaida_idx` (`unidadesaidaid` ASC),
ADD INDEX `fk_cargatransfer_motorista_idx` (`motoristaid` ASC),
ADD INDEX `fk_cargatransfer_veiculo_idx` (`veiculoid` ASC),
ADD INDEX `fk_cargatransfer_usersaida_idx` (`saidauserid` ASC),
ADD INDEX `fk_cargatransfer_userentrada_idx` (`entradauserid` ASC),
ADD INDEX `fk_cargatransfer_usercreated_idx` (`useridcreated` ASC),
ADD INDEX `fk_cargatransfer_userupdated_idx` (`useridupdated` ASC);

ALTER TABLE `cargatransfer` 
ADD CONSTRAINT `fk_cargatransfer_unidadeentrada`
  FOREIGN KEY (`unidadeentradaid`)
  REFERENCES `unidade` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_unidadesaida`
  FOREIGN KEY (`unidadesaidaid`)
  REFERENCES `unidade` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_veiculo`
  FOREIGN KEY (`veiculoid`)
  REFERENCES `veiculo` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_usersaida`
  FOREIGN KEY (`saidauserid`)
  REFERENCES `usuario` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_userentrada`
  FOREIGN KEY (`entradauserid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_usercreated`
  FOREIGN KEY (`useridcreated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cargatransfer_userupdated`
  FOREIGN KEY (`useridupdated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


CREATE TABLE `cargatransferitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cargatransferid` int(11) NOT NULL,
  `etiquetaid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `cargatransferitem` 
CHANGE COLUMN `etiquetaid` `etiquetaean13` VARCHAR(13) NOT NULL ,
ADD UNIQUE INDEX `unique_cargatransferitem` (`etiquetaean13` ASC),
ADD INDEX `fk_cargatransferitem_carga_idx` (`cargatransferid` ASC);


ALTER TABLE `cargatransferitem` 
ADD CONSTRAINT `fk_cargatransferitem_carga`
  FOREIGN KEY (`cargatransferid`)
  REFERENCES `cargatransfer` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
  
ALTER TABLE `cargatransferitem` 
ADD CONSTRAINT `fk_cargatransferitem_etiquetas`
  FOREIGN KEY (`etiquetaean13`)
  REFERENCES `etiquetas` (`ean13`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `cargatransfer` 
CHANGE COLUMN `saidauserid` `saidauserid` INT(11) NULL ,
CHANGE COLUMN `entradauserid` `entradauserid` INT(11) NULL ;


ALTER TABLE `cargatransferitem` 
DROP INDEX `unique_cargatransferitem` ;

ALTER TABLE `cargatransferitem` 
DROP FOREIGN KEY `fk_cargatransferitem_etiquetas`;

ALTER TABLE `cargatransferitem` 
DROP INDEX `unique_cargatransferitem` ;


ALTER TABLE `cargatransferitem` 
ADD INDEX `fk_cargatransferitem_etoqueta_idx` (`etiquetaean13` ASC);

ALTER TABLE `cargatransferitem` 
ADD CONSTRAINT `fk_cargatransferitem_etoqueta`
  FOREIGN KEY (`etiquetaean13`)
  REFERENCES `etiquetas` (`ean13`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

CREATE TABLE `cargaentrega` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('1','2','3','4') NOT NULL COMMENT '1=aberto, 2=Liberado carregamento e entrega, 3=Em transito, 4=Entregue',
  `unidadesaidaid` int(11) NOT NULL,
  `motoristaid` int(11) NOT NULL,
  `veiculoid` int(11) NOT NULL,
  `saidadh` datetime DEFAULT NULL,
  `saidauserid` int(11) DEFAULT NULL,
  `entregadh` datetime DEFAULT NULL,
  `entregauserid` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `useridcreated` int(11) NOT NULL,
  `useridupdated` int(11) NOT NULL,
  `volqtde` int(11) NOT NULL DEFAULT '0',
  `peso` double NOT NULL DEFAULT '0',
  `erroqtde` int(11) NOT NULL DEFAULT '0',
  `erromsg` varchar(5000) DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  
  KEY `fk_cargaentrega_unidadesaida_idx` (`unidadesaidaid`),
  KEY `fk_cargaentrega_motorista_idx` (`motoristaid`),
  KEY `fk_cargaentrega_veiculo_idx` (`veiculoid`),
  KEY `fk_cargaentrega_usersaida_idx` (`saidauserid`),
  KEY `fk_cargaentrega_userentrega_idx` (`entregauserid`),
  KEY `fk_cargaentrega_usercreated_idx` (`useridcreated`),
  KEY `fk_cargaentrega_userupdated_idx` (`useridupdated`),
  
  CONSTRAINT `fk_cargaentrega_motorista` FOREIGN KEY (`motoristaid`) REFERENCES `motorista` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_unidadesaida` FOREIGN KEY (`unidadesaidaid`) REFERENCES `unidade` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_usercreated` FOREIGN KEY (`useridcreated`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_userentrega` FOREIGN KEY (`entregauserid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_usersaida` FOREIGN KEY (`saidauserid`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_userupdated` FOREIGN KEY (`useridupdated`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentrega_veiculo` FOREIGN KEY (`veiculoid`) REFERENCES `veiculo` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `cargaentregaitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cargaentregaid` int(11) NOT NULL,
  `etiquetaean13` varchar(13) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cargaentregaitem_carga_idx` (`cargaentregaid`),
  KEY `fk_cargaentregaitem_etoqueta_idx` (`etiquetaean13`),
  CONSTRAINT `fk_cargaentregaitem_carga` FOREIGN KEY (`cargaentregaid`) REFERENCES `cargaentrega` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentregaitem_etoqueta` FOREIGN KEY (`etiquetaean13`) REFERENCES `etiquetas` (`ean13`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `cargaentregaitem` 
ADD COLUMN `ctechave` VARCHAR(44) NULL AFTER `etiquetaean13`,
ADD COLUMN `ctecnpj` VARCHAR(14) NULL AFTER `ctechave`,
ADD COLUMN `ctenumero` INT(11) NULL AFTER `ctecnpj`;


CREATE TABLE `etiquetas_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ean13` VARCHAR(13) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `useridcreated` INT(11) NOT NULL,
  `origem` ENUM('cargaentradaitem', 'cargatransferitem', 'cargaentregaitem') NOT NULL,
  `origemid` INT(11) NOT NULL,
  `action` ENUM('add', 'delete', 'update') NOT NULL,
  PRIMARY KEY (`id`)
);


ALTER TABLE `etiquetas_log` 
ADD INDEX `fk_etiquetas_log_usercreated_idx` (`useridcreated` ASC),
ADD INDEX `idx_etiquetas_log_ean13` (`ean13` ASC),
ADD INDEX `idx_etiquetas_log_origem` (`origem` ASC),
ADD INDEX `idx_etiquetas_log_origemid` (`origemid` ASC),
ADD INDEX `idx_etiquetas_log_action` (`action` ASC),
ADD INDEX `idx_etiquetas_log_createdat` (`created_at` ASC);

ALTER TABLE `etiquetas_log` 
ADD CONSTRAINT `fk_etiquetas_log_usercreated`
  FOREIGN KEY (`useridcreated`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `etiquetas` 
ADD COLUMN `logatualid` INT(11) NULL ,
ADD INDEX `fk_etiquetas_log_idx` (`logatualid` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_log`
  FOREIGN KEY (`logatualid`)
  REFERENCES `etiquetas_log` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `etiquetas_log` 
ADD COLUMN `detalhe` VARCHAR(5000) NULL AFTER `action`;

ALTER TABLE `etiquetas` 
DROP FOREIGN KEY `fk_etiquetas_log`;

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_log`
  FOREIGN KEY (`logatualid`)
  REFERENCES `etiquetas_log` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;




ALTER TABLE `etiquetas` 
ADD COLUMN `statusanterior` ENUM('1', '2', '3', '4', '5') NULL AFTER `status`,
ADD COLUMN `unidadeatualid` INT(11) NULL AFTER `logatualid`,
ADD COLUMN `travado` INT(1) NULL DEFAULT 0 COMMENT 'Etiqueta travada para outros processo de movimentação' AFTER `unidadeatualid`,
ADD INDEX `fk_etiquetas_unidadeatual_idx` (`unidadeatualid` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_unidadeatual`
  FOREIGN KEY (`unidadeatualid`)
  REFERENCES `unidade` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


CREATE TABLE `paletes` (
  `id` int(11) NOT NULL,
  `unidadeid` int(11) NOT NULL,
  `ean13` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `useridcreated` int(11) NOT NULL,
  `useridupdated` int(11) NOT NULL,
  `volqtde` int(11) NOT NULL,
  `pesototal` double NOT NULL,
  `status` enum('1','2','3','4','5') NOT NULL COMMENT '1=EmAberto, 2=Lacrado, 3=Despachado, 4=Cancelado',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_paletes_ean13` (`ean13`),
  KEY `fk_paletes_usuario_idx` (`useridcreated`),
  KEY `fk_paletes_usuarioupdated_idx` (`useridupdated`),
  KEY `fk_paletes_unidadeatual_idx` (`unidadeid`),
  CONSTRAINT `fk_paletes_unidade` FOREIGN KEY (`unidadeid`) REFERENCES `unidade` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_paletes_usuario` FOREIGN KEY (`useridcreated`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_paletes_usuarioupdated` FOREIGN KEY (`useridupdated`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Paletes';

ALTER TABLE `paletes` 
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ;

ALTER TABLE `paletes` 
ADD COLUMN `descricao` VARCHAR(45) NULL AFTER `ean13`;

ALTER TABLE `etiquetas` 
ADD COLUMN `paleteid` INT(11) NULL AFTER `travado`,
ADD INDEX `fk_etiquetas_palete_idx` (`paleteid` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_palete`
  FOREIGN KEY (`paleteid`)
  REFERENCES `paletes` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;
  
ALTER TABLE `paletes` 
ADD COLUMN `erroqtde` int(11)  default 0,
ADD COLUMN `erromsg` varchar(5000) null;

CREATE TABLE `paletesitem` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `paleteid` INT(11) NOT NULL,
  `ean13` VARCHAR(13) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_paletesitem_etiqueta_idx` (`ean13` ASC),
  INDEX `fk_paletesitem_palete_idx` (`paleteid` ASC),
  UNIQUE INDEX `unique_paletesitem` (`paleteid` ASC, `ean13` ASC),
  CONSTRAINT `fk_paletesitem_etiqueta`
    FOREIGN KEY (`ean13`)
    REFERENCES `etiquetas` (`ean13`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_paletesitem_palete`
    FOREIGN KEY (`paleteid`)
    REFERENCES `paletes` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE);

ALTER TABLE `etiquetas_log` 
CHANGE COLUMN `origem` `origem` ENUM('cargaentradaitem', 'cargatransferitem', 'cargaentregaitem', 'paleteitem') NOT NULL ;
   
 

ALTER TABLE `paletes` 
CHANGE COLUMN `descricao` `descricao` VARCHAR(150) NULL DEFAULT NULL ;

ALTER TABLE `cargaentrada` 
ADD COLUMN `conferidoprogresso` DOUBLE NOT NULL DEFAULT 0 AFTER `editadomanualmente`;

ALTER TABLE `etiquetas` 
ADD COLUMN `conferidoentrada` INT(1) NOT NULL DEFAULT 0,
ADD COLUMN `conferidoentradauserid` INT(11) NULL AFTER `conferidoentrada`,
ADD COLUMN `conferidoentradadh` DATETIME NULL AFTER `conferidoentradauserid`,
ADD COLUMN `conferidoentradauuid` VARCHAR(45) NULL AFTER `conferidoentradadh`;


ALTER TABLE `etiquetas` 
ADD INDEX `fk_etiquetas_userconferido_idx` (`conferidoentradauserid` ASC),
ADD INDEX `fk_etiquetas_conferidouuid_idx` (`conferidoentradauuid` ASC);

ALTER TABLE `etiquetas` 
ADD CONSTRAINT `fk_etiquetas_userconferido`
  FOREIGN KEY (`conferidoentradauserid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_etiquetas_conferidouuid`
  FOREIGN KEY (`conferidoentradauuid`)
  REFERENCES `dispositivo` (`uuid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `cargaentrada` 
ADD COLUMN `conferidoqtde` INT(11) NOT NULL DEFAULT 0 AFTER `conferidoprogresso`;



CREATE TABLE `usuario_unidade` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuarioid` INT(11) NOT NULL,
  `unidadeid` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `created_usuarioid` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usuario_unidade_unique` (`unidadeid` ASC, `usuarioid` ASC),
  INDEX `fk_usuario_unidade_usuario_idx` (`usuarioid` ASC),
  INDEX `fk_usuario_unidade_usuariocreated_idx` (`created_usuarioid` ASC),
  CONSTRAINT `fk_usuario_unidade_unidade`
    FOREIGN KEY (`unidadeid`)
    REFERENCES `unidade` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_unidade_usuario`
    FOREIGN KEY (`usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_unidade_usuariocreated`
    FOREIGN KEY (`created_usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE);


ALTER TABLE `etiquetas_log` 
CHANGE COLUMN `detalhe` `detalhe` MEDIUMTEXT NULL DEFAULT NULL ;




start transaction;
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `ordem`) VALUES ('cargas', 'Gestão de cargas', '1', '04');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entradas', 'Entrada de cargas', '1', 'cargas', '04.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.transferencia', 'Transferência de cargas entre unidades', '1', 'cargas', '04.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entrega', 'Entrega de cargas ao cliente', '1', 'cargas', '04.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.paletes', 'Gestão de paletes', '1', 'cargas', '04.04');

INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entradas.consulta', 'Consulta de entradas de cargas', '0', 'cargas.entradas', '04.01.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entradas.save', 'Criar e alterar carga de entrada', '0', 'cargas.entradas', '04.01.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entradas.alterarstatus', 'Alterar status da carga de entrada', '0', 'cargas.entradas', '04.01.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entradas.delete', 'Excluir uma carga de entrada', '0', 'cargas.entradas', '04.01.04');

INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.transferencia.consulta', 'Consulta de transferência de cargas', '0', 'cargas.transferencia', '04.02.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.transferencia.save', 'Criar e alterar carga de transferência', '0', 'cargas.transferencia', '04.02.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.transferencia.alterarstatus', 'Alterar status da carga de transferência', '0', 'cargas.transferencia', '04.02.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.transferencia.delete', 'Excluir uma carga de transferência', '0', 'cargas.transferencia', '04.02.04');

INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entrega.consulta', 'Consulta de entregas de cargas', '0', 'cargas.entrega', '04.03.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entrega.save', 'Criar e alterar carga de entrega', '0', 'cargas.entrega', '04.03.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entrega.alterarstatus', 'Alterar status da carga de entrega', '0', 'cargas.entrega', '04.03.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.entrega.delete', 'Excluir uma carga de entrega', '0', 'cargas.entrega', '04.03.04');

INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.paletes.consulta', 'Consulta de paletes', '0', 'cargas.paletes', '04.04.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.paletes.save', 'Criar e alterar paletes', '0', 'cargas.paletes', '04.04.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.paletes.alterarstatus', 'Alterar status do palete', '0', 'cargas.paletes', '04.04.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.paletes.delete', 'Excluir um palete', '0', 'cargas.paletes', '04.04.04');


INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.etiquetas', 'Consulta de etiquetas', '1', 'cargas', '04.05');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.etiquetas.consulta', 'Consulta de etiquetas', '0', 'cargas.etiquetas', '04.05.01');


commit;



start transaction;
update coletas_nota
set baixanfestatus=0, xmlprocessado=0, baixanfetentativas=0, baixanfemsg=null
where date(dhlocal_created_at) >= date(date_add(now(), interval -90 day))
;
commit;


-- php artisan bsoft:processanf
-- php artisan coletanota:indexanf





