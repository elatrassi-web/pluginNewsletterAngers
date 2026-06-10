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

            if ($action === 'unsubscribe' && $sid && isset($_GET['token'])) {
                global $wpdb;
                $table = $wpdb->prefix . 'man_subscribers';
                $token = sanitize_text_field($_GET['token']);

                $subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND unsubscribe_token = %s", $sid, $token));

                if (!$subscriber) {
                    wp_die("Lien de désinscription invalide.");
                }

                $wpdb->update($table, array('status' => 'unsubscribed'), array('id' => $sid));

                // Branded unsubscription page
                ?>
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Désinscription - Angers Info</title>
                    <script src="https://cdn.tailwindcss.com"></script>
                    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
                    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
                </head>
                <body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-6">
                    <div class="max-w-xl w-full bg-white rounded-[3rem] p-12 shadow-2xl shadow-slate-200 text-center border border-slate-100">
                        <div class="mb-10"><h1 class="text-3xl font-black uppercase tracking-tighter text-[#f60]">Angers<span class="text-[#121826] italic">Info</span></h1></div>
                        <div class="mb-8">
                            <div class="w-24 h-24 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-8 shadow-inner text-slate-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h2 class="text-4xl font-black text-[#0f172a] mb-4 tracking-tight">C'est fait !</h2>
                            <p class="text-slate-500 font-bold text-lg leading-relaxed">Vous avez été désinscrit avec succès de notre newsletter. Nous sommes désolés de vous voir partir.</p>
                        </div>
                        <a href="<?php echo home_url(); ?>" class="inline-block bg-[#121826] text-white px-10 py-5 rounded-2xl font-black hover:scale-105 transition-transform shadow-xl shadow-slate-900/10 uppercase tracking-widest text-sm">Retour au site</a>
                    </div>
                </body>
                </html>
                <?php
                exit;
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
        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $token = $wpdb->get_var($wpdb->prepare("SELECT unsubscribe_token FROM $table WHERE id = %d", $subscriber_id));
        return home_url("/?man_track=unsubscribe&sid=$subscriber_id&token=$token");
    }
}

new MAN_Stats();
