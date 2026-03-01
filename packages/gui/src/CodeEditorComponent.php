<?php
namespace CentralBooking\GUI;

class CodeEditorComponent extends TextareaComponent
{
    private string $language = 'javascript';
    private string $theme = 'default';
    private array $options = [];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->id = 'code-editor-' . uniqid();
    }

    // public function setValue(mixed $value)
    // {
    //     parent::setValue(stripslashes($value));
    // }

    public function set_language(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function set_theme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function set_options(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    public function compact(): string
    {
        wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js', [], '5.65.16', true);
        wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css', [], '5.65.16');

        $this->enqueue_language_mode();

        if ($this->theme !== 'default') {
            wp_enqueue_style('codemirror-theme', "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/{$this->theme}.min.css", [], '5.65.16');
        }

        ob_start();
        ?>
        <textarea id="<?= esc_attr($this->id) ?>" name="<?= esc_attr($this->attributes->get('name')) ?>" <?php foreach ($this->attributes->toArray() as $key => $value): ?>             <?php if ($key !== 'name'): ?>                 <?= esc_attr($key) ?>="<?= esc_attr($value) ?>" <?php endif; ?>         <?php endforeach; ?>
            class="<?= implode(' ', $this->class_list->values()) ?>"
            style="width: 100%; height: 300px; font-family: 'Courier New', monospace;"><?= esc_textarea($this->inner_text ?? '') ?></textarea>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof CodeMirror !== 'undefined') {
                    const editor = CodeMirror.fromTextArea(document.getElementById('<?= $this->id ?>'), {
                        mode: '<?= $this->language ?>',
                        theme: '<?= $this->theme ?>',
                        lineNumbers: true,
                        indentWithTabs: true,
                        tabSize: 4,
                        indentUnit: 4,
                        lineWrapping: true,
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        styleActiveLine: true,
                        extraKeys: {
                            "Ctrl-Space": "autocomplete",
                            "Tab": function (cm) {
                                if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                } else {
                                    cm.replaceSelection("    ");
                                }
                            }
                        },
                        <?php foreach ($this->options as $key => $value): ?>
                                                            <?= json_encode($key) ?>: <?= json_encode($value) ?>,
                        <?php endforeach; ?>
                    });

                    editor.setSize("100%", "400px");

                    setTimeout(() => editor.refresh(), 100);
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    private function enqueue_language_mode(): void
    {
        $modes = [
            'javascript' => 'javascript',
            'php' => 'php',
            'css' => 'css',
            'html' => 'htmlmixed',
            'sql' => 'sql',
            'json' => 'javascript',
            'xml' => 'xml',
            'python' => 'python',
            'yaml' => 'yaml'
        ];

        if (isset($modes[$this->language])) {
            wp_enqueue_script(
                "codemirror-mode-{$this->language}",
                "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/{$modes[$this->language]}/{$modes[$this->language]}.min.js",
                ['codemirror'],
                '5.65.16',
                true
            );
        }
    }
}