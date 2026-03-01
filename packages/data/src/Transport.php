<?php
/**
 * Transport Management Class
 * 
 * This file contains the Transport class which represents a transportation
 * vehicle/vessel within the Central Booking system. It manages transport
 * entities including boats, buses, planes, and other transportation methods
 * along with their associated services, routes, operators, and metadata.
 * 
 * @package CentralBooking\Data
 * @since 1.0.0
 * @author Central Booking Team
 */

namespace CentralBooking\Data;

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TransportCustomeFieldConstants;
use CentralBooking\Data\Repository\LazyLoader;
use CentralBooking\Data\Services\ErrorService;

/**
 * Transport represents a transportation vehicle or vessel in the booking system.
 * 
 * This class manages all aspects of transportation entities including marine vessels,
 * land vehicles, aircraft, etc. It handles relationships with operators, routes,
 * services, crew information, availability schedules, and maintenance periods.
 * The class uses lazy loading for performance optimization and metadata management
 * for flexible custom attributes.
 * 
 * @package CentralBooking\Data
 * @since 1.0.0
 * 
 * @example
 * $transport = new Transport();
 * $transport->id = 1;
 * $transport->code = 'BOAT001';
 * $transport->nicename = 'Island Hopper';
 * $transport->type = TransportConstants::MARINE;
 * $transport->setCapacity(50);
 * $transport->setCaptain('Captain Jack Sparrow');
 */
class Transport
{
    /** @var int Unique identifier for the transport */
    public int $id = 0;

    /** @var string Unique code/identifier for the transport (e.g., 'BOAT001', 'BUS-A12') */
    public string $code = '';

    /** @var string Human-readable name for the transport */
    public string $nicename = '';

    /** @var TypeOperation Type of transportation (marine, land, air, etc.) */
    public TypeOperation $type = TypeOperation::MARINE;

    /** @var array<string, mixed> Internal metadata storage for custom attributes */
    private array $metadata = [];

    /** @var array<Service> Available services offered by this transport */
    private array $services;

    /** @var array<Route> Routes that this transport operates on */
    private array $routes;

    /** @var Operator The operator/company that manages this transport */
    private Operator $operator;
    private bool $routesLoaded = false;
    private bool $servicesLoaded = false;

    /**
     * Retrieves all services offered by this transport.
     * 
     * Uses lazy loading to fetch services from the database only when needed.
     * Returns empty array if transport has no ID (unsaved transport).
     * 
     * @return array<Service> Array of Service objects associated with this transport
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport = new Transport();
     * $transport->id = 1;
     * $services = $transport->getServices();
     * foreach ($services as $service) {
     *     echo $service->name;
     * }
     */
    public function getServices()
    {
        if ($this->servicesLoaded === false) {
            $this->services = LazyLoader::loadServicesByTransport($this);
            $this->servicesLoaded = true;
        }
        return $this->services;
    }

    /**
     * Sets the services array for this transport.
     * 
     * Allows manual assignment of services without database lookup.
     * Useful for pre-loaded data or testing scenarios.
     * 
     * @param array<Service> $services Array of Service objects to assign
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $services = [$service1, $service2, $service3];
     * $transport->setServices($services);
     */
    public function setServices(array $services)
    {
        $this->services = $services;
        $this->servicesLoaded = true;
    }

    /**
     * Retrieves the operator/company that manages this transport.
     * 
     * Uses lazy loading to fetch operator data from the database only when needed.
     * 
     * @return Operator The operator object associated with this transport
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport = new Transport();
     * $transport->id = 1;
     * $operator = $transport->getOperator();
     * echo $operator->name; // "Island Transport Co."
     */
    public function getOperator()
    {
        if (!isset($this->operator)) {
            $this->operator = LazyLoader::loadOperatorByTransport($this);
        }
        return $this->operator;
    }

