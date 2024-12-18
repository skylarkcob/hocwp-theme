<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait HOCWP_Theme_Import_Administrative_Boundaries {
	/**
	 * Really long running process
	 *
	 * @return int
	 */
	public function really_long_running_task() {
		return sleep( 5 );
	}

	public function import_commune( $commune, $taxonomy, $res, $ab ) {
		if ( $commune ) {
			$name = array_shift( $ab );
			$id   = array_shift( $ab );

			if ( ! term_exists( $name, $taxonomy, $res['term_id'] ) ) {
				$res = wp_insert_term( $name, $taxonomy, array( 'parent' => $res['term_id'] ) );

				if ( is_array( $res ) && isset( $res['term_id'] ) ) {
					update_term_meta( $res['term_id'], 'ab_id', $id );
					update_term_meta( $res['term_id'], 'ab_type', 'commune' );
					update_term_meta( $res['term_id'], 'ab_name', $name );
				}
			}
		}
	}

	public function import_district( $district, $commune, $taxonomy, $res, $ab ) {
		if ( $district && is_taxonomy_hierarchical( $taxonomy ) ) {
			$name = array_shift( $ab );
			$id   = array_shift( $ab );

			$exists = term_exists( $name, $taxonomy, $res['term_id'] );

			if ( ! $exists ) {
				$res = wp_insert_term( $name, $taxonomy, array( 'parent' => $res['term_id'] ) );

				if ( is_array( $res ) && isset( $res['term_id'] ) ) {
					update_term_meta( $res['term_id'], 'ab_id', $id );
					update_term_meta( $res['term_id'], 'ab_type', 'district' );
					update_term_meta( $res['term_id'], 'ab_name', $name );

					$this->import_commune( $commune, $taxonomy, $res, $ab );
				}
			} elseif ( is_array( $exists ) && isset( $exists['term_id'] ) ) {
				// Insert commune
				$this->import_commune( $commune, $taxonomy, $exists, $ab );
			}
		}
	}

	public function import_one_time( $csv, $taxonomy, $district, $commune ) {
		foreach ( $csv as $ab ) {
			$ab = explode( ',', $ab );

			$name = array_shift( $ab );
			$id   = array_shift( $ab );

			$exists = term_exists( $name, $taxonomy );

			if ( ! $exists ) {
				// Insert province
				$res = wp_insert_term( $name, $taxonomy );

				if ( is_array( $res ) && isset( $res['term_id'] ) ) {
					update_term_meta( $res['term_id'], 'ab_id', $id );
					update_term_meta( $res['term_id'], 'ab_type', 'province' );
					update_term_meta( $res['term_id'], 'ab_name', $name );

					// Insert district and commune
					$this->import_district( $district, $commune, $taxonomy, $res, $ab );
				}
			} elseif ( is_array( $exists ) && isset( $exists['term_id'] ) ) {
				// Insert district and commune
				$this->import_district( $district, $commune, $taxonomy, $exists, $ab );
			}
		}
	}

	private function import_child( $list, $taxonomy, $parent_id = null ) {
		if ( ht()->array_has_value( $list ) ) {
			foreach ( $list as $id => $data ) {
				$name = $data['name'];
				$type = $data['type'];

				unset( $data['name'], $data['type'] );

				$res = $this->import_item( $name, $id, $type, $taxonomy, $parent_id );

				if ( ht()->array_has_value( $data ) ) {
					if ( isset( $res['term_id'] ) ) {
						$this->import_child( $data, $taxonomy, $res['term_id'] );
					} else {
						$this->import_child( $data, $taxonomy );
					}
				}
			}
		}
	}

	public function background_import( $item ) {
		$id       = $item['id'];
		$taxonomy = $item['taxonomy'];
		$value    = $item['value'];

		$name = $value['name'];
		$type = $value['type'];

		unset( $value['name'], $value['type'] );

		$res = $this->import_item( $name, $id, $type, $taxonomy );

		if ( isset( $res['term_id'] ) ) {
			$this->import_child( $value, $taxonomy, $res['term_id'] );
		} else {
			$this->import_child( $value, $taxonomy );
		}
	}

	public function import_item( $name, $id, $type, $taxonomy, $parent = null ) {
		if ( ht()->is_positive_number( $parent ) ) {
			$exists = term_exists( $name, $taxonomy, $parent );
		} else {
			$exists = term_exists( $name, $taxonomy );
		}

		if ( ! $exists ) {
			if ( ht()->is_positive_number( $parent ) ) {
				$res = wp_insert_term( $name, $taxonomy, array( 'parent' => $parent ) );
			} else {
				$res = wp_insert_term( $name, $taxonomy );
			}

			if ( is_array( $res ) && isset( $res['term_id'] ) ) {
				update_term_meta( $res['term_id'], 'ab_id', $id );
				update_term_meta( $res['term_id'], 'ab_type', $type );
				update_term_meta( $res['term_id'], 'ab_name', $name );
			}

			return $res;
		}

		return null;
	}

	public function convert_to_array( $csv, $district, $commune ) {
		return ht_util()->convert_administrative_boundaries_to_array( $csv, $district, $commune );
	}
}