<?php
/*
Plugin Name: Simplest Gallery
Version: 3.3
Plugin URI: http://www.simplestgallery.com/
Description: The simplest way to integrate Wordpress' builtin Photo Galleries into your pages with a nice jQuery fancybox effect
Author: Cristiano Leoni
Author URI: http://www.linkedin.com/pub/cristiano-leoni/2/b53/34

# This file is UTF-8 - These are accented Italian letters àèìòù

*/

/*

    History
   + 3.3 2014-08-20	Fixes to tune-up the priority of filters so that any post text filters should run first and not break the javascript code
   			Fixes to replace also the default WP jquery-migrate code in case the user chooses to use Simplest Gallery's bundled one
   			Fixes to allow better language support (new strings added, English .po template file added, IT translation updated)
   + 3.2 2014-08-12	Fixed problem in Lightbox WITH/WITHOUT labels. Now you see labels or you don't according to the chosen style
   			Bundled all necessary jquery scripts with the plugin (no more loading from CDN) for offline use
   + 3.1 2014-05-28	Added translations file for Serbian and Spanish - Thanks to Ogi Djuraskovic - http://firstsiteguide.com/
   + 3.0 2014-04-05	Small fix for Admin Bar display
   + 2.9 2014-04-05	Replaced Fancybox library with to FancyBox 1.3.4 (http://fancybox.net/) which is Licensed under both MIT and GPL licenses
   + 2.8 2013-11-04	Fix for language support - Fix for admin bar disappearence problem (Thanks Mike Hegy)
   + 2.7 2013-10-27	Fixed and tested for WP 3.7 - Added support for user-set columns - Fixed notices with WP_DEBUG set
   + 2.6 2013-10-15	Improved support towards earlier versions of jQuery via the migration jQuery plugin
   + 2.5 2013-09-18	Improved support for addon styles (added $gallery_id parameter for rendering function API syntax). Support & fix for jQuery 1.10.2 (Thanks Ian Byrne)
   + 2.4 2013-09-12	Added settings box in page/post edit screen for selecting the desired gallery type and more settings. 
   			Support for multiple galleries in the same page/post.
   			Extended the SimplestGallery API to support rendering of more than one gallery per page (gallery_id and post_id parameters)
   			Auto-setup for fresh WP installs (uses jQuery bundled with Simplest Gallery)
   + 2.3 2013-08-28	Optimized code for speed. Bug fix: Plugin did not work for WP gallery setting different from Link to: Attachment Page - now fixed.
   + 2.2 2013-08-28	Bug fix in fbg-init.js. Added setting to force WP to use the correct version of jQuery - fixed compatibility issues with WP 3.6
   + 2.1 2013-07-21	Added folders to the distribution (language support and more stuff) 
   + 2.0 2013-07-21	Replaced included fancybox library to FancyBox 2.1.5 by Janis Skarnelis - http://fancyapps.com/fancybox/ in order to fix IE10 compatibility issues for default gallery style
   + 1.3 2013-04-29	Added API support for external modules: More gallery formats can now be easily added with custom made plugins. 
   			Added support for gallery_type custom field for using different gallery types on different posts/pages
   + 1.2 2013-04-16	Added possibility to select from a list of gallery types (for the moment: with/without labels).Multi-language support
   + 1.1 2013-04-01	Replaced standard Lightbox with Lightbox 1.2.1 by Janis Skarnelis available under MIT License http://en.wikipedia.org/wiki/MIT_License
   + 1.0 2013-03-28	First working version
*/

// CONFIG
$sga_version = '3.3';
$sga_gallery_types = array(
				'lightbox'=>'FancyBox without labels',
				'lightbox_labeled'=>'FancyBox WITH labels',
				// new types will be added soon...
			);
$sga_compat_types = array(
				'specific'=>'Use Gallery Specific jQuery',
				'trust_wp'=>'Use WP\'s default jQuery',
			);

$sga_gallery_params = array();

add_filter('the_content', 'sga_contentfilter',10); // This value should allow other text-formatting filters to run first
add_action('wp_head', 'sga_head',1);
add_action('wp_footer', 'sga_footer');

