# Módulo de Datos - Central Booking

Este módulo se encarga del acceso a la base de datos y la gestión de entidades del sistema de reservas Central Booking. Proporciona una capa de abstracción para el manejo de datos relacionados con transportes, rutas, servicios, pasajeros y tickets.

## 📋 Índice

- [Entidades](#-entidades)
- [Metadatos](#-metadatos)
- [Funciones de la API](#-funciones-de-la-api)
- [Gestión de Entidades](#-gestión-de-entidades)
- [Validación y Disponibilidad](#-validación-y-disponibilidad)
- [Sistema de Configuración](#-sistema-de-configuración)
- [Sistema de Logs](#-sistema-de-logs)
- [Errores de Servicio](#-errores-de-servicio)

## 🏗️ Entidades

El módulo gestiona las siguientes entidades principales:

| Entidad | Descripción | Archivo |
|---------|-------------|---------|
| **Ticket** | Representa una reserva de transporte | [Ticket.php](src/Ticket.php) |
| **Passenger** | Información de los pasajeros | [Passenger.php](src/Passenger.php) |
| **Route** | Rutas de transporte entre ubicaciones | [Route.php](src/Route.php) |
| **Transport** | Vehículos de transporte (barcos, autobuses, etc.) | [Transport.php](src/Transport.php) |
| **Service** | Servicios adicionales disponibles | [Service.php](src/Service.php) |
| **Location** | Ubicaciones geográficas (puertos, terminales) | [Location.php](src/Location.php) |
| **Zone** | Zonas geográficas que agrupan ubicaciones | [Zone.php](src/Zone.php) |
| **Operator** | Operadores de transporte | [Operator.php](src/Operator.php) |

Todas las entidades utilizan un sistema de metadatos flexible que permite almacenar información adicional de cualquier tipo.

## 🗃️ Metadatos

La clase [`MetaManager`](src/MetaManager.php) permite manipular metadatos flexibles para cualquier entidad del sistema. Los metadatos son campos de información adicional que pueden almacenar cualquier tipo de dato serializado.

### Uso de Metadatos

```php
// GUARDAR UN METADATO
MetaManager::setMeta(
    MetaManager::TICKET,    // Tipo de entidad (TICKET, TRANSPORT, ROUTE, PASSENGER, etc.)
    1,                      // ID del registro al que pertenece el metadato
    'clave',                // Clave del metadato
    'valor'                 // Valor del metadato (cualquier tipo)
);

// RECUPERAR UN METADATO
$meta = MetaManager::getMeta(
    MetaManager::TICKET,    // Tipo de entidad
    1,                      // ID del registro
    'clave'                 // Clave del metadato
);

echo $meta; // Resultado: 'valor'
```

### Tipos de Metadatos Disponibles

- `MetaManager::TICKET` - Metadatos de tickets
- `MetaManager::TRANSPORT` - Metadatos de transportes
- `MetaManager::ROUTE` - Metadatos de rutas
- `MetaManager::PASSENGER` - Metadatos de pasajeros
- `MetaManager::SERVICE` - Metadatos de servicios
- `MetaManager::LOCATION` - Metadatos de ubicaciones
- `MetaManager::ZONE` - Metadatos de zonas
- `MetaManager::SETTING` - Configuraciones del sistema

## 🔧 Funciones de la API

El archivo [`functions.php`](functions.php) expone todas las funciones públicas del módulo de datos. **Se recomienda nunca instanciar objetos directamente**, en su lugar, utiliza las funciones proporcionadas como interfaz.

### ❌ No Recomendado
```php
$ticket = new Ticket();
```

### ✅ Recomendado
```php
$ticket = git_ticket_create();
```

Las funciones de creación ofrecen ventajas importantes:
- Parámetros de inicialización simplificados
- Validación automática de datos
- Relaciones entre entidades automáticas
- Sanitización de datos de entrada

### Ejemplo: Creación con Parámetros

```php
// Instanciación manual (no recomendada)
$ticket_manual = new Ticket();
$ticket_manual->id = 1;
$ticket_manual->total_amount = 1900;
$ticket_manual->setOrder(wc_get_order(2));

// Método recomendado
$ticket_optimizado = git_ticket_create([
    'id' => 1,
    'total_amount' => 1900,
    'order_id' => 2,
]);

// Ambos objetos son equivalentes en contenido
```

### Ejemplo: Crear y Guardar un Servicio

```php
// Opción 1: Crear y luego guardar
$service = git_service_create([
    'name' => 'Chaleco salvavidas',
    'icon' => 'https://services.com/chaleco.png',
    'price' => 0
]);
$service_saved = git_service_save($service);

// Opción 2: Crear y guardar en una sola operación
$service_saved = git_service_save([
    'name' => 'Chaleco salvavidas',
    'icon' => 'https://services.com/chaleco.png',
    'price' => 0
]);

echo $service_saved->id; // ID del registro en la base de datos
```

## 💾 Métodos de Guardado en Entidades

Además de las funciones globales `git_*_save()`, las entidades también proporcionan un método `save()` integrado que simplifica el guardado:

### ✨ Método `save()` en Entidades

Las siguientes entidades incluyen un método `save()` que retorna un booleano (`true` si se guardó exitosamente, `false` en caso contrario):

| Entidad | Método save() | Función Equivalente |
|---------|---------------|-------------------|
| **Service** | `$service->save()` | `git_service_save($service)` |
| **Ticket** | `$ticket->save()` | `git_ticket_save($ticket)` |
| **Passenger** | `$passenger->save()` | `git_passenger_save($passenger)` |
| **Route** | `$route->save()` | `git_route_save($route)` |
| **Transport** | `$transport->save()` | `git_transport_save($transport)` |
| **Location** | `$location->save()` | `git_location_save($location)` |
| **Operator** | `$operator->save()` | `git_operator_save($operator)` |
| **Zone** | `$zone->save()` | `git_zone_save($operator)` |

### Ejemplos de Uso

```php
// ✅ Método directo en la entidad
$service = git_service_create([
    'name' => 'WiFi Premium',
    'price' => 500
]);

if ($service->save()) {
    echo "Servicio guardado exitosamente con ID: {$service->id}";
} else {
    echo "Error al guardar el servicio";
}

// ✅ Función global equivalente
$result = git_service_save($service);
if ($result !== null) {
    echo "Servicio guardado exitosamente con ID: {$result->id}";
}

// ✅ Ambos métodos son funcionalmente equivalentes
// La única diferencia es el valor de retorno:
// - $entity->save() retorna boolean
// - git_*_save() retorna el objeto guardado o null
```

### Comparación de Enfoques

```php
// Método 1: Usando método save() de la entidad
$passenger = git_passenger_create($data);
$success = $passenger->save();

if ($success) {
    $passenger_id = $passenger->id; // ID disponible después del guardado
}

// Método 2: Usando función global
$passenger_saved = git_passenger_save($data);

if ($passenger_saved !== null) {
    $passenger_id = $passenger_saved->id;
}

// Método 3: Crear y guardar en una línea
$passenger_saved = git_passenger_save($data);
```

### Cuándo Usar Cada Método

- **`$entity->save()`** - Cuando ya tienes la instancia de la entidad y solo necesitas confirmar si se guardó
- **`git_*_save()`** - Cuando necesitas el objeto guardado con el ID actualizado o prefieres un enfoque funcional
- **Crear y guardar directo** - Cuando trabajas con arrays de datos y quieres simplificar el código

## 🏪 Gestión de Entidades

### Operadores
```php
// Obtener todos los operadores
$operadores = git_operators();

// Obtener operador por ID
$operador = git_operator_by_id(1);

// Guardar operador
$operador_guardado = git_operator_save($operador);

// Asignar cupón a operador
git_assign_coupon_to_operator($operador, $cupon);
```

### Rutas
```php
// Obtener rutas con paginación
$rutas_paginadas = git_routes_result_set([
    'limit' => 10,
    'offset' => 0,
    'order_by' => 'id',
    'order' => 'ASC'
]);

// Obtener todas las rutas
$rutas = git_routes();

// Crear ruta con ubicaciones
$ruta = git_route_create([
    'origin_id' => 1,
    'destiny_id' => 2,
    'departure_time' => '08:30:00',
    'arrival_time' => '10:15:00',
    'type' => 'marine'
]);
```

### Transportes
```php
// Obtener transportes disponibles
$transportes = git_transports();

// Crear transporte completo
$transporte = git_transport_create([
    'code' => 'FERRY001',
    'nicename' => 'Ferry Express',
    'type' => 'marine',
    'capacity' => 150,
    'operator_id' => 1,
    'working_days' => ['monday', 'tuesday', 'wednesday'],
    'routes_id' => [1, 2, 3],
    'services_id' => [1, 2]
]);
```

### Pasajeros
```php
// Obtener pasajeros con filtros
$pasajeros = git_passengers([
    'route_id' => 1,
    'date_trip' => '2026-01-20',
    'served' => false
]);

// Crear pasajero
$pasajero = git_passenger_create([
    'name' => 'Juan Pérez',
    'nationality' => 'CO',
    'type_document' => 'CC',
    'data_document' => '12345678',
    'birthday' => '1990-01-01',
    'route_id' => 1,
    'transport_id' => 1
]);
```

### Ubicaciones y Zonas
```php
// Gestión de zonas
$zona = git_zone_create([
    'name' => 'Costa Atlántica',
    'meta' => ['color' => '#blue', 'region' => 'north']
]);
$zona_guardada = git_zone_save($zona);

// Gestión de ubicaciones
$ubicacion = git_location_create([
    'name' => 'Puerto de Cartagena',
    'zone_id' => 1,
    'meta' => ['coordinates' => '10.4,-75.5']
]);
$ubicacion_guardada = git_location_save($ubicacion);
```

## ✅ Validación y Disponibilidad

El módulo incluye funciones especializadas para validar operaciones y verificar disponibilidad:

### Verificación de Disponibilidad de Transporte
```php
/**
 * Verifica si un transporte tiene capacidad para una ruta en una fecha específica
 * @return bool|ErrorService true si está disponible, ErrorService en caso de error
 */
$disponible = git_transport_check_availability(
    transport: $transporte_id,      // ID del transporte o objeto Transport
    route: $ruta_id,                // ID de la ruta o objeto Route  
    date_trip: new Date('2026-01-20'), // Fecha del viaje
    passengers_count: 2             // Número de pasajeros (opcional, default: 1)
);

if ($disponible === true) {
    echo "Transporte disponible";
} else {
    // Manejar error específico
    switch($disponible) {
        case ErrorService::TRANSPORT_NOT_FOUND:
            echo "Transporte no encontrado";
            break;
        case ErrorService::TRANSPORT_NOT_AVAILABLE:
            echo "Transporte no disponible en esa fecha";
            break;
        // ... otros casos
    }
}
```

### Transferencia de Pasajeros
```php
/**
 * Transfiere un pasajero a una nueva ruta y transporte
 * Solo funciona si el ticket es flexible
 */
$transferencia = git_passenger_transfer(
    passenger: 1,                   // ID del pasajero
    route: 2,                      // Nueva ruta
    transport: 3,                  // Nuevo transporte
    date_trip: new Date('2026-01-25') // Nueva fecha
);

if ($transferencia === true) {
    echo "Pasajero transferido exitosamente";
}
```

### Programar Mantenimiento
```php
/**
 * Programa período de mantenimiento para un transporte
 * Valida que no haya pasajeros pendientes en ese período
 */
$mantenimiento = git_transport_set_maintenance(
    transport: $transporte,
    dateStart: new Date('2026-02-01'),
    dateEnd: new Date('2026-02-05')
);

if ($mantenimiento !== true) {
    echo "No se puede programar mantenimiento: " . $mantenimiento->name;
}
```

## ⚙️ Sistema de Configuración

El módulo proporciona un sistema de configuración persistente usando metadatos:

```php
// Guardar configuración
git_set_setting('api_key', 'abc123');
git_set_setting('max_passengers', 150);
git_set_setting('notification_settings', [
    'email' => true,
    'sms' => false,
    'webhook_url' => 'https://example.com/webhook'
]);

// Recuperar configuración
$api_key = git_get_setting('api_key');
$max_passengers = git_get_setting('max_passengers', 100); // Con valor por defecto

// Configuraciones anidadas (usando dot notation)
$email_enabled = git_get_map_setting('notification_settings.email');
$webhook_url = git_get_map_setting('notification_settings.webhook_url');
```

## 📊 Sistema de Logs

Sistema completo de logs para auditoría y debugging:

### Crear Logs
```php
use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;

// Crear log de información
git_log_create(
    source: LogSource::BOOKING,
    id_source: 123,
    message: "Reserva creada exitosamente",
    level: LogLevel::INFO
);

// Crear log de error
git_log_create(
    source: LogSource::TRANSPORT,
    id_source: 456,
    message: "Error al verificar disponibilidad",
    level: LogLevel::ERROR
);
```

### Consultar Logs
```php
// Obtener logs con filtros
$logs = git_log_result_set([
    'level' => LogLevel::ERROR,
    'source' => LogSource::BOOKING,
    'date_from' => new Date('2026-01-01'),
    'date_to' => new Date('2026-01-31'),
    'limit' => 50,
    'offset' => 0
]);

foreach ($logs->getItems() as $log) {
    echo "ID: {$log->id}, Mensaje: {$log->message}";
}

// Información de paginación
echo "Total: {$logs->getTotalItems()}";
echo "Páginas: {$logs->getTotalPages()}";
```

### Niveles de Log Disponibles
- `LogLevel::DEBUG` - Información de debugging
- `LogLevel::INFO` - Información general
- `LogLevel::WARNING` - Advertencias
- `LogLevel::ERROR` - Errores
- `LogLevel::CRITICAL` - Errores críticos

### Fuentes de Log
- `LogSource::BOOKING` - Operaciones de reserva
- `LogSource::TRANSPORT` - Operaciones de transporte
- `LogSource::PAYMENT` - Operaciones de pago
- `LogSource::SYSTEM` - Operaciones del sistema

## 🧰 Utilidades de Serialización

El módulo incluye utilidades para manejar datos complejos:

```php
// Serializar datos para almacenamiento
$data_compleja = [
    'usuarios' => ['juan', 'maría'],
    'configuracion' => ['activo' => true, 'max' => 100]
];
$serialized = git_serialize($data_compleja);

// Deserializar datos recuperados
$data_recuperada = git_unserialize($serialized);

// Funciona con todos los tipos de datos
$bool_serializado = git_serialize(true);     // "true"
$int_serializado = git_serialize(42);        // "42"
$null_serializado = git_serialize(null);     // "null"
```

## ⚠️ Errores de Servicio

Muchas funciones del módulo retornan objetos del tipo `ErrorService` para manejar errores de manera consistente. Este enum define los errores más comunes en las transacciones de datos:

```php
enum ErrorService
{
    case NO_ERROR;                          // Sin errores
    case PASSENGER_NOT_APPROVED;            // Pasajero no aprobado
    case ROUTE_NOT_FOUND;                   // Ruta no encontrada
    case TICKET_NOT_FOUND;                  // Ticket no encontrado
    case PASSENGER_NOT_FOUND;               // Pasajero no encontrado
    case TRANSPORT_NOT_FOUND;               // Transporte no encontrado
    case TRANSPORT_NOT_TAKE_ROUTE;          // Transporte no toma esta ruta
    case TRANSPORT_NOT_AVAILABLE;           // Transporte no disponible
    case INVALID_DATE_RANGE;                // Rango de fechas inválido
    case PASSENGERS_PENDING_TRIPS;          // Pasajeros con viajes pendientes
    case TICKET_NOT_FLEXIBLE;               // Ticket no es flexible
    case TRANSPORT_DOES_NOT_TAKE_ROUTE;     // Transporte no cubre esta ruta
}
```

### Manejo de Errores
```php
$resultado = git_passenger_transfer($pasajero, $ruta, $transporte, $fecha);

if ($resultado === true) {
    echo "Transferencia exitosa";
} else {
    // Manejar error específico
    match($resultado) {
        ErrorService::PASSENGER_NOT_FOUND => echo "Pasajero no existe",
        ErrorService::TICKET_NOT_FLEXIBLE => echo "El ticket no permite cambios",
        ErrorService::TRANSPORT_NOT_AVAILABLE => echo "Transporte no disponible",
        ErrorService::ROUTE_NOT_FOUND => echo "Ruta no válida",
        default => echo "Error desconocido"
    };
}
```

## 📚 Referencia Rápida de Funciones

### 🎫 Tickets
```php
git_tickets()                    // Obtener todos los tickets
git_ticket_by_id(int $id)       // Ticket por ID
git_ticket_save(Ticket|array)   // Guardar ticket
git_ticket_create(array)        // Crear ticket

// Método directo en la entidad
$ticket->save()                  // Guardar ticket (retorna bool)
```

### 👥 Pasajeros
```php
git_passengers(array $args)         // Obtener pasajeros con filtros
git_passenger_by_id(int $id)        // Pasajero por ID
git_passenger_save(Passenger|array) // Guardar pasajero
git_passenger_create(array)         // Crear pasajero
git_passenger_transfer(...)          // Transferir pasajero

// Método directo en la entidad
$passenger->save()                   // Guardar pasajero (retorna bool)
```

### 🚐 Transportes
```php
git_transports(array $args)            // Obtener transportes
git_transport_by_id(int $id)           // Transporte por ID
git_transport_save(Transport|array)    // Guardar transporte
git_transport_create(array)            // Crear transporte
git_transport_check_availability(...)  // Verificar disponibilidad
git_transport_set_maintenance(...)     // Programar mantenimiento

// Método directo en la entidad
$transport->save()                      // Guardar transporte (retorna bool)
```

### 🗺️ Rutas y Ubicaciones
```php
git_routes(array $args)          // Obtener rutas
git_route_by_id(int $id)         // Ruta por ID
git_route_save(Route|array)      // Guardar ruta
git_route_create(array)          // Crear ruta

git_locations(array $args)       // Obtener ubicaciones
git_location_by_id(int $id)      // Ubicación por ID
git_location_save(Location|array) // Guardar ubicación
git_location_create(array)       // Crear ubicación

git_zones(array $args)           // Obtener zonas
git_zone_by_id(int $id)          // Zona por ID
git_zone_save(Zone|array)        // Guardar zona (sin método save() directo)
git_zone_create(array)           // Crear zona

// Métodos directos en las entidades
$route->save()                   // Guardar ruta (retorna bool)
$location->save()                // Guardar ubicación (retorna bool)
// Nota: Zone no tiene método save() directo
```

### 🛎️ Servicios y Operadores
```php
git_services(array $args)        // Obtener servicios
git_service_by_id(int $id)       // Servicio por ID
git_service_save(Service|array)  // Guardar servicio
git_service_create(array)        // Crear servicio

git_operators()                  // Obtener operadores
git_operator_by_id(int $id)      // Operador por ID
git_operator_save(Operator)      // Guardar operador

// Métodos directos en las entidades
$service->save()                 // Guardar servicio (retorna bool)
$operator->save()                // Guardar operador (retorna bool)
```

## 🚀 Ejemplos de Uso Avanzado

### Crear Reserva Completa
```php
// 1. Crear pasajeros
$pasajeros = [];
$pasajeros[] = git_passenger_create([
    'name' => 'Ana García',
    'nationality' => 'CO',
    'type_document' => 'CC',
    'data_document' => '987654321',
    'birthday' => '1985-05-15',
    'route_id' => 1,
    'transport_id' => 1,
    'date_trip' => '2026-02-01'
]);

// 2. Crear ticket
$ticket = git_ticket_create([
    'total_amount' => 50000,
    'flexible' => true,
    'order_id' => 123,
    'passengers' => $pasajeros
]);

// 3. Guardar todo
$ticket_guardado = git_ticket_save($ticket);
echo "Reserva #{$ticket_guardado->id} creada exitosamente";
```

### Sistema de Disponibilidad
```php
function verificar_disponibilidad_completa($ruta_id, $fecha, $num_pasajeros) {
    $ruta = git_route_by_id($ruta_id);
    if (!$ruta) return false;
    
    // Obtener transportes que cubren esta ruta
    $transportes = git_transports(['route_id' => $ruta_id]);
    
    foreach ($transportes as $transporte) {
        $disponible = git_transport_check_availability(
            $transporte, 
            $ruta, 
            new Date($fecha), 
            $num_pasajeros
        );
        
        if ($disponible === true) {
            return [
                'disponible' => true,
                'transporte' => $transporte,
                'ruta' => $ruta
            ];
        }
    }
    
    return ['disponible' => false];
}
```

---

## 📝 Notas Importantes

1. **Siempre usar funciones públicas**: No instanciar clases directamente
2. **Métodos de guardado**: Las entidades incluyen método `save()` que retorna boolean, equivalente a las funciones `git_*_save()`
3. **Validar resultados**: Muchas funciones pueden retornar `ErrorService`
4. **Usar metadatos**: Para información flexible y extensible
5. **Manejar fechas**: Utilizar objetos `Date` del módulo
6. **Logs**: Registrar operaciones importantes para auditoría
7. **Zona especial**: La entidad `Zone` no tiene método `save()` directo, usar `git_zone_save()`

## 🔗 Enlaces Útiles

- [Documentación de WordPress](https://developer.wordpress.org/)
- [WooCommerce Docs](https://docs.woocommerce.com/)
- [PHP Manual](https://www.php.net/manual/)

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2026