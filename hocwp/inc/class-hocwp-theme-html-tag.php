<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_HTML_Tag {
	/**
	 * List of self close tags.
	 *
	 * @var string[]
	 */
	private $self_closers = array( 'input', 'img', 'hr', 'br', 'meta', 'link', 'path' );

	/**
	 * List of HTML self closer tags must end with backslash.
	 *
	 * @var string[]
	 */
	private $must_slash_closers = array( 'path' );


	/**
	 * The HTML tag name.
	 *
	 * @var string
	 */
	protected $name;


	/**
	 * The HTML attributes array.
	 *
	 * @var array
	 */
	protected $attributes;


	/**
	 * The HTML text.
	 *
	 * @var string
	 */
	protected $text;

	/**
	 * The break line status.
	 *
	 * @var bool|string
	 */
	protected $break_line = true;


	/**
	 * The HTML close status.
	 *
	 * @var bool
	 */
	protected $close = true;


	/**
	 * The only text status.
	 *
	 * @var bool
	 */
	protected $only_text = false;

	/**
	 * The HTML wrap tag outer.
	 *
	 * @var string|HOCWP_Theme_HTML_Tag
	 */
	protected $wrap_tag = '';

	/**
	 * The attributes array for wrap tag.
	 *
	 * @var array
	 */
	protected $wrap_attributes = array();

	/**
	 * The HTML parent tag outer.
	 *
	 * @var HOCWP_Theme_HTML_Tag
	 */
	protected $parent;

	/**
	 * Set HTML tag name.
	 *
	 * @param string $name The name of HTML tag.
	 *
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = strtolower( $name );
	}

	/**
	 * Get the HTML tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Check if an attribute is existing.
	 *
	 * @param string $attribute_name The attribute name need to be checked.
	 *
	 * @return bool
	 */
	public function attribute_exists( $attribute_name ) {
		if ( ! is_string( $attribute_name ) ) {
			return false;
		}

		if ( ! is_array( $this->attributes ) ) {
			$this->attributes = array();
		}

		return array_key_exists( $attribute_name, $this->attributes );
	}

	/**
	 * Add attributes array to HTML object.
	 *
	 * @param array $attributes The attributes array list.
	 *
	 * @return void
	 */
	public function add_attributes( $attributes ) {
		if ( is_array( $attributes ) ) {
			if ( is_array( $this->attributes ) ) {
				$this->attributes = wp_parse_args( $attributes, $this->attributes );
			} else {
				$this->attributes = $attributes;
			}
		}
	}

	/**
	 * Check and validate HTML classes or add HTML classes.
	 *
	 * @param mixed $classes The classes need to be validated.
	 * @param mixed $add The classes need to be added.
	 *
	 * @return array|string
	 */
	private function sanitize_classes_array( $classes, $add = '' ) {
		if ( is_null( $classes ) ) {
			$classes = array();
		} elseif ( ! is_array( $classes ) && ! empty( $classes ) ) {
			$classes = explode( ' ', $classes );
		}

		if ( ! is_array( $classes ) ) {
			$classes = array();
		}

		if ( ! empty( $add ) && ! is_bool( $add ) ) {
			$add     = $this->sanitize_classes_array( $add, true );
			$classes = array_merge( $classes, $add );
		}

		$classes = array_unique( $classes );
		$classes = array_filter( $classes );

		if ( $add ) {
			return $classes;
		}

		return implode( ' ', $classes );
	}

	/**
	 * Check and validate HTML class.
	 *
	 * @param $class
	 *
	 * @return string
	 */
	public function sanitize_html_class( $class ) {
		if ( empty( $class ) ) {
			return '';
		}

		$classes = $this->get_attribute( 'class' );
		$classes = $this->sanitize_classes_array( $classes, $class );

		if ( is_array( $classes ) ) {
			$classes = implode( ' ', $classes );
		}

		return $classes;
	}

	/**
	 * Add HTML class attribute.
	 *
	 * @param mixed $class The classes need to be added.
	 *
	 * @return void
	 */
	public function add_class( $class ) {
		$this->set_attribute( 'class', $this->sanitize_html_class( $class ) );
	}

	/**
	 * The alias of {@see add_attribute}.
	 *
	 * Performs the same operation as {@see add_attribute}, but uses a different name.
	 *
	 * @param string|array $attribute_name The HTML attribute name or array list attributes.
	 * @param string|null $attribute_value The attribute value.
	 *
	 * @return void
	 */
	public function set_attribute( $attribute_name, $attribute_value ) {
		$this->add_attribute( $attribute_name, $attribute_value );
	}

	/**
	 * Add attribute for HTML object.
	 *
	 * @param string|array $attribute_name The HTML attribute name or array list attributes.
	 * @param string|null $value The attribute value.
	 *
	 * @return void
	 */
	public function add_attribute( $attribute_name, $value = null ) {
		if ( ! is_array( $this->attributes ) ) {
			$this->attributes = array();
		}

		// Used for same attribute name and value
		if ( is_null( $value ) && is_string( $attribute_name ) ) {
			$this->attributes[ $attribute_name ] = $attribute_name;

			return;
		}

		if ( null === $value || is_array( $attribute_name ) ) {
			if ( is_array( $attribute_name ) ) {
				$atts = $attribute_name;
			} else {
				$atts = ht()->attribute_to_array( $attribute_name );
			}

			foreach ( $atts as $key => $value ) {
				$this->add_attribute( $key, $value );
			}
		} else {
			if ( is_bool( $value ) ) {
				$value = ht()->bool_to_string( $value );
			}

			if ( 'class' == $attribute_name ) {
				$value = $this->sanitize_html_class( $value );
			}

			$this->attributes[ $attribute_name ] = $value;
		}
	}

	/**
	 * Get attribute value.
	 *
	 * @param string $attribute_name The attribute name.
	 *
	 * @return mixed|null|string
	 */
	public function get_attribute( $attribute_name ) {
		if ( $this->attribute_exists( $attribute_name ) ) {
			return $this->attributes[ $attribute_name ];
		}

		return null;
	}

	/**
	 * Remove an attribute.
	 *
	 * @param string $attribute_name The attribute name.
	 *
	 * @return void
	 */
	public function remove_attribute( $attribute_name ) {
		if ( $this->attribute_exists( $attribute_name ) ) {
			unset( $this->attributes[ $attribute_name ] );
		}
	}

	/**
	 * Set tag attributes aray.
	 *
	 * @param array $attributes The attribute array.
	 *
	 * @return void
	 */
	public function set_attributes( $attributes ) {
		if ( is_array( $attributes ) ) {
			$this->attributes = $attributes;
		}
	}

	/**
	 * Get attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Remove all attributes.
	 *
	 * @return void
	 */
	public function remove_attributes() {
		$this->attributes = array();
	}

	/**
	 * Set tag HTML text.
	 *
	 * @param string|HOCWP_Theme_HTML_Tag $text The text of HTML tag or a HOCWP_Theme_HTML_Tag object.
	 *
	 * @return void
	 */
	public function set_text( $text ) {
		if ( $text instanceof HOCWP_Theme_HTML_Tag ) {
			$text = $text->build();
		}

		if ( 'input' == $this->get_name() ) {
			$this->add_attribute( 'value', $text );
		}

		$this->text = $text;
	}

	/**
	 * Get the HTML text.
	 *
	 * @return string
	 */
	public function get_text() {
		return $this->text;
	}

	/**
	 * Set HTML break line.
	 *
	 * @param string|bool $break_line The break line HTML string or boolean status.
	 *
	 * @return void
	 */
	public function set_break_line( $break_line = '' ) {
		if ( empty( $break_line ) ) {
			$break_line = PHP_EOL;
		}

		$this->break_line = $break_line;
	}

	/**
	 * Get HTML break line status or string.
	 *
	 * @return bool|string
	 */
	public function get_break_line() {
		return $this->break_line;
	}

	/**
	 * Set close HTML tag status.
	 *
	 * @param bool $close The close tag status.
	 *
	 * @return void
	 */
	public function set_close( $close ) {
		$this->close = $close;
	}

	/**
	 * Get close HTML tag status.
	 *
	 * @return bool
	 */
	public function get_close() {
		return $this->close;
	}

	/**
	 * Set the only text value.
	 *
	 * @param bool $only_text
	 *
	 * @return void
	 */
	public function set_only_text( $only_text ) {
		$this->only_text = $only_text;
	}

	/**
	 * Get the only text value.
	 *
	 * @return bool
	 */
	public function get_only_text() {
		return $this->only_text;
	}

	/**
	 * Check HTML tag name is self closer tag.
	 *
	 * @param string $tag_name
	 *
	 * @return bool
	 */
	private function is_self_closer_tag( $tag_name = '' ) {
		if ( empty( $tag_name ) ) {
			$tag_name = $this->get_name();
		}

		return ht()->in_array( $tag_name, $this->self_closers );
	}

	/**
	 * Set attributes for wrap tag.
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public function set_wrap_attributes( $atts ) {
		$this->wrap_attributes = $atts;
	}

	/**
	 * Set the wrap HTML tag name.
	 *
	 * @param string|HOCWP_Theme_HTML_Tag $tag_name The HTML tag name or HOCWP_Theme_HTML_Tag object.
	 *
	 * @return void
	 */
	public function set_wrap_tag( $tag_name ) {
		if ( ! $this->is_self_closer_tag( $tag_name ) ) {
			$this->wrap_tag = $tag_name;
		}
	}

	/**
	 * Get the wrap HTML tag name or object.
	 *
	 * @return string|HOCWP_Theme_HTML_Tag
	 */
	public function get_wrap_tag() {
		return $this->wrap_tag;
	}

	/**
	 * Set the HTML tag parent.
	 *
	 * @param HOCWP_Theme_HTML_Tag $parent
	 *
	 * @return void
	 */
	public function set_parent( HOCWP_Theme_HTML_Tag $parent ) {
		$this->parent = $parent;
	}

	public function __construct( $name ) {
		$this->set_name( $name );

		if ( 'img' == strtolower( $name ) ) {
			// Add default empty alt attribute for image
			$this->add_attribute( 'alt', '' );
		}
	}

	/**
	 * Convert attributes array to string.
	 *
	 * @return string
	 */
	private function build_attributes() {
		$attributes_string = '';

		if ( ! empty( $this->get_attributes() ) ) {
			foreach ( $this->get_attributes() as $key => $value ) {
				if ( in_array( $key, ht()->same_value_atts ) ) {
					$value = $key;
				}

				$attributes_string .= sprintf( ' %1$s="%2$s"', $key, trim( esc_attr( maybe_serialize( $value ) ) ) );
			}
		}

		return $attributes_string;
	}

	/**
	 * Build the HTML tag object to string.
	 *
	 * @return string
	 */
	public function build() {
		// Return only text string.
		if ( $this->get_only_text() ) {
			return $this->get_text();
		}

		$tag_name = $this->get_name();

		if ( empty( $tag_name ) ) {
			return '';
		}

		$this->attributes = apply_filters( 'hocwp_theme_html_tag_attributes', $this->attributes, $this );

		// Add default aria-label for button to fix Buttons do not have an accessible name
		if ( 'button' == $tag_name && ! isset( $this->attributes['aria-label'] ) && ! empty( $this->get_text() ) ) {
			$this->attributes['aria-label'] = esc_attr( $this->get_text() );
		}

		$attribute = $this->build_attributes();

		$is_self_closer   = $this->is_self_closer_tag( $tag_name );
		$must_slash_close = $is_self_closer && in_array( $tag_name, $this->must_slash_closers );

		// Check if self closer tag need end with backslash.
		$result = sprintf( '<%1$s%2$s%3$s>', $tag_name, $attribute, $must_slash_close ? ' /' : '' );

		if ( ! $is_self_closer || 'input' == $tag_name ) {
			$result .= $this->build_text();
		}

		if ( $this->get_close() && ! $is_self_closer ) {
			$result .= sprintf( '</%s>', $tag_name );
		}

		$wrap_tag = $this->get_wrap_tag();

		if ( ! empty( $wrap_tag ) ) {
			if ( ! ( $wrap_tag instanceof HOCWP_Theme_HTML_Tag ) ) {
				$wrap_tag = new HOCWP_Theme_HTML_Tag( $wrap_tag );
			}

			$wrap_tag->set_attributes( $this->wrap_attributes );
			$wrap_tag->set_text( $result );
			$result = $wrap_tag->build();
		}

		return $result;
	}

	/**
	 * Build HTML text string.
	 *
	 * @return string
	 */
	private function build_text() {
		$text = $this->get_text();

		if ( is_array( $text ) || is_object( $text ) ) {
			$text = maybe_serialize( $text );
		}

		return $text;
	}

	/**
	 * Output HTML tag string.
	 *
	 * @return void
	 */
	public function output() {
		if ( $this->parent instanceof HOCWP_Theme_HTML_Tag ) {
			$this->parent->set_text( $this );
			$html = $this->parent->build();
		} else {
			$html = $this->build();
		}

		if ( $this->get_break_line() ) {
			$html .= PHP_EOL;
		}

		echo $html;
	}
}