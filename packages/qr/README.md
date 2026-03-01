# Módulo de Códigos QR - Central Booking

Este módulo proporciona una interfaz completa para la generación y personalización de códigos QR. Aunque es compacto, está diseñado como un módulo independiente para facilitar su expansión y mantenimiento futuro.

## 📋 Índice

- [Módulo de Códigos QR - Central Booking](#módulo-de-códigos-qr---central-booking)
  - [📋 Índice](#-índice)
  - [✨ Características](#-características)
  - [🔧 API de Funciones](#-api-de-funciones)
    - [❌ No Recomendado](#-no-recomendado)
    - [✅ Recomendado](#-recomendado)
  - [📱 Tipos de Datos QR](#-tipos-de-datos-qr)
    - [Detalles de Cada Tipo](#detalles-de-cada-tipo)
      - [🌐 URLs](#-urls)
      - [📧 Email](#-email)
      - [📞 Teléfono](#-teléfono)
      - [📶 WiFi](#-wifi)
      - [💬 WhatsApp](#-whatsapp)
      - [📝 Datos Personalizados](#-datos-personalizados)
  - [🎨 Personalización](#-personalización)
    - [Parámetros de Configuración](#parámetros-de-configuración)
    - [Niveles de Corrección de Errores](#niveles-de-corrección-de-errores)
    - [Funciones de Color](#funciones-de-color)
  - [🔨 Ejemplos de Uso](#-ejemplos-de-uso)
    - [Ejemplo Básico](#ejemplo-básico)
    - [Ejemplo con Personalización Completa](#ejemplo-con-personalización-completa)
    - [Ejemplo de QR para WiFi Empresarial](#ejemplo-de-qr-para-wifi-empresarial)
    - [Ejemplo de WhatsApp para Soporte](#ejemplo-de-whatsapp-para-soporte)
    - [Ejemplo de Email con Información Predefinida](#ejemplo-de-email-con-información-predefinida)
  - [🏗️ Clases Principales](#️-clases-principales)
    - [DataQr (Interface)](#dataqr-interface)
    - [CodeQr](#codeqr)
    - [ColorQr](#colorqr)
    - [ErrorCorrectionCode (Enum)](#errorcorrectioncode-enum)
  - [📚 Referencia Rápida](#-referencia-rápida)
    - [🔧 Funciones de Creación de Datos](#-funciones-de-creación-de-datos)
    - [🎨 Funciones de Color](#-funciones-de-color)
    - [📱 Función Principal](#-función-principal)
    - [🖼️ Renderizado](#️-renderizado)
  - [🚀 Casos de Uso Avanzados](#-casos-de-uso-avanzados)
    - [QR Dinámico para Reservas](#qr-dinámico-para-reservas)
    - [QR para Datos de Contacto Completos](#qr-para-datos-de-contacto-completos)
  - [📝 Notas Importantes](#-notas-importantes)
  - [🔗 Enlaces Útiles](#-enlaces-útiles)

## ✨ Características

- **Generación de códigos QR** para múltiples tipos de datos
- **Personalización completa** de colores, tamaño y márgenes  
- **Corrección de errores configurable** (4 niveles disponibles)
- **Integración con WordPress** usando funciones seguras
- **Múltiples formatos de datos** preconfigurados
- **API externa optimizada** para generación en la nube

## 🔧 API de Funciones

El archivo [`functions.php`](functions.php) expone todas las funciones públicas del módulo. **Se recomienda nunca instanciar clases directamente**, utiliza siempre las funciones proporcionadas.

### ❌ No Recomendado
```php
$data = new URLData($url);
```

### ✅ Recomendado  
```php
$data = git_create_qr_url_data($url);
```

## 📱 Tipos de Datos QR

El módulo incluye tipos de datos predefinidos que implementan la interfaz `DataQr`:

| Tipo | Función de Creación | Uso |
|------|-------------------|-----|
| **URL** | `git_create_qr_url_data($url)` | Enlaces web |
| **Email** | `git_create_qr_email_data($email, $subject, $body)` | Correos electrónicos |
| **Teléfono** | `git_create_qr_phone_data($phone)` | Números de teléfono |
| **WiFi** | `get_create_qr_wifi_data($ssid, $password, $encryption, $hidden)` | Configuración WiFi |
| **WhatsApp** | `git_create_qr_whatsapp_data($phone, $message)` | Contactos de WhatsApp |
| **Personalizado** | `git_create_qr_data($data)` | Cualquier texto/datos |

### Detalles de Cada Tipo

#### 🌐 URLs
```php
$data = git_create_qr_url_data('https://example.com');
```

#### 📧 Email
```php
// Email básico
$data = git_create_qr_email_data('contacto@example.com');

// Email con asunto y cuerpo
$data = git_create_qr_email_data(
    email_address: 'soporte@example.com',
    subject: 'Consulta sobre reserva',
    body: 'Hola, necesito información sobre...'
);
```

#### 📞 Teléfono
```php
$data = git_create_qr_phone_data('+1234567890');
```

#### 📶 WiFi
```php
// WiFi básico WPA
$data = get_create_qr_wifi_data('MiWiFi', 'password123');

// WiFi personalizado
$data = get_create_qr_wifi_data(
    ssid: 'RestauranteWiFi',
    password: 'cliente2026', 
    encryption: 'WPA',      // WPA, WEP, nopass
    hidden: false           // red oculta o no
);
```

#### 💬 WhatsApp
```php
// Contacto básico
$data = git_create_qr_whatsapp_data('+573001234567');

// Con mensaje predefinido  
$data = git_create_qr_whatsapp_data(
    phone: '+573001234567',
    message: 'Hola! Vengo por el código QR'
);
```

#### 📝 Datos Personalizados
```php
$data = git_create_qr_data('Cualquier texto o información');
```

## 🎨 Personalización

La función `git_qr_create()` acepta un segundo parámetro con opciones de personalización:

```php
function git_qr_create(DataQr $data, array $params = []): CodeQr
```

### Parámetros de Configuración

| Parámetro | Tipo | Descripción | Valores |
|-----------|------|-------------|---------|
| `size` | `int` | Tamaño del QR en píxeles | Mínimo: 10, por defecto: 350 |
| `margin` | `int` | Margen alrededor del QR | 0-50, por defecto: 10 |
| `color` | `ColorQr` | Color del QR | Objeto ColorQr |
| `color_hex` | `string` | Color en formato hex | `#000000`, `#fff`, etc. |
| `color_rgb` | `array` | Color en formato RGB | `['r' => 255, 'g' => 0, 'b' => 0]` |
| `bg_color` | `ColorQr` | Color de fondo | Objeto ColorQr |
| `bg_color_hex` | `string` | Fondo en formato hex | `#ffffff`, etc. |
| `bg_color_rgb` | `array` | Fondo en formato RGB | `['r' => 255, 'g' => 255, 'b' => 255]` |
| `error_correction_code` | `string\|ErrorCorrectionCode` | Nivel de corrección | Ver tabla siguiente |

### Niveles de Corrección de Errores

| Nivel | Slug | Descripción | Tolerancia a Daños |
|-------|------|-------------|-------------------|
| **LOW** | `'low'` | Baja corrección | ~7% |
| **MEDIUM** | `'medium'` | Corrección media | ~15% |
| **QUARTILE** | `'quartile'` | Corrección cuartil | ~25% |
| **HIGH** | `'high'` | Alta corrección | ~30% |

### Funciones de Color

```php
// Crear colores desde valores hexadecimales
$color = git_create_color_from_hex('#ff0000');

// Crear colores desde valores RGB
$color = git_create_color_from_rgb(255, 0, 0); // Rojo
// Retorna null si los valores están fuera del rango (0-255)
```

## 🔨 Ejemplos de Uso

### Ejemplo Básico
```php
// Crear datos y QR básico
$data = git_create_qr_phone_data('+573001234567');
$qr = git_qr_create($data);

// Renderizar como imagen HTML
echo $qr->render(); // <img src="..." alt="QR Code">
```

### Ejemplo con Personalización Completa
```php
// Configuración avanzada
$params = [
    'size' => 500,
    'margin' => 25,
    'color_hex' => '#2c3e50',              // Azul oscuro
    'bg_color_rgb' => ['r' => 236, 'g' => 240, 'b' => 241], // Gris claro  
    'error_correction_code' => 'high'      // Máxima corrección
];

$data = git_create_qr_url_data('https://centralbooking.com');
$qr = git_qr_create($data, $params);

echo $qr->render('QR Central Booking');
```

### Ejemplo de QR para WiFi Empresarial
```php
$wifi_data = get_create_qr_wifi_data(
    ssid: 'OficinaGuest',
    password: 'Invitado2026!',
    encryption: 'WPA',
    hidden: false
);

$params = [
    'size' => 400,
    'margin' => 30,
    'color_hex' => '#34495e',
    'bg_color_hex' => '#ecf0f1',
    'error_correction_code' => 'medium'
];

$qr = git_qr_create($wifi_data, $params);
echo $qr->render('WiFi de Invitados');
```

### Ejemplo de WhatsApp para Soporte
```php
$whatsapp_data = git_create_qr_whatsapp_data(
    phone: '+573001234567',
    message: 'Hola, necesito soporte técnico para mi reserva'
);

$params = [
    'size' => 300,
    'color_hex' => '#25d366',     // Verde WhatsApp
    'bg_color_hex' => '#ffffff',
    'error_correction_code' => 'quartile'
];

$qr = git_qr_create($whatsapp_data, $params);
echo $qr->render('Soporte WhatsApp');
```

### Ejemplo de Email con Información Predefinida
```php
$email_data = git_create_qr_email_data(
    email_address: 'reservas@centralbooking.com',
    subject: 'Consulta sobre mi reserva',
    body: 'Estimados, quisiera consultar sobre el estado de mi reserva #'
);

$qr = git_qr_create($email_data, [
    'size' => 350,
    'color_hex' => '#e74c3c',
    'error_correction_code' => 'medium'
]);

echo $qr->render('Contacto por Email');
```

## 🏗️ Clases Principales

### DataQr (Interface)
Interface base que define el método `getData(): string` para todos los tipos de datos.

### CodeQr
```php
class CodeQr
{
    public static function create(
        DataQr $data,
        ErrorCorrectionCode $errorCorrectionCode = ErrorCorrectionCode::LOW,
        int $size = 200,
        int $margin = 10,
        ?ColorQr $color = null,
        ?ColorQr $bgColor = null
    ): CodeQr

    public function render(string $title = 'QR Code'): string
    public function getUrlCode(): string
}
```

### ColorQr
```php
class ColorQr
{
    public static function fromHex(string $hex): ColorQr
    public static function fromRGB(int $r, int $g, int $b): ColorQr
    
    public function getColorRGB(): array
    public function getColorHex(): string
}
```

### ErrorCorrectionCode (Enum)
```php
enum ErrorCorrectionCode
{
    case LOW;       // 7% corrección
    case MEDIUM;    // 15% corrección  
    case QUARTILE;  // 25% corrección
    case HIGH;      // 30% corrección
    
    public function label(): string
    public function slug(): string
    public static function fromSlug(string $slug): ?ErrorCorrectionCode
}
```

## 📚 Referencia Rápida

### 🔧 Funciones de Creación de Datos
```php
git_create_qr_url_data(string $url): DataQr
git_create_qr_email_data(string $email, ?string $subject, ?string $body): DataQr
git_create_qr_phone_data(string $phone): DataQr
get_create_qr_wifi_data(string $ssid, string $password, $encryption, $hidden): DataQr
git_create_qr_whatsapp_data(string $phone, ?string $message): DataQr
git_create_qr_data(string $data): DataQr
```

### 🎨 Funciones de Color
```php
git_create_color_from_hex(string $hex): ColorQr
git_create_color_from_rgb(int $r, int $g, int $b): ?ColorQr
```

### 📱 Función Principal
```php
git_qr_create(DataQr $data, array $params = []): CodeQr
```

### 🖼️ Renderizado
```php
$qr->render(string $title = 'QR Code'): string  // Etiqueta <img>
$qr->getUrlCode(): string                       // URL directa de la imagen
```

## 🚀 Casos de Uso Avanzados

### QR Dinámico para Reservas
```php
function generate_booking_qr($booking_id) {
    $url = home_url("/verificar-reserva/$booking_id");
    $data = git_create_qr_url_data($url);
    
    $params = [
        'size' => 250,
        'margin' => 15,
        'color_hex' => '#2980b9',
        'error_correction_code' => 'high'
    ];
    
    return git_qr_create($data, $params);
}

// Uso
$qr = generate_booking_qr(123);
echo $qr->render("Verificar Reserva #123");
```

### QR para Datos de Contacto Completos
```php
function create_contact_qr($name, $phone, $email, $company) {
    $vcard = "BEGIN:VCARD\n";
    $vcard .= "VERSION:3.0\n";
    $vcard .= "FN:$name\n";
    $vcard .= "TEL:$phone\n";
    $vcard .= "EMAIL:$email\n";
    $vcard .= "ORG:$company\n";
    $vcard .= "END:VCARD";
    
    $data = git_create_qr_data($vcard);
    
    return git_qr_create($data, [
        'size' => 400,
        'error_correction_code' => 'quartile'
    ]);
}
```

---

## 📝 Notas Importantes

1. **Usar siempre las funciones públicas** - No instanciar clases directamente
2. **Validación automática** - Los datos se validan al crear las instancias
3. **API externa** - Los QR se generan usando api.qrserver.com
4. **Límites de tamaño** - Mínimo 10px, máximo margen 50px
5. **Formatos soportados** - Solo PNG por defecto
6. **WordPress integration** - Funciones escapadas para seguridad

## 🔗 Enlaces Útiles

- [Especificaciones QR](https://www.qrcode.com/en/)
- [API QR Server](https://goqr.me/api/)
- [Estándares vCard](https://tools.ietf.org/html/rfc6350)

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2026