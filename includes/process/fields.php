<?php
/**
 * Create form setting
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wc_filter_func_render_form_setting' ) ) :

	function wc_filter_func_render_form_setting( $fields = array() ) {

		$default_field = array(
			'title'   => '',
			'name'    => '',
			'id_form' => uniqid( 'id-form-' ),
			'button'  => esc_html__( 'Save Changes', 'realty-portal' ),
			'fields'  => array(),
		);

		$fields = wp_parse_args( $fields, $default_field );

		extract( $fields );

		?>
		<form class="rp-validate-form rp-setting-form" id="<?php echo esc_attr( $id_form ); ?>">
			<div class="rp-setting-wrap">
				<?php if ( ! empty( $title ) ) : ?>
					<h1 class="rp-setting-title">
						<?php echo esc_html( $title ); ?>
					</h1>
				<?php endif; ?>
				<div class="rp-setting-notice" id="<?php echo $id_notice = uniqid( 'id-notice-' ) ?>"></div>
				<?php
				foreach ( $fields as $field ) {
					if ( isset( $field[ 'is_section' ] ) && $field[ 'is_section' ] ) {
						echo "<h3>{$field['title']}</h3>";
						continue;
					}
					if ( empty( $field[ 'name' ] ) || empty( $field[ 'type' ] ) ) {
						continue;
					}
					$std   = ( isset( $field[ 'std' ] ) ? esc_attr( $field[ 'std' ] ) : '' );
					$type  = ( isset( $field[ 'type' ] ) ? esc_attr( $field[ 'type' ] ) : '' );
					$class = ( isset( $field[ 'class' ] ) ? esc_attr( $field[ 'class' ] ) : '' );
					$value = WC_Filter_By_Multiple_Tax::get_setting( esc_attr( $name ), esc_attr( $field[ 'name' ] ), $std );
					?>
				<div class="rp-setting-item <?php echo esc_attr( $type ); ?> <?php echo esc_attr( $class ); ?>">
					<?php if ( isset( $field[ 'title' ] ) ) : ?>
						<label for="<?php echo esc_attr( $field[ 'name' ] ); ?>">
							<?php echo esc_html( $field[ 'title' ] ); ?>
						</label>
					<?php endif; ?>
					<?php
					/**
					 * Require field
					 */
					RP_Custom_Fields::renderFormField( $type, $field, $value, false );
					?>
					</div><?php
				}
				?>

				<input type="hidden" name="name_option" value="<?php echo esc_attr( $name ); ?>" />

				<input class="button button-primary btn-property-submit" type="submit" name="submit" data-id-notice="<?php echo esc_attr( $id_notice ); ?>" data-name-option="property" data-id-form="<?php echo esc_attr( $id_form ); ?>" value="<?php echo esc_html( $button ); ?>" />
			</div>

		</form>
		<?php
	}

endif;