<?php
namespace CentralBooking\GUI;

class TableComponent extends BaseComponent
{
    private $rows = [];

    /**
     * @param array<array<ComponentInterface|DisplayerInterface|string>> $header
     * @param array<array<ComponentInterface|DisplayerInterface|string>> $body
     * @param array<array<ComponentInterface|DisplayerInterface|string>> $footer
     */
    public function __construct(array $header = [], array $body = [], array $footer = [], )
    {
        parent::__construct('table');
        $this->rows['header'] = $header;
        $this->rows['body'] = $body;
        $this->rows['footer'] = $footer;
        wp_enqueue_style(
            'git-table-style',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/table-component.css',
            [],
            time(),
        );
    }

    private function addRow(string $section, array $row, ?string $state = null)
    {
        if (!in_array($section, ['header', 'body', 'footer'])) {
            $section = 'body';
        }

        $this->rows[$section][] = $row;

        return $this;
    }

    /**
     * Add a header row
     * 
     * @param array<ComponentInterface|DisplayerInterface|string> $row
     * @return self
     */
    public function addHeaderRow(array $row): self
    {
        return $this->addRow('header', $row);
    }

    /**
     * Add a body row
     * 
     * @param array<ComponentInterface|DisplayerInterface|string> $row
     * @return self
     */
    public function addBodyRow(array $row): self
    {
        return $this->addRow('body', $row);
    }

    /**
     * Add a footer row
     * 
     * @param array<ComponentInterface|DisplayerInterface|string> $row
     * @return self
     */
    public function addFooterRow(array $row): self
    {
        return $this->addRow('footer', $row);
    }

    private function renderContent($cell, $isTH = false)
    {
        if ($cell instanceof TableCellComponent) {
            ob_start();
            $cell->render();
            return ob_get_clean() ?? '';
        }
        $html = $isTH ? "<th>" : "<td>";
        if (is_string($cell)) {
            $html .= esc_html($cell);
        } elseif ($cell instanceof ComponentInterface) {
            $html .= $cell->compact();
        } elseif ($cell instanceof DisplayerInterface) {
            ob_start();
            $cell->render();
            $html .= ob_get_clean() ?? '';
        }
        $html .= $isTH ? "</th>" : "</td>";
        return $html;
    }

    public function compact()
    {
        $html = "<{$this->tag} class=\"git-table\">";
        $html .= "<thead>";
        foreach ($this->rows['header'] as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= $this->renderContent($cell, true);
            }
            $html .= "</tr>";
        }
        $html .= "</thead>";
        $html .= "<tbody>";
        foreach ($this->rows['body'] as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= $this->renderContent($cell, false);
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>";
        $html .= "<tfoot>";
        foreach ($this->rows['footer'] as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= $this->renderContent($cell, false);
            }
            $html .= "</tr>";
        }
        $html .= "</tfoot>";
        $html .= "</{$this->tag}>";
        return $html;
    }
}
