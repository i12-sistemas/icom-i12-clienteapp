ALTER TABLE `cargatransfer` 
ADD COLUMN conferidoentradaprogresso double default 0,
ADD COLUMN conferidoentradaqtde int(11) default 0;

ALTER TABLE `cargatransferitem` 
ADD COLUMN conferidoentrada int(1) default 0,
ADD COLUMN conferidoentradauserid int(11),
ADD COLUMN conferidoentradadh datetime,
ADD COLUMN conferidoentradauuid varchar(45);



-- aplicado em produção dia 28/06/2021


