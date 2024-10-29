<?php
namespace AcademyCertificates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Admin {

	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
	}

	public function dispatch_hooks(){
		add_action( 'admin_init', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'manage_academy_certificate_posts_columns', [ $this, 'manage_columns' ] );
		add_action( 'manage_academy_certificate_posts_custom_column', [ $this, 'columns_content' ], 10, 2 );	
		add_action( 'wp_ajax_academyc_default_certificate', [$this, 'academyc_default_certificate'] );
		add_action( 'admin_notices', array( $this, 'academy_certificate_warning_notice' ) );
	}

	public function flush_rewrite_rules() {
		if ( get_option( 'academyc_required_rewrite_flush' ) ) {
			delete_option( 'academyc_required_rewrite_flush' );
			flush_rewrite_rules();
		}
	}

	public function manage_columns( $columns ) {
		$column_date    = $columns['date'];
		unset( $columns['date'] );
		$columns['setDefault']  = esc_html__( 'Activated Certificate', 'academy-certificates' );
		$columns['background_preview']  = esc_html__( 'Preview', 'academy-certificates' );
		$columns['date']        = esc_html( $column_date );
		return $columns;
	}

	/**
	 * Manage Custom column content
	 *
	 * @param [string] $column_name
	 * @param [int]    $post_id
	 * @return void
	 */
	public function columns_content( $column_name, $post_id ) {
		if( 'setDefault' === $column_name ) {
			$certificate_id = get_option('academyc_default_certificate_id') ;
			$checked = checked( $post_id, $certificate_id, false );
			echo '<label class="academyc-default-certificate">
				<input class="academyc-status" name="academyc-default-certificate" type="radio" value="' . esc_attr( $post_id ) . '" ' . esc_attr( $checked ) . '/>
				<span class="academyc-default-certificate__status"></span>
			</label>';
		} else if('background_preview' === $column_name){
			$image_ids = get_post_meta( $post_id, '_image_ids', true );
			if ( is_array( $image_ids ) && count( $image_ids ) > 0 ) {
				if ( is_numeric( $image_ids[0] ) ) {
					$image_id   = $image_ids[0];
					$image_src  = wp_get_attachment_url( $image_id );
					echo '<img width="300" src="'.esc_url($image_src).'" alt="certificate" />';
				}
			}
		}
	}

	public function academyc_default_certificate(){
		if(current_user_can('manage_options')){
			$ID = (int) (isset($_POST['ID']) ? $_POST['ID'] : '');
			update_option('academyc_default_certificate_id', $ID);
			wp_send_json_success('<strong>Saved!</strong>');
			wp_die();
		}
		wp_send_json_error('Permission Denied');
		wp_die();
	}

	public function academy_certificate_warning_notice() {
		$academy_pro_version = get_option( 'academy_pro_version' );
		
		if ( ! $academy_pro_version ) {
			$link = 'https://academylms.net/docs/how-to-use-academy-certificate-builder/';
			$image = '<a href="' . esc_url( $link ) . '" target="_blank"><img style="max-width: 100%; height: auto;" src="' . esc_url(ACADEMY_CERTIFICATES_ASSETS_URI . 'images/notice-Banner-1.jpg') . '" alt="banner1"></a>';
		} else {
			$link = 'https://academylms.net/docs/how-to-use-academy-certificate-builder/';
			$image = '<a href="' . esc_url( $link ) . '" target="_blank"><img style="max-width: 100%; height: auto;" src="' . esc_url(ACADEMY_CERTIFICATES_ASSETS_URI . 'images/notice-Banner-2.jpg') . '" alt="banner2"></a>';
		}
		
		return printf( '<div class="academy-certificate-notice">%s</div>', $image );
	}
	
}
