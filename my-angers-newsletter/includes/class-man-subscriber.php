<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Subscriber {
    public function __construct() {
        add_action('wp_ajax_man_add_subscriber', array($this, 'ajax_add_subscriber'));
        add_action('wp_ajax_nopriv_man_add_subscriber', array($this, 'ajax_add_subscriber'));
        add_action('wp_ajax_man_delete_subscriber', array($this, 'ajax_delete_subscriber'));
        add_action('wp_ajax_man_import_csv', array($this, 'ajax_import_csv'));
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

    public static function add_subscriber($email, $status = 'pending', $categories = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $categories_str = !empty($categories) ? maybe_serialize($categories) : null;

        $existing = $wpdb->get_row($wpdb->prepare("SELECT id, status FROM $table WHERE email = %s", $email));

        if ($existing) {
            $update_data = array('categories' => $categories_str);

            if ($existing->status !== 'active') {
                $update_data['status'] = $status;

                if ($status === 'pending' && get_option('man_double_optin', '1') === '1') {
                    $token = wp_generate_password(32, false);
                    $update_data['token'] = $token;
                    MAN_Automation::send_confirmation_email($email, $token);
                } elseif ($status === 'active') {
                    MAN_Automation::send_welcome_email($email);
                }
            }

            $result = $wpdb->update($table, $update_data, array('id' => $existing->id));

            if ($result === false) {
                error_log("MAN Newsletter Error (Update): " . $wpdb->last_error . " | Data: " . print_r($update_data, true));
                return false;
            }

            return array('id' => $existing->id, 'type' => 'existing');
        }

        // New subscriber
        $token = wp_generate_password(32, false);
        $unsubscribe_token = wp_generate_password(32, false);
        $insert_data = array(
            'email' => $email,
            'status' => $status,
            'token' => $token,
            'unsubscribe_token' => $unsubscribe_token,
            'categories' => $categories_str,
            'created_at' => current_time('mysql')
        );

        $result = $wpdb->insert($table, $insert_data);

        if ($result) {
            $subscriber_id = $wpdb->insert_id;
            if ($status === 'pending' && get_option('man_double_optin', '1') === '1') {
                MAN_Automation::send_confirmation_email($email, $token);
            } elseif ($status === 'active') {
                MAN_Automation::send_welcome_email($email);
            }
            return array('id' => $subscriber_id, 'type' => 'new');
        }

        error_log("MAN Newsletter Error (Insert): " . $wpdb->last_error . " | Data: " . print_r($insert_data, true));
        return false;
    }

    public function ajax_add_subscriber() {
        global $wpdb;
        $is_admin_request = !empty($_POST['is_admin']) && $_POST['is_admin'] === '1';

        if ($is_admin_request) {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permission refusée');
            }
            check_ajax_referer('man_admin_nonce', 'nonce');
            $status = 'active';
        } else {
            if (!empty($_POST['nonce'])) {
                if (!wp_verify_nonce($_POST['nonce'], 'man_frontend_nonce')) {
                    wp_send_json_error('Session expirée. Veuillez rafraîchir la page.');
                }
            }
            $status = (get_option('man_double_optin', '1') === '1') ? 'pending' : 'active';
        }

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();

        if (!is_email($email)) {
            wp_send_json_error('Veuillez saisir une adresse email valide.');
        }

        $result = self::add_subscriber($email, $status, $categories);

        if ($result && isset($result['id'])) {
            if ($is_admin_request) {
                wp_send_json_success('Abonné enregistré avec succès !');
            } else {
                if ($result['type'] === 'existing') {
                    wp_send_json_success('Vous êtes déjà inscrit ! Vos préférences ont été mises à jour.');
                } else {
                    if ($status === 'pending') {
                        wp_send_json_success('Merci ! Veuillez confirmer votre inscription via le mail envoyé.');
                    } else {
                        wp_send_json_success('Félicitations, vous êtes maintenant bien inscrit !');
                    }
                }
            }
        } else {
            $msg = 'Une erreur est survenue lors de l\'enregistrement.';
            if (!empty($wpdb->last_error)) {
                $msg .= ' DB Error: ' . $wpdb->last_error;
            } else {
                $msg .= ' (Erreur inconnue, veuillez vérifier les logs PHP)';
            }
            wp_send_json_error($msg);
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

    public function ajax_import_csv() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        check_ajax_referer('man_admin_nonce', 'nonce');

        if (empty($_FILES['csv_file']['tmp_name'])) {
            wp_send_json_error('Aucun fichier reçu');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        $count = 0;

        // Skip header
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== FALSE) {
            $email = sanitize_email($data[0]);
            if (is_email($email)) {
                if (self::add_subscriber($email, 'active')) {
                    $count++;
                }
            }
        }
        fclose($handle);

        wp_send_json_success("$count abonnés importés avec succès !");
    }
}

new MAN_Subscriber();
