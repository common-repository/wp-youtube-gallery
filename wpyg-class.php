<?php 
/** Plugin created by raghunath gurjar 
 * Create a new youtube post type
 * define the all options for new post type
 * @init
 * @register_post_type
 * @add_meta_boxes
 * */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action('init','add_wp_youtube_post_type');
if(!function_exists('add_wp_youtube_post_type')){ 
function add_wp_youtube_post_type()
{
	$labels = array(
	   'name'=>__('WP Youtube Gallery'),
	   'singular_name'=>__('Video'),
	   'all_items'=>__('All Videos'),
	   'add_new'=>__('Add New Video'),
	   'edit_item'         => __( 'Edit Video' ),
	   'update_item'       => __( 'Update Video' ),
	   'add_new_item'      => __( 'Add New Video' ),);
	
	register_post_type('wp_youtube_gallery',
	array(
	 'labels'=>$labels,
	 'public'=>true,
	 'has_archive'=>false,
	 'supports'=>array('title','editor','page-attributes'),
	 'publicly_queryable' =>false,
	 'rewrite' =>false,
	    )
	);
}
}

/*
 * Add New Meta Box Field For WP Youtube Gallery
 * define all meta boxes that will be publish on WP Youtube Gallery posts 
 * */
//define action for create new meta boxes
add_action( 'add_meta_boxes', 'add_wp_youtube_gallery_meta_box' );
/**
 * Adds the WP Youtube Gallery meta box
 */
if(!function_exists('add_wp_youtube_gallery_meta_box')){ 
function add_wp_youtube_gallery_meta_box()
{
 global $wpyg_meta_box;
	$screens = array( 'wp_youtube_gallery');
	foreach ( $screens as $screen ) {

		add_meta_box(
			'youtube-meta-box',
			__( 'Youtube Information', 'wpyg_textdomain' ),
			'show_wp_youtube_gallery_meta_box',
			$screen
		);
	}

}
}

//Define meta box fields

  $wpyg_prefix = 'wpyg_';
    $wpyg_meta_box = array(
    'id' => 'youtube-meta-box',
    'title' => 'Extra Information',
    'page' => '',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(array(
    'name' => 'Youtube Video ID: ',
    'desc' => 'Example: https://www.youtube.com/watch?v=EKyirtVHsK0 , Here video id is "<b>EKyirtVHsK0</b>"',
    'id' => $wpyg_prefix . 'video-id',
    'type' => 'text',
    'std' => ''
    )
    )
    );


//Display WP Youtube Gallery Meta Box on youtube pages
if(!function_exists('show_wp_youtube_gallery_meta_box')){ 
function show_wp_youtube_gallery_meta_box()
{
global $wpyg_meta_box, $post;
    wp_nonce_field( 'wp_youtube_gallery_box_field', 'wp_youtube_gallery_box_meta_box_once' );

    foreach ($wpyg_meta_box['fields'] as $field) {
    // get current post meta data
   
    $meta = get_post_meta($post->ID, $field['id'], true);
    echo '<p>',
    '<label for="', $field['id'], '">', $field['name'], '</label>','';
    switch ($field['type']) {
    case 'text':
    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />', '<br />', $field['desc'];
    break;
    case 'textarea':
    echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', '<br />', $field['desc'];
    break;
    case 'select':
    echo '<select name="', $field['id'], '" id="', $field['id'], '" >';
    $optionVal=explode(',',$field['options']);
    foreach($optionVal as $optVal):
    if($meta==$optVal){
    $valseleted =' selected="selected"';}else {
		 $valseleted ='';
		}
    echo '<option value="', $optVal, '" ',$valseleted,' id="', $field['id'], '">', $optVal, '</option>';
    endforeach;
    echo '</select>',$field['desc'];
    break;
    '</p>';
    }

    }
}
}

