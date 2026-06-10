<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Automation {
    public function __construct() {
        add_action('publish_post', array($this, 'handle_new_post_notification'), 10, 2);
        add_action('man_daily_digest', array($this, 'send_daily_digest'));
    }

    public static function send_confirmation_email($email, $token) {
        $confirm_url = home_url('/?man_action=confirm&token=' . $token);
        $subject = "Confirmez votre inscription - Angers Info";

        $raw_content = '<div style="text-align:center; padding: 20px;">';
        $raw_content .= '<h2 style="font-size:24px; font-weight:800; color:#121826;">Dernière étape !</h2>';
        $raw_content .= '<p style="font-size:18px; color:#475569; margin-bottom:30px;">Merci de vous être inscrit. Cliquez sur le bouton ci-dessous pour valider votre adresse email.</p>';
        $raw_content .= '<a href="' . $confirm_url . '" style="display:inline-block; background-color:#f60; color:#fff; padding:18px 40px; text-decoration:none; border-radius:20px; font-weight:900; font-size:18px; box-shadow:0 15px 30px rgba(255,102,0,0.3);">Confirmer mon inscription</a>';
        $raw_content .= '</div>';

        $newsletter = new MAN_Newsletter();
        $content = $newsletter->prepare_email_content($raw_content, $subject, 0, null, 'modern');
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($email, $subject, $content, $headers);
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
                Merci de nous avoir rejoint ! Vous faites maintenant partie d\'un cercle privilégié de lecteurs passionnés par l\'actualité d\'Angers.
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
                        <span style="color: #f60; margin-right: 10px;">📅</span> Le récapitulatif quotidien chaque soir à 18h
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

        wp_mail($email, $subject, $content, $headers);
    }

    public function handle_new_post_notification($ID, $post) {
        if (get_option('man_auto_notify', '0') !== '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subscribers = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active'");

        if (!$subscribers) return;

        $subject = "Nouvel article : " . get_the_title($ID);
        $excerpt = wp_trim_words(get_the_excerpt($ID), 30);
        $url = get_permalink($ID);
        $thumb = get_the_post_thumbnail_url($ID, 'medium');

        $raw_content = '<div style="text-align:center;">';
        if ($thumb) $raw_content .= '<img src="' . $thumb . '" style="width:100%; border-radius:12px; margin-bottom:20px;">';
        $raw_content .= '<h2 style="font-size:24px; font-weight:800;">' . get_the_title($ID) . '</h2>';
        $raw_content .= '<p>' . $excerpt . '</p>';
        $raw_content .= '<a href="' . $url . '" style="display:inline-block; background-color:#f60; color:#fff; padding:12px 24px; text-decoration:none; border-radius:8px; font-weight:bold;">Lire la suite</a>';
        $raw_content .= '</div>';

        $newsletter = new MAN_Newsletter();

        foreach ($subscribers as $sub) {
            $content = $newsletter->prepare_email_content($raw_content, $subject, 0, $sub);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($sub->email, $subject, $content, $headers);
        }
    }

    public function send_daily_digest() {
        if (get_option('man_daily_digest', '0') !== '1') {
            return;
        }

        $posts = get_posts(array(
            'date_query' => array(
                array(
                    'after' => 'today',
                    'inclusive' => true,
                ),
            ),
            'posts_per_page' => -1,
        ));

        if (empty($posts)) return;

        $subject = "Récapitulatif du jour - " . date_i18n(get_option('date_format'));

        // Build Grid/Masonry-like layout for email
        $grid_html = '<div style="display: flex; flex-wrap: wrap; margin: -10px;">';
        foreach ($posts as $index => $post) {
            $thumb = get_the_post_thumbnail_url($post->ID, 'medium');
            $width = ($index % 3 === 0) ? '100%' : '48%'; // Mixing full width and half width for masonry feel

            $grid_html .= '<div style="width: ' . $width . '; box-sizing: border-box; padding: 10px;">';
            $grid_html .= '<div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden;">';
            if ($thumb) $grid_html .= '<img src="' . $thumb . '" style="width: 100%; height: auto; display: block;">';
            $grid_html .= '<div style="padding: 15px;">';
            $grid_html .= '<h3 style="margin: 0 0 10px 0; font-size: 18px; line-height: 1.3;">' . get_the_title($post->ID) . '</h3>';
            $grid_html .= '<p style="font-size: 14px; color: #64748b; margin-bottom: 15px;">' . wp_trim_words(get_the_excerpt($post->ID), 15) . '</p>';
            $grid_html .= '<a href="' . get_permalink($post->ID) . '" style="color: #f60; font-weight: bold; text-decoration: none; font-size: 14px;">Lire la suite →</a>';
            $grid_html .= '</div></div></div>';
        }
        $grid_html .= '</div>';

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subscribers = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active'");

        $newsletter = new MAN_Newsletter();
        foreach ($subscribers as $sub) {
            $content = $newsletter->prepare_email_content($grid_html, $subject, 0, $sub);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($sub->email, $subject, $content, $headers);
        }
    }
}

new MAN_Automation();
