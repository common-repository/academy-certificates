<?php
namespace AcademyCertificates\Certificate;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class to manage the tFPDF library.
 */
class ConfigTFPDF {
	/**
	 * Create and return the tFPDF object.
	 *
	 * @param string $orientation
	 * @param string $units
	 * @param string $size
	 *
	 * @return tFPDF\PDF
	 */
	public static function get_tfpdf_object( $orientation, $units, $size ) {
		// Include the pdf library if needed.
		require_once ACADEMY_CERTIFICATES_INCLUDES_DIR_PATH . '/library/tfpdf/tFPDF/PDF.php';
		require_once ACADEMY_CERTIFICATES_INCLUDES_DIR_PATH . '/library/tfpdf/tFPDF/TTFontFile.php';

		return new WrapperTFPDF( $orientation, $units, $size );
	}

	/**
	 * Get the PDF from the tFPDF object and send it to the HTTP client. Note
	 * that this will set headers and echo to stdout.
	 *
	 * @param \tFPDF\PDF $tfpdf    The tFPDF object.
	 * @param string     $filename The filename to send in the HTTP headers.
	 */
	public static function output_to_http( $tfpdf, $filename ) {
		header( 'Content-Type: application/pdf' );
		header( "Content-Disposition: inline; filename=\"$filename\"" );
		header( 'Cache-Control: private, max-age=0, must-revalidate' );
		header( 'Pragma: public' );

		echo $tfpdf->output();
	}
}
