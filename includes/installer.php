<?php
namespace AcademyCertificates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Installer {
	public $academyc_version;
	public static function init() {
		$self = new self();
		$self->academy_version = get_option( 'academyc_version' );
		$self->create_initial_certificate();
		$self->save_option();
	}

	public function create_initial_certificate(){
		if ( get_option( 'academyc_version' ) ) {
			return;
		}
		$Template = new Admin\ExampleCertificate();
		$Template->create_certificate_template();
	}

	public function save_option() {
		if ( ! $this->academyc_version ) {
			add_option( 'academyc_version', ACADEMY_CERTIFICATES_VERSION );
		}
		update_option( 'academyc_required_rewrite_flush', true );
	}
}
