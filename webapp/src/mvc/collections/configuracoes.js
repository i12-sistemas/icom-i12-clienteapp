import axios from 'axios'
import Configuracao from '@/models/configuracao.js'

class Configuracoes {

    constructor() {
        this.limpardados();
    }

    async limpardados(){
        this.itens = null;


        // limit = page size limit
        // orderby = campo de ordenação
        // descending = 1 or 0 
        // publicado = 1 ou 0 - filtro pelo campo publicado
        // find = string de consulta
        // page = numero atual da pagina

        this.page = 1;
        this.limit = 10;
        this.orderby = 'titulo',
        this.descending = 0;
        this.publicado = 1;
        this.find = '';
        this.recordCount = 0;
    }

    async fetch(){
        var self = this;
        let params = { 
            list : (self.list ? JSON.stringify(self.list) : null),
            // limit : (self.limit ? self.limit : 0),
            // orderby : (self.orderby  ? self.orderby : 'dhpost'),
            // descending : (self.descending ? self.descending ? 1 : 0 : 1),
            // publicado : self.publicado,
            // find: (self.find ? self.find : '')
          }   

        return await axios.get('config', {params})
            .then(response => {
                let data = response.data;
                if (data.ok) {
                    let dados = data.data;
                    self.itens =[];
                    dados.forEach(element => {
                        let u = new Configuracao;
                        u.cloneFrom(element);
                        self.itens.push(u);
                    });
                    self.recordCount = self.itens.length;
                    return {ok: true, msg: ''};
                }else{
                    self.error = (data.msg ? data.msg : 'Sem retorno de dados');
                    return {ok: false, msg: 'Sem retorno de dados'};
                }
            }
            ).catch(error => {
                let msg = error;
                if (error.response) {
                    msg = 'Code: ' + error.response.status + ' - ' + error.response.data.message;
                } else {
                    msg = error.message;
                }
                return {ok: false, msg: msg };

                self.error  = msg;
                }
            )



    } 
}
export default Configuracoes;