if(!function_exists('wpyg_sanitize_fields')){ 
function wpyg_sanitize_fields($type='',$val='')
{
	// Is this textarea
	if($type='textarea')
	{
	  $val = sanitize_textarea_field($val);
	}else
	{
		$val = sanitize_text_field($val);
	}
	return $val;
  }
}
//Define action for save to "WP Youtube Gallery" Meta Box fields Value
add_action( 'save_post', 'save_wp_youtube_gallery_meta_box' );
if(!function_exists('save_wp_youtube_gallery_meta_box')){ 
function save_wp_youtube_gallery_meta_box($post_id) {
	global $wpyg_meta_box;
	// Check if our nonce is set.
	 if ( ! isset( $_POST['wp_youtube_gallery_box_meta_box_once'] ) ) {
			return;
		}
		
	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	return $post_id;
	}

	// check permissions
	if ('wp_youtube_gallery' == $_POST['post_type']) 
	{
		if (!current_user_can('edit_page', $post_id))
		return $post_id;
	} 
	elseif(!current_user_can('edit_post', $post_id)){
	return $post_id;
	}

	foreach ($wpyg_meta_box['fields'] as $field) 
	{
		$old = get_post_meta($post_id, $field['id'], true);
		$new = wpyg_sanitize_fields($field['type'],$_POST[$field['id']]);
		if ($new && $new != $old){
		 update_post_meta($post_id, $field['id'], $new);
		} 
		elseif ('' == $new && $old) {
		delete_post_meta($post_id, $field['id'], $old);
		}
	}
}
}

/* 
 * Register New "WP Youtube Gallery" Texonimoies
 * @register_taxonomy()
 * 
 * */

// define init action and call create_wp_youtube_gallery_taxonomies when it fires
add_action( 'init', 'create_wp_youtube_gallery_taxonomies', 0 );
if(!function_exists('create_wp_youtube_gallery_taxonomies')){ 
function create_wp_youtube_gallery_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Categories', 'Video Categories' ),
		'singular_name'     => _x( 'Category', 'Video' ),
		'search_items'      => __( 'Search Video Categories' ),
		'all_items'         => __( 'All Video Categories' ),
		'parent_item'       => __( 'Parent Video Category' ),
		'parent_item_colon' => __( 'Parent Video Category:' ),
		'edit_item'         => __( 'Edit Video Category' ),
		'update_item'       => __( 'Update Video Category' ),
		'add_new_item'      => __( 'Add New Video Category' ),
		'new_item_name'     => __( 'New Video Category Name' ),
		'menu_name'         => __( 'Categories' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'            => false,
		'rewrite'           => array( 'slug' => 'youtube-gallery', 'with_front' => true,'hierarchical' => false),
	);

	register_taxonomy( 'wp_youtube_gallery_taxonomy', array( 'wp_youtube_gallery' ), $args );
}
}
/* End Youtube Taxonomies */

/** add css to wp_head */
add_action('wp_enqueue_scripts','add_wpyg_style');
if(!function_exists('add_wpyg_style')){ 
	function add_wpyg_style()
	{
	wp_register_style( 'wpyg_style', plugins_url( 'css/wpyg.min.css',__FILE__ ) );
	wp_enqueue_style( 'wpyg_style' );
	}
}

/*
 * Function for get all youtube pages on list page
 * @WP_Query() 
 * 
 * */
