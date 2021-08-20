cd "C:\Users\Weber\Desenvolvimento\Websites\ConectaTransporte\ConectaColetaAdmin\conecta-mobileapp\src-cordova"
quasar build -m android
cd "C:\Users\Weber\Desenvolvimento\Websites\ConectaTransporte\ConectaColetaAdmin\conecta-mobileapp\release"
copy "C:\Users\Weber\Desenvolvimento\Websites\ConectaTransporte\ConectaColetaAdmin\conecta-mobileapp\src-cordova\platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk" app-release-unsigned.apk
jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore conecta-upload.keystore -storepass C0n3ct@ app-release-unsigned.apk conectaKeyGoogle
del ConectaAdminApp.apk
zipalign -v 4 app-release-unsigned.apk ConectaAdminApp.apk

