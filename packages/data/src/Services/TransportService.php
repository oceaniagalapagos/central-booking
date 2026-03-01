<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\TransportORM;
use CentralBooking\Data\Repository\TransportRepository;
use CentralBooking\Data\Transport;
use Exception;

class TransportService
{
    private TransportRepository $repository;
    private ORMInterface $orm;
    public ErrorService $lastError = ErrorService::NO_ERROR;
    private static ?TransportService $instance = null;

    public static function getInstance(): TransportService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $wpdb = $GLOBALS['wpdb'];
        if ($wpdb) {
            $this->repository = new TransportRepository($wpdb);
            $this->orm = new TransportORM();
        } else {
            throw new Exception('Error en la variable gloabl wpdb');
        }
    }

    public function save(Transport $transport)
    {
        // echo json_encode((array)$transport);
        if ($this->validCode($transport) === false) {
            return null;
        }
        if ($this->validNicename($transport) === false) {
            return null;
        }
        return $this->repository->save($transport);
    }

    /**
     * Validates that a transport code is unique in the database.
     * 
     * This method ensures code uniqueness based on different scenarios:
     * - For new transports (id <= 0): verifies the code doesn't exist in database
     * - For existing transports (id > 0): 
     *   - First checks if the transport exists in database
     *   - If transport doesn't exist, validates code uniqueness
     *   - If transport exists, allows the same code or validates uniqueness for new codes
     * 
     * @param Transport $transport Transport object to validate
     * @param string $code Code to validate for uniqueness
     * 
     * @return bool Returns true if code is valid/unique, false otherwise
     * 
     * @since 1.0.0
     */
    private function validCode(Transport $transport): bool
    {
        $currentTransportExists = $this->repository->getTotalCount(['id' => $transport->id]);

        if ($currentTransportExists === 0) {
            $existingCount = $this->repository->getTotalCount(['code' => $transport->code]);
            return $existingCount === 0;
        }

        $currentTransport = $this->find(['id' => $transport->id]);
        if (!$currentTransport->hasItems()) {
            return false;
        }

        $currentTransportData = $currentTransport->getItems()[0];

        if ($currentTransportData->code === $transport->code) {
            return true;
        }

        $existingCount = $this->repository->getTotalCount(['code' => $transport->code]);
        return $existingCount === 0;
    }

    /**
     * Validates that a transport nicename is unique in the database.
     * 
     * This method ensures nicename uniqueness based on different scenarios:
     * - For new transports (id <= 0): verifies the nicename doesn't exist in database
     * - For existing transports (id > 0): 
     *   - First checks if the transport exists in database
     *   - If transport doesn't exist, validates nicename uniqueness
     *   - If transport exists, allows the same nicename or validates uniqueness for new nicenames
     * 
     * @param Transport $transport Transport object to validate
     * @param string $nicename Nicename to validate for uniqueness
     * 
     * @return bool Returns true if nicename is valid/unique, false otherwise
     * 
     * @since 1.0.0
     */
    private function validNicename(Transport $transport): bool
    {
        $currentTransportExists = $this->repository->getTotalCount(['id' => $transport->id]);

        if ($currentTransportExists === 0) {
            $existingCount = $this->repository->getTotalCount(['nicename' => $transport->nicename]);
            return $existingCount === 0;
        }

        $currentTransport = $this->find(['id' => $transport->id]);
        if (!$currentTransport->hasItems()) {
            return false;
        }

        $currentTransportData = $currentTransport->getItems()[0];

        if ($currentTransportData->nicename === $transport->nicename) {
            return true;
        }

        $existingCount = $this->repository->getTotalCount(['nicename' => $transport->nicename]);
        return $existingCount === 0;
    }

    public function find(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0,
    ) {
        return $this->repository->find(
            $this->orm,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
