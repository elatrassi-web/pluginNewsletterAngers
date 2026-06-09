<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Subscriber {
    public function __construct() {
        add_action('wp_ajax_man_add_subscriber', array($this, 'ajax_add_subscriber'));
        add_action('wp_ajax_nopriv_man_add_subscriber', array($this, 'ajax_add_subscriber'));
        add_action('wp_ajax_man_delete_subscriber', array($this, 'ajax_delete_subscriber'));
        add_action('admin_init', array($this, 'handle_export_csv'));
    }

    public function handle_export_csv() {
        if (isset($_GET['page']) && $_GET['page'] === 'man-subscribers' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
            if (!current_user_can('manage_options')) return;
            check_admin_referer('man_export_subscribers');

            global $wpdb;
            $table = $wpdb->prefix . 'man_subscribers';
            $results = $wpdb->get_results("SELECT email, status, created_at FROM $table", ARRAY_A);

            if (!$results) return;

            $filename = 'subscribers-' . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, array('Email', 'Status', 'Date d\'inscription'));

            foreach ($results as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        }
    }

    public static function add_subscriber($email, $status = 'pending') {
        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';

        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
        if ($existing) {
            return false;
        }

        $token = wp_generate_password(32, false);
        $result = $wpdb->insert($table, array(
            'email' => $email,
            'status' => $status,
            'token' => $token,
            'created_at' => current_time('mysql')
        ));

        if ($result) {
            $subscriber_id = $wpdb->insert_id;
            if ($status === 'pending' && get_option('man_double_optin', '1') === '1') {
                MAN_Automation::send_confirmation_email($email, $token);
            } elseif ($status === 'active') {
                MAN_Automation::send_welcome_email($email);
            }
            return $subscriber_id;
        }

        return false;
    }

    public function ajax_add_subscriber() {
        if (is_admin()) {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permission refusée');
            }
            check_ajax_referer('man_admin_nonce', 'nonce');
        }

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (!is_email($email)) {
            wp_send_json_error('Email invalide');
        }

        $status = (get_option('man_double_optin', '1') === '1' && !is_admin()) ? 'pending' : 'active';
        $subscriber_id = self::add_subscriber($email, $status);

        if ($subscriber_id) {
            wp_send_json_success(is_admin() ? 'Abonné ajouté !' : 'Merci pour votre inscription !');
        } else {
            wp_send_json_error('Vous êtes déjà inscrit ou une erreur est survenue.');
        }
    }

    public function ajax_delete_subscriber() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        check_ajax_referer('man_admin_nonce', 'nonce');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) wp_send_json_error('ID manquant');

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $wpdb->delete($table, array('id' => $id));

        wp_send_json_success('Abonné supprimé');
    }
}

new MAN_Subscriber();
