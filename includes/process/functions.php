<?php
/**
 * This function get all functions
 * @package WC Filter By Multiple Tax
 * @author  TVLA92
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function return path page current
if( ! function_exists( 'wc_filter_func_get_wc_page_current_url' ) ):

    function wc_filter_func_get_wc_page_current_url() {

    	if( class_exists( 'woocommerce' ) ){

    		// Get url page shop
    		if( wc_get_page_id( 'shop' ) && ! is_woocommerce() ){
	    		$page_current = get_permalink( wc_get_page_id( 'shop' ) );
    		}

    		// Check page is paged
	        if( is_paged() ){

	            if( is_shop() ){
	                
	                $page_current = get_permalink( wc_get_page_id( 'shop' ) );
	                return esc_url($page_current);
	                
	            }

	            if( is_tax() ){

	                $tax_name    = get_queried_object()->taxonomy;
	                $tax_term_id = get_queried_object()->term_taxonomy_id;
	                $product_tax = array_keys(wc_filter_func_get_product_list_taxonomy_name());

	                if( in_array( $tax_name, $product_tax ) ){

	                    $shop = get_permalink( wc_get_page_id( 'shop' ) );
	                    $page_current = $shop . '?_' . $tax_name . '='.$tax_term_id;
	                    return esc_url($page_current);

	                } else {

	                    return;

	                }

	            }

	        }
	        return isset($page_current) ? $page_current : null;

        } else {

        	return;

        };


    }

endif;

//  Function return protocol relative asset URL.
if( ! function_exists( 'wc_filter_func_get_asset_url' ) ):

    function wc_filter_func_get_asset_url( $path ) {

        return str_replace( array( 'http:', 'https:' ), '', plugins_url( $path, WC_FILTER_PLUGIN_FILE ) );
        
    }

endif;

// Create Transient save feature&Brand related with Product category
if( ! function_exists( 'wc_filter_func_create_transient_taxonomies' ) ):

    function wc_filter_func_create_transient_taxonomies(){

    	$tax_arr = array_keys(wc_filter_func_get_product_list_taxonomy_name());
    	if( empty($tax_arr) ){
    		return false;
    	}

    	foreach ($tax_arr as $tax) {
    		// EX: wc_filter_relate_term_product_cat
    		$key_transient = "wc_filter_relate_term_" . $tax; 
			if( ! get_transient( $key_transient ) ){
				// EX: $relate_term_product_cat
				${"relate_term_".$tax} = wc_filter_func_get_transient_taxonomy( $tax );
				// EX: set_transient( 'wc_filter_relate_term_product_cat', $relate_term_product_cat, 365 * DAY_IN_SECONDS );
				set_transient( $key_transient, ${"relate_term_".$tax}, 365 * DAY_IN_SECONDS );

			}
    	}

    }

    // Run once (In the first install plugin or Not exists key trainsient product cat)
    if ( is_admin() ) :
    	if( ! get_transient( 'wc_filter_relate_term_product_cat' ) ){
    		add_action( 'post_updated', 'wc_filter_func_create_transient_taxonomies' );
    		add_action( 'admin_init', 'wc_filter_func_create_transient_taxonomies' );
    	}
        add_action( 'save_post', 'wc_filter_func_create_transient_taxonomies' );
    endif;

endif;

// Update Transient when have action update post
if( !function_exists( 'wc_filter_func_update_transient_taxonomies' ) ):

	function wc_filter_func_update_transient_taxonomies( $post_ID ) {

		// Check Post Type is only product
		if( get_post_type( $post_ID ) !== 'product' )
			return;

		$tax_arr = array_keys(wc_filter_func_get_product_list_taxonomy_name());

		foreach ($tax_arr as $tax) {
			// Transient
			$key_transient = "wc_filter_relate_term_".$tax;
			${"relate_term_".$tax} = get_transient( $key_transient );
			// Terms
			${"post_terms_".$tax} = wp_get_post_terms( $post_ID, $tax, array("fields" => "ids") );
			if( $tax == 'product_cat' || $tax == 'feature' ){
				foreach ( ${"post_terms_".$tax} as $term_id ){
					${"post_terms_".$tax} = array_merge( wc_filter_func_get_all_tax_parent_id( $tax, $term_id ), ${"post_terms_".$tax} );
				}
			}
			${"post_terms_".$tax} = array_unique(${"post_terms_".$tax});
		}

		//Update
		foreach( $tax_arr as $tax ){
			
			$key_transient = "wc_filter_relate_term_".$tax;

			if( empty(${"post_terms_".$tax}) )
				continue;

			foreach ( ${"post_terms_".$tax} as $value ){

				foreach( $tax_arr as $tax_name ){

					// Check term in relate_term_, if posts is empty, remove term
					if( isset( ${"relate_term_".$tax}[$value] ) && isset( ${"relate_term_".$tax}[$value][$tax_name] ) ):

						foreach ( ${"relate_term_".$tax}[$value][$tax_name] as $key => $term_id ){

							if ( empty($term_id) )
								continue;

							$posts = get_posts(
								array(
									'post_type'      => 'product',
									'post_status'    => 'publish',
									'posts_per_page' => 30,
									'tax_query'      => array(
										'relation'     => 'AND',
										array(
											'taxonomy' => $tax,
											'field'    => 'term_id',
											'terms'    => $value
										),
										array(
											'taxonomy' => $tax_name,
											'field'    => 'term_id',
											'terms'    => $term_id
										)
									)
								)
							);

							if( empty( $posts ) )
								unset( ${"relate_term_".$tax}[$value][$tax_name][$key] );

						}

					endif;

					// Add new term
					foreach ( ${"post_terms_".$tax_name} as $val ){

						if( isset( ${"relate_term_".$tax}[$value] ) && isset( ${"relate_term_".$tax}[$value][$tax_name] ) ) {
							if ( ! in_array( $val, ${"relate_term_" . $tax}[ $value ][ $tax_name ] ) ) {
								${"relate_term_" . $tax}[ $value ][ $tax_name ][] = $val;
							}
						} else {
							${"relate_term_" . $tax}[ $value ][ $tax_name ][] = $val;
						}

					}

				}
			}

			set_transient( $key_transient, ${"relate_term_".$tax}, 365 * DAY_IN_SECONDS );
		}

	}

	if( is_admin() ):
		add_action( 'post_updated', 'wc_filter_func_update_transient_taxonomies' );
	endif;

endif;

// Get Transient save taxonomies related with term of taxonomy in taxonomies
if( ! function_exists ( 'wc_filter_func_get_transient_taxonomy' ) ):

	function wc_filter_func_get_transient_taxonomy( $tax_name, $list_relate = array() ) {

		$list_tax    = wc_filter_func_get_product_list_taxonomy_name();
		unset($list_tax[$tax_name]);
		$list_relate = array_keys($list_tax);
		if( empty( $tax_name ) || empty( $list_relate ) )
			return;

		$taxonomies = get_categories( array( 'taxonomy' => (string)$tax_name ) );

		if( !$taxonomies )
			return;

		$post_per_page = 30;

		$relate_term_tax_name = array();
		foreach ($taxonomies as $val) {

			$term_id = $val->term_id;
			foreach ($list_relate as $value) {
				${$value."_parent_terms"} = array();
			}
			${$tax_name."_parent_terms"} = array();

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => (int)$post_per_page,
				'post_status'    => 'publish',
				'orderby '       => 'date',
				'order'          => 'DESC',
				'tax_query'      => array(
					array(
						'taxonomy' => $tax_name,
						'field'    => 'term_id',
						'terms'    => $term_id
					)
				)
			);
			$new_query = new WP_Query($args);

			if( !$new_query->have_posts() )
				continue;

			while ( $new_query->have_posts() ) {

				$new_query->the_post();
				global $post;
				$product_id = $post->ID;

				// Get all term taxonomies has in product by product_id
				foreach ($list_relate as $key => $value) {

					${$value."_terms"} = wp_get_post_terms( $product_id, $value, array("fields" => "ids") );

					foreach (${$value."_terms"} as $_term_id) {

						if( !in_array( $_term_id, ${$value."_parent_terms"} ) ){
							${$value."_parent_terms"}[] = $_term_id;
						}

					}

				}

				${$tax_name."_terms"} = wp_get_post_terms( $product_id, $tax_name, array("fields" => "ids") );
				foreach (${$tax_name."_terms"} as $key => $value) {

					if( !in_array( $value, ${$tax_name."_parent_terms"} ) ){
						${$tax_name."_parent_terms"}[] = $value;
					}

				}

			}
			wp_reset_query();

			if( !in_array($term_id, $relate_term_tax_name) ){

				foreach ($list_relate as $key => $value) {
					$relate_term_tax_name[$term_id][$value] = ${$value."_parent_terms"};
				}

				$relate_term_tax_name[$term_id][$tax_name] = ${$tax_name."_parent_terms"};

			} else {

				foreach ($list_relate as $key => $value) {
					$relate_term_tax_name[$term_id][$value] = array_merge( $relate_term_tax_name[$term_id][$value], ${$value."_parent_terms"} );
				}

				$relate_term_tax_name[$term_id][$tax_name] = array_merge( $relate_term_tax_name[$term_id][$tax_name], ${$tax_name."_parent_terms"} );

			}

		}

		return $relate_term_tax_name;

	}

endif;

// Get function Query
if( ! function_exists( 'wc_filter_func_get_query' ) ):
	function wc_filter_func_get_query( $post_type, $post_per_page = 10, $paged = 1, $orderby='date', $order='DESC', $taxonomy='', $term_id = '' ){
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $post_per_page,
			'post_status'    => 'publish',
			'paged'          => $paged,
			'order'			 => $order
		);

        switch ( $orderby ) {
            case 'title':
                $args['orderby']  = 'title';
                break;
            case 'modified':
                $args['orderby']  = 'modified';
                break;
            case 'rand' :
                $args['orderby']  = 'rand';
                break;
            default :
                $args['orderby']  = 'date';
                break;
        }

        if( function_exists( 'WooCommerce' ) ){

        	if( $orderby == 'popularity' ){
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales';
        	}
        	if( $orderby == '_featured' ){
                $args['meta_key'] = '_featured';
        	}
        	if( $orderby == 'rating' ){
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating';
        	}
        	if( $orderby == 'sales' ){
                $args['meta_key'] = 'total_sales';
                $args['orderby']  = 'meta_value_num';
        	}
        	if( $orderby == 'price' ){
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
        	}

        }

        if( $taxonomy != '' && $term_id != '' ){
        	$args['tax_query'] = array(
        		array(
        			'taxonomy' => $taxonomy,
        			'field' => 'term_id',
        			'terms' => $term_id
    			)
    		);
        }

		return new WP_Query($args);

	}
endif;

// Get function filter use for shortcode
if( ! function_exists( 'wc_filter_func_get_filter' ) ):
	function wc_filter_func_get_filter(){
		$post_type     = $_POST['post_type'];
		$orderby       = $_POST['orderby'];
		$order         = $_POST['order'];
		$post_per_page = $_POST['per_page'];
		$taxonomy      = $_POST['taxonomy'];
		$term_id       = $_POST['term_id'];

		$new_posts = wc_filter_func_get_query( $post_type, $post_per_page, $paged = 1, $orderby, $order, $taxonomy, $term_id );

		if( $post_type == 'product' && $new_posts->have_posts() && class_exists('WooCommerce') ){
			while ( $new_posts->have_posts() ): $new_posts->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
		}
		
        wp_reset_postdata();

		wp_die();
	}
	add_action( 'wp_ajax_wc_filter_get_filter', 'wc_filter_func_get_filter' );
	add_action( 'wp_ajax_nopriv_wc_filter_get_filter', 'wc_filter_func_get_filter' );
endif;

/**
 * Function get tax term id has parent = 0
 * @var int
 */
