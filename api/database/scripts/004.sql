CREATE TABLE `cargaentregabaixaimg` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cargaentregaid` INT(11) NOT NULL,
  `ctechave` VARCHAR(44) NULL,
  `tipo` ENUM('cte', 'carga') NOT NULL,
  `origem` ENUM('1', '2') NOT NULL COMMENT '1=Interno, 2= App do Motorista',
  `uuid` VARCHAR(45) NULL,
  `motoritstaid` INT(11) NULL,
  `usuarioid` INT(11) NULL,
  `baixadhlocal` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `imglocal` ENUM('local', 's3') NULL,
  `imgfullname` VARCHAR(1500) NULL,
  `imgmd5` VARCHAR(45) NULL,
  `imgext` VARCHAR(10) NULL,
  `imgsize` INT(11) NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_cargaentregabaixaimg_usuario_idx` (`usuarioid` ASC),
  INDEX `fk_cargaentregabaixaimg_motorista_idx` (`motoritstaid` ASC),
  INDEX `fk_cargaentregabaixaimg_dispositivo_idx` (`uuid` ASC),
  INDEX `fk_cargaentregabaixaimg_cargaentrega_idx` (`cargaentregaid` ASC),
  CONSTRAINT `fk_cargaentregabaixaimg_usuario`
    FOREIGN KEY (`usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentregabaixaimg_motorista`
    FOREIGN KEY (`motoritstaid`)
    REFERENCES `motorista` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentregabaixaimg_dispositivo`
    FOREIGN KEY (`uuid`)
    REFERENCES `dispositivo` (`uuid`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_cargaentregabaixaimg_cargaentrega`
    FOREIGN KEY (`cargaentregaid`)
    REFERENCES `cargaentrega` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `cargaentregaitem` 
ADD INDEX `idxcargaentregaitemchavete` (`ctechave` ASC);

ALTER TABLE `cargaentregabaixaimg` 
ADD INDEX `fk_cargaentregabaixaimg_cargaentregaitem_idx` (`ctechave` ASC);

ALTER TABLE `cargaentregabaixaimg` 
ADD CONSTRAINT `fk_cargaentregabaixaimg_cargaentregaitem`
  FOREIGN KEY (`ctechave`)
  REFERENCES `cargaentradaitem` (`nfechave`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `cargaentregabaixaimg` 
ADD UNIQUE INDEX `unique_cargaentregabaixaimg_chave` (`ctechave` ASC),
ADD INDEX `unique_cargaentregabaixaimg_carga` (`tipo` ASC, `cargaentregaid` ASC, `ctechave` ASC);

ALTER TABLE `cargaentregaitem` 
ADD COLUMN `entregue` INT(1) NOT NULL DEFAULT 0 AFTER `ctenumero`;


ALTER TABLE `cargaentregaitem` 
ADD COLUMN `entreguedh` DATETIME NULL AFTER `entregue`,
ADD INDEX `idx_cargaentregaitem_entreguedh` (`entreguedh` ASC);


ALTER TABLE `cargaentregabaixaimg` 
DROP FOREIGN KEY `fk_cargaentregabaixaimg_cargaentregaitem`;

ALTER TABLE `cargaentregabaixaimg` 
DROP INDEX `unique_cargaentregabaixaimg_chave` ;

ALTER TABLE `cargaentregabaixaimg` 
ADD CONSTRAINT `fk_cargaentregabaixaimg_cargaentregaitem`
  FOREIGN KEY (`ctechave`)
  REFERENCES `cargaentregaitem` (`ctechave`)
  ON UPDATE CASCADE;




ALTER TABLE `cargaentrega` 
DROP FOREIGN KEY `fk_cargaentrega_userentrega`;

ALTER TABLE `cargaentrega` 
DROP COLUMN `entregauserid`,
DROP COLUMN `entregadh`,

ADD COLUMN `entregatipo` ENUM('1', '2') NULL COMMENT '1=Por Carga, 2=Por item' AFTER `erromsg`,
ADD COLUMN `entregaqtdeitem` INT(11) NULL DEFAULT 0 AFTER `entregatipo`,
ADD COLUMN `entregapercentual` DOUBLE NULL DEFAULT 0 AFTER `entregaqtdeitem`,
ADD COLUMN `entregaultimadh` DATETIME NULL AFTER `entregapercentual`,
DROP INDEX `fk_cargaentrega_userentrega_idx` ;



ALTER TABLE `cargaentregaitem` 
ADD INDEX `idx_cargaentregaitem_entregue` (`entregue` ASC);



ALTER TABLE `cargaentregabaixaimg` 
ADD COLUMN `operacao` ENUM('A', 'M') NULL DEFAULT 'A' COMMENT 'A=Automatica, M=Manual' ;


ALTER TABLE `cargaentrega` 
ADD COLUMN `entregaoperacao` ENUM('A', 'M') NULL DEFAULT 'A' COMMENT 'A=Automatica, M=Manual' AFTER `entregaultimadh`;

ALTER TABLE `cargaentregaitem` 
ADD COLUMN `entregueoperacao` ENUM('A', 'M') NULL DEFAULT 'A' COMMENT 'A=Automatica, M=Manual';


ALTER TABLE `cargaentrega` 
ADD COLUMN `senha` VARCHAR(6) NULL AFTER `id`;

update cargaentrega
set senha=LEFT(UUID(), 6)
where senha is null;

ALTER TABLE `cargaentrega` 
CHANGE COLUMN `senha` `senha` VARCHAR(6) NOT NULL ,
ADD UNIQUE INDEX `senha_UNIQUE` (`senha` ASC);

ALTER TABLE `cargaentrega` 
DROP INDEX `senha_UNIQUE` ;

ALTER TABLE `coletas_nota` 
ADD COLUMN `coletaavulsantentativa` INT(11) NULL DEFAULT 0 AFTER `coletaavulsaerrormsg`;



-- aplicado em produca