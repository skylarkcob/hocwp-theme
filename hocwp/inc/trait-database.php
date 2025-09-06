<?php
defined( 'ABSPATH' ) || exit;

trait HOCWP_Theme_Database {
	public function get_database_size( $database ) {
		global $wpdb;

		$sql = "SELECT table_schema AS `Database`, 
            SUM(data_length + index_length) AS `Size` 
            FROM information_schema.tables 
            WHERE table_schema = %s 
            GROUP BY table_schema";

		$prepared_sql = $wpdb->prepare( $sql, $database );

		$size = false;

		try {
			$result = $wpdb->get_row( $prepared_sql, ARRAY_A );

			if ( $result ) {
				$size = (float) $result['Size'];
			} else {
				$size = 0;
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'get_size_failed', sprintf( __( 'Failed to get database size: %s', 'hocwp-theme' ), $e->getMessage() ) );
		}

		return $size;
	}

	public function import_database( $file_path, $db_config = '' ) {
		$error = new WP_Error();

		if ( ! file_exists( $file_path ) ) {
			$error->add( 'invalid_file', sprintf( __( 'File not found: %s', 'hocwp-theme' ), $file_path ) );
		}

		$extension = pathinfo( $file_path, PATHINFO_EXTENSION );

		switch ( strtolower( $extension ) ) {
			case 'sql':
				break;
			case 'zip':
				if ( ! class_exists( 'ZipArchive' ) ) {
					$error->add( 'missing_ziparchive', __( 'Cannot open zip file.', 'hocwp-theme' ) );

					return $error;
				}

				$zip = new ZipArchive;

				if ( $zip->open( $file_path ) === true ) {
					$tmp_dir = trailingslashit( WP_CONTENT_DIR ) . 'temp';
					$tmp_dir = trailingslashit( $tmp_dir ) . uniqid( 'extract-' );

					$zip->extractTo( $tmp_dir );
					$zip->close();

					$sql_file = '';

					$tmp_files = ht()->scandir( $tmp_dir, true );

					foreach ( $tmp_files as $file ) {
						if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'sql' ) {
							$sql_file = $file;
							break;
						}
					}

					$file_system = ht_util()->filesystem();

					if ( ! empty( $sql_file ) ) {
						$tmp = dirname( $sql_file );

						// Rename forlder has whitespace in name
						while ( $tmp != $tmp_dir && ( str_contains( $tmp, '\\' ) || str_contains( $tmp, '/' ) ) ) {
							$name = basename( $tmp );
							$tmp  = dirname( $tmp );

							if ( str_contains( $name, ' ' ) ) {
								rename( $tmp . '/' . $name, $tmp . '/' . str_replace( ' ', '-', $name ) );
							}
						}

						$sql_file = str_replace( ' ', '-', $sql_file );

						$res = $this->import_database( $sql_file, $db_config );
						$file_system->rmdir( $tmp_dir, true );

						return $res;
					} else {
						$error->add( 'missing_sql_file', __( 'SQL file not found in the ZIP archive.', 'hocwp-theme' ) );
					}

					if ( is_dir( $tmp_dir ) ) {
						$file_system->rmdir( $tmp_dir, true );
					}
				} else {
					$error->add( 'open_zip_failed', sprintf( __( 'Failed to open ZIP archive: %s', 'hocwp-theme' ), esc_html( $file_path ) ) );
				}

				return $error;
			case 'gz':
				if ( ! function_exists( 'gzopen' ) ) {
					$error->add( 'missing_gz', __( 'Cannot open gzip file.', 'hocwp-theme' ) );

					return $error;
				}

				$file_system = ht_util()->filesystem();

				$tmp_dir = trailingslashit( WP_CONTENT_DIR ) . 'temp';

				$sql_file = trailingslashit( $tmp_dir ) . uniqid() . '-temp.sql';

				$gzipped = gzopen( $file_path, 'rb' );

				if ( false === $gzipped ) {
					$error->add( 'open_file_failed', sprintf( __( 'Failed to open .gz file: %s', 'hocwp-theme' ), $file_path ) );

					return $error;
				}

				$uncompressed = $file_system->put_contents( $sql_file, '', FS_CHMOD_FILE );

				$size = $file_system->size( $file_path );

				$buffer = '';

				while ( ! gzeof( $gzipped ) ) {
					$chunk = gzread( $gzipped, $size );

					if ( $chunk === false ) {
						$error->add( 'read_file_failed', sprintf( __( 'Failed to read from .gz file: %s', 'hocwp-theme' ), esc_html( $file_path ) ) );
					}

					$buffer .= $chunk;
				}

				gzclose( $gzipped );

				$file_system->put_contents( $sql_file, $buffer, FS_CHMOD_FILE );

				$res = $this->import_database( $sql_file, $db_config );
				$file_system->delete( $sql_file, true );

				return $res;
			default:
				$error->add( 'unsupported_extension', sprintf( __( 'Unsupported file extension: %s', 'hocwp-theme' ), esc_html( $extension ) ) );
		}