if(!function_exists('get_wpyg_admin_options')){ 
function get_wpyg_admin_options() {
		global $wpdb;
		$wpygOptions = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wpyg_%'");
								
		foreach ($wpygOptions as $option) {
			$wpygOptions[$option->option_name] =  $option->option_value;
		}
	
		return $wpygOptions;	
	}
}
if(!function_exists('wp_youtube_gallery_func')):
function wp_youtube_gallery_func( $attr ) {
/* Get Plugin Options */	
$pluginOptions=get_wpyg_admin_options();	
$catslug = isset($attr['category_slug']) ? $attr['category_slug'] : (isset($attr['slug']) ? $attr['slug'] : '');


if(isset($attr['catid']) && $attr['catid']!='')
{
	$terms=$attr['catid'];
	$field='id';
	
	}else
{
	$terms=$catslug;
	$field='slug';
	}

wp_reset_query();
$argslightbox = array(
    'post_type' => 'wp_youtube_gallery',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'show_ui'            => true,
	'show_in_menu'       => true,
	'menu_position' =>5,
    'tax_query' => array(
        array(
            'taxonomy' => 'wp_youtube_gallery_taxonomy',
            'field' => $field,
            'terms' => $terms
        )
    )
);
$wpyg_query = new WP_Query( $argslightbox );
$wpyg_contentlightbox=$isDesc=$isTitle=$contentdesc=$wpyg_isWidth=$wpyg_isHeight=$wpyg_InlineCss=$contentdesc='';

if(isset($pluginOptions['wpyg_desc']) && $pluginOptions['wpyg_desc']==1){
$isDesc='yes';}
if(isset($pluginOptions['wpyg_title']) && $pluginOptions['wpyg_title']==1){
$isTitle='yes';}
if(isset($pluginOptions['wpyg_iframe_w']) && $pluginOptions['wpyg_iframe_w']!=''){
$wpyg_isWidth='width:'.$pluginOptions['wpyg_iframe_w'].';';}
if(isset($pluginOptions['wpyg_min_h']) && $pluginOptions['wpyg_min_h']!=''){
$wpyg_isHeight='min-height:'.$pluginOptions['wpyg_min_h'].';';}
$wpyg_InlineCss='style="'.$wpyg_isWidth.$wpyg_isHeight.'"';
$contentLimit='200';
$limit=$pluginOptions['wpyg_content_limit'];
if($limit!='') {$contentLimit=$limit;}
$contentTitle = $extraContent = '';
$extraContentJs = '<script type="text/javascript">jQuery(document).ready(function(){jQuery(".wpyg-showhide").click(function(){$currentId=jQuery(this).attr("id"),$currentDivId=jQuery(this).parent("div").attr("id"),"wpyg-showmore"==$currentId?(jQuery("#"+$currentDivId+" .wpyg-showmore-content").removeClass("divhide"),jQuery("#"+$currentDivId+" .wpyg-showmore-content").addClass("divshow"),jQuery(this).hide()):($currentDivId=jQuery(this).attr("data-id"),jQuery("#"+$currentDivId+" .wpyg-showhide").show(),jQuery("#"+$currentDivId+" .wpyg-showmore-content").removeClass("divshow"),jQuery("#"+$currentDivId+" .wpyg-showmore-content").addClass("divhide"))})});</script>';
if($wpyg_query->have_posts()):
$wpyg_contentlightbox .='<div class="wyg_nolightbox wyg_css">'.$extraContentJs;
while ( $wpyg_query->have_posts() ) : $wpyg_query->the_post();
       $videoId=stripslashes(get_post_meta(get_the_ID(),'wpyg_video-id',true)); //youtube id
       $contentLength = strlen(strip_tags(get_the_content()));
	   if($contentLength  > $contentLimit)
	   {
		   $extraContent = '<a href="javascript:" class="wpyg-showhide" id="wpyg-showmore">show more</a><div class="wpyg-showmore-content divhide">'.substr(strip_tags(get_the_content()), $contentLimit,$contentLength).'<a href="javascript:" class="wpyg-showhide" id="wpyg-showless" data-id="video-'.get_the_ID().'">show less</a></div>';
       }
	   $content=substr(strip_tags(get_the_content()),0,$contentLimit);
       if($isDesc=='yes')
       $contentdesc='<div class="youtubecontent" id="video-'.get_the_ID().'">'.$content.$extraContent.'</div>';
       if($isTitle=='yes')
       $contentTitle=ucfirst(get_the_title());
       if($videoId!=''):
       $wpyg_contentlightbox .='<div class="wp_youtube_gallery_post" id="youtubevideo_'.$videoId.'" '.$wpyg_InlineCss.'>
                                    <div class="youtubevideo" ><iframe src="//www.youtube.com/embed/'.$videoId.'" frameborder="0" allowfullscreen width="100%" height="auto"></iframe>
                                    </div><span class="videotitle">'.$contentTitle.'</span>'.$contentdesc.'</div>';
       endif;           
	endwhile;
wp_reset_query();
endif;
$wpyg_contentlightbox .=' </div>'; 
return $wpyg_contentlightbox;
}
endif;
add_shortcode( 'wp_youtube_gallery', 'wp_youtube_gallery_func' );
add_shortcode( 'wyg', 'wp_youtube_gallery_func' );
