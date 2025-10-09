<?php

namespace WpDatabaseHelperV2\Fields;

class WpField {
    protected string $id;
    public function getId(){
        return $this->id;
    }

    public function __construct() {
        $this->id = 'id_'.wp_rand();
    }

    //
    public static function make(): self {
        \WpDatabaseHelperV2\Services\Assets::get_instance();
        $inst = new self();
        return $inst;
    }

    // 
    protected string $kind = 'input';
    public function kind($kind): self {
        $this->kind = $kind;
        return $this;
    }
    public function getKind(): string {
        return $this->kind;
    }

    protected string $type = 'number';
    public function type(string $type): self {
        $this->type = $type;
        return $this;
    }
    public function getType(): string {
        return $this->type;
    }

    //
    protected string $name;
    public function name($name): self {
        $this->name = $name;
        return $this;
    }
    public function getName(): string {
        return $this->name;
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
    protected array $attributes = [];
    public function attribute(array $attrs): self {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }
    public function getAttributes(): array {
        return $this->attributes;
    }

    //
    protected mixed $default = '';
    public function default(mixed $v): self {
        $this->default = $v;
        return $this;
    }
    public function getDefault() {
        return $this->default;
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

    //
    protected string $namePrefix = '';
    public function namePrefix(string $v): self {
        $this->namePrefix = $v;
        return $this;
    }
    public function getNamePrefix() {
        return $this->namePrefix;
    }

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

        // chưa được lưu thì load default
        // '' : được lưu rồi thì ko làm gì cả, tôn trọng admin
        if ($dbValue === false) {
            $dbValue = $this->default;
        }

        $fullName = $namePrefix ? "{$namePrefix}[{$this->name}]" : $this->name;
        $fieldId = $this->id . "_" . wp_rand(); // Cần có thêm wp_rand() bởi vì một đối tượng wpField có thể được sử dụng nhiều lần trong repeater

        echo "<div class='wpdh-field wpdh-field-{$this->kind}'>";

        // Label
        echo "<div class='wpdh-field-label'>";
        echo "<label for='{$fieldId}'>{$this->label}</label>";
        echo "</div>"; // .wpdh-field-label

        echo "<div class='wpdh-field-control'>";
        switch ($this->kind) {
            case 'input':
                $val = esc_attr($dbValue ?? '');
                echo "<input id='{$fieldId}' type='{$this->type}' name='{$fullName}' value='{$val}'" . $this->renderAttributes() . ">";
                break;

            case 'textarea':
                $val = esc_textarea($dbValue ?? '');
                echo "<textarea id='{$fieldId}' name='{$fullName}'" . $this->renderAttributes() . ">{$val}</textarea>";
                break;

                // Thêm các kind khác nếu cần
        }
        echo "</div>"; // .wpdh-field-control

        echo "</div>";
        return ob_get_clean();
    }

    protected function renderAttributes(): string {
        // ép buộc luôn có class wpdh-control
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'wpdh-control';
        } else {
            $this->attributes['class'] .= ' wpdh-control';
        }

        $attrs = '';
        foreach ($this->attributes as $k => $v) {
            $attrs .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }
        return $attrs;
    }
}
