<?php



if ( ! class_exists( 'WP_Bootstrap_Navwalker' ) ) :
		class WP_Bootstrap_Navwalker extends Walker_Nav_Menu {

		private $has_schema = false;

		
		public function __construct() {
			if ( ! has_filter( 'wp_nav_menu_args', array( $this, 'add_schema_to_navbar_ul' ) ) ) {
				add_filter( 'wp_nav_menu_args', array( $this, 'add_schema_to_navbar_ul' ) );
			}
		}

		
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}
			$indent = str_repeat( $t, $depth );
			
			$classes = array( 'dropdown-menu' );
			
			$class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			
			$labelledby = '';
			
			preg_match_all( '/(<a.*?id=\"|\')(.*?)\"|\'.*?>/im', $output, $matches );
			
			if ( end( $matches[2] ) ) {
				
				$labelledby = 'aria-labelledby="' . esc_attr( end( $matches[2] ) ) . '"';
			}
			$output .= "{$n}{$indent}<ul$class_names $labelledby>{$n}";
		}

		
		 		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}
			$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

			if ( false !== strpos( $args->items_wrap, 'itemscope' ) && false === $this->has_schema ) {
				$this->has_schema  = true;
				$args->link_before = '<span itemprop="name">' . $args->link_before;
				$args->link_after .= '</span>';
			}

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;

			
			$split_on_spaces = function ( $class ) {
				return preg_split( '/\s+/', $class );
			};
			$classes         = $this->flatten( array_map( $split_on_spaces, $classes ) );

			
			$linkmod_classes = array();
			$icon_classes    = array();

			
			$classes = $this->separate_linkmods_and_icons_from_classes( $classes, $linkmod_classes, $icon_classes, $depth );

			
			$icon_class_string = join( ' ', $icon_classes );

			
			$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

			
			if ( $this->has_children ) {
				$classes[] = 'dropdown';
			}
			if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
				$classes[] = 'active';
			}

			
			$classes[] = 'menu-item-' . $item->ID;
			$classes[] = 'nav-item';

			
			$classes = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth );

			
			$class_names = join( ' ', $classes );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output .= $indent . '<li ' . $id . $class_names . '>';

			
			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			if ( '_blank' === $item->target && empty( $item->xfn ) ) {
				$atts['rel'] = 'noopener noreferrer';
			} else {
				$atts['rel'] = ! empty( $item->xfn ) ? $item->xfn : '';
			}

			
			if ( $this->has_children && 0 === $depth ) {
				$atts['href']          = '#';
				$atts['data-toggle']   = 'dropdown';
				$atts['aria-expanded'] = 'false';
				$atts['class']         = 'dropdown-toggle nav-link';
				$atts['id']            = 'menu-item-dropdown-' . $item->ID;
			} else {
				if ( true === $this->has_schema ) {
					$atts['itemprop'] = 'url';
				}

				$atts['href'] = ! empty( $item->url ) ? $item->url : '#';
				
				if ( $depth > 0 ) {
					$atts['class'] = 'dropdown-item';
				} else {
					$atts['class'] = 'nav-link';
				}
			}

			$atts['aria-current'] = $item->current ? 'page' : '';

			
			$atts = $this->update_atts_for_linkmod_type( $atts, $linkmod_classes );

			
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			
			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			
			$linkmod_type = $this->get_linkmod_type( $linkmod_classes );

			
			$item_output = isset( $args->before ) ? $args->before : '';

			
			if ( '' !== $linkmod_type ) {
				
				$item_output .= $this->linkmod_element_open( $linkmod_type, $attributes );
			} else {
				
				$item_output .= '<a' . $attributes . '>';
			}

			
			$icon_html = '';
			if ( ! empty( $icon_class_string ) ) {
				
				$icon_html = '<i class="' . esc_attr( $icon_class_string ) . '" aria-hidden="true"></i> ';
			}

			
			$title = apply_filters( 'the_title', $item->title, $item->ID );

			
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			
			if ( in_array( 'sr-only', $linkmod_classes, true ) ) {
				$title         = $this->wrap_for_screen_reader( $title );
				$keys_to_unset = array_keys( $linkmod_classes, 'sr-only', true );
				foreach ( $keys_to_unset as $k ) {
					unset( $linkmod_classes[ $k ] );
				}
			}

			
			$item_output .= isset( $args->link_before ) ? $args->link_before . $icon_html . $title . $args->link_after : '';

			
			if ( '' !== $linkmod_type ) {
				
				$item_output .= $this->linkmod_element_close( $linkmod_type );
			} else {
				
				$item_output .= '</a>';
			}

			$item_output .= isset( $args->after ) ? $args->after : '';

			
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		
		public static function fallback( $args ) {
			if ( ! current_user_can( 'edit_theme_options' ) ) {
				return;
			}

			
			$fallback_output = '';

			
			$show_container = false;
			if ( $args['container'] ) {
				/**
				 * Filters the list of HTML tags that are valid for use as menu containers.
				 *
				 * @since WP 3.0.0
				 *
				 * @param array $tags The acceptable HTML tags for use as menu containers.
				 *                    Default is array containing 'div' and 'nav'.
				 */
				$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
				if ( is_string( $args['container'] ) && in_array( $args['container'], $allowed_tags, true ) ) {
					$show_container   = true;
					$class            = $args['container_class'] ? ' class="menu-fallback-container ' . esc_attr( $args['container_class'] ) . '"' : ' class="menu-fallback-container"';
					$id               = $args['container_id'] ? ' id="' . esc_attr( $args['container_id'] ) . '"' : '';
					$fallback_output .= '<' . $args['container'] . $id . $class . '>';
				}
			}

			// The fallback menu.
			$class            = $args['menu_class'] ? ' class="menu-fallback-menu ' . esc_attr( $args['menu_class'] ) . '"' : ' class="menu-fallback-menu"';
			$id               = $args['menu_id'] ? ' id="' . esc_attr( $args['menu_id'] ) . '"' : '';
			$fallback_output .= '<ul' . $id . $class . '>';
			$fallback_output .= '<li class="nav-item"><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" class="nav-link" title="' . esc_attr__( 'Add a menu', 'wp-bootstrap-navwalker' ) . '">' . esc_html__( 'Add a menu', 'wp-bootstrap-navwalker' ) . '</a></li>';
			$fallback_output .= '</ul>';

			// Menu container closing tag.
			if ( $show_container ) {
				$fallback_output .= '</' . $args['container'] . '>';
			}

			// if $args has 'echo' key and it's true echo, otherwise return.
			if ( array_key_exists( 'echo', $args ) && $args['echo'] ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $fallback_output;
			} else {
				return $fallback_output;
			}
		}

		/**
		 * Filter to ensure the items_Wrap argument contains microdata.
		 *
		 * @since 4.2.0
		 *
		 * @param  array $args The nav instance arguments.
		 * @return array $args The altered nav instance arguments.
		 */
		public function add_schema_to_navbar_ul( $args ) {
			if ( isset( $args['items_wrap'] ) ) {
				$wrap = $args['items_wrap'];
				if ( strpos( $wrap, 'SiteNavigationElement' ) === false ) {
					$args['items_wrap'] = preg_replace( '/(>).*>?\%3\$s/', ' itemscope itemtype="http://www.schema.org/SiteNavigationElement"$0', $wrap );
				}
			}
			return $args;
		}

		/**
		 * Find any custom linkmod or icon classes and store in their holder
		 * arrays then remove them from the main classes array.
		 *
		 * Supported linkmods: .disabled, .dropdown-header, .dropdown-divider, .sr-only
		 * Supported iconsets: Font Awesome 4/5, Glypicons
		 *
		 * NOTE: This accepts the linkmod and icon arrays by reference.
		 *
		 * @since 4.0.0
		 *
		 * @param array   $classes         an array of classes currently assigned to the item.
		 * @param array   $linkmod_classes an array to hold linkmod classes.
		 * @param array   $icon_classes    an array to hold icon classes.
		 * @param integer $depth           an integer holding current depth level.
		 *
		 * @return array  $classes         a maybe modified array of classnames.
		 */
		private function separate_linkmods_and_icons_from_classes( $classes, &$linkmod_classes, &$icon_classes, $depth ) {
			// Loop through $classes array to find linkmod or icon classes.
			foreach ( $classes as $key => $class ) {
				/*
				 * If any special classes are found, store the class in it's
				 * holder array and and unset the item from $classes.
				 */
				if ( preg_match( '/^disabled|^sr-only/i', $class ) ) {
					// Test for .disabled or .sr-only classes.
					$linkmod_classes[] = $class;
					unset( $classes[ $key ] );
				} elseif ( preg_match( '/^dropdown-header|^dropdown-divider|^dropdown-item-text/i', $class ) && $depth > 0 ) {
					/*
					 * Test for .dropdown-header or .dropdown-divider and a
					 * depth greater than 0 - IE inside a dropdown.
					 */
					$linkmod_classes[] = $class;
					unset( $classes[ $key ] );
				} elseif ( preg_match( '/^fa-(\S*)?|^fa(s|r|l|b)?(\s?)?$/i', $class ) ) {
					// Font Awesome.
					$icon_classes[] = $class;
					unset( $classes[ $key ] );
				} elseif ( preg_match( '/^glyphicon-(\S*)?|^glyphicon(\s?)$/i', $class ) ) {
					// Glyphicons.
					$icon_classes[] = $class;
					unset( $classes[ $key ] );
				}
			}

			return $classes;
		}

		/**
		 * Return a string containing a linkmod type and update $atts array
		 * accordingly depending on the decided.
		 *
		 * @since 4.0.0
		 *
		 * @param array $linkmod_classes array of any link modifier classes.
		 *
		 * @return string                empty for default, a linkmod type string otherwise.
		 */
		private function get_linkmod_type( $linkmod_classes = array() ) {
			$linkmod_type = '';
			// Loop through array of linkmod classes to handle their $atts.
			if ( ! empty( $linkmod_classes ) ) {
				foreach ( $linkmod_classes as $link_class ) {
					if ( ! empty( $link_class ) ) {

						// Check for special class types and set a flag for them.
						if ( 'dropdown-header' === $link_class ) {
							$linkmod_type = 'dropdown-header';
						} elseif ( 'dropdown-divider' === $link_class ) {
							$linkmod_type = 'dropdown-divider';
						} elseif ( 'dropdown-item-text' === $link_class ) {
							$linkmod_type = 'dropdown-item-text';
						}
					}
				}
			}
			return $linkmod_type;
		}

		/**
		 * Update the attributes of a nav item depending on the limkmod classes.
		 *
		 * @since 4.0.0
		 *
		 * @param array $atts            array of atts for the current link in nav item.
		 * @param array $linkmod_classes an array of classes that modify link or nav item behaviors or displays.
		 *
		 * @return array                 maybe updated array of attributes for item.
		 */
		private function update_atts_for_linkmod_type( $atts = array(), $linkmod_classes = array() ) {
			if ( ! empty( $linkmod_classes ) ) {
				foreach ( $linkmod_classes as $link_class ) {
					if ( ! empty( $link_class ) ) {
						/*
						 * Update $atts with a space and the extra classname
						 * so long as it's not a sr-only class.
						 */
						if ( 'sr-only' !== $link_class ) {
							$atts['class'] .= ' ' . esc_attr( $link_class );
						}
						// Check for special class types we need additional handling for.
						if ( 'disabled' === $link_class ) {
							// Convert link to '#' and unset open targets.
							$atts['href'] = '#';
							unset( $atts['target'] );
						} elseif ( 'dropdown-header' === $link_class || 'dropdown-divider' === $link_class || 'dropdown-item-text' === $link_class ) {
							// Store a type flag and unset href and target.
							unset( $atts['href'] );
							unset( $atts['target'] );
						}
					}
				}
			}
			return $atts;
		}

		/**
		 * Wraps the passed text in a screen reader only class.
		 *
		 * @since 4.0.0
		 *
		 * @param string $text the string of text to be wrapped in a screen reader class.
		 * @return string      the string wrapped in a span with the class.
		 */
		private function wrap_for_screen_reader( $text = '' ) {
			if ( $text ) {
				$text = '<span class="sr-only">' . $text . '</span>';
			}
			return $text;
		}

		/**
		 * Returns the correct opening element and attributes for a linkmod.
		 *
		 * @since 4.0.0
		 *
		 * @param string $linkmod_type a sting containing a linkmod type flag.
		 * @param string $attributes   a string of attributes to add to the element.
		 *
		 * @return string              a string with the openign tag for the element with attribibutes added.
		 */
		private function linkmod_element_open( $linkmod_type, $attributes = '' ) {
			$output = '';
			if ( 'dropdown-item-text' === $linkmod_type ) {
				$output .= '<span class="dropdown-item-text"' . $attributes . '>';
			} elseif ( 'dropdown-header' === $linkmod_type ) {
				/*
				 * For a header use a span with the .h6 class instead of a real
				 * header tag so that it doesn't confuse screen readers.
				 */
				$output .= '<span class="dropdown-header h6"' . $attributes . '>';
			} elseif ( 'dropdown-divider' === $linkmod_type ) {
				// This is a divider.
				$output .= '<div class="dropdown-divider"' . $attributes . '>';
			}
			return $output;
		}

		/**
		 * Return the correct closing tag for the linkmod element.
		 *
		 * @since 4.0.0
		 *
		 * @param string $linkmod_type a string containing a special linkmod type.
		 *
		 * @return string              a string with the closing tag for this linkmod type.
		 */
		private function linkmod_element_close( $linkmod_type ) {
			$output = '';
			if ( 'dropdown-header' === $linkmod_type || 'dropdown-item-text' === $linkmod_type ) {
				/*
				 * For a header use a span with the .h6 class instead of a real
				 * header tag so that it doesn't confuse screen readers.
				 */
				$output .= '</span>';
			} elseif ( 'dropdown-divider' === $linkmod_type ) {
				// This is a divider.
				$output .= '</div>';
			}
			return $output;
		}

		/**
		 * Flattens a multidimensional array to a simple array.
		 *
		 * @param array $array a multidimensional array.
		 *
		 * @return array a simple array
		 */
		public function flatten( $array ) {
			$result = array();
			foreach ( $array as $element ) {
				if ( is_array( $element ) ) {
					array_push( $result, ...$this->flatten( $element ) );
				} else {
					$result[] = $element;
				}
			}
			return $result;
		}

	}

endif;