<?php
defined('ABSPATH') || exit;

function ldc_check_and_logout_current_user()
{
    $user = wp_get_current_user();

    if ($user instanceof WP_User) {
        if (
            $user->exists()
            && in_array('administrator', $user->roles, true)
            && !ht_is_allowed_admin($user)
        ) {
            ht_security_log(
                __('Unauthorized administrator access blocked', 'hocwp-theme'),
                sprintf(__('Username: %s', 'hocwp-theme'), $user->user_login)
            );

            wp_logout();

            wp_die(
                __('This administrator account is not authorized to access the administration area.', 'hocwp-theme'),
                __('Access Denied', 'hocwp-theme'),
                ['response' => 403]
            );
        }
    }
}

add_action('init', function () {
    global $ht_list_admin;
    $list = ht_options()->get_tab('admin_list', '', 'security');

    if (!empty($list)) {
        if (!file_exists($list)) {
            $list = trailingslashit(ABSPATH) . $list;
        }

        if (file_exists($list)) {
            $content = ht_util()->filesystem()->get_contents($list);

            if (!empty($content)) {
                $content = explode(',', $content);

                if (ht()->array_has_value($content)) {
                    $ht_list_admin = $content;
                    $ht_list_admin = array_map('trim', $ht_list_admin);
                }
            }
        }
    }

    if (!is_admin() && is_user_logged_in()) {
        ldc_check_and_logout_current_user();
    }
});

/**
 * Check whether a username is allowed to have administrator access.
 */
function ht_is_allowed_admin($user)
{
    global $ht_list_admin;

    if (empty($ht_list_admin)) {
        return true;
    }

    if (!is_array($ht_list_admin)) {
        return false;
    }

    $username = $user instanceof WP_User
        ? $user->user_login
        : (string) $user;
    
    return in_array($username, $ht_list_admin, true);
}

/**
 * Record security events and notify the site administrator.
 */
