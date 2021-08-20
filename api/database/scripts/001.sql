/*
drop schema if exists `conectatransp` ;
CREATE SCHEMA `conectatransp` ;
use conectatransp;

drop schema if exists `conecta2` ;
CREATE SCHEMA `conecta2` ;
use conecta2;


drop schema if exists `conectaadmin` ;
CREATE SCHEMA `conectaadmin` ;
use conectaadmin;
*/

drop schema if exists `conecta2` ;
CREATE SCHEMA `conecta2` ;
use conecta2;

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  `login` varchar(255) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `ativo` int(1) not null DEFAULT 1,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fotourl` varchar(500) DEFAULT null,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_login` (`login`),
  UNIQUE KEY `unique_usuario_email` (`email`),
  KEY `idx_usuario_created_usuarioid` (`created_usuarioid`),
  KEY `idx_usuario_cupdated_usuarioid` (`updated_usuarioid`),
  KEY `idx_usuario_login` (`login`),
  KEY `idx_usuario_email` (`email`),
  KEY `idx_usuario_ativo` (`ativo`),  
  CONSTRAINT `fk_usuario_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `cidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_uf` int(11) NULL,
  `uf` varchar(2) NOT NULL,
  `estado` varchar(60) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `codigo_ibge` int(11) NULL,
  `latitude` decimal(10,8) NULL,
  `longitude` decimal(10,8) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL, 
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,  
  PRIMARY KEY (`id`),
  KEY `idx_cidades_id_uf` (`id_uf`),
  KEY `idx_cidades_uf` (`uf`),
  KEY `idx_cidades_cidade` (`cidade`),
  KEY `idx_cidades_codigo_ibge` (`codigo_ibge`),
  CONSTRAINT `fk_cidades_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cidades_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de Municipios/UF IBGE';


CREATE TABLE `regiao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regiao` varchar(60) NOT NULL,
  `sugerirmotorista` int(1) NOT NULL default 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,   
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,    
  PRIMARY KEY (`id`),
  KEY `idx_regiao_regiao` (`regiao`),
  CONSTRAINT `fk_regiao_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_regiao_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE    
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de regiões';


CREATE TABLE `regiao_detalhe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regiaoid` int(11) NOT NULL,
  `cidadeid` int(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_regiao_cidade` (`regiaoid` ASC, `cidadeid` ASC),
  KEY `idx_regiao_detalhe_regiao` (`regiaoid`),
  KEY `idx_regiao_detalhe_cidade` (`cidadeid`),
  CONSTRAINT `fk_regiao_detalhe_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Relacionamento regiao X cidade';



ALTER TABLE `regiao_detalhe` 
ADD CONSTRAINT `fk_regiao_detalhe_cidade`
  FOREIGN KEY (`cidadeid`)
  REFERENCES `cidades` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_regiao_detalhe_regiao`
  FOREIGN KEY (`regiaoid`)
  REFERENCES `regiao` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;




CREATE TABLE `veiculo_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(60) NOT NULL,
  `ativo` int(1) not null DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_veiculo_tipo_tipo` (`tipo`),
  KEY `idx_veiculo_tipo_created_usuarioid` (`created_usuarioid`),
  KEY `idx_veiculo_tipo_cupdated_usuarioid` (`updated_usuarioid`),
  KEY `idx_veiculo_tipo_login` (`tipo`),
  KEY `idx_veiculo_tipo_ativo` (`ativo`),  
  CONSTRAINT `fk_veiculo_tipo_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculo_tipo_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `veiculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(60) NOT NULL,
  `placa` varchar(7) NOT NULL,
  `cidadeid` int(11)  NOT NULL,
  `tipoid` int(11)  NOT NULL,
  `ativo` int(1) not null DEFAULT 1,
  `manutencao` int(1) not null DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  -- UNIQUE KEY `unique_veiculo_tipo` (`placa`), /* desativado pois existem duplicidades */
  KEY `idx_veiculo_created_usuarioid` (`created_usuarioid`),
  KEY `idx_veiculo_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_veiculo_cidadeid` (`cidadeid`),
  KEY `idx_veiculo_placa` (`placa`),
  KEY `idx_veiculo_ativo` (`ativo`),  
  CONSTRAINT `fk_placa_cidade` FOREIGN KEY (`cidadeid`) REFERENCES `cidades` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_placa_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_placao_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `motorista` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cidadeid` int(11)  NOT NULL,
  `veiculoid` int(11)  NOT NULL,
  `nome` varchar(60) NOT NULL,
  `apelido` varchar(60) NULL,
  `fone` varchar(20) NULL,
  `cpf` varchar(11) NOT NULL,
  `gerenciamento` int(11)  NOT NULL,
  `gerenciamentooutros` varchar(50)  NULL,
  `ativo` int(1) not null DEFAULT 1,
  `antt` varchar(50) null,
  `salario` double not null default 0,
  `cnhvencimento` date null,
  `moppvencimento` date null,
  `habilitado` int(1) not null default 0,
  `username` varchar(255) null,
  `pwd` varchar(255) null,
  
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  -- UNIQUE KEY `unique_motorista_cpf` (`cpf`), /* desativa devido a problema no sqlserver duplicidade*/
  UNIQUE KEY `unique_motorista_username` (`username`),
  KEY `idx_motorista_created_usuarioid` (`created_usuarioid`),
  KEY `idx_motorista_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_motorista_cidadeid` (`cidadeid`),
  KEY `idx_motorista_veiculoid` (`veiculoid`),
  KEY `idx_motorista_username` (`username`),
  KEY `idx_motorista_cpf` (`cpf`),
  KEY `idx_motorista_ativo` (`ativo`),  
  KEY `idx_motorista_nome` (`nome`),  
  CONSTRAINT `fk_motorista_cidade` FOREIGN KEY (`cidadeid`) REFERENCES `cidades` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorista_veiculo` FOREIGN KEY (`veiculoid`) REFERENCES `veiculo` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motoristausuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_motorista_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `unidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razaosocial` varchar(255) NOT NULL,
  `fantasia` varchar(255) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `ie` varchar(14) NOT NULL,
  `fone` varchar(20) NULL,
  `logradouro` varchar(20)  NULL,
  `endereco` varchar(255)  NULL,
  `numero` varchar(10)  NULL,
  `bairro` varchar(70)  NULL,
  `cep` varchar(8)  NULL,
  `complemento` varchar(8)  NULL,
  `cidadeid` int(11)  NOT NULL,
  `ativo` int(1) not null DEFAULT 1,
  
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_unidade_cnpj` (`cnpj`),
  KEY `idx_unidade_created_usuarioid` (`created_usuarioid`),
  KEY `idx_unidade_updated_usuarioid` (`updated_usuarioid`),
  KEY `idxunidade_cidadeid` (`cidadeid`),
  KEY `idx_unidade_fantasia` (`fantasia`),
  KEY `idx_unidade_cnpj` (`cnpj`),
  KEY `idx_unidade_ativo` (`ativo`),  
  KEY `idx_unidade_razaosocial` (`razaosocial`),  
  CONSTRAINT `fk_unidade_cidade` FOREIGN KEY (`cidadeid`) REFERENCES `cidades` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_unidade_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_unidade_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;





CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cnpj` varchar(14) NOT NULL,
  `razaosocial` varchar(255) NOT NULL,
  `fantasia` varchar(255) NOT NULL,
  `fantasia_followup` varchar(255) NOT NULL,
  `fone1` varchar(20) NULL,
  `fone2` varchar(20) NULL,
  `obs` mediumtext NULL,

  `logradouro` varchar(20)  NULL,
  `endereco` varchar(255)  NULL,
  `numero` varchar(10)  NULL,
  `bairro` varchar(70)  NULL,
  `cep` varchar(8)  NULL,
  `complemento` varchar(255)  NULL,
  `cidadeid` int(11)  NOT NULL,
  
  `segqui_hr1_i` time NULL,
  `segqui_hr1_f` time NULL,
  `segqui_hr2_i` time NULL,
  `segqui_hr2_f` time NULL,
  
  `sex_hr1_i` time NULL,
  `sex_hr1_f` time NULL,
  `sex_hr2_i` time NULL,
  `sex_hr2_f` time NULL,
  
  `portaria_hr1_i` time NULL,
  `portaria_hr1_f` time NULL,
  `portaria_hr2_i` time NULL,
  `portaria_hr2_f` time NULL,  
  
  `filtro` varchar(50) NULL,  
  `prazoentrega` int(11) NOT NULL default 0,  
  
  `ativo` int(1) not null DEFAULT 1,
  
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  -- UNIQUE KEY `unique_cliente_cnpj` (`cnpj`),
  KEY `idx_cliente_created_usuarioid` (`created_usuarioid`),
  KEY `idx_cliente_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_cliente_cidadeid` (`cidadeid`),
  KEY `idx_cliente_fantasia` (`fantasia`),
  KEY `idx_cliente_fantasia_followup` (`fantasia_followup`),
  KEY `idx_cliente_cnpj` (`cnpj`),
  KEY `idx_cliente_ativo` (`ativo`),  
  KEY `idx_cliente_filtro` (`filtro`),  
  KEY `idx_cliente_razaosocial` (`razaosocial`),  
  CONSTRAINT `fk_cliente_cidade` FOREIGN KEY (`cidadeid`) REFERENCES `cidades` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `idx_emails_nome` (`nome`),
  KEY `idx_emails_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `emails_tags` (
  `emailid` int(11) NOT NULL,
  `tag` varchar(40) NOT NULL,
  UNIQUE KEY `email_UNIQUE` (`emailid`, `tag`),
  KEY `idx_emails_emailid` (`emailid`),
  KEY `idx_emails_tag` (`tag`),
  CONSTRAINT `fk_emails_tags_email` FOREIGN KEY (`emailid`) REFERENCES `emails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