if(is_admin()){
	// load localisation files
	load_plugin_textdomain('simplest-gallery',FALSE,dirname( plugin_basename( __FILE__ ) ) . '/lang/');

	add_action('admin_menu', 'sga_admin_menu');
	add_action('admin_init', 'sga_admin_init');	

	// Add settings link on plugin page
	function sga_settings_link($links) { 
	  $settings_link = '<a href="options-general.php?page=SimplestGallery">'.__('Settings').'</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}

	$plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$plugin", 'sga_settings_link' );

	add_action('edit_post', 'sga_meta_box_save');
	add_action('publish_post', 'sga_meta_box_save');
	add_action('save_post', 'sga_meta_box_save');
	add_action('edit_page_form', 'sga_meta_box_save');
}

register_activation_hook( __FILE__, 'sga_activate' );

//add_action("template_redirect", "sga_outside_init"); // UNUSED
//add_action('init', 'sga_init'); // UNUSED

// Uncomment the following to disable the admin bar
//add_filter( 'show_admin_bar', '__return_false' );

// Plugin functions

function sga_init() {
}

function sga_admin_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('SimplestGallery', 'Simplest Gallery', 'administrator', 'SimplestGallery', 'sga_settings_page');
	}
	
	if( function_exists( 'add_meta_box' )) {
	    add_meta_box( 'simplest-gallery', 'Simplest Gallery',  'sga_meta_box', 'post', 'advanced', 'high' );
	    add_meta_box( 'simplest-gallery', 'Simplest Gallery',  'sga_meta_box', 'page', 'advanced', 'high' );
	   } else {
	    add_action('dbx_post_advanced', 'sga_meta_box' );
	    add_action('dbx_page_advanced', 'sga_meta_box' );
	  }
	 
}

function sga_admin_init() {
	register_setting('sga_options', 'sga_options', 'sga_options_validate');
		
        add_settings_section('sga_main',__('Main Settings','simplest-gallery'),'sga_section_text','simplest-gallery');	
	add_settings_field('sga_settings', __('Gallery style','simplest-gallery'), 'sga_settings_html', 'simplest-gallery', 'sga_main');	
	add_settings_field('sga_settings_compat', __('Compatibility with jQuery','simplest-gallery'), 'sga_settings_compat_html', 'simplest-gallery', 'sga_main');	
}

function sga_settings_page() {
	$urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2><?php _e('Simplest Gallery Settings','simplest-gallery') ?></h2>	
	    
	    <table width="100%"><tr>
	    
        <td style="vertical-align:top">
        
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields('sga_options');	
		    do_settings_sections('simplest-gallery');
		?>
	        <?php submit_button(); ?>
	    </form>
	    
   		</td>
        <td style="vertical-align:top; width:410px">

       
        <div class="metabox-holder">

		<div class="postbox" >

			<h3 style="font-size:24px; text-transform:uppercase;color:#00C;"><?php _e('Need help?','simplest-gallery');?> <?php _e('More styles?','simplest-gallery');?></h3>
			<div class="inside">

				<p>
				<?php _e('Check out the','simplest-gallery'); ?> <a href="http://www.simplestgallery.com/support/" target="_blank"><?php _e('Simplest Gallery Website','simplest-gallery'); ?></a><br/><br/>
				<a href="http://www.simplestgallery.com/support/" target="_blank"><img width="400" src="<?php echo $urlpath?>/images/banner.png" ></a>
				</p>
			</div>
 		</div>
        </div>       

        <div class="metabox-holder">

		<div class="postbox" >

        

        	<h3 style="font-size:24px; text-transform:uppercase;color:#00C;">

        	<?php _e('Take a Look!','simplest-gallery');?>

            </h3>

            

             <h3><?php _e('Professional Wordpress Themes','simplest-gallery')?>: <a href="http://www.simplestgallery.com/aff_elegantthemes" target="_blank">Elegant Themes</a></h3>

             

        	<div class="inside">

                <p>

                <a href="http://www.simplestgallery.com/aff_elegantthemes" target="_blank"><img src="<?php echo $urlpath?>/images/elegantthemes.jpg" ></a>

				</p>



			</div>

 
 		</div>
        </div>
        
         <div class="metabox-holder">

		<div class="postbox" >           

            <h3><?php _e('Affordable Multi-domain Professional Hosting','simplest-gallery')?>: <a href="http://www.simplestgallery.com/aff_bluehost" target="_blank">BlueHost</a></h3>

            

        	<div class="inside">

                <p>

                <a href="http://www.simplestgallery.com/aff_bluehost" target="_blank"><img width="400" src="<?php echo $urlpath?>/images/bluehost.png"></a>

				</p>



			</div> 

                        

		</div>

        </div>

              

       </td>
       </tr>
       </table>
       
	</div>
<?php 
}

