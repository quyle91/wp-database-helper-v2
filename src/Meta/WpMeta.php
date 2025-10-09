<?php

namespace WpDatabaseHelperV2\Meta;

use WpDatabaseHelperV2\Fields\WpField;
use WpDatabaseHelperV2\Fields\WpRepeater;

class WpMeta {
    protected string $id;
    public function getId() {
        return $this->id;
    }

    public function __construct() {
        $this->id = 'id_' . wp_rand();
    }
    
    public static function make(): self {
        \WpDatabaseHelperV2\Services\Assets::get_instance();
        $i = new self();
        return $i;
    }

    protected string $post_type;
    public function post_type(string $post_type): self {
        $this->post_type = $post_type;
        return $this;
    }
    public function getPostType(): string {
        return $this->post_type;
    }

    protected string $metabox_label = '';
    public function metabox_label(string $label): self {
        $this->metabox_label = $label;
        return $this;
    }

    protected array $fields = [];
    public function fields(array $fields): self {
        $this->fields = $fields;
        return $this;
    }

    public function register() {
        // metabox
        add_action('add_meta_boxes', function () {
            add_meta_box(
                "wpdh_{$this->post_type}_metabox",
                $this->metabox_label,
                [$this, 'renderMetaBox'],
                $this->post_type,
                'normal',
                'default'
            );
        });

        // save meta
        add_action('save_post', [$this, 'savePostMeta'], 10, 2);

        // add columns
        add_filter("manage_{$this->post_type}_posts_columns", function ($columns) {
            foreach ($this->fields as $field) {
                if (method_exists($field, 'getAdminColumn') && $field->getAdminColumn()) {
                    $columns[$field->getName()] = $field->getLabel();
                }
            }
            return $columns;
        });

        // render column content
        add_action("manage_{$this->post_type}_posts_custom_column", function ($column, $post_id) {
            foreach ($this->fields as $field) {
                if ($field->getName() === $column && method_exists($field, 'getAdminColumn') && $field->getAdminColumn()) {
                    $value = get_post_meta($post_id, $field->getName(), true);
                    $value = metadata_exists('post', $post_id, $field->getName()) ? $value : false;

                    echo '<div class="wpdh-admin-column" data-post-id="' . esc_attr($post_id) . '" data-field-name="' . esc_attr($field->getName()) . '">';
                    echo '<div class="wpdh-admin-column-control">';

                    // giữ lại if else để dễ debug
                    if ($field instanceof WpRepeater) {
                        echo $field
                            ->value($value)
                            ->namePrefix('') // level 1 then it's ''
                            ->parentRepeater(null)
                            ->render();
                    } else if ($field instanceof WpField) {
                        echo $field
                            ->value($value)
                            ->namePrefix('') // level 1 then it's ''
                            ->render();
                    }
                    echo '</div>';
                    echo '<button type="button" class="button button-primary wpdh-save-meta hidden">Save</button>';
                    echo '<span class="wpdh-saved-status"></span>';
                    echo '</div>';
                }
            }
        }, 10, 2);

        // save
        add_action('wp_ajax_wpdh_save_meta', function () {
            try {
                // Kiểm tra nonce
                if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpdh_nonce')) {
                    throw new \Exception('Invalid nonce');
                }

                $post_id    = intval($_POST['post_id'] ?? 0);
                $field_name = sanitize_text_field($_POST['field_name'] ?? '');
                $field_value = $_POST[$field_name] ?? '';

                if (!$post_id || !$field_name) {
                    throw new \Exception('Invalid data');
                }

                // Cập nhật meta
                $updated = update_post_meta($post_id, $field_name, $field_value);

                if ($updated === false) {
                    throw new \Exception('Failed to update meta');
                }

                wp_send_json_success([
                    'message' => 'Saved successfully',
                    'field'   => $field_name,
                    'post_id' => $post_id,
                    'value'   => $field_value,
                ]);
            } catch (\Throwable $e) {
                wp_send_json_error([
                    'message' => $e->getMessage(),
                    'trace'   => WP_DEBUG ? $e->getTraceAsString() : null, // Chỉ hiển thị trace khi debug bật
                ]);
            }

            wp_die();
        });
    }

    // D:\Laragon\www\flatsome\wp-content\plugins\administrator-z\vendor\quyle91\wp-database-helper-v2\src\Meta\WpMeta.php
    public function renderMetaBox($post) {
        do_action('wpdh_meta_box_before', $post, $this);
        echo '<div class="wpdh-metabox">';
        foreach ($this->fields as $field) {

            // false: chưa được lưu
            // '': đã được lưu và ko có giá trị
            $dbValue = get_post_meta($post->ID, $field->getName(), true);
            $dbValue = metadata_exists('post', $post->ID, $field->getName()) ? $dbValue : false;

            // giữ lại if else để dễ debug
            if ($field instanceof WpRepeater) {
                echo $field
                    ->value($dbValue)
                    ->namePrefix('') // level 1 then it's ''
                    ->parentRepeater(null)
                    ->render();
            } else if ($field instanceof WpField) {
                echo $field
                    ->value($dbValue)
                    ->namePrefix('') // level 1 then it's ''
                    ->render();
            }
        }
        wp_nonce_field('wpdh_save_meta', 'wpdh_nonce');
        echo '</div>';
        do_action('wpdh_meta_box_after', $post, $this);
    }

    // static save handler (registered in bootstrap)
    public function savePostMeta($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (!isset($_POST['wpdh_nonce']) || !wp_verify_nonce($_POST['wpdh_nonce'], 'wpdh_save_meta')) return;

        // get all fields
        foreach ((array)$this->fields as $key => $field) {
            $fieldName = $field->getName();

            // '': đã được lưu và ko có giá trị
            $fieldValue = '';
            if (isset($_POST[$fieldName])) {
                $fieldValue = $_POST[$fieldName];
            }

            // $log = [
            //     'fieldName' => $fieldName,
            //     'fieldValue' => $fieldValue
            // ];
            // error_log(__CLASS__ . '::' . __FUNCTION__ . '() $log: ' . print_r($log, true));
            update_post_meta($post_id, $fieldName, $fieldValue);
        }
    }
}