		$file_system = ht_util()->filesystem();

		$file_path = str_replace( '\\', '/', $file_path );
		$content   = $file_system->get_contents( $file_path );

		if ( empty( $content ) ) {
			$error->add( 'cannot_open_file', sprintf( __( 'Failed to open file: %s', 'hocwp-theme' ), $file_path ) );

			return $error;
		}

		global $wpdb;
		$db = $wpdb;

		if ( ! empty( $db_config ) ) {
			if ( is_string( $db_config ) ) {
				$db_config = array( 'db_name' => $db_config );
			}

			if ( ht()->array_has_value( $db_config ) ) {
				$db_name = $db_config['db_name'] ?? '';

				if ( ! empty( $db_name ) && $db_name != DB_NAME ) {
					$db_user = $db_config['db_user'] ?? '';

					if ( empty( $db_user ) ) {
						$db_user = DB_USER;
					}

					$db_pass = $db_config['db_password'] ?? '';

					if ( empty( $db_pass ) ) {
						$db_pass = DB_PASSWORD;
					}

					$db_host = $db_config['db_host'] ?? '';

					if ( empty( $db_host ) ) {
						$db_host = DB_HOST;
					}

					$db = new wpdb( $db_user, $db_pass, $db_name, $db_host );
				}
			}
		}

		$query = '';

		$db->query( 'START TRANSACTION' );

		$lines = explode( "\n", $content );

		foreach ( $lines as $line ) {
			$trimmed_line = trim( $line );

			if ( empty( $trimmed_line ) || str_starts_with( $trimmed_line, '--' ) || str_starts_with( $trimmed_line, '/*' ) || str_starts_with( $trimmed_line, '//' ) ) {
				continue;
			}

			$query .= $line;

			if ( str_ends_with( trim( $query ), ';' ) ) {
				if ( preg_match( '/CREATE TABLE `?(\w+)`?/', $query, $matches ) ) {
					$tableName = $matches[1];

					$db->query( "DROP TABLE IF EXISTS `$tableName`" );
				}

				try {
					$db->query( $query );
				} catch ( Exception $e ) {
					$error->add( 'query_error_' . md5( $query ), sprintf( __( 'Error executing query: %s', 'hocwp-theme' ), $e->getMessage() ) );
				}

				$query = '';
			}
		}

		$db->query( 'COMMIT' );

		if ( $error->has_errors() ) {
			return $error;
		}

