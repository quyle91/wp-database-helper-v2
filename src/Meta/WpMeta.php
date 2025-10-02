<?php

namespace WpDatabaseHelperV2\Meta;

use WpDatabaseHelperV2\Fields\WpField;
use WpDatabaseHelperV2\Fields\WpRepeater;

class WpMeta {
    protected string $post_type;
    protected string $metabox_label = '';
    protected array $fields = [];

    public static function make(string $post_type): self {
        $i = new self();
        $i->post_type = $post_type;
        return $i;
    }

    public function metabox(string $label): self {
        $this->metabox_label = $label;
        return $this;
    }
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
        add_action('save_post', function ($post_id, $post) {
            self::savePostMeta($post_id, $post, $this->fields);
        }, 10, 2);
    }

    // D:\Laragon\www\flatsome\wp-content\plugins\administrator-z\vendor\quyle91\wp-database-helper-v2\src\Meta\WpMeta.php
    public function renderMetaBox($post) {
        echo '<div class="wpdh-metabox">';
        foreach ($this->fields as $field) {

            // pass value = '' or false, false mean not exists
            $dbValue = get_post_meta($post->ID, $field->getName(), true);
            $dbValue = metadata_exists('post', $post->ID, $field->getName()) ? $dbValue : false;
            
            // giữ lại if else để dễ debug
            if ($field instanceof WpRepeater) {
                echo $field->render($dbValue, '', null);
            } else if ($field instanceof WpField) {
                echo $field->render($dbValue, '', null);
            }
        }
        wp_nonce_field('wpdh_save_meta', 'wpdh_nonce');
        echo '</div>';
    }

    // static save handler (registered in bootstrap)
    public static function savePostMeta($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (!isset($_POST['wpdh_nonce']) || !wp_verify_nonce($_POST['wpdh_nonce'], 'wpdh_save_meta')) return;

        // Here you should map expected fields. For demo we will loop $_POST keys prefixed by known field names.
        // In real project, store the schema somewhere to know which keys to save.
        // $allowed_keys = array_keys((array) get_post_meta($post_id)); // naive, replace with schema
        // Simpler: check posted data and save them
        foreach ($_POST as $key => $value) {
            if (in_array($key, ['post_title', 'post_content', 'action', 'wpdh_nonce', '_wpnonce', '_wp_http_referer'])) continue;
            // Save arrays (repeater) or scalar
            if (is_array($value)) {
                update_post_meta($post_id, $key, $value);
            } else {
                update_post_meta($post_id, $key, sanitize_text_field($value));
            }
        }
    }
}
