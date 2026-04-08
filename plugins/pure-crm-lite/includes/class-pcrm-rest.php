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
            '/notifications',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'notifications'),
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
            '/funnels',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'list_funnels'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'create_funnel'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/funnels/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'update_funnel'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array(__CLASS__, 'can_use_crm'),
                    'callback'            => array(__CLASS__, 'delete_funnel'),
                ),
            )
        );

        register_rest_route(
            self::NS,
            '/funnels/(?P<id>\d+)/default',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => array(__CLASS__, 'can_use_crm'),
                'callback'            => array(__CLASS__, 'set_default_funnel'),
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
        $funnels = self::fetch_funnels();
        $default_funnel = self::default_funnel();

        return rest_ensure_response(
            array(
                'metrics'         => self::collect_metrics(),
                'contacts'        => self::fetch_contacts(),
                'deals'           => self::fetch_deals(),
                'tasks'           => self::fetch_tasks(),
                'funnels'         => $funnels,
                'default_funnel'  => $default_funnel,
                'smtp_accounts'   => PCRM_Mailer::get_accounts(false),
                'default_smtp'    => PCRM_Mailer::get_default_account(false),
                'email_logs'      => PCRM_Mailer::get_email_logs(30),
                'deal_stages'     => self::deal_stages(),
                'task_statuses'   => self::task_statuses(),
                'task_priorities' => self::task_priorities(),
                'notifications'   => self::collect_notifications(),
                'help'            => self::frontend_help(),
            )
        );
    }

    public static function metrics()
    {
        return rest_ensure_response(self::collect_metrics());
    }

    public static function notifications()
    {
        return rest_ensure_response(self::collect_notifications());
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

    public static function list_funnels()
    {
        return rest_ensure_response(self::fetch_funnels());
    }

    public static function create_funnel(WP_REST_Request $request)
    {
        global $wpdb;

        $table = PCRM_DB::table('funnels');
        $payload = self::prepare_funnel_payload($request);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['created_by'] = get_current_user_id();
        $payload['created_at'] = self::now_utc();
        $payload['updated_at'] = self::now_utc();

        $ok = $wpdb->insert(
            $table,
            $payload,
            array('%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s')
        );
        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_funnel_create_failed', __('Unable to create funnel.', 'pure-crm-lite')));
        }

        $id = (int) $wpdb->insert_id;
        $default = self::default_funnel(true);
        if (! $default || ! empty($payload['is_default'])) {
            self::set_default_funnel_internal($id);
        }

        return rest_ensure_response(self::fetch_funnels());
    }

    public static function update_funnel(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        $existing = self::fetch_funnel($id, true);
        if (! $existing) {
            return self::error_response(new WP_Error('pcrm_funnel_not_found', __('Funnel not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $table = PCRM_DB::table('funnels');
        $payload = self::prepare_funnel_payload($request, true, $existing);
        if (is_wp_error($payload)) {
            return self::error_response($payload);
        }

        $payload['updated_at'] = self::now_utc();
        $updated = $wpdb->update($table, $payload, array('id' => $id));
        if ($updated === false) {
            return self::error_response(new WP_Error('pcrm_funnel_update_failed', __('Unable to update funnel.', 'pure-crm-lite')));
        }

        if (isset($payload['is_default']) && (int) $payload['is_default'] === 1) {
            self::set_default_funnel_internal($id);
        }

        return rest_ensure_response(self::fetch_funnels());
    }

    public static function delete_funnel(WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int) $request['id'];
        $table = PCRM_DB::table('funnels');
        $deals_table = PCRM_DB::table('deals');
        $existing = self::fetch_funnel($id, true);
        if (! $existing) {
            return self::error_response(new WP_Error('pcrm_funnel_not_found', __('Funnel not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ($count <= 1) {
            return self::error_response(new WP_Error('pcrm_last_funnel', __('At least one funnel must exist.', 'pure-crm-lite')));
        }

        $fallback_id = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table} WHERE id != %d ORDER BY is_default DESC, id ASC LIMIT 1", $id) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );
        if ($fallback_id < 1) {
            return self::error_response(new WP_Error('pcrm_funnel_delete_failed', __('Unable to determine fallback funnel.', 'pure-crm-lite')));
        }

        $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare(
                "UPDATE {$deals_table} SET funnel_id = %d WHERE funnel_id = %d",
                $fallback_id,
                $id
            )
        );

        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        if ($deleted === false) {
            return self::error_response(new WP_Error('pcrm_funnel_delete_failed', __('Unable to delete funnel.', 'pure-crm-lite')));
        }

        if ((int) $existing['is_default'] === 1) {
            self::set_default_funnel_internal($fallback_id);
        }

        return rest_ensure_response(self::fetch_funnels());
    }

    public static function set_default_funnel(WP_REST_Request $request)
    {
        $ok = self::set_default_funnel_internal((int) $request['id']);
        if (! $ok) {
            return self::error_response(new WP_Error('pcrm_funnel_default_failed', __('Unable to set default funnel.', 'pure-crm-lite')));
        }

        return rest_ensure_response(self::fetch_funnels());
    }

    public static function list_deals(WP_REST_Request $request)
    {
        $search = (string) $request->get_param('search');
        $funnel_id = absint($request->get_param('funnel_id'));
        return rest_ensure_response(self::fetch_deals($search, $funnel_id));
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
            array('%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
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
        $existing = self::fetch_deal($id);
        if (! $existing) {
            return self::error_response(new WP_Error('pcrm_deal_not_found', __('Deal not found.', 'pure-crm-lite'), array('status' => 404)));
        }

        $table = PCRM_DB::table('deals');
        $payload = self::prepare_deal_payload($request, true, $existing);
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
        $contact_id = absint($request->get_param('contact_id'));
        return rest_ensure_response(self::fetch_tasks($search, $contact_id));
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
            array('%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s')
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

    private static function fetch_funnels($active_only = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('funnels');
        $where = $active_only ? 'WHERE active = 1' : '';
        $rows = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY is_default DESC, id ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! is_array($rows)) {
            return array();
        }

        foreach ($rows as &$row) {
            $row = self::hydrate_funnel_row($row);
        }
        unset($row);

        return $rows;
    }

    private static function fetch_funnel($id, $raw = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('funnels');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", absint($id)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if (! $row) {
            return null;
        }

        return $raw ? $row : self::hydrate_funnel_row($row);
    }

    private static function default_funnel($raw = false)
    {
        global $wpdb;

        $table = PCRM_DB::table('funnels');
        $row = $wpdb->get_row("SELECT * FROM {$table} WHERE is_default = 1 ORDER BY id DESC LIMIT 1", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! $row) {
            $row = $wpdb->get_row("SELECT * FROM {$table} ORDER BY id ASC LIMIT 1", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }
        if (! $row) {
            return null;
        }

        return $raw ? $row : self::hydrate_funnel_row($row);
    }

    private static function fetch_deals($search = '', $funnel_id = 0)
    {
        global $wpdb;

        $table = PCRM_DB::table('deals');
        $contacts = PCRM_DB::table('contacts');
        $funnels = PCRM_DB::table('funnels');
        $where = array();
        $values = array();

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $where[] = '(d.title LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s)';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }
        if ($funnel_id > 0) {
            $where[] = 'd.funnel_id = %d';
            $values[] = $funnel_id;
        }

        $where_sql = '';
        if (! empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql_base = "SELECT d.*, c.first_name, c.last_name, c.email AS contact_email, f.name AS funnel_name, f.stages_json
            FROM {$table} d
            LEFT JOIN {$contacts} c ON c.id = d.contact_id
            LEFT JOIN {$funnels} f ON f.id = d.funnel_id
            {$where_sql}
            ORDER BY d.id DESC
            LIMIT 300";
        $sql = empty($values) ? $sql_base : $wpdb->prepare($sql_base, $values); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $row['value'] = (float) $row['value'];
            $row['funnel_id'] = (int) ($row['funnel_id'] ?? 0);
            $row['stage_label'] = self::stage_label_from_json($row['stages_json'] ?? '', $row['stage'] ?? '');
            unset($row['stages_json']);
        }
        unset($row);

        return $rows;
    }

    private static function fetch_deal($id)
    {
        global $wpdb;

        $table = PCRM_DB::table('deals');
        $contacts = PCRM_DB::table('contacts');
        $funnels = PCRM_DB::table('funnels');
        $sql = $wpdb->prepare(
            "SELECT d.*, c.first_name, c.last_name, c.email AS contact_email, f.name AS funnel_name, f.stages_json
            FROM {$table} d
            LEFT JOIN {$contacts} c ON c.id = d.contact_id
            LEFT JOIN {$funnels} f ON f.id = d.funnel_id
            WHERE d.id = %d",
            absint($id)
        );
        $row = $wpdb->get_row($sql, ARRAY_A);
        if (! $row) {
            return null;
        }

        $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['value'] = (float) $row['value'];
        $row['funnel_id'] = (int) ($row['funnel_id'] ?? 0);
        $row['stage_label'] = self::stage_label_from_json($row['stages_json'] ?? '', $row['stage'] ?? '');
        unset($row['stages_json']);

        return $row;
    }

    private static function fetch_tasks($search = '', $contact_id = 0)
    {
        global $wpdb;

        $table = PCRM_DB::table('tasks');
        $contacts = PCRM_DB::table('contacts');
        $where = array();
        $values = array();

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $where[] = '(t.title LIKE %s OR t.description LIKE %s)';
            $values[] = $like;
            $values[] = $like;
        }
        if ($contact_id > 0) {
            $where[] = 't.contact_id = %d';
            $values[] = $contact_id;
        }

        $where_sql = '';
        if (! empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql_base = "SELECT t.*, c.first_name, c.last_name, c.email AS contact_email
            FROM {$table} t
            LEFT JOIN {$contacts} c ON c.id = t.contact_id
            {$where_sql}
            ORDER BY
                CASE t.status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'done' THEN 3 ELSE 4 END,
                t.due_date ASC,
                t.id DESC
            LIMIT 300";
        $sql = empty($values) ? $sql_base : $wpdb->prepare($sql_base, $values); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $row['contact_id'] = (int) ($row['contact_id'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    private static function fetch_task($id)
    {
        global $wpdb;

        $table = PCRM_DB::table('tasks');
        $contacts = PCRM_DB::table('contacts');
        $sql = $wpdb->prepare(
            "SELECT t.*, c.first_name, c.last_name, c.email AS contact_email
            FROM {$table} t
            LEFT JOIN {$contacts} c ON c.id = t.contact_id
            WHERE t.id = %d",
            absint($id)
        );
        $row = $wpdb->get_row($sql, ARRAY_A);
        if (! $row) {
            return null;
        }
        $row['contact_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['contact_id'] = (int) ($row['contact_id'] ?? 0);
        return $row;
    }

    private static function collect_metrics()
    {
        global $wpdb;

        $contacts_table = PCRM_DB::table('contacts');
        $deals_table = PCRM_DB::table('deals');
        $tasks_table = PCRM_DB::table('tasks');
        $funnels_table = PCRM_DB::table('funnels');

        $contacts_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$contacts_table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $open_deals_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$deals_table} WHERE stage NOT IN (%s, %s)", 'won', 'lost')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $won_deals_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$deals_table} WHERE stage = %s", 'won')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $pipeline_value = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(value), 0) FROM {$deals_table} WHERE stage != %s", 'lost')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $overdue_tasks_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tasks_table} WHERE status != %s AND due_date IS NOT NULL AND due_date < %s", 'done', gmdate('Y-m-d H:i:s'))); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $followups_due_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$deals_table} WHERE stage NOT IN (%s, %s) AND next_follow_up IS NOT NULL AND next_follow_up <= %s", 'won', 'lost', gmdate('Y-m-d H:i:s'))); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $funnels_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$funnels_table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return array(
            'contacts_count'      => $contacts_count,
            'open_deals_count'    => $open_deals_count,
            'won_deals_count'     => $won_deals_count,
            'pipeline_value'      => $pipeline_value,
            'overdue_tasks_count' => $overdue_tasks_count,
            'followups_due_count' => $followups_due_count,
            'funnels_count'       => $funnels_count,
        );
    }

    private static function collect_notifications()
    {
        global $wpdb;

        $deals_table = PCRM_DB::table('deals');
        $contacts_table = PCRM_DB::table('contacts');
        $tasks_table = PCRM_DB::table('tasks');
        $now = gmdate('Y-m-d H:i:s');
        $next_day = gmdate('Y-m-d H:i:s', strtotime('+24 hours'));

        $followups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT d.id, d.title, d.next_follow_up, c.first_name, c.last_name, c.email
                 FROM {$deals_table} d
                 LEFT JOIN {$contacts_table} c ON c.id = d.contact_id
                 WHERE d.stage NOT IN (%s, %s)
                   AND d.next_follow_up IS NOT NULL
                   AND d.next_follow_up <= %s
                 ORDER BY d.next_follow_up ASC
                 LIMIT 20",
                'won',
                'lost',
                $next_day
            ),
            ARRAY_A
        ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        $overdue_tasks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT t.id, t.title, t.due_date, c.first_name, c.last_name, c.email
                 FROM {$tasks_table} t
                 LEFT JOIN {$contacts_table} c ON c.id = t.contact_id
                 WHERE t.status != %s
                   AND t.due_date IS NOT NULL
                   AND t.due_date < %s
                 ORDER BY t.due_date ASC
                 LIMIT 20",
                'done',
                $now
            ),
            ARRAY_A
        ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        $items = array();
        foreach ($followups as $row) {
            $contact_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $items[] = array(
                'type'      => 'follow_up',
                'priority'  => strtotime((string) $row['next_follow_up']) < strtotime($now) ? 'high' : 'normal',
                'title'     => sprintf(__('Follow up deal: %s', 'pure-crm-lite'), (string) $row['title']),
                'message'   => $contact_name !== '' ? $contact_name : (string) ($row['email'] ?? ''),
                'entity_id' => (int) $row['id'],
                'due_at'    => (string) ($row['next_follow_up'] ?? ''),
            );
        }
        foreach ($overdue_tasks as $row) {
            $contact_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $items[] = array(
                'type'      => 'task_overdue',
                'priority'  => 'high',
                'title'     => sprintf(__('Overdue task: %s', 'pure-crm-lite'), (string) $row['title']),
                'message'   => $contact_name !== '' ? $contact_name : (string) ($row['email'] ?? ''),
                'entity_id' => (int) $row['id'],
                'due_at'    => (string) ($row['due_date'] ?? ''),
            );
        }

        usort(
            $items,
            static function ($a, $b) {
                return strtotime((string) ($a['due_at'] ?? '')) <=> strtotime((string) ($b['due_at'] ?? ''));
            }
        );

        return array(
            'count' => count($items),
            'items' => array_slice($items, 0, 30),
        );
    }

    private static function frontend_help()
    {
        return array(
            array(
                'title'   => __('Kanban pipeline workflow', 'pure-crm-lite'),
                'content' => __('Create a funnel first, then add deals. Drag cards between columns to update stage instantly.', 'pure-crm-lite'),
            ),
            array(
                'title'   => __('Follow-up notifications', 'pure-crm-lite'),
                'content' => __('Set "Next follow up" on deals and due dates on tasks. The notification center surfaces overdue and due-soon items.', 'pure-crm-lite'),
            ),
            array(
                'title'   => __('Task assignment', 'pure-crm-lite'),
                'content' => __('Assign each task to a contact from the dropdown, then update status and details from the edit action.', 'pure-crm-lite'),
            ),
            array(
                'title'   => __('Email sending', 'pure-crm-lite'),
                'content' => __('Add multiple SMTP accounts, set one default, and choose a sender account per email campaign/message.', 'pure-crm-lite'),
            ),
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

    private static function prepare_deal_payload(WP_REST_Request $request, $partial = false, $existing = null)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array();
        $funnel = null;
        $funnel_id = 0;

        if ($partial && ! array_key_exists('contact_id', $params) && ! array_key_exists('funnel_id', $params) && ! array_key_exists('stage', $params) && ! array_key_exists('title', $params) && ! array_key_exists('value', $params) && ! array_key_exists('expected_close', $params) && ! array_key_exists('next_follow_up', $params) && ! array_key_exists('notes', $params)) {
            return $payload;
        }

        if (! $partial || array_key_exists('contact_id', $params)) {
            $contact_id = absint($params['contact_id'] ?? 0);
            if ($contact_id > 0 && ! self::fetch_contact($contact_id)) {
                return new WP_Error('pcrm_invalid_deal_contact', __('Please choose a valid contact for this deal.', 'pure-crm-lite'));
            }
            if (! $partial && $contact_id < 1) {
                return new WP_Error('pcrm_required_deal_contact', __('Contact is required for deal creation.', 'pure-crm-lite'));
            }
            $payload['contact_id'] = $contact_id;
        }

        if (! $partial || array_key_exists('title', $params)) {
            $title = sanitize_text_field($params['title'] ?? '');
            if ($title === '') {
                return new WP_Error('pcrm_invalid_deal_title', __('Deal title is required.', 'pure-crm-lite'));
            }
            $payload['title'] = $title;
        }

        if (! $partial || array_key_exists('value', $params)) {
            $payload['value'] = (float) ($params['value'] ?? 0);
        }

        if (! $partial || array_key_exists('funnel_id', $params) || array_key_exists('stage', $params)) {
            if (array_key_exists('funnel_id', $params)) {
                $funnel_id = absint($params['funnel_id'] ?? 0);
            } elseif ($partial && is_array($existing)) {
                $funnel_id = absint($existing['funnel_id'] ?? 0);
            }
            $funnel = $funnel_id > 0 ? self::fetch_funnel($funnel_id) : self::default_funnel();
            if (! $funnel) {
                return new WP_Error('pcrm_no_funnel', __('Create at least one funnel first.', 'pure-crm-lite'));
            }
            $funnel_id = (int) $funnel['id'];
            $payload['funnel_id'] = $funnel_id;

            $stages = self::funnel_stage_map($funnel);
            if (array_key_exists('stage', $params)) {
                $stage_key = sanitize_key((string) ($params['stage'] ?? ''));
            } elseif ($partial && is_array($existing)) {
                $stage_key = sanitize_key((string) ($existing['stage'] ?? ''));
            } else {
                $stage_key = '';
            }
            if ($stage_key === '' || ! isset($stages[$stage_key])) {
                $keys = array_keys($stages);
                $stage_key = $keys[0] ?? 'lead';
            }
            $payload['stage'] = $stage_key;
        }

        if (! $partial || array_key_exists('expected_close', $params)) {
            $payload['expected_close'] = self::sanitize_date_field($params['expected_close'] ?? '');
        }

        if (! $partial || array_key_exists('next_follow_up', $params)) {
            $payload['next_follow_up'] = self::sanitize_datetime_field($params['next_follow_up'] ?? '');
        }

        if (! $partial || array_key_exists('notes', $params)) {
            $payload['notes'] = wp_kses_post($params['notes'] ?? '');
        }

        return $payload;
    }

    private static function prepare_task_payload(WP_REST_Request $request, $partial = false)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array();

        if (! $partial || array_key_exists('title', $params)) {
            $title = sanitize_text_field($params['title'] ?? '');
            if ($title === '') {
                return new WP_Error('pcrm_invalid_task_title', __('Task title is required.', 'pure-crm-lite'));
            }
            $payload['title'] = $title;
        }
        if (! $partial || array_key_exists('description', $params)) {
            $payload['description'] = wp_kses_post($params['description'] ?? '');
        }
        if (! $partial || array_key_exists('contact_id', $params)) {
            $contact_id = absint($params['contact_id'] ?? 0);
            if ($contact_id > 0 && ! self::fetch_contact($contact_id)) {
                return new WP_Error('pcrm_invalid_task_contact', __('Please choose a valid contact.', 'pure-crm-lite'));
            }
            $payload['contact_id'] = $contact_id;
        }
        if (! $partial || array_key_exists('related_type', $params)) {
            $payload['related_type'] = self::sanitize_related_type($params['related_type'] ?? 'contact');
        }
        if (! $partial || array_key_exists('related_id', $params)) {
            $payload['related_id'] = absint($params['related_id'] ?? 0);
        }
        if (! $partial || array_key_exists('due_date', $params)) {
            $payload['due_date'] = self::sanitize_datetime_field($params['due_date'] ?? '');
        }
        if (! $partial || array_key_exists('status', $params)) {
            $payload['status'] = self::sanitize_task_status($params['status'] ?? 'open');
        }
        if (! $partial || array_key_exists('priority', $params)) {
            $payload['priority'] = self::sanitize_task_priority($params['priority'] ?? 'normal');
        }

        return $payload;
    }

    private static function prepare_funnel_payload(WP_REST_Request $request, $partial = false, $existing = null)
    {
        $params = $request->get_json_params() ?: $request->get_params();
        $payload = array();

        if (! $partial || array_key_exists('name', $params)) {
            $name = sanitize_text_field($params['name'] ?? '');
            if ($name === '') {
                return new WP_Error('pcrm_invalid_funnel_name', __('Funnel name is required.', 'pure-crm-lite'));
            }
            $payload['name'] = $name;
            $payload['slug'] = sanitize_title($name);
        }

        if (! $partial || array_key_exists('stages', $params) || array_key_exists('stages_json', $params)) {
            $stages = self::parse_funnel_stages($params['stages'] ?? ($params['stages_json'] ?? ''));
            if (empty($stages)) {
                return new WP_Error('pcrm_invalid_funnel_stages', __('Add at least one funnel stage.', 'pure-crm-lite'));
            }
            $payload['stages_json'] = wp_json_encode($stages);
        } elseif (! $partial && ! empty($existing['stages_json'])) {
            $payload['stages_json'] = (string) $existing['stages_json'];
        }

        if (! $partial || array_key_exists('is_default', $params)) {
            $payload['is_default'] = ! empty($params['is_default']) ? 1 : 0;
        }
        if (! $partial || array_key_exists('active', $params)) {
            $payload['active'] = isset($params['active']) ? (int) (bool) $params['active'] : 1;
        }

        return $payload;
    }

    private static function now_utc()
    {
        return current_time('mysql', true);
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
        $funnel = self::default_funnel();
        if (! $funnel) {
            return array(
                'lead'        => __('Lead', 'pure-crm-lite'),
                'qualified'   => __('Qualified', 'pure-crm-lite'),
                'proposal'    => __('Proposal', 'pure-crm-lite'),
                'negotiation' => __('Negotiation', 'pure-crm-lite'),
                'won'         => __('Won', 'pure-crm-lite'),
                'lost'        => __('Lost', 'pure-crm-lite'),
            );
        }

        return self::funnel_stage_map($funnel);
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

    private static function set_default_funnel_internal($id)
    {
        global $wpdb;

        $id = absint($id);
        if ($id < 1 || ! self::fetch_funnel($id, true)) {
            return false;
        }

        $table = PCRM_DB::table('funnels');
        $wpdb->query("UPDATE {$table} SET is_default = 0"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $updated = $wpdb->update(
            $table,
            array(
                'is_default' => 1,
                'updated_at' => self::now_utc(),
            ),
            array('id' => $id),
            array('%d', '%s'),
            array('%d')
        );

        return $updated !== false;
    }

    private static function hydrate_funnel_row($row)
    {
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['is_default'] = (int) ($row['is_default'] ?? 0);
        $row['active'] = (int) ($row['active'] ?? 1);
        $stages = self::normalize_funnel_stages($row['stages_json'] ?? '');
        $row['stages'] = $stages;
        $row['stage_map'] = self::funnel_stage_map_from_stages($stages);
        return $row;
    }

    private static function normalize_funnel_stages($stages_json)
    {
        $decoded = json_decode((string) $stages_json, true);
        if (! is_array($decoded)) {
            $decoded = array();
        }

        $stages = array();
        foreach ($decoded as $item) {
            if (is_array($item)) {
                $label = sanitize_text_field((string) ($item['label'] ?? ''));
                $key = sanitize_key((string) ($item['key'] ?? ''));
                if ($label === '' && $key !== '') {
                    $label = ucwords(str_replace('_', ' ', $key));
                }
                if ($key === '' && $label !== '') {
                    $key = sanitize_key($label);
                }
            } else {
                $label = sanitize_text_field((string) $item);
                $key = sanitize_key($label);
            }

            if ($label === '' || $key === '') {
                continue;
            }

            $stages[] = array(
                'key'   => $key,
                'label' => $label,
            );
        }

        if (empty($stages)) {
            $stages = array(
                array('key' => 'lead', 'label' => __('Lead', 'pure-crm-lite')),
                array('key' => 'qualified', 'label' => __('Qualified', 'pure-crm-lite')),
                array('key' => 'proposal', 'label' => __('Proposal', 'pure-crm-lite')),
                array('key' => 'won', 'label' => __('Won', 'pure-crm-lite')),
            );
        }

        return $stages;
    }

    private static function parse_funnel_stages($raw)
    {
        if (is_string($raw)) {
            $parts = array_filter(array_map('trim', explode(',', $raw)));
            $raw = $parts;
        }

        if (! is_array($raw)) {
            return array();
        }

        $stages = array();
        foreach ($raw as $item) {
            if (is_array($item)) {
                $label = sanitize_text_field((string) ($item['label'] ?? ''));
                $key = sanitize_key((string) ($item['key'] ?? ''));
                if ($key === '' && $label !== '') {
                    $key = sanitize_key($label);
                }
            } else {
                $label = sanitize_text_field((string) $item);
                $key = sanitize_key($label);
            }
            if ($label === '' || $key === '') {
                continue;
            }
            $stages[$key] = array(
                'key'   => $key,
                'label' => $label,
            );
        }

        return array_values($stages);
    }

    private static function funnel_stage_map($funnel)
    {
        if (! is_array($funnel)) {
            return array();
        }
        if (isset($funnel['stage_map']) && is_array($funnel['stage_map'])) {
            return $funnel['stage_map'];
        }
        return self::funnel_stage_map_from_stages($funnel['stages'] ?? self::normalize_funnel_stages($funnel['stages_json'] ?? ''));
    }

    private static function funnel_stage_map_from_stages($stages)
    {
        $map = array();
        if (! is_array($stages)) {
            return $map;
        }
        foreach ($stages as $stage) {
            $key = sanitize_key((string) ($stage['key'] ?? ''));
            $label = sanitize_text_field((string) ($stage['label'] ?? ''));
            if ($key === '' || $label === '') {
                continue;
            }
            $map[$key] = $label;
        }
        return $map;
    }

    private static function stage_label_from_json($stages_json, $stage_key)
    {
        $stages = self::normalize_funnel_stages($stages_json);
        foreach ($stages as $stage) {
            if (($stage['key'] ?? '') === $stage_key) {
                return (string) ($stage['label'] ?? $stage_key);
            }
        }
        return (string) $stage_key;
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
