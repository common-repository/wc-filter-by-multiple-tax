<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! function_exists('WC') ):

	// Register taxonomies Brand & Feature for WC Product
    /**
     * BRANDS
     */
    if( ! function_exists( 'wc_filter_custom_taxonomy_brands()' ) && ! taxonomy_exists('feature') ):

	    function wc_filter_custom_taxonomy_brands()  {

	        $labels = array(
				'name'                       => esc_html__('Brands', 'wc-filter'),
				'singular_name'              => esc_html__('Brand', 'wc-filter'),
				'menu_name'                  => esc_html__('Brands', 'wc-filter'),
				'all_items'                  => esc_html__('All Brands', 'wc-filter'),
				'parent_item'                => esc_html__('Parent Brand', 'wc-filter'),
				'parent_item_colon'          => esc_html__('Parent Brand:', 'wc-filter'),
				'new_item_name'              => esc_html__('New Brand Name', 'wc-filter'),
				'add_new_item'               => esc_html__('Add New Brand', 'wc-filter'),
				'edit_item'                  => esc_html__('Edit Brand', 'wc-filter'),
				'update_item'                => esc_html__('Update Brand', 'wc-filter'),
				'separate_items_with_commas' => esc_html__('Separate Brand with commas', 'wc-filter'),
				'search_items'               => esc_html__('Search Brands', 'wc-filter'),
				'add_or_remove_items'        => esc_html__('Add or remove Brands', 'wc-filter'),
				'choose_from_most_used'      => esc_html__('Choose from the most used Brands', 'wc-filter'),
	        );
	        $args = array(
	            'labels'                     => $labels,
	            'hierarchical'               => true,
	            'public'                     => true,
	            'show_ui'                    => true,
	            'show_admin_column'          => true,
	            'show_in_nav_menus'          => true,
	            'show_tagcloud'              => true,
	        );
	        register_taxonomy( 'brand', 'product', $args );
	        register_taxonomy_for_object_type( 'brand', 'product' );

	    }
	    add_action( 'init', 'wc_filter_custom_taxonomy_brands' );

    endif;
    // Disable checked on top
	if( ! function_exists('wc_filter_brand_taxonomy_disable_checked_ontop') ):

		function wc_filter_brand_taxonomy_disable_checked_ontop( $args ) {

			if ( ! empty( $args[ 'taxonomy' ] ) && 'brand' === $args[ 'taxonomy' ] ) {
				$args[ 'checked_ontop' ] = false;
			}
			return $args;

		}
		add_filter( 'wp_terms_checklist_args', 'wc_filter_brand_taxonomy_disable_checked_ontop' );

	endif;
	
    /**
     * FEATURES
     */
    if( ! function_exists( 'wc_filter_custom_taxonomy_feature()' ) && ! taxonomy_exists('feature') ):
	    function wc_filter_custom_taxonomy_feature()  {

	        $labels = array(
				'name'                       => esc_html__('Features', 'wc-filter'),
				'singular_name'              => esc_html__('Feature', 'wc-filter'),
				'menu_name'                  => esc_html__('Features', 'wc-filter'),
				'all_items'                  => esc_html__('All Features', 'wc-filter'),
				'parent_item'                => esc_html__('Parent Feature', 'wc-filter'),
				'parent_item_colon'          => esc_html__('Parent Feature:', 'wc-filter'),
				'new_item_name'              => esc_html__('New Feature Name', 'wc-filter'),
				'add_new_item'               => esc_html__('Add New Feature', 'wc-filter'),
				'edit_item'                  => esc_html__('Edit Feature', 'wc-filter'),
				'update_item'                => esc_html__('Update Feature', 'wc-filter'),
				'separate_items_with_commas' => esc_html__('Separate Feature with commas', 'wc-filter'),
				'search_items'               => esc_html__('Search Features', 'wc-filter'),
				'add_or_remove_items'        => esc_html__('Add or remove Features', 'wc-filter'),
				'choose_from_most_used'      => esc_html__('Choose from the most used Features', 'wc-filter'),
	        );
	        $args = array(
	            'labels'                     => $labels,
	            'hierarchical'               => true,
	            'public'                     => true,
	            'show_ui'                    => true,
	            'show_admin_column'          => true,
	            'show_in_nav_menus'          => true,
	            'show_tagcloud'              => true,
	        );
	        register_taxonomy( 'feature', 'product', $args );
	        register_taxonomy_for_object_type( 'feature', 'product' );

	    }
	    add_action( 'init', 'wc_filter_custom_taxonomy_feature' );

    endif;
    // Disable checked on top
	if( ! function_exists('wc_filter_feature_taxonomy_disable_checked_ontop') ):

		function wc_filter_feature_taxonomy_disable_checked_ontop( $args ) {

			if ( ! empty( $args[ 'taxonomy' ] ) && 'feature' === $args[ 'taxonomy' ] ) {
				$args[ 'checked_ontop' ] = false;
			}
			return $args;

		}
		add_filter( 'wp_terms_checklist_args', 'wc_filter_feature_taxonomy_disable_checked_ontop' );

	endif;
	// Hidden child term in dropdown parent feature taxonomy
	if( ! function_exists('wc_filter_show_only_parent_feature') ){

		function wc_filter_show_only_parent_feature($dropdown_args, $taxonomy){
			if( $taxonomy == 'feature'){
				$dropdown_args['parent'] = '0';
			}
			return $dropdown_args;
		}

		add_filter( 'taxonomy_parent_dropdown_args', 'wc_filter_show_only_parent_feature', 10, 2 );
	}

	//Hook: Sortable custom taxonomies
    if( ! function_exists( 'wc_filter_sortable_taxonomies' ) ):

        function wc_filter_sortable_taxonomies(){

            return array( 'product_cat', 'brand', 'feature' );

        }
	    add_filter( 'woocommerce_sortable_taxonomies', 'wc_filter_sortable_taxonomies' );

    endif;

    // Hook: Filter by Taxonomy get product
    if( ! function_exists('wc_filter_is_product_query') ) :

        function wc_filter_is_product_query( $query ) {

            if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product' )
                return true;
            
            if( isset($query->query_vars['product_cat']) && $query->query_vars['product_cat'] !== '' )
                return true;

            if( isset($query->query_vars['brand']) && $query->query_vars['brand'] !== '' )
                return true;

            if( isset($query->query_vars['feature']) && $query->query_vars['feature'] !== '' )
                return true;

            return;

        }

    endif;

    if( ! function_exists('wc_filter_product_pre_get_posts') ) :

        function wc_filter_product_pre_get_posts($query) {
            if( is_admin() ) {
                return $query;
            }

            if( wc_filter_is_product_query( $query ) ) {

                if ( is_shop() || is_product_category() || is_product_tag() || is_tax( 'product_cat' ) ){

					$product_cat = isset($_GET['_product_cat']) ? $_GET['_product_cat'] : '';
					$brand       = isset($_GET['_brand']) ? $_GET['_brand'] : '';
					$feature     = isset($_GET['_feature']) ? $_GET['_feature'] : '';
					$product_tag = isset($_GET['_product_tag']) ? $_GET['_product_tag'] : '';

                    if( $feature != '' )
                        $feature      = explode(',',$feature);

                    $tax_query = array(
                        'relation' => 'AND',
                    );

                    if( $product_cat != '' ){
                        $tax_query[] = array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'term_id',
                            'terms'    => $product_cat
                        );
                    }

                    if( $brand != '' ){
                        $tax_query[] = array(
                            'taxonomy' => 'brand',
                            'field'    => 'term_id',
                            'terms'    => $brand
                        );
                    }

                    if( $feature != '' ){
                        foreach ($feature as $value) {
                            $tax_query[] = array(
                                'taxonomy' => 'feature',
                                'field'    => 'term_id',
                                'terms'    => $value
                            );
                        }
                    }

                    if( $product_tag != '' ){
                        $tax_query[] = array(
                            'taxonomy' => 'product_tag',
                            'field'    => 'term_id',
                            'terms'    => $product_tag
                        );
                    }

                    $pa_attributes = wc_filter_func_get_all_product_attribute();
                    if( !empty($pa_attributes) ){
	                    foreach ($pa_attributes as $key => $value) {
	                    	$attribute_ = isset($_GET['_'.$key]) ? $_GET['_'.$key] : '';
	                    	if( !empty($attribute_) ){
		                    	$tax_query[] = array(
									'taxonomy' => $key,
		                            'field'    => 'term_id',
		                            'terms'    => $attribute_
		                		);
		                    }
	                    }
                    }

                    $query->set('tax_query', $tax_query);

                }
            }
        }
        add_action( 'pre_get_posts', 'wc_filter_product_pre_get_posts', 20 );

    endif;

endif;