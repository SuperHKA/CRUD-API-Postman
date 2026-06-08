Integración con Seguridad en APIs con JWT (Arquitectura Stateless)
1.	Los archivos que creas: login.php (emisor del token), seguridad.php (el middleware perimetral con try/catch) y api/products.php (el endpoint protegido).
2.	Las herramientas clave: Composer, la biblioteca externa firebase/php-jwt, y Postman / Thunder Client para enviar los encabezados Authorization: Bearer.
3.	Lo que aprende el estudiante: Aprenden cómo asegurar un backend completo para aplicaciones móviles o frontend modernos (Single Page Applications). Entienden el concepto de autenticación Stateless (sin estado), donde el servidor no recuerda al usuario mediante sesiones tradicionales, sino que exige el token en cada petición HTTP.

## ¿Qué es el Payload?
El nombre Payload significa literalmente "carga útil". Es la parte central del JWT donde viaja la información que el servidor quiere "recordar" sobre el usuario después de que este se ha logueado.
En tu código, el payload es el array asociativo que contiene tres tipos de datos:
•	Claims Registrados (estándares):
o	iss (Issuer): Quién emite el token (en tu caso, tu servidor local).
o	iat (Issued At): Cuándo fue creado el token (usando time()).
o	exp (Expiration): Cuándo dejará de ser válido (en tu caso, time() + 3600 segundos, o sea, 1 hora).
•	Claims Privados (los tuyos):
o	data: Aquí es donde metes la información personalizada del usuario (id, usuario, rol).
¡Importante! NUNCA pongas la contraseña (clave) dentro del payload. Aunque el JWT está firmado, no está cifrado (cualquiera que tenga el token puede leer el contenido usando herramientas como jwt.io). Por eso, solo guardas datos de identidad que no comprometan la seguridad.
