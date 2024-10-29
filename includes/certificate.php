<?php
namespace AcademyCertificates;

use AcademyCertificates\Certificate\ConfigTFPDF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Certificate {

	public static function init() {
		$self = new self();
        add_filter( 'single_template', array( $self, 'certificate_templates_locate_preview_template' ) );
	}

    /**
	 * Locate the certificate template preview template file, in this plugin's templates directory
	 *
	 * @access public
	 * @since 1.0
	 * @param string $locate locate path
	 * @return string the location path for the certificate template preview file
	 */
    public function certificate_templates_locate_preview_template($locate ){
        $post_type = get_query_var( 'post_type' );
		
		if ( 'academy_certificate' == $post_type ) {
			$locate = ACADEMY_CERTIFICATES_ROOT_DIR_PATH . '/templates/single-academy_certificate.php';
		}

		return $locate;
    }


	/**
	 * Returns font settings for the certificate template
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function get_certificate_font_settings( $field_key = '' ) {

		$return_array = array();

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['color'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['color'] ) {
			$return_array['font_color'] = $this->certificate_template_fields[ $field_key ]['font']['color'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['family'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['family'] ) {
			$return_array['font_family'] = $this->certificate_template_fields[ $field_key ]['font']['family'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['style'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['style'] ) {
			$return_array['font_style'] = $this->certificate_template_fields[ $field_key ]['font']['style'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['size'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['size'] ) {
			$return_array['font_size'] = $this->certificate_template_fields[ $field_key ]['font']['size'];
		}

		return $return_array;

	}


	/**
	 * Gets the certificate template image id: the selected image id if this is a certificate template
	 * otherwise the certificate template primary image id
	 *
	 * @access public
	 * @since 1.0.0
	 * @return int certificate template image id
	 */
	public function get_image_id() {		
		global $post;
		if ( $post && isset( $post->ID ) && 0 < $post->ID ) {
			$image_ids = get_post_meta( $post->ID, '_image_ids', true );
			if($image_ids && is_array($image_ids) && !empty($image_ids[0])){
				return $image_ids[0];
			}
			return false;
		}
		// otherwise return the template primary image id
		return false;
	}


    /**
	 * Generate and save or stream a PDF file
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return mixed nothing if a $path is supplied, otherwise a PDF download
	 */
	public function generate_pdf() {

		global $current_user, $post;

		$image    = wp_get_attachment_metadata( $this->get_image_id() );
		$image_id = $this->get_image_id();
		if(! $image_id){
			return;
		}
		
		// determine orientation: landscape or portrait
		if ( $image['width'] > $image['height'] ) {
			$orientation = 'L';
		} else {
			$orientation = 'P';
		}

		// Create the pdf
		// TODO: we're assuming a standard DPI here of where 1 point = 1/72 inch = 1 pixel
		// When writing text to a Cell, the text is vertically-aligned in the middle
		$fpdf = ConfigTFPDF::get_tfpdf_object(
			$orientation, 'pt', array( $image['width'], $image['height'] )
		);

		$fpdf->AddPage();
		$fpdf->SetAutoPageBreak( false );

		// Add custom font
		$custom_font = apply_filters( 'sensei_certificates_custom_font', false );
		if ( $custom_font ) {
			if ( isset( $custom_font['family'] ) && isset( $custom_font['file'] ) ) {
				$fpdf->AddFont( $custom_font['family'], '', $custom_font['file'], true );
			}
		} else {
			// Add multibyte font
			$fpdf->AddFont( 'DejaVu', '', 'DejaVuSansCondensed.ttf', true );
		}

		// set the certificate image
		$fpdf->Image( get_attached_file( $this->get_image_id() ), 0, 0, $image['width'], $image['height'] );

		// this is useful for displaying the text cell borders when debugging the PDF layout,
		// though keep in mind that we translate the box position to align the text to bottom
		// edge of what the user selected, so if you want to see the originally selected box,
		// display that prior to the translation
		$show_border = 0;

		// Get Student Data
		wp_get_current_user();
		$fname        = $current_user->first_name;
		$lname        = $current_user->last_name;
		$student_name = $current_user->display_name;

		if ( '' != $fname && '' != $lname ) {
			$student_name = $fname . ' ' . $lname;
		}

		// Get Course Data
		$course               = array();
		$course['post_title'] = __( 'Course Title', 'academy-certificates' );
		$course_end_date      = date( 'Y-m-d' );

		// Get the certificate template
		$certificate_template_custom_fields = get_post_custom( $post->ID );

		// Define the data we're going to load: Key => Default value
		$load_data = array(
			'certificate_font_style'      => array(),
			'certificate_font_color'      => array(),
			'certificate_font_size'       => array(),
			'certificate_font_family'     => array(),
			'image_ids'                   => array(),
			'certificate_template_fields' => array(),
		);

		// Load the data from the custom fields
		foreach ( $load_data as $key => $default ) {

			// set value from db (unserialized if needed) or use default
			$this->$key = ( isset( $certificate_template_custom_fields[ '_' . $key ][0] ) && '' !== $certificate_template_custom_fields[ '_' . $key ][0] ) ? ( is_array( $default ) ? maybe_unserialize( $certificate_template_custom_fields[ '_' . $key ][0] ) : $certificate_template_custom_fields[ '_' . $key ][0] ) : $default;

		}

		$date = Helper::get_certificate_formatted_date( $course_end_date );

		$certificate_heading = __( 'Certificate of Completion', 'academy-certificates' ); // Certificate of Completion
		if ( isset( $this->certificate_template_fields['certificate_heading']['text'] ) && '' != $this->certificate_template_fields['certificate_heading']['text'] ) {

			$certificate_heading = $this->certificate_template_fields['certificate_heading']['text'];
			$certificate_heading = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course['post_title'], $date, get_bloginfo( 'name' ) ), $certificate_heading );

		}

		$certificate_message = __( 'This is to certify that', 'academy-certificates' ) . " \r\n\r\n" . $student_name . " \r\n\r\n" . __( 'has completed the course', 'academy-certificates' ); // This is to certify that {{learner}} has completed the course
		if ( isset( $this->certificate_template_fields['certificate_message']['text'] ) && '' != $this->certificate_template_fields['certificate_message']['text'] ) {

			$certificate_message = $this->certificate_template_fields['certificate_message']['text'];
			$certificate_message = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course['post_title'], $date, get_bloginfo( 'name' ) ), $certificate_message );

		}

		$certificate_course = $course['post_title']; // {{course_title}}
		if ( isset( $this->certificate_template_fields['certificate_course']['text'] ) && '' != $this->certificate_template_fields['certificate_course']['text'] ) {

			$certificate_course = $this->certificate_template_fields['certificate_course']['text'];
			$certificate_course = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course['post_title'], $date, get_bloginfo( 'name' ) ), $certificate_course );

		}

		$certificate_completion = $date; // {{completion_date}}
		if ( isset( $this->certificate_template_fields['certificate_completion']['text'] ) && '' != $this->certificate_template_fields['certificate_completion']['text'] ) {

			$certificate_completion = $this->certificate_template_fields['certificate_completion']['text'];
			$certificate_completion = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course['post_title'], $date, get_bloginfo( 'name' ) ), $certificate_completion );

		}

		/* translators: %s is replaced with the site title */
		$certificate_place = sprintf( __( 'At %s', 'academy-certificates' ), get_bloginfo( 'name' ) ); // At {{course_place}}
		if ( isset( $this->certificate_template_fields['certificate_place']['text'] ) && '' != $this->certificate_template_fields['certificate_place']['text'] ) {

			$certificate_place = $this->certificate_template_fields['certificate_place']['text'];
			$certificate_place = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course['post_title'], $date, get_bloginfo( 'name' ) ), $certificate_place );

		}

		$output_fields = array(
			'certificate_heading'    => 'text_field',
			'certificate_message'    => 'textarea_field',
			'certificate_course'     => 'text_field',
			'certificate_completion' => 'text_field',
			'certificate_place'      => 'text_field',
		);

		foreach ( $output_fields as $meta_key => $function_name ) {

			// Check if the field has a set position
			if ( isset( $this->certificate_template_fields[ $meta_key ]['position']['x1'] ) ) {

				$font_settings = $this->get_certificate_font_settings( $meta_key );

				call_user_func_array( array( $this, $function_name ), array( $fpdf, $$meta_key, $show_border, array( $this->certificate_template_fields[ $meta_key ]['position']['x1'], $this->certificate_template_fields[ $meta_key ]['position']['y1'], $this->certificate_template_fields[ $meta_key ]['position']['width'], $this->certificate_template_fields[ $meta_key ]['position']['height'] ), $font_settings ) );

			}
		}

		// download file
		ConfigTFPDF::output_to_http(
			$fpdf, 'certificate-preview-' . $post->ID . '.pdf'
		);

	}
	


	/**
	 * Render a multi-line text field to the PDF
	 *
	 * @access public
	 * @since 1.0.0
	 * @param FPDF   $fpdf fpdf library object
	 * @param string $field_name the field name
	 * @param mixed  $value string or int value to display
	 * @param int    $show_border a debugging/helper option to display a border
	 *           around the position for this field
	 */
	public function textarea_field( $fpdf, $value, $show_border, $position, $font = array() ) {

		if ( $value ) {

			if ( empty( $font ) ) {

				$font = array(
					'font_color'  => $this->certificate_font_color,
					'font_family' => $this->certificate_font_family,
					'font_style'  => $this->certificate_font_style,
					'font_size'   => $this->certificate_font_size,
				);

			}

			// Test each font element
			if ( empty( $font['font_color'] ) ) {
				$font['font_color'] = $this->certificate_font_color; }
			if ( empty( $font['font_family'] ) ) {
				$font['font_family'] = $this->certificate_font_family; }
			if ( empty( $font['font_style'] ) ) {
				$font['font_style'] = $this->certificate_font_style; }
			if ( empty( $font['font_size'] ) ) {
				$font['font_size'] = $this->certificate_font_size; }

			// get the field position
			list( $x, $y, $w, $h ) = $position;

			// font color
			$font_color = $this->hex2rgb( $font['font_color'] );
			$fpdf->SetTextColor( $font_color[0], $font_color[1], $font_color[2] );

			// Check for Border and Center align
			$border = 0;
			$center = 'J';
			if ( isset( $font['font_style'] ) && ! empty( $font['font_style'] ) && false !== strpos( $font['font_style'], 'C' ) ) {
				$center             = 'C';
				$font['font_style'] = str_replace( 'C', '', $font['font_style'] );
			}
			if ( isset( $font['font_style'] ) && ! empty( $font['font_style'] ) && false !== strpos( $font['font_style'], 'O' ) ) {
				$border             = 1;
				$font['font_style'] = str_replace( 'O', '', $font['font_style'] );
			}

			$custom_font = $this->set_custom_font( $fpdf, $font );

			// Set the field text styling based on the font type
			$fonttype = '';
			if ( ! $custom_font ) {
				$fonttype = $this->get_font_type( $value );
				switch ( $fonttype ) {
					case 'mb':
						$fpdf->SetFont( 'dejavusanscondensed', '', $font['font_size'] );
						break;
					case 'latin':
						$fpdf->SetFont( $font['font_family'], $font['font_style'], $font['font_size'] );
						break;
					default:
						$fpdf->SetFont( $font['font_family'], $font['font_style'], $font['font_size'] );
						break;
				}
			}

			$fpdf->setXY( $x, $y );

			if ( 0 < $border ) {
				$show_border = 1;
				$fpdf->SetDrawColor( $font_color[0], $font_color[1], $font_color[2] );
			}

			// Decode string based on font type
			if ( 'latin' == $fonttype ) {
				$value = utf8_decode( $value );
			}

			// and write out the value
			$fpdf->Multicell( $w, $font['font_size'], $value, $show_border, $center );

		}

	}

	/**
	 * Render a single-line text field to the PDF
	 *
	 * @access public
	 * @since 1.0.0
	 * @param FPDF   $fpdf fpdf library object
	 * @param string $field_name the field name
	 * @param mixed  $value string or int value to display
	 * @param int    $show_border a debugging/helper option to display a border
	 *           around the position for this field
	 */
	private function text_field( $fpdf, $value, $show_border, $position, $font = array() ) {

		if ( $value ) {

			if ( empty( $font ) ) {

				$font = array(
					'font_color'  => $this->certificate_font_color,
					'font_family' => $this->certificate_font_family,
					'font_style'  => $this->certificate_font_style,
					'font_size'   => $this->certificate_font_size,
				);

			}

			// Test each font element
			if ( empty( $font['font_color'] ) ) {
				$font['font_color'] = $this->certificate_font_color; }
			if ( empty( $font['font_family'] ) ) {
				$font['font_family'] = $this->certificate_font_family; }
			if ( empty( $font['font_style'] ) ) {
				$font['font_style'] = $this->certificate_font_style; }
			if ( empty( $font['font_size'] ) ) {
				$font['font_size'] = $this->certificate_font_size; }

			// get the field position
			list( $x, $y, $w, $h ) = $position;

			// font color
			$font_color = $this->hex2rgb( $font['font_color'] );
			$fpdf->SetTextColor( $font_color[0], $font_color[1], $font_color[2] );

			// Check for Border and Center align
			$border = 0;
			$center = 'J';
			if ( isset( $font['font_style'] ) && ! empty( $font['font_style'] ) && false !== strpos( $font['font_style'], 'C' ) ) {
				$center             = 'C';
				$font['font_style'] = str_replace( 'C', '', $font['font_style'] );
			}
			if ( isset( $font['font_style'] ) && ! empty( $font['font_style'] ) && false !== strpos( $font['font_style'], 'O' ) ) {
				$border             = 1;
				$font['font_style'] = str_replace( 'O', '', $font['font_style'] );
			}

			$custom_font = $this->set_custom_font( $fpdf, $font );

			// Set the field text styling based on the font type
			$fonttype = '';
			if ( ! $custom_font ) {
				$fonttype = $this->get_font_type( $value );
				switch ( $fonttype ) {
					case 'mb':
						$fpdf->SetFont( 'dejavusanscondensed', '', $font['font_size'] );
						break;
					case 'latin':
						$fpdf->SetFont( $font['font_family'], $font['font_style'], $font['font_size'] );
						break;
					default:
						$fpdf->SetFont( $font['font_family'], $font['font_style'], $font['font_size'] );
						break;
				}
			}

			// show a border for debugging purposes
			if ( $show_border ) {
				$fpdf->setXY( $x, $y );
				$fpdf->Cell( $w, $h, '', 1 );
			}

			if ( 0 < $border ) {
				$show_border = 1;
				$fpdf->SetDrawColor( $font_color[0], $font_color[1], $font_color[2] );
			}

			// align the text to the bottom edge of the cell by translating as needed
			$y = $font['font_size'] > $h ? $y - ( $font['font_size'] - $h ) / 2 : $y + ( $h - $font['font_size'] ) / 2;
			$fpdf->setXY( $x, $y );

			// Decode string based on font type
			if ( 'latin' == $fonttype ) {
				$value = utf8_decode( $value );
			}

			// and write out the value
			$fpdf->Cell( $w, $h, $value, $show_border, $position, $center );

		}

	}

	/**
	 * Taxes a hex color code and returns the RGB components in an array
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $hex hex color code, ie #EEEEEE
	 * @return array rgb components, ie array( 'EE', 'EE', 'EE' )
	 */
	private function hex2rgb( $hex ) {

		if ( ! $hex ) {
			return '';
		}

		$hex = str_replace( '#', '', $hex );

		if ( 3 == strlen( $hex ) ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		return array( $r, $g, $b );

	}

	/**
	 * Gets the font type (character set) of a string
	 *
	 * @access private
	 * @since  1.0.4
	 * @param  string $string String to check
	 * @return string         Font type
	 */
	public function get_font_type( $string = '' ) {

		if ( ! $string ) {
			return 'latin';
		}

		if ( mb_strlen( $string ) != strlen( $string ) ) {
			return 'mb';
		}

		return 'latin';

	}

	/**
	 * Set custom font
	 *
	 * @access private
	 * @since  1.0.4
	 * @param  object $fpdf         The FPDF object
	 * @param  array  $default_font The default font
	 * @return boolean              True if the custom font was set
	 */
	public function set_custom_font( $fpdf, $default_font ) {

		$custom_font = apply_filters( 'academy_certificates_custom_font', false );

		if ( $custom_font ) {

			if ( ! isset( $custom_font['family'] ) || ! $custom_font['family'] ) {
				$custom_font['family'] = $default_font['font_family'];
			}

			if ( ! isset( $custom_font['size'] ) || ! $custom_font['size'] ) {
				$custom_font['size'] = $default_font['font_size'];
			}

			$fpdf->SetFont( $custom_font['family'], '', $custom_font['size'] );

			return true;
		}

		return false;
	}

}
