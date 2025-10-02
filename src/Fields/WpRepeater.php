<?php

namespace WpDatabaseHelperV2\Fields;

class WpRepeater {

    protected string $name;
    public function getName(): string {
        return $this->name;
    }

    protected string $label = '';
    public function label(string $label): self {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public static function make(string $name): self {
        $i = new self();
        $i->name = $name;
        return $i;
    }

    /** @var array|WpField[]|WpRepeater[] */
    protected array $fields = [];
    public function fields(array $fields): self {
        $this->fields = $fields;
        return $this;
    }

    public function getFields(): array {
        return $this->fields;
    }

    /** @var array default data for repeater */
    protected array $default = [];
    public function default(array $data): self {
        $this->default = $data;
        return $this;
    }

    public function getDefault() {
        return $this->default;
    }

    public function setDefault($default): self {
        $this->default = $default;
        return $this;
    }

    // D:\Laragon\www\flatsome\wp-content\plugins\administrator-z\vendor\quyle91\wp-database-helper-v2\src\Fields\WpRepeater.php
    public function render($dbValue, string $namePrefix = '', ?WpRepeater $parentRepeater = null): string {
        ob_start();

        // echo '<pre>'; print_r('Load Repeater: '.$this->name); echo '</pre>';
        // echo '<pre>'; print_r($dbValue); echo '</pre>';

        $repeaterId = esc_attr(wp_rand() . '_' . $this->name);
        $fullBase = $namePrefix ? "{$namePrefix}[{$this->name}]" : $this->name;

        echo "<div class='wpdh-repeater' data-base='" . esc_attr($fullBase) . "' data-name='" . esc_attr($this->name) . "'>";

        // label
        echo "<div class='wpdh-repeater-label'>";
        echo "<label for='{$repeaterId}'>{$this->label}</label>";
        echo "</div>"; // .wpdh-repeater-label

        // xác định items để render
        // load từ db, 
        $items = $dbValue;
        // nếu false thi load default của $this
        if ($items === false) {
            $items = $this->default;
        }

        foreach ($items as $index => $item) {

            $itemPrefix = $namePrefix
                ? "{$namePrefix}[{$this->name}][{$index}]"
                : "{$this->name}[{$index}]";

            echo "<div class='wpdh-repeater-item' data-index='{$index}'>";

            foreach ($this->fields as $field) {
                $childName = $field->getName();
                $childDbValue = isset($item[$childName]) ? $item[$childName] : false;
                
                // luôn luôn override từ parent
                if ($parentRepeater instanceof WpRepeater) {
                    $parentRepeaterDefault = $parentRepeater->getDefault();
                    if (isset($parentRepeaterDefault[$index][$childName])) {
                        $childDbValue = $parentRepeaterDefault[$index][$childName];
                    }
                }

                // nếu là repeater con
                if ($field instanceof WpRepeater) {
                    echo $field->render($childDbValue, $itemPrefix, $this);
                }
                // field thường
                else {
                    echo $field->render($childDbValue, $itemPrefix, $this);
                }
            }

            echo "<button type='button' class='button wpdh-clone'>Clone</button>";
            echo "<button type='button' class='button wpdh-up'>Up</button>";
            echo "<button type='button' class='button wpdh-remove'>Remove</button>";
            echo "</div>"; // wpdh-repeater-item
        }

        echo "<button type='button' class='button wpdh-debug'>Debug</button>";
        echo "</div>";
        return ob_get_clean();
    }
}
