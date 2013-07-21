<?php
/*
Plugin Name: Simplest Gallery
Version: 2.1
Plugin URI: http://www.simplestgallery.com/
Description: The simplest way to integrate Wordpress' builtin Photo Galleries into your pages with a nice jQuery fancybox effect
Author: Cristiano Leoni
Author URI: http://www.linkedin.com/pub/cristiano-leoni/2/b53/34

# This file is UTF-8 - These are accented Italian letters àèìòù

*/

/*

    History
   + 2.1 2013-07-21	Added folders to the distribution (language support and more stuff) 
   + 2.0 2013-07-21	Replaced included fancybox library to FancyBox 2.1.5 by Janis Skarnelis - http://fancyapps.com/fancybox/ in order to fix IE10 compatibility issues for default gallery style
   + 1.3 2013-04-29	Added API support for external modules: More gallery formats can now be easily added with custom made plugins. 
   			Added support for gallery_type custom field for using different gallery types on different posts/pages
   + 1.2 2013-04-16	Added possibility to select from a list of gallery types (for the moment: with/without labels).Multi-language support
   + 1.1 2013-04-01	Replaced standard Lightbox with Lightbox 1.2.1 by Janis Skarnelis available under MIT License http://en.wikipedia.org/wiki/MIT_License
   + 1.0 2013-03-28	First working version
*/

// CONFIG
$sga_gallery_types = array(
				'lightbox'=>'FancyBox without labels',
				'lightbox_labeled'=>'FancyBox WITH labels',
				// new types will be added soon...
			);

$sga_gallery_params = array();

add_filter('the_content', 'sga_contentfilter');
add_action('wp_head', 'sga_head');
add_action('wp_footer', 'sga_footer');
add_action('init', 'sga_init');

if(is_admin()){
	// load localisation files
	load_plugin_textdomain('simplest-gallery','wp-content/plugins/simplest-gallery/lang');

	add_action('admin_menu', 'sga_admin_menu');
	add_action('admin_init', 'sga_admin_init');	
}

//add_action("template_redirect", "sga_outside_init"); // UNUSED

// Disable the hated admin bar
//add_filter( 'show_admin_bar', '__return_false' );


// Plugin functions