if( ! function_exists( 'wc_filter_func_get_tax_parent_id' ) ):
    function wc_filter_func_get_tax_parent_id( $tax_name, $term_id){
        if( $term_id != 0 ){
            $term_parent = get_term($term_id, $tax_name);
            if( $term_parent->parent == 0 ){
                return $term_id;
            } else {
                $term_id = wc_filter_func_get_tax_parent_id( $tax_name, $term_parent->parent );
            }
        }
        return $term_id;
    }
endif;

/**
 * Function merge all term of family into array
 * @var array
 */
if( !function_exists( 'wc_filter_func_get_all_tax_parent_id' ) ):

	function wc_filter_func_get_all_tax_parent_id( $tax_name, $term_id, $terms_parent = array() ){

		$term_obj = get_term($term_id, $tax_name);
		if( $term_obj->parent != 0 ){
			$terms_parent[] = $term_obj->parent;
			$terms_parent   = array_merge(wc_filter_func_get_all_tax_parent_id( $tax_name, $term_obj->parent, $terms_parent ), $terms_parent);
		}

		return $terms_parent;

	}

endif;

/**
 * Get all custom taxonomies on this install
 * @var array
 */
if( ! function_exists( 'wc_filter_func_get_taxonomies' ) ):

    function wc_filter_func_get_taxonomies( $object_type = array() ){

        $args = apply_filters( 'wc_filter_args_taxonomies', array(
            'object_type' => $object_type,
            'public'      => true,
            '_builtin'    => false
        ) );
        $output     = 'objects';
        $operator   = 'and';
        $taxonomies = get_taxonomies( $args, $output, $operator );

        return $taxonomies;
    }

endif;

/**
 * Get list taxonomy of product
 * @var array
 */
if( ! function_exists( 'wc_filter_func_get_product_list_taxonomy_name' ) ):

    function wc_filter_func_get_product_list_taxonomy_name( $product_tax = array() ) {

        $taxonomies = wc_filter_func_get_taxonomies( array('product') );

        foreach ($taxonomies as $value) {
            $key = $value->name;
        	$name = $value->labels->singular_name;
            $product_tax[$key] = $name;
        }

        return $product_tax;

    }

endif;

/**
 * Get all product attributes
 * @var array
 */
if( ! function_exists( 'wc_filter_func_get_all_product_attribute' ) ):

	function wc_filter_func_get_all_product_attribute( $attributes = array() ) {

		$taxonomies = wc_filter_func_get_product_list_taxonomy_name();

		foreach ($taxonomies as $key => $value) {
			$patterm = '/pa_(.+)/';
			if( preg_match($patterm, $key) ){
				$attributes[$key] = $value;
			}
		}

		return $attributes;

	}

endif;