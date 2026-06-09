<?php
/**
 * Plugin Name: My Angers Newsletter
 * Plugin URI: https://my-angers.info
 * Description: Un outil complet de gestion de newsletter pour my-angers.info, incluant la gestion des abonnés, l'automatisation et des statistiques.
 * Version: 1.0.0
 * Author: Jules
 * Text Domain: my-angers-newsletter
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MAN_PATH', plugin_dir_path(__FILE__));
define('MAN_URL', plugin_dir_url(__FILE__));
define('MAN_VERSION', '1.0.0');

// Main class
class MyAngersNewsletter {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        require_once MAN_PATH . 'includes/class-man-db.php';
        require_once MAN_PATH . 'includes/class-man-admin.php';
        require_once MAN_PATH . 'includes/class-man-subscriber.php';
        require_once MAN_PATH . 'includes/class-man-newsletter.php';
        require_once MAN_PATH . 'includes/class-man-automation.php';
        require_once MAN_PATH . 'includes/class-man-frontend.php';
        require_once MAN_PATH . 'includes/class-man-stats.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_filter('wp_mail_from', array($this, 'mail_from'));
        add_filter('wp_mail_from_name', array($this, 'mail_from_name'));
    }

    public function activate() {
        MAN_DB::create_tables();
        if (!wp_next_scheduled('man_daily_digest')) {
            wp_schedule_event(strtotime('18:00:00'), 'daily', 'man_daily_digest');
        }
    }

    public function deactivate() {
        wp_clear_scheduled_hook('man_daily_digest');
    }

    public function mail_from($email) {
        return 'newsletter@my-angers.info';
    }

    public function mail_from_name($name) {
        return 'Angers Info';
    }
}

function run_my_angers_newsletter() {
    return MyAngersNewsletter::get_instance();
}

run_my_angers_newsletter();
