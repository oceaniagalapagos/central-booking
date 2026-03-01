<?php
namespace CentralBooking\GUI;

class TableCellComponent implements DisplayerInterface
{
    private function __construct(
        private readonly ComponentInterface|DisplayerInterface|string $content,
        private readonly int $colspan = 1,
        private readonly int $rowspan = 1
    ) {
    }

    public function render()
    {
        $attributes = '';
        if ($this->colspan > 1) {
            $attributes .= " colspan=\"{$this->colspan}\"";
        }
        if ($this->rowspan > 1) {
            $attributes .= " rowspan=\"{$this->rowspan}\"";
        }

        $html = "<td{$attributes}>";

        if (is_string($this->content)) {
            $html .= $this->content;
        } elseif ($this->content instanceof DisplayerInterface) {
            ob_start();
            $this->content->render();
            $html .= ob_get_clean() ?? '';
        } elseif ($this->content instanceof ComponentInterface) {
            $html .= $this->content->compact();
        }

        $html .= "</td>";

        echo $html;
    }

    public static function instance(
        ComponentInterface|DisplayerInterface|string $content,
        int $colspan = 1,
        int $rowspan = 1
    ): self {
        return new self($content, $colspan, $rowspan);
    }
}
