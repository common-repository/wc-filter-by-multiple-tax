<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Taxonomy Multi Level
 *
 * @package     WC Filter By Multiple Tax
 * @author      TVLA92
 * @category    Widgets
 * @version     1.0.7
 * @extends     WP_Widget
 */
class WC_Filter_Taxonomy_Multi_Level extends WC_FILTER_Widget {
	
	public function __construct() {

		$this->widget_cssclass    = 'widget-taxonomy-menu';
		$this->widget_id          = 'wc-filter-taxonomy-menu';
		$this->widget_name        = esc_html__( 'List Taxonomies Multiple Level', 'wc-filter' );
		$this->widget_description = esc_html__( 'Dipsplay a list of your taxonomy, filter products by multiple term of taxonomy ( Product Category, Product Brand, Product Feature).', 'wc-filter' );
		$this->settings           = array();
		
		parent::__construct();

	}

	/**
	 * Default widget args
	 *
	 * Sets defaults and merges them with current $instance settings
	 */
	function defaults( $instance = array() ){
		$instance = wp_parse_args( (array)$instance, array(
			'title'          => esc_html( 'Enter your title', 'wc-filter' ),
			'style'          => 'list',
			'show_count'	 => 'true',
			'use_ajax'		 => '',
			'search'		 => '',
			'hide_empty'     => 'true',
			'show_thumb'     => 'false',
			'terms_handling' => 'auto'
		) );
		return $instance;
	}

	/**
	 * Creates a taxonomy checklist based on wp_terms_checklist()
	 *
	 * Output buffering is used so that we can run a string replace after the checklist is created
	 */
	function wc_filter_taxonomy_checklist($name = '', $tax, $selected = array()) {
	
		$name = esc_attr( $name );

		$checkboxes = '';

		ob_start();
			
		$terms_args = array ( 'taxonomy' => $tax->name, 'selected_cats' => $selected, 'checked_ontop' => false );
		
		// Note: 'hide empty' is false, therefore terms with no posts will appear in the checklist
		wp_terms_checklist( 0, $terms_args );
		
		// Replace standard checklist "name" attr with the one we need, ie 'include_' . $tax->name[]
		$checkboxes .= str_replace( 'name="tax_input['.$tax->name.'][]"', 'name="'.$name.'[]"', ob_get_clean() );
				
		return $checkboxes;
	}

	function widget( $args, $instance ) {
		extract($args);
		$title      = apply_filters('widget_title', $instance['title']);
		$taxonomies = wc_filter_func_get_taxonomies( array('product') );
		$attributes = array_keys(wc_filter_func_get_all_product_attribute());

		// Hide taxonomy use ajax filter in single product, Product Cat will be display but not use ajax filter
		if( is_product() ){
			if( $instance['use_ajax'] == true && !isset( $instance['show_tax_product_cat']) ){
				return false;
			} else {
				$instance['use_ajax'] = false;
			}
		}

		// Enqueue main-widget(.js)
		wp_enqueue_script( 'main-widget-filter' );
		// Enqueue wc-filter(.css)
		wp_enqueue_style( 'wc-filter' );

		// Get transient taxonomy
		$all_tax = array_keys(wc_filter_func_get_product_list_taxonomy_name());
		foreach ($all_tax as $taxx) {
			${'wc_filter_relate_term_'.$taxx} = get_transient( 'wc_filter_relate_term_'.$taxx ) ? get_transient( 'wc_filter_relate_term_'.$taxx ) : array();
		}
		// Get term of Taxonomy Feature with term of page tax product cat
		if( is_tax() ):

			$tax_term_id = get_queried_object()->term_taxonomy_id;
			$tax_name    = get_queried_object()->taxonomy;

			if( $tax_name == 'product_cat' ):

				// Get term children of term_id page tax
				$cat_parent_term_id = wc_filter_func_get_tax_parent_id( 'product_cat', $tax_term_id);

				if( isset($instance['show_tax_product_cat']) && $instance['show_tax_product_cat'] == true ){
					// In archive page tax, show product category with list not use filter
					$instance['use_ajax'] = false;
					if( $instance['style'] == 'dropdown' ){
						$cat_child_term   = get_term_children( $cat_parent_term_id, 'product_cat' );
						$cat_child_term[] = $cat_parent_term_id;
					}

				}

			endif;

			switch ($tax_name) {
				case 'product_tag':

					if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true ){
						// In archive page tax, show product category with list not use filter
						$instance['use_ajax'] = false;
					}

					if( isset($instance['show_tax_brand']) && $instance['show_tax_brand'] == true  ){

						if( isset( $wc_filter_relate_term_product_tag[$tax_term_id] ) && isset( $wc_filter_relate_term_product_tag[$tax_term_id]['brand'] ) ){
							$brand_show = $wc_filter_relate_term_product_tag[$tax_term_id]['brand'];
							foreach ($brand_show as $value) {
								$brand_show_parent = (array)wc_filter_func_get_tax_parent_id( 'brand', $value );
								$brand_show        = array_merge($brand_show, $brand_show_parent);
							}
							// Remove duplicate values from an array
							$brand_show = array_unique($brand_show);
							if( empty($brand_show) )
								return false;
						} else {
							return false;
						}

					}

					if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

						if( isset( $wc_filter_relate_term_product_tag[$tax_term_id] ) && isset( $wc_filter_relate_term_product_tag[$tax_term_id]['feature'] ) ){
							$feature_show = $wc_filter_relate_term_product_tag[$tax_term_id]['feature'];
							foreach ($feature_show as $value) {
								$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
								$feature_show        = array_merge($feature_show, $feature_show_parent);
							}
							// Remove duplicate values from an array
							$feature_show = array_unique($feature_show);
							if( empty($feature_show) )
								return false;
						} else {
							return false;
						}

					}

