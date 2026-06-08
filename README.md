# Integración con Seguridad en APIs con JWT (Arquitectura Stateless)
1.	Los archivos que creas: login.php (emisor del token), seguridad.php (el middleware perimetral con try/catch) y api/products.php (el endpoint protegido).
2.	Las herramientas clave: Composer, la biblioteca externa firebase/php-jwt, y Postman / Thunder Client para enviar los encabezados Authorization: Bearer.
3.	Lo que aprende el estudiante: Aprenden cómo asegurar un backend completo para aplicaciones móviles o frontend modernos (Single Page Applications). Entienden el concepto de autenticación Stateless (sin estado), donde el servidor no recuerda al usuario mediante sesiones tradicionales, sino que exige el token en cada petición HTTP.

## ¿Qué es el Payload?
El nombre Payload significa literalmente "carga útil". Es la parte central del JWT donde viaja la información que el servidor quiere "recordar" sobre el usuario después de que este se ha logueado.
En tu código, el payload es el array asociativo que contiene tres tipos de datos:

1. Claims Registrados (Estándar)
Campos predefinidos por el protocolo JWT para asegurar la integridad y el tiempo de vida del token:

 - iss (Issuer): Identifica a la entidad emisora del token (en este caso, nuestro servidor local).
 - iat (Issued At): Registra el timestamp exacto (usando time()) de cuándo fue emitido el token.
 - exp (Expiration): Define el tiempo límite de validez. Se establece mediante time() + 3600, otorgando una sesión activa de 1 hora.

2. Claims Privados (Personalizados)
Contienen la información específica de negocio necesaria para la sesión:
 - data: Objeto que encapsula la identidad y permisos del usuario:
 - id: Identificador único del usuario en la base de datos.
 - usuario: Nombre de usuario (ej. admin).
 - rol: Perfil de acceso (ej. profesor).

 ## Postman
 
 <img width="1522" height="761" alt="image" src="https://github.com/user-attachments/assets/adc0fed3-360b-4e00-aa19-35b697d07c50" />


Conversación con Gemini
Se

debe aplicar password_hash() con PASSWORD_BCRYPT al crear el usuario admin y

password_verify() al momento del login.

Utilizar una interfaz para esta parte. si yo quisiera implementar una interfaz para esta parte de las contraseñas en este punto, me podrías dar un ejemplo <?PHP



include_once 'config/config.php';

// login.php

require_once str_replace('\\', '/', __DIR__) . '/vendor/autoload.php';

use Firebase\JWT\JWT;



// Simulación de datos recibidos (POST)

$usuario = $_POST['usuario'] ?? '';

$clave = $_POST['clave'] ?? '';



// Validamos (reemplaza con tu lógica de BD si quieres)

if ($usuario === JWT_USER_SECRET && $clave === JWT_CLAVE_SECRET) {

    $clave_secreta = "tu_clave_secreta_super_segura_123";

    $algoritmo = 'HS256';

   

    $payload = [

        'iss' => 'http://localhost',

        'iat' => time(),

        'exp' => time() + 3600, // Expira en 1 hora

        'data' => [

            'id' => 45,

            'usuario' => 'admin',

            'rol' => 'profesor'

        ]

    ];



    $jwt = JWT::encode($payload, $clave_secreta, $algoritmo);



    header('Content-Type: application/json');

    echo json_encode(['token' => $jwt]);

} else {

    http_response_code(401);

    echo json_encode(['error' => 'Credenciales inválidas']);

}



?> acá en el login

¡Excelente iniciativa, Irina! Implementar una Interfaz (Interface) en PHP es la forma más profesional de enseñar "Programación Orientada a Objetos" (POO). Obligas al código a seguir un contrato: si algo es un Autenticador, debe saber verificar contraseñas.

Aquí tienes cómo transformar tu código para usar una interfaz.

1. Definir la Interfaz (IAutenticador.php)
Esto define el "contrato". Cualquier clase que gestione seguridad deberá cumplir con estos métodos.

PHP
interface IAutenticador {
    public function verificarCredenciales($usuario, $clave_input, $hash_db);
}
2. Implementar la clase (AuthService.php)
Esta clase cumple el contrato y usa password_verify de forma segura.

PHP
require_once 'IAutenticador.php';

class AuthService implements IAutenticador {
    public function verificarCredenciales($usuario, $clave_input, $hash_db) {
        // Comparamos el usuario y verificamos el hash de la clave
        if ($usuario === 'admin' && password_verify($clave_input, $hash_db)) {
            return true;
        }
        return false;
    }
}
3. El login.php transformado
Ahora tu login.php se vuelve mucho más limpio y profesional. Fíjate cómo usa la clase sin preocuparse por "cómo" se verifica la clave, solo "que" se verifique.

PHP
<?php
require_once 'vendor/autoload.php';
require_once 'AuthService.php'; // Tu nueva clase
use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"), true);
$usuario = $data['usuario'] ?? '';
$clave = $data['clave'] ?? '';

// Simulamos que este hash viene de tu base de datos (¡Guardado previamente!)
// El hash de '12345678' usando PASSWORD_BCRYPT
$hash_en_bd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; 

$auth = new AuthService();

