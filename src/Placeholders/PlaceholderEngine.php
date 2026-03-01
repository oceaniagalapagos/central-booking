<?php
namespace CentralBooking\Placeholders;

use Exception;

class PlaceholderEngine
{
    /**
     * @var array<string, callable>
     */
    private array $placeholders = [];
    private array $descriptions = [];

    public function add_placeholder(string $name, callable $callback)
    {
        $this->placeholders[$name] = $callback;
    }

    public function add_description(string $name, array $description)
    {
        $this->descriptions[$name] = [
            'title' => $description['title'] ?? '',
            'description' => $description['description'] ?? '',
            'parameters' => $description['parameters'] ?? [],
        ];
    }

    public function process(string $text)
    {
        $pattern = '/\[(\w+)([^\]]*)\]/';
        return (string) preg_replace_callback(
            $pattern,
            [$this, 'process_placeholder'],
            stripslashes($text)
        );
    }

    private function process_placeholder(array $matches)
    {
        $name = $matches[1];
        $params_string = trim($matches[2]);
        if (!isset($this->placeholders[$name])) {
            return $matches[0];
        }
        $params = $this->parse_parameters($params_string);
        try {
            $callback = $this->placeholders[$name];
            return call_user_func($callback, $params);
        } catch (Exception $e) {
            return "[Error: {$name}]";
        }
    }

    private function parse_parameters(string $params_string)
    {
        if (empty($params_string)) {
            return [];
        }

        $params = [];

        preg_match_all('/(\w+)=(["\'])(.*?)\2/', $params_string, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $params[$match[1]] = $match[3];
        }

        return $params;
    }

public function get_placeholders_info()
{
    if (empty($this->placeholders)) {
        return '<div class="alert alert-info">No hay placeholders registrados.</div>';
    }

    ksort($this->descriptions);

    ob_start();
    ?>
    <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold;">Placeholder</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold;">Descripción</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold;">Parámetros</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->descriptions as $name => $info): ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px; vertical-align: top;">
                        <code style="background: #f0f0f0; padding: 2px 4px; border-radius: 2px;">[<?= $name ?>]</code>
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; vertical-align: top; color: #666; font-size: 14px;">
                        <h3 class="wp-heading-inline" style="margin: 0;margin-top: 10px;text-decoration: underline;text-underline-offset: 5px;margin-bottom: 10px;">
                            <?= esc_html($info['title']) ?>
                        </h3>
                        <!-- <hr> -->
                        <p style="margin: 0; margin-bottom: 10px;">
                            <?= esc_html($info['description']) ?>
                        </p>
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; vertical-align: top;">
                        <?php if (!empty($info['parameters'])): ?>
                            <ul style="margin: 0; padding-left: 15px;">
                                <?php foreach ($info['parameters'] as $param): ?>
                                    <li style="margin-bottom: 5px;">
                                        <code style="background: #f0f0f0; padding: 1px 4px; font-size: 12px;"><?= esc_html($param['param']) ?></code>
                                        <?php if (isset($param['values'])): ?>
                                            <ul style="margin: 2px 0 0 10px; font-size: 12px;">
                                                <?php foreach ($param['values'] as $value): ?>
                                                    <li style="color: #777; margin-bottom: 2px;">
                                                        <code style="font-size: 11px;"><?= esc_html($value['value']) ?></code> - <?= esc_html($value['description']) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">Sin parámetros</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return git_string_to_component(ob_get_clean());
}
}