<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_ids = '';
?>
<div class="wrap delete-posts-php">
    <h1><?php _e( 'Delete Posts', 'hocwp-theme' ); ?></h1>
    <p class="description"><?php _e( 'With this tool, you can search and delete any posts completely automatically.', 'hocwp-theme' ); ?></p>
    <hr class="wp-header-end" style="clear: both;">
    <h2 class="screen-reader-text"><?php _e( 'Search and delete posts', 'hocwp-theme' ); ?></h2>
    <form id="delete-posts-form" method="post" class="delete-posts-form" action="">
		<?php
		if ( isset( $_POST['submit_form'] ) ) {
			$post_ids = isset( $_POST['post_ids'] ) ? $_POST['post_ids'] : '';

			if ( ! empty( $post_ids ) ) {
				$post_ids = explode( ',', $post_ids );
				$post_ids = array_map( 'trim', $post_ids );

				$confirm_delete = isset( $_POST['confirm_delete'] ) ? $_POST['confirm_delete'] : '';

				if ( empty( $confirm_delete ) ) {
					$msg = '';

					foreach ( $post_ids as $id ) {
						$pt = get_post_type( $id );

						if ( empty( $pt ) ) {
							$pt = __( 'unknown post type', 'hocwp-theme' );
						}

						$msg .= sprintf( '<strong>%s (%s - %s)</strong>, ', get_the_title( $id ), $id, $pt );
					}

					$msg = rtrim( $msg, ', ' );

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

				if ( 1 == $confirm_delete ) {
					$count = 0;

					foreach ( $post_ids as $id ) {
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
		?>
        <input id="confirmDelete" name="confirm_delete" value="" type="hidden">
        <table class="form-table" role="presentation">
            <tbody>
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