if ($auth->verificarCredenciales($usuario, $clave, $hash_en_bd)) {
    // ... aquí generas el JWT (la lógica sigue igual)
    $payload = ['data' => ['usuario' => $usuario]];
    $jwt = JWT::encode($payload, 'tu_clave_secreta', 'HS256');
    echo json_encode(['token' => $jwt]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
}
¿Por qué esto es un nivel superior para tus estudiantes?
Desacoplamiento: El login.php no sabe si estás usando password_verify o si mañana quieres cambiarlo por una API externa de validación. Como el login.php depende de la interfaz (IAutenticador) y no de la clase concreta, tu código es indestructible ante cambios futuros.

Arquitectura Profesional: Estás introduciendo el principio de Inyección de Dependencias y Programación basada en Interfaces, conceptos que definen a un desarrollador de software Senior.

Seguridad por diseño: Al forzar el uso de password_verify, les quitas la tentación de comparar contraseñas con == o ===, que es el error que permite el robo de claves por comparación de cadenas.

Reto para tus jóvenes:
Diles: "Si pueden cambiar la clase AuthService por otra clase LdapAuthService (que valide contra un servidor corporativo) sin tocar una sola línea de su login.php, habrán diseñado un sistema modular perfecto".

¿Qué te parece este nivel de abstracción? ¿Crees que tus alumnos están listos para este "contrato" de interfaces? ¡Es un salto pedagógico muy valioso!

la clave input que viene del postman viene 123445678 y se procede con password_hass (12345678, hash_en_bd) y si ambos hash son inguales buala

¡Casi! Tienes la lógica correcta en mente, pero hay un detalle técnico muy importante que debes corregir para que tus alumnos no se confundan.

La función password_verify($clave_input, $hash_en_bd) no compara "dos hashes", sino que hace algo mucho más inteligente.

La lógica detrás de password_verify
No necesitas aplicar password_hash a la clave que viene de Postman. Funcionaría así:

En el Registro (cuando el admin crea el usuario):

El usuario escribe: 12345678

Tú ejecutas: $hash_seguro = password_hash('12345678', PASSWORD_BCRYPT);

Guardas ese $hash_seguro en la base de datos.

En el Login (cuando el usuario vuelve a entrar):

El usuario envía desde Postman: 12345678

Tú recuperas el hash de la base de datos (por ejemplo, $2y$10$92IX...).

Tú NO hasheas la clave recibida.

Simplemente ejecutas: password_verify('12345678', $hash_en_bd);

¿Qué hace realmente password_verify internamente?
Ella toma la clave de texto plano (12345678), extrae el algoritmo y la "sal" (un valor aleatorio que se usó originalmente) del $hash_en_bd y hace el cálculo matemático. Si el resultado coincide con el hash guardado, retorna true.

Por qué esto es vital para tu laboratorio:
Si intentas hacer password_hash a lo que viene de Postman para compararlo con el hash de la base de datos, siempre te dará falso. ¿Por qué? Porque password_hash genera un "salt" (una cadena aleatoria) diferente cada vez que se ejecuta, incluso si la contraseña es la misma.

password_hash('12345678', ...) -> Resultado A

password_hash('12345678', ...) -> Resultado B (¡Diferente a A!)

Por eso password_verify es necesaria: está diseñada para leer el hash que guardaste y saber cómo recrear la comparación correctamente.

El código corregido para tu AuthService.php
PHP
public function verificarCredenciales($usuario, $clave_input, $hash_en_bd) {
    // 1. Verificamos el usuario
    if ($usuario !== 'admin') {
        return false;
    }

    // 2. Usamos password_verify directamente
    // ¡NO hasheamos la clave_input antes!
    if (password_verify($clave_input, $hash_en_bd)) {
        return true;
    }

    return false;
}
¡Esta es una lección de oro para tus jóvenes! Si logran entender que el hash guardado en la base de datos contiene en sí mismo toda la información necesaria para la verificación, habrán dado un paso gigante en su formación como desarrolladores de software.

¿Ves cómo la lógica se simplifica al usar la herramienta correcta? ¡Esto les va a ahorrar muchísimas horas de frustración depurando!

// 2. Capturar el encabezado Authorization de forma robusta

$encabezado_auth = null;

if (isset($_SERVER['Authorization'])) {

    $encabezado_auth = trim($_SERVER["Authorization"]);

} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

    $encabezado_auth = trim($_SERVER["HTTP_AUTHORIZATION"]);

} elseif (function_exists('apache_request_headers')) {

    $headers = apache_request_headers();

    if (isset($headers['Authorization'])) {

        $encabezado_auth = trim($headers['Authorization']);

    }

} me podrías explicar este asunto

Este bloque de código es fundamental para la interoperabilidad. Cuando desarrollas APIs, el mayor dolor de cabeza es que cada servidor web (Apache, Nginx, IIS) y cada configuración de PHP maneja los encabezados HTTP de forma ligeramente distinta.

Aquí te explico "este asunto" paso a paso para que puedas explicárselo a tus alumnos:

¿Qué estamos buscando?
El cliente envía su token en un encabezado llamado Authorization. El estándar es que luzca así: Authorization: Bearer <tu_token_aqui>.

El problema es que PHP no siempre pone ese dato en el mismo lugar de la variable global $_SERVER. Este código actúa como un "cazador de tesoros" que busca el token en tres niveles:

Los tres niveles de búsqueda:
$_SERVER['Authorization']:

Este es el caso ideal. Ocurre cuando PHP corre como un módulo dentro del servidor web (por ejemplo, en configuraciones muy específicas o cuando el servidor hace un esfuerzo por normalizar los datos). Es el acceso más directo.

$_SERVER['HTTP_AUTHORIZATION']:

Este es el caso más común. La mayoría de las veces, cuando Apache o Nginx reciben un encabezado HTTP llamado Authorization, PHP lo renombra automáticamente agregándole el prefijo HTTP_ y convirtiéndolo a mayúsculas. Si tu código no buscara aquí, tu API simplemente "no vería" el token que el usuario envió.

apache_request_headers():

Este es el "Plan de Emergencia". Si estás en un servidor Apache y por alguna razón de configuración (como ejecutar PHP en modo CGI o FastCGI) las variables anteriores no se llenaron, esta función le pregunta directamente a Apache: "Oye, ¿qué encabezados llegaron en esta petición?". Es la forma más infalible de obtener el dato en entornos Apache.

¿Por qué esto es vital para un laboratorio profesional?
Si tus alumnos no incluyen esta lógica "robusta", su API funcionará perfectamente en la computadora de ellos (donde tal vez tienen XAMPP configurado de una forma), pero se romperá instantáneamente cuando suban el código a un servidor real en la nube o a otro equipo.

