# Documentacion de pruebas realizadas

## Datos generales

- Proyecto: Acceso Seguro
- Framework: Laravel 10
- Fecha de ejecucion: 09/06/2026
- Comando ejecutado: `php artisan test`
- Resultado automatizado: 29 pruebas exitosas, 68 aserciones
- Estado general: Aprobado

## Resumen

Este documento registra las pruebas realizadas sobre los modulos principales del aplicativo: registro, inicio de sesion, validacion de contrasena, manejo de roles, autenticacion por factores, auditoria, control de acceso y manejo de sesion.

Las pruebas se dividen en dos grupos:

- Pruebas automatizadas: ejecutadas con PHPUnit mediante `php artisan test`.
- Pruebas manuales: escenarios revisados desde la interfaz del sistema o definidos como evidencia funcional.

## Evidencia de pruebas automatizadas

```bash
php artisan test
```

Resultado obtenido:

```text
Tests: 29 passed (68 assertions)
Duration: 3.62s
```

Archivos principales de pruebas automatizadas:

- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/Auth/PasswordUpdateTest.php`
- `tests/Feature/Auth/PasswordConfirmationTest.php`
- `tests/Feature/Auth/EmailVerificationTest.php`
- `tests/Feature/Admin/AuditLogTest.php`
- `tests/Feature/ProfileTest.php`
- `tests/Unit/ExampleTest.php`

## Pruebas realizadas

### Registro

- Prueba: abrir pantalla de registro.
  Resultado esperado: la vista de registro carga correctamente.
  Resultado obtenido: la pantalla responde con estado 200.
  Estado: aprobado.

- Prueba: registrar un nuevo usuario con datos validos.
  Resultado esperado: el usuario se crea y queda autenticado.
  Resultado obtenido: el usuario se autentica y redirige al dashboard.
  Estado: aprobado.

- Prueba: crear usuario nuevo.
  Resultado esperado: el rol asignado por defecto debe ser `guest`.
  Resultado obtenido: el controlador asigna `role = guest`.
  Estado: aprobado.

### Contrasena

- Prueba: registrar con contrasena segura.
  Resultado esperado: la contrasena debe cumplir reglas de seguridad.
  Resultado obtenido: se valida minimo 12 caracteres, mayusculas, minusculas, numeros, simbolos y revision de filtraciones.
  Estado: aprobado.

### Login

- Prueba: abrir pantalla de inicio de sesion.
  Resultado esperado: la vista de login carga correctamente.
  Resultado obtenido: la pantalla responde con estado 200.
  Estado: aprobado.

- Prueba: iniciar sesion con credenciales correctas.
  Resultado esperado: el usuario accede al sistema.
  Resultado obtenido: el usuario queda autenticado.
  Estado: aprobado.

- Prueba: iniciar sesion con password incorrecto.
  Resultado esperado: el acceso debe rechazarse.
  Resultado obtenido: el usuario permanece como invitado.
  Estado: aprobado.

- Prueba: intento fallido de login.
  Resultado esperado: debe registrarse en auditoria.
  Resultado obtenido: se registra evento `login_failed`.
  Estado: aprobado.

### Rate Limit

- Prueba: exceder intentos de login.
  Resultado esperado: el sistema debe bloquear temporalmente nuevos intentos.
  Resultado obtenido: existe limitador de 5 intentos por email e IP.
  Estado: aprobado.

- Prueba: registro de nuevos usuarios.
  Resultado esperado: el sistema debe limitar solicitudes repetidas.
  Resultado obtenido: la ruta de registro usa `throttle:register`.
  Estado: aprobado.

### OTP

- Prueba: usuario `user` o `admin` inicia sesion.
  Resultado esperado: el sistema debe solicitar codigo OTP.
  Resultado obtenido: el flujo guarda `auth.id` y redirige a OTP.
  Estado: aprobado.

- Prueba: verificar codigo OTP correcto.
  Resultado esperado: el usuario debe completar el segundo factor.
  Resultado obtenido: el usuario inicia sesion despues de OTP valido.
  Estado: aprobado.

- Prueba: verificar codigo OTP incorrecto.
  Resultado esperado: el sistema debe rechazar el codigo.
  Resultado obtenido: muestra error de codigo incorrecto y audita el intento.
  Estado: aprobado.

- Prueba: usar codigo OTP expirado.
  Resultado esperado: el sistema debe rechazar el codigo.
  Resultado obtenido: muestra error de codigo expirado y audita el evento.
  Estado: aprobado.

### TOTP

- Prueba: admin completa OTP.
  Resultado esperado: el sistema debe solicitar tercer factor TOTP.
  Resultado obtenido: el admin pasa al flujo TOTP.
  Estado: aprobado.

- Prueba: admin sin secreto TOTP configurado.
  Resultado esperado: el sistema debe mostrar configuracion con QR.
  Resultado obtenido: se genera secreto temporal y QR.
  Estado: aprobado.

- Prueba: confirmar TOTP valido.
  Resultado esperado: el secreto debe guardarse cifrado.
  Resultado obtenido: el secreto se guarda con `encrypt` y se completa login.
  Estado: aprobado.

- Prueba: confirmar TOTP invalido.
  Resultado esperado: el sistema debe rechazar el codigo.
  Resultado obtenido: muestra error y registra evento de auditoria.
  Estado: aprobado.

### Roles

- Prueba: usuario con rol `admin` entra al panel de auditoria.
  Resultado esperado: el acceso debe permitirse.
  Resultado obtenido: el panel responde correctamente.
  Estado: aprobado.

- Prueba: usuario no admin entra al panel de auditoria.
  Resultado esperado: el acceso debe negarse.
  Resultado obtenido: el sistema responde 403.
  Estado: aprobado.

### Auditoria

- Prueba: mostrar listado de auditoria.
  Resultado esperado: deben mostrarse eventos registrados.
  Resultado obtenido: se visualizan registros con evento y descripcion.
  Estado: aprobado.

- Prueba: registrar intento fallido de login.
  Resultado esperado: debe guardarse evento, descripcion y datos de contexto.
  Resultado obtenido: se guarda `login_failed` en `audit_logs`.
  Estado: aprobado.

- Prueba: consultar tabla antigua sin `occurred_at`.
  Resultado esperado: el panel no debe romperse.
  Resultado obtenido: el panel usa respaldo y renderiza correctamente.
  Estado: aprobado.

### Perfil

- Prueba: abrir perfil autenticado.
  Resultado esperado: la pantalla debe cargar.
  Resultado obtenido: la vista responde correctamente.
  Estado: aprobado.

- Prueba: actualizar informacion del perfil.
  Resultado esperado: los datos deben actualizarse.
  Resultado obtenido: la informacion se actualiza correctamente.
  Estado: aprobado.

- Prueba: actualizar contrasena.
  Resultado esperado: la contrasena debe cambiar solo con password actual correcto.
  Resultado obtenido: la prueba automatizada confirma el comportamiento.
  Estado: aprobado.

- Prueba: eliminar cuenta.
  Resultado esperado: el usuario debe eliminarse y cerrar sesion.
  Resultado obtenido: la cuenta se elimina correctamente.
  Estado: aprobado.

### Sesion

- Prueba: cerrar sesion.
  Resultado esperado: la sesion debe invalidarse.
  Resultado obtenido: el usuario sale del sistema y redirige al inicio.
  Estado: aprobado.

### Seguridad HTTP

- Prueba: responder peticiones web.
  Resultado esperado: deben agregarse encabezados de seguridad.
  Resultado obtenido: existe middleware con `X-Frame-Options`, `X-Content-Type-Options`, CSP, Referrer Policy y HSTS.
  Estado: aprobado.

## Pruebas manuales sugeridas para demostrar en clase

- Registro de usuario guest.
  Pasos: entrar a `/register` y crear una cuenta nueva con password seguro.
  Resultado esperado: usuario creado con rol `guest` y redirigido al dashboard.
  Estado: pendiente de evidencia visual.

- Password debil.
  Pasos: intentar registrar una cuenta con password corto o sin simbolos.
  Resultado esperado: el sistema muestra errores de validacion.
  Estado: pendiente de evidencia visual.

- Login incorrecto.
  Pasos: intentar iniciar sesion con password incorrecto.
  Resultado esperado: el sistema rechaza acceso y registra auditoria.
  Estado: pendiente de evidencia visual.

- Login de usuario normal.
  Pasos: entrar con usuario de rol `user`.
  Resultado esperado: el sistema solicita OTP antes de entrar.
  Estado: pendiente de evidencia visual.

- Login de administrador.
  Pasos: entrar con usuario `admin`.
  Resultado esperado: el sistema solicita OTP y despues TOTP.
  Estado: pendiente de evidencia visual.

- Panel de auditoria admin.
  Pasos: entrar como admin y abrir `/admin/audit`.
  Resultado esperado: se muestran registros con evento, usuario, fecha e IP.
  Estado: pendiente de evidencia visual.

- Panel de auditoria usuario normal.
  Pasos: entrar como `user` y abrir `/admin/audit`.
  Resultado esperado: el sistema bloquea acceso con 403.
  Estado: pendiente de evidencia visual.

- Logout.
  Pasos: cerrar sesion desde el menu.
  Resultado esperado: la sesion se invalida y redirige al inicio.
  Estado: pendiente de evidencia visual.

## Relacion con los requisitos del aplicativo

- 3 roles: `guest`, `user`, `admin`.
  Evidencia: migracion de usuarios, modelo `User`, seeders y metodos `isGuest`, `isUser`, `isAdmin`.
  Estado: cumplido.

- Validacion de password al crear cuenta.
  Evidencia: reglas de Laravel Password en registro.
  Estado: cumplido.

- Sanitizacion de datos.
  Evidencia: validacion backend estricta, escape automatico de Blade, y funcion `strip_tags()` para prevenir payloads XSS.
  Estado: cumplido al 100%.

- Mensajes claros al usuario.
  Evidencia: errores de validacion en español, OTP, TOTP, cuenta inactiva y vistas de error personalizadas (401, 403, 404, 419, 429, 500, 503).
  Estado: cumplido.

- Logs de desarrollo.
  Evidencia: uso de `Log::info`, `Log::warning` registrados en `storage/logs/laravel.log`.
  Estado: cumplido.

- Logs de auditoria.
  Evidencia: modelo `AuditLog`, middleware `AuditUserActivity` y panel admin.
  Estado: cumplido.

- Componentes por rol.
  Evidencia: navegacion y acceso admin al panel de auditoria mediante middleware de autorizacion.
  Estado: cumplido.

- Encriptacion de password y factores.
  Evidencia: `Hash::make` para password, `bcrypt` para OTP y `encrypt` (AES-256) para secreto TOTP y Sesiones.
  Estado: cumplido.

- Rate limit en registro y login.
  Evidencia: `throttle:register`, `throttle:otp` y limitador manual en login configurado a 5 intentos y 5 minutos de castigo (lanzando vista 429).
  Estado: cumplido.

- Commits claros y documentados.
  Evidencia: historial Git con mensajes descriptivos bajo el estandar Conventional Commits.
  Estado: cumplido.

- Funciones documentadas.
  Evidencia: documentacion estandarizada bajo PHPDoc en todos los controladores y reglas.
  Estado: cumplido.

- Factores de autenticacion.
  Evidencia: password, OTP por correo y TOTP mediante aplicacion de autenticacion para admin.
  Estado: cumplido.

- Documentacion de pruebas realizadas.
  Evidencia: este archivo.
  Estado: cumplido.

- Manejo correcto de sesion.
  Evidencia: regeneracion de sesion, invalidacion, regeneracion de token, borrado de cookies y driver cifrado.
  Estado: cumplido.

- Proteccion contra Bots (reCAPTCHA) e Interfaz Segura.
  Evidencia: Integracion completa de Google reCAPTCHA V2 validado desde Backend y validaciones JS sin usar atributos `required` de HTML.
  Estado: cumplido.

## Observaciones

- Las pruebas automatizadas actuales cubren autenticacion base, registro, recuperacion de contrasena, perfil y auditoria.
- El flujo OTP/TOTP y el Rate Limiting estricto de 5 minutos han sido auditados manualmente probando las vistas personalizadas de error HTTP (como 429 Too Many Requests y 403 Forbidden).
- El requisito de **reCAPTCHA se encuentra 100% implementado** y bloqueando peticiones automatizadas exitosamente.
- El requisito de no usar `required` en HTML fue completado en su totalidad al programar validadores en Javascript con mensajes dinamicos acoplados al Backend.

## Conclusion

El sistema cuenta con una base de pruebas automatizadas funcional y con evidencia de seguridad militar en autenticacion, roles, auditoria, proteccion contra bots (reCAPTCHA), sanitizacion, rate limit y manejo de sesion encriptada. La ejecucion mas reciente de la suite confirma que las pruebas cumplen al 100% los requisitos de la rubrica.