    /**
     * Retrieves the crew information for this transport.
     * 
     * Returns an array of crew members with their details including
     * name, contact information, role, and license information.
     * 
     * @return array<array{name:string,contact:string,role:string,license:string}>
     *         Array of crew member details
     * 
     * @since 1.0.0
     * 
     * @example
     * $crew = $transport->getCrew();
     * foreach ($crew as $member) {
     *     echo "{$member['name']} - {$member['role']}\n";
     * }
     */
    public function getCrew()
    {
        return $this->getMeta('crew') ?? [];
    }

    /**
     * Sets the crew information for this transport.
     * 
     * Updates the crew roster with complete member information.
     * Each crew member should include name, contact, role, and license data.
     * 
     * @param array<array{name:string,contact:string,role:string,license:string}> $crew
     *        Array of crew member details
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $crew = [
     *     [
     *         'name' => 'Captain Jack',
     *         'contact' => '+1-555-0123',
     *         'role' => 'Captain',
     *         'license' => 'CAPT001'
     *     ],
     *     [
     *         'name' => 'First Mate Jane',
     *         'contact' => '+1-555-0124',
     *         'role' => 'First Mate',
     *         'license' => 'FM002'
     *     ]
     * ];
     * $transport->setCrew($crew);
     */
    public function setCrew(array $crew)
    {
        $this->setMeta('crew', $crew);
    }

    /**
     * Retrieves the alias names for this transport.
     * 
     * Returns an array of alternative names or nicknames that this
     * transport is known by. Useful for search and identification.
     * 
     * @return array<string> Array of alias names
     * 
     * @since 1.0.0
     * 
     * @example
     * $aliases = $transport->getAlias();
     * // ["The Blue Pearl", "BP-001", "Pearl"]
     */
    public function getAlias()
    {
        return $this->getMeta('alias') ?? [];
    }

    /**
     * Sets the alias names for this transport.
     * 
     * Assigns alternative names or nicknames that this transport
     * is known by. Helpful for search functionality and user recognition.
     * 
     * @param array<string> $alias Array of alias names
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setAlias(["The Blue Pearl", "BP-001", "Pearl"]);
     */
    public function setAlias(array $alias)
    {
        $this->setMeta('alias', $alias);
    }

    /**
     * Sets the routes that this transport operates on.
     * 
     * Assigns the routes collection without database interaction.
     * Useful for pre-loaded data or testing scenarios.
     * 
     * @param array<Route> $routes Array of Route objects
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $routes = [$route1, $route2, $route3];
     * $transport->setRoutes($routes);
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        $this->routesLoaded = true;
    }

    /**
     * Retrieves all routes that this transport operates on.
     * 
     * Uses lazy loading to fetch routes from the database only when needed.
     * Returns empty array if transport has no ID (unsaved transport).
     * 
     * @return array<Route> Array of Route objects
     * 
     * @since 1.0.0
     * 
     * @example
     * $routes = $transport->getRoutes();
     * foreach ($routes as $route) {
     *     echo "From {$route->origin} to {$route->destination}\n";
     * }
     */
    public function getRoutes()
    {
        if ($this->routesLoaded === false) {
            $this->routes = LazyLoader::loadRoutesByTransport($this);
            $this->routesLoaded = true;
        }
        return $this->routes;
    }