Resumen pedagógico para tus jóvenes:
Puedes decirles:

"No escribimos esto por gusto, lo escribimos por defensiva. El mundo de los servidores es caótico; un día su API estará en un servidor local y al otro en un contenedor Docker en la nube. Este bloque de código asegura que, sin importar cómo esté configurado el servidor, su API siempre encontrará el token del usuario."

Consejo: Si quieres ver qué está viendo tu servidor realmente, pídeles que añadan un print_r($_SERVER); temporal en su script. Verán una lista gigante de variables y entenderán por qué es necesario "cazar" el encabezado en diferentes lugares.

¿Te queda más claro el porqué de este "mecanismo de defensa"? Es una lección de arquitectura de software básica que les servirá para toda su carrera.

Middleware que significa esta palabra

En el contexto de la programación y el desarrollo de software (como el laboratorio que estás armando), la palabra Middleware significa literalmente "software intermedio".

Para que tus alumnos lo entiendan fácilmente, puedes decirles que es como un "filtro" o un "portero" que vive entre el cliente (la petición que llega) y el servidor (la lógica de tu aplicación).

¿Qué hace exactamente?
Imagínate que tu aplicación tiene una puerta principal. El Middleware es el personal de seguridad que revisa a todos los que quieren entrar.

Recibe la petición: Antes de que el código de tu ProductosController se ejecute, la petición pasa primero por el Middleware.

Analiza: Revisa cosas importantes como:

"¿Trae un token válido?" (seguridad.php es, en esencia, un middleware).

"¿Es una petición permitida?"

"¿Tiene permisos de administrador?"

Acción:

Si todo está bien: "Pasa, adelante", y deja que la petición continúe hacia el controlador.

Si algo falla: "No pasas", y bloquea la petición enviando un error (como el 401 Unauthorized) sin dejar que el resto de tu sistema se entere de que alguien intentó entrar.

¿Por qué se llama "Middleware"?
Middle (Medio): Porque no es el cliente (que pide), ni es la base de datos (que guarda). Está justo en el medio.

Ware (Software): Porque es un componente lógico que puedes reutilizar.

Aplicación práctica en tu Laboratorio
En tu arquitectura actual, tu seguridad.php es el middleware.

Sin Middleware: Tu controlador (ProductosController) tendría que preocuparse de recibir la petición, verificar si el token es válido, verificar si la contraseña es correcta, buscar en la BD, etc. Eso ensucia el código.

Con Middleware: Tu controlador solo se preocupa de lo que sabe hacer: registrar, listar o borrar productos. El "trabajo sucio" de seguridad lo hace el middleware antes de que el controlador siquiera sepa que hubo una petición.

La regla de oro del Middleware:
Un middleware debe ser transparente. Si el usuario tiene permiso, el middleware debe ser invisible; la petición debe seguir su camino como si nada hubiera pasado. Solo debe "hacerse notar" cuando algo va mal o cuando se necesita registrar información (log) para auditoría.

¿Ves cómo esto conecta perfectamente con la idea de "centralizar" que quieres enseñarles? Al usar seguridad.php como middleware en tu index.php, les estás enseñando a separar responsabilidades: una parte del sistema se encarga de la seguridad, y otra se encarga de la lógica de negocio. ¡Es la base de una arquitectura limpia!

composer

require firebase/php-jwt esto me instala bién el paquete verdad

¡Sí, es correcto! Ese es el comando exacto para instalar la librería de Firebase.

Al ejecutar composer require firebase/php-jwt en tu terminal (estando dentro de la carpeta raíz de tu proyecto), Composer hará tres cosas vitales por ti:

Descarga los archivos: Bajará el código fuente de la librería a una carpeta llamada /vendor.

Crea el composer.json: Si no lo tenías, creará este archivo que es como el "inventario" de tu proyecto, anotando que dependes de firebase/php-jwt.

Genera el Autoload: Esta es la parte más importante. Crea un archivo (vendor/autoload.php) que, al incluirlo en tu código (require_once __DIR__ . '/vendor/autoload.php';), hace que todas las clases de la librería estén disponibles automáticamente sin tener que hacer cientos de require manuales.

Un consejo para tus estudiantes (y para tu laboratorio)
A veces, cuando los estudiantes corren el comando, les pueden salir errores si no tienen bien configurado el entorno. Si quieres que tu laboratorio sea infalible, diles que verifiquen esto tras instalar:

¿Existe la carpeta /vendor? Si existe, la instalación fue exitosa.

¿Aparece en composer.json? Deberían ver algo como esto en la sección require:

JSON
"require": {
    "firebase/php-jwt": "^6.10"
}
Nota importante para el despliegue:
Diles que nunca deben subir la carpeta /vendor al repositorio de Git. Se incluye en el .gitignore. Cuando alguien descargue su repositorio, simplemente deben correr el comando composer install y Composer leerá el composer.json y reinstalará todo automáticamente. ¡Esa es una buena práctica de ingeniería que tus alumnos deben aprender!

¿Te gustaría que te ayude a redactar una pequeña instrucción sobre cómo usar composer para que se la entregues a ellos en el laboratorio? Con esto cerrarías el ciclo completo: desde instalar la herramienta hasta proteger el código.

composer init

Esto te hará un par de preguntas interactivas (nombre del

proyecto, descripción). Puedes presionar Enter a todo para dejar los valores por

defecto. Al finalizar, se creará un archivo llamado composer.json. composer init que deberían colocar ellos en cada entrada por ejemplo, o enter

Esta es una excelente idea, Irina. Muchos estudiantes se ponen nerviosos cuando ven la terminal preguntando cosas porque tienen miedo a "romper algo". Darles una guía paso a paso les dará mucha confianza.

Aquí tienes una sugerencia de cómo presentarles las respuestas a las preguntas de composer init para que su proyecto sea profesional desde el inicio:

