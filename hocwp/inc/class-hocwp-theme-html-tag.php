<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class HOCWP_Theme_HTML_Tag {
	private $self_closers = array( 'input', 'img', 'hr', 'br', 'meta', 'link', 'path' );
	private $must_slash_closers = array( 'path' );
	protected $name;
	protected $attributes;
	protected $text;
	protected $break_line = true;
	protected $close = true;
	protected $only_text = false;
	protected $wrap_tag = '';
	protected $parent;

	public function set_name( $name ) {
		$this->name = strtolower( $name );
	}

	public function get_name() {
		return $this->name;
	}

	public function attribute_exists( $attribute_name ) {
		return array_key_exists( $attribute_name, $this->attributes );
	}

	public function add_attribute( $attribute_name, $value = null ) {
		if ( ! is_array( $this->attributes ) ) {
			$this->attributes = array();
		}

		if ( null === $value || is_array( $attribute_name ) ) {
			if ( is_array( $attribute_name ) ) {
				$atts = $attribute_name;
			} else {
				$atts = HOCWP_Theme::attribute_to_array( $attribute_name );
			}

			foreach ( $atts as $key => $value ) {
				$this->add_attribute( $key, $value );
			}
		} else {
			if ( is_bool( $value ) ) {
				$value = HT()->bool_to_string( $value );
			}

			$this->attributes[ $attribute_name ] = $value;
		}
	}

	public function get_attribute( $attribute_name ) {
		if ( $this->attribute_exists( $attribute_name ) ) {
			return $this->attributes[ $attribute_name ];
		}

		return null;
	}

	public function remove_attribute( $attribute_name ) {
		if ( $this->attribute_exists( $attribute_name ) ) {
			unset( $this->attributes[ $attribute_name ] );
		}
	}

	public function set_attributes( $attributes ) {
		if ( is_array( $attributes ) ) {
			$this->attributes = $attributes;
		}
	}

	public function get_attributes() {
		return $this->attributes;
	}

	public function remove_attributes() {
		$this->attributes = array();
	}

	public function set_text( $text ) {
		if ( $text instanceof HOCWP_Theme_HTML_Tag ) {
			$text = $text->build();
		}
		if ( 'input' == $this->get_name() ) {
			$this->add_attribute( 'value', $text );
		}
		$this->text = $text;
	}

	public function get_text() {
		return $this->text;
	}

	public function set_break_line( $break_line ) {
		$this->break_line = $break_line;
	}

	public function get_break_line() {
		return $this->break_line;
	}

	public function set_close( $close ) {
		$this->close = $close;
	}

	public function get_close() {
		return $this->close;
	}

	public function set_only_text( $only_text ) {
		$this->only_text = $only_text;
	}

	public function get_only_text() {
		return $this->only_text;
	}

	public function set_wrap_tag( $tag_name ) {
		if ( ! HOCWP_Theme::in_array( $tag_name, $this->self_closers ) ) {
			$this->wrap_tag = $tag_name;
		}
	}

	public function get_wrap_tag() {
		return $this->wrap_tag;
	}

	public function set_parent( HOCWP_Theme_HTML_Tag $parent ) {
		$this->parent = $parent;
	}

	public function __construct( $name ) {
		$this->set_name( $name );
		if ( 'img' == strtolower( $name ) ) {
			$this->add_attribute( 'alt', '' );
		}
	}

	public function build() {
		$wrap_tag = $this->get_wrap_tag();
		if ( $this->get_only_text() ) {
			return $this->get_text();
		}
		$tag_name = $this->get_name();
		$result   = '<' . $tag_name;
		if ( ! empty( $wrap_tag ) ) {
			$result = '<' . $wrap_tag . '>' . $result;
		}
		$this->attributes = apply_filters( 'hocwp_theme_html_tag_attributes', $this->attributes, $this );
		foreach ( (array) $this->attributes as $key => $value ) {
			$result .= sprintf( ' %1$s="%2$s"', $key, trim( esc_attr( maybe_serialize( $value ) ) ) );
		}
		if ( in_array( $tag_name, $this->self_closers ) && in_array( $tag_name, $this->must_slash_closers ) ) {
			$result .= '/';
		}
		$result .= '>';
		if ( ! HOCWP_Theme::in_array( $tag_name, $this->self_closers ) || 'input' == $tag_name ) {
			$result .= $this->text;
		}
		if ( $this->get_close() && ! HOCWP_Theme::in_array( $tag_name, $this->self_closers ) ) {
			$result .= sprintf( '</%s>', $tag_name );
		}
		if ( ! empty( $wrap_tag ) ) {
			$result .= '</' . $wrap_tag . '>';
		}

		return $result;
	}

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