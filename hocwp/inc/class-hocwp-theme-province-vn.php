<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Province_VN {
	public $folder_path;
	public $parent_code;
	public $code;
	public $type;

	public function __construct( $folder_path = '', $code = '', $parent_code = '', $type = '' ) {
		$this->folder_path = $folder_path;
		$this->code        = $code;
		$this->parent_code = $parent_code;
		$this->type        = $type;
	}

	public function set_folder_path( $path ) {
		$this->folder_path = $path;
	}

	public function get_folder_path() {
		return $this->folder_path;
	}

	public function set_type( $type ) {
		$this->type = $type;
	}

	public function get_type() {
		if ( empty( $this->type ) ) {
			$this->type = 'tinh-thanh';
		}

		return $this->type;
	}

	public function get_list( $type = '' ) {
		if ( is_dir( $this->get_folder_path() ) ) {
			if ( empty( $type ) ) {
				$type = $this->get_type();
			}

			$path = trailingslashit( $this->get_folder_path() );

			switch ( $type ) {
				case 'quan-huyen':
				case 'xa-phuong':
					$path .= $type;

					if ( is_dir( $path ) ) {
						if ( ! empty( $this->parent_code ) && 'quan-huyen' == $type ) {
							$path = trailingslashit( $path ) . $this->parent_code . '.json';
						} elseif ( ! empty( $this->code ) ) {
							$path = trailingslashit( $path ) . $this->code . '.json';
						}
					}

					break;
				default:
					$path = trailingslashit( $path ) . 'tinh_tp.json';
			}

			if ( file_exists( $path ) ) {
				$tmp = ht_util()->read_all_text( $path );

				return ht()->json_string_to_array( $tmp );
			}
		}

		return null;
	}
}