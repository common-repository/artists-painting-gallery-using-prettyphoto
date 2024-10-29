<?php
/*
Plugin Name: Artists Painting Gallery using PrettyPhoto
Plugin URI: http://withsquirrelly.com/plugins/artwork-gallery/
Description: Gallery using PrettyPhoto, for use with Artwork when you would like to add the date, medium, dimensions and associated with a particular page in the Media Gallery. 
Author: withSquirrelly
Author URI: http://withSquirrelly.com/
Tags: 
Version: 1.1
*/

/*  Copyright 2013  withSquirrelly  (email : plugins@withsquirrelly.com) */

add_action( 'init', 'Artwork_lightbox_Gallery_init_functions' );

function Artwork_lightbox_Gallery_init_functions() {
	
	/* Define constants */
	define( 'ARTWORKGALLERY_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	define( 'ARTWORKGALLERY_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'ARTWORKGALLERY_VERSION', '1.0' );
	define( 'HOOK', 'rel' );
	define( 'GALLERY', 'prettyPhoto' );
	
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'prettyPhoto', ARTWORKGALLERY_URI . 'prettyPhoto_compressed_3.1.5/css/prettyPhoto.css' );
	wp_enqueue_script( 'prettyPhoto', ARTWORKGALLERY_URI . 'prettyPhoto_compressed_3.1.5/js/jquery.prettyPhoto.js' );
	wp_enqueue_style( 'prettyphoto', ARTWORKGALLERY_URI . 'css/style.css', false, '3.1.4', 'screen' );
	
	add_action( 'wp_head', 'Artwork_Gallery_header_script', 80 );
	add_filter( 'the_content', 'Artwork_Create_Gallery', 90 );
	add_action( 'wp_footer', 'Artwork_Gallery_footer_script', 100 );
	
}

function Artwork_Gallery_header_script() {
	
	$page = get_the_title();
	
	$out = '<script>' . "\n";
	$out .= 'jQuery(function($) {' . "\n";
	$out .= '$(' . '\'' . '.entry-content a' . '\'' . ').has(' . '\'' . 'img' . '\'' . ').attr(' . '\'' . HOOK . '\'' . ', ' . '\'' . GALLERY . '[' . $page . ']\'' . ');'. "\n";
	$out .= '});' . "\n";
	$out .= '</script>' . "\n";

	echo $out;

}

function Artwork_Gallery_footer_script() 
{
	$settings = array(
		'animation_speed' => 'normal'
	);
	
	$PreOut = '<script>' . "\n";
	$PreOut .= 'jQuery(function($) {' . "\n";
	$PreOut .= '$(\'a[' . HOOK . '^="' . GALLERY . '"]\').prettyPhoto(';
	$PreOut .= '{ ';
	
	$PostOut .= '});' . "\n";;
	$PostOut .= '});' . "\n";
	$PostOut .= '</script>' . "\n";

	echo $PreOut;
	foreach($settings as $key => $value)
	{
		echo $key.": '". $value . "', ";
	}
	echo $PostOut;
}


/*Add Date, Medium nad Dimensions to media uploader*/
 
