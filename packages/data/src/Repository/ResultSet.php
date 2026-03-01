<?php
namespace CentralBooking\Data\Repository;

/**
 * @template T
 * @implements ResultSetInterface<T>
 */
final class ResultSet implements ResultSetInterface
{
    /**
     * @param array<T> $items
     * @param int $itemsPerPage
     * @param int $currentPage
     * @param int $totalItems
     * @param int $totalPages
     * @param bool $hasItems
     * @param bool $hasPrevPage
     * @param bool $hasNextPage
     */
    public function __construct(
        private readonly array $items,
        private readonly int $itemsPerPage,
        private readonly int $currentPage,
        private readonly int $totalItems,
        private readonly int $totalPages,
        private readonly bool $hasItems,
        private readonly bool $hasPrevPage,
        private readonly bool $hasNextPage,
    ) {
    }

    public function getItems(): array
    {
        return $this->items;
    }
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
    public function hasItems(): bool
    {
        return $this->hasItems;
    }
    public function hasPreviousPage(): bool
    {
        return $this->hasPrevPage;
    }
    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }
}