function sga_section_text() {
	echo '<p>'.__('Determine how the galleries will look like on your website','simplest-gallery').'.</p>';
}

function sga_settings_html() {
	global $sga_gallery_types,$sga_options;
	
	sga_get_options();
	
	$typedef = isset($sga_options['sga_gallery_type'])?$sga_options['sga_gallery_type']:NULL;
	
?>
<select id="sga_gallery_type" name="sga_options[sga_gallery_type]">
<?php
	foreach ($sga_gallery_types as $key=>$val) {
		echo '<option value="'.$key.'" '.(($typedef==$key)?'selected="selected"':'').'>'.__($val,'simplest-gallery').'</option>'."\n";
	}
?>
</select><div style="width:500px; display: block;"><p>
<?php _e('The above selected style will be applied to all galleries, but you can change the setting in individual page/posts.','simplest-gallery'); ?>
</p></div>
<?php
}

function sga_settings_compat_html() {
	global $sga_compat_types,$sga_options;
	
	sga_get_options();
	
	$typedef = $sga_options['sga_gallery_compat'];
	if (!$typedef) $typedef='trust_wp';
	
?>
<select id="sga_gallery_compat" name="sga_options[sga_gallery_compat]">
<?php
	foreach ($sga_compat_types as $key=>$val) {
		echo '<option value="'.$key.'" '.(($typedef==$key)?'selected="selected"':'').'>'.__($val,'simplest-gallery').'</option>'."\n";
	}
?>
</select><div style="width:500px; display: block;"><p>
<?php _e('This setting is used in case of jQuery conflicts between the theme you are using and specific gallery types.','simplest-gallery'); ?></p>
<p>"<em><?php _e('Use WP\'s default jQuery','simplest-gallery'); ?></em>" <?php _e('is the least intrusive method for your website.','simplest-gallery'); ?></p>
<p><?php _e('If galleries don\'t display correctly you can try using','simplest-gallery'); ?> "<em><?php _e('Use Gallery Specific jQuery','simplest-gallery'); ?></em>" <?php _e('which forces WP to use the required jQuery version for the galleries. In that case, please check your site still displays correctly: if not just revert to the default setting or upgrade your WP theme.','simplest-gallery'); ?>
</p></div>
<?php
}

function sga_options_validate($input) {
	global $sga_gallery_types,$sga_compat_types;
	
	//print_r($input); //exit;
	
	if ($sga_gallery_types[$input['sga_gallery_type']]) {
		$newinput['sga_gallery_type'] = $input['sga_gallery_type'];
	} else {
		//echo "Not exists";
	}
	
	if ($sga_compat_types[$input['sga_gallery_compat']]) {
		$newinput['sga_gallery_compat'] = $input['sga_gallery_compat'];
	} else {
		//echo "Not exists";
	}
	//print_r($newinput,true); //exit;
	return $newinput;
}

