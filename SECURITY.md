# Reporte de Seguridad de la Aplicación

Este documento detalla las medidas de seguridad y buenas prácticas implementadas en el sistema para garantizar la integridad, confidencialidad y disponibilidad de la información, previniendo los vectores de ataque más comunes en aplicaciones web.

## 1. Múltiples Factores de Autenticación (MFA)
El sistema emplea un esquema de seguridad escalonado basado en roles:
- **1FA (Nivel Base):** Contraseñas robustas obligatorias (mínimo 12 caracteres, mayúsculas, números, símbolos). Se utiliza la regla `uncompromised()` de Laravel para rechazar contraseñas que hayan aparecido en filtraciones de datos conocidas (Have I Been Pwned).
- **2FA (Nivel Usuario):** Autenticación de Dos Factores mediante One-Time Password (OTP) enviado por correo electrónico. El código expira en 10 minutos y tiene protección de un solo uso.
- **3FA (Nivel Administrador):** Autenticación de Tres Factores mediante TOTP (Google Authenticator) obligatorio para cuentas con privilegios administrativos, añadiendo una capa criptográfica basada en el tiempo.

## 2. Prevención de Ataques de Fuerza Bruta (Rate Limiting)
Se implementó *Throttling* estricto para mitigar ataques de fuerza bruta y *Credential Stuffing*:
- **Login:** Bloqueo temporal de la IP y correo tras 5 intentos fallidos por minuto.
- **Registro:** Límite de 3 cuentas nuevas por minuto por IP para evitar *Spam* o denegación de servicio (DoS) a la base de datos.
- **OTP / TOTP:** Límite de 5 intentos por minuto para la verificación de los códigos de seguridad.

## 3. Seguridad de Sesión y Cookies
- **Limpieza Rigurosa:** Al cerrar sesión, la aplicación destruye inmediatamente la sesión en el backend, regenera el token CSRF, limpia el `localStorage` / `sessionStorage` mediante Alpine.js, y fuerza el borrado manual de las cookies (`acceso_seguro_session` y `XSRF-TOKEN`).
- **Session Fixation:** Cada vez que el usuario inicia sesión, el ID de sesión se regenera automáticamente (`$request->session()->regenerate()`), anulando ataques de "Fijación de Sesión".
- **Atributos de Cookies:** Las cookies de sesión están marcadas como `HTTP Only`, impidiendo que scripts maliciosos (XSS) puedan acceder a ellas a través de JavaScript.
- **Inactividad (Timeout):** El tiempo de vida de la sesión (Lifetime) está configurado restrictivamente en las variables de entorno, desconectando a los usuarios inactivos. Adicionalmente, el parámetro `expire_on_close` asegura que la sesión se destruya al cerrar el navegador.

## 4. Cabeceras de Seguridad HTTP (Security Headers)
Se implementó un Middleware global (`SecurityHeaders.php`) que inyecta protecciones críticas a nivel de navegador:
- **Strict-Transport-Security (HSTS):** Obliga al navegador a comunicarse exclusivamente a través de HTTPS durante 1 año, bloqueando ataques *Man-in-the-Middle*.
- **Content-Security-Policy (CSP):** Restringe desde dónde se pueden cargar recursos y ejecutar scripts, siendo la defensa primaria contra ataques XSS.
- **X-Frame-Options (SAMEORIGIN):** Prohíbe que el sitio sea incrustado en `<iframe>` de dominios externos, mitigando ataques de *Clickjacking*.
- **X-Content-Type-Options (nosniff):** Evita que el navegador "adivine" los tipos MIME, previniendo ejecuciones accidentales de archivos camuflados.
- **Referrer-Policy:** Restringe qué información se envía en la cabecera `Referer` al navegar a otros sitios.

## 5. Prevención de XSS e Inyección SQL
- **Sanitización de Salida:** El motor de plantillas Blade (`{{ $variable }}`) escapa automáticamente todos los datos mostrados convirtiéndolos en entidades HTML seguras (`htmlspecialchars`).
- **Sanitización de Entrada:** Uso de validadores estrictos y funciones como `strip_tags()` antes de guardar información susceptible en la base de datos (como el nombre).
- **Inyección SQL:** Todo acceso a la base de datos se realiza a través de Eloquent ORM y Query Builder, los cuales utilizan *PDO Parameter Binding*, imposibilitando la ejecución de comandos SQL inyectados.

## 6. Prevención de CSRF (Cross-Site Request Forgery)
Todas las peticiones mutables (`POST`, `PUT`, `DELETE`) están protegidas mediante validación de un token CSRF criptográfico, garantizando que la petición proviene de un formulario legítimo de la aplicación y no de un sitio de terceros malintencionado.

## 7. Logs de Auditoría Trazables
La aplicación mantiene un registro inmutable de la actividad crítica de los usuarios.
- Captura de Dirección IP.
- Rol del usuario.
- Metadatos del evento en formato JSON.
- Estados de autenticación.
Esto facilita el análisis forense en caso de que ocurra un incidente de seguridad, cumpliendo con principios de rendición de cuentas (Accountability).

## 8. Prevención de Múltiples Peticiones (UX/Seguridad)
Se implementó una solución global mediante JavaScript que deshabilita los botones de envío (`submit`) en toda la aplicación (Registro, Login, Formularios de OTP/TOTP) después del primer clic, previniendo errores humanos de "doble envío" que agotan prematuramente el límite del *Rate Limiter*.
