<?php
namespace AcademyCertificates;

use Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Assets {

	public static function init() {
		$self = new self();
		add_action( 'admin_enqueue_scripts', [ $self, 'backend_scripts' ] );
	}

	public function get_backend_scripts_data(){
		global $post;
		
		// Get the primary image dimensions (if any) which are needed for the page script.
		$attachment = [];
		if($post && $post->ID){
			$image_ids  = get_post_meta( $post->ID, '_image_ids', true );
			if ( is_array( $image_ids ) && isset( $image_ids[0] ) && $image_ids[0] ) {
				if ( is_numeric( $image_ids[0] ) ) {
					$attachment = wp_get_attachment_metadata( $image_ids[0] );
				}
			}
		}
		
		return array(
			'_certificate_heading_pos'    => __( 'Heading', 'academy-certificates' ),
			'_certificate_message_pos'    => __( 'Message', 'academy-certificates' ),
			'_certificate_course_pos'     => __( 'Course', 'academy-certificates' ),
			'_certificate_completion_pos' => __( 'Completion Date', 'academy-certificates' ),
			'_certificate_place_pos'      => __( 'Place', 'academy-certificates' ),
			'done_label'                  => __( 'Done', 'academy-certificates' ),
			'set_position_label'          => __( 'Set Position', 'academy-certificates' ),
			'post_id'                     => isset($post->ID) ? $post->ID : null,
			'primary_image_width'         => isset( $attachment['width'] ) && $attachment['width'] ? $attachment['width'] : '0',
			'primary_image_height'        => isset( $attachment['height'] ) && $attachment['height'] ? $attachment['height'] : '0',
		);
	}

	/**
	 * Enqueue Files on Start Plugin
	 *
	 * @param string $hook
	 * @function backend_scripts
	 */
	public function backend_scripts( $hook ) {
		// Get admin screen id.
		$screen = get_current_screen();
		if ( 'academy_certificate' === $screen->post_type ) {
			wp_enqueue_style( 'academy_certificate_templates_admin_styles', ACADEMY_CERTIFICATES_ASSETS_URI . 'css/backend.css' );

			// Color picker script/styles.
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_media();

			// Image area select, for selecting the certificate fields.
			wp_enqueue_script( 'imgareaselect' );
			wp_enqueue_style( 'imgareaselect' );


			// js
			$dependencies = include_once ACADEMY_CERTIFICATES_ASSETS_DIR_PATH . 'js/backend.asset.php';
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_enqueue_script(
				'academy-certificates-admin-scripts',
				ACADEMY_CERTIFICATES_ASSETS_URI . 'js/backend.js',
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_localize_script( 'academy-certificates-admin-scripts', 'AcademyCertificatesGlobal', $this->get_backend_scripts_data() );
		}
	}
}
