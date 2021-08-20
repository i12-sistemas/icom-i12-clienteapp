
const routes = [
  {
    path: '/',
    component: () => import('layouts/MyLayout.vue'),
    meta: { authusuario: true },
    children: [
      { path: '', component: () => import('pages/Index.vue') },
      { path: 'index', name: 'home.index', component: () => import('pages/Index.vue') }
    ]
  },
  {
    path: '/usuario',
    component: () => import('layouts/MyLayoutInit.vue'),
    children: [
      { path: 'primeiroacesso', name: 'login.primeiroacesso', component: () => import('pages/login/primeiroacesso.vue') },
      { path: 'login', name: 'login.usuario', component: () => import('pages/login/usuario.vue') },
      { path: 'esqueciminhasenha', name: 'login.esqueciminhasenha', component: () => import('pages/login/esqueciminhasenha.vue') },
      { path: 'esqueciminhasenha/checkcode', name: 'login.esqueciminhasenha.checkcode', component: () => import('pages/login/esqueciminhasenhacheckcode.vue') }
    ]
  },
  {
    path: '/default',
    meta: { authusuario: true },
    component: () => import('layouts/MyLayoutPages.vue'),
    children: [
      { path: 'cargas/entradas/consulta', name: 'cargas.entradas.consulta', component: () => import('pages/cargas/entrada/consulta.vue'), props: { label: 'Cargas de Entrada' } },
      { path: 'cargas/entradas/:id/edit', name: 'cargas.entradas.edit', component: () => import('pages/cargas/entrada/detalhe.vue'), props: { label: 'Cargas de Entrada' } },

      { path: 'cargas/transferencias/consulta', name: 'cargas.transferencias.consulta', component: () => import('pages/cargas/transferencia/consulta.vue'), props: { label: 'Transferências' } },
      { path: 'cargas/transferencias/:id/edit', name: 'cargas.transferencias.edit', component: () => import('pages/cargas/transferencia/detalhe.vue'), props: { label: 'Transferências' } },
      { path: 'cargas/transferencias/add', name: 'cargas.transferencias.add', component: () => import('pages/cargas/transferencia/novo.vue'), props: { label: 'Transferências' } },

      { path: 'cargas/entregas/consulta', name: 'cargas.entregas.consulta', component: () => import('pages/cargas/entrega/consulta.vue'), props: { label: 'Entregas' } },
      { path: 'cargas/entregas/:id/edit', name: 'cargas.entregas.edit', component: () => import('pages/cargas/entrega/detalhe.vue'), props: { label: 'Entregas' } },
      { path: 'cargas/entregas/add', name: 'cargas.entregas.add', component: () => import('pages/cargas/entrega/novo.vue'), props: { label: 'Entregas' } },
      { path: 'cargas/entregas/baixa', name: 'cargas.entregas.baixa', component: () => import('pages/cargas/entrega/entregabaixa.vue'), props: { label: 'Baixa Entrega' } },

      { path: 'cargas/paletes/consulta', name: 'cargas.paletes.consulta', component: () => import('pages/cargas/palete/consulta.vue'), props: { label: 'Paletes' } },
      { path: 'cargas/paletes/:id/edit', name: 'cargas.paletes.edit', component: () => import('pages/cargas/palete/detalhe.vue'), props: { label: 'Paletes' } },
      { path: 'cargas/paletes/add', name: 'cargas.paletes.add', component: () => import('pages/cargas/palete/novo.vue'), props: { label: 'Paletes' } },

      { path: 'cargas/etiquetas/find', name: 'cargas.etiquetas.find', component: () => import('pages/cargas/etiquetas/detalhe.vue'), props: { label: 'Etiquetas' } },

      { path: 'usuario/logoff', name: 'usuario.logoff', component: () => import('pages/login/UsuarioLogoff.vue') },
      { path: 'configuracao', name: 'configuracao', component: () => import('pages/Configuracao.vue'), props: { label: 'Configuração' } }
    ]
  }
]

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
