export default async ({ store }) => {
  await store.dispatch('authusuario/getlocalstorage')
  await store.dispatch('authusuario/checklogon')
}
