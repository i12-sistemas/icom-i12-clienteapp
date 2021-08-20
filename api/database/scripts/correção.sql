start transaction;
update manutencao
set 
limitekm=(kmatual+validadekm)
where (kmatual+validadekm) <> ifnull(limitekm,0);
commit;

set @perclimitekm = 15;
set @dias = 5;

start transaction;
update manutencao
set 
alertakm=round((limitekm - (validadekm * (@perclimitekm/100))),0)
where round((limitekm - (validadekm * (@perclimitekm/100))),0) <> ifnull(alertakm,0);
commit;


start transaction;
update manutencao
set 
limitedata=date(date_add(created_at, interval validadedias day))
where ifnull(validadedias,0)>0
and  ((date(limitedata) <> date(date_add(created_at, interval validadedias day))) or (limitedata is null) );
commit;



start transaction;
update manutencao
set 
alertadata=date(date_add(limitedata, interval @dias day))
where ifnull(validadedias,0)>0
and  ((date(alertadata) <> date(date_add(limitedata, interval @dias day))) or (alertadata is null) )
and realizado=0;
commit;