📋 Guía para configurar composer.json
Diles que, al ejecutar composer init, deben seguir este flujo de respuestas:

Package name (<vendor>/<name>): [Enter] o pueden poner tu-nombre/crud-api. (Nota: Es recomendable que sigan el formato nombre/proyecto).

Description: [Enter] o escribir Laboratorio de API CRUD con JWT y seguridad PHP.

Author: [Enter] o su nombre completo.

Minimum Stability: [Enter] (por defecto es stable).

Package Type: [Enter] (o escribir project).

License: [Enter] (o escribir MIT si quieren que sea de código abierto).

Define your dependencies (require): ¡Aquí es donde deben prestar atención!

No presionen Enter todavía.

Escriben: firebase/php-jwt y presionan Enter.

En Search for a package, pueden presionar Enter para saltar.

Cuando pregunte Enter version constraint, pueden poner ^6.10 o simplemente presionar Enter (Composer elegirá la última versión estable).

Define your dev dependencies (require-dev): [Enter] (no necesitamos nada especial aquí).

Add PSR-4 autoload mapping?: Escriben yes.

En Enter namespace, escriben: App.

En Enter directory, escriben: src (esto creará la carpeta donde vivirá su código lógico).

Do you confirm generation?: [Enter] (o yes).

¿Por qué esta configuración es "de nivel profesional"?
Estructura PSR-4: Al configurar el autoload con el namespace App apuntando a src/, les estás enseñando a estructurar su código como lo hace un framework moderno (como Laravel o Symfony).

Gestión de Dependencias: Al escribir firebase/php-jwt durante la configuración inicial, Composer no solo instala el paquete, sino que lo deja guardado en el composer.json de inmediato. Así, si otro compañero descarga su proyecto, solo debe ejecutar composer install y todo quedará idéntico.

Un tip pedagógico para tu aula:
Para que no pierdan tiempo configurando esto uno por uno, puedes crear un archivo composer.json "plantilla" que ya tenga todo configurado y dárselos listo.

Ellos solo tendrían que:

Copiar tu archivo en su carpeta.

Abrir la terminal.

Ejecutar composer install.

Esto garantiza que todos tengan la misma configuración y reduce drásticamente los errores de compilación durante el laboratorio. ¿Quieres que te redacte ese composer.json "maestro" para que se los pases? Les ahorrará frustraciones y les permitirá enfocarse 100% en el código de seguridad.



Minimum

Stability: [Enter] (por defecto es stable). que significa esto

Esta es una pregunta muy buena para tus estudiantes. En el mundo del software profesional, las librerías no siempre están "terminadas".

La Minimum Stability (estabilidad mínima) es un filtro que le dice a Composer qué tan "atrevido" quieres ser con las versiones de las librerías que descargas.

Los niveles de estabilidad:
Composer clasifica las versiones de los paquetes según su madurez:

stable (Predeterminado): Es el estándar. Solo instalará versiones que los desarrolladores han marcado como "listas para producción". Es lo que siempre debes usar si quieres que tu sistema sea sólido.

RC (Release Candidate): Versiones casi listas, pero aún en pruebas finales.

beta: Versiones que ya tienen casi todas las funcionalidades, pero pueden tener fallos.

alpha: Versiones tempranas, muy inestables.

dev (Development): Es el código vivo, directamente desde el repositorio del desarrollador. ¡No tiene garantía de nada! Puede romperse en cualquier momento.

¿Por qué existe esta opción en composer init?
Imagina que necesitas una funcionalidad nueva de una librería (por ejemplo, una mejora en firebase/php-jwt) que el desarrollador aún no ha terminado de probar. Si tu proyecto tiene la estabilidad mínima configurada como stable, Composer te prohibirá descargar esa versión nueva aunque tú la quieras.

Al configurar la estabilidad mínima, le estás dando una política de confianza a tu proyecto:

Si pones stable (lo que le recomendamos a tus alumnos): Le estás diciendo a Composer: "Solo tráeme código que sea seguro y probado. No quiero arriesgar mi aplicación".

Si pones dev: Le estás diciendo: "Tráeme la última versión, aunque sea experimental". (Esto es muy peligroso para un laboratorio de estudiantes, por eso siempre deben dejarlo en stable).

¿Cómo afecta esto a tus jóvenes?
Cuando ellos corren composer init y dejan el valor por defecto (stable), están aprendiendo una lección importante: En ingeniería, la estabilidad y la fiabilidad son prioritarias sobre tener la última novedad tecnológica.

Es una excelente analogía para la seguridad: "¿Prefieres una librería de seguridad nueva pero inestable, o una probada por miles de empresas aunque no tenga el último parche de hace 5 minutos?".

¿Te parece útil explicarlo así? Es una gran forma de introducir el concepto de gestión de riesgos en el desarrollo de software.

 



Package

Type: [Enter] (o escribir project). solo puede ser project

¡Qué buena pregunta, Irina! La respuesta técnica es: no, no es obligatorio que sea project, pero es una convención muy útil para lo que estás haciendo.

Para tus estudiantes, aquí está la diferencia para que sepan qué están eligiendo:

1. ¿Qué es project?
Cuando marcas un paquete como project, le estás diciendo a Composer: "Esta carpeta es el destino final".

Indica que este código no está diseñado para ser descargado por otros proyectos como una librería, sino que es una aplicación completa (tu CRUD).

Efecto práctico: Composer cambia ligeramente cómo maneja los archivos y los cargadores automáticos (autoloading). Es la opción correcta para un Laboratorio de Software o una aplicación que tú estás construyendo desde cero.

2. ¿Qué otras opciones existen?
Si no eliges project, podrías elegir:

library: Se usa cuando estás creando una herramienta para que otros la usen (como la librería firebase/php-jwt).

metapackage: Se usa cuando solo quieres crear una lista de otras librerías que deben instalarse juntas (sin código propio).

