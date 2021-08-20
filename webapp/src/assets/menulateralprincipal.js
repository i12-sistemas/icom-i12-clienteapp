// import icons from 'src/assets/icons.js'

const menu = [
  {
    categoria: 'Operacional',
    itens: [
      {
        icon: 'arrow_back',
        text: 'Entradas',
        caption: 'Cargas de entrada',
        to: { name: 'cargas.entradas.consulta' }
      },
      {
        icon: 'multiple_stop',
        text: 'Transferências',
        caption: 'Transferência entre unidade',
        to: { name: 'cargas.transferencias.consulta' }
      },
      {
        icon: 'local_shipping',
        text: 'Saídas',
        caption: 'Carga para entrega',
        to: { name: 'cargas.entregas.consulta' }
      },
      {
        icon: 'qr_code_scanner',
        text: 'Baixar Entrega',
        caption: 'Baixa entrega por CT-e ou Carga',
        to: { name: 'cargas.entregas.baixa' }
      },
      { separator: true },
      {
        icon: 'widgets',
        text: 'Paletes',
        caption: 'Montagem de paletes',
        to: { name: 'cargas.paletes.consulta' }
      },
      { separator: true },
      {
        icon: 'fas fa-barcode',
        text: 'Etiquetas',
        caption: 'Consulta de etiqueta',
        to: { name: 'cargas.etiquetas.find' }
      }
    ]
  },
  {
    categoria: 'Configuração',
    itens: [
      { separator: true },
      { icon: 'settings', text: 'Configuração', to: { name: 'configuracao' } }
    ]
  }
]

class MenuLateral {
  constructor () {
    this.menu = menu
  }

  async processar (pUsuario) {
    // var novoMenu = []
    // for (var idx in menu) {
    //   let categ = menu[idx]
    //   if (!categ.inativo) {
    //     let novaCategoria = { categoria: categ.categoria, itens: [] }
    //     for (var idxItem in categ.itens) {
    //       var element = categ.itens[idxItem]
    //       if (element.menu) {
    //         var novosubmenu = []
    //         for (var idxItemSubMenu in element.menu) {
    //           var submenu = element.menu[idxItemSubMenu]
    //           let tempermissao = true
    //           if ((pUsuario) && (submenu.idpermissao)) tempermissao = await pUsuario.permite(submenu.idpermissao)
    //           if (tempermissao) novosubmenu.push(submenu)
    //         }
    //         element.menu = novosubmenu
    //       }

    //       if (element.idpermissao) {
    //         let tempermissao = false
    //         if (pUsuario) tempermissao = await pUsuario.permite(element.idpermissao)
    //         if (tempermissao) novaCategoria.itens.push(element)
    //       } else {
    //         novaCategoria.itens.push(element)
    //       }
    //     }
    //     if (novaCategoria.itens.length > 0) novoMenu.push(novaCategoria)
    //   }
    // }
    // this.menu = novoMenu
    this.menu = menu
  }
}

export default MenuLateral
