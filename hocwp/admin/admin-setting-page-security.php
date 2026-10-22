<?php
defined('ABSPATH') || exit;

$tab = new HOCWP_Theme_Admin_Setting_Tab('security', __('Security', 'hocwp-theme'), '<span class="dashicons dashicons-shield"></span>');

$tab->add_field_array(array(
    'id'    => 'admin_list',
    'title' => __('Admin List', 'hocwp-theme'),
    'args'  => array(
        'type'          => 'text',
        'description'  => __('Enter the path to a text file containing a list of admin usernames separated by comma. For example: admin, hocwp, user1. Only users in this list will be able to access the admin area.', 'hocwp-theme'),
        'callback_args' => array(
            'type' => 'text'
        )
    )
));
