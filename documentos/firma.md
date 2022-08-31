# Integración FirmaGob

Una vez que ya tengas instalado SIMPLE OnPremise, debes incorporar tu entidad de FirmaGob desde la sección de manager `<mi-url>/manager/cuentas`, seleccionando la cuenta a la que quieras asociar tus credenciales, desde el apartado "Editar configuración de Firma Electrónica", ahí podras registrar tu entidad designada.


![Ejemplo registrar entidad de FirmaGob desde Manager](https://github.com/digital-gob-cl/simple2/blob/master/documentos/imagenes/form_firma_manager.jpg)


Luego de registrar la entidad para tu cuenta, deberás registrar a tus firmantes dirigiendote a la sección de `<mi-url>/backend/configuracion/firmas_electronicas` "Nuevo" e ingresar la información de la persona, este paso necesita que hayas registrado el nombre de "entidad" previamente (el paso anterior).

![Ejemplo registrar firmante de FirmaGob desde Backend](https://github.com/digital-gob-cl/simple2/blob/master/documentos/imagenes/form_crear_firmante.png)

## Consideración:
SIMPLE necesita que definas ciertas variables de entorno antes de comenzar a utilizar la firma electrónica (FirmaGob). Estas credenciales debes solicitarlas directamente con el encargado de gestionar tu proceso de activación.

Deben ser definidas en el archivo `.env`.

``` sh
JWT_SECRET=<my-secret>
JWT_API_TOKEN_KEY=<my-token-key>
JWT_URL_API_FIRMA=<url-api-firmagob>
```

## Nota:
Debes verificar si puedes utilizar la integración de FirmaGob y posteriormente, cumplir exitosamente el proceso de activación.
Para obtener las credenciales sandbox y de producción solicítalas en el sitio de https://firma.digital.gob.cl/