function sga_contentfilter($content = '') {
	global $sga_gallery_types,$post,$sga_options,$sga_gallery_params;
	$post_id = $post->ID; 
	$gallid = $post->ID; 

	if (!(strpos($content,'[gallery')===FALSE)) {
		$howmany = preg_match_all('/\[gallery(\s+columns="[^"]*")?(\s+link="[^"]*")?\s+ids="([^"]*)"\]/',$content,$arrmatches);
		//echo "Post ID: $post_id - res: $res - Matches:".print_r($arrmatches,true);exit;

		if (!($gallery_type=get_post_meta($post_id, 'gallery_type', true))) { // Post/page's specific setting may override site-wide
			sga_get_options();
			$gallery_type = isset($sga_options['sga_gallery_type'])?$sga_options['sga_gallery_type']:NULL;
		}
		
		for ($gallid=0; $gallid<$howmany; $gallid++) {

			$gall = '';	// Reset gallery buffer
			//$gall = "gallery type: $gallery_type<br/>\n";
			
			$res = preg_match('/\s*columns="([0-9]+)"/',$arrmatches[1][$gallid],$arrcolmatch);
			$columns = 3;
			if (isset($arrcolmatch[1]) && intval($arrcolmatch[1])) $columns = intval($arrcolmatch[1]);
			
			$ids=$arrmatches[3][$gallid]; // gallery images IDs are here now

			$images = sga_gallery_images('large',$ids);
			$thumbs = sga_gallery_images('thumbnail',$ids);
			
			// Safety check: if there are not settings for selected gallery type, just switch back to lightbox
			if (!in_array($gallery_type,array('lightbox','lightbox_labeled')) && !is_array($sga_gallery_params[$gallery_type])) $gallery_type='lightbox';

			if (count($images)) {

				switch ($gallery_type) {
				case 'lightbox':
				case 'lightbox_labeled':
				case '':
					$gall .= '
	<style type="text/css">
					#gallery-'.$gallid.' {
						margin: auto;
					}
					#gallery-'.$gallid.' .gallery-item {
						float: left;
						margin-top: 10px;
						text-align: center;
						width: '.intval(98/$columns).'%;
					}
					#gallery-'.$gallid.' img {
						border: 2px solid #cfcfcf;
					}
					#gallery-'.$gallid.' .gallery-caption {
						margin-left: 0;
					}
	</style>';


					$gall .= '
<script type="text/javascript">
$(document).ready(function() {
	/*
	 *  Simple image gallery. Uses default settings
	 */

	$(".fancybox").fancybox({
		"transitionIn"		: "none",
		"transitionOut"		: "none",
		"titlePosition" 	: "over"';

					if ($gallery_type == 'lightbox_labeled') // Add labels
		$gall .= '
		,
		"titleFormat"		: function(title, currentArray, currentIndex, currentOpts) {
			return "<span id=\'fancybox-title-over\'>'.__('Image','simplest-gallery').' "+(currentIndex+1)+" / " + currentArray.length + (title.length ? " &nbsp; " + title : "") + "</span>";
		}';
		$gall .= '
		
	});	
});
</script>
';
	
	$gall .= '
	<div id="gallery-'.$gallid.'" class="gallery galleryid-'.$gallid.' gallery-size-thumbnail">';

					for ($i=0;$i<count($thumbs);$i++) {
						$thumb = $thumbs[$i];
						$image = $images[$i];
						$gall .= '<dl class="gallery-item"><dt class="gallery-icon">
						<a class="fancybox" href="'.$image[0].'"'.(($gallery_type == 'lightbox_labeled')?' title="'.$thumb[5].'"':'').' rel="gallery-'.$gallid.'"><img width="'.$thumb[1].'" height="'.$thumb[2].'" class="attachment-thumbnail" src="'.$thumb[0].'" /></a></dt>';
						if ($gallery_type == 'lightbox_labeled') {	// Add labels
							$gall .= '<dd class="wp-caption-text gallery-caption">'.$thumb[5].'</dd>';
						}
						$gall .= '</dl>'."\n\n"; // title="'.print_r($thumb,true).'" 
					}

					$gall .= '</div><br clear="all" />';
				break;
				default:
					if (isset($sga_gallery_params[$gallery_type]) && ($hfunct = $sga_gallery_params[$gallery_type]['render_function'])) {
						if (function_exists($hfunct)) {
							if ($res = call_user_func($hfunct,$images,$thumbs,$post_id,$gallid)) { // If WP triggers an error here, you have an outdated addon plugin. A new param has been added in Simplest Gallery 2.5
								$gall .= "<!-- Rendered by {$sga_gallery_types[$gallery_type]} BEGIN -->\n";
								$gall .= $res;
								$gall .= "<!-- Rendered by {$sga_gallery_types[$gallery_type]} END -->\n";
							}
						}
					}
				} // Closes SWITCH

				$content = str_replace($arrmatches[0][$gallid],$gall,$content);
			} else {
				$gall .= 'Gallery is empty!';
				$content = str_replace($arrmatches[0][$gallid],$gall,$content);
			}		
		} // Foreach loop on galleries
	}

	return $content;
}

