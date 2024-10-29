<?php
namespace AcademyCertificates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Frontend {

	public static function init() {
		$self = new self();
		add_action('academy/templates/single_course/enroll_complete_form', [$self, 'download_certificate']);
		add_filter( 'template_include', array( $self, 'load_certificate_template' ), 40 );

	}

	public function download_certificate($is_complete){
		if(!$is_complete) return;
		?>
		<div class="academy-widget-enroll__continue">
			<a class="academy-btn academy-btn--bg-light-purple" href="<?php echo esc_url( add_query_arg( array( 'source' => 'certificate' ), get_the_permalink() ) ); ?>"><?php esc_html_e('Download Certificate', 'academy-certificates') ?></a>
		</div>
		<?php
	}

	public function load_certificate_template($template){
		if ( get_query_var( 'post_type' ) === 'academy_courses' && get_query_var( 'source' ) === 'certificate' ) {
			if ( is_user_logged_in() ) {
				return ACADEMY_CERTIFICATES_ROOT_DIR_PATH . 'templates/download-certificate.php';
			}
		}
		return $template;
	}
}