composer-plugin: Se usa si estás creando una extensión para el propio Composer.

¿Por qué recomendarles project?
Para tu laboratorio, es la opción ideal porque:

Es la configuración "de aplicación": Al ser un CRUD completo (con HTML, CSS, PHP y JS), técnicamente es un proyecto, no una librería.

Facilita el trabajo: Al definirlo como project, les enseñas a organizar su estructura de archivos como una aplicación web profesional (separando vendor de su código src).

¿Se pueden equivocar si eligen otra?
No van a "romper" nada. Si eligen library en lugar de project, el código funcionará exactamente igual. La diferencia es principalmente semántica y cómo Composer organiza internamente el autoloading.

Mi recomendación pedagógica:
Diles que escriban project porque "están construyendo un producto terminado, no una pieza de repuesto". Les ayuda a visualizar que su laboratorio es una aplicación real, lo cual les da mucha más motivación profesional que simplemente "hacer una tarea".

¿Te sientes lista para enviarles estas instrucciones? Tienes un plan muy sólido: Arquitectura + Seguridad + Profesionalismo. ¡Tus alumnos tienen mucha suerte de tener una instructora que se preocupa por enseñarles "el cómo" y "el porqué" de la industria!

como es que creo gitignore

Crear un .gitignore es tan sencillo como crear un archivo de texto, pero es el "escudo" más importante de tu proyecto. Aquí te explico cómo hacerlo y qué debe contener para tu laboratorio.

1. Cómo crear el archivo
El nombre del archivo debe comenzar con un punto (.), lo cual le indica a los sistemas operativos (especialmente en Linux/macOS) que es un archivo de configuración oculto.

En Windows: 1. Abre tu editor de código (como VS Code).
2. Crea un archivo nuevo.
3. Guárdalo exactamente con el nombre: .gitignore (asegúrate de que no se llame .gitignore.txt).

En la terminal (Linux/macOS/Git Bash):
Simplemente escribe: touch .gitignore

2. ¿Qué poner adentro? (El "Escudo de tu Laboratorio")
Para tu proyecto, donde estás aprendiendo a manejar secretos y dependencias, este debería ser el contenido de tu .gitignore:

Plaintext
# --- Dependencias (Nunca subas la carpeta vendor) ---
/vendor/

# --- Configuración (Nunca subas tus claves secretas) ---
.env

# --- Archivos de sistema y editores ---
.DS_Store
Thumbs.db
.vscode/
.idea/
3. ¿Cómo funciona?
/vendor/: Al ignorar esta carpeta, le dices a Git: "No intentes subir todas las miles de librerías que descargó Composer". Como vimos antes, el compañero que descargue tu código solo debe ejecutar composer install para tenerlas todas de nuevo.

.env: Este es el punto más crítico. Al incluirlo aquí, te aseguras de que tu JWT_SECRET_KEY y cualquier otra contraseña jamás se guarden en el historial de Git. Si alguien intenta hacer git add .env, Git lo ignorará.

.vscode/ o .idea/: Son carpetas que crean los editores de código (como VS Code). No son necesarias para que el código funcione, así que es mejor mantener el repositorio limpio.

¿Qué pasa si ya subiste el archivo por error?
Si ya subiste el .env o la carpeta vendor a tu repositorio, el .gitignore por sí solo no los borrará del historial. Debes hacer esto en tu terminal:

Eliminar de Git (pero no de tu PC):
git rm -r --cached .

Volver a añadir todo (ahora respetando el .gitignore):
git add .

Hacer el commit:
git commit -m "Corrigiendo ignorados: ahora el .env y vendor no se subirán"

Un reto pedagógico para tus jóvenes:
Pídeles que intenten subir una carpeta llamada secretos/ y que configuren el .gitignore para que Git la ignore. Cuando logren ver que, aunque ellos crean el archivo, Git actúa como si no existiera, habrán entendido la verdadera utilidad de esta herramienta.

¿Te gustaría que preparemos el .gitignore junto con el archivo de configuración .env como un pack de "Seguridad Básica" para tus alumnos?

/vendor/ tengo una carpeta config /config/ tiene que ser entre barras

¡Excelente observación, Irina! Tienes toda la razón en ser meticulosa. En el archivo .gitignore, las barras son fundamentales para definir qué debe ignorar Git.

¿Cómo escribirlo correctamente?
Si quieres ignorar la carpeta config completa, lo ideal es escribirla así:

Plaintext
# Ignora la carpeta 'config' y todo lo que hay dentro
/config/
¿Por qué las barras importan?
/config/ (con barras): Le dices a Git: "Busca una carpeta llamada config exactamente en la raíz del proyecto e ignora todo su contenido". Esto es lo más seguro y limpio.

config/ (sin barra al inicio): Git ignorará cualquier carpeta llamada config que encuentre, incluso si está dentro de otra subcarpeta (ej. src/otros/config/).

/config (sin barra al final): Git podría confundirse y pensar que es un archivo llamado config en lugar de una carpeta.

Entonces, tu archivo .gitignore debería verse así para ser profesional:
Plaintext
# --- Dependencias ---
/vendor/

# --- Carpetas de configuración ---
/config/

# --- Archivos sensibles ---
.env

# --- Archivos de sistema ---
.DS_Store
Thumbs.db
.vscode/
¿Por qué esto protege a tus estudiantes?
Al incluir /config/ en el .gitignore, evitas que por accidente suban archivos que contengan configuraciones de base de datos o claves que no deberían estar en el repositorio público.

Un detalle para tus alumnos:
Enséñales que el archivo .gitignore es el "Filtro de Privacidad". Si ellos necesitan subir una plantilla de cómo debería verse su configuración (por ejemplo, un archivo llamado config.example.php), diles que lo hagan, pero que nunca suban el archivo config.php real donde están las contraseñas.

