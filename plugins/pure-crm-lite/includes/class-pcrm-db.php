<?php

if (! defined('ABSPATH')) {
    exit;
}

class PCRM_DB
{
    const VERSION_OPTION = 'pcrm_db_version';
    const VERSION = '0.2.0';

    public static function activate()
    {
        self::create_tables_and_seed();
        update_option(self::VERSION_OPTION, self::VERSION);
    }

    public static function maybe_upgrade()
    {
        $installed_version = get_option(self::VERSION_OPTION, '');
        if ($installed_version !== self::VERSION) {
            self::create_tables_and_seed();
            update_option(self::VERSION_OPTION, self::VERSION);
        }
    }

    public static function table($key)
    {
        global $wpdb;

        $map = array(
            'contacts'      => $wpdb->prefix . 'pcrm_contacts',
            'deals'         => $wpdb->prefix . 'pcrm_deals',
            'tasks'         => $wpdb->prefix . 'pcrm_tasks',
            'funnels'       => $wpdb->prefix . 'pcrm_funnels',
            'smtp_accounts' => $wpdb->prefix . 'pcrm_smtp_accounts',
            'email_logs'    => $wpdb->prefix . 'pcrm_email_logs',
        );

        return isset($map[$key]) ? $map[$key] : '';
    }

    private static function create_tables_and_seed()
    {
        self::create_tables();
        self::ensure_default_funnel();
    }

    private static function create_tables()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $contacts_table = self::table('contacts');
        $deals_table = self::table('deals');
        $tasks_table = self::table('tasks');
        $funnels_table = self::table('funnels');
        $smtp_table = self::table('smtp_accounts');
        $email_logs_table = self::table('email_logs');

        $sql_contacts = "CREATE TABLE {$contacts_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(120) NOT NULL DEFAULT '',
            last_name VARCHAR(120) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(40) NOT NULL DEFAULT '',
            company VARCHAR(190) NOT NULL DEFAULT '',
            source VARCHAR(120) NOT NULL DEFAULT '',
            tags TEXT NULL,
            notes LONGTEXT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY owner_user_id (owner_user_id)
        ) {$charset_collate};";

        $sql_deals = "CREATE TABLE {$deals_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            funnel_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(190) NOT NULL,
            value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            stage VARCHAR(60) NOT NULL DEFAULT 'lead',
            expected_close DATE NULL,
            next_follow_up DATETIME NULL,
            notes LONGTEXT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY funnel_id (funnel_id),
            KEY stage (stage),
            KEY owner_user_id (owner_user_id)
        ) {$charset_collate};";

        $sql_tasks = "CREATE TABLE {$tasks_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(190) NOT NULL,
            description LONGTEXT NULL,
            contact_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            related_type VARCHAR(30) NOT NULL DEFAULT 'contact',
            related_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            due_date DATETIME NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            priority VARCHAR(20) NOT NULL DEFAULT 'normal',
            owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY related_type (related_type),
            KEY related_id (related_id),
            KEY status (status),
            KEY owner_user_id (owner_user_id)
        ) {$charset_collate};";

        $sql_funnels = "CREATE TABLE {$funnels_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(190) NOT NULL,
            slug VARCHAR(190) NOT NULL,
            stages_json LONGTEXT NOT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY slug (slug),
            KEY is_default (is_default),
            KEY active (active)
        ) {$charset_collate};";

        $sql_smtp = "CREATE TABLE {$smtp_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            label VARCHAR(120) NOT NULL,
            host VARCHAR(190) NOT NULL,
            port SMALLINT UNSIGNED NOT NULL DEFAULT 587,
            encryption VARCHAR(10) NOT NULL DEFAULT 'tls',
            username VARCHAR(190) NOT NULL,
            password_encrypted LONGTEXT NULL,
            from_email VARCHAR(190) NOT NULL,
            from_name VARCHAR(190) NOT NULL DEFAULT '',
            reply_to VARCHAR(190) NOT NULL DEFAULT '',
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY is_default (is_default),
            KEY active (active)
        ) {$charset_collate};";

        $sql_email_logs = "CREATE TABLE {$email_logs_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            smtp_account_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            sent_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
            recipients LONGTEXT NOT NULL,
            subject TEXT NOT NULL,
            body LONGTEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'sent',
            error_message LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY smtp_account_id (smtp_account_id),
            KEY sent_by (sent_by),
            KEY status (status)
        ) {$charset_collate};";

        dbDelta($sql_contacts);
        dbDelta($sql_deals);
        dbDelta($sql_tasks);
        dbDelta($sql_funnels);
        dbDelta($sql_smtp);
        dbDelta($sql_email_logs);
    }

    private static function ensure_default_funnel()
    {
        global $wpdb;

        $funnels_table = self::table('funnels');
        $deals_table = self::table('deals');
        $now = current_time('mysql', true);

        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$funnels_table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ($count < 1) {
            $wpdb->insert(
                $funnels_table,
                array(
                    'name'       => __('Main Sales Funnel', 'pure-crm-lite'),
                    'slug'       => 'main-sales-funnel',
                    'stages_json' => wp_json_encode(self::default_funnel_stages()),
                    'is_default' => 1,
                    'active'     => 1,
                    'created_by' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ),
                array('%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s')
            );
        }

        $default_funnel_id = (int) $wpdb->get_var("SELECT id FROM {$funnels_table} WHERE is_default = 1 ORDER BY id DESC LIMIT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ($default_funnel_id < 1) {
            $default_funnel_id = (int) $wpdb->get_var("SELECT id FROM {$funnels_table} ORDER BY id ASC LIMIT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            if ($default_funnel_id > 0) {
                $wpdb->query("UPDATE {$funnels_table} SET is_default = 0"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->update(
                    $funnels_table,
                    array(
                        'is_default' => 1,
                        'updated_at' => $now,
                    ),
                    array('id' => $default_funnel_id),
                    array('%d', '%s'),
                    array('%d')
                );
            }
        }

        if ($default_funnel_id > 0) {
            $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->prepare(
                    "UPDATE {$deals_table} SET funnel_id = %d WHERE funnel_id = 0",
                    $default_funnel_id
                )
            );
        }
    }

    private static function default_funnel_stages()
    {
        return array(
            array('key' => 'lead', 'label' => __('Lead', 'pure-crm-lite')),
            array('key' => 'qualified', 'label' => __('Qualified', 'pure-crm-lite')),
            array('key' => 'proposal', 'label' => __('Proposal', 'pure-crm-lite')),
            array('key' => 'negotiation', 'label' => __('Negotiation', 'pure-crm-lite')),
            array('key' => 'won', 'label' => __('Won', 'pure-crm-lite')),
            array('key' => 'lost', 'label' => __('Lost', 'pure-crm-lite')),
        );
    }
}
