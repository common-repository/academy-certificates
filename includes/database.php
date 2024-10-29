<?php
namespace AcademyCertificates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {
	private $certificate_template_fields = [];
	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'create_initial_post_types' ] );
		add_action( 'add_meta_boxes', [$self, 'certificate_templates_meta_boxes'] );
		add_action( 'save_post', [$self, 'certificate_templates_meta_boxes_save'], 1, 2 );
		add_action( 'publish_academy_certificate', [$self, 'certificate_template_private'], 10, 2 );
		add_action( 'academy_certificate_process_certificate_template_meta', [$self, 'certificate_templates_process_meta'], 10, 2 );
		add_action( 'academy_certificate_process_certificate_template_meta', [$self, 'certificate_template_process_images_meta'], 10, 2 );

	}


	public function create_initial_post_types() {
		$args = array(
			'labels'              => array(
				'name'               => _x( 'Certificate Templates', 'post type general name', 'academy-certificates' ),
				'singular_name'      => _x( 'Certificate Template', 'post type singular name', 'academy-certificates' ),
				'add_new'            => _x( 'Add New Template', 'post type add_new', 'academy-certificates' ),
				'add_new_item'       => __( 'Add New Template', 'academy-certificates' ),
				'edit_item'          => __( 'Edit Certificate Template', 'academy-certificates' ),
				'new_item'           => __( 'New Certificate Template', 'academy-certificates' ),
				'all_items'          => __( 'Certificate Templates', 'academy-certificates' ),
				'view_item'          => __( 'View Certificate Template', 'academy-certificates' ),
				'search_items'       => __( 'Search Certificate Templates', 'academy-certificates' ),
				'not_found'          => __( 'No certificate templates found', 'academy-certificates' ),
				'not_found_in_trash' => __( 'No certificate templates found in Trash', 'academy-certificates' ),
				'parent_item_colon'  => '',
				'menu_name'          => __( 'Certificates', 'academy-certificates' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'query_var'           => true,
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'menu_icon'          => 'dashicons-welcome-learn-more',
				'menu_position'		=> 2
		);

		register_post_type( 'academy_certificate', $args );
	}

	public function certificate_templates_meta_boxes() {

		// Certificate Primary Image box.
		add_meta_box(
			'academy-certificate-image',
			__( 'Certificate Background Image <small>&ndash; Used to lay out the certificate fields found in the Certificate Data box.</small>', 'academy-certificates' ),
			[$this, 'certificate_template_image_meta_box'],
			'academy_certificate',
			'normal',
			'high'
		);
	
		// Certificate Data box.
		add_meta_box(
			'academy-certificate-data',
			__( 'Certificate Settings', 'academy-certificates' ),
			[$this, 'certificate_template_data_meta_box'],
			'academy_certificate',
			'side',
			'core'
		);
	
		// Remove unnecessary meta boxes.
		remove_meta_box( 'wpseo_meta', 'academy_certificate', 'normal' );
		remove_meta_box( 'woothemes-settings', 'academy_certificate', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'academy_certificate', 'normal' );
		remove_meta_box( 'slugdiv', 'academy_certificate', 'normal' );
	}

	public function certificate_templates_meta_boxes_save( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( is_int( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		if (
			empty( $_POST['certificates_meta_nonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Leave nonce value unmodified.
			|| ! wp_verify_nonce( wp_unslash( $_POST['certificates_meta_nonce'] ), 'certificates_save_data' )
		) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( 'academy_certificate' != $post->post_type ) {
			return;
		}
	
		do_action( 'academy_certificate_process_certificate_template_meta', $post_id, $post );
	}
	
	public function certificate_template_private( $post_id, $post ) {

		global $wpdb;
	
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'private' ), array( 'ID' => (int) $post_id ) );
	}


	public function certificate_template_data_meta_box( $post ) {
		wp_nonce_field( 'certificates_save_data', 'certificates_meta_nonce' );
		$default_fonts   = array(
			'Helvetica' => 'Helvetica',
			'Courier'   => 'Courier',
			'Times'     => 'Times',
		);
		$available_fonts = array_merge( array( '' => '' ), $default_fonts );
		$this->certificate_template_fields = get_post_meta( $post->ID, '_certificate_template_fields', true);
		include ACADEMY_CERTIFICATES_INCLUDES_DIR_PATH . 'admin/views/meta-settings.php';
	}
	
	
	public function certificate_template_image_meta_box() {

		global $post;
	
		$image_src = '';
		$image_id  = '';
	
		$image_ids = get_post_meta( $post->ID, '_image_ids', true );
	
		if ( is_array( $image_ids ) && count( $image_ids ) > 0 ) {
	
			if ( is_numeric( $image_ids[0] ) ) {
	
				$image_id   = $image_ids[0];
				$image_src  = wp_get_attachment_url( $image_id );
				$attachment = wp_get_attachment_metadata( $image_id );
	
			}
		}
	
		?>
		<div id="certificate_image_wrapper" style="position:relative;">
			<img id="certificate_image_0" src="<?php echo esc_attr( $image_src ); ?>" style="max-width:100%;" />
		</div>
		<input type="hidden" name="upload_image_id[0]" id="upload_image_id_0" value="<?php echo esc_attr( $image_id ); ?>" />
		<p>
			<a title="<?php esc_attr_e( 'Set certificate image', 'academy-certificates' ); ?>" href="#" id="set-certificate-image"><?php esc_html_e( 'Set certificate image', 'academy-certificates' ); ?></a>
			<a title="<?php esc_attr_e( 'Remove certificate image', 'academy-certificates' ); ?>" href="#" id="remove-certificate-image" style="<?php echo ( ! $image_id ? 'display:none;' : '' ); ?>"><?php esc_html_e( 'Remove certificate image', 'academy-certificates' ); ?></a>
		</p>
		<?php
	}
	



	/**
	 * Rendres a custom admin input field to select a font which includes font
	 * family, size and style (bold/italic).
	 *
	 * @since 1.0.0
	 */
	public function certificate_templates_wp_font_select( $field ) {

		global $post;

		$thepostid = $post->ID;

		// Values.
		$font_family_value = $font_size_value = $font_style_value = '';

		if ( '_certificate' == $field['id'] ) {

			// Certificate defaults.
			$font_family_value = get_post_meta( $thepostid, $field['id'] . '_font_family', true );
			$font_size_value   = get_post_meta( $thepostid, $field['id'] . '_font_size', true );
			$font_style_value  = get_post_meta( $thepostid, $field['id'] . '_font_style', true );

		} else {

			// Field-specific overrides.
			$certificate_fields = get_post_meta( $thepostid, '_certificate_template_fields', true );

			$field_name = ltrim( $field['id'], '_' );

			if ( is_array( $certificate_fields ) ) {
				if ( isset( $certificate_fields[ $field_name ]['font']['family'] ) ) {
					$font_family_value = $certificate_fields[ $field_name ]['font']['family'];
				}
				if ( isset( $certificate_fields[ $field_name ]['font']['size'] ) ) {
					$font_size_value = $certificate_fields[ $field_name ]['font']['size'];
				}
				if ( isset( $certificate_fields[ $field_name ]['font']['style'] ) ) {
					$font_style_value = $certificate_fields[ $field_name ]['font']['style'];
				}
			}
		}

		// Defaults.
		if ( ! $font_size_value && isset( $field['font_size_default'] ) ) {
			$font_size_value = $field['font_size_default'];
		}

		echo '<div class="academyc-typography">';
				echo '<label for="' . esc_attr( $field['id'] ) . '_font_family">' . esc_html( $field['label'] ) . '<span class="dashicons dashicons-edit"></span></label>';
			echo '<div class="academyc-typography__body">';
				echo '<label class="fontfamily">'.esc_html__('Font Family', 'academy certificates');
				echo '<select id="' . esc_attr( $field['id'] ) . '_font_family" name="' . esc_attr( $field['id'] ) . '_font_family" class="select short">';
					foreach ( $field['options'] as $key => $value ) {

						echo '<option value="' . esc_attr( $key ) . '" ';
						selected( $font_family_value, $key );
						echo '>' . esc_html( $value ) . '</option>';

					}
				echo '</select></label>';

				echo '<label>'.esc_html__('Font Size', 'academy certificates').'<input type="text" size="2" name="' . esc_attr( $field['id'] ) . '_font_size" id="' . esc_attr( $field['id'] ) . '_font_size" value="' . esc_attr( $font_size_value ) . '" placeholder="' . esc_attr__( 'Size', 'academy-certificates' ) . '" /> </label>';

				echo '<label for="' . esc_attr( $field['id'] ) . '_font_style_b">' . esc_html__( 'Bold', 'academy-certificates' ) . '<input type="checkbox" class="checkbox" style="margin-top:4px;" name="' . esc_attr( $field['id'] ) . '_font_style_b" id="' . esc_attr( $field['id'] ) . '_font_style_b" value="yes" ';
				checked( false !== strpos( $font_style_value, 'B' ), true );
				echo ' /> </label>';

				echo '<label for="' . esc_attr( $field['id'] ) . '_font_style_i">' . esc_html__( 'Italic', 'academy-certificates' ) . '<input type="checkbox" class="checkbox" style="margin-top:4px;" name="' . esc_attr( $field['id'] ) . '_font_style_i" id="' . esc_attr( $field['id'] ) . '_font_style_i" value="yes" ';
				checked( false !== strpos( $font_style_value, 'I' ), true );
				echo ' /> </label>';

				if ( '_certificate' != $field['id'] ) {

					echo '<label for="' . esc_attr( $field['id'] ) . '_font_style_c">' . esc_html__( 'Center Align', 'academy-certificates' ) . '<input type="checkbox" class="checkbox" style="margin-top:4px;" name="' . esc_attr( $field['id'] ) . '_font_style_c" id="' . esc_attr( $field['id'] ) . '_font_style_c" value="yes" ';
					checked( false !== strpos( $font_style_value, 'C' ), true );
					echo ' /> </label>';

					echo '<label for="' . esc_attr( $field['id'] ) . '_font_style_o">' . esc_html__( 'Border', 'academy-certificates' ) . '<input type="checkbox" class="checkbox" style="margin-top:4px;" name="' . esc_attr( $field['id'] ) . '_font_style_o" id="' . esc_attr( $field['id'] ) . '_font_style_o" value="yes" ';
					checked( false !== strpos( $font_style_value, 'O' ), true );
					echo ' /> </label>';

				}

				echo '</div>';
		echo '</div>';
	}

	public function add_tooltip($text){
		if($text){
			return '<div class="academyc-tooltip"><span class="dashicons dashicons-info-outline"></span><span class="academyc-tooltiptext">'.esc_html($text).'</span></div>';
		}
		return '';
	}


	/**
	 * Add inline javascript to activate the farbtastic color picker element.
	 * Must be called in order to use the certificate_templates_wp_color_picker() method.
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 2.0.4
	 */
	public function certificate_templates_wp_color_picker_js() {
		_deprecated_function( __FUNCTION__, '2.0.4' );
		ob_start();
		?>
		$(".colorpick").wpColorPicker();

		$(document).mousedown(function(e) {
			if ($(e.target).hasParent(".wp-picker-holder"))
				return;
			if ($( e.target ).hasParent("mark"))
				return;
			$(".wp-picker-holder").each(function() {
				$(this).fadeOut();
			});
		});
		<?php
		$javascript = ob_get_clean();

	}


	/**
	 * Renders a custom admin control used on the certificate edit page to Set/Remove
	 * the position via two buttons.
	 *
	 * @since 1.0.0
	 */
	public function certificate_templates_wp_position_picker( $field ) {

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}
		$tooltip = $this->add_tooltip(!empty($field['description']) ? esc_html( $field['description'] ) : '');

		echo '<div class="academyc-position-wrap">
			<label class="form-field__label">' . esc_html( $field['label'] ) . $tooltip . '</label>
			<div class="form-field__control">
				<input type="button" id="' . esc_attr( $field['id'] ) . '" class="set_position button" value="' . esc_attr__( 'Set Position', 'academy-certificates' ) . '" style="width:auto;" /> 
				<input type="button" id="remove_' . esc_attr( $field['id'] ) . '" class="remove_position button" value="' . esc_attr__( 'Remove Position', 'academy-certificates' ) . '" style="width:auto;' . ( $field['value'] ? '' : 'display:none' ) . ';" />
			</div>';
		echo '</div>';
	}


	/**
	 * Output a text input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_text_input( $field ) {

		global $post;

		$thepostid              = $post->ID;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';

		// Custom attribute handling.
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		$tooltip = $this->add_tooltip(wp_kses_post( ! empty( $field['description'] ) ? $field['description'] : ''));

		echo '<div class="'.esc_attr($field['wrapper_class']).'"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . $tooltip . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . esc_attr( implode( ' ', $custom_attributes ) ) . ' /> </div>';
	}


	/**
	 * Output a hidden input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_hidden_input( $field ) {

		global $thepostid, $post;

		$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['value'] = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['class'] = isset( $field['class'] ) ? $field['class'] : '';

		echo '<input type="hidden" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" /> ';
	}


	/**
	 * Output a textarea input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_textarea_input( $field ) {

		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );

		$tooltip = $this->add_tooltip(wp_kses_post( ! empty( $field['description'] ) ? $field['description'] : ''));

		echo '<div class="academyc-textarea-wrap"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . $tooltip . '</label><textarea class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="6" cols="20">' . esc_textarea( $field['value'] ) . '</textarea> </div>';
	}


	/**
	 * Output a checkbox input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_checkbox( $field ) {

		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';

		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . ' /> ';

		if ( ! empty( $field['description'] ) ) {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

		echo '</p>';
	}


	/**
	 * Output a select input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_select( $field ) {

		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );

		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';

		foreach ( $field['options'] as $key => $value ) {

			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';

		}

		echo '</select> ';

		echo '</p>';
	}

	/**
	 * Output a radio input box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array $field
	 * @return void
	 */
	public function certificates_wp_radio( $field ) {

		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );

		echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend><ul>';

		if ( ! empty( $field['description'] ) ) {

			echo '<li class="description">' . wp_kses_post( $field['description'] ) . '</li>';

		}

		foreach ( $field['options'] as $key => $value ) {

			echo '<li><label><input
					name="' . esc_attr( $field['id'] ) . '"
					value="' . esc_attr( $key ) . '"
					type="radio"
					class="' . esc_attr( $field['class'] ) . '"
					' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
					/> ' . esc_html( $value ) . '</label>
			</li>';

		}

		echo '</ul></fieldset>';
	}

	public function get_field_position($field_name){
		return isset( $this->certificate_template_fields[ $field_name ]['position'] ) ? $this->certificate_template_fields[ $field_name ]['position'] : array();
	}

	public function certificate_templates_process_meta( $post_id, $post ) {
		if (
			empty( $_POST['certificates_meta_nonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Leave nonce value unmodified.
			|| ! wp_verify_nonce( wp_unslash( $_POST['certificates_meta_nonce'] ), 'certificates_save_data' )
		) {
			return;
		}
	
		$font_color  = ! empty( $_POST['_certificate_font_color'] ) ? sanitize_text_field( wp_unslash( $_POST['_certificate_font_color'] ) ) : '#000000'; // Provide a default.
		$font_size   = ! empty( $_POST['_certificate_font_size'] ) ? intval( $_POST['_certificate_font_size'] ) : 11; // Provide a default.
		$font_family = ! empty( $_POST['_certificate_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['_certificate_font_family'] ) ) : '';
	
		// Certificate template font defaults.
		update_post_meta( $post_id, '_certificate_font_color', $font_color );
		update_post_meta( $post_id, '_certificate_font_size', $font_size );
		update_post_meta( $post_id, '_certificate_font_family', $font_family );
		update_post_meta(
			$post_id,
			'_certificate_font_style',
			( isset( $_POST['_certificate_font_style_b'] ) && 'yes' == $_POST['_certificate_font_style_b'] ? 'B' : '' ) .
															( isset( $_POST['_certificate_font_style_i'] ) && 'yes' == $_POST['_certificate_font_style_i'] ? 'I' : '' ) .
															( isset( $_POST['_certificate_font_style_c'] ) && 'yes' == $_POST['_certificate_font_style_c'] ? 'C' : '' ) .
															( isset( $_POST['_certificate_font_style_o'] ) && 'yes' == $_POST['_certificate_font_style_o'] ? 'O' : '' )
		);
	
		// Original sizes: default 11, product name 16, sku 8.
		// Create the certificate template fields data structure.
		$fields = array();
		foreach ( array( '_certificate_heading', '_certificate_message', '_certificate_course', '_certificate_completion', '_certificate_place' ) as $i => $field_name ) {
	
			// Set the field defaults.
			$field = array(
				'type'     => 'property',
				'font'     => array(
					'family' => '',
					'size'   => '',
					'style'  => '',
					'color'  => '',
				),
				'position' => array(),
				'order'    => $i,
			);
	
			// Get the field position (if set).
			if ( ! empty( $_POST[ $field_name . '_pos' ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized after the explode in map with intval.
				$position = explode( ',', wp_unslash( $_POST[ $field_name . '_pos' ] ) );
				$position = array_map( 'intval', $position );
	
				$field['position'] = array(
					'x1'     => $position[0],
					'y1'     => $position[1],
					'width'  => $position[2],
					'height' => $position[3],
				);
			}
	
			if ( ! empty( $_POST[ $field_name . '_text' ] ) ) {
				$field['text'] = sanitize_textarea_field( wp_unslash( $_POST[ $field_name . '_text' ] ) );
			}
	
			// Get the field font settings (if any).
			if ( ! empty( $_POST[ $field_name . '_font_family' ] ) ) {
				$field['font']['family'] = sanitize_text_field( wp_unslash( $_POST[ $field_name . '_font_family' ] ) );
			}
			if ( ! empty( $_POST[ $field_name . '_font_size' ] ) ) {
				$field['font']['size'] = intval( $_POST[ $field_name . '_font_size' ] );
			}
			if ( isset( $_POST[ $field_name . '_font_style_b' ] ) ) {
				$field['font']['style'] = 'B';
			}
			if ( isset( $_POST[ $field_name . '_font_style_i' ] ) ) {
				$field['font']['style'] .= 'I';
			}
			if ( isset( $_POST[ $field_name . '_font_style_c' ] ) ) {
				$field['font']['style'] .= 'C';
			}
			if ( isset( $_POST[ $field_name . '_font_style_o' ] ) ) {
				$field['font']['style'] .= 'O';
			}
			if ( isset( $_POST[ $field_name . '_font_color' ] ) ) {
				$field['font']['color'] = sanitize_text_field( wp_unslash( $_POST[ $field_name . '_font_color' ] ) );
			}
	
			// Cut off the leading '_' to create the field name.
			$fields[ ltrim( $field_name, '_' ) ] = $field;
	
		}
		update_post_meta( $post_id, '_certificate_template_fields', $fields );
	}

	public function certificate_template_process_images_meta( $post_id, $post ) {
		if (
			empty( $_POST['certificates_meta_nonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Leave nonce value unmodified.
			|| ! wp_verify_nonce( wp_unslash( $_POST['certificates_meta_nonce'] ), 'certificates_save_data' )
			|| empty( $_POST['upload_image_id'] )
		) {
			return;
		}
	
		// Handle the image_ids meta, which will always have at least an index 0 for the main template image, even if the value is empty.
		$image_ids       = array();
		$upload_image_id = array_map( 'intval', wp_unslash( $_POST['upload_image_id'] ) );
	
		foreach ( $upload_image_id as $i => $image_id ) {
	
			if ( 0 == $i || $image_id ) {
				$image_ids[] = $image_id !== 0 ? $image_id : '';
			}
		}
	
		update_post_meta( $post_id, '_image_ids', $image_ids );
	
		if ( $image_ids[0] ) {
			set_post_thumbnail( $post_id, $image_ids[0] );
		} else {
			delete_post_thumbnail( $post_id );
		}
	}

}
