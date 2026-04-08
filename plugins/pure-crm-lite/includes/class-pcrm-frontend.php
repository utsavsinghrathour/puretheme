<?php

if (! defined('ABSPATH')) {
    exit;
}

class PCRM_Frontend
{
    public static function init()
    {
        add_shortcode('pure_crm', array(__CLASS__, 'render_crm_app'));
        add_shortcode('pure_crm_lead_form', array(__CLASS__, 'render_lead_form'));
    }

    public static function render_crm_app()
    {
        if (! is_user_logged_in()) {
            return sprintf(
                '<div class="pcrm-locked">%s <a href="%s">%s</a></div>',
                esc_html__('Please log in to access the CRM dashboard.', 'pure-crm-lite'),
                esc_url(wp_login_url(get_permalink())),
                esc_html__('Log in', 'pure-crm-lite')
            );
        }

        $required_capability = apply_filters('pcrm_required_capability', 'read');
        if (! current_user_can($required_capability)) {
            return '<div class="pcrm-locked">' . esc_html__('You do not have permission to access this CRM.', 'pure-crm-lite') . '</div>';
        }

        self::enqueue_assets();

        ob_start();
        ?>
        <div class="pcrm-app" id="pcrm-app">
            <aside class="pcrm-sidebar">
                <div class="pcrm-brand">
                    <div class="pcrm-logo">CRM</div>
                    <div>
                        <h2><?php esc_html_e('Pure CRM', 'pure-crm-lite'); ?></h2>
                        <p><?php esc_html_e('Frontend Command Center', 'pure-crm-lite'); ?></p>
                    </div>
                </div>
                <nav class="pcrm-nav">
                    <button class="pcrm-nav-btn is-active" data-section="dashboard"><?php esc_html_e('Dashboard', 'pure-crm-lite'); ?></button>
                    <button class="pcrm-nav-btn" data-section="contacts"><?php esc_html_e('Contacts', 'pure-crm-lite'); ?></button>
                    <button class="pcrm-nav-btn" data-section="funnels"><?php esc_html_e('Funnels', 'pure-crm-lite'); ?></button>
                    <button class="pcrm-nav-btn" data-section="tasks"><?php esc_html_e('Tasks', 'pure-crm-lite'); ?></button>
                    <button class="pcrm-nav-btn" data-section="email"><?php esc_html_e('Emails', 'pure-crm-lite'); ?></button>
                    <button class="pcrm-nav-btn" data-section="smtp"><?php esc_html_e('SMTP Accounts', 'pure-crm-lite'); ?></button>
                </nav>
            </aside>

            <main class="pcrm-main">
                <header class="pcrm-header">
                    <div>
                        <h1><?php esc_html_e('CRM Workspace', 'pure-crm-lite'); ?></h1>
                        <p><?php esc_html_e('Manage pipeline, tasks, and outreach without wp-admin.', 'pure-crm-lite'); ?></p>
                    </div>
                    <div id="pcrm-toast-anchor"></div>
                </header>

                <section class="pcrm-section is-active" data-section="dashboard">
                    <div class="pcrm-metrics" id="pcrm-metrics"></div>
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card pcrm-help-card">
                            <h3><?php esc_html_e('Important Steps', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-help-list"></div>
                        </div>
                        <div class="pcrm-card pcrm-notification-card">
                            <h3><?php esc_html_e('Follow-up Notifications', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-notifications"></div>
                        </div>
                    </div>
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Recent Tasks', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-dashboard-tasks"></div>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Recent Email Activity', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-dashboard-emails"></div>
                        </div>
                    </div>
                </section>

                <section class="pcrm-section" data-section="contacts">
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Add Contact', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-contact-form" class="pcrm-form">
                                <div class="pcrm-form-row">
                                    <input type="text" name="first_name" placeholder="<?php esc_attr_e('First name', 'pure-crm-lite'); ?>" required>
                                    <input type="text" name="last_name" placeholder="<?php esc_attr_e('Last name', 'pure-crm-lite'); ?>">
                                </div>
                                <div class="pcrm-form-row">
                                    <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'pure-crm-lite'); ?>" required>
                                    <input type="text" name="phone" placeholder="<?php esc_attr_e('Phone', 'pure-crm-lite'); ?>">
                                </div>
                                <div class="pcrm-form-row">
                                    <input type="text" name="company" placeholder="<?php esc_attr_e('Company', 'pure-crm-lite'); ?>">
                                    <input type="text" name="source" placeholder="<?php esc_attr_e('Source (e.g. Ads)', 'pure-crm-lite'); ?>">
                                </div>
                                <input type="text" name="tags" placeholder="<?php esc_attr_e('Tags (comma separated)', 'pure-crm-lite'); ?>">
                                <textarea name="notes" rows="4" placeholder="<?php esc_attr_e('Notes', 'pure-crm-lite'); ?>"></textarea>
                                <button type="submit" class="pcrm-btn"><?php esc_html_e('Create Contact', 'pure-crm-lite'); ?></button>
                            </form>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Contact Directory', 'pure-crm-lite'); ?></h3>
                            <input type="search" id="pcrm-contact-search" placeholder="<?php esc_attr_e('Search contacts...', 'pure-crm-lite'); ?>">
                            <div id="pcrm-contacts-table"></div>
                        </div>
                    </div>
                </section>

                <section class="pcrm-section" data-section="funnels">
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Funnel Builder', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-funnel-form" class="pcrm-form">
                                <input type="text" name="name" placeholder="<?php esc_attr_e('Funnel name (e.g. High Ticket Sales)', 'pure-crm-lite'); ?>" required>
                                <input type="text" name="stages" placeholder="<?php esc_attr_e('Stages: lead,discovery,proposal,won,lost', 'pure-crm-lite'); ?>" required>
                                <label class="pcrm-check">
                                    <input type="checkbox" name="is_default" value="1">
                                    <?php esc_html_e('Set as default funnel', 'pure-crm-lite'); ?>
                                </label>
                                <button type="submit" class="pcrm-btn"><?php esc_html_e('Create Funnel', 'pure-crm-lite'); ?></button>
                            </form>
                            <div id="pcrm-funnel-list"></div>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Create Deal', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-deal-form" class="pcrm-form">
                                <select name="funnel_id" id="pcrm-deal-funnel" required></select>
                                <select name="contact_id" id="pcrm-deal-contact" required></select>
                                <input type="text" name="title" placeholder="<?php esc_attr_e('Deal title', 'pure-crm-lite'); ?>" required>
                                <div class="pcrm-form-row">
                                    <input type="number" step="0.01" name="value" placeholder="<?php esc_attr_e('Value', 'pure-crm-lite'); ?>">
                                    <select name="stage" id="pcrm-deal-stage"></select>
                                </div>
                                <input type="date" name="expected_close">
                                <input type="datetime-local" name="next_follow_up">
                                <textarea name="notes" rows="3" placeholder="<?php esc_attr_e('Deal notes', 'pure-crm-lite'); ?>"></textarea>
                                <button type="submit" class="pcrm-btn"><?php esc_html_e('Add Deal', 'pure-crm-lite'); ?></button>
                            </form>
                        </div>
                    </div>
                    <div class="pcrm-card pcrm-kanban-card">
                        <h3><?php esc_html_e('Pipeline Kanban (Drag & Drop)', 'pure-crm-lite'); ?></h3>
                        <div class="pcrm-inline-filter">
                            <select id="pcrm-active-funnel"></select>
                        </div>
                        <div id="pcrm-funnels-board" class="pcrm-pipeline"></div>
                    </div>
                </section>

                <section class="pcrm-section" data-section="tasks">
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Create / Update Task', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-task-form" class="pcrm-form">
                                <input type="hidden" name="id" id="pcrm-task-id">
                                <input type="text" name="title" placeholder="<?php esc_attr_e('Task title', 'pure-crm-lite'); ?>" required>
                                <textarea name="description" rows="3" placeholder="<?php esc_attr_e('Description', 'pure-crm-lite'); ?>"></textarea>
                                <div class="pcrm-form-row">
                                    <select name="contact_id" id="pcrm-task-contact"></select>
                                    <select name="related_type">
                                        <option value="contact"><?php esc_html_e('Related to Contact', 'pure-crm-lite'); ?></option>
                                        <option value="deal"><?php esc_html_e('Related to Deal', 'pure-crm-lite'); ?></option>
                                        <option value="general"><?php esc_html_e('General', 'pure-crm-lite'); ?></option>
                                    </select>
                                </div>
                                <div class="pcrm-form-row">
                                    <input type="number" name="related_id" min="0" placeholder="<?php esc_attr_e('Related ID', 'pure-crm-lite'); ?>">
                                    <select name="status">
                                        <option value="open"><?php esc_html_e('Open', 'pure-crm-lite'); ?></option>
                                        <option value="in_progress"><?php esc_html_e('In Progress', 'pure-crm-lite'); ?></option>
                                        <option value="done"><?php esc_html_e('Done', 'pure-crm-lite'); ?></option>
                                    </select>
                                </div>
                                <div class="pcrm-form-row">
                                    <input type="datetime-local" name="due_date">
                                    <select name="priority">
                                        <option value="low"><?php esc_html_e('Low', 'pure-crm-lite'); ?></option>
                                        <option value="normal" selected><?php esc_html_e('Normal', 'pure-crm-lite'); ?></option>
                                        <option value="high"><?php esc_html_e('High', 'pure-crm-lite'); ?></option>
                                    </select>
                                </div>
                                <div class="pcrm-inline-actions">
                                    <button type="submit" class="pcrm-btn"><?php esc_html_e('Save Task', 'pure-crm-lite'); ?></button>
                                    <button type="button" class="pcrm-btn-link" id="pcrm-task-cancel-edit"><?php esc_html_e('Clear', 'pure-crm-lite'); ?></button>
                                </div>
                            </form>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Task List', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-tasks-list"></div>
                        </div>
                    </div>
                </section>

                <section class="pcrm-section" data-section="email">
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Compose Email', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-email-form" class="pcrm-form">
                                <select name="smtp_account_id" id="pcrm-email-smtp"></select>
                                <input type="text" name="to" placeholder="<?php esc_attr_e('To emails (comma separated)', 'pure-crm-lite'); ?>" required>
                                <input type="text" name="subject" placeholder="<?php esc_attr_e('Subject', 'pure-crm-lite'); ?>" required>
                                <textarea name="body" rows="7" placeholder="<?php esc_attr_e('Message body (HTML allowed)', 'pure-crm-lite'); ?>" required></textarea>
                                <button type="submit" class="pcrm-btn"><?php esc_html_e('Send Email', 'pure-crm-lite'); ?></button>
                            </form>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Email Logs', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-email-logs"></div>
                        </div>
                    </div>
                </section>

                <section class="pcrm-section" data-section="smtp">
                    <div class="pcrm-grid-2">
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('Add SMTP Account', 'pure-crm-lite'); ?></h3>
                            <form id="pcrm-smtp-form" class="pcrm-form">
                                <input type="text" name="label" placeholder="<?php esc_attr_e('Account label (e.g. Sales Gmail)', 'pure-crm-lite'); ?>" required>
                                <input type="text" name="host" placeholder="<?php esc_attr_e('SMTP host', 'pure-crm-lite'); ?>" required>
                                <div class="pcrm-form-row">
                                    <input type="number" name="port" value="587" min="1" required>
                                    <select name="encryption">
                                        <option value="tls"><?php esc_html_e('TLS', 'pure-crm-lite'); ?></option>
                                        <option value="ssl"><?php esc_html_e('SSL', 'pure-crm-lite'); ?></option>
                                        <option value="none"><?php esc_html_e('None', 'pure-crm-lite'); ?></option>
                                    </select>
                                </div>
                                <input type="text" name="username" placeholder="<?php esc_attr_e('SMTP username', 'pure-crm-lite'); ?>" required>
                                <input type="password" name="password" placeholder="<?php esc_attr_e('SMTP password / app password', 'pure-crm-lite'); ?>" required>
                                <div class="pcrm-form-row">
                                    <input type="email" name="from_email" placeholder="<?php esc_attr_e('From email', 'pure-crm-lite'); ?>" required>
                                    <input type="text" name="from_name" placeholder="<?php esc_attr_e('From name', 'pure-crm-lite'); ?>">
                                </div>
                                <input type="email" name="reply_to" placeholder="<?php esc_attr_e('Reply-to email (optional)', 'pure-crm-lite'); ?>">
                                <label class="pcrm-check">
                                    <input type="checkbox" name="is_default" value="1">
                                    <?php esc_html_e('Set as default sender', 'pure-crm-lite'); ?>
                                </label>
                                <label class="pcrm-check">
                                    <input type="checkbox" name="active" value="1" checked>
                                    <?php esc_html_e('Active account', 'pure-crm-lite'); ?>
                                </label>
                                <button type="submit" class="pcrm-btn"><?php esc_html_e('Save SMTP Account', 'pure-crm-lite'); ?></button>
                            </form>
                        </div>
                        <div class="pcrm-card">
                            <h3><?php esc_html_e('SMTP Accounts', 'pure-crm-lite'); ?></h3>
                            <div id="pcrm-smtp-list"></div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
        <?php

        return ob_get_clean();
    }

    public static function render_lead_form($atts = array())
    {
        $atts = shortcode_atts(
            array(
                'title'  => __('Talk to our team', 'pure-crm-lite'),
                'source' => __('Website Lead Form', 'pure-crm-lite'),
            ),
            $atts,
            'pure_crm_lead_form'
        );

        self::enqueue_assets();

        ob_start();
        ?>
        <div class="pcrm-lead-wrap">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <form class="pcrm-form pcrm-lead-form" data-source="<?php echo esc_attr($atts['source']); ?>">
                <div class="pcrm-form-row">
                    <input type="text" name="first_name" placeholder="<?php esc_attr_e('First name', 'pure-crm-lite'); ?>" required>
                    <input type="text" name="last_name" placeholder="<?php esc_attr_e('Last name', 'pure-crm-lite'); ?>">
                </div>
                <input type="email" name="email" placeholder="<?php esc_attr_e('Work email', 'pure-crm-lite'); ?>" required>
                <input type="text" name="phone" placeholder="<?php esc_attr_e('Phone', 'pure-crm-lite'); ?>">
                <input type="text" name="company" placeholder="<?php esc_attr_e('Company', 'pure-crm-lite'); ?>">
                <textarea name="notes" rows="4" placeholder="<?php esc_attr_e('How can we help?', 'pure-crm-lite'); ?>"></textarea>
                <input type="text" name="website" class="pcrm-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button type="submit" class="pcrm-btn"><?php esc_html_e('Submit', 'pure-crm-lite'); ?></button>
                <p class="pcrm-lead-msg" aria-live="polite"></p>
            </form>
        </div>
        <?php

        return ob_get_clean();
    }

    private static function enqueue_assets()
    {
        wp_enqueue_style(
            'pcrm-app',
            PCRM_PLUGIN_URL . 'assets/css/pcrm-app.css',
            array(),
            PCRM_VERSION
        );
        wp_enqueue_script(
            'pcrm-app',
            PCRM_PLUGIN_URL . 'assets/js/pcrm-app.js',
            array(),
            PCRM_VERSION,
            true
        );

        wp_localize_script(
            'pcrm-app',
            'PCRM_APP',
            array(
                'restUrl'      => esc_url_raw(rest_url(PCRM_REST::NS . '/')),
                'leadUrl'      => esc_url_raw(rest_url(PCRM_REST::NS . '/lead-capture')),
                'nonce'        => wp_create_nonce('wp_rest'),
                'isLoggedIn'   => is_user_logged_in(),
                'currentUser'  => wp_get_current_user()->display_name,
                'strings'      => array(
                    'loading'        => __('Loading CRM data...', 'pure-crm-lite'),
                    'empty'          => __('No records yet.', 'pure-crm-lite'),
                    'saved'          => __('Saved successfully.', 'pure-crm-lite'),
                    'deleted'        => __('Deleted.', 'pure-crm-lite'),
                    'sendOk'         => __('Email sent successfully.', 'pure-crm-lite'),
                    'leadSuccess'    => __('Thanks! We will contact you shortly.', 'pure-crm-lite'),
                    'leadError'      => __('Unable to submit right now. Please try again.', 'pure-crm-lite'),
                    'smtpDefaultTag' => __('Default', 'pure-crm-lite'),
                    'dropHint'       => __('Drag a deal card to another stage to update status.', 'pure-crm-lite'),
                ),
            )
        );
    }
}
