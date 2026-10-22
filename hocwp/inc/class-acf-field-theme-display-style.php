<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Theme_ACF_Field_Display_Style extends acf_field {
    public $label = 'Theme Display Style';

    function __construct() {
        $this->name = 'acf_theme_display_style';
        // This custom field will be in Layout category
        $this->category = 'layout';

        add_filter( 'acf/update_field', function ( $field ) {
            if ( 'acf_theme_display_style' !== ( $field['type'] ?? '' ) ) {
                return $field;
            }

            $field['label'] = __( 'Theme Display Style', 'hocwp-theme' );
            $field['name']  = 'theme_display_style';

            return $field;
        } );

        parent::__construct();
    }

    function load_value( $value, $post_id, $field ) {
        return $value;
    }

    function update_value( $value, $field, $post_id ) {
        return $value;
    }

    public function format_value( $value, $field ) {
        if ( is_admin() ) {
            return $value;
        }

        if ( ! $value ) {
            return false;
        }

        return $value;
    }

    public function format_value_for_api( $value, $field ) {
        if ( is_admin() ) {
            return $value;
        }

        return $this->format_value( $value, $field );
    }

    function load_field( $field ) {
        // Fixed field settings.
        $field['label'] = __( 'Theme Display Style', 'hocwp-theme' );
        $field['name']  = 'theme_display_style';

        return $field;
    }

    public function create_field( $field ) {
        $this->render_field( $field );
    }

    public function render_field( $field ) {
        $name  = $field['name'] ?? '';
        $id    = $field['id'] ?? '';
        $value = $field['value'] ?? '';

        $choices = [
                'default' => __( 'Default', 'hocwp-theme' )
        ];

        $choices = apply_filters( 'ht/acf/field_theme_display_style_choices', $choices );
        ?>
        <select id="<?php echo esc_attr( $id ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                data-ui="0"
                data-ajax="0"
                data-multiple="0"
                data-placeholder="Select"
                data-allow_null="0">
            <?php foreach ( $choices as $choice_value => $choice_label ) : ?>
                <option value="<?php echo esc_attr( $choice_value ); ?>" <?php selected( $value, $choice_value ); ?>>
                    <?php echo esc_html( $choice_label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    function create_options( $field ) {

    }

    function input_admin_enqueue_scripts() {

    }

    function input_admin_head() {

    }

    function field_group_admin_enqueue_scripts() {

    }

    function field_group_admin_head() {

    }
}