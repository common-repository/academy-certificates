<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$Certificate = new AcademyCertificates\Certificate\Download();

if ( $Certificate->get_image_id() ) {
	// stream the example certificate pdf
	$Certificate->generate_pdf();
	exit;
} else {
	wp_die( esc_html__( 'You must set a certificate_template primary image before you can preview', 'academy-certificates' ) );
}
