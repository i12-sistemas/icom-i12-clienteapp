import axios from 'axios'
import moment from "moment";
import VueMomentJS from "vue-momentjs";

class Configuracao {

    constructor() {
        this.limpardados();

    }

    async limpardados(){
        this.id = null;
        this.descricao = '';
        this.fileext = null;
        this.filemd5 = null;
        this.filemimetype = null;
        this.tipo = 'VARCHAR';
        this.valor = '';
        this.admin = true;

        this.maxlength = 0;
        this.hint = '';
        this.prependiconmaterial = '';
        this.fileextallowed = null;
        this.filemaxbytes = 0;
        this.filesizebyte = 0;
        this.url = '';

        // this.fileUploader = null;
        // this.clearfile = false;
    }

    urlDownload(usertoken){
        var self = this;
        var link = '';
        if (self.filename){
            link = axios.defaults.baseURL + '/arquivos/download/' +  (localStorage.getItem("token") ? localStorage.getItem("token") : '') + '/' + self.id + '/' + self.filemd5;
        }
        return link;
    }

    async cloneFrom(item){
        this.id = item.id;
        this.descricao = item.descricao;
        this.tipo = item.tipo ? item.tipo.toUpperCase() : '';
        this.fileext = item.fileext;
        this.filemd5 = item.filemd5;
        this.filemimetype = item.filemimetype;
        this.valor = item.valor;
        this.admin = item.admin ? item.admin : false;
        this.maxlength = item.maxlength ? item.maxlength : 0;
        this.hint = item.hint;
        this.prependiconmaterial = item.prependiconmaterial;
        this.fileextallowed = item.fileextallowed;
        this.filemaxbytes = item.filemaxbytes ? item.filemaxbytes : 0;
        this.filesizebyte = item.filesizebyte ? item.filesizebyte : 0;
        this.url = item.url ? item.url : null;
        
    }    

    
    
    async save(pID){
        var self = this;
        var ret =  await self.update();
        return ret;
    }    

    async uploadFile(){
        var self = this;
        let formData = new FormData();
        var fileitem  = self.fileUploader;
        if ((fileitem.uploadPercentage>0) || (fileitem.error)) return;
        fileitem.sending = true;
        const CancelToken = axios.CancelToken;
        const source = CancelToken.source();
        fileitem.source = source;
        formData.append('file', fileitem.file); 
        formData.append('valor', fileitem.file.name); 
        return await axios.post('config/' + self.id, formData,     
              {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                cancelToken: fileitem.source.token,
                onUploadProgress: function( progressEvent ) {
                  fileitem.bytesLoad = progressEvent.loaded;
                  fileitem.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                }.bind(this)
              }
            )
            .then(response => {
                let data = response.data;
                if(!data){
                  fileitem.error = 'Sem resposta do upload'; 
                  return {ok: false, msg: fileitem.error};
                }else{
                  if(!data.ok){
                    fileitem.error = data.msg ? data.msg : 'Falha no upload do arquivo'; 
                    return {ok: false, msg: fileitem.error};
                  }else{
                    return {ok: true, msg: ''};
                  }
                }
                fileitem.sending = false;
                
            }).catch(function (error) {
                var msg = error;
                if (axios.isCancel(error)) {
                  msg = error.message;
                }
                fileitem.sending = false;
                fileitem.error = msg;
                return {ok: false, msg: fileitem.error};
            })
    }    

    async update(){
        var self = this;
        let value = null;
        if(self.tipo=='LIST'){
            value = JSON.stringify(self.valor);
        } else if(self.tipo=='FILE'){
            return await self.uploadFile();
            return;
        }else{
            value = self.valor;
        }
        let datapost = { 
            valor: value
        };
        if(self.clearfile)
            datapost.clearfile = true;
        return await axios.post('config/' + self.id, datapost)
            .then(response => {
                let data = response.data;
                if (!data) {
                    self.error = 'Sem retorno de dados';
                    return {ok: false, msg: 'Sem retorno de dados'};                    
                }
                if (data.ok) {
                    return {ok: true, msg: data.msg, id: data.id};
                }else{
                    return {ok: false, msg: data.msg};
                }

                
            }).catch(function (error) {
                let msg = error;
                if (error.response) {
                    msg = 'Code: ' + error.response.status + ' - ' + msg;
                } else {
                    msg = error.message;
                }
                return {ok: false, msg: msg};
            })
        
    }      
      
}

export default Configuracao;