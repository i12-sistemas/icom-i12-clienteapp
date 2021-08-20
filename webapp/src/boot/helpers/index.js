import moment from 'moment'
import PlacaMercosul from './placa-mercosul-brasil'
import jsmd5 from 'js-md5'
import dialogSelecaoUnidadePadrao from 'src/pages/unidades/dialog-selecao.vue'
import Unidade from 'src/mvc/models/unidade.js'

/* eslint-disable no-useless-escape */
export default async ({ Vue }) => {
  Vue.prototype.$helpers = {
    echo (p1) {
      return p1
    },
    vibrate (time) {
      if ('vibrate' in navigator) {
        navigator.vibrate(time)
      }
    },
    isInt (str) {
      return /^\+?(0|[1-9]\d*)$/.test(str)
    },
    isFloat (val) {
      var floatRegex = /^-?\d+(?:[.,]\d*?)?$/
      if (!floatRegex.test(val)) return false
      val = parseFloat(val)
      if (isNaN(val)) return false
      return true
    },
    async ShowSelecaoUnidadePadrao (app) {
      return new Promise((resolve) => {
        app.$q.dialog({
          parent: app,
          component: dialogSelecaoUnidadePadrao,
          cancel: true
        }).onOk(async unidade => {
          resolve({ ok: unidade })
        }).onCancel(() => {
          resolve({ ok: false })
        })
      })
    },
    async getUnidadeLogada (app) {
      var unidade = null
      if (app.$store.state.authusuario.unidade ? app.$store.state.authusuario.unidade.id > 0 : false) unidade = new Unidade(app.$store.state.authusuario.unidade)
      return unidade
    },
    generateUUID (hideMask) {
      var d = new Date().getTime()
      // Time in microseconds since page-load or 0 if unsupported
      var d2 = (performance && performance.now && (performance.now() * 1000)) || 0
      var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16
        if (d > 0) {
          // eslint-disable-next-line no-redeclare
          var r = (d + r) % 16 | 0
          d = Math.floor(d / 16)
        } else {
          // eslint-disable-next-line no-redeclare
          var r = (d2 + r) % 16 | 0
          d2 = Math.floor(d2 / 16)
        }
        return (c === 'x' ? r : (r & 0x7 | 0x8)).toString(16)
      })
      if (hideMask) uuid = uuid.replace('-', '')
      return uuid
    },
    async showDialog (ret, forceAsError) {
      var title = ''
      var bgcolor = 'white'
      var color = 'black'
      var msg = ret.msg ? ret.msg : ''
      if (ret.error) {
        if (ret.error.codeinit === 5) {
          bgcolor = 'red-9'
          color = 'white'
          title = ':-( Ops'
        } else if (ret.error.codeinit === 4) {
          bgcolor = 'yellow-9'
          color = 'white'
          title = ':-( Ops'
        } else {
          bgcolor = 'black'
          color = 'white'
          title = ':-( Ops'
        }
        if (ret.error) {
          msg = '<p class="text-body">' + ret.error.title + ', se o problema persistir faça contato com o suporte.</p>' +
                '<p class="text-caption">Msg técnica: ' + ret.error.msg + '</p>'
        }
      }
      if (ret.warning) {
        bgcolor = 'blue-grey'
        color = 'white'
        title = ''
        msg = ret.msg
      }
      if (ret.permissaonegada) {
        bgcolor = 'grey-10'
        color = 'white'
        title = 'Acesso negado!'
        msg = ret.msg
      }

      if ((!ret.error) && (!ret.ok) && (forceAsError) && (!ret.permissaonegada)) {
        bgcolor = 'red-9'
        color = 'white'
        title = ':-( Ops'
        msg = '<p class="text-body">' + ret.msg + '</p>' +
              '<p class="text-caption">Se o problema persistir faça contato com o suporte.</p>'
      }
      return new Promise((resolve) => {
        Vue.prototype.$q.dialog({
          title: title,
          message: msg,
          html: true,
          cancel: false,
          persistent: true,
          color: color,
          class: 'bg-' + bgcolor + ' text-' + color
        }).onOk(async data => {
          resolve({ ok: true })
        }).onCancel(() => {
          resolve({ ok: true })
        })
      })
    },
    bcrypt (valor) {
      if (valor.length <= 11) {
        return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, '\$1.\$2.\$3\-\$4')
      } else {
        return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, '\$1.\$2.\$3\/\$4\-\$5')
      }
    },
    href (link) {
      window.open(link, '_blank')
    },
    sendmail (mail) {
      window.open('mailto:' + mail, '_blank')
    },
    whatsapp (number) {
      window.open('https://api.whatsapp.com/send?phone=55' + number, '_blank')
    },
    discartel (number) {
      window.open('tel:' + number, '_blank')
    },
    isOdd (index) {
      if (index % 2 === 0) {
        return true
      }
    },
    mapslink (endereco) {
      let link = 'http://maps.google.com/maps?q=' + encodeURIComponent(endereco)
      window.open(link, '_blank')
    },
    mapsroutelink (origin, destination) {
      let link = 'https://www.google.com/maps/dir/?api=1&travelmode=driving' +
               ((origin !== '') ? '&origin=' + encodeURIComponent(origin) : '') +
               ((destination !== '') ? '&destination=' + encodeURIComponent(destination) : '')
      window.open(link, '_blank')
    },
    mascaraCpfCnpj (valor) {
      if (valor === null) return ''
      if (valor.length <= 11) {
        return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, '\$1.\$2.\$3\-\$4')
      } else {
        return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, '\$1.\$2.\$3\/\$4\-\$5')
      }
    },
    mascaraCEP (valor) {
      if (valor === null) return ''
      return valor.replace(/(\d{2})(\d{3})(\d{3})/g, '\$1.\$2\-\$3')
    },
    mascaraChaveNFe (valor) {
      if (valor === null) return ''
      return valor.replace(/(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})/g, '\$1 \$2 \$3 \$4 \$5 \$6 \$7 \$8 \$9 \$10 \$11 ')
    },
    randomDate (start, end) {
      // start = new Date(2001, 0, 1)
      // end = new Date()
      return new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()))
    },
    paginationInfo (currentPage, pagesize, paginationtotal) {
      var info = ''
      if (paginationtotal === 0) {
        info = 'Nenhum registro'
      } else {
        if (paginationtotal < pagesize) {
          info = 'Mostrando ' + paginationtotal + ' registro(s)'
        } else {
          info =
            'Página ' +
            currentPage +
            ' - Mostrando ' +
            pagesize +
            ' de ' +
            paginationtotal +
            ' registro(s)'
        }
      }
      return info
    },
    md5 (s) {
      return jsmd5(s)
    },
    toBool (val) {
      return (val === true) || (val === 1) || (val === '1') || (val === 'true') || (val === 'True') || (val === 'TRUE')
    },
    getAge (born) {
      var now = new Date()
      var nborn = new Date(born)
      var birthday = new Date(now.getFullYear(), nborn.getMonth(), nborn.getDate())
      if (now >= birthday) {
        return now.getFullYear() - nborn.getFullYear()
      } else {
        return now.getFullYear() - nborn.getFullYear() - 1
      }
    },
    MinToHourTxt (Minutes, middleChar = 'h', EndChar = 'm') {
      var hours = Math.trunc(Minutes / 60)
      var shr = ''
      if (hours <= 0) {
        shr = ''
      } else {
        shr = hours + middleChar
      }
      var minutes = (Minutes % 60).toFixed(0)
      var s = minutes + ''
      var n = s.length
      if (n === 1) {
        s = '0' + s
      } else if (n === 0) {
        s = '00' + s
      }
      return shr + s + EndChar
    },
    HourToHHMM (horaStr, middleChar = 'h', EndChar = 'm') {
      var r = '-'
      try {
        var hora = moment(horaStr, 'HH:mm:ss')
        r = hora.format('HH') + middleChar + hora.format('mm') + EndChar
      } catch (error) {
        console.error(error)
        r = '-'
      }
      return r
    },
    getPerc (vrlatual, vlrtotal, multiplicoCem = true, QtdeCasaDecimal = 2, forcelimit100 = false) {
      if (vrlatual === 0 || vlrtotal === 0) {
        return 0
      }

      var perc = vrlatual / vlrtotal
      if (multiplicoCem) {
        perc = perc * 100
      }
      perc = perc.toFixed(QtdeCasaDecimal)
      if ((forcelimit100) && (perc > 100)) perc = 100
      return perc
    },
    DateToData (data) {
      try {
        var d = moment(data, 'DD/MM/YYYY')
        if (!d.isValid()) throw new Error('Data inválida')
        return d.format('YYYY-MM-DD')
      } catch (error) {
        return null
      }
    },
    dateToBR (date, omiteano = false) {
      var esteano = parseInt(moment().format('YYYY'))
      var data = moment(date, 'YYYY-MM-DD')
      var anodata = parseInt(data.format('YYYY'))
      if ((esteano === anodata) && (omiteano)) {
        return data.format('DD/MM')
      } else {
        return data.format('DD/MM/YYYY')
      }
      // return moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY')
    },
    timeToBR (date, mask) {
      return moment(date, 'YYYY-MM-DD HH:mm:ss').format(mask)
    },
    datetimeFormat (date, mask) {
      moment.locale('pt-br')
      if (date === '' || date === undefined) return '-'
      var dh = moment(date, 'YYYY-MM-DD HH:mm:ss')
      if (dh < moment('01/01/1900', 'YYYY-MM-DD')) return '-'
      return dh.format(mask)
    },
    datetimeToBR (date, OmiteDataSeHoje = false, OmiteSegundo = false) {
      moment.locale('pt-br')
      if (date === '' || date === undefined) return '-'
      var dh = moment(date, 'YYYY-MM-DD HH:mm:ss')
      if (dh < moment('01/01/1900', 'YYYY-MM-DD')) return '-'
      var mask = 'DD/MM/YYYY - HH:mm:ss'
      if (OmiteDataSeHoje) {
        var today = dh.format('DD/MM/YYYY') === moment().format('DD/MM/YYYY')
        if (today) {
          mask = OmiteSegundo ? 'HH:mm' : 'HH:mm:ss'
        }
      } else {
        mask = 'DD/MM/YYYY - HH:mm' + (OmiteSegundo ? '' : ':ss')
      }

      return moment(date, 'YYYY-MM-DD - HH:mm:ss').format(mask)
    },
    datetimeRelativeToday (datetime) {
      moment.locale('pt-br')
      if (datetime) {
        var dh = moment(datetime, 'YYYY-MM-DD HH:mm:ss')
        return dh.fromNow()
      } else {
        return '-'
      }
    },
    errorReturn (pError, pValidacao = false) {
      try {
        if (!pError) throw new Error('Erro desconhecido (error null)')
        var msg = ''
        var code = -1
        var title = 'Ops! Algo não deu certo'
        var nCateg = 0
        if (pError.response) {
          code = pError.response.status ? pError.response.status.toString() : ''
          nCateg = parseInt(code.substring(0, 1))
          msg = (pError.response.data.message ? pError.response.data.message : (pError.response.data.msg ? pError.response.data.msg : '')) + ' - Code: ' + code
          if (nCateg === 4) title = 'Algo não deu certo aqui no app'
          if (nCateg === 5) title = 'Algo não deu certo no servidor'
          if (nCateg === 3) title = 'Algum redicionamento ocorreu'
        } else {
          msg = pError.message
        }
        var r = { ok: false, msg: msg, error: { code: code, error: pError, title: title, msg: msg, codeinit: nCateg } }
        if (pValidacao) r.warning = true
        return r
      } catch (error) {
        return { ok: false, msg: error.message, error: { code: -1, error: pError, title: 'Ops! Algo não deu certo', msg: error.message } }
      }
    },
    formatRS (valor, ShowCifrao = true, QtdeCasaDecimal = 2) {
      var v = 0
      if (typeof valor !== 'undefined') {
        v = valor.toFixed(QtdeCasaDecimal)
      }
      var numero = v.split('.')
      numero[0] = numero[0].split(/(?=(?:...)*$)/).join('.')
      var s = numero.join(',')

      // let v = 0;
      // if (!(valor == "undefined")) {
      //   v = valor.toFixed(QtdeCasaDecimal);
      // }
      return (ShowCifrao ? 'R$ ' : '') + (v !== 0 ? s : '-')
    },
    padLeftZero (num, size) {
      var s = num + ''
      while (s.length < size) s = '0' + s
      return s
    },
    checkpermissao (minhaspermissoes, permissao) {
      let idx = minhaspermissoes.indexOf(permissao)
      return idx >= 0
    },
    nl2br (str, isxhtml) {
      if (typeof str === 'undefined' || str === null) {
        return ''
      }
      var breakTag = isxhtml || typeof isxhtml === 'undefined' ? '<br />' : '<br>'
      return (str + '').replace(
        /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
        '$1' + breakTag + '$2'
      )
    },
    clearMask (str) {
      let s = str
      s = s.replace(/[.,-/_/ /(/)/[\]]/g, '')
      return s
    },
    mascaraCpf (valor) {
      return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, '\$1.\$2.\$3\-\$4')
    },
    mascaraCnpj (valor) {
      return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, '\$1.\$2.\$3\/\$4\-\$5')
    },
    mascaraDocCPFCNPJ (valor) {
      let d = valor || ''
      d = this.clearMask(d)
      if (d.length === 14) {
        return this.mascaraCnpj(d)
      } else {
        return this.mascaraCpf(d)
      }
    },
    mascaratel (telefone) {
      let v = this.clearMask(telefone)
      let q = v.length
      if (q > 8) {
        v = v.replace(/^(\d{2})(\d)/g, '($1) $2') // Coloca parênteses em volta dos dois primeiros dígitos
        v = v.replace(/(\d)(\d{4})$/, '$1-$2') // Coloca hífen entre o quarto e o quinto dígitos
      } else {
        v = v.replace(/(\d)(\d{4})$/, '$1-$2') // Coloca hífen entre o quarto e o quinto dígitos
      }

      return v
    },
    validarCNPJCPF (doc) {
      let d = doc || ''
      if (d.length === 14) {
        return this.validarCNPJ(d)
      } else {
        return this.validaCPF(d)
      }
    },
    strToMoment (pStrDatetime) {
      moment.locale('pt-br')
      var dh = moment()
      if (pStrDatetime.toString().indexOf('T') >= 0) {
        dh = moment(pStrDatetime)
      } else {
        var tembarra = (pStrDatetime.toString().indexOf('/') >= 0)
        dh = moment(pStrDatetime, (tembarra ? 'YYYY/MM/DD' : 'YYYY-MM-DD') + ' HH:mm:ss')
      }
      return dh
    },
    placaMask (pPlacaStr) {
      var placa = new PlacaMercosul()
      return placa.mask(pPlacaStr)
    },
    diffTime (pdh1, pdh2, unidadeTempo) {
      var dh1 = pdh1 === '' ? moment() : moment(pdh1)
      var dh2 = pdh2 === '' ? moment() : moment(pdh2)
      var diff = dh2.diff(dh1, unidadeTempo)
      return diff
    },
    validarCNPJ (cnpj) {
      cnpj = cnpj.replace(/[^\d]+/g, '')
      if (cnpj === '') return false

      if (cnpj.length !== 14) { return false }

      // Elimina CNPJs invalidos conhecidos
      if (cnpj === '00000000000000' || cnpj === '11111111111111' || cnpj === '22222222222222' ||
            cnpj === '33333333333333' || cnpj === '44444444444444' || cnpj === '55555555555555' ||
            cnpj === '66666666666666' || cnpj === '77777777777777' || cnpj === '88888888888888' ||
            cnpj === '99999999999999') { return false }

      // Valida DVs
      let tamanho = cnpj.length - 2
      let numeros = cnpj.substring(0, tamanho)
      let digitos = cnpj.substring(tamanho)
      let soma = 0
      let pos = tamanho - 7
      let i
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--
        if (pos < 2) { pos = 9 }
      }
      let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
      if (parseInt(resultado) !== parseInt(digitos.charAt(0))) { return false }

      tamanho = tamanho + 1
      numeros = cnpj.substring(0, tamanho)
      soma = 0
      pos = tamanho - 7
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--
        if (pos < 2) { pos = 9 }
      }
      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
      if (parseInt(resultado) !== parseInt(digitos.charAt(1))) { return false }

      return true
    },
    validaCPF (cpf) {
      var numeros, digitos, soma, i, resultado, digitosiguais
      digitosiguais = 1
      if (cpf.length < 11) return false
      for (i = 0; i < cpf.length - 1; i++) {
        if (cpf.charAt(i) !== cpf.charAt(i + 1)) {
          digitosiguais = 0
          break
        }
      }
      if (!digitosiguais) {
        numeros = cpf.substring(0, 9)
        digitos = cpf.substring(9)
        soma = 0
        for (i = 10; i > 1; i--) soma += numeros.charAt(10 - i) * i
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
        if (resultado !== digitos.charAt(0)) return false
        numeros = cpf.substring(0, 10)
        soma = 0
        for (i = 11; i > 1; i--) soma += numeros.charAt(11 - i) * i
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11)
        if (resultado !== digitos.charAt(1)) return false
        return true
      } else return false
    },
    getExtension (filename) {
      return filename.split('.').pop()
    },
    bytesToHumanFileSizeString (fileSizeInBytes) {
      var i = -1
      var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB']
      do {
        fileSizeInBytes = fileSizeInBytes / 1024
        i++
      } while (fileSizeInBytes > 1024)

      return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i]
    },
    cteExtraiQrCode (data) {
      var numero = null
      var ehCTeQrCode = (data.indexOf('https://nfe.fazenda.sp.gov.br/CTeConsulta') >= 0)
      if (ehCTeQrCode) {
        var tag = 'chCTe='
        var idxCte = data.indexOf(tag)
        idxCte = idxCte + tag.length
        numero = data.substr(idxCte, 44)
      }
      return numero
    },
    validaEmail (email) {
      var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/
      // var address = document.getElementById[email].value;
      return reg.test(email)
    },
    arrayObjectIndexOf (myArray, searchTerm, property) {
      for (var i = 0, len = myArray.length; i < len; i++) {
        if (myArray[i][property] === searchTerm) return i
      }
      return -1
    },
    joinElement (myArray, elemento, CharJoin) {
      var a = []
      myArray.forEach(element => {
        a.push(element[elemento])
      })
      return a.join(CharJoin)
    },
    replaceAt (str, index, replacement) {
      var s = str
      return (
        s.substr(0, index) +
        replacement +
        s.substr(index + replacement.length)
      )
    },
    jsonEqual (a, b) {
      return JSON.stringify(a) === JSON.stringify(b)
    },

    async  asyncForEach (array, callback) {
      for (let index = 0; index < array.length; index++) {
        await callback(array[index], index, array)
      }
    }
  }
}
