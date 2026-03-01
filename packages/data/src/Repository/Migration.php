<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\ExportData;
use CentralBooking\Data\ImportData;

defined('ABSPATH') || exit;

final class Migration
{
    public function get_data(bool $settings, bool $entities, bool $products)
    {
        $exporter = new ExportData();
        return $exporter->export([
            'settings' => $settings,
            'entities' => $entities,
            'products' => $products,
        ]);
    }

    public function set_data(array $data)
    {
        $importer = new ImportData();
        $importer->import($data);
    }
}
