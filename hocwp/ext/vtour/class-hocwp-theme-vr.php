<?php
defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'HT_VR_Tour_Template' ) ) {
	require_once( __DIR__ . '/trait-template.php' );
}

class HOCWP_Theme_VR {
	use HT_VR_Tour_Template;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu_action' ), 99999 );
		add_action( 'wp_footer', array( $this, 'wp_footer_action' ), 9 );
	}

	public function admin_menu_action() {
		add_filter( 'hocwp_theme_setting_fields', array( $this, 'general_setting_fields_filter' ), 99999, 2 );

		add_filter( 'hocwp_theme_settings_page_mobile_settings_field', array(
			$this,
			'mobile_setting_fields_filter'
		), 99999 );

		add_filter( 'hocwp_theme_settings_page_tabs', array( $this, 'setting_tabs_filter' ), 9999 );
	}

	public function setting_tabs_filter( $tabs ) {
		unset( $tabs['home'] );

		return $tabs;
	}

	public function mobile_setting_fields_filter( $fields ) {
		$fields[] = new HOCWP_Theme_Admin_Setting_Field( 'intro_image', __( 'Intro Image', 'hocwp-theme' ), 'media_upload', array(), 'id', 'mobile' );

		return $fields;
	}

	public function general_setting_fields_filter( $fields, $options ) {
		$args = array(
			'fields' => array(
				'header'  => array(
					'callback' => 'editor',
					'label'    => __( 'Header:', 'hocwp-theme' )
				),
				'post_id' => array(
					'callback' => 'select_page',
					'label'    => __( 'Page:', 'hocwp-theme' )
				)
			)
		);

		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'contact', __( 'Contact', 'hocwp-theme' ), 'fields', $args, 'array' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'hotline', __( 'Hotline', 'hocwp-theme' ) );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'hotline_icon', __( 'Hotline Icon', 'hocwp-theme' ), 'media_upload', array(), 'id' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'bg_music', __( 'Background Music', 'hocwp-theme' ), 'media_upload', array( 'media_type' => 'file' ), 'array' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'loading_image', __( 'Loading Image', 'hocwp-theme' ), 'media_upload', array(), 'id' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'loading_text', __( 'Loading Text', 'hocwp-theme' ) );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'intro_image', __( 'Intro Image', 'hocwp-theme' ), 'media_upload', array(), 'id' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'intro_music', __( 'Intro Music', 'hocwp-theme' ), 'media_upload', array( 'media_type' => 'file' ), 'array' );
		$fields[] = new HOCWP_Theme_Admin_Setting_Field_General( 'right_logo', __( 'Right Logo', 'hocwp-theme' ), 'image_link', array(), 'array', 'site_identity' );

		return $fields;
	}

	public function audio_html( $music, $html_id, $loop = null, $class = '', $hidden = true ) {
		if ( ! empty( $music ) ) {
			$music = ht_media()->sanitize_value( $music );

			$id  = $music['id'] ?? '';
			$url = $music['url'] ?? '';

			$class .= ' player';

			if ( ( is_bool( $loop ) && $loop ) ) {
				$class .= ' loop';
				$loop  = '';
			}

			if ( ht()->is_positive_number( $loop ) ) {
				$class .= ' loop';
			} else {
				$loop = '';
			}

			if ( $hidden ) {
				$class .= ' hidden';
			}
			?>
            <div class="sound-box" data-id="<?php echo esc_attr( $html_id ); ?>">
                <audio id="<?php echo esc_attr( $html_id ); ?>" data-media-id="<?php echo esc_attr( $id ); ?>"
                       class="<?php echo esc_attr( $class ); ?>" data-interval="<?php echo esc_attr( $loop ); ?>"
                       controls>
                    <source src="<?php echo esc_url( $url ); ?>"
                            type="<?php echo esc_attr( $music['mime'] ); ?>">
					<?php echo ht_message()->browser_not_support_audio(); ?>
                </audio>
            </div>
			<?php
		}
	}

	public function wp_footer_action() {
		$this->custom_html();
	}

	public function detect_vr_folder() {
		$directories = glob( untrailingslashit( ABSPATH ) . '/*', GLOB_ONLYDIR );

		$excludes = array( 'wp-content', 'wp-admin', 'wp-includes' );

		$folder = '';

		foreach ( $directories as $name ) {
			$bn = basename( $name );

			if ( ! in_array( $bn, $excludes ) ) {
				if ( file_exists( $name . '/tour.xml' ) ) {
					$folder = $bn;
					break;
				}
			}
		}

		if ( empty( $folder ) ) {
			$folder = 'vtour';
		}

		return apply_filters( 'hocwp_theme_vr_tour_folder_name', $folder );
	}

	public function get_vr_dir() {
		return apply_filters( 'hocwp_theme_vr_tour_dir', ABSPATH . $this->detect_vr_folder() );
	}

	public function get_vr_url() {
		return apply_filters( 'hocwp_theme_vr_tour_url', home_url( $this->detect_vr_folder() ) );
	}

	public function custom_html() {
		include_once __DIR__ . '/custom-html.php';
	}
}

new HOCWP_Theme_VR();

function ht_vr() {
	return HOCWP_Theme_VR::instance();
}