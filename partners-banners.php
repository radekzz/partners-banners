<?php
/*
Plugin Name: Partners Banners
Plugin URI: http://bannershtml5.net
Description: Display partners banners in widget
Version: 1.0
Author: Zedna Brickick Website
Author URI: http://www.mezulanik.cz
License: GPL2
Text Domain: partners-banners
Domain Path: /languages/
*/

class partners_banners_widgets {
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'load' ), 9 );
		add_action( 'widgets_init', array( $this, 'init' ), 10 );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	public function load() {
		$dir = plugin_dir_path( __FILE__ );
		
    include_once( $dir . 'inc/widget-partners-banners.php' );
    
	}

	public function init() {
		if ( ! is_blog_installed() ) {
			return;
		}

		load_plugin_textdomain( 'partners-banners-widgets', false, 'partners-banners/languages' );

		register_widget( 'Partners_Banners_Widget' );
	}

	public function uninstall() {}
}

$partners_banners_widgets = new partners_banners_widgets();


/* Custom post types */
add_action( 'init', 'create_post_type_banner' );
function create_post_type_banner() {
  register_post_type( 'partnersbanner',
    array(
      'labels' => array(
        'name' => __( 'Partner banners' ),
        'singular_name' => __( 'Partners banner' )
      ),
      'supports' => array( 'title', 'thumbnail'),  
      'public' => true,
      'has_archive' => true,
      'taxonomies' => array('category'),
      'rewrite' => array( 'slug' => 'partnersbanners', 'with_front' => true)
    )
  ); 
}
/* // Custom post types */

// Add the Meta Box for partner custom field
function add_partner_meta_box_banner() {
    add_meta_box(
        'partner_meta_box', // $id
        'Partner link', // $title
        'show_partner_meta_box_banner', // $callback
        'partnersbanner', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'add_partner_meta_box_banner', 0 );

// Field Array for partner custom field
$partner_meta_fields = array(
     array(
        'label'=> 'Partner URL link',
        'desc'  => 'e.g.: http://www.partnerurl.com <p>Insert partner logo as Thumbnail image.</p>',
        'id'    => 'partnerlink',
        'type'  => 'text'
    )
);

// The Callback for partner and homepage partner custom field
function show_partner_meta_box_banner() {
global $partner_meta_fields, $post;
// Use nonce for verification
echo '<input type="hidden" name="partner_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
     
    // Begin the field table and loop
    echo '<table class="form-table">';
    foreach ($partner_meta_fields as $field) {
        // get value of this field if it exists for this post
        $meta = get_post_meta($post->ID, $field['id'], true);
        // begin a table row with
        echo '<tr>        
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                case 'select':                    
                  echo '</td><td><select>';
                  echo '<option value="'.$field['options']['partnerlink'].'">'.$field['options']['partnerlink'].'</option>';
                  echo '</select></td></tr>';
                break;
                case 'text':
                  echo '</td><td><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />&nbsp;<span class="description">'.$field['desc'].'</span></td></tr>';
                break;
                
                    // case items will go here
                } //end switch
        // text
    } // end foreach
    echo '</table>'; // end table    
}


// Save the Data for partner custom field
function save_partner_meta_banner($post_id) {
    global $partner_meta_fields;
     
    // verify nonce
    if (!wp_verify_nonce($_POST['partner_meta_box_nonce'], basename(__FILE__))) 
        return $post_id;
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
    }
     
    // loop through fields and save the data
    foreach ($partner_meta_fields as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];
        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    } // end foreach
}
add_action('save_post', 'save_partner_meta_banner');
/* // ADD CUSTOM FIELDS TO partner POSTS */
