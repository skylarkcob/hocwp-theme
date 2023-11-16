<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_ids    = '';
$post_type   = '';
$search_post = '';
?>
<div class="wrap delete-posts-php">
    <h1><?php _e( 'Delete Posts', 'hocwp-theme' ); ?></h1>
    <p class="description"><?php _e( 'With this tool, you can search and delete any posts completely automatically.', 'hocwp-theme' ); ?></p>
    <hr class="wp-header-end" style="clear: both;">
    <h2 class="screen-reader-text"><?php _e( 'Search and delete posts', 'hocwp-theme' ); ?></h2>
    <form id="delete-posts-form" method="post" class="delete-posts-form" action="">
		<?php
		if ( isset( $_POST['submit_form'] ) ) {
			$post_ids    = $_POST['post_ids'] ?? '';
			$post_type   = $_POST['delete_post_type'] ?? '';
			$search_post = $_POST['search_post'] ?? '';

			if ( ! empty( $post_ids ) || ! empty( $search_post ) ) {
				$post_ids = explode( ',', $post_ids );
				$post_ids = array_map( 'trim', $post_ids );

				$search_post = explode( ',', $search_post );
				$search_post = array_map( 'trim', $search_post );

				$dels = array_merge( $post_ids, $search_post );
				$dels = array_unique( $dels );
				$dels = array_filter( $dels );

				$confirm_delete = $_POST['confirm_delete'] ?? '';

				if ( empty( $confirm_delete ) ) {
					$msg = '';

					foreach ( $dels as $key => $id ) {
						$pt = get_post_type( $id );

						if ( ! empty( $post_type ) && $pt != $post_type ) {
							unset( $dels[ $key ] );
							continue;
						}

						if ( empty( $pt ) ) {
							$pt = __( 'unknown post type', 'hocwp-theme' );
						}

						$msg .= sprintf( '<strong>%s (%s - %s)</strong>, ', get_the_title( $id ), $id, $pt );
					}

					if ( ! empty( $msg ) ) {
						$msg = rtrim( $msg, ', ' );
					}

					if ( ! empty( $msg ) ) {
						$confirm = get_submit_button( 'Yes', 'primary small', 'yes_delete', false );
						$confirm .= '&nbsp;';
						$confirm .= get_submit_button( 'No', 'default small', 'no_delete', false );
						?>
                        <div class="notice notice-warning is-dismissible">
                            <p><?php printf( __( '<strong>Warning:</strong> You are preparing to permanently delete posts: %s.', 'hocwp-theme' ), $msg ); ?></p>
                            <p class="confirm"><?php printf( __( 'Are you sure? %s', 'hocwp-theme' ), $confirm ); ?></p>
                        </div>
						<?php
					}
				}

				if ( 1 == $confirm_delete ) {
					$count = 0;

					foreach ( $dels as $id ) {
						$result = wp_delete_post( $id, true );

						if ( $result instanceof WP_Post ) {
							$count ++;
						}
					}

					if ( 0 < $count ) {
						$msg = sprintf( __( '%s posts have been deleted!', 'hocwp-theme' ), number_format_i18n( $count ) );
					} else {
						$msg = __( 'No posts are deleted.', 'hocwp-theme' );
					}
					?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php echo $msg; ?></p>
                    </div>
					<?php
					$post_ids = '';
				}
			}
		}

		if ( is_array( $post_ids ) ) {
			$post_ids = join( ', ', $post_ids );
		}

		$post_types = get_post_types( array(), 'objects' );
		?>
        <input id="confirmDelete" name="confirm_delete" value="" type="hidden">
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="delete_post_type"><?php _e( 'Post Type', 'hocwp-theme' ); ?></label>
                </th>
                <td>
                    <select name="delete_post_type" id="delete_post_type" class="regular-text">
                        <option value=""><?php _e( 'Choose post type', 'hocwp-theme' ); ?></option>
						<?php
						foreach ( $post_types as $pt ) {
							?>
                            <option value="<?php echo esc_attr( $pt->name ); ?>"<?php selected( $pt->name, $post_type ) ?>><?php echo $pt->labels->singular_name; ?>
                                (<?php echo $pt->name; ?>)
                            </option>
							<?php
						}
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="post_ids"><?php _e( 'Post IDs', 'hocwp-theme' ); ?></label>
                </th>
                <td>
                    <input name="post_ids" type="text" id="post_ids" value="<?php echo esc_attr( $post_ids ); ?>"
                           class="regular-text">
                    <p class="desc description"><?php _e( 'Enter any post ID you want to remove, each ID separated by a commas.', 'hocwp-theme' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="search_post"><?php _e( 'Search Post', 'hocwp-theme' ); ?></label>
                </th>
                <td>
                    <input name="search_post" type="text" id="search_post" value=""
                           class="regular-text"
                           placeholder="<?php esc_attr_e( 'Enter keywords to search...', 'hocwp-theme' ); ?>">
                </td>
            </tr>
            </tbody>
        </table>
		<?php
		submit_button( __( 'Delete', 'hocwp-theme' ), 'primary large', 'submit_form', false );
		echo '&nbsp;';
		submit_button( __( 'Reset', 'hocwp-theme' ), 'default large', 'reset_form', false );
		?>
    </form>
    <script>
        const form = document.getElementById("delete-posts-form"),
            noDelete = document.getElementById("no_delete"),
            yesDelete = document.getElementById("yes_delete"),
            resetForm = document.getElementById("reset_form"),
            submitForm = document.getElementById("submit_form"),
            confirmDelete = document.getElementById("confirmDelete");

        if (noDelete) {
            noDelete.addEventListener("click", function () {
                resetForm.click();
            });

            yesDelete.addEventListener("click", function () {
                confirmDelete.setAttribute("value", "1");
                submitForm.click();
            });
        }
    </script>
</div>