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
            categories longtext DEFAULT NULL,
            created_at datetime NOT NULL,
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
            created_at datetime NOT NULL,
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
            timestamp datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_subscribers);
        dbDelta($sql_newsletters);
        dbDelta($sql_stats);

        return self::repair_database();
    }

    /**
     * Resiliently check and add missing columns.
     * Returns true if all columns are present or successfully added.
     */
    public static function repair_database() {
        global $wpdb;
        $table_subscribers = $wpdb->prefix . 'man_subscribers';

        // Ensure table exists first
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_subscribers'") !== $table_subscribers) {
            return false;
        }

        $columns = $wpdb->get_col("DESCRIBE $table_subscribers", 0);

        if (empty($columns)) {
            error_log("MAN Newsletter DB Error: Could not describe table $table_subscribers");
            return false;
        }

        $success = true;

        if (!in_array('unsubscribe_token', $columns)) {
            $wpdb->query("ALTER TABLE $table_subscribers ADD unsubscribe_token varchar(100) DEFAULT '' AFTER token");
            if (!empty($wpdb->last_error)) {
                error_log("MAN Newsletter DB Error (Repair unsubscribe_token): " . $wpdb->last_error);
                $success = false;
            }
        }

        if (!in_array('categories', $columns)) {
            $wpdb->query("ALTER TABLE $table_subscribers ADD categories longtext DEFAULT NULL AFTER unsubscribe_token");
            if (!empty($wpdb->last_error)) {
                error_log("MAN Newsletter DB Error (Repair categories): " . $wpdb->last_error);
                $success = false;
            }
        }

        return $success;
    }
}
