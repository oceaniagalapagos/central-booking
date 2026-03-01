<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Constants\TypeOperation;

class TypeOperationSelect
{
    public function __construct(private string $name = 'type')
    {
    }

    public function create(bool $multiple = false)
    {
        $container = $multiple ? git_multiselect_field(['name' => $this->name]) : git_select_field(['name' => $this->name]);

        $container->addOption('Seleccione...', '');

        foreach (TypeOperation::cases() as $type) {
            if ($type === TypeOperation::NONE) {
                continue;
            }
            $container->addOption(
                $type->label(),
                $type->slug(),
            );
        }

        return $container;
    }
}