		return true;
	}

	public function export_database( $db_name = '', $destination = '' ) {
		if ( ! function_exists( 'exec' ) ) {
			return false;
		}

		if ( empty( $db_name ) ) {
			$db_name = DB_NAME;
		}

		$name = $db_name;
		$user = DB_USER;
		$pass = DB_PASSWORD;

		if ( stripos( PHP_OS, 'WIN' ) !== false ) {
			$root = dirname( $_SERVER['DOCUMENT_ROOT'] );
			$root = trailingslashit( $root ) . 'mysql/bin/mysqldump';
		} else {
			$root = 'mysqldump';
		}

		if ( empty( $destination ) ) {
			$destination = trailingslashit( ABSPATH ) . $db_name . '.sql';
		} else {
			// Check if destionation does not end with a valid file
			$ext = strtolower( pathinfo( $destination, PATHINFO_EXTENSION ) );

			if ( empty( $ext ) ) {
				$ext = $db_name . '_' . current_time( 'timestamp' ) . '.sql';

				// Name will be like path/db_name_1757172999.sql
				$destination = trailingslashit( $destination ) . $ext;
			}
		}

		$dir = dirname( $destination );

		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0777, true ); // Create folders first
		}

		$cmd = $root . " -u$user -p$pass $name > $destination";

		$res = call_user_func( 'exec', $cmd );

		if ( '' == $res ) {
			return $destination;
		}

		return false;
	}

	public function get_min_max_meta( $meta_key, $type = 'min' ) {
		$type = strtolower( $type );

		global $wpdb;

		$sql = "SELECT ";

		if ( 'min' == $type ) {
			$sql .= 'MIN';
		} else {
			$sql .= 'MAX';
		}

		$sql .= "(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = '" . $meta_key . "'";

		$number = absint( $wpdb->get_var( $sql ) );

		return apply_filters( 'hocwp_theme_min_max_value', $number, $meta_key, $type );
	}

	public function update_user_activation_key( $user, $key ) {
		global $wp_hasher, $wpdb;

		if ( empty( $wp_hasher ) ) {
			require_once( ABSPATH . WPINC . '/class-phpass.php' );
			$wp_hasher = new PasswordHash( 8, true );
		}

		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array(
			'user_login' => $user->user_login,
			'ID'         => $user->ID
		) );

		$_SESSION['user_activation_key'] = $hashed;

		return $hashed;
	}

	public function get_table_prefix() {
		global $wpdb;

		if ( is_multisite() ) {
			return $wpdb->base_prefix;
		} else {
			return $wpdb->get_blog_prefix( 0 );
		}
	}

	public function create_database_table( $table_name, $sql_column ) {
		if ( str_contains( $sql_column, 'CREATE TABLE' ) || str_contains( $sql_column, 'create table' ) ) {
			ht_util()->doing_it_wrong( __FUNCTION__, __( 'The <strong>$sql_column</strong> argument just only contains MySQL query inside (), it isn\'t full MySQL query.', 'hocwp-theme' ), '6.5.2' );

			return;
		}

		global $wpdb;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			/** @noinspection SqlNoDataSourceInspection */
			$sql = "CREATE TABLE ";
			$sql .= "$table_name ( $sql_column ) $charset_collate;\n";

			if ( ! function_exists( 'dbDelta' ) ) {
				load_template( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			dbDelta( $sql );
		}
	}

	public function is_database_table_exists( $table_name ) {
		global $wpdb;

		if ( ! ht()->string_contain( $table_name, $wpdb->prefix ) ) {
			$table_name = $wpdb->prefix . $table_name;
		}

		$result = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );

		if ( empty( $result ) ) {
			return false;
		}

		return true;
	}

	public function delete_transient( $transient_name = '' ) {
		global $wpdb;

		/** @noinspection SqlNoDataSourceInspection */
		$query_root = "DELETE FROM $wpdb->options";
		$query_root .= " WHERE option_name like %s";
		$key_1      = '_transient_';
		$key_2      = '_transient_timeout_';

		if ( ! empty( $transient_name ) ) {
			$transient_name = '%' . $transient_name . '%';

			$key_1 .= $transient_name;
			$key_2 .= $transient_name;
		}

		$key_1 = $wpdb->prepare( $query_root, $key_1 );
		$key_2 = $wpdb->prepare( $query_root, $key_2 );

		$wpdb->query( $key_1 );
		$wpdb->query( $key_2 );
	}
}