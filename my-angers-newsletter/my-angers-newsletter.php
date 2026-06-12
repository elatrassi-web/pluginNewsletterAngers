<?php
/**
 * Plugin Name: My Angers Newsletter
 * Plugin URI: https://my-angers.info
 * Description: Un outil complet de gestion de newsletter pour my-angers.info, incluant la gestion des abonnés, l'automatisation et des statistiques.
 * Version: 1.1.8
 * Author: Jules
 * Text Domain: my-angers-newsletter
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MAN_PATH', plugin_dir_path(__FILE__));
define('MAN_URL', plugin_dir_url(__FILE__));
define('MAN_VERSION', '1.1.8');
define('MAN_DB_VERSION', '1.1.8');

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

        // Auto-update database if version changed
        add_action('plugins_loaded', array($this, 'check_db_update'));
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
    }

    public function check_db_update() {
        $installed_ver = get_option('man_db_version');
        if ($installed_ver !== MAN_DB_VERSION) {
            MAN_DB::create_tables();
            update_option('man_db_version', MAN_DB_VERSION);
        }
    }

    public function activate() {
        MAN_DB::create_tables();
        update_option('man_db_version', MAN_DB_VERSION);

        // Ensure all existing subscribers have an unsubscribe token
        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subs = $wpdb->get_results("SELECT id FROM $table WHERE unsubscribe_token = '' OR unsubscribe_token IS NULL");
        if ($subs) {
            foreach ($subs as $sub) {
                $wpdb->update($table,
                    array('unsubscribe_token' => wp_generate_password(32, false)),
                    array('id' => $sub->id)
                );
            }
        }

        if (!wp_next_scheduled('man_daily_digest')) {
            wp_schedule_event(strtotime('18:00:00'), 'daily', 'man_daily_digest');
        }
    }

    public function deactivate() {
        wp_clear_scheduled_hook('man_daily_digest');
    }

    /**
     * Centralized mail sending to apply custom branding only for newsletter-related emails.
     */
    public static function send_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        $instance = self::get_instance();
        add_filter('wp_mail_from', array($instance, 'custom_mail_from'));
        add_filter('wp_mail_from_name', array($instance, 'custom_mail_from_name'));

        $result = wp_mail($to, $subject, $message, $headers, $attachments);

        remove_filter('wp_mail_from', array($instance, 'custom_mail_from'));
        remove_filter('wp_mail_from_name', array($instance, 'custom_mail_from_name'));

        return $result;
    }

    public function custom_mail_from($email) {
        return 'newsletter@my-angers.info';
    }

    public function custom_mail_from_name($name) {
        return 'Angers Info';
    }
}

function run_my_angers_newsletter() {
    return MyAngersNewsletter::get_instance();
}

run_my_angers_newsletter();
