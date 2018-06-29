<?php

class HOCWP_Theme_Admin_Setting_Tabs {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public $tabs = array();
	public $tab;
	public $tab_name;

	public function __construct() {
		$this->get_tab_name();

		if ( self::$instance instanceof self ) {
			return;
		}

		load_template( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-admin-setting-tab.php' );
	}

	public function get_tab_name() {
		if ( empty( $this->tab_name ) ) {
			$this->tab_name = HT()->get_method_value( 'tab', 'request', 'general' );
		}

		return $this->tab_name;
	}

	public function add( $tab ) {
		if ( $tab instanceof HOCWP_Theme_Admin_Setting_Tab ) {
			if ( ! isset( $this->tabs[ $tab->name ] ) ) {
				$this->tabs[ $tab->name ] = $tab;
			}
		}
	}

	public function sanitize( $name, $tab ) {
		if ( ! ( $tab instanceof HOCWP_Theme_Admin_Setting_Tab ) ) {
			if ( is_array( $tab ) ) {
				$label = isset( $tab['text'] ) ? $tab['text'] : '';
			} else {
				$label = $tab;
			}

			$icon = ( is_array( $tab ) && isset( $tab['icon'] ) ) ? $tab['icon'] : '';

			$tab = new HOCWP_Theme_Admin_Setting_Tab( $name, $label, $icon );
		}

		return $tab;
	}

	public function get_by_name( $name ) {
		return ( isset( $this->tabs[ $name ] ) ) ? $this->tabs[ $name ] : null;
	}

	public function get() {
		$this->tabs = apply_filters( 'hocwp_theme_settings_page_tabs', $this->tabs );

		foreach ( $this->tabs as $name => $tab ) {
			$this->tabs[ $name ] = $this->sanitize( $name, $tab );
		}

		if ( ! ( $this->tab instanceof HOCWP_Theme_Admin_Setting_Tab ) ) {
			$this->tab = $this->get_by_name( $this->tab_name );
		}

		return $this->tabs;
	}

	public function html() {
		$this->get();

		if ( HT()->array_has_value( $this->tabs ) ) {
			?>
			<div id="nav">
				<ul class="nav-tab-wrapper">
					<?php
					$current_url = HT_Util()->get_current_url( true );
					$current_url = remove_query_arg( 'settings-updated', $current_url );

					$count = 0;

					foreach ( $this->tabs as $key => $tab ) {
						if ( $tab instanceof HOCWP_Theme_Admin_Setting_Tab ) {
							$url   = add_query_arg( array( 'tab' => $key ), $current_url );
							$class = 'nav-tab';
							$icon  = $tab->icon;

							$li_class = 'menu-item';

							if ( ( ( $this->tab instanceof HOCWP_Theme_Admin_Setting_Tab ) && $this->tab->name == $key ) || ( empty( $this->tab ) && 0 == $count ) ) {
								$class .= ' nav-tab-active';
								$li_class .= ' active';
							}

							$text = $tab->label;
							$text = $icon . ' ' . $text;
							?>
							<li class="<?php echo $li_class; ?>">
								<a class="<?php echo $class; ?>"
								   href="<?php echo esc_url( $url ); ?>"><?php echo $text; ?></a>
							</li>
							<?php
							$count ++;
						}
					}
					?>
				</ul>
			</div>
			<?php
		}
	}
}

function HT_Admin_Setting_Tabs() {
	return HOCWP_Theme_Admin_Setting_Tabs::get_instance();
}