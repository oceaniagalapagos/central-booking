<?php
namespace CentralBooking\Data\Repository;

/**
 * @template T
 */
interface Repository
{
    /**
     * Saves an entity to the repository.
     *
     * @param T $entity The entity to be saved.
     * @return ?T
     */
    public function save($entity);

    /**
     * @param int $id
     * @return bool
     */
    public function exists(int $id);

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id);

    /**
     * @param array $args
     * @return bool
     */
    public function remove_by(array $args = []);

    /**
     * @param array $args
     * @param string $order_by
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return array<T>
     */
    public function find_by(
        array $args = [],
        string $order_by = 'id',
        string $order = 'ASC',
        int $limit = 10,
        int $offset = 0
    );

    /**
     * @param array $args
     * @return ?T
     */
    public function find_first(array $args = []);
}
