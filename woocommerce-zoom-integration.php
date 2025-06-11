<?php
/**
 * Plugin Name: WooCommerce Zoom Integration
 * Plugin URI: https://github.com/royzxje/ntq-woocommerce-zoom
 * Description: Tự động tạo phòng họp Zoom khi đơn hàng WooCommerce hoàn thành và gửi thông tin truy cập cho khách hàng
 * Version: 1.0.0
 * Author: ntquan
 * Author URI: https://q2k1.com/
 * Text Domain: wc-zoom-integration
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_ZOOM_PLUGIN_VERSION', '1.0.0');
define('WC_ZOOM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_ZOOM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_ZOOM_PLUGIN_FILE', __FILE__);
define('WC_ZOOM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function wc_zoom_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wc_zoom_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Admin notice when WooCommerce is missing
 */
function wc_zoom_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>' . esc_html__('WooCommerce Zoom Integration', 'wc-zoom-integration') . '</strong> ' . esc_html__('requires WooCommerce to be installed and active.', 'wc-zoom-integration') . '</p></div>';
}

/**
 * Initialize the plugin
 */
function wc_zoom_init() {
    if (!wc_zoom_check_woocommerce()) {
        return;
    }

    // Load text domain
    load_plugin_textdomain('wc-zoom-integration', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Include required files
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-main.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-admin.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-api.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-order-handler.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-email.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-logger.php';
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-shortcodes.php';
    
    // Initialize main class
    WC_Zoom_Main::instance();
}

/**
 * Add custom email to WooCommerce emails
 */
function wc_zoom_add_emails($email_classes) {
    require_once WC_ZOOM_PLUGIN_PATH . 'includes/class-wc-zoom-meeting-email.php';
    $email_classes['WC_Zoom_Meeting_Email'] = new WC_Zoom_Meeting_Email();
    return $email_classes;
}
add_filter('woocommerce_email_classes', 'wc_zoom_add_emails');

/**
 * Plugin activation hook
 */
function wc_zoom_activate() {
    if (!wc_zoom_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce Zoom Integration requires WooCommerce to be installed and active.', 'wc-zoom-integration'));
    }

    // Create database tables
    wc_zoom_create_tables();
    
    // Set default options
    wc_zoom_set_default_options();
}

/**
 * Plugin deactivation hook
 */
function wc_zoom_deactivate() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('wc_zoom_cleanup_logs');
}

/**
 * Create database tables
 */
function wc_zoom_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Table for Zoom accounts
    $table_accounts = $wpdb->prefix . 'wc_zoom_accounts';
    $sql_accounts = "CREATE TABLE $table_accounts (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        account_name varchar(255) NOT NULL,
        api_key varchar(255) NOT NULL,
        api_secret text NOT NULL,
        account_id varchar(255) DEFAULT '',
        status varchar(20) DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Table for distribution rules
    $table_rules = $wpdb->prefix . 'wc_zoom_distribution_rules';
    $sql_rules = "CREATE TABLE $table_rules (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        rule_name varchar(255) NOT NULL,
        condition_type varchar(50) NOT NULL,
        condition_value varchar(255) NOT NULL,
        zoom_account_id mediumint(9) NOT NULL,
        priority int(11) DEFAULT 0,
        status varchar(20) DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY zoom_account_id (zoom_account_id)
    ) $charset_collate;";

    // Table for logs
    $table_logs = $wpdb->prefix . 'wc_zoom_logs';
    $sql_logs = "CREATE TABLE $table_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        level varchar(20) NOT NULL,
        message text NOT NULL,
        context text DEFAULT '',
        order_id bigint(20) DEFAULT NULL,
        meeting_id varchar(255) DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY level (level),
        KEY order_id (order_id),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_accounts);
    dbDelta($sql_rules);
    dbDelta($sql_logs);
}

/**
 * Set default options
 */
function wc_zoom_set_default_options() {
    $default_options = array(
        'wc_zoom_order_status' => 'completed',
        'wc_zoom_email_enabled' => 'yes',
        'wc_zoom_email_subject' => __('Your Zoom Meeting Details', 'wc-zoom-integration'),
        'wc_zoom_email_template' => __("Dear {customer_name},\n\nThank you for your order. Your Zoom meeting has been scheduled.\n\nMeeting Details:\n- Meeting ID: {meeting_id}\n- Join URL: {join_url}\n- Passcode: {passcode}\n- Start Time: {start_time}\n\nBest regards,\n{site_name}", 'wc-zoom-integration'),
        'wc_zoom_log_level' => 'error',
        'wc_zoom_log_retention_days' => 30,
        'wc_zoom_meeting_duration' => 60,
        'wc_zoom_meeting_timezone' => get_option('timezone_string', 'UTC'),
        'wc_zoom_meeting_password_enabled' => 'yes',
        'wc_zoom_meeting_waiting_room' => 'yes',
    );

    foreach ($default_options as $option_name => $option_value) {
        if (false === get_option($option_name)) {
            add_option($option_name, $option_value);
        }
    }
}

// Hooks
register_activation_hook(__FILE__, 'wc_zoom_activate');
register_deactivation_hook(__FILE__, 'wc_zoom_deactivate');
add_action('plugins_loaded', 'wc_zoom_init');

// Add settings link to plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_zoom_add_settings_link');

function wc_zoom_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wc-zoom-settings">' . __('Settings', 'wc-zoom-integration') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}