<style type="text/css">
    #misc-publishing-actions { display:none; }
    #edit-slug-box { display:none }
    .imgareaselect-outer { cursor: crosshair; }
</style>
<div id="certificate_options" class="panel certificate_templates_options_panel">
        <!-- Default -->
        <div class="academyc-panel academyc-panel--open">
            <div class="academyc-panel__title"><?php esc_html_e('Default Style', 'academy-certificates'); ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'                => '_certificate',
                            'label'             => __( 'Typography', 'academy-certificates' ),
                            'options'           => $default_fonts,
                            'font_size_default' => 12,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'          => '_certificate_font_color',
                            'label'       => __( 'Color', 'academy-certificates' ),
                            'default'     => '#000000',
                            'description' => __( 'The default text color for the certificate.', 'academy-certificates' ),
                            'class'       => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker'
                        )
                    );
                ?>
            </div>
        </div>
        <!-- Heading -->
        <div class="academyc-panel">
            <div class="academyc-panel__title"><?php esc_html_e('Heading Style', 'academy-certificates'); ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_position_picker(
                        array(
                            'id'          => 'certificate_heading_pos',
                            'label'       => __( 'Position', 'academy-certificates' ),
                            'value'       => implode( ',', $this->get_field_position( 'certificate_heading' ) ),
                            'description' => __( 'Optional position of the Certificate Heading', 'academy-certificates' ),
                        )
                    );
                    $this->certificates_wp_hidden_input(
                        array(
                            'id'    => '_certificate_heading_pos',
                            'class' => 'field_pos',
                            'value' => implode( ',', $this->get_field_position( 'certificate_heading' ) ),
                        )
                    );
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'      => '_certificate_heading',
                            'label'   => __( 'Typography', 'academy-certificates' ),
                            'options' => $available_fonts,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'    => '_certificate_heading_font_color',
                            'label' => __( 'Color', 'academy-certificates' ),
                            'value' => isset( $this->certificate_template_fields['certificate_heading']['font']['color'] ) ? $this->certificate_template_fields['certificate_heading']['font']['color'] : '',
                            'class' => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker',
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'wrapper_class' => 'academyc-text-wrap',
                            'class'       => 'medium',
                            'id'          => '_certificate_heading_text',
                            'label'       => __( 'Heading Text', 'academy-certificates' ),
                            'description' => __( 'Text to display in the heading.', 'academy-certificates' ),
                            'placeholder' => __( 'Certificate of Completion', 'academy-certificates' ),
                            'value'       => isset( $this->certificate_template_fields['certificate_heading']['text'] ) ? $this->certificate_template_fields['certificate_heading']['text'] : '',
                        )
                    );
                ?>
            </div>
        </div>
        <!-- Message -->
        <div class="academyc-panel">
            <div class="academyc-panel__title"><?php esc_html_e( 'Message Style', 'academy-certificates' ) ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_position_picker(
                        array(
                            'id'          => 'certificate_message_pos',
                            'label'       => __( 'Position', 'academy-certificates' ),
                            'value'       => implode( ',', $this->get_field_position( 'certificate_message' ) ),
                            'description' => __( 'Optional position of the Certificate Message', 'academy-certificates' ),
                        )
                    );
                    $this->certificates_wp_hidden_input(
                        array(
                            'id'    => '_certificate_message_pos',
                            'class' => 'field_pos',
                            'value' => implode( ',', $this->get_field_position( 'certificate_message' ) ),
                        )
                    );
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'      => '_certificate_message',
                            'label'   => __( 'Typography', 'academy-certificates' ),
                            'options' => $available_fonts,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'    => '_certificate_message_font_color',
                            'label' => __( 'Color', 'academy-certificates' ),
                            'value' => isset( $this->certificate_template_fields['certificate_message']['font']['color'] ) ? $this->certificate_template_fields['certificate_message']['font']['color'] : '',
                            'class' => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker',
                        )
                    );
                    $this->certificates_wp_textarea_input(
                        array(
                            'class'       => 'medium',
                            'wrapper_class' => 'academyc-text-wrap',
                            'id'          => '_certificate_message_text',
                            'label'       => __( 'Message Text', 'academy-certificates' ),
                            'description' => __( 'Text to display in the message area.', 'academy-certificates' ),
                            'placeholder' => __( 'This is to certify that {{learner}} has completed the course', 'academy-certificates' ),
                            'value'       => isset( $this->certificate_template_fields['certificate_message']['text'] ) ? $this->certificate_template_fields['certificate_message']['text'] : '',
                        )
                    );
                ?>
            </div>
        </div>
        <!-- Course -->
        <div class="academyc-panel">
            <div class="academyc-panel__title"><?php esc_html_e('Course Title Style', 'academy-certificates'); ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_position_picker(
                        array(
                            'id'          => 'certificate_course_pos',
                            'label'       => __( 'Position', 'academy-certificates' ),
                            'value'       => implode( ',', $this->get_field_position( 'certificate_course' ) ),
                            'description' => __( 'Optional position of the Course Name', 'academy-certificates' ),
                        )
                    );
                    $this->certificates_wp_hidden_input(
                        array(
                            'id'    => '_certificate_course_pos',
                            'class' => 'field_pos',
                            'value' => implode( ',', $this->get_field_position( 'certificate_course' ) ),
                        )
                    );
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'      => '_certificate_course',
                            'label'   => __( 'Typography', 'academy-certificates' ),
                            'options' => $available_fonts,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'    => '_certificate_course_font_color',
                            'label' => __( 'Color', 'academy-certificates' ),
                            'value' => isset( $this->certificate_template_fields['certificate_course']['font']['color'] ) ? $this->certificate_template_fields['certificate_course']['font']['color'] : '',
                            'class' => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker',
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'class'       => 'medium',
                            'wrapper_class' => 'academyc-text-wrap',
                            'id'          => '_certificate_course_text',
                            'label'       => __( 'Course Title Text', 'academy-certificates' ),
                            'description' => __( 'Text to display in the course area.', 'academy-certificates' ),
                            'placeholder' => __( '{{course_title}}', 'academy-certificates' ),
                            'value'       => isset( $this->certificate_template_fields['certificate_course']['text'] ) ? $this->certificate_template_fields['certificate_course']['text'] : '',
                        )
                    );
                ?>
            </div>
        </div>
        <!-- Complete Position -->
        <div class="academyc-panel">
            <div class="academyc-panel__title"><?php esc_html_e('Course Complete Date Style', 'academy-certificates'); ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_position_picker(
                        array(
                            'id'          => 'certificate_completion_pos',
                            'label'       => __( 'Position', 'academy-certificates' ),
                            'value'       => implode( ',', $this->get_field_position( 'certificate_completion' ) ),
                            'description' => __( 'Optional position of the Course Completion date', 'academy-certificates' ),
                        )
                    );
                    $this->certificates_wp_hidden_input(
                        array(
                            'id'    => '_certificate_completion_pos',
                            'class' => 'field_pos',
                            'value' => implode( ',', $this->get_field_position( 'certificate_completion' ) ),
                        )
                    );
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'      => '_certificate_completion',
                            'label'   => __( 'Typography', 'academy-certificates' ),
                            'options' => $available_fonts,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'    => '_certificate_completion_font_color',
                            'label' => __( 'Color', 'academy-certificates' ),
                            'value' => isset( $this->certificate_template_fields['certificate_completion']['font']['color'] ) ? $this->certificate_template_fields['certificate_completion']['font']['color'] : '',
                            'class' => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker',
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'class'       => 'medium',
                            'wrapper_class' => 'academyc-text-wrap',
                            'id'          => '_certificate_completion_text',
                            'label'       => __( 'Course Complete Date Text', 'academy-certificates' ),
                            'description' => __( 'Text to display in the course completion date area.', 'academy-certificates' ),
                            'placeholder' => __( '{{completion_date}}', 'academy-certificates' ),
                            'value'       => isset( $this->certificate_template_fields['certificate_completion']['text'] ) ? $this->certificate_template_fields['certificate_completion']['text'] : '',
                        )
                    );
                ?>
            </div>
        </div>
        <!-- Place Position -->
        <div class="academyc-panel">
            <div class="academyc-panel__title"><?php esc_html_e('Course Place Style') ?></div>
            <div class="academyc-panel__body">
                <?php 
                    $this->certificate_templates_wp_position_picker(
                        array(
                            'id'          => 'certificate_place_pos',
                            'label'       => __( 'Position', 'academy-certificates' ),
                            'value'       => implode( ',', $this->get_field_position( 'certificate_place' ) ),
                            'description' => __( 'Optional position of the place of Certification.', 'academy-certificates' ),
                        )
                    );
                    $this->certificates_wp_hidden_input(
                        array(
                            'id'    => '_certificate_place_pos',
                            'class' => 'field_pos',
                            'value' => implode( ',', $this->get_field_position( 'certificate_place' ) ),
                        )
                    );
                    $this->certificate_templates_wp_font_select(
                        array(
                            'id'      => '_certificate_place',
                            'label'   => __( 'Typography', 'academy-certificates' ),
                            'options' => $available_fonts,
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'id'    => '_certificate_place_font_color',
                            'label' => __( 'Color', 'academy-certificates' ),
                            'value' => isset( $this->certificate_template_fields['certificate_place']['font']['color'] ) ? $this->certificate_template_fields['certificate_place']['font']['color'] : '',
                            'class' => 'colorpick',
                            'wrapper_class' => 'academyc-color-picker',
                        )
                    );
                    $this->certificates_wp_text_input(
                        array(
                            'class'       => 'medium',
                            'wrapper_class' => 'academyc-text-wrap',
                            'id'          => '_certificate_place_text',
                            'label'       => __( 'Course Place Text', 'academy-certificates' ),
                            'description' => __( 'Text to display in the course place area.', 'academy-certificates' ),
                            'placeholder' => __( '{{course_place}}', 'academy-certificates' ),
                            'value'       => isset( $this->certificate_template_fields['certificate_place']['text'] ) ? $this->certificate_template_fields['certificate_place']['text'] : '',
                        )
                    );
                ?>
            </div>
        </div>
        
    
</div>