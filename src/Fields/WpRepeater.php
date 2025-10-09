<?php

namespace WpDatabaseHelperV2\Fields;

class WpRepeater {
    protected string $id;
    public function getId() {
        return $this->id;
    }

    public function __construct() {
        $this->id = 'id_' . wp_rand();
    }

    //
    public static function make(): self {
        \WpDatabaseHelperV2\Services\Assets::get_instance();
        $i = new self();
        return $i;
    }

    // base name of the repeater
    protected string $name;
    public function name(string $name): self {
        $this->name = $name;
        return $this;
    }
    public function getName(): string {
        return $this->name;
    }

    // prefix of name, empty is no prefix
    // only use if it's level 2, see render() func
    protected string $namePrefix = '';
    public function namePrefix(string $v): self {
        $this->namePrefix = $v;
        return $this;
    }
    public function getNamePrefix() {
        return $this->namePrefix;
    }

    //
    protected string $label = '';
    public function label(string $label): self {
        $this->label = $label;
        return $this;
    }
    public function getLabel(): string {
        return $this->label;
    }

    //
    protected array $fields = [];
    public function fields(array $fields): self {
        $this->fields = $fields;
        return $this;
    }
    public function getFields(): array {
        return $this->fields;
    }

    //
    protected array $default = [];
    public function default(array $data): self {
        $this->default = $data;
        return $this;
    }
    public function getDefault() {
        return $this->default;
    }

    //
    protected string $direction = 'vertical';
    public function direction(string $direction): self {
        $this->direction = $direction;
        return $this;
    }

    //
    protected mixed $value = false;
    public function value(mixed $v): self {
        $this->value = $v;
        return $this;
    }
    public function getValue() {
        return $this->value;
    }

    // chỉ dùng để ghi đè default
    protected ?object $parentRepeater = null;
    public function parentRepeater(?object $v): self {
        $this->parentRepeater = $v;
        return $this;
    }
    public function getParentRepeater() {
        return $this->parentRepeater;
    }

    //
    protected bool $adminColumn = false;
    public function adminColumn(bool $enable = true): self {
        $this->adminColumn = $enable;
        return $this;
    }
    public function getAdminColumn(): bool {
        return $this->adminColumn;
    }

    //
    public function render(): string {
        ob_start();

        $dbValue = $this->value;
        $namePrefix = $this->namePrefix;
        $parentRepeater = $this->parentRepeater;

        $fullBase = $namePrefix ? "{$namePrefix}[{$this->name}]" : $this->name;

        echo "<div class='wpdh-repeater' data-base='" . esc_attr($fullBase) . "' data-name='" . esc_attr($this->name) . "'>";

        // label
        echo "<div class='wpdh-repeater-label'>";
        echo "<label>{$this->label}</label>";
        echo "</div>"; // .wpdh-repeater-label

        // items
        echo "<div class='wpdh-repeater-items {$this->direction}'>";
        $items = $dbValue;
        // false: chưa được lưu
        if ($items == false) {
            $items = $this->default;
        }
        // '': đã được lưu và ko có giá trị => load default thay vì empty
        if ($items == '') {
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
                    echo $field
                        ->value($childDbValue)
                        ->namePrefix($itemPrefix)
                        ->parentRepeater($this)
                        ->render();
                }
                // field thường
                else {
                    echo $field
                        ->value($childDbValue)
                        ->namePrefix($itemPrefix)
                        ->render();
                }
            }

            echo "<button type='button' class='button wpdh-clone'>Clone</button>";
            echo "<button type='button' class='button wpdh-up'>Up</button>";
            echo "<button type='button' class='button wpdh-remove'>Remove</button>";
            echo "</div>"; // wpdh-repeater-item
        }
        echo "</div>"; // .wpdh-repeater-items

        if ($this->isLocalHost()) {
            echo "<button type='button' class='button wpdh-debug'>Debug</button>";
        }
        echo "</div>";
        return ob_get_clean();
    }

    // others
    private function isLocalHost() {
        return ($_SERVER['SERVER_ADDR'] ?? '') === '127.0.0.1' || ($_SERVER['HTTP_HOST'] ?? '') === 'localhost';
    }
}
