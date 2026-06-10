<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_DB {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_subscribers = $wpdb->prefix . 'man_subscribers';
        $sql_subscribers = "CREATE TABLE $table_subscribers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            token varchar(100) DEFAULT '',
            unsubscribe_token varchar(100) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        $table_newsletters = $wpdb->prefix . 'man_newsletters';
        $sql_newsletters = "CREATE TABLE $table_newsletters (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            subject varchar(255) NOT NULL,
            content longtext NOT NULL,
            status varchar(20) DEFAULT 'draft' NOT NULL,
            sent_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $table_stats = $wpdb->prefix . 'man_stats';
        $sql_stats = "CREATE TABLE $table_stats (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            newsletter_id bigint(20) NOT NULL,
            subscriber_id bigint(20) NOT NULL,
            action varchar(20) NOT NULL,
            clicked_url text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_subscribers);
        dbDelta($sql_newsletters);
        dbDelta($sql_stats);
    }
}
