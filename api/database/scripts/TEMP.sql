select coletas_nota.remetentecnpj as cnpj, cliente.razaosocial, cliente.fantasia, count(distinct coletas_nota.notachave) as qtdeNota, min(coletas_nota.dhlocal_created_at) as desdeDe,
ifnull(group_concat(distinct emailcliente.email), 'contato@i12.com.br') as email,
count(distinct req.token) as q
from coletas_nota
left join coletas_nota_xml_token as req on req.cnpj=coletas_nota.remetentecnpj and req.expire_at > now()
left join coletas_nota_xml on coletas_nota_xml.chave=coletas_nota.notachave
left join cliente on coletas_nota.remetentecnpj=cliente.cnpj
	left join 
		(select emails.*, cliente_email.clienteid
			from emails_tags
			inner join emails on emails.id=emails_tags.emailid
			inner join cliente_email on cliente_email.emailid=emails.id
            where emails_tags.tag='nfe') as emailcliente on emailcliente.clienteid=cliente.id
where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null and coletas_nota_xml.id is null
and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=2
and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -60 day)
and req.token is null
group by coletas_nota.remetentecnpj
order by coletas_nota.dhlocal_created_at desc
;


delete FROM conectaadmin.coletas_nota_xml_token;

select * FROM conectaadmin.coletas_nota_xml_token;


select coletas_nota.notanumero, coletas_nota.notaserie, coletas_nota.notachave, coletas_nota.dhlocal_created_at as desdede
from coletas_nota
inner join cliente on if(coletas_nota.remetenteid is not null, coletas_nota.remetenteid=cliente.id, coletas_nota.remetentecnpj=cliente.cnpj) 
where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null
and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=3
and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -3 month)
and cliente.cnpj='71655203000164'
order by coletas_nota.id desc