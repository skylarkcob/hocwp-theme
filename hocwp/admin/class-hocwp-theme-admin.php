<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_Admin extends HOCWP_Theme_Utility {
	public static $instance;

	protected function __construct() {
		add_filter( 'update_plugin_complete_actions', array( $this, 'update_plugin_complete_actions_filter' ), 10, 2 );
	}

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function update_plugin_complete_actions_filter( $update_actions, $plugin ) {
		if ( isset( $_REQUEST['action'] ) && 'upgrade-plugin' == $_REQUEST['action'] ) {
			if ( ! is_array( $update_actions ) ) {
				$update_actions = array();
			}

			$slug = dirname( $plugin );

			$plugins = HT_Requirement()->get_required_plugins();

			if ( HT()->array_has_value( $plugins ) && in_array( $slug, $plugins ) ) {
				$update_actions['required_plugins_page'] = '<a href="' . admin_url( 'themes.php?page=hocwp_theme_plugins&tab=required' ) . '" target="_parent">' . __( 'Back to required plugins page', 'hocwp-theme' ) . '</a>';
			} else {
				$update_actions['theme_plugins_page'] = '<a href="' . admin_url( 'themes.php?page=hocwp_theme_plugins' ) . '" target="_parent">' . __( 'Back to theme plugins page', 'hocwp-theme' ) . '</a>';
			}
		}

		return $update_actions;
	}

	public function is_admin_page( $pages, $admin_page = '' ) {
		global $pagenow;

		if ( ! empty( $admin_page ) ) {
			global $plugin_page;

			if ( ! empty( $plugin_page ) && $admin_page != $plugin_page ) {
				return false;
			}
		}

		if ( is_string( $pages ) && $pagenow == $pages ) {
			return true;
		}

		return ( is_array( $pages ) && in_array( $pagenow, $pages ) ) ? true : false;
	}

	public function is_post_new_update_page() {
		return $this->is_admin_page( array( 'post.php', 'post-new.php' ) );
	}

	public function is_edit_post_new_update_page() {
		return ( $this->is_post_new_update_page() || $this->is_admin_page( 'edit.php' ) );
	}

	public function get_current_post_type() {
		global $post_type, $typenow;

		$result = $post_type;

		if ( empty( $result ) ) {
			$result = $typenow;
		}

		if ( empty( $result ) ) {
			if ( isset( $_GET['post_type'] ) ) {
				$result = $_GET['post_type'];
			} else {
				$action  = isset( $_GET['action'] ) ? $_GET['action'] : '';
				$post_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;

				if ( 'edit' == $action && HT()->is_positive_number( $post_id ) ) {
					$obj    = get_post( $post_id );
					$result = $obj->post_type;

					unset( $obj );
				}

				unset( $action, $post_id );
			}
		}

		return $result;
	}

	public function get_current_post_id( $post = null ) {
		if ( $post instanceof WP_Post ) {
			return $post->ID;
		}

		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : '';

		if ( isset( $_POST['post_ID'] ) ) {
			$post_id = $_POST['post_ID'];
		}

		if ( ! HT()->is_positive_number( $post_id ) && HT_Admin()->is_admin_page( 'post-new.php' ) ) {
			$post_id = HT_Admin()->get_current_new_post( 'ID' );
		}

		return $post_id;
	}

	public function get_current_new_post( $output = OBJECT ) {
		global $pagenow;
		$result = null;

		if ( 'post-new.php' == $pagenow ) {
			$query_args = array(
				'post_status'    => 'auto-draft',
				'orderby'        => 'date',
				'order'          => 'desc',
				'posts_per_page' => 1,
				'cache'          => false
			);

			$post_type = $this->get_current_post_type();

			if ( ! empty( $post_type ) ) {
				$query_args['post_type'] = $post_type;
			}

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {
				$result = array_shift( $query->posts );
			}

			unset( $query_args, $post_type, $query );
		}

		if ( OBJECT != $output && $result instanceof WP_Post ) {
			$result = $result->ID;
		}

		return $result;
	}

	public function is_theme_option_page() {
		return $this->is_admin_page( 'themes.php', HOCWP_Theme()->get_prefix() );
	}
}

function HT_Admin() {
	return HOCWP_Theme_Admin::get_instance();
}