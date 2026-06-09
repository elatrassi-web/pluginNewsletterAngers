<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function add_menu_pages() {
        add_menu_page(
            'Angers Newsletter',
            'Newsletter',
            'manage_options',
            'man-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'man-dashboard',
            'Tableau de Bord',
            'Tableau de Bord',
            'manage_options',
            'man-dashboard',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'man-dashboard',
            'Abonnés',
            'Abonnés',
            'manage_options',
            'man-subscribers',
            array($this, 'render_subscribers')
        );

        add_submenu_page(
            'man-dashboard',
            'Campagnes',
            'Campagnes',
            'manage_options',
            'man-campaigns',
            array($this, 'render_campaigns')
        );

        add_submenu_page(
            'man-dashboard',
            'Réglages',
            'Réglages',
            'manage_options',
            'man-settings',
            array($this, 'render_settings')
        );
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'man-') === false) {
            return;
        }

        wp_enqueue_style('man-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('man-admin-style', MAN_URL . 'assets/css/admin-style.css', array(), MAN_VERSION);
        wp_enqueue_script('man-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), MAN_VERSION, true);
        wp_enqueue_script('man-admin-script', MAN_URL . 'assets/js/admin-script.js', array('jquery'), MAN_VERSION, true);

        wp_localize_script('man-admin-script', 'man_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('man_admin_nonce')
        ));
    }

    public function render_dashboard() {
        include MAN_PATH . 'templates/admin-dashboard.php';
    }

    public function render_subscribers() {
        include MAN_PATH . 'templates/admin-subscribers.php';
    }

    public function render_campaigns() {
        include MAN_PATH . 'templates/admin-campaigns.php';
    }

    public function render_settings() {
        include MAN_PATH . 'templates/admin-settings.php';
    }
}

new MAN_Admin();
