<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Newsletter {
    public function __construct() {
        add_action('wp_ajax_man_save_campaign', array($this, 'ajax_save_campaign'));
        add_action('wp_ajax_man_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_man_send_campaign', array($this, 'ajax_send_campaign'));
    }

    private function check_permission() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        check_ajax_referer('man_admin_nonce', 'nonce');
    }

    public function ajax_send_campaign() {
        $this->check_permission();

        global $wpdb;
        $table_newsletters = $wpdb->prefix . 'man_newsletters';
        $table_subscribers = $wpdb->prefix . 'man_subscribers';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $recipient_type = isset($_POST['recipient_type']) ? sanitize_text_field($_POST['recipient_type']) : 'all';
        $selected_ids = isset($_POST['selected_ids']) ? array_map('intval', (array)$_POST['selected_ids']) : array();

        if (!$id) wp_send_json_error('ID de campagne manquant');

        $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_newsletters WHERE id = %d", $id));
        if (!$campaign) wp_send_json_error('Campagne non trouvée');

        if ($recipient_type === 'manual' && !empty($selected_ids)) {
            $placeholders = implode(',', array_fill(0, count($selected_ids), '%d'));
            $subscribers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_subscribers WHERE id IN ($placeholders) AND status = 'active'", ...$selected_ids));
        } else {
            $subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers WHERE status = 'active'");
        }

        if (!$subscribers) wp_send_json_error('Aucun abonné actif trouvé');

        $count = 0;
        foreach ($subscribers as $sub) {
            $content = $this->prepare_email_content($campaign->content, $campaign->subject, $campaign->id, $sub);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            if (wp_mail($sub->email, $campaign->subject, $content, $headers)) {
                $count++;
            }
        }

        $wpdb->update($table_newsletters,
            array('status' => 'sent', 'sent_at' => current_time('mysql')),
            array('id' => $id)
        );

        wp_send_json_success("Campagne envoyée à $count abonnés !");
    }

    public function prepare_email_content($raw_content, $subject, $newsletter_id, $sub) {
        $template_type = get_option('man_email_template', 'modern');
        $content = $raw_content;

        // Process links for tracking
        $content = preg_replace_callback('/href="([^"]+)"/', function($matches) use ($newsletter_id, $sub) {
            $url = $matches[1];
            if (strpos($url, 'mailto:') === 0 || strpos($url, '#') === 0) return $matches[0];
            $tracked_url = MAN_Stats::get_tracking_url($newsletter_id, $sub->id, $url);
            return 'href="' . $tracked_url . '"';
        }, $content);

        $unsubscribe_url = MAN_Stats::get_unsubscribe_url($sub->id);
        $pixel = '<img src="' . MAN_Stats::get_pixel_url($newsletter_id, $sub->id) . '" width="1" height="1" style="display:none;">';

        $footer = '<div style="margin-top:40px; padding-top:20px; border-top:1px solid #eee; font-size:12px; color:#999; text-align:center;">';
        $footer .= "Vous recevez cet email car vous êtes inscrit à la newsletter d'Angers Info. <br>";
        $footer .= '<a href="' . $unsubscribe_url . '" style="color:#f60; text-decoration:none;">Se désabonner</a>';
        $footer .= '</div>';

        switch ($template_type) {
            case 'classic':
                $final_html = '<div style="font-family:serif; max-width:600px; margin:0 auto; padding:20px; line-height:1.6; color:#333;">';
                $final_html .= '<h1 style="text-align:center; color:#111; border-bottom:2px solid #111; padding-bottom:10px;">' . esc_html($subject) . '</h1>';
                $final_html .= $content;
                $final_html .= $footer . $pixel . '</div>';
                break;
            case 'minimal':
                $final_html = '<div style="font-family:sans-serif; max-width:500px; margin:0 auto; padding:10px; line-height:1.5; color:#444;">';
                $final_html .= $content;
                $final_html .= '<p style="font-size:11px; color:#ccc; margin-top:30px;"><a href="' . $unsubscribe_url . '">Unsubscribe</a></p>';
                $final_html .= $pixel . '</div>';
                break;
            case 'modern':
            default:
                $final_html = '<div style="font-family:sans-serif; background-color:#f8fafc; padding:40px 0;">';
                $final_html .= '<div style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05);">';
                $final_html .= '<div style="background-color:#f60; padding:30px; text-align:center;"><h1 style="color:#ffffff; margin:0; font-size:24px; font-weight:800;">Angers Info</h1></div>';
                $final_html .= '<div style="padding:40px; color:#1e293b; font-size:16px; line-height:1.8;">' . $content . '</div>';
                $final_html .= '<div style="padding:40px; background-color:#f1f5f9;">' . $footer . '</div>';
                $final_html .= '</div>' . $pixel . '</div>';
                break;
        }

        return $final_html;
    }

    public function ajax_save_campaign() {
        $this->check_permission();

        global $wpdb;
        $table = $wpdb->prefix . 'man_newsletters';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if ($id) {
            $wpdb->update($table,
                array('subject' => $subject, 'content' => $content),
                array('id' => $id)
            );
        } else {
            $wpdb->insert($table,
                array('subject' => $subject, 'content' => $content, 'status' => 'draft', 'created_at' => current_time('mysql'))
            );
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(array('id' => $id, 'message' => 'Campagne enregistrée !'));
    }

    public function ajax_get_posts() {
        $this->check_permission();

        $posts = get_posts(array(
            'posts_per_page' => 10,
            'post_status' => 'publish'
        ));

        $result = array();
        foreach ($posts as $post) {
            $result[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'excerpt' => wp_trim_words(get_the_excerpt($post->ID), 20),
                'url' => get_permalink($post->ID),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail')
            );
        }

        wp_send_json_success($result);
    }
}

new MAN_Newsletter();
