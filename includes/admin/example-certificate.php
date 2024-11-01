<?php
namespace AcademyCertificates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ExampleCertificate {

    
    function create_certificate_template() {

        // Register Post Data.
        $post                 = array();
        $post['post_status']  = 'private';
        $post['post_type']    = 'academy_certificate';
        $post['post_title']   = __( 'Example Template', 'academy-certificates' );
        $post['post_content'] = '';

        // Create Post.
        $post_id = wp_insert_post( $post );

        $url = ACADEMY_CERTIFICATES_ASSETS_URI . 'images/certificate-nograde.png';
        if ( ! function_exists( 'download_url' ) ) {
            include_once ABSPATH . '/wp-admin/includes/file.php';
        }
        $tmp     = download_url( $url );
        $post_id = $post_id;
        $desc    = __( 'Academy LMS Certificate Template Example', 'academy-certificates' );

        // Set variables for storage.
        // fix file filename for query strings.
        preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches );
        $file_array['name']     = basename( $matches[0] );
        $file_array['tmp_name'] = $tmp;

        // If error storing temporarily, unlink.
        if ( is_wp_error( $tmp ) ) {
            @unlink( $file_array['tmp_name'] );
            $file_array['tmp_name'] = '';
            error_log( 'An error occurred while uploading the image' );
        }

        if ( ! function_exists( 'media_handle_sideload' ) ) {
            include_once ABSPATH . '/wp-admin/includes/image.php';
            include_once ABSPATH . '/wp-admin/includes/media.php';
        }

        // Do the validation and storage stuff.
        $image_id = media_handle_sideload( $file_array, $post_id, $desc );

        // If error storing permanently, unlink.
        if ( is_wp_error( $image_id ) ) {
            @unlink( $file_array['tmp_name'] );
            error_log( 'An error occurred while uploading the image' );
        }

        $src = wp_get_attachment_url( $image_id );

        $defaults = array(
            '_certificate_font_color'             => '#000000',
            '_certificate_font_size'              => '12',
            '_certificate_font_family'            => 'Helvetica',
            '_certificate_font_style'             => '',
            '_certificate_heading'                => '',
            '_certificate_heading_pos'            => '114,11,989,57',
            '_certificate_heading_font_color'     => '#595959',
            '_certificate_heading_font_size'      => '25',
            '_certificate_heading_font_family'    => 'Helvetica',
            '_certificate_heading_font_style'     => 'C',
            '_certificate_heading_text'           => __( 'Certificate of Completion', 'academy-certificates' ),
            '_certificate_message'                => '',
            '_certificate_message_pos'            => '110,306,996,167',
            '_certificate_message_font_color'     => '#000000',
            '_certificate_message_font_size'      => '36',
            '_certificate_message_font_family'    => 'Helvetica',
            '_certificate_message_font_style'     => 'BC',
            '_certificate_message_text'           => __( 'This is to certify that', 'academy-certificates' ) . " \r\n\r\n" . '{{learner}}' . " \r\n\r\n" . __( 'has completed the course', 'academy-certificates' ),
            '_certificate_course'                 => '',
            '_certificate_course_pos'             => '186,88,838,116',
            '_certificate_course_font_color'      => '#000000',
            '_certificate_course_font_size'       => '48',
            '_certificate_course_font_family'     => 'Helvetica',
            '_certificate_course_font_style'      => 'BCO',
            '_certificate_course_text'            => __( '{{course_title}}', 'academy-certificates' ),
            '_certificate_completion'             => '',
            '_certificate_completion_pos'         => '108,599,998,48',
            '_certificate_completion_font_color'  => '#9e9e9e',
            '_certificate_completion_font_size'   => '20',
            '_certificate_completion_font_family' => 'Helvetica',
            '_certificate_completion_font_style'  => 'C',
            '_certificate_completion_text'        => __( '{{completion_date}} at {{course_place}}', 'academy-certificates' ),
            '_certificate_place'                  => '',
            '_certificate_place_pos'              => '',
            '_certificate_place_font_color'       => '#9e9e9e',
            '_certificate_place_font_size'        => '20',
            '_certificate_place_font_family'      => 'Helvetica',
            '_certificate_place_font_style'       => '',
            '_certificate_place_text'             => __( '{{course_place}}', 'academy-certificates' ),
        );

        // Certificate template font defaults.
        update_post_meta( $post_id, '_certificate_font_color', $defaults['_certificate_font_color'] );
        update_post_meta( $post_id, '_certificate_font_size', $defaults['_certificate_font_size'] );
        update_post_meta( $post_id, '_certificate_font_family', $defaults['_certificate_font_family'] );
        update_post_meta( $post_id, '_certificate_font_style', $defaults['_certificate_font_style'] );

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
            if ( $defaults[ $field_name . '_pos' ] ) {
                $position          = explode( ',', $defaults[ $field_name . '_pos' ] );
                $field['position'] = array(
                    'x1'     => $position[0],
                    'y1'     => $position[1],
                    'width'  => $position[2],
                    'height' => $position[3],
                );
            }

            if ( $defaults[ $field_name . '_text' ] ) {
                $field['text'] = $defaults[ $field_name . '_text' ] ? $defaults[ $field_name . '_text' ] : '';
            }

            // Get the field font settings (if any).
            if ( $defaults[ $field_name . '_font_family' ] ) {
                $field['font']['family'] = $defaults[ $field_name . '_font_family' ];
            }
            if ( $defaults[ $field_name . '_font_size' ] ) {
                $field['font']['size'] = $defaults[ $field_name . '_font_size' ];
            }
            if ( $defaults[ $field_name . '_font_style' ] ) {
                $field['font']['style'] = $defaults[ $field_name . '_font_style' ];
            }
            if ( $defaults[ $field_name . '_font_color' ] ) {
                $field['font']['color'] = $defaults[ $field_name . '_font_color' ];
            }

            // Cut off the leading '_' to create the field name.
            $fields[ ltrim( $field_name, '_' ) ] = $field;
        }

        update_post_meta( $post_id, '_certificate_template_fields', $fields );

        // Test attachment upload.
        $image_ids   = array();
        $image_ids[] = $image_id;
        update_post_meta( $post_id, '_image_ids', $image_ids );

        if ( $image_ids[0] ) {
            set_post_thumbnail( $post_id, $image_ids[0] );
        }

        // Set all courses to the default template.
        $query_args['posts_per_page'] = -1;
        $query_args['post_status']    = 'any';
        $query_args['post_type']      = 'course';
        $the_query                    = new \WP_Query( $query_args );

        if ( $the_query->have_posts() ) {

            $count = 0;

            while ( $the_query->have_posts() ) {

                $the_query->the_post();

                update_post_meta( get_the_id(), '_course_certificate_template', $post_id );

            }
        }

        wp_reset_postdata();

        // make it default
        update_option('academyc_default_certificate_id', $post_id);

        if ( 0 < $post_id ) {
            return true;
        } else {
            return false;
        }
    }
}