function sga_gallery_images($size = 'large',$ids) {
	global $post;

	$galleryimages = array();
	
	if ($ids) {
		$arrids = explode(',',$ids);
		if (is_array($arrids)) {
			foreach ($arrids as $id) {
				//$attimg   = wp_get_attachment_url($id,$size); // Anche _image va
				$attimg   = wp_get_attachment_image_src($id,$size,FALSE); // Anche _image va
				$attimg[] = $id; // slot 4 holds ID
				$attimg[] = get_post_field('post_excerpt', $id); // slot 5 holds caption
				$galleryimages[] = $attimg;
				// echo "<li>$id -  $attimg</li>\n";
			}
		}
	}

	return $galleryimages;
}


// Da usare per PHP4 just in case
function sga_strrpos(  $haystack, $needle, $offset = 0  ) {
        if(  !is_string( $needle )  )$needle = chr(  intval( $needle )  );
        if(  $offset < 0  ){
            $temp_cut = strrev(  substr( $haystack, 0, abs($offset) )  );
        }
        else{
            $temp_cut = strrev(    substr(   $haystack, 0, max(  ( strlen($haystack) - $offset ), 0  )   )    );
        }
        if(   (  $found = strpos( $temp_cut, strrev($needle) )  ) === FALSE   )return FALSE;
        $pos = (   strlen(  $haystack  ) - (  $found + $offset + strlen( $needle )  )   );
        return $pos;
}



