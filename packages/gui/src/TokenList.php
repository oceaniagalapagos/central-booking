<?php
namespace CentralBooking\GUI;

use Exception;

/**
 * Class ArrayAccess
 *
 * A simple class to store, check, add, and remove values from a private array.
 * It enforces that values do not contain spaces and prevents duplicates.
 */
class TokenList
{
    /**
     * @var array<string> Stores values in a private array
     */
    private array $array = [];

    /**
     * Checks if a value exists in the array
     *
     * @param mixed $search The value to look for
     * @return bool `true` if found, `false` otherwise
     */
    public function contains($search)
    {
        return in_array($search, $this->array);
    }

    /**
     * Adds multiple values to the array if they do not contain spaces and are not duplicates
     *
     * @param array<string> $values List of values to add
     * @throws Exception If a value contains spaces
     * @return void
     */
    public function add(...$values)
    {
        foreach ($values as $value) {
            // Check if the value contains spaces
            if (str_contains($value, ' ')) {
                throw new Exception("Error Processing Request"); // Throws an exception if spaces are detected
            }
            // Add value if it does not already exist
            if (!isset($this->array[$value])) {
                $this->array[] = $value;
            }
        }
    }

    /**
     * Removes a value from the array if it exists
     *
     * @param string $value Value to remove
     * @return void
     */
    public function remove(string $value)
    {
        // Find the index of the value before removing it
        $index = array_search($value, $this->array);
        if ($index !== false) {
            unset($this->array[$index]);
        }
    }

    /**
     * Retrieves all stored values in the array
     *
     * @return array<string> List of stored values
     */
    public function values()
    {
        return $this->array;
    }

    /**
     * Returns the total count of elements in the array
     *
     * @return int Number of stored values
     */
    public function length()
    {
        return sizeof($this->array);
    }
}