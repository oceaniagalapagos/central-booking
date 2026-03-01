<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Passenger;

final class PassengerCombine
{
    private Passenger $passenger;

    public function __construct(Passenger|array $passenger = [])
    {
        $this->passenger = is_array($passenger)
            ? git_passenger_create($passenger)
            : $passenger;
    }

    public function getNameInput(string $name = 'name')
    {
        $input = git_input_field(['name' => $name]);
        $input->setValue($this->passenger->name);
        $input->setRequired(true);
        return $input;
    }

    public function getTypeDocumentSelect(string $name = 'type_document')
    {
        $select = new TypeDocumentSelect($name);
        $selectComponent = $select->create();
        $selectComponent->setValue($this->passenger->typeDocument);
        $selectComponent->setRequired(true);
        return $selectComponent;
    }

    public function getDataDocumentInput(string $name = 'data_document')
    {
        $input = git_input_field(['name' => $name]);
        $input->setValue($this->passenger->dataDocument);
        $input->setRequired(true);
        return $input;
    }

    public function getBirthdayInput(string $name = 'birthday')
    {
        $input = git_input_field(['name' => $name, 'type' => 'date']);
        $input->setValue($this->passenger->getBirthday()->format('Y-m-d'));
        $input->setRequired(true);
        return $input;
    }

    public function getNationalitySelect(string $name = 'nationality')
    {
        $select = git_country_select_field($name);
        $select->setValue($this->passenger->nationality);
        $select->setRequired(true);
        return $select;
    }
}