<?php
namespace CentralBooking\Implementation\GUI;

final class TypeDocumentSelect
{
    private static array $types;

    public function __construct(private string $name = 'type_document')
    {
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple ? git_multiselect_field(['name' => $this->name]) : git_select_field(['name' => $this->name]);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->get_documents() as $document) {
            $selectComponent->addOption($document, $document);
        }

        return $selectComponent;
    }

    private function get_documents()
    {
        if (isset(self::$types)) {
            return self::$types;
        }

        $jsonFilePath = CENTRAL_BOOKING_DIR . 'assets/data/documents.json';
        $jsonString = file_get_contents($jsonFilePath);

        if ($jsonString === false) {
            return [];
        }

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        self::$types = $data;

        return self::$types;
    }
}
