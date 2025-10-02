<?php

namespace WpDatabaseHelperV2\Services;

class Assets {
    protected string $version;
    protected string $plugin_dir;
    protected string $plugin_url;

    private static $instance = null;
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->plugin_dir = wp_normalize_path(dirname(__DIR__, 2));
        $this->plugin_url = $this->getPluginUrl($this->plugin_dir);
        $this->version = $this->getVersion();
        add_action('admin_init', [$this, 'enqueue']);
    }

    private function getVersion(): string {
        $composerFile = $this->plugin_dir . '/composer.json';
        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            return $composerData['version'] ?? '0.0.0';
        }
        return '0.0.0';
    }

    private function getPluginUrl(string $path): string {
        $path = wp_normalize_path($path);
        $contentDir = wp_normalize_path(WP_CONTENT_DIR);

        if (strpos($path, $contentDir) === 0) {
            return str_replace($contentDir, WP_CONTENT_URL, $path);
        }

        // fallback: nếu nằm ngoài wp-content (hiếm gặp)
        return content_url(basename($path));
    }

    // register script/style
    public function enqueue(): void {

        // repeater
        wp_enqueue_script(
            'wpdh-repeater',
            "$this->plugin_url/assets/js/repeater.js",
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'wpdh-repeater',
            "$this->plugin_url/assets/css/repeater.css",
            [],
            $this->version
        );

        // meta
        wp_enqueue_script(
            'wpdh-meta',
            "$this->plugin_url/assets/js/meta.js",
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'wpdh-meta',
            "$this->plugin_url/assets/css/meta.css",
            [],
            $this->version
        );

        // field
        wp_enqueue_script(
            'wpdh-field',
            "$this->plugin_url/assets/js/field.js",
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'wpdh-field',
            "$this->plugin_url/assets/css/field.css",
            [],
            $this->version
        );

        wp_localize_script('wpdh', 'Wpdh', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wpdh_nonce'),
            'i18n'     => [],
        ]);
    }
}
