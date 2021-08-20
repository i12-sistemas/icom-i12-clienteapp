ALTER TABLE `acertoviagem` 
ADD COLUMN `veiculocarretaid` INT(11) NULL AFTER `veiculoid`,
ADD INDEX `fk_acertoviagem_veiculocarreta_idx` (`veiculocarretaid` ASC);

ALTER TABLE `acertoviagem` 
ADD CONSTRAINT `fk_acertoviagem_veiculocarreta`
  FOREIGN KEY (`veiculocarretaid`)
  REFERENCES `veiculo` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;



-- aplicado em produção dia 12/07/2021

