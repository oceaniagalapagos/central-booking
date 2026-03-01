# Módulo de Webhooks - Central Booking

Este módulo proporciona un sistema completo de webhooks para notificar a servicios externos sobre eventos importantes dentro del sistema de reservas Central Booking. Permite la integración con sistemas de terceros mediante notificaciones HTTP automáticas.

## 📋 Índice

- [Características](#-características)
- [Conceptos Básicos](#-conceptos-básicos)
- [API de Funciones](#-api-de-funciones)
- [Eventos Disponibles](#-eventos-disponibles)
- [Estados de Webhook](#-estados-de-webhook)
- [Configuración](#-configuración)
- [Ejemplos de Uso](#-ejemplos-de-uso)
- [Seguridad y Mejores Prácticas](#-seguridad-y-mejores-prácticas)
- [Clases Principales](#-clases-principales)
- [Referencia Rápida](#-referencia-rápida)

## ✨ Características

- **Notificaciones automáticas** sobre eventos del sistema
- **Múltiples topics/eventos** predefinidos para diferentes acciones
- **Gestión de estados** (activo, pausado, deshabilitado)
- **Múltiples webhooks por evento** - Notificar a varios endpoints
- **Validación de URLs** automática
- **Integración nativa con WordPress** usando `wp_remote_post()`
- **Persistencia en base de datos** con gestión automática
- **Singleton manager** para gestión centralizada

## 🧠 Conceptos Básicos

### ¿Qué es un Webhook?

Un webhook es una notificación HTTP automática que se envía cuando ocurre un evento específico en el sistema. Es como un "reverse API call" - en lugar de que tú solicites información, el sistema te la envía cuando algo sucede.

### Componentes Principales

1. **Webhook** - La configuración que define qué, cuándo y dónde enviar
2. **Topic** - El evento específico que dispara el webhook  
3. **Status** - Si el webhook está activo, pausado o deshabilitado
4. **Delivery URL** - El endpoint donde se enviará la notificación
5. **Payload** - Los datos que se envían en la notificación

## 🔧 API de Funciones

El archivo [`functions.php`](functions.php) expone todas las funciones públicas del módulo. **Se recomienda nunca instanciar clases directamente**, utiliza siempre las funciones proporcionadas.

### ❌ No Recomendado
```php
$webhook = new Webhook();
```

### ✅ Recomendado
```php
$webhook = git_webhook_create($data);
```

## 📡 Eventos Disponibles

El sistema define eventos específicos que pueden disparar webhooks:

| Topic | Valor | Descripción | Cuándo se Dispara |
|-------|-------|-------------|-------------------|
| **COUPON_USED** | `'coupon_used'` | Cupón utilizado | Al aplicar un cupón en una reserva |
| **TICKET_CREATE** | `'ticket_create'` | Ticket creado | Al crear una nueva reserva/ticket |
| **TICKET_UPDATE** | `'ticket_update'` | Ticket actualizado | Al modificar datos de un ticket existente |
| **INVOICE_UPLOAD** | `'invoice_upload'` | Factura subida | Al cargar comprobante de pago |
| **PASSENGER_SERVED** | `'passenger_served'` | Pasajero atendido | Al marcar un pasajero como servido |
| **PASSENGER_APPROVED** | `'passenger_approved'` | Pasajero aprobado | Al aprobar un pasajero para el viaje |
| **PASSENGER_TRANSFERRED** | `'passenger_transferred'` | Pasajero trasladado | Al transferir un pasajero a otra ruta |

### Usar Topics en Código

```php
use CentralBooking\Webhook\WebhookTopic;

// Crear webhook para tickets nuevos
$webhook = git_webhook_create([
    'name' => 'Notificación Nuevas Reservas',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://mi-sistema.com/webhook/nuevas-reservas'
]);
```

## 🔄 Estados de Webhook

Los webhooks pueden estar en diferentes estados:

| Estado | Valor | Descripción |
|--------|-------|-------------|
| **ACTIVE** | `'active'` | Webhook activo - Envía notificaciones |
| **IN_PAUSE** | `'in_pause'` | En pausa - Temporalmente deshabilitado |
| **DISABLED** | `'disabled'` | Deshabilitado - No envía notificaciones |

### Control de Estados

```php
use CentralBooking\Webhook\WebhookStatus;

// Webhook activo
$webhook = git_webhook_create([
    'name' => 'Sistema Principal',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://sistema.com/webhook'
]);

// Pausar temporalmente
$webhook->status = WebhookStatus::IN_PAUSE;
git_webhook_save($webhook);

// Deshabilitar permanentemente  
$webhook->status = WebhookStatus::DISABLED;
git_webhook_save($webhook);
```

## ⚙️ Configuración

### Crear un Nuevo Webhook

```php
$webhook_data = [
    'name' => 'Sistema de Contabilidad',
    'topic' => WebhookTopic::INVOICE_UPLOAD,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://contabilidad.empresa.com/api/webhook'
];

$webhook = git_webhook_create($webhook_data);
$result = git_webhook_save($webhook);

if ($result) {
    echo "Webhook creado con ID: {$webhook->id}";
}
```

### Gestionar Webhooks Existentes

```php
// Obtener todos los webhooks
$all_webhooks = git_webhook_get_all();

// Obtener webhook específico
$webhook = git_webhook_get_by_id(123);

// Obtener webhooks por topic
$ticket_webhooks = git_webhook_get_by_topic(WebhookTopic::TICKET_CREATE);

// Modificar webhook existente
if ($webhook) {
    $webhook->status = WebhookStatus::IN_PAUSE;
    git_webhook_save($webhook);
}
```

## 🔥 Ejemplos de Uso

### Ejemplo 1: Notificar Nuevas Reservas a Sistema Externo

```php
// 1. Crear webhook para nuevas reservas
$webhook = git_webhook_create([
    'name' => 'CRM Principal',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://crm.miempresa.com/api/nueva-reserva'
]);

git_webhook_save($webhook);

// 2. En tu código donde se crean reservas
function crear_nueva_reserva($datos_reserva) {
    // ... lógica para crear la reserva
    $ticket = git_ticket_save($datos_reserva);
    
    // Disparar webhook automáticamente
    $payload = [
        'ticket_id' => $ticket->id,
        'customer_name' => $datos_reserva['customer_name'],
        'amount' => $ticket->total_amount,
        'created_at' => date('Y-m-d H:i:s'),
        'route' => $ticket->getRoute()->name ?? 'N/A'
    ];
    
    git_webhook_trigger(WebhookTopic::TICKET_CREATE, $payload);
    
    return $ticket;
}
```

### Ejemplo 2: Sistema de Notificaciones por Email

```php
// Webhook para notificaciones internas por email
$email_webhook = git_webhook_create([
    'name' => 'Notificaciones Email',
    'topic' => WebhookTopic::PASSENGER_APPROVED,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://tu-servidor.com/send-email-notification'
]);

git_webhook_save($email_webhook);

// Endpoint que recibe el webhook (en tu servidor)
// POST https://tu-servidor.com/send-email-notification
function handle_passenger_approved_webhook() {
    $payload = json_decode(file_get_contents('php://input'), true);
    
    $passenger_name = $payload['passenger_name'];
    $ticket_id = $payload['ticket_id'];
    
    // Enviar email de confirmación
    wp_mail(
        $payload['customer_email'],
        'Pasajero Aprobado',
        "Su pasajero {$passenger_name} ha sido aprobado para el viaje #{$ticket_id}"
    );
}
```

### Ejemplo 3: Integración con Sistema de Facturación

```php
// Webhook para sistema de facturación automática
$billing_webhook = git_webhook_create([
    'name' => 'Sistema de Facturación',
    'topic' => WebhookTopic::INVOICE_UPLOAD,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://facturacion.empresa.com/api/procesar-comprobante'
]);

git_webhook_save($billing_webhook);

// Disparar cuando se sube comprobante
function procesar_comprobante_pago($ticket_id, $archivo_comprobante) {
    // ... lógica para procesar el archivo
    
    $ticket = git_ticket_by_id($ticket_id);
    $payload = [
        'ticket_id' => $ticket_id,
        'amount' => $ticket->total_amount,
        'customer_id' => $ticket->getClient()->ID,
        'invoice_url' => wp_get_attachment_url($archivo_comprobante),
        'upload_date' => date('Y-m-d H:i:s')
    ];
    
    git_webhook_trigger(WebhookTopic::INVOICE_UPLOAD, $payload);
}
```

### Ejemplo 4: Webhook para Analytics/Métricas

```php
// Múltiples webhooks para el mismo evento
$analytics_webhook = git_webhook_create([
    'name' => 'Google Analytics',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://analytics.google.com/webhook/eventos'
]);

$metrics_webhook = git_webhook_create([
    'name' => 'Dashboard Interno', 
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://dashboard.empresa.com/api/nueva-venta'
]);

git_webhook_save($analytics_webhook);
git_webhook_save($metrics_webhook);

// Un solo trigger enviará a ambos sistemas
function registrar_nueva_venta($ticket) {
    $payload = [
        'event' => 'new_booking',
        'value' => $ticket->total_amount,
        'timestamp' => time(),
        'source' => 'central_booking'
    ];
    
    // Envía automáticamente a Google Analytics Y Dashboard Interno
    git_webhook_trigger(WebhookTopic::TICKET_CREATE, $payload);
}
```

## 🔒 Seguridad y Mejores Prácticas

### Validación de URLs

```php
// El sistema valida automáticamente las URLs
$invalid_webhook = git_webhook_create([
    'name' => 'Test',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'not-a-valid-url'  // ❌ Fallará al guardar
]);

$result = git_webhook_save($invalid_webhook);
// $result será false debido a la URL inválida
```

### Manejo de Errores HTTP

```php
// El webhook automáticamente maneja códigos de respuesta
class Webhook {
    public function send(array $payload) {
        // Solo webhooks activos envían datos
        if ($this->status !== WebhookStatus::ACTIVE) {
            return false;
        }
        
        $response = wp_remote_post($this->url_delivery, $args);
        
        // Considera exitoso códigos 200-299
        return $response['response']['code'] >= 200 && 
               $response['response']['code'] < 300;
    }
}
```

### Recomendaciones

1. **Validar endpoints** antes de configurar webhooks en producción
2. **Usar HTTPS** para todas las URLs de delivery
3. **Implementar idempotencia** en tus endpoints
4. **Manejar timeouts** apropiadamente  
5. **Logar fallos** para debugging
6. **Pausar webhooks** que fallan consistentemente

## 🏗️ Clases Principales

### Webhook
```php
class Webhook
{
    public int $id = 0;
    public string $name = '';
    public WebhookStatus $status = WebhookStatus::ACTIVE;
    public WebhookTopic $topic = WebhookTopic::NONE;
    public string $url_delivery = '';
    
    public function send(array $payload): bool
}
```

### WebhookManager (Singleton)
```php
class WebhookManager  
{
    public static function getInstance(): WebhookManager
    public function save(Webhook $webhook): mixed
    public function get(int $id): ?Webhook
    public function getAll(): array
    public function getByTopic(WebhookTopic $topic): array
    public function trigger(WebhookTopic $topic, array $payload): void
}
```

### WebhookTopic (Enum)
```php
enum WebhookTopic: string
{
    case NONE = 'none';
    case COUPON_USED = 'coupon_used';
    case TICKET_CREATE = 'ticket_create';
    case TICKET_UPDATE = 'ticket_update';
    case INVOICE_UPLOAD = 'invoice_upload';
    case PASSENGER_SERVED = 'passenger_served';
    case PASSENGER_APPROVED = 'passenger_approved';
    case PASSENGER_TRANSFERRED = 'passenger_transferred';
    
    public function label(): string
}
```

### WebhookStatus (Enum)
```php
enum WebhookStatus: string
{
    case ACTIVE = 'active';
    case IN_PAUSE = 'in_pause';
    case DISABLED = 'disabled';
    
    public function label(): string
}
```

## 📚 Referencia Rápida

### 🔧 Funciones de Gestión
```php
git_webhook_create(array $data): Webhook              // Crear webhook
git_webhook_save(Webhook $webhook): mixed             // Guardar webhook
git_webhook_get_by_id(int $id): ?Webhook              // Obtener por ID
git_webhook_get_all(): array                          // Obtener todos
git_webhook_get_by_topic(WebhookTopic $topic): array  // Obtener por topic
```

### 🔥 Función de Disparo
```php
git_webhook_trigger(WebhookTopic $topic, array $payload): void
```

### 📊 Estructura del Payload

Los payloads son arrays asociativos que se serializan como JSON:

```php
// Ejemplo de payload típico
$payload = [
    'event_type' => 'ticket_created',
    'ticket_id' => 123,
    'timestamp' => '2026-01-15T10:30:00Z',
    'data' => [
        'customer_name' => 'Juan Pérez',
        'total_amount' => 50000,
        'route_name' => 'Cartagena - Isla del Rosario'
    ]
];
```

## 🚀 Casos de Uso Avanzados

### Sistema de Respaldo Múltiple

```php
// Configurar webhook principal y respaldo
$primary_webhook = git_webhook_create([
    'name' => 'Sistema Principal',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://primary-system.com/webhook'
]);

$backup_webhook = git_webhook_create([
    'name' => 'Sistema Respaldo',
    'topic' => WebhookTopic::TICKET_CREATE,
    'status' => WebhookStatus::ACTIVE,
    'delivery_url' => 'https://backup-system.com/webhook'
]);

git_webhook_save($primary_webhook);
git_webhook_save($backup_webhook);

// Un trigger enviará a ambos sistemas automáticamente
```

### Webhook Condicional

```php
function trigger_conditional_webhook($ticket) {
    // Solo disparar para tickets costosos
    if ($ticket->total_amount > 100000) {
        $payload = [
            'ticket_id' => $ticket->id,
            'amount' => $ticket->total_amount,
            'requires_approval' => true
        ];
        
        git_webhook_trigger(WebhookTopic::TICKET_CREATE, $payload);
    }
}
```

### Webhook con Rate Limiting

```php
function smart_webhook_trigger($topic, $payload) {
    $cache_key = 'webhook_last_' . $topic->value;
    $last_trigger = get_transient($cache_key);
    
    // Solo disparar cada 30 segundos por topic
    if (!$last_trigger || time() - $last_trigger > 30) {
        git_webhook_trigger($topic, $payload);
        set_transient($cache_key, time(), 60);
    }
}
```

---

## 📝 Notas Importantes

1. **Usar siempre las funciones públicas** - No instanciar clases directamente
2. **Validar URLs de destino** - El sistema rechaza URLs inválidas automáticamente
3. **Solo webhooks activos se ejecutan** - Estados pausado/deshabilitado no envían
4. **Múltiples webhooks por evento** - Un topic puede tener varios webhooks
5. **Singleton manager** - Un solo gestor centralizado para todo el sistema
6. **Integración WordPress nativa** - Usa `wp_remote_post()` para máxima compatibilidad
7. **Serialización automática** - Los payloads se convierten a JSON automáticamente

## 🔗 Enlaces Útiles

- [WordPress HTTP API](https://developer.wordpress.org/plugins/http-api/)
- [Webhook Best Practices](https://docs.github.com/en/developers/webhooks-and-events/webhooks/best-practices-for-using-webhooks)
- [JSON Schema](https://json-schema.org/)

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2026
