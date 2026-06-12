<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Automation {
    public function __construct() {
        add_action('transition_post_status', array($this, 'handle_post_status_transition'), 10, 3);
        add_action('man_daily_digest', array($this, 'send_daily_digest'));
        add_action('wp_ajax_man_trigger_digest_now', array($this, 'ajax_trigger_digest_now'));
    }

    public static function send_confirmation_email($email, $token) {
        $confirm_url = home_url('/?man_action=confirm&token=' . $token);
        $subject = "Confirmez votre inscription - Angers Info";

        $raw_content = '<div style="text-align:center; padding: 20px;">';
        $raw_content .= '<h2 style="font-size:24px; font-weight:800; color:#121826;">Dernière étape !</h2>';
        $raw_content .= '<p style="font-size:18px; color:#475569; margin-bottom:30px;">Merci de vous être inscrit et d\'avoir choisi vos éditions. Cliquez sur le bouton ci-dessous pour valider votre adresse email et commencer à recevoir votre actu personnalisée.</p>';
        $raw_content .= '<a href="' . $confirm_url . '" style="display:inline-block; background-color:#f60; color:#fff; padding:18px 40px; text-decoration:none; border-radius:20px; font-weight:900; font-size:18px; box-shadow:0 15px 30px rgba(255,102,0,0.3);">Confirmer mon inscription</a>';
        $raw_content .= '</div>';

        $newsletter = new MAN_Newsletter();
        $content = $newsletter->prepare_email_content($raw_content, $subject, 0, null, 'modern');
        $headers = array('Content-Type: text/html; charset=UTF-8');

        MyAngersNewsletter::send_mail($email, $subject, $content, $headers);
    }

    public static function send_welcome_email($email) {
        $subject = "Bienvenue dans l'aventure Angers Info ! 🚀";

        $raw_content = '
        <div style="text-align:center; padding: 40px 20px;">
            <div style="font-size: 80px; margin-bottom: 30px; display: inline-block; line-height: 1;">🌟</div>

            <h2 style="font-size: 36px; font-weight: 900; color: #121826; margin-bottom: 20px; letter-spacing: -1px; line-height: 1.1;">
                C\'est le début d\'une <span style="color: #f60;">belle histoire.</span>
            </h2>

            <p style="font-size: 20px; color: #475569; line-height: 1.6; margin-bottom: 40px; font-weight: 500;">
                Merci de nous avoir rejoint ! Vous faites maintenant partie d\'un cercle privilégié de lecteurs passionnés par l\'actualité d\'Angers avec <strong>Angers Info</strong>.
            </p>

            <div style="background-color: #f8fafc; border-radius: 32px; padding: 40px; margin-bottom: 40px; border: 2px dashed #e2e8f0;">
                <h3 style="font-size: 20px; font-weight: 800; color: #121826; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">Au programme :</h3>
                <ul style="text-align: left; list-style: none; padding: 0; margin: 0; display: inline-block;">
                    <li style="margin-bottom: 15px; font-size: 17px; color: #64748b; font-weight: 600;">
                        <span style="color: #f60; margin-right: 10px;">⚡</span> Les actualités brûlantes en temps réel
                    </li>
                    <li style="margin-bottom: 15px; font-size: 17px; color: #64748b; font-weight: 600;">
                        <span style="color: #f60; margin-right: 10px;">📊</span> Des analyses exclusives sur notre région
                    </li>
                    <li style="margin-bottom: 0; font-size: 17px; color: #64748b; font-weight: 600;">
                        <span style="color: #f60; margin-right: 10px;">📅</span> Le récapitulatif quotidien chaque soir
                    </li>
                </ul>
            </div>

            <p style="font-size: 18px; color: #475569; margin-bottom: 40px; italic: italic;">
                "L\'information locale, augmentée par la passion."
            </p>

            <a href="' . home_url() . '" style="display: inline-block; background-color: #121826; color: #fff; padding: 22px 50px; text-decoration: none; border-radius: 24px; font-weight: 900; font-size: 18px; box-shadow: 0 20px 40px rgba(18,24,38,0.2); text-transform: uppercase; letter-spacing: 1px;">
                Découvrir les derniers articles
            </a>
        </div>';

        $newsletter = new MAN_Newsletter();
        $content = $newsletter->prepare_email_content($raw_content, $subject, 0, null, 'modern');
        $headers = array('Content-Type: text/html; charset=UTF-8');

        MyAngersNewsletter::send_mail($email, $subject, $content, $headers);
    }

    public function handle_post_status_transition($new_status, $old_status, $post) {
        if ($new_status !== 'publish' || $old_status === 'publish' || $post->post_type !== 'post') {
            return;
        }

        if (get_option('man_auto_notify', '0') !== '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subscribers = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active'");

        if (!$subscribers) return;

        $subject = "Nouvel article : " . get_the_title($post->ID);
        $excerpt = wp_trim_words(get_the_excerpt($post->ID), 30);
        $url = get_permalink($post->ID);
        $thumb = get_the_post_thumbnail_url($post->ID, 'medium');

        $raw_content = '<div style="text-align:center;">';
        if ($thumb) $raw_content .= '<img src="' . $thumb . '" style="width:100%; border-radius:12px; margin-bottom:20px;">';
        $raw_content .= '<h2 style="font-size:24px; font-weight:800;">' . get_the_title($post->ID) . '</h2>';
        $raw_content .= '<p>' . $excerpt . '</p>';
        $raw_content .= '<a href="' . $url . '" style="display:inline-block; background-color:#f60; color:#fff; padding:12px 24px; text-decoration:none; border-radius:8px; font-weight:bold;">Lire la suite</a>';
        $raw_content .= '</div>';

        $newsletter = new MAN_Newsletter();

        foreach ($subscribers as $sub) {
            $content = $newsletter->prepare_email_content($raw_content, $subject, 0, $sub);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            MyAngersNewsletter::send_mail($sub->email, $subject, $content, $headers);
        }
    }

    public function ajax_trigger_digest_now() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        check_ajax_referer('man_admin_nonce', 'nonce');

        $this->send_daily_digest(true);
        wp_send_json_success('Le récapitulatif quotidien a été envoyé aux abonnés actifs (articles des dernières 24h).');
    }

    public function send_daily_digest($force = false) {
        if (!$force && get_option('man_daily_digest', '0') !== '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subscribers = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active'");

        if (empty($subscribers)) return;

        $newsletter = new MAN_Newsletter();
        $date_str = date_i18n(get_option('date_format'));

        foreach ($subscribers as $sub) {
            $sub_cats = maybe_unserialize($sub->categories);

            $args = array(
                'date_query' => array(
                    array(
                        'after' => '24 hours ago',
                        'inclusive' => true,
                    ),
                ),
                'posts_per_page' => -1,
            );

            if (!empty($sub_cats)) {
                $args['category__in'] = $sub_cats;
            }

            $posts = get_posts($args);

            if (empty($posts)) continue;

            $subject = "Votre Récapitulatif Quotidien - " . $date_str;

            $grid_html = '<div style="padding-bottom: 30px; border-bottom: 2px solid #f1f5f9; margin-bottom: 30px;">';
            $grid_html .= '<h1 style="font-size: 32px; font-weight: 900; color: #121826; margin: 0;">Le meilleur de <span style="color: #f60;">vos éditions</span></h1>';
            $grid_html .= '<p style="color: #64748b; font-size: 16px; margin-top: 5px;">Voici les actualités du jour sélectionnées pour vous.</p>';
            $grid_html .= '</div>';

            $grid_html .= '<div style="display: flex; flex-wrap: wrap; margin: -10px;">';
            foreach ($posts as $index => $post) {
                $thumb = get_the_post_thumbnail_url($post->ID, 'medium');
                $width = ($index % 3 === 0) ? '100%' : '48%';

                $grid_html .= '<div style="width: ' . $width . '; box-sizing: border-box; padding: 10px;">';
                $grid_html .= '<div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; overflow: hidden; height: 100%; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">';
                if ($thumb) $grid_html .= '<img src="' . $thumb . '" style="width: 100%; height: auto; display: block;">';
                $grid_html .= '<div style="padding: 20px;">';
                $grid_html .= '<h3 style="margin: 0 0 12px 0; font-size: 20px; line-height: 1.3; font-weight: 800; color: #121826; text-transform: uppercase;">' . get_the_title($post->ID) . '</h3>';
                $grid_html .= '<p style="font-size: 15px; color: #475569; margin-bottom: 20px; line-height: 1.6;">' . wp_trim_words(get_the_excerpt($post->ID), 20) . '</p>';
                $grid_html .= '<a href="' . get_permalink($post->ID) . '" style="display: inline-block; color: #f60; font-weight: 900; text-decoration: none; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Lire l\'article →</a>';
                $grid_html .= '</div></div></div>';
            }
            $grid_html .= '</div>';

            $content = $newsletter->prepare_email_content($grid_html, $subject, 0, $sub, 'modern');
            $headers = array('Content-Type: text/html; charset=UTF-8');
            MyAngersNewsletter::send_mail($sub->email, $subject, $content, $headers);
        }
    }
}

new MAN_Automation();