					foreach ($attributes as $attr) {
						if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
							if( isset( $wc_filter_relate_term_product_tag[$tax_term_id] ) && isset( $wc_filter_relate_term_product_tag[$tax_term_id][$attr] ) ){
								${$attr.'_show'} = $wc_filter_relate_term_product_tag[$tax_term_id][$attr];
								if( empty(${$attr.'_show'}) )
									return false;
							} else {
								return false;
							}
						}
					}

					break;
					
				case 'brand':

					if( isset($instance['show_tax_brand']) && $instance['show_tax_brand'] == true ){
						// In archive page tax, show product category with list not use filter
						$instance['use_ajax'] = false;
					}

					if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

						if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id]['feature'] ) ){
							$feature_show = $wc_filter_relate_term_brand[$tax_term_id]['feature'];
							foreach ($feature_show as $value) {
								$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
								$feature_show        = array_merge($feature_show, $feature_show_parent);
							}
							// Remove duplicate values from an array
							$feature_show = array_unique($feature_show);
							if( empty($feature_show) )
								return false;
						} else {
							return false;
						}

					}

					if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

						if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id]['product_tag'] ) ){
							$product_tag_show = $wc_filter_relate_term_brand[$tax_term_id]['product_tag'];
							if( empty($product_tag_show) )
								return false;
						} else {
							return false;
						}

					}

					foreach ($attributes as $attr) {
						if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
							if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id][$attr] ) ){
								${$attr.'_show'} = $wc_filter_relate_term_brand[$tax_term_id][$attr];
								if( empty(${$attr.'_show'}) )
									return false;
							} else {
								return false;
							}
						}
					}

					break;

				case 'feature':

					if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true ){
						// In archive page tax, show product category with list not use filter
						$instance['use_ajax'] = false;
					}
					
					if( isset($instance['show_tax_brand']) && $instance['show_tax_brand'] == true  ){

						if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id]['brand'] ) ){

							$brand_show = $wc_filter_relate_term_feature[$tax_term_id]['brand'];
							foreach ($brand_show as $value) {
								$brand_show_parent = (array)wc_filter_func_get_tax_parent_id( 'brand', $value );
								$brand_show        = array_merge($brand_show, $brand_show_parent);
							}
							// Remove duplicate values from an array
							$brand_show = array_unique($brand_show);
							if( empty($brand_show) )
								return false;
						} else {
							return false;
						}

					}

					if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

						if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id]['product_tag'] ) ){
							$product_tag_show = $wc_filter_relate_term_feature[$tax_term_id]['product_tag'];
							if( empty($product_tag_show) )
								return false;
						} else {
							return false;
						}

					}

					foreach ($attributes as $attr) {
						if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
							if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id][$attr] ) ){
								${$attr.'_show'} = $wc_filter_relate_term_feature[$tax_term_id][$attr];
								if( empty(${$attr.'_show'}) )
									return false;
							} else {
								return false;
							}
						}
					}

					break;
				
				default:
					if( isset($instance['show_tax_brand']) && $instance['show_tax_brand'] == true  ){

						if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['brand'] ) ){
							$brand_show = $wc_filter_relate_term_product_cat[$tax_term_id]['brand'];
							foreach ($brand_show as $value) {
								$brand_show_parent = (array)wc_filter_func_get_tax_parent_id( 'brand', $value );
								$brand_show        = array_merge($brand_show, $brand_show_parent);
							}
							// Remove duplicate values from an array
							$brand_show = array_unique($brand_show);
							if( empty($brand_show) )
								return false;
						} else {
							return false;
						}

					}

					if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

						if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['feature'] ) ){
							$feature_show = $wc_filter_relate_term_product_cat[$tax_term_id]['feature'];
							foreach ($feature_show as $value) {
								$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
								$feature_show        = array_merge($feature_show, $feature_show_parent);
							}
							// Remove duplicate values from an array
							$feature_show = array_unique($feature_show);
							if( empty($feature_show) )
								return false;
						} else {
							return false;
						}

					}

					if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

						if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['product_tag'] ) ){
							$product_tag_show = $wc_filter_relate_term_product_cat[$tax_term_id]['product_tag'];
							if( empty($product_tag_show) )
								return false;
						} else {
							return false;
						}

					}

					foreach ($attributes as $attr) {
						if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
							if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id][$attr] ) ){
								${$attr.'_show'} = $wc_filter_relate_term_product_cat[$tax_term_id][$attr];
								if( empty(${$attr.'_show'}) )
									return false;
							} else {
								return false;
							}
						}
					}

					break;
			}

		endif;

		if( isset( $_GET['_product_cat'] ) && !empty( $_GET['_product_cat'] ) ){

			$tax_term_id = (int)$_GET['_product_cat'];

			if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

				if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['product_tag'] ) ){
					$product_tag_show = $wc_filter_relate_term_product_cat[$tax_term_id]['product_tag'];
					if( empty($product_tag_show) )
						return false;
				} else {
					return false;
				}

			}

			if( isset($instance['show_tax_brand']) && $instance['show_tax_brand'] == true  ){
				if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['brand'] ) ){
					$brand_show = $wc_filter_relate_term_product_cat[$tax_term_id]['brand'];
					foreach ($brand_show as $value) {
						$brand_show_parent = (array)wc_filter_func_get_tax_parent_id( 'brand', $value );
						$brand_show        = array_merge($brand_show, $brand_show_parent);
					}
					// Remove duplicate values from an array
					$brand_show = array_unique($brand_show);
					if( empty($brand_show) )
						return false;
				} else {
					return false;
				}

			}

			if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

				if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id]['feature'] ) ){
					$feature_show = $wc_filter_relate_term_product_cat[$tax_term_id]['feature'];
					foreach ($feature_show as $value) {
						$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
						$feature_show        = array_merge($feature_show, $feature_show_parent);
					}
					// Remove duplicate values from an array
					$feature_show = array_unique($feature_show);

					if( empty($feature_show) )
						return false;
				} else {
					return false;
				}

			}

			foreach ($attributes as $attr) {
				if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
					if( isset( $wc_filter_relate_term_product_cat[$tax_term_id] ) && isset( $wc_filter_relate_term_product_cat[$tax_term_id][$attr] ) ){
						${$attr.'_show'} = $wc_filter_relate_term_product_cat[$tax_term_id][$attr];
						if( empty(${$attr.'_show'}) )
							return false;
					} else {
						return false;
					}
				}
			}

		}

		if( isset( $_GET['_product_tag'] ) && !empty( $_GET['_product_tag'] ) ){

			$tax_term_id = (int)$_GET['_product_tag'];

			if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

				if( isset( $wc_filter_relate_term_product_tag[$tax_term_id] ) && isset( $wc_filter_relate_term_product_tag[$tax_term_id]['feature'] ) ){
					$new_feature_show = $wc_filter_relate_term_product_tag[$tax_term_id]['feature'];
					foreach ($new_feature_show as $value) {
						$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
						$new_feature_show        = array_merge($new_feature_show, $feature_show_parent);
					}
					if( isset( $feature_show ) && !empty($feature_show) ){
						$feature_show = array_intersect($new_feature_show, $feature_show);
					} else {
						$feature_show = $new_feature_show;
					}
					// Remove duplicate values from an array
					$feature_show = array_unique($feature_show);
					if( empty($feature_show) )
						return false;
				} else {
					return false;
				}

			}

			foreach ($attributes as $attr) {
				if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
					if( isset( $wc_filter_relate_term_product_tag[$tax_term_id] ) && isset( $wc_filter_relate_term_product_tag[$tax_term_id][$attr] ) ){
						${$attr.'_show'} = $wc_filter_relate_term_product_tag[$tax_term_id][$attr];
						if( empty(${$attr.'_show'}) )
							return false;
					} else {
						return false;
					}
				}
			}

		}

		if( isset( $_GET['_brand'] ) && !empty( $_GET['_brand'] ) ){

			$tax_term_id = (int)$_GET['_brand'];

			if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true ){

				if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id]['feature'] ) ){
					$new_feature_show = $wc_filter_relate_term_brand[$tax_term_id]['feature'];
					foreach ($new_feature_show as $value) {
						$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
						$new_feature_show        = array_merge($new_feature_show, $feature_show_parent);
					}
					if( isset( $feature_show ) && !empty($feature_show) ){
						$feature_show = array_intersect($new_feature_show, $feature_show);
					} else {
						$feature_show = $new_feature_show;
					}
					// Remove duplicate values from an array
					$feature_show = array_unique($feature_show);
				} else {
					return false;
				}

			}

			if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

				if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id]['product_tag'] ) ){
					$product_tag_show = $wc_filter_relate_term_brand[$tax_term_id]['product_tag'];
				} else {
					return false;
				}

			}

			foreach ($attributes as $attr) {
				if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
					if( isset( $wc_filter_relate_term_brand[$tax_term_id] ) && isset( $wc_filter_relate_term_brand[$tax_term_id][$attr] ) ){
						${$attr.'_show'} = $wc_filter_relate_term_brand[$tax_term_id][$attr];
						if( empty(${$attr.'_show'}) )
							return false;
					} else {
						return false;
					}
				}
			}

		}

		if( isset( $_GET['_feature'] ) && !empty( $_GET['_feature'] ) ){

			$tax_term_id = (int)$_GET['_feature'];

			if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true  ){

				if( ! strpos( $_GET['_feature'], ',' ) ){

					if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id]['feature'] ) ){
						$new_feature_show = $wc_filter_relate_term_feature[$tax_term_id]['feature'];
						if( isset( $feature_show ) && !empty($feature_show) ){
							$feature_show = array_intersect($new_feature_show, $feature_show);
						} else {
							$feature_show = $new_feature_show;
						}
						foreach ($feature_show as $value) {
							$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
							$feature_show        = array_merge($feature_show, $feature_show_parent);
						}
						// Remove duplicate values from an array
						$feature_show = array_unique($feature_show);
					} else {
						return false;
					}

				} else {

					$tax_term_id     = $_GET['_feature'];
					$tax_term_id_arr = explode(',', $tax_term_id);
					$new_feature_show    = array();
					foreach ( $tax_term_id_arr as $tax_id ){

						if( empty($new_feature_show) ){
							$new_feature_show = $wc_filter_relate_term_feature[$tax_id]['feature'];
						} else {
							$new_feature_show = array_intersect( $new_feature_show, $wc_filter_relate_term_feature[$tax_id]['feature'] );
						}

					}

					if( isset( $feature_show ) && !empty($feature_show) ){
						$feature_show = array_intersect($new_feature_show, $feature_show);
					} else {
						$feature_show = $new_feature_show;
					}

					if( empty($feature_show) )
						return false;

					foreach ( $feature_show as $value ) {
						$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
						$feature_show        = array_merge($feature_show, $feature_show_parent);
					}
					// Remove duplicate values from an array
					$feature_show = array_unique($feature_show);

				}


			}

			if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

				if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id]['product_tag'] ) ){
					$product_tag_show = $wc_filter_relate_term_feature[$tax_term_id]['product_tag'];
				} else {
					return false;
				}

			}

			foreach ($attributes as $attr) {
				if( isset($instance['show_tax_'.$attr]) && $instance['show_tax_'.$attr] == true  ){
					if( isset( $wc_filter_relate_term_feature[$tax_term_id] ) && isset( $wc_filter_relate_term_feature[$tax_term_id][$attr] ) ){
						${$attr.'_show'} = $wc_filter_relate_term_feature[$tax_term_id][$attr];
						if( empty(${$attr.'_show'}) )
							return false;
					} else {
						return false;
					}
				}
			}

		}

		foreach ($attributes as $attr) {

			if( isset( $_GET['_'.$attr] ) && !empty( $_GET['_'.$attr] ) ){

				$tax_term_id = (int)$_GET['_'.$attr];

				if( isset($instance['show_tax_feature']) && $instance['show_tax_feature'] == true ){

					if( isset( ${'wc_filter_relate_term_'.$attr}[$tax_term_id] ) && isset( ${'wc_filter_relate_term_'.$attr}[$tax_term_id]['feature'] ) ){
						$new_feature_show = ${'wc_filter_relate_term_'.$attr}[$tax_term_id]['feature'];
						foreach ($new_feature_show as $value) {
							$feature_show_parent = (array)wc_filter_func_get_tax_parent_id( 'feature', $value );
							$new_feature_show        = array_merge($new_feature_show, $feature_show_parent);
						}
						if( isset( $feature_show ) && !empty($feature_show) ){
							$feature_show = array_intersect($new_feature_show, $feature_show);
						} else {
							$feature_show = $new_feature_show;
						}
						// Remove duplicate values from an array
						$feature_show = array_unique($feature_show);
					} else {
						return false;
					}

				}

				if( isset($instance['show_tax_product_tag']) && $instance['show_tax_product_tag'] == true  ){

					if( isset( ${'wc_filter_relate_term_'.$attr}[$tax_term_id] ) && isset( ${'wc_filter_relate_term_'.$attr}[$tax_term_id]['product_tag'] ) ){
						$product_tag_show = ${'wc_filter_relate_term_'.$attr}[$tax_term_id]['product_tag'];
					} else {
						return false;
					}

				}

			}

		}

		echo $before_widget;
		echo $before_title . $title . $after_title;

		foreach ($taxonomies as $tax) {
			if( isset( $instance['show_tax_' .$tax->name] ) && $instance['show_tax_' .$tax->name] == true ){

				$current_terms = get_terms( $tax->name, array( 'hide_empty' => 0 ) );

				if( isset( $instance['known_' .$tax->name ] ) && !empty( $instance['known_' .$tax->name ] ) ){

		  			// Update the known_ array with all current terms
		  			foreach ( $current_terms as $current_term ) {
		  				
		  				// Store them in the ['known_' . $tax->name] array for later use on output of widget
		  				$instance['known_' . $tax->name][] = $current_term->term_id;
		  				
		  				$parent_id = $current_term->parent;
		  				
		  				// Terms handling
		  				// Nothing to do if not a Smart Child method
		  				if ( $instance['terms_handling'] == 'auto' ){
							$instance['include_' . $tax->name][] = $current_term->term_id;
		  					continue;
	  					}
		  				// Must be a child term and Smart Child method active
						// therefore should we include the new child term?	
						if ( in_array( $parent_id, $instance['include_' . $custom_tax->name] ) ) {
					
							// This is a child term of a checked parent
							// Therefore add new term to the $instance['include_' . $custom_tax->name] array
							$instance['include_' . $custom_tax->name][] = $current_term->term_id;
					
						}
		  			}
					
				}
				
				if( isset($feature_show) && !empty($feature_show) && $tax->name == 'feature' ) {
					foreach ($instance['include_' . $tax->name] as $key => $value) {
						if( !in_array($value, $feature_show) ){
							unset( $instance['include_' . $tax->name][$key] );
						}
					}
				}

				if( isset( $brand_show ) && !empty( $brand_show ) && $tax->name == 'brand'  ){
					foreach ($instance['include_' . $tax->name] as $key => $value) {
						if( !in_array($value, $brand_show) ){
							unset( $instance['include_' . $tax->name][$key] );
						}
					}
				}

				if( isset( $product_tag_show ) && !empty( $product_tag_show ) && $tax->name == 'product_tag'  ){
					foreach ($instance['include_' . $tax->name] as $key => $value) {
						if( !in_array($value, $product_tag_show) ){
							unset( $instance['include_' . $tax->name][$key] );
						}
					}
				}

				if( isset($cat_child_term) && !empty($cat_child_term) && $tax->name == 'product_cat' ) {
					$cat_term_active = array();
					foreach ($instance['include_' . $tax->name] as $key => $value) {
						if( in_array($value, $cat_child_term) ){
							unset( $instance['include_' . $tax->name][$key] );
							$cat_term_active[] = $value;
						}
					}
				}

				foreach ($attributes as $attr) {
					if( isset(${$attr.'_show'}) && !empty(${$attr.'_show'}) && $tax->name == $attr ){
						foreach ($instance['include_' . $tax->name] as $key => $value) {
							if( !in_array($value, ${$attr.'_show'}) ){
								unset( $instance['include_' . $tax->name][$key] );
							}
						}
					}
				}

				// Build the menu for Taxonomy
				/**
				 * Class List_Category_Images
				 * @var array $args['show_thumb'] - show thumbnail of term
				 */
				$show_thumb = (isset($instance['show_thumb']) && $instance['show_thumb']) ? $instance['show_thumb'] : false;
				$args_list = array(
					'taxonomy'   => $tax->name,
					'show_count' => $instance['show_count'],
					'title_li'   => '',
					'hide_empty' => $instance['hide_empty'] ? true : false,
					'include'    => implode( ',', $instance['include_' . $tax->name] ),
					'echo'       => '0',
					'show_thumb' => $show_thumb,
					'walker'     => new List_Category_Images
				);

				$list = wp_list_categories($args_list);

				if( isset($cat_term_active) ){
					$args_list = array(
						'taxonomy'   => $tax->name,
						'show_count' => $instance['show_count'],
						'title_li'   => '',
						'hide_empty' => $instance['hide_empty'] ? true : false,
						'include'    => implode( ',', $cat_term_active ),
						'echo'       => '0',
						'show_thumb' => $show_thumb,
    					'walker'     => new List_Category_Images
					);

					$list_active = wp_list_categories($args_list);
				}
				$class_style = apply_filters( 'wc_filter_add_class_to_ul', $instance['style'] );

				?>
				<?php
					if ( isset($instance['search'])  && $instance['search'] == true && isset($instance['use_ajax']) && $instance['use_ajax'] == true ) {
						echo '<span class="wc-filter-search"><input type="text" name="search_term" value="" placeholder="'.esc_html__( 'Search option', 'wc-filter' ).'"></span>';
					}
				?>
				<ul class="product-taxonomy <?php echo esc_attr($class_style) . ' ' .esc_attr($tax->name); if( $instance['use_ajax'] ) echo ' taxonomy-filter'; if( isset($list_active) ) echo ' cat_hide'; ?>" data-keys="<?php echo esc_attr($tax->name); ?>">
  				
  					<?php echo $list; ?>
  				  				
  				</ul>
  				<?php if( isset($list_active) ): ?>
					<ul class="product-taxonomy <?php echo esc_attr($class_style) . ' ' .esc_attr($tax->name); if( $instance['use_ajax'] ) echo ' taxonomy-filter'; ?>" data-keys="<?php echo esc_attr($tax->name); ?>">
						<?php echo $list_active; ?>
					</ul>
  				<?php endif;

			}
		}
		?>
		<?php

		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		// Get parsed defaults
		$instance = $this->defaults( $old_instance );

		$new_instance['title']          = isset($new_instance['title']) ? strip_tags($new_instance['title']) : $instance['title'];
		$new_instance['style']          = isset($new_instance['style']) ? $new_instance['style'] : $old_instance['style'];
		$new_instance['show_count']     = ( isset($new_instance['show_count']) && $new_instance['show_count']== 'true' ) ? 'true' : 0;
		$new_instance['use_ajax']       = ( isset($new_instance['use_ajax']) && $new_instance['use_ajax'] == 'true' ) ? 'true' : 0;
		$new_instance['search'] 	    = ( isset($new_instance['search']) && $new_instance['search'] == 'true' ) ? 'true' : 0;
		$new_instance['hide_empty']     = ( isset($new_instance['hide_empty']) && $new_instance['hide_empty'] == 'true' )  ? 'true' : 0;
		$new_instance['show_thumb']     = ( isset($new_instance['show_thumb']) && $new_instance['show_thumb'] == 'true' )  ? 'true' : 0;
		$new_instance['terms_handling'] = 'auto';

		// Get all custom taxonomies
		$taxonomies = wc_filter_func_get_taxonomies( array('product') );
		
		foreach( $taxonomies as $tax ) {
		
			// Update the known_ array with all current terms
			$current_terms = get_terms( $tax->name, array( 'hide_empty' => 0 ) );
  				
  			// Update the known_ array with all current terms
  			foreach ( $current_terms as $current_term ) {
  				
  				// Store them in the ['known_' . $tax->name] array for later use on output of widget
  				$new_instance['known_' . $tax->name][] = $current_term->term_id;
  				
  				$parent_id = $current_term->parent;
  				
  				// Terms handling
  				// Nothing to do if not a Smart Child method
  				if ( $new_instance['terms_handling'] == 'auto' )
  					continue;
  				
  				// Nothing to do if this is a top level term	
  				if ( $parent_id == '0' )
  					continue;
  				
  				// This is a child term and Smart Child method
  				// Need to uncheck this child term if its parent has just been unchecked
  				// Test if this child term's parent has been unchecked AND child term is checked
  				if ( ! in_array( $parent_id, $new_instance['include_' . $tax->name] ) &&
  						in_array( $current_term->term_id, $new_instance['include_' . $tax->name] ) ) {
  					
  					// Parent is unchecked, therefore remove this checked child from include_
  					if ( ( $key = array_search( $current_term->term_id, $new_instance['include_' . $tax->name] ) ) !== false ) {
    					unset( $new_instance['include_' . $tax->name][$key] );
					}
  				}
  			}
  		} // end taxonomy foreach loop
		
		return $new_instance;

	}

	function form( $instance ) {

		// Get parsed defaults
		$instance = $this->defaults( $instance );

		$taxonomies = wc_filter_func_get_taxonomies( array('product') );
		foreach( $taxonomies as $tax ) {
		
  			// Get all terms that currently exist right now, for this custom taxonomy
			$current_terms = get_terms( $tax->name, array( 'hide_empty' => 0 ) );
			
			// Case 1: first use - auto check all terms
			if( empty( $instance['include_' . $tax->name] ) ) {

				foreach( $current_terms as $current_term ) {
				
					// Check all terms in this taxonomy
					$instance['include_' . $tax->name][] = $current_term->term_id;
					
					// Add all terms as "known"
					$instance['known_' . $tax->name][] = $current_term->term_id;
				}
			}
			
			// Populate the 'show_tax' taxonomy checkboxes to prevent PHP undefined index warnings
			if( empty( $instance['show_tax_' . $tax->name] ) ) {
				
				// This is temporary. Only "true" will be saved by the form
				$instance['show_tax_' . $tax->name] = "false";
			}
			
			// Case 2: deal with any new terms added since last save
			// Make sure we have known_ terms
  			if ( ! empty ( $instance['known_' . $tax->name] ) && in_array('product',$tax->object_type) ) {
  				
  				// Loop through all existing terms and look for newly added ones
  				foreach ( $current_terms as $current_term ) {
  					
  					// Do we have a new term added since the widget form was last saved?
  					if( ! in_array( $current_term->term_id, $instance['known_' . $tax->name] ) ) {
  					
  						$parent_id = $current_term->parent;
  						
  						// Terms handling
  						// Auto -> Add in the term, regardless of top level or child
  						if ( $instance['terms_handling'] == 'auto' ) {
  							$instance['include_' . $tax->name][] = $current_term->term_id;
  							continue;
  						}
  							
  						// Must be a child term and Smart Child method active
  						// therefore should we include the new child term?	
  						if ( in_array( $parent_id, $instance['include_' . $tax->name] ) ) {
  						
  							// This is a child term of a checked parent
  							// Therefore add new term to the $instance['include_' . $tax->name] array
  							$instance['include_' . $tax->name][] = $current_term->term_id;
  						
  						}
  					}
  				}
			}
		}
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo esc_html__('Title', 'wc-filter'); ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_name( 'style' ); ?>"><?php echo esc_html__('Style:', 'wc-filter'); ?></label>
			<select name="<?php echo $this->get_field_name( 'style' ); ?>" class="widefat">
				<option value="list" <?php selected('list', $instance['style']); ?>><?php echo esc_html__('List','wc-filter')?></option>
				<option value="dropdown" <?php selected('dropdown', $instance['style']); ?>><?php echo esc_html__('Dropdown','wc-filter')?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="true" <?php checked( 'true', $instance['show_count'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
				<?php echo esc_html__( 'Show post count', 'wc-filter' ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" value="true" <?php checked( 'true', $instance['hide_empty'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>">
				<?php echo esc_html__( 'Hide Term Empty', 'wc-filter' ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" value="true" <?php checked( 'true', $instance['show_thumb'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>">
				<?php echo esc_html__( 'Show Thumbnail', 'wc-filter' ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'use_ajax' ); ?>" name="<?php echo $this->get_field_name( 'use_ajax' ); ?>" value="true" <?php checked( 'true', $instance['use_ajax'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'use_ajax' ); ?>">
				<?php echo esc_html__( 'Use filter', 'wc-filter' ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'search' ); ?>" name="<?php echo $this->get_field_name( 'search' ); ?>" value="true" <?php checked( 'true', $instance['search'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'search' ); ?>">
				<?php echo esc_html__( 'Search term of taxonomy', 'wc-filter' ); ?>
				<i><?php echo esc_html__( '(Only work with option "Use filter")', 'wc-filter' ) ?></i>
			</label>
		</p>
		<h4><?php echo esc_html__( 'Select taxonomies and terms', 'wc-filter' ); ?></h4>
		<p><?php echo esc_html__( 'Use the checklist(s) below to choose which custom taxonomies and terms you want to include in your menu. To hide a taxonomy and all its terms, uncheck the taxonomy name.', 'wc-filter' ); ?></p>
		<?php
			// Produce a checklist of terms for each custom taxonomy
			foreach ( $taxonomies as $tax ) :
		
				$checkboxes = '';
			
				// Need to make sure that the taxonomy has some terms. If it doesn't, skip to the next taxonomy
				// Prevents PHP index notice when tax has no terms
				if( empty( $instance['include_' . $tax->name] ) || !in_array('product',$tax->object_type) )
					continue;
				
				// Get checklist, wc_filter_taxonomy_checklist( $name, $tax, $selected )
				$checkboxes = $this->wc_filter_taxonomy_checklist( $this->get_field_name( 'include_' . $tax->name ), $tax, $instance['include_' . $tax->name] );
				?>
		
				<div class="custom-taxonomies-menu-list wc-filter-taxonomy-wrap">
				
					<p>
						<input type="checkbox" id="<?php echo $this->get_field_id( 'show_tax_' . $tax->name ); ?>" name="<?php echo $this->get_field_name( 'show_tax_' . $tax->name ); ?>" value="true" <?php checked( 'true', $instance['show_tax_'.$tax->name] ); ?> />
						<label for="<?php echo $this->get_field_id( 'show_tax' . $tax->name ); ?>" class="wc-filter-tax-label"><?php echo $tax->label; ?></label>
					</p>
			
					<ul class="custom-taxonomies-menu-checklist wc-filter-taxonomy-checklist">
						<?php echo $checkboxes; ?>
					</ul>
				</div>
		
			<?php
			endforeach; 
		?>
		<script type="text/javascript" charset="utf-8" async defer>
			jQuery(document).ready(function($) {
		        $('.wc-filter-taxonomy-wrap').on('click', 'input[type=checkbox]', function() {
		            if( $(this).is(':checked') ){
		                $(this).parent().next('ul').css('display', 'block');
		            } else {
		                $(this).parent().next('ul').css('display', 'none');
		            }
		        });
		        $('.wc-filter-taxonomy-wrap').each( function(index, el) {
		            if( $(this).find('p > input[type=checkbox]').is(':checked') ){
		                $(this).find('> ul').css('display', 'block');
		            }
		        });
		    });
		</script>
		<?php 
	}

}