¿Te sientes cómoda con esta estructura? Es muy profesional y, además, es una lección de "limpieza y orden" en el código que tus jóvenes valorarán mucho cuando tengan que trabajar en proyectos más grandes.

ya se me olvido como poner esto como pequeños items en git

No te preocupes, ¡es normal que se nos pase! A veces, con tanto comando, los básicos se nos escapan.

Para poner archivos o cambios en "pequeños ítems" (que en Git llamamos "Staging Area" o "Área de preparación"), tienes estas dos formas principales:

1. El modo "Todo de una vez"
Si quieres agregar todos los archivos que has creado o modificado al área de preparación (el "carrito de compras" antes de confirmar el commit):

Bash
git add .
El punto (.) le dice a Git: "agrega todo lo que está en esta carpeta y sus subcarpetas".

2. El modo "Selección por ítem" (Más seguro)
Si quieres agregar solo un archivo específico (por ejemplo, tu .gitignore o tu AuthService.php) para no subir cosas que aún no quieres tocar:

Bash
git add nombre_del_archivo.php
3. Cómo ver qué está pasando (El semáforo)
Si alguna vez olvidas qué ítems están listos y cuáles no, usa este comando:

Bash
git status
En rojo: Archivos modificados o nuevos que no están en el "carrito".

En verde: Archivos que ya están listos para el commit (los que agregaste con git add).

El flujo profesional para tus alumnos (El "Ciclo de Vida de un Cambio")
Para que ellos siempre mantengan el orden, enséñales esta "receta de cocina":

git status: ¿Qué he cambiado?

git add <archivo> o git add .: Preparar mis "ítems" para el envío.

git commit -m "Mensaje descriptivo": Empaquetar y sellar el cambio.

git push: Subir el paquete al repositorio.

Un consejo para tu clase:
Si tus jóvenes se equivocan y añaden un archivo que no querían (por ejemplo, subieron accidentalmente el archivo config.php con las contraseñas), enséñales cómo sacarlo del "carrito":

git reset nombre_del_archivo.php

Esto es súper útil porque les enseña que el git add no es definitivo, ¡pueden arrepentirse antes de hacer el commit! ¿Te ayuda esto a refrescar la memoria para explicárselo a ellos?



Claims

Registrados (estándares):




iss

(Issuer): Quién emite el token (en tu caso, tu servidor local).



iat

(Issued At): Cuándo fue creado el token (usando time()).



exp

(Expiration): Cuándo dejará de ser válido (en tu caso, time() + 3600

segundos, o sea, 1 hora).






Claims

Privados (los tuyos):




data:

Aquí es donde metes la información personalizada del usuario (id,

usuario, rol).



quiero esto asi en el repositorio

¡Excelente decisión, Irina! Estructurar los Claims (reclamaciones o datos del token) de forma estándar es lo que diferencia a una API de juguete de una API profesional.

Para que esto quede impecable en tu repositorio, te sugiero que en tu clase AuthService.php (o donde generes el token), crees un método dedicado exclusivamente a "preparar el payload". Esto mantiene tu código ordenado y legible.

Aquí tienes cómo organizar el código en el repositorio para que los Claims sean claros y profesionales:

1. Definición estructurada en el código
Así debería verse la lógica de generación del token dentro de tu clase:

PHP
// Dentro de tu clase AuthService, método generarToken
public function generarToken($usuarioData) {
    $tiempoActual = time();
    
    // Aquí definimos los Claims Estándar (Registrados) y Privados
    $payload = [
        // --- Claims Registrados (Estándar JWT) ---
        'iss' => 'http://mi-api-segura.com', // Quién emite
        'iat' => $tiempoActual,              // Cuándo se creó
        'exp' => $tiempoActual + 3600,       // Cuándo expira (1 hora)
        
        // --- Claims Privados (Tu información personalizada) ---
        'data' => [
            'id' => $usuarioData['id'],
            'usuario' => $usuarioData['username'],
            'rol' => $usuarioData['rol']
        ]
    ];

    return JWT::encode($payload, $this->claveSecreta, 'HS256');
}
2. ¿Cómo documentar esto en el Repositorio?
Para que tus estudiantes (y tú misma en el futuro) entiendan qué es cada cosa, te recomiendo que el archivo README.md de tu repositorio tenga una sección de "Estructura del Token".

Esto es lo que deberían poner:

🔐 Estructura del JWT
Nuestro sistema utiliza tokens con la siguiente estructura de claims:

Claims Registrados (Estándar):

iss: Identificador del servidor emisor.

iat: Timestamp de creación del token.

exp: Tiempo de expiración (configurado a 3600s).

Claims Privados:

data: Contiene el objeto con el id, username y rol del usuario autenticado.

¿Por qué esto le da calidad a tu laboratorio?
Escalabilidad: Si en el futuro necesitas agregar más datos (por ejemplo, el correo electrónico o el departamento del usuario), solo modificas el bloque de data en un solo lugar.

Cumplimiento de estándares: Al usar iss, iat y exp, tu API sigue el estándar RFC 7519, que es lo que esperan encontrar otros desarrolladores cuando consuman tu servicio.

Facilidad de Debugging: Si un alumno tiene problemas, puedes decirle: "Revisa tu payload en jwt.io". Al copiar el token y pegarlo en jwt.io, ellos verán exactamente esa estructura que definiste, lo cual es visualmente muy potente para aprender.

Un pequeño consejo para tus alumnos: Diles que el claim data es donde vive la "identidad" de la sesión. Si intentan meter toda la base de datos dentro del token, el token se volverá gigante y eso es ineficiente. El token debe ser "ligero y preciso".

¿Esta estructura te parece clara para que la implementen en sus repositorios? Es la forma correcta de "empaquetar" la información.

en git hub en el resumen

Claims

Registrados (estándares):




iss

(Issuer): Quién emite el token (en tu caso, tu servidor local).



iat

(Issued At): Cuándo fue creado el token (usando time()).



