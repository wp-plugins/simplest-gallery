<?php
/*
Plugin Name: Simplest Gallery
Version: 1.2
Plugin URI: http://www.sitiweb-bologna.com/risorse/wordpress-simplest-gallery-plugin/
Description: The simplest way to integrate Wordpress' builtin Photo Galleries into your pages with a nice jQuery fancybox effect
Author: Cristiano Leoni
Author URI: http://www.linkedin.com/pub/cristiano-leoni/2/b53/34

# This file is UTF-8 - These are accented Italian letters àèìòù

*/

/*

    History
   + 1.2 2013-04-16	Added possibility to select from a list of gallery types (for the moment: with/without labels)
   + 1.1 2013-04-01	Replaced standard Lightbox with Lightbox 1.2.1 by Janis Skarnelis available under MIT License http://en.wikipedia.org/wiki/MIT_License
   + 1.0 2013-03-28	First working version
*/

// CONFIG
$sga_gallery_types = array(
				'lightbox'=>'FancyBox without labels',
				'lightbox_labeled'=>'FancyBox WITH labels',
				// new types will be added soon...
			);

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
add_filter( 'show_admin_bar', '__return_false' );


// Plugin functions

function sga_init() {
    $urlpath = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

    wp_enqueue_script('fancybox', $urlpath . '/fancybox/jquery.fancybox-1.2.1.js', array('jquery'), '1.2.1');
    wp_enqueue_script('easing', $urlpath . '/fancybox/jquery.easing.1.3.js', array('jquery'), '1.3');
    wp_enqueue_script('fb-init', $urlpath . '/fbg-init.js', array('fancybox'), '1.0.0', true);
    wp_enqueue_style('fancybox', $urlpath . '/fancybox/jquery.fancybox.css');
    wp_enqueue_style('fancybox-override', $urlpath . '/fbg-override.css');
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
	global $sga_gallery_types;
	
	$options = get_option('sga_options');
	
	//print_r($options); //exit;
	
	$typedef = $options['sga_gallery_type'];
	
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
	global $sga_gallery_types,$post;
	
	$gallid = $post->ID; 

	if (!(strpos($content,'[gallery')===FALSE)) {
		$res = preg_match('/\[gallery ids="([^"]*)"\]/',$content,$matches);
		$ids=$matches[1]; // gallery images IDs are here now

		$images = sga_gallery_images('large');
		$thumbs = sga_gallery_images('thumbnail');
		
		if (count($images)) {
		
			$options = get_option('sga_options');
			
			$gallery_type = $options['sga_gallery_type'];
			
			switch ($gallery_type) {
			case 'lightbox_labeled':
			default:
		
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
					<a href="'.$image[0].'" title="'.$thumb[5].'" rel="gallery-'.$gallid.'"><img width="'.$thumb[1].'" height="'.$thumb[2].'" class="attachment-thumbnail" src="'.$thumb[0].'" /></a></dt>';
					if ($gallery_type == 'lightbox_labeled') {	// Add labels
						$gall .= '<dd class="wp-caption-text gallery-caption">'.$thumb[5].'</dd>';
					}
					$gall .= '</dl>'."\n\n"; // title="'.print_r($thumb,true).'" 
				}

				$gall .= '</div><br clear="all" />';
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
?>
<!-- Added by Simplest Gallery Plugin BEGIN -->


<!-- Added by Simplest Gallery Plugin END -->
<?php

}

function sga_footer() {

?>
<!-- Added by Simplest Gallery Plugin -->
<?php

}



?>