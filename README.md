# Central Reservas

## Plugin de Gestión de Cabotaje para Galápagos

<img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.3+-blue.svg">
<img alt="PHP" src="https://img.shields.io/badge/PHP-8.1+-purple.svg">
<img alt="WooCommerce" src="https://img.shields.io/badge/WooCommerce-Requerido-96588a.svg">
<img alt="Licencia" src="https://img.shields.io/badge/Licencia-Propietaria-red.svg">
<img alt="Estado" src="https://img.shields.io/badge/Estado-En Desarrollo-yellow.svg">

Central Tickets es un plugin de WordPress diseñado específicamente para resolver la gestión centralizada de transportes turísticos en las Islas Galápagos, permitiendo la venta anticipada de tickets y eliminando la competencia desleal entre operadores.

## Instalación

Primero descargue el proyecto. Todos los archivos deben estar dentro de una carpeta `central-booking`:

```
central-booking
├─ assets
├─ includes
├─ packages
├─ src
└─ vendor
```

Comprime la carpeta en un archivo .zip. Luego solo súbelo usando la herramienta de importación de plugins de WordPress.

## Arquitectura

El plugin tiene una arquitectura modular. Cada modulo controla una parte independiente del sistema. Los modulos están contenidos en la carpeta `packages`. Dentro de la carpeta, se encuentran los diferentes módulos:

- **`data` (Datos)**: Controla el acceso de datos del sistema y la conección a la base de datos. <small>[Ver documentación](packages/data/README.md)</small>.
- **`gui` (Interfaz gráfica de usuario)**: Dispone de elementos visuales predefinidos para el su uso en la vista del usuario.
- **`pdf` (Controlador de archivos pdf)**: Crea documentos en formato .pdf.
- **`qr` (Creador de códigos QR)**: Crea y renderiza códigos QR con cierto grado de flexibilidad. <small>[Ver documentación](packages/qr/README.md)</small>.
- **`webhook` (Disparadores de Webhooks)**: Ejecuta webhook según ciertas partes del sistema.

Cada modulo incluye un archivo `functions.php` preparado para externalizar las funciones de los módulos. Es decir, no se recomienda instanciar clases pertenecientes a los módulos, lo que se recomienda es usar funciones preparadas dentro de los archivos functions.

Por ejemplo, al crear un objeto de tipo `Ticket`:

```php
// X NO RECOMENDADA
$ticket = new Ticket();

// RECOMENDADA
$ticket = git_ticket_create();
```
