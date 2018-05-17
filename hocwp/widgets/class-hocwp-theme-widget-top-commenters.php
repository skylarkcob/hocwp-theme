<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Widget_Top_Commenters extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults  = array(
			'number'        => 5,
			'date_interval' => 'all'
		);
		$this->defaults  = apply_filters( 'hocwp_theme_widget_top_commenters_defaults', $this->defaults, $this );
		$widget_options  = array(
			'classname'   => 'hocwp-theme-widget-top-commenters hocwp-widget-top-commenters',
			'description' => _x( 'A list of top commenters.', 'widget hocwp term', 'hocwp-theme' )
		);
		$control_options = array(
			'width' => 400
		);
		parent::__construct( 'hocwp_widget_top_commenters', 'HocWP Top Commenters', $widget_options, $control_options );
		add_action( 'transition_comment_status', array( $this, 'transition_comment_status' ) );
		add_action( 'wp_insert_comment', array( $this, 'insert_comment' ) );
	}

	public function widget( $args, $instance ) {
		$instance = apply_filters( 'hocwp_theme_widget_top_commenters_instance', $instance, $args, $this );

		$number        = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];
		$date_interval = isset( $instance['date_interval'] ) ? $instance['date_interval'] : $this->defaults['date_interval'];

		$tr_name = 'hocwp_theme_top_commenters_' . $date_interval;

		if ( false === ( $commenters = get_transient( $tr_name ) ) ) {
			$commenters = HT_Query()->get_top_commenters( $number, $date_interval );

			if ( HT()->array_has_value( $commenters ) ) {
				set_transient( $tr_name, $commenters );
			}
		}

		if ( HT()->array_has_value( $commenters ) ) {
			do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
			?>
            <ul>
				<?php
				foreach ( $commenters as $commenter ) {
					$email = $commenter->comment_author_email;
					if ( ! is_email( $email ) ) {
						continue;
					}
					$name   = $commenter->comment_author;
					$avatar = get_avatar( $email, 48, '', $name );

					$avatar .= ' ' . $name;
					$avatar .= ' (' . $commenter->comments_count . ')';

					$url = esc_url( $commenter->comment_author_url );

					if ( 'http://' == $url || 'https://' == $url ) {
						$url = '';
					}

					if ( ! empty( $url ) ) {
						$avatar = sprintf( '<a href="%s" title="%s" rel="nofollow" target="_blank">%s</a>', $url, $name, $avatar );
					}
					?>
                    <li>
						<?php echo $avatar; ?>
                    </li>
					<?php
				}
				?>
            </ul>
			<?php
			do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
		}
	}

	public function form( $instance ) {
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : $this->defaults['number'];

		$date_intervals = HT_Util()->date_intervals();
		$date_interval  = isset( $instance['date_interval'] ) ? $instance['date_interval'] : $this->defaults['date_interval'];

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of top commenters to show:', 'hocwp-theme' ); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>"
                   name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1"
                   value="<?php echo $number; ?>" size="3"/>
        </p>
        <p>
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'date_interval' ),
				'text' => __( 'Date interval:', 'hocwp-theme' )
			);
			HT_HTML_Field()->label( $args );
			$args = array(
				'id'      => $this->get_field_id( 'date_interval' ),
				'name'    => $this->get_field_name( 'date_interval' ),
				'options' => $date_intervals,
				'class'   => 'widefat',
				'value'   => $date_interval
			);
			HT_HTML_Field()->select( $args );
			?>
        </p>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = sanitize_text_field( $new_instance['title'] );
		$instance['number']        = isset( $new_instance['number'] ) ? absint( $new_instance['number'] ) : $this->defaults['number'];
		$instance['date_interval'] = isset( $new_instance['date_interval'] ) ? $new_instance['date_interval'] : $this->defaults['date_interval'];

		return $instance;
	}

	private function remove_transient() {
		HT_Util()->delete_transient( 'hocwp_theme_top_commenters' );
	}

	public function transition_comment_status() {
		$this->remove_transient();
	}

	public function insert_comment() {
		$this->remove_transient();
	}
}