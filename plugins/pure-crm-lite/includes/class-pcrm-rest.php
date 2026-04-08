<?php

if (! defined('ABSPATH')) {
    exit;
}

class PCRM_REST
{
    const NS = 'pcrm/v1';

    public static function register_routes()
    {
        register_rest_route(
            self::NS,
            '/bootstrap',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'bootstrap'),
            )
        );

        register_rest_route(
            self::NS,
            '/metrics',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'metrics'),
            )
        );

        register_rest_route(
            self::NS,
            '/contacts',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'list_contacts'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'create_contact'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/contacts/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'get_contact'),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'update_contact'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'delete_contact'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/deals',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'list_deals'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'create_deal'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/deals/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'get_deal'),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'update_deal'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'delete_deal'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/tasks',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'list_tasks'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'create_task'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/tasks/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'get_task'),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'update_task'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'delete_task'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/smtp',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'list_smtp_accounts'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'create_smtp_account'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/smtp/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'update_smtp_account'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'delete_smtp_account'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/smtp/(?P<id>\d+)/default',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'set_default_smtp_account'),
            )
        );

        register_rest_route(
            self::NS,
            '/emails/send',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'send_email'),
            )
        );

        register_rest_route(
            self::NS,
            '/emails/logs',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'email_logs'),
            )
        );

        register_rest_route(
            self::NS,
            '/lead-capture',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => '__return_true',
                'callback'            => array(__CLASS__, 'lead_capture'),
            )
        );
    }

    public static function can_use_crm()
    {
        if (! is_user_logged_in()) {
            return new WP_Error('pcrm_forbidden', __('Please log in to use CRM.', 'pure-crm-lite'), array('status' => 401));
        }

        $required_capability = apply_filters('pcrm_required_capability', 'read');
        if (! current_user_can($required_capability)) {
            return new WP_Error('pcrm_capability_forbidden', __('You do not have access to CRM.', 'pure-crm-lite'), array('status' => 403));
        }

        return true;
    }

    public static function bootstrap()
    {
        return rest_ensure_response(
            array(
                'metrics'         => self::collect_metrics(),
                'contacts'        => self::fetch_contacts(),
                'deals'           => self::fetch_deals(),
                'tasks'           => self::fetch_tasks(),
                'smtp_accounts'   => PCRM_Mailer::get_accounts(false),
                'default_smtp'    => PCRM_Mailer::get_default_account(false),
                'email_logs'      => PCRM_Mailer::get_email_logs(30),
                'deal_stages'     => self::deal_stages(),
                'task_statuses'   => self::task_statuses(),
                'task_priorities' => self::task_priorities(),
            )
        );
    }

    public static function metrics()
    {
        return rest_ensure_response(self::collect_metrics());
    }

    public static function list_contacts(WP_REST_Request $request)
    {
        $search = (string) $request->get_param('search');
        return rest_ensure_response(self::fetch_contacts($search));
    }

    public static function create_contact(WP_REST_Request $request)
    {
        global $wpdb;

        $table = PCRM_DB::table('contacts');
        $payload = self::prepare_contact_payload($request);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['owner_user_id'] = get_current_user_id();
        $payload['created_at'] = self::now_utc();
        $payload['updated_at'] = self::now_utc();

        $ok = $wpdb->insert(
            $table,
            $payload,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_contact_create_failed', __('Unable to create contact.', 'pure-crm-lite')));
        }

        $id = (int) $wpdb->insert_id;
        return rest_ensure_response(self::fetch_contact($id));
    }

    public static function get_contact(WP_REST_Request $request)
    {
        $contact = self::fetch_contact((int) $request['id']);
        if (! $contact) {
            return self::error_response(new WP_Error('pcrm_contact_not_found', __('Contact not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        return rest_ensure_response($contact);
    }

    public static function update_contact(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        if (! self::fetch_contact($id)) {
            return self::error_response(new WP_Error('pcrm_contact_not_found', __('Contact not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $table = PCRM_DB::table('contacts');
        $payload = self::prepare_contact_payload($request, true);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }
        $payload['updated_at'] = self::now_utc();

        $updated = $wpdb->update($table, $payload, array('id' => $id));
        if ($updated === false) {
            return self::error_response(new WP_Error('pcrm_contact_update_failed', __('Unable to update contact.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_contact($id));
    }

    public static function delete_contact(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        $table = PCRM_DB::table('contacts');
        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        if ($deleted === false) {
            return self::error_response(new WP_Error('pcrm_contact_delete_failed', __('Unable to delete contact.', 'pure-crm-lite')));
        }

        return rest_ensure_response(array('success' => true));
    }

    public static function list_deals(WP_REST_Request $request)
    {
        $search = (string) $request->get_param('search');
        return rest_ensure_response(self::fetch_deals($search));
    }

    public static function create_deal(WP_REST_Request $request)
    {
        global $wpdb;

        $table = PCRM_DB::table('deals');
        $payload = self::prepare_deal_payload($request);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['owner_user_id'] = get_current_user_id();
        $payload['created_at'] = self::now_utc();
        $payload['updated_at'] = self::now_utc();

        $ok = $wpdb->insert(
            $table,
            $payload,
            array('%d', '%s', '%f', '%s', '%s', '%s', '%d', '%s', '%s')
        );
        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_deal_create_failed', __('Unable to create deal.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_deal((int) $wpdb->insert_id));
    }

    public static function get_deal(WP_REST_Request $request)
    {
        $deal = self::fetch_deal((int) $request['id']);
        if (! $deal) {
            return self::error_response(new WP_Error('pcrm_deal_not_found', __('Deal not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        return rest_ensure_response($deal);
    }

    public static function update_deal(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        if (! self::fetch_deal($id)) {
            return self::error_response(new WP_Error('pcrm_deal_not_found', __('Deal not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $table = PCRM_DB::table('deals');
        $payload = self::prepare_deal_payload($request, true);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['updated_at'] = self::now_utc();
        $updated = $wpdb->update($table, $payload, array('id' => $id));
        if ($updated === false) {
            return self::error_response(new WP_Error('pcrm_deal_update_failed', __('Unable to update deal.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_deal($id));
    }

    public static function delete_deal(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        $table = PCRM_DB::table('deals');
        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        if ($deleted === false) {
            return self::error_response(new WP_Error('pcrm_deal_delete_failed', __('Unable to delete deal.', 'pure-crm-lite')));
        }

        return rest_ensure_response(array('success' => true));
    }

    public static function list_tasks(WP_REST_Request $request)
    {
        $search = (string) $request->get_param('search');
        return rest_ensure_response(self::fetch_tasks($search));
    }

    public static function create_task(WP_REST_Request $request)
    {
        global $wpdb;

        $table = PCRM_DB::table('tasks');
        $payload = self::prepare_task_payload($request);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['owner_user_id'] = get_current_user_id();
        $payload['created_at'] = self::now_utc();
        $payload['updated_at'] = self::now_utc();

        $ok = $wpdb->insert(
            $table,
            $payload,
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s')
        );
        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_task_create_failed', __('Unable to create task.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_task((int) $wpdb->insert_id));
    }

    public static function get_task(WP_REST_Request $request)
    {
        $task = self::fetch_task((int) $request['id']);
        if (! $task) {
            return self::error_response(new WP_Error('pcrm_task_not_found', __('Task not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        return rest_ensure_response($task);
    }

    public static function update_task(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        if (! self::fetch_task($id)) {
            return self::error_response(new WP_Error('pcrm_task_not_found', __('Task not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $table = PCRM_DB::table('tasks');
        $payload = self::prepare_task_payload($request, true);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['updated_at'] = self::now_utc();
        $updated = $wpdb->update($table, $payload, array('id' => $id));
        if ($updated === false) {
            return self::error_response(new WP_Error('pcrm_task_update_failed', __('Unable to update task.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_task($id));
    }

    public static function delete_task(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        $table = PCRM_DB::table('tasks');
        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        if ($deleted === false) {
            return self::error_response(new WP_Error('pcrm_task_delete_failed', __('Unable to delete task.', 'pure-crm-lite')));
        }

        return rest_ensure_response(array('success' => true));
    }

    public static function list_smtp_accounts()
    {
        return rest_ensure_response(PCRM_Mailer::get_accounts(false));
    }

    public static function create_smtp_account(WP_REST_Request $request)
    {
        $created = PCRM_Mailer::create_account($request->get_json_params() ?: $request->get_params(), get_current_user_id());
        if (is_wp_error($created)) {
            return self::error_response($created);
        }

        return rest_ensure_response($created);
    }

    public static function update_smtp_account(WP_REST_Request $request)
    {
        $updated = PCRM_Mailer::update_account((int) $request['id'], $request->get_json_params() ?: $request->get_params());
        if (is_wp_error($updated)) {
            return self::error_response($updated);
        }

        return rest_ensure_response($updated);
    }

    public static function delete_smtp_account(WP_REST_Request $request)
    {
        $deleted = PCRM_Mailer::delete_account((int) $request['id']);
        if (is_wp_error($deleted)) {
            return self::error_response($deleted);
        }

        return rest_ensure_response(array('success' => true));
    }

    public static function set_default_smtp_account(WP_REST_Request $request)
    {
        $ok = PCRM_Mailer::set_default_account((int) $request['id']);
        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_smtp_default_failed', __('Unable to set default SMTP account.', 'pure-crm-lite')));
        }

        return rest_ensure_response(PCRM_Mailer::get_accounts(false));
    }

    public static function send_email(WP_REST_Request $request)
    {
        $result = PCRM_Mailer::send_email($request->get_json_params() ?: $request->get_params(), get_current_user_id());
        if (is_wp_error($result)) {
            return self::error_response($result);
        }

        return rest_ensure_response($result);
    }

    public static function email_logs(WP_REST_Request $request)
    {
        $limit = (int) $request->get_param('limit');
        if ($limit < 1) {
            $limit = 50;
        }
        return rest_ensure_response(PCRM_Mailer::get_email_logs($limit));
    }

    public static function lead_capture(WP_REST_Request $request)
    {
        global $wpdb;

        $params = $request->get_json_params() ?: $request->get_params();
        if (! empty($params['website'])) {
            // Honeypot pass-through to keep bot response indistinguishable.
            return rest_ensure_response(array('success' => true));
        }

        $email = sanitize_email($params['email'] ?? '');
        $first_name = sanitize_text_field($params['first_name'] ?? '');
        $last_name = sanitize_text_field($params['last_name'] ?? '');
        $phone = sanitize_text_field($params['phone'] ?? '');
        $company = sanitize_text_field($params['company'] ?? '');
        $notes = wp_kses_post($params['notes'] ?? '');
        $source = sanitize_text_field($params['source'] ?? __('Website Lead Form', 'pure-crm-lite'));

        if (! is_email($email)) {
            return self::error_response(new WP_Error('pcrm_invalid_lead_email', __('Please provide a valid email address.', 'pure-crm-lite')));
        }
        if ($first_name === '' && $last_name === '') {
            return self::error_response(new WP_Error('pcrm_invalid_lead_name', __('Please provide your name.', 'pure-crm-lite')));
        }

        $table = PCRM_DB::table('contacts');
        $now = self::now_utc();

        $existing_id = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table} WHERE email = %s ORDER BY id DESC LIMIT 1", $email) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        if ($existing_id > 0) {
            $wpdb->update(
                $table,
                array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'phone'      => $phone,
                    'company'    => $company,
                    'source'     => $source,
                    'notes'      => $notes,
                    'updated_at' => $now,
                ),
                array('id' => $existing_id),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'first_name'    => $first_name,
                    'last_name'     => $last_name,
                    'email'         => $email,
                    'phone'         => $phone,
                    'company'       => $company,
                    'source'        => $source,
                    'tags'          => 'lead,website',
                    'notes'         => $notes,
                    'owner_user_id' => 0,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
            );
        }

        return rest_ensure_response(array('success' => true));
    }

    private static function fetch_contacts($search = '')
    {
        global $wpdb;

        $table = PCRM_DB::table('contacts');
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $sql = $wpdb->prepare(
                "SELECT * FROM {$table}
                WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR company LIKE %s
                ORDER BY id DESC LIMIT 300",
                $like,
                $like,
                $like,
                $like
            );
        } else {
            $sql = "SELECT * FROM {$table} ORDER BY id DESC LIMIT 300"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    private static function fetch_contact($id)
    {
        global $wpdb;
        $table = PCRM_DB::table('contacts');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", absint($id)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    private static function fetch_deals($search = '')
    {
        global $wpdb;

        $table = PCRM_DB::table('deals');
        $contacts = PCRM_DB::table('contacts');
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $sql = $wpdb->prepare(
                "SELECT d.*, c.first_name, c.last_name, c.email AS contact_email
                FROM {$table} d
                LEFT JOIN {$contacts} c ON c.id = d.contact_id
                WHERE d.title LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s
                ORDER BY d.id DESC LIMIT 300",
                $like,
                $like,
                $like
            );
        } else {
            $sql = "SELECT d.*, c.first_name, c.last_name, c.email AS contact_email
                FROM {$table} d
                LEFT JOIN {$contacts} c ON c.id = d.contact_id
                ORDER BY d.id DESC LIMIT 300"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $row['value'] = (float) $row['value'];
        }
        unset($row);

        return $rows;
    }

    private static function fetch_deal($id)
    {
        global $wpdb;

        $table = PCRM_DB::table('deals');
        $contacts = PCRM_DB::table('contacts');
        $sql = $wpdb->prepare(
            "SELECT d.*, c.first_name, c.last_name, c.email AS contact_email
            FROM {$table} d
            LEFT JOIN {$contacts} c ON c.id = d.contact_id
            WHERE d.id = %d",
            absint($id)
        );
        $row = $wpdb->get_row($sql, ARRAY_A);
        if (! $row) {
            return null;
        }

        $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['value'] = (float) $row['value'];

        return $row;
    }

    private static function fetch_tasks($search = '')
    {
        global $wpdb;

        $table = PCRM_DB::table('tasks');
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $sql = $wpdb->prepare(
                "SELECT * FROM {$table}
                WHERE title LIKE %s OR description LIKE %s
                ORDER BY
                    CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'done' THEN 3 ELSE 4 END,
                    due_date ASC,
                    id DESC
                LIMIT 300",
                $like,
                $like
            );
        } else {
            $sql = "SELECT * FROM {$table}
                ORDER BY
                    CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'done' THEN 3 ELSE 4 END,
                    due_date ASC,
                    id DESC
                LIMIT 300"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    private static function fetch_task($id)
    {
        global $wpdb;
        $table = PCRM_DB::table('tasks');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", absint($id)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    private static function collect_metrics()
    {
        global $wpdb;

        $contacts_table = PCRM_DB::table('contacts');
        $deals_table = PCRM_DB::table('deals');
        $tasks_table = PCRM_DB::table('tasks');

        $contacts_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$contacts_table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $open_deals_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$deals_table} WHERE stage IN (%s, %s, %s, %s)", 'lead', 'qualified', 'proposal', 'negotiation')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $won_deals_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$deals_table} WHERE stage = %s", 'won')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $pipeline_value = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(value), 0) FROM {$deals_table} WHERE stage != %s", 'lost')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $overdue_tasks_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tasks_table} WHERE status != %s AND due_date IS NOT NULL AND due_date < %s", 'done', gmdate('Y-m-d H:i:s'))); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return array(
            'contacts_count'      => $contacts_count,
            'open_deals_count'    => $open_deals_count,
            'won_deals_count'     => $won_deals_count,
            'pipeline_value'      => $pipeline_value,
            'overdue_tasks_count' => $overdue_tasks_count,
        );
    }

    private static function prepare_contact_payload(WP_REST_Request $request, $partial = false)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array();

        $payload['first_name'] = sanitize_text_field($params['first_name'] ?? '');
        $payload['last_name'] = sanitize_text_field($params['last_name'] ?? '');
        $payload['email'] = sanitize_email($params['email'] ?? '');
        $payload['phone'] = sanitize_text_field($params['phone'] ?? '');
        $payload['company'] = sanitize_text_field($params['company'] ?? '');
        $payload['source'] = sanitize_text_field($params['source'] ?? '');
        $payload['tags'] = sanitize_textarea_field($params['tags'] ?? '');
        $payload['notes'] = wp_kses_post($params['notes'] ?? '');

        if ($partial) {
            foreach (array_keys($payload) as $key) {
                if (! array_key_exists($key, $params)) {
                    unset($payload[$key]);
                }
            }
        }

        if (! $partial || array_key_exists('email', $params)) {
            if (($payload['email'] ?? '') === '' || ! is_email($payload['email'])) {
                return new WP_Error('pcrm_invalid_contact_email', __('Please provide a valid contact email.', 'pure-crm-lite'));
            }
        }

        if (! $partial || array_key_exists('first_name', $params) || array_key_exists('last_name', $params)) {
            if (($payload['first_name'] ?? '') === '' && ($payload['last_name'] ?? '') === '') {
                return new WP_Error('pcrm_invalid_contact_name', __('Please provide first name or last name.', 'pure-crm-lite'));
            }
        }

        return $payload;
    }

    private static function prepare_deal_payload(WP_REST_Request $request, $partial = false)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array(
            'contact_id'      => absint($params['contact_id'] ?? 0),
            'title'           => sanitize_text_field($params['title'] ?? ''),
            'value'           => (float) ($params['value'] ?? 0),
            'stage'           => self::sanitize_deal_stage($params['stage'] ?? 'lead'),
            'expected_close'  => self::sanitize_date_field($params['expected_close'] ?? ''),
            'notes'           => wp_kses_post($params['notes'] ?? ''),
        );

        if ($partial) {
            foreach (array_keys($payload) as $key) {
                if (! array_key_exists($key, $params)) {
                    unset($payload[$key]);
                }
            }
        }

        if (! $partial || array_key_exists('title', $params)) {
            if (($payload['title'] ?? '') === '') {
                return new WP_Error('pcrm_invalid_deal_title', __('Deal title is required.', 'pure-crm-lite'));
            }
        }

        return $payload;
    }

    private static function prepare_task_payload(WP_REST_Request $request, $partial = false)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array(
            'title'        => sanitize_text_field($params['title'] ?? ''),
            'description'  => wp_kses_post($params['description'] ?? ''),
            'related_type' => self::sanitize_related_type($params['related_type'] ?? 'contact'),
            'related_id'   => absint($params['related_id'] ?? 0),
            'due_date'     => self::sanitize_datetime_field($params['due_date'] ?? ''),
            'status'       => self::sanitize_task_status($params['status'] ?? 'open'),
            'priority'     => self::sanitize_task_priority($params['priority'] ?? 'normal'),
        );

        if ($partial) {
            foreach (array_keys($payload) as $key) {
                if (! array_key_exists($key, $params)) {
                    unset($payload[$key]);
                }
            }
        }

        if (! $partial || array_key_exists('title', $params)) {
            if (($payload['title'] ?? '') === '') {
                return new WP_Error('pcrm_invalid_task_title', __('Task title is required.', 'pure-crm-lite'));
            }
        }

        return $payload;
    }

    private static function now_utc()
    {
        return current_time('mysql', true);
    }

    private static function sanitize_deal_stage($stage)
    {
        $stages = self::deal_stages();
        $stage = sanitize_key((string) $stage);
        return isset($stages[$stage]) ? $stage : 'lead';
    }

    private static function sanitize_task_status($status)
    {
        $statuses = self::task_statuses();
        $status = sanitize_key((string) $status);
        return isset($statuses[$status]) ? $status : 'open';
    }

    private static function sanitize_task_priority($priority)
    {
        $priorities = self::task_priorities();
        $priority = sanitize_key((string) $priority);
        return isset($priorities[$priority]) ? $priority : 'normal';
    }

    private static function sanitize_related_type($related_type)
    {
        $allowed = array('contact', 'deal', 'task', 'general');
        $related_type = sanitize_key((string) $related_type);
        return in_array($related_type, $allowed, true) ? $related_type : 'contact';
    }

    private static function sanitize_date_field($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }
        $ts = strtotime($date);
        return $ts ? gmdate('Y-m-d', $ts) : null;
    }

    private static function sanitize_datetime_field($datetime)
    {
        $datetime = trim((string) $datetime);
        if ($datetime === '') {
            return null;
        }
        $ts = strtotime($datetime);
        return $ts ? gmdate('Y-m-d H:i:s', $ts) : null;
    }

    private static function deal_stages()
    {
        return array(
            'lead'        => __('Lead', 'pure-crm-lite'),
            'qualified'   => __('Qualified', 'pure-crm-lite'),
            'proposal'    => __('Proposal', 'pure-crm-lite'),
            'negotiation' => __('Negotiation', 'pure-crm-lite'),
            'won'         => __('Won', 'pure-crm-lite'),
            'lost'        => __('Lost', 'pure-crm-lite'),
        );
    }

    private static function task_statuses()
    {
        return array(
            'open'        => __('Open', 'pure-crm-lite'),
            'in_progress' => __('In Progress', 'pure-crm-lite'),
            'done'        => __('Done', 'pure-crm-lite'),
        );
    }

    private static function task_priorities()
    {
        return array(
            'low'    => __('Low', 'pure-crm-lite'),
            'normal' => __('Normal', 'pure-crm-lite'),
            'high'   => __('High', 'pure-crm-lite'),
        );
    }

    private static function error_response(WP_Error $error)
    {
        $status = (int) $error->get_error_data('status');
        if ($status < 100) {
            $status = 400;
        }

        return new WP_REST_Response(
            array(
                'code'    => $error->get_error_code(),
                'message' => $error->get_error_message(),
            ),
            $status
        );
    }
}
