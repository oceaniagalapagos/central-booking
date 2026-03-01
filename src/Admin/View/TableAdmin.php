<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\PaginationComponent;

abstract class TableAdmin implements DisplayerInterface
{
    protected function __construct(private bool $add_pagination_controls = true)
    {
    }

    public function render()
    {
        if (empty($this->get_result_set()->has_items)) {
            $this->no_content();
        } else {
            $this->table();
            if ($this->add_pagination_controls) {
                $this->display_pagination_controls();
            }
        }
    }

    private function display_pagination_controls()
    {
        $pagination = new PaginationComponent();
        $pagination->setData(
            $this->get_result_set()->getTotalItems(),
            $this->get_result_set()->getCurrentPage(),
            $this->get_result_set()->getTotalPages(),
        );
        $pagination->setLinks(
            $this->get_pagination_links()['first'],
            $this->get_pagination_links()['last'],
            $this->get_pagination_links()['prev'],
            $this->get_pagination_links()['next'],
        );
    }

    /**
     * @return array{first: string, prev: string, next: string, last: string}
     */
    abstract protected function get_pagination_links();
    /**
     * @return ResultSetInterface
     */
    abstract protected function get_result_set();
    /**
     * @return void
     */
    abstract protected function no_content();
    /**
     * @return void
     */
    abstract protected function table();
}