CREATE TABLE `cliente_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emailid` int(11) NOT NULL,
  `clienteid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cliente_email_email_idx` (`emailid`),
  KEY `fk_cliente_email_clientes_idx` (`clienteid`),
  CONSTRAINT `fk_clientesemail_clientes` FOREIGN KEY (`clienteid`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_clientesemail_email` FOREIGN KEY (`emailid`) REFERENCES `emails` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onu` int(11)  NULL,
  `nome` varchar(255) NOT NULL,
  `classerisco` varchar(50) NULL,
  `riscosubs` varchar(50) NULL,
  `riscosubs2` varchar(50) NULL,
  `numrisco` varchar(50) NULL,
  `grupoemb` varchar(50) NULL,
  `provespec` varchar(50) NULL,
  `qtdeltdav` varchar(50) NULL,
  `qtdeltdae` varchar(50) NULL,
  `embibcinst` varchar(50) NULL,
  `embibcprov` varchar(50) NULL,
  `tanqueinst` varchar(50) NULL,
  `tanqueprov` varchar(50) NULL,
  `guia` int(11) NULL,
  `polimeriza` varchar(50) NULL,
  `reage_agua` int(1) NOT NULL Default 0,
  `epi` varchar(50) NULL,
  `kit` varchar(50) NULL,
  
  `ativo` int(1) not null DEFAULT 1,

  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_produto_created_usuarioid` (`created_usuarioid`),
  KEY `idx_produto_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_produto_nome` (`nome`),
  KEY `idx_produto_ativo` (`ativo`),  
  CONSTRAINT `fk_produto_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_produto_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `unidade` 
CHANGE COLUMN `complemento` `complemento` VARCHAR(70) NULL DEFAULT NULL ;


ALTER TABLE `unidade` 
CHANGE COLUMN `ie` `ie` VARCHAR(14) NULL ;


ALTER TABLE `cliente` 
ADD COLUMN `cnpjmemo` VARCHAR(5000) NULL AFTER `cnpj`;



DROP TABLE IF EXISTS `coletas_itens` ;
DROP TABLE IF EXISTS `coletas` ;
CREATE TABLE `coletas` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`chavenota` varchar(44) NULL,
	`dhcoleta` datetime NOT NULL,
	`dhbaixa` datetime NULL,
	`situacao` int(11)  NOT NULL default 0 COMMENT '0=Em aberto (Nao coletada), 1=Coletada',
	`ultimoevento` int(11)  NULL,
	`origemclienteid` int(11)  NOT NULL,
	`destinoclienteid` int(11)  NOT NULL,
   
	`endcoleta_logradouro` varchar(20)  NULL,
	`endcoleta_endereco` varchar(255)  NULL,
	`endcoleta_numero` varchar(10)  NULL,
	`endcoleta_bairro` varchar(70)  NULL,
	`endcoleta_cep` varchar(8)  NULL,
	`endcoleta_complemento` varchar(255)  NULL,
	`endcoleta_cidadeid` int(11)  NOT NULL,

	`motoristaid` int(11)  NOT NULL,

	`contatonome` varchar(255) NOT NULL,
	`contatoemail` varchar(255) NOT NULL,

	`peso` double NOT NULL DEFAULT 0,
	`qtde` double NOT NULL DEFAULT 0,
	`especie` varchar(150) NULL,
	`obs` mediumtext NULL,

	`liberado` INT(1) NOT NULL DEFAULT 0,
	`veiculoexclusico` INT(1) NOT NULL DEFAULT 0,
	`cargaurgente` INT(1) NOT NULL DEFAULT 0,
	`produtosperigosos` INT(1) NOT NULL DEFAULT 0,

	`gestaocliente` INT(1) NOT NULL DEFAULT 0,
	`gestaocliente_id` INT(11) NULL,
	`gestaocliente_ordemcompra` INT(11) NULL,
	`gestaocliente_comprador` VARCHAR(100) NULL,
	`gestaocliente_itenscomprador` mediumtext NULL,
 
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `updated_usuarioid` int(11)  NOT NULL,
  `deleted_usuarioid` int(11)  NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_coleta_chavenota` (`chavenota` ASC),
  KEY `idx_coleta_created_usuarioid` (`created_usuarioid`),
  KEY `idx_coleta_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_coleta_deleted_usuarioid` (`deleted_usuarioid`),
  KEY `idx_coleta_chavenota` (`chavenota`),
  KEY `idx_coleta_dhcoleta` (`dhcoleta`),
  KEY `idx_coleta_dhbaixa` (`dhbaixa`),
  KEY `idx_coleta_situacao` (`situacao`),  
  CONSTRAINT `fk_coleta_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_coleta_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_coleta_usuariodeleted` FOREIGN KEY (`deleted_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `coletas` 
ADD INDEX `fk_coleta_clienteorigem_idx` (`origemclienteid` ASC),
ADD INDEX `fk_coleta_clientedestino_idx` (`destinoclienteid` ASC),
ADD INDEX `fk_coleta_cidadecoleta_idx` (`endcoleta_cidadeid` ASC),
ADD INDEX `fk_coleta_motorista_idx` (`motoristaid` ASC),
ADD INDEX `fk_coleta_clientegestao_idx` (`gestaocliente_id` ASC);

ALTER TABLE `coletas` 
ADD CONSTRAINT `fk_coleta_clienteorigem`
  FOREIGN KEY (`origemclienteid`)
  REFERENCES `cliente` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coleta_clientedestino`
  FOREIGN KEY (`destinoclienteid`)
  REFERENCES `cliente` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_coleta_cidadecoleta`
  FOREIGN KEY (`endcoleta_cidadeid`)
  REFERENCES `cidades` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coleta_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coleta_clientegestao`
  FOREIGN KEY (`gestaocliente_id`)
  REFERENCES `cliente` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



DROP TABLE IF EXISTS `coletas_itens` ;
CREATE TABLE `coletas_itens` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`coletaid` int(11)  NOT NULL,
	`produtoid` int(11)  NULL,
	`produtodescricao` varchar(255) NULL,
	`qtde` double NOT NULL DEFAULT 0,
	`embalagem` varchar(50) NULL,
	`created_at` datetime NOT NULL,
	`created_usuarioid` int(11)  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_coletas_itens_created_usuarioid` (`created_usuarioid`),
  KEY `idx_coletas_itens_coleta` (`coletaid`),
  KEY `idx_coletas_produto` (`produtoid`),
  KEY `idx_coletas_itens_produtodescricao` (`produtodescricao`),
  CONSTRAINT `fk_coletas_itens_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_coletas_itens_produto` FOREIGN KEY (`produtoid`) REFERENCES `produto` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_coletas_itens_coletas` FOREIGN KEY (`coletaid`) REFERENCES `coletas` (`id`)   ON DELETE CASCADE ON UPDATE CASCADE  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `coletas` 
DROP FOREIGN KEY `fk_coleta_clientegestao`;

ALTER TABLE `coletas` 
DROP COLUMN `gestaocliente_id`,
DROP COLUMN `gestaocliente`,
DROP INDEX `fk_coleta_clientegestao_idx` ;
;

ALTER TABLE `coletas` 
CHANGE COLUMN `gestaocliente_ordemcompra` `gestaocliente_ordemcompra` VARCHAR(50) NULL DEFAULT NULL ;


ALTER TABLE `coletas` 
ADD COLUMN `origem` INT(11) NOT NULL COMMENT '1=ADMIN, 2=ORCAMENTO' AFTER `id`;

ALTER TABLE `coletas` 
CHANGE COLUMN `origem` `origem` ENUM('1', '2') NOT NULL COMMENT '1=ADMIN, 2=ORCAMENTO' ;


ALTER TABLE `coletas` 
CHANGE COLUMN `contatonome` `contatonome` VARCHAR(255) NULL ,
CHANGE COLUMN `contatoemail` `contatoemail` VARCHAR(255) NULL ,
CHANGE COLUMN `especie` `especie` VARCHAR(150) NOT NULL ;



ALTER TABLE `coletas_itens` 
ADD COLUMN `updated_usuarioid` INT(11) NOT NULL AFTER `created_usuarioid`,
ADD COLUMN `updated_at` DATETIME NOT NULL AFTER `updated_usuarioid`,
ADD INDEX `fk_coletas_itens_usuarioupdated_idx` (`updated_usuarioid` ASC);

ALTER TABLE `coletas_itens` 
ADD CONSTRAINT `fk_coletas_itens_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;




ALTER TABLE `coletas` 
CHANGE COLUMN `motoristaid` `motoristaid` INT(11) NULL ;



ALTER TABLE `cidades` 
ADD COLUMN `regiaoid` INT(11) NULL AFTER `cidade`,
ADD INDEX `fk_cidades_regiao_idx` (`regiaoid` ASC);

ALTER TABLE `cidades` 
ADD CONSTRAINT `fk_cidades_regiao`
  FOREIGN KEY (`regiaoid`)
  REFERENCES `regiao` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

DROP TABLE IF EXISTS `regiao_detalhe`;




CREATE TABLE `coletas_eventos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `coletaid` INT(11) NOT NULL,
  `tipo` ENUM('insert', 'update', 'delete', 'baixa', 'baixaundo', 'liberado', 'liberadoundo', 'revisaoorcamento') NOT NULL,
  `created_at` datetime NOT NULL,
  `created_usuarioid` int(11)  NOT NULL,
  `detalhe` MEDIUMTEXT NULL,
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_coletas_eventos_created_usuarioid_idx` (`created_usuarioid` ASC),
  INDEX `idx_coletas_eventos_created_at` (`created_at` ASC),
  INDEX `idx_coletas_eventos_tipo` (`tipo` ASC),
  INDEX `idx_coletas_eventos_coletaid` (`coletaid` ASC),
  CONSTRAINT `fk_coletas_eventos_usuari`
    FOREIGN KEY (`created_usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

ALTER TABLE `coletas_eventos` 
ADD COLUMN `datajson` MEDIUMTEXT NULL AFTER `detalhe`;

ALTER TABLE `coletas_eventos` 
CHANGE COLUMN `tipo` `tipo` ENUM('insert', 'update', 'cancel', 'cancelundo', 'baixa', 'baixaundo', 'revisaoorcamento') NOT NULL ;

ALTER TABLE `coletas` 
CHANGE COLUMN `origem` `origem` ENUM('1', '2', '3', '4') NOT NULL COMMENT '1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo' ;

ALTER TABLE `coletas` 
DROP COLUMN `liberado`,
ADD COLUMN `encerramentotipo` ENUM('1', '2', '3') NULL COMMENT '1 = Interno, 2 = Aplicativo motorista, 3 = Painel do cliente' AFTER `situacao`,
ADD COLUMN `justcancelamento` VARCHAR(255) NULL AFTER `deleted_usuarioid`,
CHANGE COLUMN `situacao` `situacao` ENUM('0', '1', '2', '3') NOT NULL DEFAULT '0' COMMENT '0 = Bloqueado, 1 = Liberado, 2 = Encerrado, 3 = Cancelado' ;


ALTER TABLE `coletas` 
CHANGE COLUMN `justcancelamento` `justsituacao` VARCHAR(255) NULL DEFAULT NULL ;


ALTER TABLE `coletas` 
DROP FOREIGN KEY `fk_coleta_usuariodeleted`;

ALTER TABLE `coletas` 
DROP COLUMN `deleted_usuarioid`,
DROP COLUMN `deleted_at`,
DROP INDEX `idx_coleta_deleted_usuarioid` ;


ALTER TABLE `coletas` 
DROP COLUMN `ultimoevento`;


drop table if exists orcamento_itens;
drop table if exists orcamento;
CREATE TABLE `orcamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `situacao` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '0 = Em aberto, 1 = AprovadoColetaBloqueada, 2 = AprovadoColetaLiberada, 3 = Reprovado',
  `justsituacao` varchar(255) DEFAULT NULL,
  `coletaid` int(11) NULL,
  `dhcoleta` datetime NOT NULL,
  `origemclienteid` int(11) NOT NULL,
  `destinoclienteid` int(11) NOT NULL,
  `endcoleta_logradouro` varchar(20) DEFAULT NULL,
  `endcoleta_endereco` varchar(255) DEFAULT NULL,
  `endcoleta_numero` varchar(10) DEFAULT NULL,
  `endcoleta_bairro` varchar(70) DEFAULT NULL,
  `endcoleta_cep` varchar(8) DEFAULT NULL,
  `endcoleta_complemento` varchar(255) DEFAULT NULL,
  `endcoleta_cidadeid` int(11) NOT NULL,
  `motoristaid` int(11) DEFAULT NULL,
  `contatonome` varchar(255) DEFAULT NULL,
  `contatoemail` varchar(255) DEFAULT NULL,
  
  `vlrfrete` double NOT NULL DEFAULT '0',
  `tomador` enum('CIF','FOB') NOT NULL DEFAULT 'CIF',
  
  `peso` double NOT NULL DEFAULT '0',
  `qtde` double NOT NULL DEFAULT '0',
  `especie` varchar(150) NOT NULL,
  `obscoleta` mediumtext,
  `obsorcamento` mediumtext,
  `veiculoexclusico` int(1) NOT NULL DEFAULT '0',
  `cargaurgente` int(1) NOT NULL DEFAULT '0',
  `produtosperigosos` int(1) NOT NULL DEFAULT '0',
  `gestaocliente_ordemcompra` varchar(50) DEFAULT NULL,
  `gestaocliente_comprador` varchar(100) DEFAULT NULL,
  `gestaocliente_itenscomprador` mediumtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `updatedstatus_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,
  `updatedstatus_usuarioid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_orcamento_coleta` (`coletaid`),
  KEY `idx_orcamento_created_usuarioid` (`created_usuarioid`),
  KEY `idx_orcamento_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_orcamento_updatedstatus_usuarioid` (`updatedstatus_usuarioid`),
  KEY `idx_orcamento_dhcoleta` (`dhcoleta`),
  KEY `idx_orcamento_created_at` (`created_at`),
  KEY `idx_orcamento_updatedstatus_at` (`updatedstatus_at`),
  KEY `fk_orcamento_coleta_idx` (`coletaid`),
  KEY `fk_orcamento_clienteorigem_idx` (`origemclienteid`),
  KEY `fk_orcamento_clientedestino_idx` (`destinoclienteid`),
  KEY `fk_orcamento_cidadecoleta_idx` (`endcoleta_cidadeid`),
  KEY `fk_orcamento_motorista_idx` (`motoristaid`),
  CONSTRAINT `fk_orcamento_coleta` FOREIGN KEY (`coletaid`) REFERENCES `coletas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_cidadecoleta` FOREIGN KEY (`endcoleta_cidadeid`) REFERENCES `cidades` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_clientedestino` FOREIGN KEY (`destinoclienteid`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_orcamento_clienteorigem` FOREIGN KEY (`origemclienteid`) REFERENCES `cliente` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_motorista` FOREIGN KEY (`motoristaid`) REFERENCES `motorista` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_usuarioupdatedstatus` FOREIGN KEY (`updatedstatus_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


drop table if exists orcamento_itens;
CREATE TABLE `orcamento_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orcamentoid` int(11) NOT NULL,
  `produtoid` int(11) DEFAULT NULL,
  `produtodescricao` varchar(255) DEFAULT NULL,
  `qtde` double NOT NULL DEFAULT '0',
  `embalagem` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orcamento_itens_created_usuarioid` (`created_usuarioid`),
  KEY `idx_orcamento_itens_orcamento` (`orcamentoid`),
  KEY `idx_orcamento_produto` (`produtoid`),
  KEY `idx_orcamento_itens_produtodescricao` (`produtodescricao`),
  KEY `fk_orcamento_itens_usuarioupdated_idx` (`updated_usuarioid`),
  CONSTRAINT `fk_orcamento_itens_orcamento` FOREIGN KEY (`orcamentoid`) REFERENCES `orcamento` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_itens_produto` FOREIGN KEY (`produtoid`) REFERENCES `produto` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_itens_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orcamento_itens_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `usuariotokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuariotokens_uuid` (`uuid`),
  KEY `idx_usuariotokens_username` (`username`),
  KEY `idx_usuariotokens_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `perfilacesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  `ativo` int(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,  
  PRIMARY KEY (`id`),
  KEY `idx_perfilacesso_created_usuarioid` (`created_usuarioid`),
  KEY `idx_perfilacesso_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_perfilacesso_created_at` (`created_at`),
  KEY `idx_perfilacesso_updated_at` (`updated_at`),
  CONSTRAINT `fk_perfilacesso_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_perfilacesso_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Perfil de usuario';

ALTER TABLE `perfilacesso` 
ADD UNIQUE INDEX `unique_perfilacesso_descricao` (`descricao` ASC);

CREATE TABLE `permissao` (
  `id` varchar(200) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `detalhe` mediumtext,
  `grupo` int(1) NOT NULL DEFAULT '0',
  `idpai` int(11) DEFAULT NULL,
  `ordem` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Cadastro de permissões de acesso ao sistema';


ALTER TABLE `permissao` 
CHANGE COLUMN `idpai` `idpai` VARCHAR(200) NULL DEFAULT NULL ,
ADD INDEX `fk_permissao_pai_idx` (`idpai` ASC);

ALTER TABLE `permissao` 
ADD CONSTRAINT `fk_permissao_pai`
  FOREIGN KEY (`idpai`)
  REFERENCES `permissao` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;




start transaction;
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros', 'Cadastros', '', 1, null, '01');
	insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.clientes', 'Clientes', '', 1, 'cadastros', '01.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.clientes.consulta', 'Consultar/visualizar', '', 0, 'cadastros.clientes', '01.01.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.clientes.save', 'Alterar e criar novos clientes', '', 0, 'cadastros.clientes', '01.01.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.clientes.delete', 'Excluir cadastro de cliente', '', 0, 'cadastros.clientes', '01.01.03');
        
	insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.produtos', 'Produtos', '', 1, 'cadastros', '01.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.produtos.consulta', 'Consultar/visualizar', '', 0, 'cadastros.produtos', '01.02.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.produtos.save', 'Alterar e criar novos produtos', '', 0, 'cadastros.produtos', '01.02.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.produtos.delete', 'Excluir cadastro de produtos', '', 0, 'cadastros.produtos', '01.02.03');     
        
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional', 'Operacional', '', 1, null, '00');
	insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas', 'Coletas', '', 1, 'operacional', '00.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas.consulta', 'Consultar/visualizar uma coleta', '', 0, 'operacional.coletas', '00.01.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas.save', 'Alterar e criar novas coletas', '', 0, 'operacional.coletas', '00.01.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas.cancelar', 'Cancelar um coleta em aberto', '', 0, 'operacional.coletas', '00.01.03');
        
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial', 'Comercial', '', 1, null, '02');
	insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos', 'Orçamentos', '', 1, 'comercial', '02.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.consulta', 'Consultar/visualizar um orçamento', '', 0, 'comercial.orcamentos', '02.01.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.save', 'Alterar e criar novos orçamentos', '', 0, 'comercial.orcamentos', '02.01.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.alterarstatus', 'Alterar o status de um orçamento', '', 0, 'comercial.orcamentos', '02.01.03');
        
        
commit;


CREATE TABLE `perfilacesso_permissoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `perfilid` INT(11) NOT NULL,
  `permissaoid` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_perfilacesso_permissoes` (`perfilid` ASC, `permissaoid` ASC),
  INDEX `fk_perfilacesso_permissoes_permissoes_idx` (`permissaoid` ASC),
  CONSTRAINT `fk_perfilacesso_permissoes_perfil`
    FOREIGN KEY (`perfilid`)
    REFERENCES `perfilacesso` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_perfilacesso_permissoes_permissoes`
    FOREIGN KEY (`permissaoid`)
    REFERENCES `permissao` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `usuario_perfil` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuarioid` INT(11) NOT NULL,
  `perfilid` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `created_usuarioid` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_perfil_perfil_idx` (`perfilid` ASC),
  INDEX `fk_usuario_perfil_usuario_idx` (`usuarioid` ASC),
  INDEX `fk_usuario_perfil_usuariocreated_idx` (`created_usuarioid` ASC),
  UNIQUE INDEX `unique_usuario_perfil` (`perfilid` ASC, `usuarioid` ASC),
  CONSTRAINT `fk_usuario_perfil_perfil`
    FOREIGN KEY (`perfilid`)
    REFERENCES `perfilacesso` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_perfil_usuario`
    FOREIGN KEY (`usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_perfil_usuariocreated`
    FOREIGN KEY (`created_usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE);


ALTER TABLE `veiculo` 
ADD COLUMN `proprietario` ENUM('I', 'T') NOT NULL DEFAULT 'T' COMMENT 'I=interno, T=terceiro\n' AFTER `placa`;



DROP TABLE IF EXISTS `veiculo_km` ;
DROP TABLE IF EXISTS `manutencao` ;
DROP TABLE IF EXISTS `manutencaoservicos` ;
CREATE TABLE `manutencaoservicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  `proxmanut_km` int(11) NOT NULL,
  `proxmanut_dias` int(11) NOT NULL,
  `ativo` int(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,  
  PRIMARY KEY (`id`),
  KEY `idx_manutencaoservicos_created_usuarioid` (`created_usuarioid`),
  KEY `idx_manutencaoservicos_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_manutencaoservicos_created_at` (`created_at`),
  KEY `idx_manutencaoservicos_updated_at` (`updated_at`),
  CONSTRAINT `fk_manutencaoservicos_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_manutencaoservicos_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de serviço para manutenção';

DROP TABLE IF EXISTS `manutencao` ;
CREATE TABLE `manutencao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculoid` int(11) NOT NULL,
  `servicoid` int(11) NOT NULL,
  `kmatual` int(11) NOT NULL,
  `codpeca` varchar(50) NULL,
  `obs` mediumtext NULL,
  `validadedias` int(11) NOT NULL,
  `validadekm` int(11) NOT NULL,
  `realizado` int(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,  
  PRIMARY KEY (`id`),
  KEY `idx_manutencao_created_usuarioid` (`created_usuarioid`),
  KEY `idx_manutencao_updated_usuarioid` (`updated_usuarioid`),
  KEY `idx_manutencao_created_at` (`created_at`),
  KEY `idx_manutencao_updated_at` (`updated_at`),
  CONSTRAINT `fk_manutencao_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_manutencao_usuarioupdated` FOREIGN KEY (`updated_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de manutenção';


ALTER TABLE `manutencao` 
ADD INDEX `fk_manutencao_servico_idx` (`servicoid` ASC),
ADD INDEX `fk_manutencao_veiculo_idx` (`veiculoid` ASC),
ADD INDEX `idx_manutencao_realizado` (`realizado` ASC),
ADD INDEX `idx_manutencao_kmatual` (`kmatual` ASC);

ALTER TABLE `manutencao` 
ADD CONSTRAINT `fk_manutencao_servico`
  FOREIGN KEY (`servicoid`)
  REFERENCES `manutencaoservicos` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_manutencao_veiculo`
  FOREIGN KEY (`veiculoid`)
  REFERENCES `veiculo` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

DROP TABLE IF EXISTS `veiculo_km` ;
CREATE TABLE `veiculo_km` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculoid` int(11) NOT NULL,
  `km` int(11) NOT NULL,
  `tableorigem` varchar(75) NULL,
  `tableid` int(11) NULL,
  `dhleitura` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_veiculo_km_created_veiculoid` (`veiculoid`),
  KEY `idx_veiculo_km_created_usuarioid` (`created_usuarioid`),
  KEY `idx_veiculo_km_created_at` (`created_at`),
  KEY `idx_veiculo_km_dhleitura` (`dhleitura`),
  KEY `idx_veiculo_km_km` (`km`),
  KEY `idx_veiculo_km_tableorigem` (`tableorigem`),
  KEY `idx_veiculo_km_tableid` (`tableid`),
  CONSTRAINT `fk_veiculo_km_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculo_km_veiculo` FOREIGN KEY (`veiculoid`) REFERENCES `veiculo` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de manutenção';


DROP TABLE IF EXISTS `veiculo_alertamanut` ;
CREATE TABLE `veiculo_alertamanut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculoid` int(11) NOT NULL,
  `prioridade` ENUM('1','2','3') NOT NULL,
  `revoked` INT(1) DEFAULT 0,
  `obs` varchar(100) NULL,
  `tempoprevisto` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `revoked_at` datetime NOT NULL,
  `revoked_usuarioid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_veiculo_alertamanut_created_veiculoid` (`veiculoid`),
  KEY `idx_veiculo_alertamanut_created_usuarioid` (`created_usuarioid`),
  KEY `idx_veiculo_alertamanut_revoked_usuarioid` (`revoked_usuarioid`),
  KEY `idx_veiculo_alertamanut_created_at` (`created_at`),
  KEY `idx_veiculo_alertamanut_revoked_at` (`revoked_at`),
  KEY `idx_veiculo_alertamanut_revoked` (`revoked`),
  CONSTRAINT `fk_veiculo_alertamanut_usuariocreated` FOREIGN KEY (`created_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculo_alertamanut_revoked_usuario` FOREIGN KEY (`revoked_usuarioid`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculo_alertamanut_veiculo` FOREIGN KEY (`veiculoid`) REFERENCES `veiculo` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Cadastro de manutenção';

ALTER TABLE `veiculo` 
DROP COLUMN `manutencao`,
ADD COLUMN `alertamanutid` INT(11) NULL AFTER `tipoid`,
ADD INDEX `fk_veiculo_alertamanut_idx` (`alertamanutid` ASC);
ALTER TABLE `veiculo` 
ADD CONSTRAINT `fk_veiculo_alertamanut`
  FOREIGN KEY (`alertamanutid`)
  REFERENCES `veiculo_alertamanut` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;


ALTER TABLE `veiculo_alertamanut` 
CHANGE COLUMN `revoked_at` `revoked_at` DATETIME NULL ,
CHANGE COLUMN `revoked_usuarioid` `revoked_usuarioid` INT(11) NULL ;


CREATE TABLE `config` (
  `id` VARCHAR(255) NOT NULL,
  `tipo` ENUM('string', 'integer', 'double', 'datetime', 'boolean', 'file', 'mediumtext', 'json') NOT NULL,
  `valor` VARCHAR(500) NULL,
  `texto` MEDIUMTEXT NULL,
  `arquivo` LONGBLOB NULL,
  `ext` VARCHAR(45) NULL,
  `updated_at` DATETIME NOT NULL,
  `updated_usuarioid` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_config_usuarioupdate_idx` (`updated_usuarioid` ASC),
  INDEX `idx_config_tipo` (`tipo` ASC),
  CONSTRAINT `fk_config_usuarioupdate`
    FOREIGN KEY (`updated_usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;


SET FOREIGN_KEY_CHECKS=0;
delete from usuario;
INSERT INTO `usuario` (`nome`, `login`, `ativo`, `created_at`, `updated_at`, `created_usuarioid`, `updated_usuarioid`) 
VALUES ('sistema-auto', 'sistema-auto', '0', '2020-09-17 23:16:08', '2020-09-17 23:16:18', '0', '0');
set @lastid = (select LAST_INSERT_ID());
update usuario set id=0, created_usuarioid=0, updated_usuarioid=0 where id=@lastid;
SET FOREIGN_KEY_CHECKS=1;



start transaction;
INSERT INTO `config` (`id`, `tipo`, `valor`, `updated_at`, `updated_usuarioid`) VALUES ('manutencao_alerta_minimo_km', 'double', '15', now(), '0');
INSERT INTO `config` (`id`, `tipo`, `valor`, `updated_at`, `updated_usuarioid`) VALUES ('manutencao_alerta_minimo_dias', 'integer', '5', now(), '0');
commit;



ALTER TABLE `veiculo` 
ADD COLUMN `ultimokm` INT(11) NULL AFTER `updated_usuarioid`,
ADD COLUMN `ultimokmdhcheck` DATETIME NULL AFTER `ultimokm`;


delimiter $$
DROP TRIGGER IF EXISTS `veiculo_km_AFTER_INSERT` $$
CREATE TRIGGER `veiculo_km_AFTER_INSERT` AFTER INSERT ON `veiculo_km` FOR EACH ROW
BEGIN
	declare lUltimoKm int(11);
	SET lUltimoKm = ifnull((select max(km) as km from veiculo_km where veiculoid = new.veiculoid), 0);
	update veiculo 
		set ultimokm=lUltimoKm, ultimokmdhcheck=now()
        where id=new.veiculoid;
END$$

DROP TRIGGER IF EXISTS `veiculo_km_AFTER_DELETE` $$
CREATE TRIGGER `veiculo_km_AFTER_DELETE` AFTER DELETE ON `veiculo_km` FOR EACH ROW
BEGIN
	declare lUltimoKm int(11);
	SET lUltimoKm = ifnull((select max(km) as km from veiculo_km where veiculoid = OLD.veiculoid), 0);
	update veiculo 
		set ultimokm=lUltimoKm, ultimokmdhcheck=now()
        where id=OLD.veiculoid;
END$$
delimiter ;



CREATE TABLE `usuarioresetpwdtokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `usuarioid` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `processado` int(1) NOT NULL DEFAULT 0 ,
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuarioresetpwdtokens_uuid` (`uuid`),
  KEY `idx_usuarioresetpwdtokens_username` (`username`),
  KEY `idx_usuarioresetpwdtokens_email` (`email`),
  KEY `idx_usuarioresetpwdtokens_processado` (`processado`),
  KEY `idx_usuarioresetpwdtokens_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `usuarioresetpwdtokens` 
ADD INDEX `fk_usuario_resetpwdtokens_usuario_idx` (`usuarioid` ASC)
, RENAME TO  `usuario_resetpwdtokens` ;

ALTER TABLE `usuario_resetpwdtokens` 
ADD CONSTRAINT `fk_usuario_resetpwdtokens_usuario`
  FOREIGN KEY (`usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `usuario_resetpwdtokens` 
ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`;


ALTER TABLE `usuario_resetpwdtokens` 
ADD COLUMN `codenumber` INT(11) NOT NULL AFTER `usuarioid`;


ALTER TABLE `manutencao` 
ADD COLUMN `alertakm` INT(11) NULL AFTER `validadekm`;

ALTER TABLE `manutencao` 
ADD COLUMN `limitekm` INT(11) NULL AFTER `alertakm`;

ALTER TABLE `manutencao` 
ADD COLUMN `alertadata` DATETIME NULL AFTER `alertakm`;

ALTER TABLE `manutencao` 
ADD COLUMN `limitedata` DATETIME NULL AFTER `alertadata`;


ALTER TABLE `veiculo` 
ADD COLUMN `tara` DOUBLE NOT NULL DEFAULT 0 AFTER `ultimokmdhcheck`,
ADD COLUMN `lotacao` DOUBLE NOT NULL DEFAULT 0 AFTER `tara`,
ADD COLUMN `pbt` DOUBLE NOT NULL DEFAULT 0 AFTER `lotacao`,
ADD COLUMN `pbtc` DOUBLE NOT NULL DEFAULT 0 AFTER `pbt`;



CREATE TABLE `acertoviagem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigoacerto` int(11) COMMENT 'NAO IDENTIFICADO, SERÁ REMOVIDO',
  `dhacerto` datetime NOT NULL,
  
  `motoristaid` int(11) NOT NULL,
  `veiculoid` int(11) NOT NULL,
  `cidadeorigemid` int(11) NOT NULL,
  `cidadedestinoid` int(11) NOT NULL,
  
  `kmini` int(11) NOT NULL default 0,
  `kmfim` int(11) NOT NULL default 0,
  `kmtotal` int(11) NOT NULL default 0,
  
  `vlradicional` double not null default 0,
  `vlradiantamento` double not null default 0,
  `vlradiantamentototal` double not null default 0 COMMENT 'vlradicional+vlradiantamento',
  

  `cafeqtde` int(11) NOT NULL default 0,
  `cafeextra` int(11) NOT NULL default 0,
  `cafetotal` int(11) NOT NULL default 0,
  `cafevlr` double not null default 0,
  `cafeapagar` double not null default 0,

  `almocoqtde` int(11) NOT NULL default 0,
  `almocoextra` int(11) NOT NULL default 0,
  `almocototal` int(11) NOT NULL default 0,
  `almocovlr` double not null default 0,
  `almocoapagar` double not null default 0,
  
  `jantarqtde` int(11) NOT NULL default 0,
  `jantarextra` int(11) NOT NULL default 0,
  `jantartotal` int(11) NOT NULL default 0,
  `jantarvlr` double not null default 0,
  `jantarapagar` double not null default 0,  
  
  `pernoiteqtde` int(11) NOT NULL default 0,
  `pernoiteextra` int(11) NOT NULL default 0,
  `pernoitetotal` int(11) NOT NULL default 0,
  `pernoitevlr` double not null default 0,
  `pernoiteapagar` double not null default 0,
  
  `vlrtotaldiaria` double not null default 0,

  
  `totallitros` double not null default 0,  
  `vlrtotalabastecimento` double not null default 0,  
  `vlrtotalabastecimentodinheiro` double not null default 0,  
  `vlrtotaldespesas` double not null default 0,  
  `vlrsaldofinal` double not null default 0 comment '(vlradiantamentototal - (vlrtotalabastecimentodinheiro + vlrtotaldespesas + vlrtotaldiaria))',  
      
  `status` int(1) NOT NULL DEFAULT 0 comment '0=Em aberto,1=Encerrado',
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  
  `createduserid` int(11) NOT NULL default 0,
  `updateduserid` int(11) NOT NULL default 0,
 
  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `acertoviagem` 
CHANGE COLUMN `createduserid` `created_usuarioid` INT(11) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `updateduserid` `updated_usuarioid` INT(11) NOT NULL DEFAULT '0' ,
ADD INDEX `fk_acertoviagem_usuariocad_idx` (`created_usuarioid` ASC),
ADD INDEX `fk_acertoviagem_usuarioalt_idx` (`updated_usuarioid` ASC),
ADD INDEX `fk_acertoviagem_veiculo_idx` (`veiculoid` ASC),
ADD INDEX `fk_acertoviagem_motorista_idx` (`motoristaid` ASC),
ADD INDEX `fk_acertoviagem_cidadeorigem_idx` (`cidadeorigemid` ASC),
ADD INDEX `fk_acertoviagem_cidadedestino_idx` (`cidadedestinoid` ASC),
ADD INDEX `idx_acertoviagem_dhacerto` (`dhacerto` ASC),
ADD INDEX `idx_acertoviagem_status` (`status` ASC),
ADD INDEX `idx_acertoviagem_createdat` (`created_at` ASC),
ADD INDEX `idx_acertoviagem_updatedat` (`updated_at` ASC);
;

ALTER TABLE `acertoviagem` 
ADD CONSTRAINT `fk_acertoviagem_usuariocad`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagem_usuarioalt`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagem_veiculo`
  FOREIGN KEY (`veiculoid`)
  REFERENCES `veiculo` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagem_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagem_cidadeorigem`
  FOREIGN KEY (`cidadeorigemid`)
  REFERENCES `cidades` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagem_cidadedestino`
  FOREIGN KEY (`cidadedestinoid`)
  REFERENCES `cidades` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;




CREATE TABLE `acertoviagemroteiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acertoid` int(11) NOT NULL,
  `rota` VARCHAR(70) NOT NULL,
  `ordem` double not null default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `acertoviagemroteiro` 
ADD INDEX `fk_acertoviagemroteiro_acerto_idx` (`acertoid` ASC),
ADD INDEX `idx_acertoviagemroteiro_rota` (`rota` ASC),
ADD INDEX `idx_acertoviagemroteiro_ordem` (`ordem` ASC);

ALTER TABLE `acertoviagemroteiro` 
ADD CONSTRAINT `fk_acertoviagemroteiro_acerto`
  FOREIGN KEY (`acertoid`)
  REFERENCES `acertoviagem` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;




CREATE TABLE `acertoviagemperiodo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acertoid` int(11) NOT NULL,
  `dhi` DATETIME NOT NULL,
  `dhf` DATETIME,
  `totalmin` INT(11) NOT NULL default 0,
  `obs` VARCHAR(70),
  `ordem` double not null default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `acertoviagemperiodo` 
ADD INDEX `fk_acertoviagemperiodo_acerto_idx` (`acertoid` ASC),
ADD INDEX `idx_acertoviagemperiodo_dhi` (`dhi` ASC),
ADD INDEX `idx_acertoviagemperiodo_dhf` (`dhf` ASC),
ADD INDEX `idx_acertoviagemperiodo_ordem` (`ordem` ASC);

ALTER TABLE `acertoviagemperiodo` 
ADD CONSTRAINT `fk_acertoviagemperiodo_acerto`
  FOREIGN KEY (`acertoid`)
  REFERENCES `acertoviagem` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
  
  
  
  CREATE TABLE `despesaviagem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(70) NOT NULL,
  `ativo` INT(1) NOT NULL default 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL default 0,
  `updated_usuarioid` int(11) NOT NULL default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




ALTER TABLE `despesaviagem` 
ADD INDEX `fk_despesaviagem_usercad_idx` (`created_usuarioid` ASC),
ADD INDEX `fk_despesaviagem_useralt_idx` (`updated_usuarioid` ASC),
ADD INDEX `idx_despesaviagem_ativo` (`ativo` ASC),
ADD INDEX `idx_despesaviagem_descricao` (`descricao` ASC);

ALTER TABLE `despesaviagem` 
ADD CONSTRAINT `fk_despesaviagem_usercad`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_despesaviagem_useralt`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


CREATE TABLE `acertoviagemdespesas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acertoid` int(11) NOT NULL,
  `despesaviagemid` int(11) NOT NULL,
  `valor` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `acertoviagemdespesas` 
ADD INDEX `fk_acertoviagemdespesas_acerto_idx` (`acertoid` ASC),
ADD INDEX `fk_acertoviagemdespesas_despesas_idx` (`despesaviagemid` ASC);

ALTER TABLE `acertoviagemdespesas` 
ADD CONSTRAINT `fk_acertoviagemdespesas_acerto`
  FOREIGN KEY (`acertoid`)
  REFERENCES `acertoviagem` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_acertoviagemdespesas_despesas`
  FOREIGN KEY (`despesaviagemid`)
  REFERENCES `despesaviagem` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;




CREATE TABLE `acertoviagemabastec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acertoid` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `kmini` int(11) NOT NULL,
  `kmfim` int(11) NOT NULL,
  `kmtotal` int(11) NOT NULL,
  `litros` double NOT NULL DEFAULT 0,
  `vlrabastecimento` double NOT NULL DEFAULT 0,
  `media` double NOT NULL DEFAULT 0,
  `vlrlitro` double NOT NULL DEFAULT 0,
  `tipopagto` ENUM('C','D') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `acertoviagemabastec` 
ADD INDEX `fk_acertoviagemabastec_acerto_idx` (`acertoid` ASC),
ADD INDEX `idx_acertoviagemabastec_data` (`data` ASC),
ADD INDEX `idx_acertoviagemabastec_kmi` (`kmini` ASC),
ADD INDEX `idx_acertoviagemabastec_tipop` (`tipopagto` ASC);

ALTER TABLE `acertoviagemabastec` 
ADD CONSTRAINT `fk_acertoviagemabastec_acerto`
  FOREIGN KEY (`acertoid`)
  REFERENCES `acertoviagem` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `veiculo` 
ADD COLUMN `mediaconsumo` DOUBLE NULL DEFAULT 0 AFTER `pbtc`;


ALTER TABLE `acertoviagem` 
CHANGE COLUMN `dhacerto` `dhacerto` DATETIME NULL ;

ALTER TABLE `acertoviagemdespesas` 
CHANGE COLUMN `despesaviagemid` `despesaviagemid` INT(11) NULL ;


ALTER TABLE `config` 
CHANGE COLUMN `tipo` `tipo` ENUM('string', 'integer', 'double', 'datetime', 'time', 'boolean', 'file', 'mediumtext', 'json') NOT NULL ;


ALTER TABLE `acertoviagem` 
ADD COLUMN `cafehrbc` TIME NULL AFTER `pernoiteapagar`,
ADD COLUMN `almocohrbc` TIME NULL AFTER `cafehrbc`,
ADD COLUMN `jantarhrbc` TIME NULL AFTER `almocohrbc`,
ADD COLUMN `pernoitehrbc` TIME NULL AFTER `jantarhrbc`;

update acertoviagem
set cafehrbc='06:00:00',
almocohrbc='11:00:00',
jantarhrbc='19:00:00',
pernoitehrbc='23:59:00';

ALTER TABLE `acertoviagem` 
CHANGE COLUMN `cafehrbc` `cafehrbc` TIME NOT NULL ,
CHANGE COLUMN `almocohrbc` `almocohrbc` TIME NOT NULL ,
CHANGE COLUMN `jantarhrbc` `jantarhrbc` TIME NOT NULL ,
CHANGE COLUMN `pernoitehrbc` `pernoitehrbc` TIME NOT NULL ;

CREATE TABLE `caixa_categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(50) NOT NULL,
  `ativo` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `caixa_categoria` 
ADD INDEX `idx_caixa_categoria_descricao` (`descricao` ASC),
ADD INDEX `idx_caixa_categoria_ativo` (`ativo` ASC);

ALTER TABLE `caixa_categoria` 
ADD COLUMN `created_at` DATETIME NULL AFTER `ativo`,
ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`,
ADD COLUMN `created_usuarioid` INT(11) NULL AFTER `updated_at`,
ADD COLUMN `updated_usuarioid` INT(11) NULL AFTER `created_usuarioid`;

ALTER TABLE `caixa_categoria` 
CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL ,
CHANGE COLUMN `updated_at` `updated_at` DATETIME NOT NULL ,
CHANGE COLUMN `created_usuarioid` `created_usuarioid` INT(11) NOT NULL ,
CHANGE COLUMN `updated_usuarioid` `updated_usuarioid` INT(11) NOT NULL ,
ADD INDEX `fk_caixa_categoria_usuariocreated_idx` (`created_usuarioid` ASC),
ADD INDEX `fk_caixa_categoria_usuarioupdated_idx` (`updated_usuarioid` ASC);

ALTER TABLE `caixa_categoria` 
ADD CONSTRAINT `fk_caixa_categoria_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_caixa_categoria_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



insert into caixa_categoria (id, descricao, ativo, created_at, updated_at, created_usuarioid, updated_usuarioid)
values (1, 'PADRÃO', 1, now(), now(), 0, 0);





CREATE TABLE `caixa_depto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `depto` varchar(50) NOT NULL,
  `ativo` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `caixa_depto` 
ADD INDEX `idx_caixa_depto_depto` (`depto` ASC),
ADD INDEX `idx_caixa_depto_ativo` (`ativo` ASC);


ALTER TABLE `caixa_depto` 
ADD COLUMN `created_at` DATETIME NULL AFTER `ativo`,
ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`,
ADD COLUMN `created_usuarioid` INT(11) NULL AFTER `updated_at`,
ADD COLUMN `updated_usuarioid` INT(11) NULL AFTER `created_usuarioid`;

ALTER TABLE `caixa_depto` 
CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL ,
CHANGE COLUMN `updated_at` `updated_at` DATETIME NOT NULL ,
CHANGE COLUMN `created_usuarioid` `created_usuarioid` INT(11) NOT NULL ,
CHANGE COLUMN `updated_usuarioid` `updated_usuarioid` INT(11) NOT NULL ,
ADD INDEX `fk_caixa_depto_usuariocreated_idx` (`created_usuarioid` ASC),
ADD INDEX `fk_caixa_depto_usuarioupdated_idx` (`updated_usuarioid` ASC);

ALTER TABLE `caixa_depto` 
ADD CONSTRAINT `fk_caixa_depto_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_caixa_depto_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



insert into caixa_depto (id, depto, ativo, created_at, updated_at, created_usuarioid, updated_usuarioid) values (1, 'Logística', 1, now(), now(), 0, 0);
insert into caixa_depto (id, depto, ativo, created_at, updated_at, created_usuarioid, updated_usuarioid) values (2, 'RH', 1, now(), now(), 0, 0);
insert into caixa_depto (id, depto, ativo, created_at, updated_at, created_usuarioid, updated_usuarioid) values (3, 'Manutenção', 1, now(), now(), 0, 0);


CREATE TABLE `caixa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deptoid` int(11) NOT NULL,
  `categoriaid` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_usuarioid` int(11) NOT NULL,
  `tipo` ENUM('E', 'S') NOT NULL,
  `valor` double NOT NULL,
  `saldo` double NOT NULL,
  `historico` varchar(150) NOT NULL,
  `origem` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



ALTER TABLE `caixa` 
ADD INDEX `fk_caixa_depto_idx` (`deptoid` ASC),
ADD INDEX `fk_caixa_categoria_idx` (`categoriaid` ASC),
ADD INDEX `fk_caixa_usuariocreated_idx` (`created_usuarioid` ASC),
ADD INDEX `idx_caixa_created_at` (`created_at` ASC),
ADD INDEX `idx_caixa_tipo` (`tipo` ASC),
ADD INDEX `idx_caixa_historico` (`historico` ASC);

ALTER TABLE `caixa` 
ADD CONSTRAINT `fk_caixa_depto`
  FOREIGN KEY (`deptoid`)
  REFERENCES `caixa_depto` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_caixa_categoria`
  FOREIGN KEY (`categoriaid`)
  REFERENCES `caixa_categoria` (`id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
ADD CONSTRAINT `fk_caixa_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



CREATE TABLE `telefones` (
  `id` int(11) NOT NULL,
  `telefone` varchar(45) NOT NULL,
  `contato` varchar(75) NOT NULL,
  `categ` varchar(45) DEFAULT NULL,
  `icon` varchar(45) DEFAULT NULL,
  `nordem` double DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `telefones` 
ADD INDEX `idx_telefones_telefone` (`telefone` ASC),
ADD INDEX `idx_telefones_contato` (`contato` ASC),
ADD INDEX `idx_telefones_nordem` (`nordem` ASC);

ALTER TABLE `telefones` 
ADD COLUMN `created_usuarioid` INT(11) NULL AFTER `updated_at`,
ADD COLUMN `updated_usuarioid` INT(11) NULL AFTER `created_usuarioid`;

ALTER TABLE `telefones` 
CHANGE COLUMN `created_usuarioid` `created_usuarioid` INT(11) NOT NULL ,
CHANGE COLUMN `updated_usuarioid` `updated_usuarioid` INT(11) NOT NULL ,
ADD INDEX `fk_telefones_usuariocreated_idx` (`created_usuarioid` ASC),
ADD INDEX `fk_telefones_usuarioupdated_idx` (`updated_usuarioid` ASC);

ALTER TABLE `telefones` 
ADD CONSTRAINT `fk_telefones_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_telefones_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;
  
ALTER TABLE `telefones` 
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `nordem` `nordem` DOUBLE NOT NULL DEFAULT '0' ,
CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL ,
CHANGE COLUMN `updated_at` `updated_at` DATETIME NOT NULL ;



CREATE TABLE `dispositivo` (
  `uuid` varchar(45) NOT NULL,
  `platform` varchar(45) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `accesscode` varchar(255) DEFAULT NULL,
  `tokenexpire_at` datetime DEFAULT NULL,
  `version` varchar(45) DEFAULT NULL,
  `model` varchar(45) DEFAULT NULL,
  `fabricante` varchar(45) DEFAULT NULL,
  `descricao` varchar(45) DEFAULT NULL,
  `tokenupdated_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `dispositivolink` (
  `token` varchar(45) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `expired` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `dispositivo` 
ADD COLUMN `updated_usuarioid` INT(11) NULL;

ALTER TABLE `dispositivo` 
ADD INDEX `fk_dispositivo_usuarioupdated_idx` (`updated_usuarioid` ASC);

ALTER TABLE `dispositivo` 
ADD CONSTRAINT `fk_dispositivo_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;
  
ALTER TABLE `dispositivo` 
ADD COLUMN `status` INT(1) NOT NULL DEFAULT 0 COMMENT '0=Em Aberto (Pendente aprovação), 1=Liberado, 2=Revogado' AFTER `deleted_at`,
ADD INDEX `idx_dispositivo_descricao` (`descricao` ASC),
ADD INDEX `idx_dispositivo_status` (`status` ASC);




CREATE TABLE `motoristatokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_motoristatokens_uuid` (`uuid`),
  KEY `idx_motoristatokens_username` (`username`),
  KEY `idx_motoristatokens_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `motorista_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deNome` varchar(70) NOT NULL,
  `deTelefone` varchar(20) DEFAULT NULL,
  `paraidmotorista` int(11) DEFAULT NULL,
  `todos` int(1) NOT NULL DEFAULT '0',
  `titulo` varchar(30) NOT NULL,
  `msg` varchar(500) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `idmotoristaresp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_motorista_msg_created` (`created_at`),
  KEY `idx_motorista_msg_motoritsta` (`paraidmotorista`),
  KEY `idx_motorista_msgtodos` (`todos`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `motorista_msg` 
ADD INDEX `fk_motorista_msg_motoristaresp_idx` (`idmotoristaresp` ASC),
ADD INDEX `fk_motorista_msg_usuario_idx` (`iduser` ASC);

ALTER TABLE `motorista_msg` 
ADD CONSTRAINT `fk_motorista_msg_motoristapara`
  FOREIGN KEY (`paraidmotorista`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_motorista_msg_motoristaresp`
  FOREIGN KEY (`idmotoristaresp`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_motorista_msg_usuario`
  FOREIGN KEY (`iduser`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;




CREATE TABLE `coletas_nota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) NOT NULL,
  `localid` int(11) NOT NULL,
  `dhlocal_data` datetime NOT NULL,
  `dhlocal_created_at` datetime NOT NULL,
  `coletaavulsa` int(1) NOT NULL DEFAULT '0',
  `idcoletaavulsa` int(11) DEFAULT '0',
  `idcoleta` int(11) DEFAULT NULL,
  `remetentecnpj` varchar(14) NOT NULL,
  `remetentenome` varchar(255) NOT NULL,
  `destinatariocnpj` varchar(14) NOT NULL,
  `destinatarionome` varchar(255) NOT NULL,
  `motoristaid` int(11) NOT NULL,
  `notanumero` int(11) NOT NULL,
  `notachave` varchar(44) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `geo_error` varchar(500) DEFAULT NULL,
  `geo_latitude` double DEFAULT NULL,
  `geo_longitude` double DEFAULT NULL,
  `geo_altitude` double DEFAULT NULL,
  `geo_accuracy` double DEFAULT NULL,
  `geo_heading` double DEFAULT NULL,
  `geo_speed` double DEFAULT NULL,
  `geo_timestamp` timestamp NULL DEFAULT NULL,
  `obs` varchar(500) DEFAULT NULL,
  `docfiscal` enum('nfe','nfse') DEFAULT 'nfe',
  `notadh` datetime DEFAULT NULL,
  `notavalor` double DEFAULT '0',
  `controla_processo` int(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coletas_nota_1` (`uuid`,`idcoleta`,`notachave`,`docfiscal`),
  UNIQUE KEY `unique_coletas_nota_2` (`uuid`,`coletaavulsa`,`idcoletaavulsa`,`notachave`,`docfiscal`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `coletas_nota` 
ADD INDEX `fk_coletas_nota_coleta_idx` (`idcoleta` ASC),
ADD INDEX `fk_coletas_nota_motorista_idx` (`motoristaid` ASC);

ALTER TABLE `coletas_nota` 
ADD CONSTRAINT `fk_coletas_nota_dispositivo`
  FOREIGN KEY (`uuid`)
  REFERENCES `dispositivo` (`uuid`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coletas_nota_coleta`
  FOREIGN KEY (`idcoleta`)
  REFERENCES `coletas` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coletas_nota_motorista`
  FOREIGN KEY (`motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `coletas_eventos` 
CHANGE COLUMN `tipo` `tipo` ENUM('insert', 'update', 'cancel', 'cancelundo', 'baixa', 'baixaapp', 'baixaundo', 'revisaoorcamento') NOT NULL ;


ALTER TABLE `coletas_eventos` 
DROP FOREIGN KEY `fk_coletas_eventos_usuari`;

ALTER TABLE `coletas_eventos` 
ADD COLUMN `created_motoristaid` INT(11) NULL AFTER `created_usuarioid`,
CHANGE COLUMN `created_usuarioid` `created_usuarioid` INT(11) NULL ,
ADD INDEX `fk_coletas_eventos_motorista_idx` (`created_motoristaid` ASC);

ALTER TABLE `coletas_eventos` 
ADD CONSTRAINT `fk_coletas_eventos_usuari`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_coletas_eventos_motorista`
  FOREIGN KEY (`created_motoristaid`)
  REFERENCES `motorista` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `coletas_nota` 
ADD COLUMN `coletaavulsaincluida` INT(1) NOT NULL DEFAULT 0 AFTER `coletaavulsa`;






ALTER TABLE `coletas_nota` 
ADD COLUMN `remetenteid` INT(11) NULL AFTER `controla_processo`,
ADD COLUMN `destinatarioid` INT(11) NULL AFTER `remetenteid`,
ADD COLUMN `coletaavulsaerror` INT(1) NULL DEFAULT 0 AFTER `destinatarioid`,
ADD COLUMN `coletaavulsaerrormsg` VARCHAR(1000) NULL AFTER `coletaavulsaerror`,
ADD COLUMN `endcoleta_cidadecodibge` INT(11) NULL AFTER `coletaavulsaerrormsg`,
ADD COLUMN `endcoleta_endereco` VARCHAR(275) NULL AFTER `endcoleta_cidadecodibge`,
ADD COLUMN `endcoleta_numero` VARCHAR(10) NULL AFTER `endcoleta_endereco`,
ADD COLUMN `endcoleta_bairro` VARCHAR(70) NULL AFTER `endcoleta_numero`,
ADD COLUMN `endcoleta_cep` VARCHAR(8) NULL AFTER `endcoleta_bairro`,
ADD COLUMN `endcoleta_complemento` VARCHAR(255) NULL AFTER `endcoleta_cep`,
ADD COLUMN `peso` DOUBLE NULL AFTER `endcoleta_complemento`,
ADD COLUMN `qtde` DOUBLE NULL AFTER `peso`,
ADD COLUMN `especie` VARCHAR(150) NULL AFTER `qtde`;



ALTER TABLE `coletas_nota` 
ADD COLUMN `storagetipo` ENUM('local', 's3') NULL AFTER `especie`,
ADD COLUMN `storageurl` VARCHAR(1000) NULL AFTER `storagetipo`;


ALTER TABLE `coletas_nota` 
DROP COLUMN `controla_processo`,
ADD COLUMN `baixanfetentativas` INT(11) NULL,
ADD COLUMN `baixanfemsg` VARCHAR(1000) NULL AFTER `baixanfetentativas`;

ALTER TABLE `coletas_nota` 
ADD COLUMN `baixanfestatus` INT(1) NOT NULL DEFAULT 0 COMMENT '0=Nao baixou, 1=OK, 2=Erro';


ALTER TABLE `coletas_nota` 
ADD COLUMN `xmlprocessado` INT(1) NOT NULL DEFAULT 0 AFTER `baixanfestatus`;

ALTER TABLE `coletas_nota` 
CHANGE COLUMN `endcoleta_numero` `endcoleta_numero` VARCHAR(60) NULL DEFAULT NULL ;


ALTER TABLE `coletas` 
CHANGE COLUMN `encerramentotipo` `encerramentotipo` ENUM('1', '2', '3', '4') NULL DEFAULT NULL COMMENT '1 = Interno, 2 = Aplicativo motorista, 3 = Painel do cliente, 4=atraves da reabertura do orçamento' ;


CREATE TABLE `nota_conferencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baixado` int(1) NOT NULL DEFAULT 0,

  `created_usuarioid` int(11) NOT NULL,
  `updated_usuarioid` int(11) NOT NULL,
 
  `clienteid` int(11) NOT NULL,
  `notacnpj` varchar(14) NOT NULL,
  `notanumero` int(11) NOT NULL,
  `notachave` varchar(44) DEFAULT NULL,

  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nota_conferencia_chave` (`notachave`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


ALTER TABLE `nota_conferencia` 
ADD CONSTRAINT `fk_nota_conferencia_cliente`
  FOREIGN KEY (`clienteid`)
  REFERENCES `cliente` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_nota_conferencia_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_nota_conferencia_usuarioupdated`
  FOREIGN KEY (`updated_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;
  
  
ALTER TABLE `nota_conferencia` 
ADD INDEX `idx_nota_conferencia_baixadp` (`baixado` ASC);



ALTER TABLE `acertoviagemroteiro` 
ADD COLUMN `cidadeid` INT(11) NULL AFTER `ordem`,
ADD INDEX `fk_acertoviagemroteiro_cidade_idx` (`cidadeid` ASC);

ALTER TABLE `acertoviagemroteiro` 
ADD CONSTRAINT `fk_acertoviagemroteiro_cidade`
  FOREIGN KEY (`cidadeid`)
  REFERENCES `cidades` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


drop table if exists usuariotokens;
CREATE TABLE `usuariotokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `accesscode` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuariotokens_uuid` (`uuid`),
  KEY `idx_usuariotokens_username` (`username`),
  KEY `idx_usuariotokens_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;





ALTER TABLE `cidades` 
ADD COLUMN `ativo` INT(1) NOT NULL DEFAULT 1 AFTER `created_usuarioid`,
CHANGE COLUMN `updated_usuarioid` `updated_usuarioid` INT(11) NOT NULL AFTER `id`;

ALTER TABLE `acertoviagemperiodo` 
ADD COLUMN `cafeqtde` INT(11) NULL DEFAULT 0 AFTER `ordem`,
ADD COLUMN `almocoqtde` INT(11) NULL DEFAULT 0,
ADD COLUMN `jantarqtde` INT(11) NULL DEFAULT 0,
ADD COLUMN `pernoiteqtde` INT(11) NULL DEFAULT 0,
ADD COLUMN `qtdedias` INT(11) NULL DEFAULT 0;

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.aprovarebloquear', 'Aprova o orçamento e mantem bloqueado', '', 0, 'comercial.orcamentos', '02.01.04');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.aprovareliberar', 'Aprova o orçamento e libera coleta (cria coleta)', '', 0, 'comercial.orcamentos', '02.01.05');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.reprovar', 'Reprova o orçamento', '', 0, 'comercial.orcamentos', '02.01.06');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.orcamentos.desfazeraprovarreprovar', 'Desfazer aprovação ou reprovação de um orçamento', '', 0, 'comercial.orcamentos', '02.01.07');


delete from perfilacesso_permissoes where permissaoid='comercial.orcamentos.alterarstatus';
delete from permissao where id='comercial.orcamentos.alterarstatus';

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.caixas', 'Caixas', '', 1, 'operacional', '00.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.caixas.consulta', 'Consultar caixas', '', 0, 'operacional.caixas', '00.02.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.caixas.add', 'Incluir novo registro no caixa', '', 0, 'operacional.caixas', '00.02.02');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas.baixar', 'Baixar coletas em aberto', '', 0, 'operacional.coletas', '00.01.04');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('operacional.coletas.baixardesfazer', 'Desfazer baixa de coletas (Reabrir)', '', 0, 'operacional.coletas', '00.01.05');




insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config', 'Configuração', '', 1, null, '99');

	insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.configuracao', 'Sistema', '', 1, 'config', '99.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.configuracao.geral', 'Confguração geral do sistema', '', 0, 'config.configuracao', '99.01.01');
    
    insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.usuarios', 'Usuários do sistema', '', 1, 'config', '99.02');
        insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.usuarios.consulta', 'Consultar/visualizar usuários', '', 0, 'config.usuarios', '99.02.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.usuarios.save', 'Alterar e criar usuários', '', 0, 'config.usuarios', '99.02.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.usuarios.delete', 'Excluir usuários', '', 0, 'config.usuarios', '99.02.03');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.usuarios.liberarpermissao', 'Liberar ou remover acesso do usuário', '', 0, 'config.usuarios', '99.02.04');
		
    insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.perfilacesso', 'Perfil de acesso de usuários do sistema', '', 1, 'config', '99.03');
        insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.perfilacesso.consulta', 'Consultar/visualizar perfil de acesso', '', 0, 'config.perfilacesso', '99.03.01');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.perfilacesso.save', 'Alterar e criar perfil de acesso', '', 0, 'config.perfilacesso', '99.03.02');
		insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('config.perfilacesso.delete', 'Excluir perfil de acesso', '', 0, 'config.perfilacesso', '99.03.03');        
        
        
        
ALTER TABLE `coletas_nota` 
ADD COLUMN `coletaavulsaignorada` INT(1) NULL DEFAULT 0 AFTER `destinatarioid`;
        
        
        
CREATE TABLE `caixa_depto_usuario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `caixadeptoid` INT(11) NOT NULL,
  `usuarioid` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `created_usuarioid` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_caixa_usuarios_usuario_idx` (`usuarioid` ASC),
  INDEX `fk_caixa_usuarios_caixadepto_idx` (`caixadeptoid` ASC),
  INDEX `fk_caixa_usuarios_createdusuario_idx` (`created_usuarioid` ASC),
  CONSTRAINT `fk_caixa_usuarios_usuario`
    FOREIGN KEY (`usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_caixa_usuarios_caixadepto`
    FOREIGN KEY (`caixadeptoid`)
    REFERENCES `caixa_depto` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_caixa_usuarios_createdusuario`
    FOREIGN KEY (`created_usuarioid`)
    REFERENCES `usuario` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.motoristas', 'Motoristas', '', 1, 'cadastros', '01.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.motoristas.consulta', 'Consulta listagem completo de motoristas', '', 0, 'cadastros.motoristas', '01.03.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.motoristas.save', 'Alterar e criar novos motoristas', '', 0, 'cadastros.motoristas', '01.03.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.motoristas.delete', 'Excluir cadastro de motorista', '', 0, 'cadastros.motoristas', '01.03.03');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.motoristas.resetsenhaapp', 'Alterar a senha de acesso ao aplicativo ColetaApp', '', 0, 'cadastros.motoristas', '01.03.04');


ALTER TABLE `coletas_nota` 
ADD INDEX `idx_coletas_nota_chave` (`notachave` ASC);


insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel', 'Dispositivo Móvel', '', 1, 'cadastros', '01.04');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel.consulta', 'Consulta listagem de dispositivo móvel', '', 0, 'cadastros.dispositomovel', '01.04.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel.liberar', 'Liberar solicitação de acesso de um dispositivo móvel', '', 0, 'cadastros.dispositomovel', '01.04.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel.revogar', 'Revogar o acesso de um dispositivo móvel', '', 0, 'cadastros.dispositomovel', '01.04.03');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel.delete', 'Excluir um dispositivo móvel', '', 0, 'cadastros.dispositomovel', '01.04.04');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.dispositomovel.save', 'Editar informações de cadastro do dispositivo móvel', '', 0, 'cadastros.dispositomovel', '01.04.05');



insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.cidades', 'Cidades', '', 1, 'cadastros', '01.05');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.cidades.consulta', 'Consulta listagem de cidades', '', 0, 'cadastros.cidades', '01.05.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.cidades.save', 'Alterar e criar novas cidades', '', 0, 'cadastros.cidades', '01.05.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.cidades.delete', 'Excluir cadastro de cidade', '', 0, 'cadastros.cidades', '01.05.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.regiao', 'Região', '', 1, 'cadastros', '01.06');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.regiao.consulta', 'Consulta listagem de região', '', 0, 'cadastros.regiao', '01.06.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.regiao.save', 'Alterar e criar região', '', 0, 'cadastros.regiao', '01.06.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.regiao.delete', 'Excluir cadastro de região', '', 0, 'cadastros.regiao', '01.06.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculos', 'Veiculos', '', 1, 'cadastros', '01.07');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculos.consulta', 'Consulta listagem de veiculos', '', 0, 'cadastros.veiculos', '01.07.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculos.save', 'Alterar e criar veiculos', '', 0, 'cadastros.veiculos', '01.07.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculos.delete', 'Excluir cadastro de veiculo', '', 0, 'cadastros.veiculos', '01.07.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.despesaviagem', 'Despesas de Viagens', '', 1, 'cadastros', '01.08');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.despesaviagem.consulta', 'Consulta listagem de despesa de viagem', '', 0, 'cadastros.despesaviagem', '01.08.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.despesaviagem.save', 'Alterar e criar despesa de viagem', '', 0, 'cadastros.despesaviagem', '01.08.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.despesaviagem.delete', 'Excluir cadastro de despesa de viagem', '', 0, 'cadastros.despesaviagem', '01.08.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixacategoria', 'Caixa - Categorias', '', 1, 'cadastros', '01.09');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixacategoria.consulta', 'Consulta listagem de categorias de caixas', '', 0, 'cadastros.caixacategoria', '01.09.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixacategoria.save', 'Alterar e criar cadastro de categorias de caixas', '', 0, 'cadastros.caixacategoria', '01.09.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixacategoria.delete', 'Excluir cadastro de categorias de caixas', '', 0, 'cadastros.caixacategoria', '01.09.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixadepto', 'Caixa - Departamentos', '', 1, 'cadastros', '01.10');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixadepto.consulta', 'Consulta listagem de departamentos de caixas', '', 0, 'cadastros.caixadepto', '01.10.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixadepto.save', 'Alterar e criar cadastro de departamentos de caixas', '', 0, 'cadastros.caixadepto', '01.10.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.caixadepto.delete', 'Excluir cadastro de departamentos de caixas', '', 0, 'cadastros.caixadepto', '01.10.03');


insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.unidades', 'Unidades', '', 1, 'cadastros', '01.11');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.unidades.consulta', 'Consulta listagem de unidades', '', 0, 'cadastros.unidades', '01.11.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.unidades.save', 'Alterar e criar cadastro de unidade', '', 0, 'cadastros.unidades', '01.11.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.unidades.delete', 'Excluir cadastro de unidades', '', 0, 'cadastros.unidades', '01.11.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculotipo', 'Tipos de Veículos', '', 1, 'cadastros', '01.12');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculotipo.consulta', 'Consulta listagem de tipos de veículos', '', 0, 'cadastros.veiculotipo', '01.12.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculotipo.save', 'Alterar e criar cadastros de tipos de veículos', '', 0, 'cadastros.veiculotipo', '01.12.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.veiculotipo.delete', 'Excluir cadastros de tipos de veículos', '', 0, 'cadastros.veiculotipo', '01.12.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.servicomanutencao', 'Serviços de Manutenção', '', 1, 'cadastros', '01.13');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.servicomanutencao.consulta', 'Consulta listagem de serviços de manutenção', '', 0, 'cadastros.servicomanutencao', '01.13.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.servicomanutencao.save', 'Alterar e criar cadastros de serviços de manutenção', '', 0, 'cadastros.servicomanutencao', '01.13.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.servicomanutencao.delete', 'Excluir cadastros de serviços de manutenção', '', 0, 'cadastros.servicomanutencao', '01.13.03');

insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.telefoneemergencial', 'Telefone emergêncial', '', 1, 'cadastros', '01.14');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.telefoneemergencial.consulta', 'Consulta listagem de telefone emergêncial', '', 0, 'cadastros.telefoneemergencial', '01.14.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.telefoneemergencial.save', 'Alterar e criar cadastro de telefone emergêncial', '', 0, 'cadastros.telefoneemergencial', '01.14.02');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('cadastros.telefoneemergencial.delete', 'Excluir cadastro de telefone emergêncial', '', 0, 'cadastros.telefoneemergencial', '01.14.03');


ALTER TABLE `coletas_eventos` 
CHANGE COLUMN `tipo` `tipo` ENUM('insert', 'update', 'cancel', 'cancelundo', 'baixa', 'baixaapp', 'baixaundo', 'revisaoorcamento', 'info') NOT NULL ;



DROP TABLE IF EXISTS `tmptransfer`;

CREATE TEMPORARY  TABLE `tmptransfer` (
  `coletaid` int(11) NOT NULL,
  `chavenota` varchar(44) DEFAULT NULL,
  PRIMARY KEY (`coletaid`),
  KEY `idx_tmptransfer_chave` (`chavenota`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

start transaction;

insert into tmptransfer
select coletas.id, notas.notachave 
from coletas
inner join (
	select * from coletas_nota group by notachave order by coletas_nota.idcoleta,  coletas_nota.dhlocal_data desc, coletas_nota.dhlocal_created_at desc
) as notas on notas.idcoleta=coletas.id
group by coletas.id
;

update coletas
inner join tmptransfer on tmptransfer.coletaid=coletas.id
set coletas.chavenota=tmptransfer.chavenota
where coletas.chavenota is null
;

commit;
DROP TABLE IF EXISTS `tmptransfer`;



drop table if exists appmotorista_log;
CREATE TABLE `appmotorista_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(45) NOT NULL,
  `motoristaid` int(11) NULL,
  `created_at` DATETIME NOT NULL,
  `ip` VARCHAR(255) NULL,
  `host` VARCHAR(255) NULL,
  `uri` VARCHAR(255) NULL,
  `request` VARCHAR(5000) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `appmotorista_log` 
ADD INDEX `idx_appmotorista_log_uuid` (`uuid` ASC),
ADD INDEX `idx_appmotorista_log_motoristaid` (`motoristaid` ASC),
ADD INDEX `idx_appmotorista_log_created` (`created_at` ASC);


ALTER TABLE `nota_conferencia` 
ADD COLUMN `editadomanual` INT(1) NULL AFTER `updated_at`,
ADD COLUMN `baixado_at` DATETIME NULL AFTER `editadomanual`,
ADD COLUMN `peso` double DEFAULT NULL,
ADD COLUMN `qtde` double DEFAULT NULL,
ADD COLUMN `storagetipo` enum('local','s3') DEFAULT NULL,
ADD COLUMN `storageurl` varchar(1000) DEFAULT NULL,
ADD COLUMN `baixanfetentativas` int(11) DEFAULT NULL,
ADD COLUMN `baixanfemsg` varchar(1000) DEFAULT NULL,
ADD COLUMN `baixanfestatus` int(11) NOT NULL DEFAULT '0' COMMENT '0=Nao baixou, 1=OK, 2=Erro',
ADD COLUMN `xmlprocessado` int(11) NOT NULL DEFAULT '0';

update nota_conferencia set editadomanual=0, peso=0, qtde=0;

ALTER TABLE `nota_conferencia` 
ADD COLUMN `baixado_usuarioid` INT(11) NULL AFTER `updated_usuarioid`,
CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL ,
CHANGE COLUMN `updated_at` `updated_at` DATETIME NOT NULL ,
CHANGE COLUMN `editadomanual` `editadomanual` INT(1) NOT NULL DEFAULT 0 ,
CHANGE COLUMN `peso` `peso` DOUBLE NOT NULL ,
CHANGE COLUMN `qtde` `qtde` DOUBLE NOT NULL ;


update nota_conferencia set baixado_usuarioid=updated_usuarioid, baixado_at=updated_at where baixado=1;

update nota_conferencia set xmlprocessado=0, baixanfestatus=2, baixanfetentativas=100, baixanfemsg='Ignorado pelo processo de importação'
where date(created_at) < '2021-01-01';


ALTER TABLE `followup_files` 
DROP FOREIGN KEY `fk_followup_files_usuariocreated`;

ALTER TABLE `followup_files` 
DROP INDEX `fk_followup_files_usuariocreated_idx` ,
ADD INDEX `fk_followup_files_usuariocreated_idx` (`created_usuarioid` ASC);

ALTER TABLE `followup_files` 
ADD CONSTRAINT `fk_followup_files_usuariocreated`
  FOREIGN KEY (`created_usuarioid`)
  REFERENCES `usuario` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



ALTER TABLE `followup` ADD INDEX `idx_followup_comprador` (`comprador` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_itemdescricao` (`itemdescricao` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_itemid` (`itemid` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_itemdescricao2` (`itemid` ASC, `itemdescricao` ASC);


ALTER TABLE `followup_log` 
CHANGE COLUMN `tipoorigem` `tipoorigem` INT(11) NOT NULL COMMENT '1=Alteração manual operador, 2=Novo registro importação planilha, 3=update registro importação planilha' ;


start transaction;
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.nfe', 'Notas Fiscais', '', 1, 'comercial', '03.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.nfe.consulta', 'Consulta de nota fiscal por número, cnpj, emitente, destinatário e outros campos', '', 0, 'comercial.nfe', '03.01.01');
insert into permissao (id, titulo, detalhe, grupo, idpai, ordem) values ('comercial.nfe.consultaporchave', 'Consulta de nota fiscal por chave e impressão do DANFE', '', 0, 'comercial.nfe', '03.01.02');
commit;






ALTER TABLE `followup_log` 
CHANGE COLUMN `datapromessa` `datapromessa` DATETIME NULL ,
CHANGE COLUMN `ordemcompra` `ordemcompra` INT(11) NULL ;

ALTER TABLE `followup` ADD COLUMN `arquivoultimoid` INT(11) NULL AFTER `tipoorigem`;

ALTER TABLE `followup` ADD INDEX `idx_followup_dhimportacao` (`dhimportacao` ASC);
ALTER TABLE `followup` DROP INDEX `idx_followup_cliente_datasolicitacao2` ;
ALTER TABLE `followup` DROP INDEX `idx_followup_cliente_datapromessa2` ;

ALTER TABLE `followup` ADD INDEX `idx_followup_erroagendastatus` (`erroagendastatus` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_errocoletastatus` (`errocoletastatus` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_errodtpromessastatus` (`errodtpromessastatus` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_coletaid` (`coletaid` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_statusconfirmacaocoleta` (`statusconfirmacaocoleta` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_iniciofollowup` (`iniciofollowup` ASC);

ALTER TABLE `followup` ADD INDEX `idx_followup_dataconfirmacao` (`dataconfirmacao` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_forneccnpj` (`forneccnpj` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_forneccidade` (`forneccidade` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_fornecuf` (`fornecuf` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_fornectelefone` (`fornectelefone` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_contato` (`contato` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_email` (`email` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_qtdesolicitada` (`qtdesolicitada` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_qtderecebida` (`qtderecebida` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_qtdedevida` (`qtdedevida` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_observacao` (`observacao` ASC);
ALTER TABLE `followup` ADD INDEX `idx_followup_aprovacaorc` (`aprovacaorc` ASC);



ALTER TABLE followup ADD INDEX idx_followup_normalurgente (normalurgente ASC);
ALTER TABLE followup ADD INDEX idx_followup_compradelegada (compradelegada ASC);
ALTER TABLE followup ADD INDEX idx_followup_tipooc (tipooc ASC);
ALTER TABLE followup ADD INDEX idx_followup_statusoc (statusoc ASC);
ALTER TABLE followup ADD INDEX idx_followup_statusliberacao (statusliberacao ASC);
ALTER TABLE followup ADD INDEX idx_followup_situacaolinha (situacaolinha ASC);
ALTER TABLE followup ADD INDEX idx_followup_compradoracordo (compradoracordo ASC);
ALTER TABLE followup ADD INDEX idx_followup_datanecessidaderc (datanecessidaderc ASC);
ALTER TABLE followup ADD INDEX idx_followup_criacaooc (criacaooc ASC);
ALTER TABLE followup ADD INDEX idx_followup_aprovacaooc (aprovacaooc ASC);
ALTER TABLE followup ADD INDEX idx_followup_dataliberacao (dataliberacao ASC);
ALTER TABLE followup ADD INDEX idx_followup_diaatraso (diaatraso ASC);
ALTER TABLE followup ADD INDEX idx_followup_condpagto (condpagto ASC);
ALTER TABLE followup ADD INDEX idx_followup_grupo (grupo ASC);
ALTER TABLE followup ADD INDEX idx_followup_familia (familia ASC);
ALTER TABLE followup ADD INDEX idx_followup_subfamilia (subfamilia ASC);
ALTER TABLE followup ADD INDEX idx_followup_udm (udm ASC);
ALTER TABLE followup ADD INDEX idx_followup_vlrunitario (vlrunitario ASC);
ALTER TABLE followup ADD INDEX idx_followup_vlrultcompra (vlrultcompra ASC);
ALTER TABLE followup ADD INDEX idx_followup_moeda (moeda ASC);
ALTER TABLE followup ADD INDEX idx_followup_totallinhaoc (totallinhaoc ASC);
ALTER TABLE followup ADD INDEX idx_followup_dataultimaentrada (dataultimaentrada ASC);
ALTER TABLE followup ADD INDEX idx_followup_tipofrete (tipofrete ASC);
ALTER TABLE followup ADD INDEX idx_followup_notafiscal (notafiscal ASC);
ALTER TABLE followup ADD INDEX idx_followup_dataagendamentocoleta (dataagendamentocoleta ASC);
ALTER TABLE followup ADD INDEX idx_followup_datacoleta (datacoleta ASC);
ALTER TABLE followup ADD INDEX idx_followup_datahora_followup (datahora_followup ASC);



ALTER TABLE `coletas`
ADD COLUMN `ctenumero` INT(11) NULL AFTER `justsituacao`;



ALTER TABLE `coletas` 
ADD INDEX `idx_coletas_origem` (`origem` ASC),
ADD INDEX `idx_coletas_veiculoexclusico` (`veiculoexclusico` ASC),
ADD INDEX `idx_coletas_cargaurgente` (`cargaurgente` ASC),
ADD INDEX `idx_coletas_produtosperigosos` (`produtosperigosos` ASC),
ADD INDEX `idx_coletas_ctenumero` (`ctenumero` ASC),
ADD INDEX `idx_coletas_updated_at` (`updated_at` ASC),
ADD INDEX `idx_coletas_contatonome` (`contatonome` ASC),
ADD INDEX `idx_coletas_contatoemail` (`contatoemail` ASC),
ADD INDEX `idx_coletas_peso` (`peso` ASC),
ADD INDEX `idx_coletas_qtde` (`qtde` ASC),
ADD INDEX `idx_coletas_encerramentotipo` (`encerramentotipo` ASC),
ADD INDEX `idx_coletas_created_at` (`created_at` ASC),
ADD INDEX `idx_coletas_endcoleta_logradouro` (`endcoleta_logradouro` ASC),
ADD INDEX `idx_coletas_endcoleta_endereco` (`endcoleta_endereco` ASC),
ADD INDEX `idx_coletas_endcoleta_cep` (`endcoleta_cep` ASC),
ADD INDEX `idx_coletas_gestaocliente_ordemcompra` (`gestaocliente_ordemcompra` ASC),
ADD INDEX `idx_coletas_gestaocliente_comprador` (`gestaocliente_comprador` ASC),
ADD INDEX `idx_coletas_justsituacao` (`justsituacao` ASC),
ADD INDEX `idx_coletas_especie` (`especie` ASC);


INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('followup.gestao.dashboard', 'Visualizar dashboard de gestão', '0', 'followup', '03.02');

INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.manutencao', 'Manutenção e agenda de manutenção de veículos', '1', 'operacional', '00.03');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.manutencao.consulta', 'Consultar manutenção e agenda de manutenção de veículos', '0', 'operacional.manutencao', '00.03.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.manutencao.save', 'Adicionar e editar manutenção de veículos', '0', 'operacional.manutencao', '00.03.02');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.caixamensagem', 'Comunicação com os motoristas', '1', 'operacional', '00.04');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.caixamensagem.consulta', 'Consultar tela de comunicação com os motoristas', '0', 'operacional.caixamensagem', '00.04.01');
INSERT INTO `permissao` (`id`, `titulo`, `grupo`, `idpai`, `ordem`) VALUES ('operacional.caixamensagem.save', 'Adicionar ou excluir mensagens com os motoristas', '0', 'operacional.caixamensagem', '00.04.02');
INSERT INTO `permissao` (`id`, `titulo`, `idpai`, `ordem`) VALUES ('operacional.expedicao', 'Gestão da expedição', 'operacional', '00.05');
INSERT INTO `permissao` (`id`, `titulo`, `idpai`, `ordem`) VALUES ('operacional.expedicao.consulta', 'Consultar notas de entrada da expedição', 'operacional.expedicao.consulta', '00.05.01');
INSERT INTO `permissao` (`id`, `titulo`, `idpai`, `ordem`) VALUES ('operacional.expedicao.incluir', 'Incluir notas de entrada na expedição', 'operacional.expedicao.incluir', '00.05.02');
INSERT INTO `permissao` (`id`, `titulo`, `idpai`, `ordem`) VALUES ('operacional.expedicao.baixar', 'Baixar notas de entrada na expedição', 'operacional.expedicao.baixar', '00.05.03');
INSERT INTO `permissao` (`id`, `titulo`, `idpai`, `ordem`) VALUES ('operacional.expedicao.delete', 'Excluir notas de entrada na expedição', 'operacional.expedicao.baixar', '00.05.04');

ALTER TABLE `usuario` 
ADD COLUMN `defaulturl` VARCHAR(5000) NULL AFTER `updated_usuarioid`;
