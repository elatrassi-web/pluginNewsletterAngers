<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Stats {
    public function __construct() {
        add_action('init', array($this, 'handle_tracking'));
    }

    public static function log_action($newsletter_id, $subscriber_id, $action, $url = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'man_stats';

        $wpdb->insert($table, array(
            'newsletter_id' => $newsletter_id,
            'subscriber_id' => $subscriber_id,
            'action' => $action,
            'clicked_url' => $url,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => current_time('mysql')
        ));
    }

    public function handle_tracking() {
        if (isset($_GET['man_track'])) {
            $action = sanitize_text_field($_GET['man_track']);
            $nid = isset($_GET['nid']) ? intval($_GET['nid']) : 0;
            $sid = isset($_GET['sid']) ? intval($_GET['sid']) : 0;

            if ($action === 'open' && $nid && $sid) {
                self::log_action($nid, $sid, 'open');
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                exit;
            }

            if ($action === 'click' && $nid && $sid && isset($_GET['url'])) {
                $url = base64_decode(urldecode($_GET['url']));

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    self::log_action($nid, $sid, 'click', $url);
                    wp_redirect($url);
                } else {
                    wp_die("URL invalide.");
                }
                exit;
            }

            if ($action === 'unsubscribe' && $sid) {
                global $wpdb;
                $table = $wpdb->prefix . 'man_subscribers';
                $wpdb->update($table, array('status' => 'unsubscribed'), array('id' => $sid));
                wp_die("Vous avez été désinscrit avec succès.", "Désinscription");
            }
        }
    }

    public static function get_tracking_url($newsletter_id, $subscriber_id, $target_url) {
        return home_url("/?man_track=click&nid=$newsletter_id&sid=$subscriber_id&url=" . urlencode(base64_encode($target_url)));
    }

    public static function get_pixel_url($newsletter_id, $subscriber_id) {
        return home_url("/?man_track=open&nid=$newsletter_id&sid=$subscriber_id");
    }

    public static function get_unsubscribe_url($subscriber_id) {
        return home_url("/?man_track=unsubscribe&sid=$subscriber_id");
    }
}

new MAN_Stats();
