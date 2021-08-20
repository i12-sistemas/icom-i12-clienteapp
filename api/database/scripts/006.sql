CREATE TABLE `guaritacheck` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unidadeid` INT(11) NOT NULL,
  `motoritstaid` INT(11) NOT NULL,
  `veiculoid` INT(11) NOT NULL,
  `status` ENUM('1', '2') NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `userid` INT(11) NOT NULL,
  `erroqtde` INT(11) NULL DEFAULT 0,
  `erromsg` VARCHAR(5000) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_guaritacheck_usuario_idx` (`userid` ASC),
  INDEX `fk_guaritacheck_unidade_idx` (`unidadeid` ASC),
  INDEX `fk_guaritacheck_motorista_idx` (`motoritstaid` ASC),
  INDEX `fk_guaritacheck_veiculo_idx` (`veiculoid` ASC),
  CONSTRAINT `fk_guaritacheck_usuario`
    FOREIGN KEY (`userid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheck_unidade`
    FOREIGN KEY (`unidadeid`)
    REFERENCES `unidade` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheck_motorista`
    FOREIGN KEY (`motoritstaid`)
    REFERENCES `motorista` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheck_veiculo`
    FOREIGN KEY (`veiculoid`)
    REFERENCES `veiculo` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `guaritacheckitem` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `guaritacheckid` INT(11) NOT NULL,
  `nfechave` VARCHAR(44) NOT NULL,
  `nfenumero` INT(11) NOT NULL,
  `nfecnpj` VARCHAR(14) NOT NULL,
  `clienteid` INT(11) NULL,
  `created_at` DATETIME NOT NULL,
  `userid` INT(11) NOT NULL,
  `coletaid` INT(11) NULL,
  `error` INT(1) NULL,
  `errormsg` VARCHAR(5000) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `nfechave_UNIQUE` (`nfechave` ASC),
  INDEX `fk_guaritacheckitem_guarita_idx` (`guaritacheckid` ASC),
  INDEX `fk_guaritacheckitem_coleta_idx` (`coletaid` ASC),
  INDEX `fk_guaritacheckitem_cliente_idx` (`userid` ASC),
  CONSTRAINT `fk_guaritacheckitem_guarita`
    FOREIGN KEY (`guaritacheckid`)
    REFERENCES `guaritacheck` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheckitem_coleta`
    FOREIGN KEY (`coletaid`)
    REFERENCES `coletas` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheckitem_coletasnotas`
    FOREIGN KEY (`nfechave`)
    REFERENCES `coletas_nota` (`notachave`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheckitem_cliente`
    FOREIGN KEY (`userid`)
    REFERENCES `cliente` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_guaritacheckitem_usuario`
    FOREIGN KEY (`userid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `guaritacheck` 
CHANGE COLUMN `motoritstaid` `motoritstaid` INT(11) NULL ,
CHANGE COLUMN `veiculoid` `veiculoid` INT(11) NULL ;


ALTER TABLE `guaritacheck` 
DROP FOREIGN KEY `fk_guaritacheck_motorista`;

ALTER TABLE `guaritacheck` 
CHANGE COLUMN `motoritstaid` `motoristaid` INT(11) NULL DEFAULT NULL ;

ALTER TABLE `guaritacheck` 
ADD CONSTRAINT `fk_guaritacheck_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON UPDATE CASCADE;


ALTER TABLE `guaritacheckitem` 
DROP FOREIGN KEY `fk_guaritacheckitem_coletasnotas`;


ALTER TABLE `guaritacheckitem` 
DROP FOREIGN KEY `fk_guaritacheckitem_coleta`;

ALTER TABLE `guaritacheckitem` 
DROP COLUMN `coletaid`,
DROP INDEX `fk_guaritacheckitem_coleta_idx` ;


ALTER TABLE `guaritacheckitem` 
CHANGE COLUMN `error` `erro` INT(1) NOT NULL DEFAULT 0 ,
CHANGE COLUMN `errormsg` `erromsg` VARCHAR(5000) NULL DEFAULT NULL ;


ALTER TABLE `guaritacheck` 
ADD COLUMN `motoristalock` INT(1) NOT NULL DEFAULT 0 AFTER `veiculoid`;


ALTER TABLE `coletas_nota` 
DROP FOREIGN KEY `fk_coletas_nota_dispositivo`;

ALTER TABLE `coletas_nota` 
CHANGE COLUMN `uuid` `uuid` VARCHAR(45) NULL ;

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `coletas_nota` 
ADD CONSTRAINT `fk_coletas_nota_dispositivo`
  FOREIGN KEY (`uuid`)
  REFERENCES `dispositivo` (`uuid`)
  ON UPDATE CASCADE;
  
SET FOREIGN_KEY_CHECKS=1;


ALTER TABLE `coletas_nota` 
ADD COLUMN `guarita` INT(1) NULL DEFAULT 0 AFTER `xmlprocessado`,
ADD COLUMN `guaritauserid` INT(11) NULL AFTER `guarita`,
ADD INDEX `fk_coletas_nota_guaritauser_idx` (`guaritauserid` ASC);

ALTER TABLE `coletas_nota` 
ADD CONSTRAINT `fk_coletas_nota_guaritauser`
  FOREIGN KEY (`guaritauserid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



ALTER TABLE `coletas_nota` 
ADD COLUMN `guaritaitemid` INT(11) NULL AFTER `guaritauserid`,
ADD COLUMN `guaritaid` INT(11) NULL AFTER `guaritaitemid`,
ADD INDEX `fk_coletas_nota_guaritaitem_idx` (`guaritaitemid` ASC),
ADD INDEX `fk_coletas_nota_guarita_idx` (`guaritaid` ASC);

ALTER TABLE `coletas_nota` 
ADD CONSTRAINT `fk_coletas_nota_guaritaitem`
  FOREIGN KEY (`guaritaitemid`)
  REFERENCES `guaritacheckitem` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coletas_nota_guarita`
  FOREIGN KEY (`guaritaid`)
  REFERENCES `guaritacheck` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `coletas_nota` 
CHANGE COLUMN `localid` `localid` INT(11) NULL ;


start transaction;
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.guarita', 'Guarita - Conferência de entrada de nota', '1', 'cargas', '04.06');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('cargas.guarita.add', 'Criar, alterar, excluir o próprio lançamento de nota', '0', 'cargas.guarita', '04.06.01');
commit;



-- aplicado em produção dia 28/06/2021

