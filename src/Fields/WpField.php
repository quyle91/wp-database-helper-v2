<?php

namespace WpDatabaseHelperV2\Fields;

class WpField {
    protected string $type;
    protected string $name;

    public static function make(string $type, string $name): self {
        $inst = new self();
        $inst->type = $type;
        $inst->name = $name;
        return $inst;
    }

    public function getType(): string {
        return $this->type;
    }

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

    protected array $attributes = [];
    public function attribute(array $attrs): self {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }

    /** @var mixed */
    protected $default = '';
    public function default($v): self {
        $this->default = $v;
        return $this;
    }

    public function getDefault() {
        return $this->default;
    }

    // D:\Laragon\www\flatsome\wp-content\plugins\administrator-z\vendor\quyle91\wp-database-helper-v2\src\Fields\WpField.php
    public function render($dbValue, string $namePrefix = ''): string {
        ob_start();
        
        // dbValue false mean metadata_exists() = false -> get from default
        if($dbValue === false) {
            $dbValue = $this->default;
        }

        $fullName = $namePrefix ? "{$namePrefix}[{$this->name}]" : $this->name;
        $fieldId = esc_attr(wp_rand() . '_' . $this->name);

        echo "<div class='wpdh-field wpdh-field-{$this->type}'>";

        // Label
        echo "<div class='wpdh-field-label'>";
        echo "<label for='{$fieldId}'>{$this->label}</label>";
        echo "</div>"; // .wpdh-field-label

        echo "<div class='wpdh-field-control'>";
        switch ($this->type) {
            case 'text':
                $val = esc_attr($dbValue ?? '');
                echo "<input type='text' id='{$fieldId}' name='{$fullName}' value='{$val}'" . $this->renderAttributes() . ">";
                break;

            case 'textarea':
                $val = esc_textarea($dbValue ?? '');
                echo "<textarea id='{$fieldId}' name='{$fullName}'" . $this->renderAttributes() . ">{$val}</textarea>";
                break;

                // Thêm các type khác nếu cần
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

    // for wpmeta
    protected bool $adminColumn = false;
    public function adminColumn(bool $enable = true): self {
        $this->adminColumn = $enable;
        return $this;
    }

    public function isAdminColumn(): bool {
        return $this->adminColumn;
    }
}
