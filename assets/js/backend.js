jQuery((function(e){"use strict";var t,i;window.send_to_editor_default=window.send_to_editor;var a=AcademyCertificatesGlobal.primary_image_width,c=AcademyCertificatesGlobal.primary_image_height;function n(){e(".field_pos").each((function(t,i){var c=(i=e(i)).attr("id"),n=e("#field"+c),r=e("#certificate_image_0");if(""!=r.attr("src")){var o=1;a!=r.width()&&(o=r.width()/a);var s=i.val()?i.val().split(",").map((function(e){return parseInt(e)*o})):null;0===n.length&&(e("#certificate_image_wrapper").append('<span id="field'+c+'" class="certificate_field" style="display:none;">'+AcademyCertificatesGlobal[c]+"</span>"),e("#field"+c).click((function(e){l(e.target.id.substr(6))})),n=e("#field"+c)),s?(n.css({left:s[0]+"px",top:s[1]+"px",width:s[2]+"px",height:s[3]+"px"}),n.show()):n.hide()}else n&&n.hide()}))}function l(t){if(e("img#certificate_image_0").attr("src"))if(r(),e("#"+t).val()!=AcademyCertificatesGlobal.done_label){e("#field_"+t).hide();var i=e("#_"+t).val()?e("#_"+t).val().split(",").map((function(e){return parseInt(e)})):[null,null,null,null];e("input.set_position").val(AcademyCertificatesGlobal.set_position_label),e("#"+t).val(AcademyCertificatesGlobal.done_label),e("img#certificate_image_0").imgAreaSelect({show:!0,handles:!0,instance:!0,imageWidth:a,imageHeight:c,x1:i[0],y1:i[1],x2:i[0]+i[2],y2:i[1]+i[3],onSelectEnd:function(i,a){!function(t,i){t&&0!==t.width&&0!==t.height?e("#_"+i).val(t.x1+","+t.y1+","+t.width+","+t.height):l(i),e("#remove_"+i).show()}(a,t)}}),e(document).scrollTop()>e("img#certificate_image_0").offset().top+e("img#certificate_image_0").height()*(2/3)&&e("html, body").animate({scrollTop:e("#title").offset().top},500)}else e("#"+t).val(AcademyCertificatesGlobal.set_position_label)}function r(){e("img#certificate_image_0").imgAreaSelect({remove:!0}),n()}e(document).on("click","#set-certificate-image",(function(n){n.preventDefault(),i=e(this),t||(t=wp.media.frames.file_frame=wp.media({title:"Select an Image",button:{text:"Set Image"},multiple:!1})).on("select",(function(){var n=t.state().get("selection").first().toJSON();a=n.width,c=n.height,"set-certificate-image"==i.attr("id")&&(e("#upload_image_id_0").val(n.id),e("#remove-certificate-image").show(),e("img#certificate_image_0").attr("src",n.url))})),t.open()})),e("#remove-certificate-image").click((function(){return e("#upload_image_id_0").val(""),e("img#certificate_image_0").attr("src",""),e(this).hide(),!1})),e(window).resize((function(){n()})),n(),e("input.set_position").click((function(){l(this.id)})),e("input.remove_position").click((function(){e(this).hide(),e("#_"+this.id.substr(7)).val(""),e("#"+this.id.substr(7)).val(AcademyCertificatesGlobal.set_position_label),r()})),"function"!=typeof jQuery.fn.hasParent&&jQuery.extend(jQuery.fn,{hasParent:function(t){return this.filter((function(){return e(t).find(this).length}))}});const o=e(".colorpick");"function"==typeof jQuery.fn.wpColorPicker&&o.length>0&&(o.wpColorPicker(),e(document).mousedown((function(t){e(t.target).hasParent(".wp-picker-holder")||e(t.target).hasParent("mark")||e(".wp-picker-holder").each((function(){e(this).fadeOut()}))}))),jQuery(".academyc-panel .academyc-panel__title").click((function(e){var t=jQuery(this).parent(".academyc-panel");t.toggleClass("academyc-panel--open"),jQuery(".academyc-panel").not(t).removeClass("academyc-panel--open"),e.preventDefault()})),jQuery(document).click((function(t){t.stopPropagation(),0===e(".academyc-typography").has(t.target).length&&jQuery(".academyc-typography__body").hide()})),jQuery(".academyc-typography > label").on("click",(function(){jQuery(this).siblings(".academyc-typography__body").toggle()})),jQuery('label.academyc-default-certificate input[type="radio"]').on("click",(function(){let e=this;var t={action:"academyc_default_certificate",ID:jQuery(this).prop("value")};jQuery.post(ajaxurl,t,(function(t){jQuery(e).next(".academyc-default-certificate__status").html(t.data),setTimeout((function(){jQuery(e).next(".academyc-default-certificate__status").html("")}),1e3)}))}))}));