<?php
defined('ABSPATH') || exit;

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
 * Log security events.
 */
function ht_security_log($event, $details = '')
{
    error_log(
        sprintf(
            __('[HT Security] %s | %s | IP: %s', 'hocwp-theme'),
            $event,
            $details,
            sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown')
        )
    );
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

    $user = wp_get_current_user();

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