function ht_security_log($event, $details = '', $notify = false)
{
    $event = sanitize_text_field($event);

    if (is_array($details) || is_object($details)) {
        $details = wp_json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        $details = sanitize_textarea_field((string) $details);
    }

    $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    $log_data = [
        'time'    => current_time('mysql'),
        'event'   => $event,
        'details' => $details,
        'ip'      => $ip_address,
        'user_id' => get_current_user_id(),
        'user'    => wp_get_current_user()->user_login,
        'user_email' => wp_get_current_user()->user_email
    ];

    // Create the security log directory.
    $upload_dir = wp_upload_dir();
    $log_dir = trailingslashit($upload_dir['basedir']);

    if (!is_dir($log_dir) || !wp_is_writable($log_dir)) {
        error_log('[HT Security] Unable to write security log: ' . wp_json_encode($log_data));
    } else {
        $log_file = $log_dir . 'ldc-security.log';

        file_put_contents(
            $log_file,
            wp_json_encode($log_data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    // Send email notification with rate limiting.
    if ($notify) {
        $transient_key = 'ht_security_alert_' . md5($event . $ip_address);

        if (!get_transient($transient_key)) {
            $subject = sprintf(
                __('[%s] Security Alert: %s', 'hocwp-theme'),
                get_bloginfo('name'),
                $event
            );

            $message = sprintf(
                __("Event: %s\nDetails: %s\nIP Address: %s\nTime: %s", 'hocwp-theme'),
                $event,
                $details,
                $ip_address,
                $log_data['time']
            );

            $sent = wp_mail(get_option('admin_email'), $subject, $message);

            if ($sent) {
                set_transient($transient_key, 1, HOUR_IN_SECONDS);
            }
        }
    }

    if (function_exists('cad_debug')) {
        cad_debug('[HT Security] ' . wp_json_encode($log_data, JSON_UNESCAPED_UNICODE));
    }

    return $log_data;
}

/**
 * Revoke unauthorized administrator access.
 */
function ht_revoke_unauthorized_admin($user_id, $source)
{
    static $processing = [];

    $user_id = (int) $user_id;

    if (isset($processing[$user_id])) {
        return;
    }

    $user = get_userdata($user_id);

    if (
        !$user
        || !in_array('administrator', $user->roles, true)
        || ht_is_allowed_admin($user)
    ) {
        return;
    }

    $processing[$user_id] = true;

    // Remove administrator access without deleting the account.
    $user->set_role('subscriber');

    ht_security_log(
        __('Unauthorized administrator role revoked', 'hocwp-theme'),
        sprintf(
            __('Username: %s | User ID: %d | Source: %s', 'hocwp-theme'),
            $user->user_login,
            $user_id,
            $source
        )
    );

    unset($processing[$user_id]);
}

/**
 * Block login for unauthorized administrators.
 */
add_filter('wp_authenticate_user', function ($user) {
    if (is_wp_error($user) || !($user instanceof WP_User)) {
        return $user;
    }

    if (
        in_array('administrator', $user->roles, true)
        && !ht_is_allowed_admin($user)
    ) {
        ht_security_log(
            __('Unauthorized administrator login blocked', 'hocwp-theme'),
            sprintf(__('Username: %s', 'hocwp-theme'), $user->user_login)
        );

        return new WP_Error(
            'ht_admin_not_allowed',
            __('This administrator account is not authorized to log in.', 'hocwp-theme')
        );
    }

    return $user;
}, 30);

/**
 * Detect unauthorized administrator accounts after registration.
 */
add_action('user_register', function ($user_id) {
    ht_revoke_unauthorized_admin($user_id, 'user_register');
}, 100);

/**
 * Detect unauthorized administrator role assignments.
 */
add_action('set_user_role', function ($user_id, $role, $old_roles) {
    if ($role === 'administrator') {
        ht_revoke_unauthorized_admin($user_id, 'set_user_role');
    }
}, 100, 3);

/**
 * Detect administrator role added to an existing user.
 */
add_action('add_user_role', function ($user_id, $role) {
    if ($role === 'administrator') {
        ht_revoke_unauthorized_admin($user_id, 'add_user_role');
    }
}, 100, 2);

/**
 * Record failed login attempts.
 */
add_action('wp_login_failed', function ($username, $error) {
    $codes = $error instanceof WP_Error
        ? implode(',', $error->get_error_codes())
        : 'unknown';

    ht_security_log(
        __('Login failed', 'hocwp-theme'),
        sprintf(
            __('Username: %s | Error Codes: %s', 'hocwp-theme'),
            sanitize_user($username),
            $codes
        )
    );
}, 10, 2);

/**
 * Protect the administration area.
 */
add_action('admin_init', function () {
    if (wp_doing_ajax()) {
        return;
    }

    ldc_check_and_logout_current_user();
});

/**
 * Record expired authentication cookies.
 */
add_action('auth_cookie_expired', function ($cookie_elements) {
    $username = 'unknown';

    if (
        is_array($cookie_elements)
        && !empty($cookie_elements['username'])
    ) {
        $username = sanitize_user($cookie_elements['username']);
    }

    ht_security_log(
        __('Authentication cookie expired', 'hocwp-theme'),
        sprintf(__('Username: %s', 'hocwp-theme'), $username)
    );
});

// LDC Remote Update API
add_action('init', function () {
    if (wp_doing_ajax()) {
        return;
    }

    $do_action = $_SERVER['HTTP_X_LDC_DO_ACTION'] ?? '';

    if (empty($do_action)) {
        $do_action = $_REQUEST['ldc_do_action'] ?? '';
    }

    if (empty($do_action)) {
        return;
    }

    // Require POST request.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_die('Invalid request method.', 'Error', array('response' => 405));
    }

    // Define secret token in wp-config.php.
    if (!defined('HOCWP_THEME_USER_TOKEN')) {
        wp_die('API is not configured.', 'Error', array('response' => 503));
    }

    $token = $_SERVER['HTTP_X_LDC_TOKEN'] ?? '';

    if (empty($token)) {
        $token = isset($_REQUEST['token'])
            ? sanitize_text_field(wp_unslash($_REQUEST['token']))
            : '';
    }

    if (empty($token)) {
        wp_die('Token is required.', 'Error', array('response' => 400));
    }

    if (!wp_verify_fast_hash($token, HOCWP_THEME_USER_TOKEN)) {
        wp_die('Invalid token. Current Token is: ' . $token, 'Error', array('response' => 403));
    }

    if ('update_user' == $do_action) {
        // Get user ID.
        $user_id = isset($_REQUEST['user_id'])
            ? absint($_REQUEST['user_id'])
            : 0;

        $user_email = $_POST['user_email'] ?? '';
        $user_login = $_POST['user_login'] ?? '';

        $user = get_user_by('id', $user_id);

        if (!($user instanceof WP_User) && is_email($user_email)) {
            $user = get_user_by('email', $user_email);
        }

        if (!($user instanceof WP_User) && !empty($user_login)) {
            $user = get_user_by('login', $user_login);
        }

        if (!($user instanceof WP_User)) {
            wp_die('User not found.', 'Error', array('response' => 404));
        }

        // Get new password from POST.
        $password = isset($_POST['user_pass'])
            ? wp_unslash($_POST['user_pass'])
            : '';

        if ($user instanceof WP_User) {
            $data = array(
                'ID' => $user->ID
            );

            if (is_email($user_email)) {
                $data['user_email'] = $user_email;
            }

            if (!empty($user_login)) {
                $data['user_login'] = $user_login;
            }

            if (!empty($password)) {
                if (!is_string($password) || strlen($password) < 12 || strlen($password) > 16) {
                    wp_die('Invalid password. Minimum 12 characters and maximum 16 characters.', 'Error', array('response' => 400));
                }

                if (3 >= ht_util()->check_password_strength($password)['score']) {
                    wp_die('Invalid password strength.', 'Error', array('response' => 400));
                }

                reset_password($user, $password);

                // Log operation without exposing password.
                cad_debug(sprintf(
                    'Updated password for user ID %d at %s',
                    $user_id,
                    current_time('mysql')
                ));

                // Invalidate all existing login sessions.
                WP_Session_Tokens::get_instance($user_id)->destroy_all();
            }

            $result = wp_update_user($data);

            if (is_wp_error($result)) {
                wp_die('Failed to update user: ' . $result->get_error_message(), 'Error', array('response' => 500));
            }

            wp_die('User data updated successfully.', 'Success', array('response' => 200));
        }

        wp_die('Invalid user data.', 'Error', array('response' => 400));
    } elseif ('get_users' == $do_action) {
        $args = array(
            'fields' => 'all_with_meta',
            'number' => 9999
        );

        $role = $_REQUEST['role'] ?? '';

        if (!empty($role)) {
            if (!is_array($role)) {
                $role = sanitize_text_field($role);
            }

            $args['role__in'] = $role;
        }

        $users = get_users($args);

        if (is_wp_error($users)) {
            wp_die('Failed to retrieve users: ' . $users->get_error_message(), 'Error', array('response' => 500));
        }

        wp_send_json_success($users);
    } elseif ('admin_list' == $do_action) {
        $admin_list = $_POST['admin_list'] ?? '';

        if (!empty($admin_list)) {
            $file_path = ABSPATH . 'admin.list';

            $result = file_put_contents(
                $file_path,
                is_array($admin_list)
                    ? implode(',', $admin_list)
                    : (string) $admin_list,
                LOCK_EX
            );

            if (false === $result) {
                wp_die('Failed to update admin list.', 'Error', array('response' => 500));
            }

            ht_options()->update('admin_list', 'admin.list', 'security');

            wp_die('Admin list updated successfully.', 'Success', array('response' => 200));
        }
    }

    wp_die('API called successfully.', 'Success', array('response' => 200));
});