function be_attachment_field_credit( $form_fields, $post ) {
	$form_fields['artwork-date'] = array(
		'label' => 'Date',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'artwork_date', true ),
		'helps' => 'The date of the project',
	);
	$form_fields['artwork-medium'] = array(
		'label' => 'Medium',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'artwork_medium', true ),
		'helps' => 'Medium of the artwork',
	);
	$form_fields['artwork-dimensions'] = array(
		'label' => 'Dimensions',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'artwork_dimensions', true ),
		'helps' => 'The dimensions, format: height vs. width',
	);
	
	// Set up options
	$args = array(
		      'exclude' => '',
		      'sort_order' => 'ASC',
		      );
	// 	$args = array( 'exclude' => '9,22,7', );
	$pages = get_pages($args);
	
	// Get currently selected value
	$selected = get_post_meta( $post->ID, 'which_gallery', true );
	
	// If no selected value, default to 'No'
	if( !isset( $selected ) ) 
		$selected = '0';
	
	// Display each option	
	foreach ( $pages as $page ) {
		$checked = '';
		$css_id = 'which-gallery-option-' . $page->ID;
 
		if ( $selected == $page->ID ) {
			$checked = " checked='checked'";
		}
		$html = "<div class='which-gallery-option'><input type='radio' name='attachments[$post->ID][which-gallery]' id='{$css_id}' value='{$page->ID}'$checked />";
		$html .= "<label for='{$css_id}'> ";
		$html .= $page->post_title;
		$html .= "</label>";
		$html .= "</div>";
		// Display the default none option	
 		if ($page === end($pages)){
			$html .= "<div class='which-gallery-option'><input type='radio' name='attachments[$post->ID][which-gallery]' id='which-gallery-option-0' value='0' ";
			if ( $selected == '0' ) {
				$html .= "checked='checked'";	
			}
			$html .= "/>";
			$html .= "<label for='which-gallery-option-0'> ";
			$html .= "Do not display on any pages";
			$html .= "</label>";
			$html .= "</div>";
		}
		$out[] = $html;
	}
 
	// Construct the form field
	$form_fields['which-gallery'] = array(
		'label' => 'Which page you want this image on',
		'input' => 'html',
		'html'  => join("\n", $out),
	);
	
	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

/*Save values of Date, Medium and Dimensions in media uploader*/

function be_attachment_field_credit_save( $post, $attachment ) {
	if( isset( $attachment['artwork-date'] ) )
		update_post_meta( $post['ID'], 'artwork_date', $attachment['artwork-date'] );
	if( isset( $attachment['artwork-medium'] ) )
		update_post_meta( $post['ID'], 'artwork_medium', $attachment['artwork-medium'] );
	if( isset( $attachment['artwork-dimensions'] ) )
		update_post_meta( $post['ID'], 'artwork_dimensions', $attachment['artwork-dimensions'] );
	if( isset( $attachment['which-gallery'] ) ) 
		update_post_meta( $post['ID'], 'which_gallery', $attachment['which-gallery'] );
	return $post;
}

add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );

/*Displays gallery in content window based on page selection*/

function Artwork_Create_Gallery() {
	
	$content = get_the_content();
	// do your transformation here
	$content = '<div>'.$content.'</div>';
	echo do_shortcode($content);

	$postid = get_the_ID();
	$args = array( 'post_type' => 'attachment', 'orderby' => 'menu_order', 'order' => 'ASC', 'post_mime_type' => 'image' ,'post_status' => null, 'numberposts' => null, 'post_parent' => $post->ID );
	$page = get_the_title();
	
	$attachments = get_posts($args);
	if ($attachments) {
		echo '<div id="ArtistGallery">';
		foreach ( $attachments as $attachment ) {
			$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
			$thumb_src = wp_get_attachment_thumb_url($attachment->ID); 
			$image_src = wp_get_attachment_url($attachment->ID, 'medium'); 
			$image_title = $attachment->post_title;
			$artwork_date = $attachment->artwork_date;
			$artwork_medium = $attachment->artwork_medium;
			$artwork_dimensions = $attachment->artwork_dimensions;
			$which_gallery = $attachment->which_gallery;
			$caption = $attachment->post_excerpt;
			$description = $image->post_content;
			if ($which_gallery == $postid) {
				echo '<dl class="gallery-item"><dt>';
				echo '<a href="' . $image_src .'" title="';
				if ($artwork_date) {
					echo '<span>Date: ' . $artwork_date . '</span>';
				}
				if ($artwork_medium) {
					echo ' <span>Medium: ' . $artwork_medium . '</span>';
				}
				if ($artwork_dimensions) {
					echo ' <span>Size: ' . $artwork_dimensions . '</span>';
				}
				echo '">';
				echo '<img src="' . $thumb_src . '" alt="' . $alt . '" />';
				echo '</a>';
				echo '<dd class="wp-caption-text gallery-caption">' . $image_title . '</dd>';
				echo '</dt></dl>';
				}
			}
		echo '<br style="clear: both;"></div>';
		}
        }
?>