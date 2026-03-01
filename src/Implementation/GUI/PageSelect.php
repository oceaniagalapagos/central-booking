<?php
namespace CentralBooking\Implementation\GUI;

final class PageSelect
{
    private array $pages;
    public function __construct(private string $name = 'page', ?int $operator = null)
    {
        $this->pages = $this->get_pages();
    }

    public function create(bool $multiple = false)
    {

        $selectComponent = $multiple ? git_multiselect_field(['name' => $this->name]) : git_select_field(['name' => $this->name]);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->pages as $page) {
            $selectComponent->addOption(
                $page->post_title,
                $page->ID
            );
        }

        return $selectComponent;
    }

    private function get_pages()
    {
        if (isset($this->pages)) {
            return $this->pages;
        }
        return get_pages([
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ]);
    }
}