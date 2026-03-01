<?php
namespace CentralBooking\Data\Repository;

/**
 * @template T
 */
interface RepositoryInterface
{
    /**
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
     * @param array $args
     * @param string $order_by
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<T>
     */
    public function findBy(
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
    public function findFirst(array $args = []);
}
