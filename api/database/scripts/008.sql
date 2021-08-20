start transaction;
update coletas_nota
set coletaavulsaerror=0, coletaavulsaerrormsg=null, coletaavulsantentativa=0
where POSITION("Nenhum cliente de" IN coletaavulsaerrormsg)>0
and coletaavulsaerror=1;
commit;


ALTER TABLE `orcamento` 
CHANGE COLUMN `tomador` `tomador` ENUM('CIF', 'FOB', 'OUTROS') NOT NULL DEFAULT 'CIF' ;


ALTER TABLE `cargaentradaitem` 
ADD INDEX `idx_cargaentradaitem_chave` (`nfechave` ASC);

ALTER TABLE `coletas_nota` 
ADD INDEX `idx_coletas_nota_remetentecnpj` (`remetentecnpj` ASC),
ADD INDEX `idx_coletas_nota_destinatariocnpj` (`destinatariocnpj` ASC);


ALTER TABLE `coletas_nota` 
DROP INDEX `idx_coletas_nota_chave` ,
ADD INDEX `idx_coletas_nota_chave` USING BTREE (`notachave`);



ALTER TABLE `cargaentradaitem` 
ADD COLUMN `coletanotaid` INT(11) NULL AFTER `nfechave`,
ADD INDEX `fk_cargaentradaitem_coletanota_idx` (`coletanotaid` ASC);

ALTER TABLE `conectaadmin`.`cargaentradaitem` 
ADD CONSTRAINT `fk_cargaentradaitem_coletanota`
  FOREIGN KEY (`coletanotaid`)
  REFERENCES `coletas_nota` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


ALTER TABLE `coletas_nota` 
ADD COLUMN `notaserie` INT(11) NULL AFTER `notanumero`;


start transaction;
update cargaentradaitem
inner join coletas_nota on coletas_nota.notachave=cargaentradaitem.nfechave
set cargaentradaitem.coletanotaid=coletas_nota.id
where cargaentradaitem.coletanotaid is null;
commit;


start transaction;
update coletas_nota
set notaserie=cast(SUBSTRING(notachave, 23, 3) as unsigned)
where notaserie is null and ifnull(notachave,'')<>'';
commit;

start transaction;
update coletas_nota
set notaserie=0
where notaserie is null and ifnull(notachave,'')='';
commit;


ALTER TABLE `coletas_nota` 
CHANGE COLUMN `notaserie` `notaserie` INT(11) NOT NULL ,
CHANGE COLUMN `notachave` `notachave` VARCHAR(44) NOT NULL ;


ALTER TABLE `coletas_nota` 
CHANGE COLUMN `notaserie` `notaserie` INT(11) NULL ,
CHANGE COLUMN `notachave` `notachave` VARCHAR(44) NULL ;




delimiter $$
DROP PROCEDURE IF EXISTS `corrigeDuplicidadeVeiculo`$$
CREATE PROCEDURE `corrigeDuplicidadeVeiculo`()
BEGIN
	declare noMoreRows integer;
	declare lids varchar(5000);
	declare lplaca varchar(20);
	declare lqtde int(11);
    
    declare curs cursor for
		select group_concat(distinct id order by id) as id,
		count(id) as qtde, placa
		from veiculo 
		group by placa
		having count(placa) >1
        ;

	declare continue handler for not found set noMoreRows = 1;
	set noMoreRows = 0;
    

DROP TEMPORARY TABLE  IF EXISTS tmp_corrige;
CREATE TEMPORARY TABLE tmp_corrige (
	iderrado int(11),
    placa varchar(255),
    idcerto int(11)
) ENGINE=MEMORY;    
        
	open curs;
	myLoop:loop
		fetch curs into lids, lqtde, lplaca;
		if noMoreRows then
			leave myLoop;
		end if;
        
        -- select lids, lqtde, lplaca;
		SET @idcerto = (select  id from veiculo where find_in_set(id, lids) order by id, if(ativo=1, 'a','b')  limit 1);
		-- select  @idcerto;
	
        update motorista set veiculoid=@idcerto where find_in_set(veiculoid, lids) and not(veiculoid=@idcerto);
        update acertoviagem set veiculoid=@idcerto where find_in_set(veiculoid, lids) and not(veiculoid=@idcerto);
        update cargaentrada set veiculoid=@idcerto where find_in_set(veiculoid, lids) and not(veiculoid=@idcerto);
        
        delete from veiculo where find_in_set(id, lids) and not(id=@idcerto);
        
        
 
	end loop myLoop;
	close curs;
END$$
delimiter ;

call corrigeDuplicidadeVeiculo();
DROP PROCEDURE IF EXISTS `corrigeDuplicidadeVeiculo` ;




ALTER TABLE `motorista` 
DROP FOREIGN KEY `fk_motorista_veiculo`;

ALTER TABLE `motorista` 
CHANGE COLUMN `veiculoid` `veiculoid` INT(11) NULL ;

ALTER TABLE `motorista` 
ADD CONSTRAINT `fk_motorista_veiculo`
  FOREIGN KEY (`veiculoid`)
  REFERENCES `veiculo` (`id`)
  ON UPDATE CASCADE;




delimiter $$
DROP PROCEDURE IF EXISTS `corrigeDuplicidadeMotorista`$$
CREATE PROCEDURE `corrigeDuplicidadeMotorista`()
BEGIN
	declare noMoreRows integer;
	declare lids varchar(5000);
	declare lcpf varchar(20);
	
    
    declare curs cursor for
		select group_concat(distinct motorista.id order by motorista.id) as id,
		motorista.cpf
		from motorista 
		group by motorista.cpf
		having count(motorista.cpf) >1
        
        ;

	declare continue handler for not found set noMoreRows = 1;
	set noMoreRows = 0;
    

	open curs;
	myLoop:loop
		fetch curs into lids, lcpf;
		if noMoreRows then
			leave myLoop;
		end if;
        
        -- select lids, lcpf;
		SET @idcerto = (select  id from motorista where find_in_set(id, lids) order by id, if(ativo=1, 'a','b')  limit 1);
		-- select  @idcerto;
	
        update coletas set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        update orcamento set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        update acertoviagem set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        update coletas_eventos set created_motoristaid=@idcerto where find_in_set(created_motoristaid, lids) and not(created_motoristaid=@idcerto);
        update coletas_nota set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        update cargaentrada set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        update guaritacheck set motoristaid=@idcerto where find_in_set(motoristaid, lids) and not(motoristaid=@idcerto);
        
        delete from motorista where find_in_set(id, lids) and not(id=@idcerto);
        
        
	end loop myLoop;
	close curs;
END$$
delimiter ;

call corrigeDuplicidadeMotorista();
DROP PROCEDURE IF EXISTS `corrigeDuplicidadeMotorista` ;




-- aplicado em produção dia 02/08/2021

