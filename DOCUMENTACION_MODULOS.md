# 📖 Documentación de Módulos: YaLoPido (Bocao Style)

Este documento detalla la arquitectura modular y el funcionamiento de la plataforma YaLoPido, diseñada bajo una estética minimalista y eficiente de tipo "Uber Eats".

---

## 1. Módulo de Usuario (Frontend / Cliente)
*   **Archivos Clave**: `index.html`, `style.css`, `get_feed.php`, `enviar_pedido.php`
*   **Funcionamiento**:
    *   **Geolocalización Manual**: El usuario selecciona su colonia en Pánuco. El sistema filtra los restaurantes que sirven a esa zona y calcula el costo de envío automáticamente.
    *   **Carrito Inteligente**: Persistente mediante `localStorage`, permite añadir productos de un solo comercio a la vez.
    *   **Proceso de Pedido**: Se envía un JSON al servidor (`enviar_pedido.php`) que:
        1. Registra la estadística de venta para el dueño.
        2. Dispara un Webhook a n8n para procesamiento de datos.
    *   **Rastreo**: Redirige a `order-status.php` para ver el progreso del pedido en vivo.

---

## 2. Módulo de Seguridad (Acceso Admin/Socio)
*   **Archivos Clave**: `admin/login.php`, `admin/auth_process.php`, `admin/check_session.php`
*   **Funcionamiento**:
    *   **Validación de Sesiones**: Cada página protegida incluye `check_session.php` al inicio.
    *   **Sistema Dual**: Un solo login identifica si el usuario es un **Administrador Maestro** o un **Socio Comercial**, redirigiéndolo a su panel correspondiente.
    *   **Hashing**: Contraseñas protegidas con el estándar `bcrypt`.

---

## 3. Módulo Administrativo (Dueño de la Plataforma)
*   **Archivos Clave**: `admin/dashboard.php`
*   **Funcionamiento**:
    *   **Impacto Visual**: Utiliza `Chart.js` para mostrar tendencias de pedidos diarios y zonas de mayor demanda.
    *   **Gestión de Red**: Tabla CRUD para aprobar el alta de nuevos negocios (`registra-negocio.html`) y monitorear su estado (Activo/Pendiente).
    *   **Control de Zonas**: Permite visualizar qué áreas de la ciudad están generando más tracción.

---

## 4. Módulo del Socio (Dueño de Restaurante)
*   **Archivos Clave**: `partner/dashboard.php`, `partner/api_actions.php`
*   **Funcionamiento**:
    *   **Enfoque Mobile-First**: Interfaz optimizada para que el dueño geste el negocio desde la cocina o mostrador.
    *   **Interruptor de Local (On/Off)**: Si el socio pone el local en "Cerrado", el algoritmo lo oculta automáticamente del feed del cliente para evitar frustraciones.
    *   **Disponibilidad de Menú**: Permite "pausar" platillos específicos en tiempo real (ej. "se agotó la masa de pizza") mediante toggles instantáneos.
    *   **Perfil Express**: Actualización rápida de WhatsApp de pedidos y contacto.

---

## 🏗️ Flujo de Datos
1.  **Registro**: El socio se registra -> Queda en estado `pendiente`.
2.  **Activación**: El Admin activa al socio -> Se crea su usuario de acceso.
3.  **Gestión**: El socio abre su local -> Aparece en `index.html`.
4.  **Venta**: Cliente pide -> n8n recibe notificación -> Estadísticas se actualizan en el Dashboard.

---
**YaLoPido v4.0** - *Pedir Fácil • Vender Fácil*