function sga_head() {
	global $sga_gallery_types,$sga_options,$sga_gallery_params,$sga_version;
    
	$urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
?>
<!-- Added by Simplest Gallery Plugin v. <?=$sga_version?> BEGIN -->
<?php

	sga_get_options('CHECK');
	$gallery_type = isset($sga_options['sga_gallery_type'])?$sga_options['sga_gallery_type']:NULL;
	
	//echo "<!-- galltype: $gallery_type ".print_r($sga_gallery_types,true).print_r($sga_gallery_params,true)." -->\n";

	switch ($gallery_type) {
	case 'lightbox':
	case 'lightbox_labeled':
	case '':
		if ($sga_options['sga_gallery_compat']=='specific') {
			wp_deregister_script('jquery'); // Force WP to use my desired jQuery version
			wp_deregister_script('jquery.migrate'); 
			wp_register_script('jquery', $urlpath . '/lib/jquery-1.10.2.min.js', false, '1.10.2');
		} else {
			wp_enqueue_script('jquery', $urlpath . '/lib/jquery-1.10.2.min.js', false, '1.10.2');
		}

		wp_enqueue_script('jquery.migrate', $urlpath . '/lib/jquery-migrate-1.2.1.min.js', array('jquery'), '1.2.1'); // Helps migrating from earlier versions of jQuery
		wp_enqueue_script('jquery.mousewheel', $urlpath . '/lib/jquery.mousewheel-3.0.6.pack.js', array('jquery'), '3.0.6');
		wp_enqueue_script('fancybox', $urlpath . '/fancybox/jquery.fancybox-1.3.4.js', array('jquery'), '1.3.4');

		//wp_enqueue_script('fancybox-init', $urlpath . '/fbg-init.js', array('fancybox'), '1.3.4', true);
		wp_enqueue_style('fancybox', $urlpath . '/fancybox/jquery.fancybox-1.3.4.css');
		wp_enqueue_style('fancybox-override', $urlpath . '/fbg-override.css');
	break;
	default:
		// Include Scripts
		$arr = NULL;
		if (isset($sga_gallery_params[$gallery_type]) && ($arr = $sga_gallery_params[$gallery_type]['scripts'])) {
			if (is_array($arr) && count($arr)) {
				foreach ($arr as $k=>$v) {
					if (is_array($v)) {
						if ($k=='jquery') {	
							if ($sga_options['sga_gallery_compat']=='specific') {
								wp_deregister_script('jquery'); // Force WP to use my desired jQuery version
								wp_deregister_script('jquery.migrate'); 
								wp_register_script('jquery', $v[0], $v[1], $v[2]);
							} else {
								wp_enqueue_script($k, $v[0], $v[1], $v[2]);
							}
							wp_enqueue_script('jquery.migrate', $urlpath . '/lib/jquery-migrate-1.2.1.min.js', array('jquery'), '1.2.1'); // Helps migrating from earlier versions of jQuery
						} else {			
							wp_enqueue_script($k, $v[0], $v[1], $v[2]);
						}
					} else {
						echo "<!-- error: script item is not an array -->\n"; 
					}
				}
			} else {
				echo "<!-- error: scripts is not an array -->\n"; 
			}
		}

		// Include CSSs		
		if ($arr = $sga_gallery_params[$gallery_type]['css']) {
			if (is_array($arr) && count($arr)) {
				foreach ($arr as $k=>$v) {
					wp_enqueue_style($k, $v);
				}
			}
		}
	} // Switch
			
	if (isset($sga_gallery_params[$gallery_type]) && ($hfunct = $sga_gallery_params[$gallery_type]['header_function'])) {
		if (function_exists($hfunct)) {
			if ($res = call_user_func($hfunct)) {
				echo "<!-- Added by {$sga_gallery_types[$gallery_type]} BEGIN -->\n";
				echo $res;
				echo "<!-- Added by {$sga_gallery_types[$gallery_type]} END -->\n";
			}
		}
	}
?>
<!-- Added by Simplest Gallery Plugin END -->
<?php

}

function sga_footer() {
	// Empty so far - might be used in future versions
}

// Optimized code: gets plugin options only when called the first time
// $check_post_fields: defaults to FALSE. Set to TRUE if you would like to inspect the current posts' custom fields for gallery_type selection
function sga_get_options($check_post_fields=FALSE) {
	global $sga_options,$sga_gallery_types,$post;
	
	if (!is_array($sga_options)) {
		$sga_options = get_option('sga_options');
	}

	if ($check_post_fields && $post) {
		// If custom field 'gallery_type' is used, pick it to select gallery type
		if (($forced_type = get_post_meta($post->ID, 'gallery_type', true)) && $sga_gallery_types[$forced_type]) {
			$sga_options['sga_gallery_type'] = $forced_type;
		}
	}	
}

function sga_register_gallery_type($gallery_type_id,$gallery_type_name,$render_function,$header_function,$scripts_array,$css_array,$params_array=NULL) {
	global $sga_gallery_types,$sga_gallery_params;
	
	if (!$gallery_type_id || !$gallery_type_name) return FALSE;

	$sga_gallery_types[$gallery_type_id] = $gallery_type_name;

	$paramsarr = array();
	
	if ($render_function) {
		$paramsarr['render_function']=$render_function;
	}
	if ($header_function) {
		$paramsarr['header_function']=$header_function;
	}
	if ($scripts_array && is_array($scripts_array)) {
		$paramsarr['scripts']=$scripts_array;
	}
	if ($css_array && is_array($css_array)) {
		$paramsarr['css']=$css_array;
	}
	if ($params_array && is_array($params_array)) {
		$paramsarr['params']=$params_array;
	}
	
	$sga_gallery_params[$gallery_type_id] = $paramsarr;
	
	return TRUE;
}

