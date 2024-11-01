<?php
/*
Plugin Name: WP Youtube Gallery
Plugin URI: https://www.wp-experts.in/
Description: A very simple and light weight youtube gallery plugin. Using shortcode you can add youtube video gallery on any page of the website.
Author: WP Experts Team
Author URI: http://www.wp-experts.in
Version: 1.9
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('WP_Youtube_Gallery'))
{
    class WP_Youtube_Gallery
    {
	 /**
      * Construct the plugin object
      */
	public function __construct()
	   {
		    // register actions
			add_action('admin_init', array(&$this, 'wpyg_sidebar_init'));
			add_action('admin_menu', array(&$this, 'wpyg_sidebar_menu'));
			add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), array(&$this,'wpyg_add_settings_link' ));
			/** register_activation_hook */
			register_activation_hook( __FILE__, array(&$this, 'init_activation_wpyg_plugins' ) );
			/** register_deactivation_hook */
			register_deactivation_hook( __FILE__, array(&$this, 'init_deactivation_wpyg_plugins' ) );
			add_action( 'admin_bar_menu', array(&$this,'toolbar_link_to_wpyg'), 999 );
			add_filter('manage_edit-wp_youtube_gallery_taxonomy_columns', array(&$this,'term_shortcode_column_header'), 10);
			add_filter( 'manage_wp_youtube_gallery_taxonomy_custom_column' , array(&$this,'term_shortcode_column') , 10 , 3 );
		}
		/**
		 *  Add shortcode column for taxonomy
		 */
		public function term_shortcode_column_header( $columns ){
		unset($columns['slug']);
			$columns['shortcode'] = 'Shortcode';
			return $columns;
		}
		
		public function term_shortcode_column($content,$column_name,$term_id){
			$term= get_term($term_id, 'wp_youtube_gallery_taxonomy');
			switch ($column_name) {
				case 'shortcode':
					$content = '<code>[wyg slug="'.$term->slug.'"]</code>';
					break;
				default:
					break;
			}
			return $content;
		}
		/**
		 * hook to add link under adminmenu bar
		 */		
		public function toolbar_link_to_wpyg( $wp_admin_bar ) {
			$args = array(
				'id'    => 'wpyg_menu_bar',
				'title' => 'WP Youtube Gallery',
				'href'  => admin_url('admin.php?page=wpyg-settings'),
				'meta'  => array( 'class' => 'wpyg-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
			//second lavel
			$wp_admin_bar->add_node( array(
				'id'    => 'wpyg-second-sub-item',
				'parent' => 'wpyg_menu_bar',
				'title' => 'Settings',
				'href'  => admin_url('admin.php?page=wpyg-settings'),
				'meta'  => array(
					'title' => __('Settings'),
					'target' => '_self',
					'class' => 'wpyg_menu_item_class'
				),
			));
		}
	/**
      * Admin Menu
      */
	public function wpyg_sidebar_menu()
	{
	  add_submenu_page('edit.php?post_type=wp_youtube_gallery','WP Youtube Gallery Settings Page','Settings','manage_options','wpyg-settings',array(&$this,'wpyg_sidebar_admin_option_page'));
     }
    /**
      *  Register_Setting
      */
    function wpyg_sidebar_init()
    {
		register_setting('wpyg_sidebar_options','wpyg_min_h');
		register_setting('wpyg_sidebar_options','wpyg_lightbox');
		register_setting('wpyg_sidebar_options','wpyg_iframe_w');
		register_setting('wpyg_sidebar_options','wpyg_desc');
		register_setting('wpyg_sidebar_options','wpyg_title');
		register_setting('wpyg_sidebar_options','wpyg_content_limit');
		register_setting('wpyg_sidebar_options','wpyg_per_row_posts');
    }
	/**
	*  Plugin Settings Links
	*/
    public function wpyg_add_settings_link( $links ) 
    {
            $settings_link = '<a href="edit.php?post_type=wp_youtube_gallery&page=wpyg-settings">' . __( 'Settings', 'wpyg' ) . '</a> | <a href="https://www.wp-experts.in/products/wp-youtube-gallery-pro/">' . __( 'Go Pro', 'wpyg' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
     }
     /** 
	  * Display the Options form for WP Youtube Gallery
	  *
	*/
   public function wpyg_sidebar_admin_option_page(){ ?>
	<div style="width: 80%; padding: 10px; margin: 10px;"> 
	<h1>WP Youtube Gallery Settings</h1>
   <!-- Start Options Form -->
	<form action="options.php" method="post" id="wpyg-sidebar-admin-form">
	<div id="wpyg-tab-menu"><a id="wpyg-general" class="wpyg-tab-links active" >General Settings</a> <a  id="wpyg-support" class="wpyg-tab-links">Support & Tutorial</a> </div>
	<div class="wpyg-setting">
	<!-- General Setting -->	
	<div class="first wpyg-tab" id="div-wpyg-general">
	<h2>General Settings</h2>
	
	<table>
	<tr>
	<td valign="top">
	<p><input type="checkbox" id="wpyg_title" name="wpyg_title" value='1' <?php if(get_option('wpyg_title')!=''){ echo ' checked="checked"'; }?>/><label><?php _e('Show Title:');?><label> </p>
	<p><input type="checkbox" id="wpyg_desc" name="wpyg_desc" value='1' <?php if(get_option('wpyg_desc')!=''){ echo ' checked="checked"'; }?>/><label><?php _e('Show Description:');?><label></p>  
	 <p><label><?php _e('Video Box Width:');?><label><br><input type="text" id="wpyg_iframe_w" name="wpyg_iframe_w" size="10" value="<?php echo get_option('wpyg_iframe_w'); ?>" placeholder="100%"> </p>  
	  <p><label><?php _e('Video Minimum Height:');?><label><br><input type="text" id="wpyg_min_h" name="wpyg_min_h" size="10" value="<?php echo get_option('wpyg_min_h'); ?>" placeholder="auto"> </p> 
	 <p><label><?php _e('Content Limit:');?><label><br><input type="text" id="wpyg_content_limit" name="wpyg_content_limit" size="10" value="<?php echo get_option('wpyg_content_limit'); ?>" placeholder="200"> </p>  
	<hr>
	<h2>Shortcodes</h2>
	<p><a href="edit-tags.php?taxonomy=wp_youtube_gallery_taxonomy&post_type=wp_youtube_gallery" target="_blank">Click here</a> to get shortcode.</p>
	<p>To add video gallery on your website pages, please use given below shortocde.</p>
	<p><b>[wyg slug="wordpress-tutorial"]<br>or<br>[wp_youtube_gallery category_slug="ENTER YOUTUBE CATEGORY SLUG"]</b> </p>
	<p>To add video gallery through template files, please use given below function.</p>
	<p><code>if(function_exists('wp_youtube_gallery_func')){<br> echo do_shortcode('[wyg slug="ENTER YOUTUBE CATEGORY SLUG"]');<br>}</code></p>
		  
	</td>
	<td valign="top" style="border-left:2px solid #ccc; padding-left:20px;">
	<h3>Add-on Features</h3>
	<ol>
<li>Responsive Video Gallery</li>
<li>Video Lightbox popup</li>
<li>Disable Related Videos</li>
<li>Disable Related Videos</li>
<li>Manage Videos Order</li>
<li>Category base filter</li>
<li>Manage CSS from admin</li>
<li>Faster support</li>
	</ol>
	<p><h3><a href="https://www.wp-experts.in/products/wp-youtube-gallery-pro/">Click here</a> to download addon.</h3></p>
	<iframe width="560" height="315" src="https://www.youtube.com/embed/v2rPeKY5ynk?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
	
	</td>
	</tr>
	</table>
	
	
	</div>
	<!-- Shortcodes -->
	<!-- Support -->
	<div class="last author wpyg-tab" id="div-wpyg-support">
	<table>
	<tr>
	<td width="50%" valign="top">
		<h3>Video Tutorial:</h3>
		<iframe width="560" height="315" src="https://www.youtube.com/embed/v2rPeKY5ynk" frameborder="0" allow="encrypted-media" allowfullscreen></iframe>
		<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4624D4L4LT6NU" target="_blank" style="font-size: 17px; font-weight: bold;"><img src="<?php echo  plugins_url( 'images/btn_donate_LG.gif' , __FILE__ );?>" title="Donate for this plugin"></a></p>
	<p><strong>Plugin Author:</strong><br><a href="https://www.wp-experts.in" target="_blank">WP Experts Team</a></p>
	<p><a href="mailto:raghunath.0087@gmail.com" target="_blank" class="contact-author">Contact Author</a></p></td>
	<td style="padding:0px 10px 0px 10px;border-left:2px solid #ccc;"><p><strong>Our Other Plugins:</strong><br>
	 <ol>
					<li><a href="https://wordpress.org/plugins/custom-share-buttons-with-floating-sidebar" target="_blank">Custom Share Buttons With Floating Sidebar</a></li>
					<li><a href="https://wordpress.org/plugins/seo-manager/" target="_blank">SEO Manager</a></li>
							<li><a href="https://wordpress.org/plugins/protect-wp-admin/" target="_blank">Protect WP-Admin</a></li>
							<li><a href="https://wordpress.org/plugins/wp-sales-notifier/" target="_blank">WP Sales Notifier</a></li>
							<li><a href="https://wordpress.org/plugins/wp-tracking-manager/" target="_blank">WP Tracking Manager</a></li>
							<li><a href="https://wordpress.org/plugins/wp-categories-widget/" target="_blank">WP Categories Widget</a></li>
							<li><a href="https://wordpress.org/plugins/wp-protect-content/" target="_blank">WP Protect Content</a></li>
							<li><a href="https://wordpress.org/plugins/wp-version-remover/" target="_blank">WP Version Remover</a></li>
							<li><a href="https://wordpress.org/plugins/wp-posts-widget/" target="_blank">WP Post Widget</a></li>
							<li><a href="https://wordpress.org/plugins/wp-importer" target="_blank">WP Importer</a></li>
							<li><a href="https://wordpress.org/plugins/wp-csv-importer/" target="_blank">WP CSV Importer</a></li>
							<li><a href="https://wordpress.org/plugins/wp-testimonial/" target="_blank">WP Testimonial</a></li>
							<li><a href="https://wordpress.org/plugins/wc-sales-count-manager/" target="_blank">WooCommerce Sales Count Manager</a></li>
							<li><a href="https://wordpress.org/plugins/wp-social-buttons/" target="_blank">WP Social Buttons</a></li>
							<li><a href="https://wordpress.org/plugins/wp-youtube-gallery/" target="_blank">WP Youtube Gallery</a></li>
							<li><a href="https://wordpress.org/plugins/tweets-slider/" target="_blank">Tweets Slider</a></li>
							<li><a href="https://wordpress.org/plugins/rg-responsive-gallery/" target="_blank">RG Responsive Slider</a></li>
							<li><a href="https://wordpress.org/plugins/cf7-advance-security" target="_blank">Contact Form 7 Advance Security WP-Admin</a></li>
							<li><a href="https://wordpress.org/plugins/wp-easy-recipe/" target="_blank">WP Easy Recipe</a></li>
					</ol>
	 </td>
	</tr>
	</table>
	</div>
	</div>
    <span class="submit-btn"><?php echo get_submit_button('Save Settings','button-primary','submit','','');?></span>
    <?php settings_fields('wpyg_sidebar_options'); ?>
	</form>
	<!-- End Options Form -->
		</div>
	<?php
	}
	 /** 
      * register_activation_hook 
      **/
    static function init_activation_wpyg_plugins(){
		 /* Flush rewrite rules for custom post types. */
		 flush_rewrite_rules();

		}
	/** 
	 * register_deactivation_hook 
	 **/
    static function init_deactivation_wpyg_plugins(){
		 flush_rewrite_rules();

		} 
	
    } // END class WP_Youtube_Gallery
} // END if(!class_exists('WP_Youtube_Gallery'))

if(class_exists('WP_Youtube_Gallery'))
{
    // instantiate the plugin class
    $wpyg_plugin_template = new WP_Youtube_Gallery();
}

require dirname(__FILE__).'/wpyg-class.php';
/** add js into admin footer */
add_action('admin_footer','init_wpyg_admin_scripts');
if(!function_exists('init_wpyg_admin_scripts')):
function init_wpyg_admin_scripts()
{
wp_register_style( 'wpyg_admin_style', plugins_url( 'css/wpyg-admin.css',__FILE__ ) );
wp_enqueue_style( 'wpyg_admin_style' );
echo $script='<script type="text/javascript">
	/* WP Youtube Gallery js for admin */
	jQuery(document).ready(function(){
		jQuery(".wpyg-tab").hide();
		jQuery("#div-wpyg-general").show();
	    jQuery(".wpyg-tab-links").click(function(){
		var divid=jQuery(this).attr("id");
		jQuery(".wpyg-tab-links").removeClass("active");
		jQuery(".wpyg-tab").hide();
		jQuery("#"+divid).addClass("active");
		jQuery("#div-"+divid).fadeIn();
		})
		})
	</script>';

	}	
endif;	
?>