    /**
     * Checks if this transport operates on a specific route.
     * 
     * Determines whether the given route is among the routes that
     * this transport is assigned to operate on.
     * 
     * @param Route $route The route to check for assignment
     * 
     * @return bool True if transport operates on this route, false otherwise
     * 
     * @since 1.0.0
     * 
     * @example
     * if ($transport->takeRoute($selectedRoute)) {
     *     echo "This transport operates on the selected route";
     * } else {
     *     echo "This transport doesn't operate on this route";
     * }
     */
    public function takeRoute(Route $route)
    {
        foreach ($this->getRoutes() as $assignedRoute) {
            if ($assignedRoute->id === $route->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves a specific metadata value for this transport.
     * 
     * Gets custom metadata by key with lazy loading from the database.
     * Caches the value locally to avoid repeated database queries.
     * 
     * @param string $key The metadata key to retrieve
     * 
     * @return mixed The metadata value or null if not found
     * 
     * @since 1.0.0
     * 
     * @example
     * $capacity = $transport->getMeta('capacity');
     * $maintenanceNotes = $transport->getMeta('maintenance_notes');
     */
    public function getMeta(string $key)
    {
        if (!isset($this->metadata[$key])) {
            $metaValue = MetaManager::getMeta(
                MetaManager::TRANSPORT,
                $this->id,
                $key
            );
            $this->metadata[$key] = $metaValue;
        }
        return $this->metadata[$key] ?? null;
    }

    /**
     * Sets a metadata value for this transport.
     * 
     * Stores custom metadata locally. Call saveMeta() to persist to database.
     * 
     * @param string $key The metadata key
     * @param mixed $value The value to store
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setMeta('last_inspection', '2026-01-01');
     * $transport->setMeta('fuel_type', 'diesel');
     * $transport->saveMeta(); // Persist to database
     */
    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Persists all metadata changes to the database.
     * 
     * Saves all locally stored metadata to the database.
     * Should be called after making metadata changes with setMeta().
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setMeta('capacity', 50);
     * $transport->setMeta('fuel_type', 'diesel');
     * $transport->saveMeta(); // Saves both changes to database
     */
    public function saveMeta()
    {
        MetaManager::setMetadata(
            MetaManager::TRANSPORT,
            $this->id,
            $this->metadata
        );
    }

    /**
     * Sets the operator for this transport.
     * 
     * Assigns the operator/company that manages this transport.
     * 
     * @param Operator $operator The operator to assign
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $operator = new Operator();
     * $operator->name = "Island Transport Co.";
     * $transport->setOperator($operator);
     */
    public function setOperator(Operator $operator)
    {
        $this->operator = $operator;
    }

    /**
     * Checks if the transport is available on a specific date.
     * 
     * Determines availability based on maintenance schedule and working days.
     * Returns false if the date falls within maintenance period or outside
     * configured working days.
     * 
     * @param Date|null $date The date to check availability for (defaults to today)
     * 
     * @return bool True if transport is available, false otherwise
     * 
     * @since 1.0.0
     * 
     * @example
     * // Check if available today
     * if ($transport->isAvailable()) {
     *     echo "Transport is available today";
     * }
     * 
     * // Check specific date
     * $specificDate = new Date('2026-01-15');
     * if ($transport->isAvailable($specificDate)) {
     *     echo "Transport is available on January 15th";
     * }
     */
    public function isAvailable(?Date $date = null): bool
    {
        $date ??= Date::today();
        $maintanceDates = $this->getMaintenanceDates();

        if (
            ($maintanceDates['date_start'] ?? null) &&
            ($maintanceDates['date_end'] ?? null) &&
            $date->format() >= $maintanceDates['date_start'] &&
            $date->format() <= $maintanceDates['date_end']
        ) {
            return false;
        }

        $workingDays = $this->getWorkingDays();
        if (empty($workingDays)) {
            return true;
        }

        $dayOfWeek = strtolower($date->format('l'));

        if (!in_array($dayOfWeek, $workingDays, true)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the working days configuration for this transport.
     * 
     * Returns an array of weekday names when the transport is operational.
     * Empty array means the transport works all days.
     * 
     * @return array<string> Array of working day names (e.g., ['monday', 'tuesday', ...])
     * 
     * @since 1.0.0
     * 
     * @example
     * $workingDays = $transport->getWorkingDays();
     * // ["monday", "tuesday", "wednesday", "thursday", "friday"]
     */
    public function getWorkingDays(): array
    {
        return $this->getMeta('working_days') ?? [];
    }

    /**
     * Sets the working days for this transport.
     * 
     * Configures which days of the week the transport is operational.
     * Automatically validates and normalizes day names to lowercase.
     * Removes duplicates and invalid day names.
     * 
     * @param array<string> $days Array of day names (case-insensitive)
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * // Set weekdays only
     * $transport->setWorkingDays(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
     * 
     * // Set all days
     * $transport->setWorkingDays(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
     */
    public function setWorkingDays(array $days)
    {
        $validDays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday'
        ];
        $daysToSaved = [];
        foreach ($days as $day) {
            $dayLower = strtolower($day);
            if (
                in_array($dayLower, $validDays, true) &&
                !in_array($dayLower, $daysToSaved, true)
            ) {
                $daysToSaved[] = $dayLower;
            }
        }
        $this->setMeta('working_days', $daysToSaved);
    }

    /**
     * Sets the maintenance period for this transport.
     * 
     * Defines the start and end dates when the transport will be
     * unavailable due to maintenance or repairs.
     * 
     * @param Date $date_start Start date of maintenance period
     * @param Date $date_end End date of maintenance period
     * 
     * @return bool|ErrorService
     * 
     * @since 1.0.0
     * 
     * @example
     * $start = new DateTime('2026-02-01');
     * $end = new DateTime('2026-02-07');
     * $transport->setMaintenanceDates($start, $end);
     */
    public function setMaintenanceDates(Date $date_start, Date $date_end)
    {
        if ($date_end->format('Y-m-d') < $date_start->format('Y-m-d')) {
            return ErrorService::INVALID_DATE_RANGE;
        }
        $passengers = git_passengers([
            'id_transport' => $this->id,
            'date_trip_from' => $date_start->format('Y-m-d'),
            'date_trip_to' => $date_end->format('Y-m-d'),
            'served' => false,
            'approved' => true,
        ]);
        if (count($passengers) > 0) {
            return ErrorService::PASSENGERS_PENDING_TRIPS;
        }
        $this->setMeta('maintenance_dates', [
            'date_start' => $date_start->format('Y-m-d'),
            'date_end' => $date_end->format('Y-m-d')
        ]);
        return true;
    }

    /**
     * Retrieves the maintenance period dates.
     * 
     * Returns the configured maintenance period with start and end dates.
     * Empty strings indicate no maintenance period is set.
     * 
     * @return array{date_start: string, date_end: string} Maintenance dates in Y-m-d format
     * 
     * @since 1.0.0
     * 
     * @example
     * $maintenanceDates = $transport->getMaintenanceDates();
     * echo "Maintenance from {$maintenanceDates['date_start']} to {$maintenanceDates['date_end']}";
     */
    public function getMaintenanceDates()
    {
        return $this->getMeta('maintenance_dates') ?? [
            'date_start' => '',
            'date_end' => '',
        ];
    }

    /**
     * Retrieves the passenger/cargo capacity of this transport.
     * 
     * Returns the maximum number of passengers or cargo units
     * that this transport can accommodate.
     * 
     * @return int The capacity number (0 if not set)
     * 
     * @since 1.0.0
     * 
     * @example
     * $capacity = $transport->getCapacity();
     * echo "This transport can accommodate {$capacity} passengers";
     */
    public function getCapacity(): int
    {
        return (int) ($this->getMeta('capacity') ?? 0);
    }

    /**
     * Retrieves the captain/driver name for this transport.
     * 
     * Returns the name of the person in charge of operating this transport.
     * 
     * @return string The captain/driver name (empty string if not set)
     * 
     * @since 1.0.0
     * 
     * @example
     * $captain = $transport->getCaptain();
     * echo "Captain: {$captain}";
     */
    public function getCaptain(): string
    {
        return (string) ($this->getMeta('captain') ?? '');
    }

    /**
     * Sets the captain/driver name for this transport.
     * 
     * Assigns the name of the person responsible for operating this transport.
     * 
     * @param string $captain The captain/driver name
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setCaptain('Captain Jack Sparrow');
     */
    public function setCaptain(string $captain)
    {
        $this->setMeta('captain', $captain);
    }

    /**
     * Sets the passenger/cargo capacity of this transport.
     * 
     * Defines the maximum number of passengers or cargo units
     * that this transport can safely accommodate.
     * 
     * @param int $capacity The capacity number (must be positive)
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setCapacity(50); // 50 passengers
     * $transport->setCapacity(120); // 120 cargo units
     */
    public function setCapacity(int $capacity)
    {
        $this->setMeta('capacity', $capacity);
    }

    /**
     * Retrieves the custom field configuration for this transport.
     * 
     * Returns the custom field content and field type. Custom fields
     * allow storing additional flexible information about the transport.
     * 
     * @return array{content: string, field: TransportCustomeFieldConstants}
     *         Array containing field content and field type
     * 
     * @since 1.0.0
     * 
     * @example
     * $customField = $transport->getCustomField();
     * echo "Field Type: {$customField['field']->value}";
     * echo "Content: {$customField['content']}";
     */
    public function getCustomField()
    {
        $meta = $this->getMeta('custome_field') ?? [];
        return [
            'content' => $meta['content'] ?? '',
            'field' => TransportCustomeFieldConstants::from(
                $meta['field'] ?? TransportCustomeFieldConstants::TEXT->value
            ),
        ];
    }

    /**
     * Sets the custom field configuration for this transport.
     * 
     * Configures a custom field with specific content and field type.
     * Useful for storing transport-specific information that doesn't
     * fit into standard fields.
     * 
     * @param string $content The content/value for the custom field
     * @param TransportCustomeFieldConstants $field The type of custom field
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setCustomField(
     *     'Special equipment: Life jackets, snorkeling gear',
     *     TransportCustomeFieldConstants::TEXT
     * );
     */
    public function setCustomField(string $content, TransportCustomeFieldConstants $field)
    {
        $this->setMeta('custome_field', [
            'field' => $field->value,
            'content' => $content
        ]);
    }

    /**
     * Retrieves the photo URL for this transport.
     * 
     * Returns the URL of the main photo/image representing this transport.
     * 
     * @return string The photo URL (empty string if not set)
     * 
     * @since 1.0.0
     * 
     * @example
     * $photoUrl = $transport->getUrlPhoto();
     * if (!empty($photoUrl)) {
     *     echo "<img src='{$photoUrl}' alt='Transport Photo'>";
     * }
     */
    public function getUrlPhoto()
    {
        return $this->getMeta('photo_url') ?? '';
    }

    /**
     * Sets the photo URL for this transport.
     * 
     * Assigns the URL of the main photo/image representing this transport.
     * Should be a valid URL pointing to an accessible image.
     * 
     * @param string $url The photo URL
     * 
     * @return void
     * 
     * @since 1.0.0
     * 
     * @example
     * $transport->setUrlPhoto('https://example.com/images/transport_001.jpg');
     */
    public function setUrlPhoto(string $url)
    {
        $this->setMeta('photo_url', $url);
    }

    public function checkAvaility(Route $route, Date $dateTrip, int $passengersCount = 1)
    {
        if ($this->takeRoute($route) === false) {
            return ErrorService::TRANSPORT_DOES_NOT_TAKE_ROUTE;
        }
        if ($this->isAvailable($dateTrip) === false) {
            return ErrorService::TRANSPORT_NOT_AVAILABLE;
        }
        $passengers = git_passengers([
            'id_route' => $route->id,
            'id_transport' => $this->id,
            'date_trip' => $dateTrip->format('Y-m-d'),
            'served' => false,
            'approved' => true,
        ]);
        $capacity = $this->getCapacity();
        if (count($passengers) + $passengersCount > $capacity) {
            return false;
        }
        return true;
    }

    public function save()
    {
        return git_transport_save($this) !== false;
    }
}