function sga_init() {
	global $sga_gallery_types,$sga_options,$sga_gallery_params;
    
	$urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

	//sga_get_options();
	//$gallery_type = $sga_options['sga_gallery_type'];

	foreach ($sga_gallery_types as $gallery_type=>$name) {
			
		switch ($gallery_type) {
		case 'lightbox':
		case 'lightbox_labeled':
		case '':
			wp_enqueue_script('jquery', $urlpath . '/lib/jquery-1.10.1.min.js', false, '1.10.1');
			wp_enqueue_script('jquery.mousewheel', $urlpath . '/lib/jquery.mousewheel-3.0.6.pack.js', array('jquery'), '3.0.6');
			wp_enqueue_script('fancybox', $urlpath . '/source/jquery.fancybox.js', array('jquery'), '2.1.5', true);
			wp_enqueue_script('fancybox-init', $urlpath . '/fbg-init.js', array('fancybox'), '2.1.5', true);
			wp_enqueue_style('fancybox', $urlpath . '/source/jquery.fancybox.css');
			wp_enqueue_style('fancybox-override', $urlpath . '/fbg-override.css');
		break;
		default:
			// Include Scripts
			if ($arr = $sga_gallery_params[$gallery_type]['scripts']) {
				if (is_array($arr) && count($arr)) {
					foreach ($arr as $k=>$v) {
						if (is_array($v)) {
							wp_enqueue_script($k, $v[0], $v[1], $v[2]);
						}
					}
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
	} // Foreach
}

function sga_admin_menu() {
    if (function_exists('add_options_page')) {
        add_options_page('SimplestGallery', 'Simplest Gallery', 'administrator', 'SimplestGallery', 'sga_settings_page');
    }
}

function sga_admin_init() {
	register_setting('sga_options', 'sga_options', 'sga_options_validate');
		
        add_settings_section('sga_main',__('Main Settings','simplest-gallery'),'sga_section_text','simplest-gallery');	
	add_settings_field('sga_settings', __('Gallery format','simplest-gallery'), 'sga_settings_html', 'simplest-gallery', 'sga_main');	
}

function sga_settings_page() {
?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2><? _e('Simplest Gallery Settings','simplest-gallery') ?></h2>			
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields('sga_options');	
		    do_settings_sections('simplest-gallery');
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
<?php 
}

function sga_section_text() {
	echo '<p>'.__('Choose how the galleries will look like on your website','simplest-gallery').'.</p>';
}

function sga_settings_html() {
	global $sga_gallery_types,$sga_options;
	
	sga_get_options();
	
	$typedef = $sga_options['sga_gallery_type'];
	
?>
<select id="sga_gallery_type" name="sga_options[sga_gallery_type]">
<?php
	foreach ($sga_gallery_types as $key=>$val) {
		echo '<option value="'.$key.'" '.(($typedef==$key)?'selected="selected"':'').'>'.$val.'</option>'."\n";
	}
?>
</select>
<?php
}

function sga_options_validate($input) {
	global $sga_gallery_types;
	
	//print_r($input); //exit;
	
	if ($sga_gallery_types[$input['sga_gallery_type']]) {
		$newinput['sga_gallery_type'] = $input['sga_gallery_type'];
	} else {
		//echo "Not exists";
	}
	
	//print_r($newinput,true); //exit;
	return $newinput;
}

function sga_contentfilter($content = '') {
	global $sga_gallery_types,$post,$sga_options,$sga_gallery_params;
	
	$gallid = $post->ID; 

	if (!(strpos($content,'[gallery')===FALSE)) {
		$res = preg_match('/\[gallery ids="([^"]*)"\]/',$content,$matches);
		$ids=$matches[1]; // gallery images IDs are here now

		$images = sga_gallery_images('large');
		$thumbs = sga_gallery_images('thumbnail');
		
		if (count($images)) {
		
			sga_get_options();
			
			$gallery_type = $sga_options['sga_gallery_type'];
			
			switch ($gallery_type) {
			case 'lightbox':
			case 'lightbox_labeled':
			case '':
		
				$gall = '
<style type="text/css">
				#gallery-1 {
					margin: auto;
				}
				#gallery-1 .gallery-item {
					float: left;
					margin-top: 10px;
					text-align: center;
					width: 33%;
				}
				#gallery-1 img {
					border: 2px solid #cfcfcf;
				}
				#gallery-1 .gallery-caption {
					margin-left: 0;
				}
</style>
<div id="gallery-1" class="gallery galleryid-'.$gallid.' gallery-columns-3 gallery-size-thumbnail">';
		
				for ($i=0;$i<count($thumbs);$i++) {
					$thumb = $thumbs[$i];
					$image = $images[$i];
					$gall .= '<dl class="gallery-item"><dt class="gallery-icon">
					<a class="fancybox" href="'.$image[0].'" title="'.$thumb[5].'" rel="gallery-'.$gallid.'"><img width="'.$thumb[1].'" height="'.$thumb[2].'" class="attachment-thumbnail" src="'.$thumb[0].'" /></a></dt>';
					if ($gallery_type == 'lightbox_labeled') {	// Add labels
						$gall .= '<dd class="wp-caption-text gallery-caption">'.$thumb[5].'</dd>';
					}
					$gall .= '</dl>'."\n\n"; // title="'.print_r($thumb,true).'" 
				}

				$gall .= '</div><br clear="all" />';
			break;
			default:
				if ($hfunct = $sga_gallery_params[$gallery_type]['render_function']) {
					if (function_exists($hfunct)) {
						if ($res = call_user_func($hfunct,$images,$thumbs)) {
							$gall = "<!-- Rendered by {$sga_gallery_types[$gallery_type]} BEGIN -->\n";
							$gall .= $res;
							$gall .= "<!-- Rendered by {$sga_gallery_types[$gallery_type]} END -->\n";
						}
					}
				}
			} // Closes SWITCH

			$content = str_replace($matches[0],$gall,$content);
		}		
		
	}

	return $content;
}

function sga_gallery_images($size = 'large') {
	global $post;

	$galleryimages = array();
	
	$text = get_the_content();
	
	//echo $text; 
	//echo "<br>res:$res<br>matches: ".print_r($matches,true); exit;
	
	$res = preg_match('/\[gallery ids="([^"]*)"\]/',$text,$matches);
	$ids=$matches[1];
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
	global $sga_gallery_types,$sga_options,$sga_gallery_params;
    
?>
<!-- Added by Simplest Gallery Plugin BEGIN -->
<?php
	sga_get_options('CHECK');
	$gallery_type = $sga_options['sga_gallery_type'];
			
	if ($hfunct = $sga_gallery_params[$gallery_type]['header_function']) {
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

?>
<!-- Added by Simplest Gallery Plugin -->
<?php

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

function sga_register_gallery_type($gallery_type_id,$gallery_type_name,$render_function,$header_function,$scripts_array,$css_array) {
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
	
	$sga_gallery_params[$gallery_type_id] = $paramsarr;
	
	return TRUE;
}


?>