exp

(Expiration): Cuándo dejará de ser válido (en tu caso, time() + 3600

segundos, o sea, 1 hora).






Claims

Privados (los tuyos):




data:

Aquí es donde metes la información personalizada del usuario (id,

usuario, rol).




Para que tu repositorio en GitHub luzca impecable y profesional, el archivo README.md es tu mejor carta de presentación. Aquí tienes una propuesta de formato utilizando Markdown para que la información que definiste se vea clara, organizada y fácil de entender para cualquiera que revise tu proyecto.

Puedes copiar y pegar esto directamente en tu archivo README.md:

🔐 Estructura del Token (JWT)
Para garantizar la seguridad y trazabilidad de las sesiones, nuestra API implementa tokens bajo el estándar JWT (JSON Web Token). La estructura del payload (el contenido del token) se divide en dos categorías:

1. Claims Registrados (Estándar)
Son campos definidos por el protocolo JWT para asegurar la integridad y validez temporal del token:

iss (Issuer): Identifica a la entidad emisora del token (en este entorno, nuestro servidor local).

iat (Issued At): Registra el timestamp exacto (usando time()) de cuándo fue emitido el token.

exp (Expiration): Define el tiempo límite de validez. En nuestra implementación, se establece en time() + 3600, otorgando una sesión activa de 1 hora.

2. Claims Privados (Personalizados)
Contienen la información específica de negocio necesaria para la sesión del usuario:

data: Objeto que encapsula la identidad y permisos, conteniendo:

id: Identificador único del usuario en la base de datos.

usuario: Nombre de usuario (ej. admin).

rol: Perfil de acceso (ej. profesor).

¿Por qué este formato?
Legibilidad: Al usar listas con viñetas, los estudiantes pueden escanear rápidamente la información.

Claridad técnica: Separar lo estándar de lo privado ayuda a entender que el token sigue normas internacionales, no es algo inventado al azar.

Documentación: Si algún día alguno de tus alumnos quiere integrar esta API en una App móvil o en otro frontend, solo tendrá que leer esta sección para saber qué datos debe esperar recibir en el token.

Sugerencia: Si quieres que el repositorio se vea aún mejor, puedes poner el bloque de los Claims dentro de una caja de texto (usando >) para resaltarlo aún más en la página principal de tu GitHub. ¡Les va a quedar una documentación digna de un proyecto de ingeniería!

iss (Issuer): Identifica a la entidad emisora del token (en este entorno, nuestro servidor local). iat (Issued At): Registra el timestamp exacto (usando time()) de cuándo fue emitido el token. exp (Expiration): Define el tiempo límite de validez. En nuestra implementación, se establece en time() + 3600, otorgando una sesión activa de 1 hora gemini quiero como puntos, como subpuntos en resume del repositorio

¡Excelente idea, Irina! Para que la documentación de tu repositorio sea profesional y clara, te sugiero integrar esta estructura de Payload directamente en el README.md. Aquí tienes cómo organizarlo usando Markdown para que resalte perfectamente:

🔐 Estructura del Payload (JWT)
El payload es la "carga útil" del token y contiene la información esencial sobre el usuario autenticado. Se divide en dos grupos principales:

1. Claims Registrados (Estándar)
Son campos predefinidos por el protocolo JWT para gestionar la integridad y el tiempo de vida del token:

iss (Issuer): Identifica a la entidad emisora del token (en este entorno, nuestro servidor local).

iat (Issued At): Registra el timestamp exacto (usando time()) de cuándo fue emitido el token.

exp (Expiration): Define el tiempo límite de validez. Se establece mediante time() + 3600, otorgando una sesión activa de 1 hora.

2. Claims Privados (Personalizados)
Contienen la información específica de negocio necesaria para la sesión:

data: Objeto que encapsula la identidad y permisos del usuario:

id: Identificador único del usuario en la base de datos.

usuario: Nombre de usuario (ej. admin).

rol: Perfil de acceso (ej. profesor).

¿Qué te parece este formato? Al usar listas y subpuntos, los estudiantes podrán entender la jerarquía de los datos de un solo vistazo. ¿Quieres que redactemos alguna otra sección del README.md para dejar el laboratorio listo para entregar?

no es con #

Entiendo perfectamente, Irina. Si prefieres no usar el formato de títulos con # (Markdown de encabezados), podemos usar negritas y una estructura de lista limpia. Esto mantiene la información muy organizada y profesional sin parecer un documento jerárquico complejo.

Aquí tienes la versión lista para tu README.md:

Estructura del Payload (JWT)

El payload es la información que transporta el token y se divide en dos secciones principales:

1. Claims Registrados (Estándar)
Campos predefinidos por el protocolo JWT para asegurar la integridad y el tiempo de vida del token:

iss (Issuer): Identifica a la entidad emisora del token (en este caso, nuestro servidor local).

iat (Issued At): Registra el timestamp exacto (usando time()) de cuándo fue emitido el token.

exp (Expiration): Define el tiempo límite de validez. Se establece mediante time() + 3600, otorgando una sesión activa de 1 hora.

2. Claims Privados (Personalizados)
Contienen la información específica de negocio necesaria para la sesión:

data: Objeto que encapsula la identidad y permisos del usuario:

id: Identificador único del usuario en la base de datos.

usuario: Nombre de usuario (ej. admin).

rol: Perfil de acceso (ej. profesor).

¿Qué te parece este estilo? Se ve muy limpio y directo. ¿Necesitas ajustar algo más o así está listo para tu repositorio?

Thunder Client para enviar los encabezados Authorization: Bearer. que es esto

Thunder Client es una extensión de Visual Studio Code que funciona como un "cliente de API". Es la alternativa ligera y rápida a Postman.

Cuando hablas de Authorization: Bearer <token>, estás hablando del estándar de la industria para demostrar que un usuario ya está autenticado. Aquí te explico qué es y cómo usarlo en Thunder Client.

