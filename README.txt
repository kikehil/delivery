======================================================================
     URBIX - PEDIR FÁCIL • VENDER FÁCIL (PÁNUCO, VER.)
======================================================================

¡Felicidades! Tienes en tus manos el ecosistema completo de Urbix.
Sigue estos pasos para instalarlo en tu servidor local (XAMPP).

----------------------------------------------------------------------
1. INSTALACIÓN DE ARCHIVOS
----------------------------------------------------------------------
- Copia todos los archivos de este proyecto dentro de la carpeta:
  C:\xampp\htdocs\urbix (o la ruta de tu preferencia).

- Asegúrate de tener el archivo 'logo.png' en la carpeta principal
  para que la marca se vea correctamente.

----------------------------------------------------------------------
2. CONFIGURACIÓN DE BASE DE DATOS
----------------------------------------------------------------------
- Abre el Panel de Control de XAMPP e inicia 'Apache' y 'MySQL'.
- Entra a http://localhost/phpmyadmin
- Ve a la pestaña 'Importar' (Import).
- Selecciona el archivo: 'urbix_master_setup.sql'.
- Haz clic en 'Continuar' (Go). Se creará la base de datos 
  'night_market_db' con todos los cupones y negocios iniciales.

----------------------------------------------------------------------
3. CONFIGURACIÓN DE CONEXIÓN (IMPORTANTE)
----------------------------------------------------------------------
- Abre el archivo 'conexion.php'.
- Asegúrate de que los datos de acceso sean los de tu XAMPP:
    $user = 'root';
    $pass = ''; // Déjalo vacío si no pusiste contraseña en MySQL.

----------------------------------------------------------------------
4. PERSONALIZACIÓN FINAL (WHATSAPP)
----------------------------------------------------------------------
- Para recibir las solicitudes de nuevos socios en tu celular:
  Abre 'gracias.html' y en la línea 87 busca 'adminPhone'.
  Cambia el número por tu número real con código de país (ej: 521...).

- Para recibir pedidos de prueba:
  Los negocios en la base de datos tienen teléfonos ficticios.
  Puedes cambiarlos en la tabla 'negocios' desde phpMyAdmin.

----------------------------------------------------------------------
5. PANTALLAS INCLUIDAS
----------------------------------------------------------------------
- http://localhost/urbix/index.html         -> App de Clientes
- http://localhost/urbix/registra-negocio.html -> Registro de Socios
- http://localhost/urbix/order-status.php   -> Seguimiento de pedidos

----------------------------------------------------------------------
¡Todo listo! Urbix está listo para conquistar Pánuco.
Desarrollado con lógica Senior UX y B2B Integration.
----------------------------------------------------------------------