function sga_meta_box() {
	global $post,$wp_version,$sga_gallery_types;
	$post_id = $post;
	if (is_object($post_id)) {
	    	$post_id = $post_id->ID;
	}

	$gall_type = stripcslashes(get_post_meta($post_id, 'gallery_type', true));
	$gall_width = stripcslashes(get_post_meta($post_id, 'gall_width', true));
	$gall_height = stripcslashes(get_post_meta($post_id, 'gall_height', true));
		
	?>		
		<?php if ((substr($wp_version, 0, 3) < '2.5')) { ?>
		<div class="dbx-b-ox-wrapper">
		<fieldset id="sga" class="dbx-box">
		<div class="dbx-h-andle-wrapper">
		<h3 class="dbx-handle">Simplest Gallery</h3>
		</div>
		<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content">
		<?php } ?>

		<div style="width:200px;float:right;background:#ffffaa;padding:20px;"><?php _e('If galleries don\'t work, then change the compatibility setting','simplest-gallery') ?> <a target="_blank" href="options-general.php?page=SimplestGallery"><?php _e('here','simplest-gallery') ?></a>. <?php _e('Need help?','simplest-gallery') ?> <a target="_blank" href="http://www.simplestgallery.com/support/"><?php _e('Click here for Support', 'simplest-gallery') ?></a></div>

		<input value="sga_edit" type="hidden" name="sga_edit" />
		<table style="margin-bottom:40px">
		<tr>
		<th style="text-align:left;" colspan="2">
		</th>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Gallery style','simplest-gallery') ?></th>
		<td>
		<select id="sga_gallery_type" name="sga_gallery_type">
		<?php
			$chosen = false;
			foreach ($sga_gallery_types as $key=>$val) {
				echo '<option value="'.$key.'" '.(($gall_type==$key)?'selected="selected"':'').'>'.__($val,'simplest-gallery').'</option>'."\n";
				$chosen=$chosen||($gall_type==$key);
			}
			echo '<option '.((!$chosen)?'selected="selected"':'').' value="">(default)</option>';
		?>
		
		</select>
		</td>
		</tr>
		
		
		<tr>
		<th scope="row" style="text-align:left;"><?php _e('Gallery size', 'simplest-gallery') ?>:</th>
		<td>
		<?php _e('Width', 'simplest-gallery') ?> <input value="<?php echo $gall_width ?>" type="text" name="sga_gall_width" size="4" />
		<?php _e('Height', 'simplest-gallery') ?> <input value="<?php echo $gall_height ?>" type="text" name="sga_gall_height" size="4" /> (<?php _e('In pixels, a few gallery formats use them. If unsure leave blank', 'simplest-gallery') ?>)
		</td>
		</tr>
		
		</table>		
		<input type="hidden" name="sga_meta_nonce" value="<?php echo wp_create_nonce('sga_meta_nonce') ?>" />

		<?php if ((substr($wp_version, 0, 3) < '2.5')) { ?>
		</div>
		</fieldset>
		</div>
		<?php } ?>
		<?php  
}

function sga_meta_box_save($id) {
	$sga_edit = isset($_POST["sga_edit"])?$_POST["sga_edit"]:NULL;
	$nonce = isset($_POST['sga_meta_nonce'])?$_POST['sga_meta_nonce']:NULL;
	if (isset($sga_edit) && !empty($sga_edit) && wp_verify_nonce($nonce, 'sga_meta_nonce')) {
		foreach (array('sga_gallery_type','sga_gall_width','sga_gall_height') as $k) {
			$setting = str_replace('sga_','',$k);
			$v = $_POST[$k];
			delete_post_meta($id, $setting);
			if ($v) {
				add_post_meta($id, $setting, $v);
			}
		}

	}

}

function sga_activate() {
	global $sga_options;
	
    // Activation code here...
	sga_get_options();
	
	if (!is_array($sga_options)) {
		// On fresh installs use our Gallery-specific jQuery
		add_option('sga_options',array('sga_gallery_compat'=>'specific'));
	}

}

?>