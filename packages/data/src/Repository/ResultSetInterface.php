<?php
namespace CentralBooking\Data\Repository;

/**
 * @template T
 */
interface ResultSetInterface
{
    /**
     * @return array<T>
     */
    public function getItems(): array;
    public function getItemsPerPage(): int;
    public function getCurrentPage(): int;
    public function getTotalItems(): int;
    public function getTotalPages(): int;
    public function hasItems(): bool;
    public function hasPreviousPage(): bool;
    public function hasNextPage(): bool;
}