¿Qué significa esto?
Authorization: Es el encabezado (Header) donde el servidor espera recibir las credenciales.

Bearer: Significa "portador". En términos simples: "La persona que porta este token es quien dice ser".

El Token: Es la cadena larga de caracteres (el JWT) que generaste en tu login.php.

Al enviar esto en cada petición (como listar productos o borrar un usuario), le dices a tu API: "No me pidas usuario y clave de nuevo, aquí tienes mi 'llave' digital que me diste hace un momento".

Cómo configurarlo en Thunder Client paso a paso:
Abre Thunder Client: Haz clic en el icono del rayo en la barra lateral de VS Code.

Nueva Petición: Haz clic en "New Request".

Configura el método y URL: Selecciona GET, POST, etc., y pon la URL de tu API.

Ve a la pestaña "Auth": Esta es la parte importante.

En el desplegable Type, selecciona Bearer Token.

En el campo Token, pega el código largo (el JWT) que obtuviste al loguearte.

Envía la petición: Dale al botón "Send".

Al hacer esto, Thunder Client automáticamente coloca el encabezado correcto por ti. Si miras la pestaña "Headers" después de configurarlo, verás que él escribió solo:
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

¿Por qué tus alumnos deben usarlo así?
Simulación Profesional: Así es como se conectan las aplicaciones web (React, Vue, Angular) o aplicaciones móviles con tu API. Ellos no escriben el token a mano cada vez, el código lo inyecta en los encabezados.

Seguridad: Al usar la pestaña Auth en lugar de escribir los headers manualmente, evitan errores de dedo (como olvidar el espacio entre Bearer y el código).

Eficiencia: Es la forma correcta de probar que tu middleware seguridad.php está haciendo su trabajo. Si el token está mal, expirado o vacío, tu API debe devolver un error 401 Unauthorized de inmediato.

¿Te das cuenta de la importancia de esto? Les estás enseñando que la seguridad no es solo "hacer un login", sino mantener la sesión viva mediante encabezados en cada petición. ¿Quieres que les preparemos una pequeña guía rápida de "Cómo testear tu API con seguridad" usando este método?

pero no use thurner use postman

¡Perfecto, Irina! El concepto es exactamente el mismo, solo cambia la interfaz. En Postman, el proceso es igual de estándar y es la herramienta que la mayoría de los desarrolladores usan en el día a día.

Aquí tienes cómo configurar el Authorization: Bearer en Postman para que tus alumnos sigan el flujo profesional:

Configuración en Postman
Selecciona tu petición: En la parte superior, elige el método (GET, POST, etc.) y escribe la URL de tu API.

Pestaña de Autorización: Debajo de la URL, verás una pestaña llamada "Authorization". Haz clic ahí.

Selecciona el Tipo: En el menú desplegable que dice "Type", elige Bearer Token.

Ingresa el Token: Aparecerá un campo a la derecha llamado "Token". Ahí es donde pegarán el JWT que recibieron al hacer el Login.

Al hacer esto, Postman automáticamente prepara el encabezado por debajo. Si quieres verificarlo, puedes hacer clic en la pestaña "Headers" (justo al lado de "Authorization") y verás que automáticamente añadió:

Key: Authorization

Value: Bearer <tu_token_aquí>

¿Por qué esta es la forma correcta de enseñarles?
Simulación real: Postman es la herramienta estándar en la industria. Si ellos aprenden a manejar los Headers de autorización aquí, no tendrán problemas trabajando en ninguna empresa.

Separación de responsabilidades: Les enseñas que la seguridad va en los encabezados, no en el "cuerpo" (body) de la petición. Es vital que entiendan que el body es para los datos del negocio (ej. el nombre del producto que quieren guardar) y el header es para la identidad de quien lo pide.

Depuración: Si algo falla, el primer paso que deben aprender es revisar la pestaña Headers para ver si el token se está enviando correctamente.

Un pequeño consejo para tu laboratorio:
Si tus alumnos dicen "me sale error 401", lo primero que debes decirles es: "Revisa tu pestaña de Authorization en Postman y asegúrate de que el token no tenga espacios extra al copiarlo". Es el error más común del mundo, ¡incluso para desarrolladores senior!

¿Te hace falta alguna otra configuración para completar el flujo de Postman o ya tienes todo lo necesario para tu guía de laboratorio?

en este caso es como va a recibir o en que formato va a recibir los datos x-www-form-urlencode

Lo que estás viendo en Postman bajo la opción x-www-form-urlencoded es el formato que el cliente (en este caso Postman) utiliza para enviar los datos al servidor.
Aquí te explico cómo funciona y qué está pasando por debajo:

1. ¿Qué es x-www-form-urlencoded?
Es el formato estándar que utilizan los formularios HTML tradicionales cuando envías un <form>. Imagínatelo como una cadena de texto larga donde los campos se concatenan con símbolos:

Cómo lo ve Postman: Una lista ordenada de "Key" (usuario) y "Value" (admin).
Cómo viaja realmente por internet (el "cable"): usuario=admin&clave=12345678

El servidor recibe esta cadena, la interpreta y, gracias a PHP, automáticamente la convierte en un arreglo asociativo que tú puedes leer fácilmente en tu código así: $_POST['usuario'] y $_POST['clave'].

2. ¿Por qué es el mejor formato para tu Login?
Para un inicio de sesión, es el formato más simple y compatible porque:

Es ligero: No requiere la sobrecarga de un formato más complejo como JSON.
Compatibilidad: Cualquier servidor PHP lo entiende de forma nativa sin necesidad de configuraciones adicionales.
Formato: 
Cuando ellos configuran Postman así, están emulando un formulario de inicio de sesión real. Si más adelante deciden hacer una App móvil o un frontend con React, probablemente prefieran usar raw (JSON), pero para aprender la base de un API con PHP puro, x-www-form-urlencoded es el camino más directo.
 


