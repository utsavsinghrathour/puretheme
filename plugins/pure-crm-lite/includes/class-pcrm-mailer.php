<?php

if (! defined('ABSPATH')) {
    exit;
}

class PCRM_Mailer
{
    private static $runtime_account = null;
    private static $runtime_password = '';

    public static function get_accounts($active_only = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('smtp_accounts');
        $where = $active_only ? 'WHERE active = 1' : '';

        $accounts = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY is_default DESC, label ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! is_array($accounts)) {
            return array();
        }

        return array_map(array(__CLASS__, 'sanitize_account_for_response'), $accounts);
    }

    public static function get_account($id, $raw = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('smtp_accounts');
        $account = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", absint($id)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if (! $account) {
            return null;
        }

        return $raw ? $account : self::sanitize_account_for_response($account);
    }

    public static function create_account($data, $created_by = 0)
    {
        global $wpdb;

        $table = PCRM_DB::table('smtp_accounts');
        $now = current_time('mysql', true);

        $insert = array(
            'label'              => sanitize_text_field($data['label'] ?? ''),
            'host'               => sanitize_text_field($data['host'] ?? ''),
            'port'               => absint($data['port'] ?? 587),
            'encryption'         => self::sanitize_encryption($data['encryption'] ?? 'tls'),
            'username'           => sanitize_text_field($data['username'] ?? ''),
            'password_encrypted' => self::encrypt_password($data['password'] ?? ''),
            'from_email'         => sanitize_email($data['from_email'] ?? ''),
            'from_name'          => sanitize_text_field($data['from_name'] ?? ''),
            'reply_to'           => sanitize_email($data['reply_to'] ?? ''),
            'is_default'         => ! empty($data['is_default']) ? 1 : 0,
            'active'             => isset($data['active']) ? (int) (bool) $data['active'] : 1,
            'created_by'         => absint($created_by),
            'created_at'         => $now,
            'updated_at'         => $now,
        );

        $formats = array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s');

        if (empty($insert['label']) || empty($insert['host']) || empty($insert['username']) || empty($insert['from_email'])) {
            return new WP_Error('pcrm_invalid_smtp', __('Please provide required SMTP fields.', 'pure-crm-lite'));
        }

        if (! is_email($insert['from_email'])) {
            return new WP_Error('pcrm_invalid_from_email', __('From email is invalid.', 'pure-crm-lite'));
        }

        $ok = $wpdb->insert($table, $insert, $formats);
        if (! $ok) {
            return new WP_Error('pcrm_smtp_insert_failed', __('Unable to save SMTP account.', 'pure-crm-lite'));
        }

        $id = (int) $wpdb->insert_id;
        $existing_default = self::get_default_account(false, true);
        if (! $existing_default || ! empty($insert['is_default'])) {
            self::set_default_account($id);
        }

        return self::get_account($id);
    }

    public static function update_account($id, $data)
    {
        global $wpdb;

        $id = absint($id);
        if (! $id) {
            return new WP_Error('pcrm_invalid_id', __('Invalid SMTP account ID.', 'pure-crm-lite'));
        }

        $existing = self::get_account($id, true);
        if (! $existing) {
            return new WP_Error('pcrm_smtp_not_found', __('SMTP account not found.', 'pure-crm-lite'));
        }

        $update = array(
            'label'      => sanitize_text_field($data['label'] ?? $existing['label']),
            'host'       => sanitize_text_field($data['host'] ?? $existing['host']),
            'port'       => absint($data['port'] ?? $existing['port']),
            'encryption' => self::sanitize_encryption($data['encryption'] ?? $existing['encryption']),
            'username'   => sanitize_text_field($data['username'] ?? $existing['username']),
            'from_email' => sanitize_email($data['from_email'] ?? $existing['from_email']),
            'from_name'  => sanitize_text_field($data['from_name'] ?? $existing['from_name']),
            'reply_to'   => sanitize_email($data['reply_to'] ?? $existing['reply_to']),
            'active'     => isset($data['active']) ? (int) (bool) $data['active'] : (int) $existing['active'],
            'is_default' => isset($data['is_default']) ? (int) (bool) $data['is_default'] : (int) $existing['is_default'],
            'updated_at' => current_time('mysql', true),
        );

        if (isset($data['password']) && $data['password'] !== '') {
            $update['password_encrypted'] = self::encrypt_password($data['password']);
        }

        if (! is_email($update['from_email'])) {
            return new WP_Error('pcrm_invalid_from_email', __('From email is invalid.', 'pure-crm-lite'));
        }

        $table = PCRM_DB::table('smtp_accounts');
        $ok = $wpdb->update($table, $update, array('id' => $id));
        if ($ok === false) {
            return new WP_Error('pcrm_smtp_update_failed', __('Unable to update SMTP account.', 'pure-crm-lite'));
        }

        if (! empty($update['is_default'])) {
            self::set_default_account($id);
        }

        return self::get_account($id);
    }

    public static function set_default_account($id)
    {
        global $wpdb;

        $id = absint($id);
        if (! $id) {
            return false;
        }

        $table = PCRM_DB::table('smtp_accounts');
        $wpdb->query("UPDATE {$table} SET is_default = 0"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $updated = $wpdb->update(
            $table,
            array(
                'is_default' => 1,
                'updated_at' => current_time('mysql', true),
            ),
            array('id' => $id),
            array('%d', '%s'),
            array('%d')
        );

        return $updated !== false;
    }

    public static function delete_account($id)
    {
        global $wpdb;

        $id = absint($id);
        if (! $id) {
            return new WP_Error('pcrm_invalid_id', __('Invalid SMTP account ID.', 'pure-crm-lite'));
        }

        $table = PCRM_DB::table('smtp_accounts');
        $existing = self::get_account($id, true);
        if (! $existing) {
            return new WP_Error('pcrm_smtp_not_found', __('SMTP account not found.', 'pure-crm-lite'));
        }

        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        if ($deleted === false) {
            return new WP_Error('pcrm_smtp_delete_failed', __('Unable to delete SMTP account.', 'pure-crm-lite'));
        }

        if ((int) $existing['is_default'] === 1) {
            $fallback = self::get_default_account(false, true);
            if (! $fallback) {
                $fallback = $wpdb->get_row("SELECT * FROM {$table} ORDER BY id ASC LIMIT 1", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            }
            if ($fallback && ! empty($fallback['id'])) {
                self::set_default_account((int) $fallback['id']);
            }
        }

        return true;
    }

    public static function get_default_account($active_only = true, $raw = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('smtp_accounts');
        $where = $active_only ? ' AND active = 1' : '';

        $account = $wpdb->get_row("SELECT * FROM {$table} WHERE is_default = 1 {$where} ORDER BY id DESC LIMIT 1", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! $account && $active_only) {
            $account = $wpdb->get_row("SELECT * FROM {$table} WHERE active = 1 ORDER BY id ASC LIMIT 1", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        if (! $account) {
            return null;
        }

        return $raw ? $account : self::sanitize_account_for_response($account);
    }

    public static function send_email($payload, $sent_by = 0)
    {
        $smtp_id = absint($payload['smtp_account_id'] ?? 0);
        $account = $smtp_id ? self::get_account($smtp_id, true) : self::get_default_account(true, true);
        if (! $account || empty($account['active'])) {
            return new WP_Error('pcrm_no_smtp', __('No active SMTP account found.', 'pure-crm-lite'));
        }

        $to_raw = $payload['to'] ?? '';
        $recipients = array_filter(array_map('trim', explode(',', (string) $to_raw)));
        if (empty($recipients)) {
            return new WP_Error('pcrm_email_no_recipients', __('Please add at least one recipient.', 'pure-crm-lite'));
        }

        foreach ($recipients as $email) {
            if (! is_email($email)) {
                return new WP_Error('pcrm_email_invalid_recipient', sprintf(__('Invalid recipient email: %s', 'pure-crm-lite'), esc_html($email)));
            }
        }

        $subject = sanitize_text_field($payload['subject'] ?? '');
        $body = wp_kses_post($payload['body'] ?? '');
        if ($subject === '' || trim(wp_strip_all_tags($body)) === '') {
            return new WP_Error('pcrm_email_empty', __('Subject and message are required.', 'pure-crm-lite'));
        }

        self::$runtime_account = $account;
        self::$runtime_password = self::decrypt_password($account['password_encrypted'] ?? '');
        add_action('phpmailer_init', array(__CLASS__, 'configure_phpmailer'));

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (! empty($account['from_name']) && is_email($account['from_email'])) {
            $headers[] = sprintf('From: %s <%s>', $account['from_name'], $account['from_email']);
        } elseif (is_email($account['from_email'])) {
            $headers[] = sprintf('From: <%s>', $account['from_email']);
        }
        if (! empty($account['reply_to']) && is_email($account['reply_to'])) {
            $headers[] = sprintf('Reply-To: %s', $account['reply_to']);
        }

        try {
            $sent = wp_mail($recipients, $subject, wpautop($body), $headers);
            self::log_email(array(
                'smtp_account_id' => (int) $account['id'],
                'sent_by'         => (int) $sent_by,
                'recipients'      => wp_json_encode($recipients),
                'subject'         => $subject,
                'body'            => $body,
                'status'          => $sent ? 'sent' : 'failed',
                'error_message'   => $sent ? '' : __('wp_mail returned false.', 'pure-crm-lite'),
            ));
        } catch (Throwable $e) {
            self::log_email(array(
                'smtp_account_id' => (int) $account['id'],
                'sent_by'         => (int) $sent_by,
                'recipients'      => wp_json_encode($recipients),
                'subject'         => $subject,
                'body'            => $body,
                'status'          => 'failed',
                'error_message'   => $e->getMessage(),
            ));
            $sent = false;
        }

        remove_action('phpmailer_init', array(__CLASS__, 'configure_phpmailer'));
        self::$runtime_account = null;
        self::$runtime_password = '';

        if (! $sent) {
            return new WP_Error('pcrm_send_failed', __('Email send failed. Check SMTP settings and logs.', 'pure-crm-lite'));
        }

        return array(
            'success'        => true,
            'smtp_account_id' => (int) $account['id'],
            'recipients'     => $recipients,
        );
    }

    public static function configure_phpmailer($phpmailer)
    {
        if (empty(self::$runtime_account)) {
            return;
        }

        $account = self::$runtime_account;
        $phpmailer->isSMTP();
        $phpmailer->Host = $account['host'];
        $phpmailer->Port = (int) $account['port'];
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $account['username'];
        $phpmailer->Password = self::$runtime_password;
        $phpmailer->CharSet = 'UTF-8';
        $phpmailer->Timeout = 20;

        if ($account['encryption'] === 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ($account['encryption'] === 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        if (is_email($account['from_email'])) {
            $phpmailer->setFrom($account['from_email'], $account['from_name'], false);
        }
        if (! empty($account['reply_to']) && is_email($account['reply_to'])) {
            $phpmailer->addReplyTo($account['reply_to']);
        }
    }

    public static function get_email_logs($limit = 50)
    {
        global $wpdb;

        $table = PCRM_DB::table('email_logs');
        $smtp_table = PCRM_DB::table('smtp_accounts');
        $limit = max(1, min(absint($limit), 200));

        $sql = $wpdb->prepare(
            "SELECT l.*, s.label AS smtp_label, s.from_email
             FROM {$table} l
             LEFT JOIN {$smtp_table} s ON s.id = l.smtp_account_id
             ORDER BY l.id DESC
             LIMIT %d",
            $limit
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    private static function log_email($data)
    {
        global $wpdb;

        $table = PCRM_DB::table('email_logs');
        $wpdb->insert(
            $table,
            array(
                'smtp_account_id' => absint($data['smtp_account_id'] ?? 0),
                'sent_by'         => absint($data['sent_by'] ?? 0),
                'recipients'      => (string) ($data['recipients'] ?? '[]'),
                'subject'         => (string) ($data['subject'] ?? ''),
                'body'            => (string) ($data['body'] ?? ''),
                'status'          => sanitize_key($data['status'] ?? 'sent'),
                'error_message'   => (string) ($data['error_message'] ?? ''),
                'created_at'      => current_time('mysql', true),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    private static function sanitize_encryption($value)
    {
        $allowed = array('none', 'tls', 'ssl');
        $value = sanitize_key((string) $value);
        return in_array($value, $allowed, true) ? $value : 'tls';
    }

    private static function sanitize_account_for_response($account)
    {
        unset($account['password_encrypted']);
        $account['id'] = (int) $account['id'];
        $account['port'] = (int) $account['port'];
        $account['is_default'] = (int) $account['is_default'];
        $account['active'] = (int) $account['active'];
        return $account;
    }

    private static function encrypt_password($password)
    {
        $password = (string) $password;
        if ($password === '') {
            return '';
        }

        if (! function_exists('openssl_encrypt')) {
            return base64_encode($password);
        }

        $key = hash('sha256', wp_salt('auth'), true);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            return base64_encode($password);
        }

        return base64_encode($iv . $encrypted);
    }

    private static function decrypt_password($encrypted)
    {
        $encrypted = (string) $encrypted;
        if ($encrypted === '') {
            return '';
        }

        $decoded = base64_decode($encrypted, true);
        if ($decoded === false) {
            return '';
        }

        if (! function_exists('openssl_decrypt')) {
            return $decoded;
        }

        if (strlen($decoded) <= 16) {
            return $decoded;
        }

        $iv = substr($decoded, 0, 16);
        $payload = substr($decoded, 16);
        $key = hash('sha256', wp_salt('auth'), true);
        $plain = openssl_decrypt($payload, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($plain === false) {
            return '';
        }

        return $plain;
    }
}
