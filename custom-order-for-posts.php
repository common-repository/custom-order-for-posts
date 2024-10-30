<?php
/*
Plugin Name: Custom Order For Posts
Plugin URI: https://rajatmeshram.in/custom-order-plugin
Description: This Plugin create a custom order input box where you can give custom ordering for the post which shows in the archieve page. According to the ordering posts will show in archieve list.
Version: 1.1
Author: Rajat Meshram
Author URI: https://rajatmeshram.in
License: GPLv2
Text Domain : custom-order-for-posts
*/

/* Plugin Licence

Copyright 2014 RAJAT MESHRAM (email : support@rajatmeshram.in)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// Make sure we don't expose any info if called directly
if ( basename( $_SERVER['PHP_SELF'] ) == basename( __FILE__ ) ) {
	die( 'Sorry, but you cannot access this page directly.' );
}

if( ! function_exists( 'custom_order_post_list_sort' ) ) {
    function custom_order_post_list_sort( $post ) {
        add_meta_box( 
            'custom_post_sort_box', 
            'Position in List of Posts', 
            'custom_order_post_list_order', 
            'post' ,
            'side'
        );
    }
}

if( ! function_exists( 'custom_order_post_list_order' ) ) {
    function custom_order_post_list_order( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'custom_order_post_list_order_nonce' );
        $current_pos = get_post_meta( $post->ID, '_custom_post_order', true); 
        $custom_pos = intval( $current_pos );
    ?>
        <p><?php echo __( 'Enter the position at which you would like the post Will show in List. For exampe, if you set "1" will appear at first Position, post "2" second, and so on.', 'custom-order-for-posts' ); ?></p>
        <p><input type="number" name="pos" value="<?php echo esc_attr( $custom_pos ); ?>" /></p>
    <?php
    }
}
add_action( 'add_meta_boxes', 'custom_order_post_list_sort' );
  
// Save the input to post_meta_data
if( ! function_exists( 'custom_post_order_list_save' ) ) {
    function custom_post_order_list_save( $post_id ) {
        if ( !isset( $_POST['custom_order_post_list_order_nonce'] ) || !wp_verify_nonce( $_POST['custom_order_post_list_order_nonce'], basename( __FILE__ ) ) ){
            return;
        } 
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( isset( $_REQUEST['pos'] ) ) {
            update_post_meta( $post_id, '_custom_post_order', sanitize_text_field( $_POST['pos'] ) );
        }
    }
}
add_action( 'save_post', 'custom_post_order_list_save' );

// Add custom post order column to post list
if( ! function_exists( 'custom_post_order_list_column' ) ) {
    function custom_post_order_list_column( $columns ) {
        return array_merge ( $columns,
        array( 'pos' => 'Position', ));
    }
}
add_filter('manage_posts_columns' , 'custom_post_order_list_column');
  
// Display custom post order in the post list
if( ! function_exists( 'custom_order_post_list_order_value' ) ) {
    function custom_order_post_list_order_value( $column, $post_id ) {
        if ( $column == 'pos' ) {
            $cust_order_disp = intVal( get_post_meta( $post_id, '_custom_post_order', true ) );
            echo '<p>' . esc_attr($cust_order_disp) . '</p>';
        }
    }
}
add_action( 'manage_posts_custom_column' , 'custom_order_post_list_order_value' , 10 , 2 );

// Sort posts on the blog posts page according to the custom sort order
if( ! function_exists( 'custom_order_post_list_order_sort' ) ) {
    function custom_order_post_list_order_sort( $query ) {
        if ( $query->is_main_query() && is_home() ){
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', '_custom_post_order' );
            $query->set( 'order' , 'ASC' );
        }
    }
}
add_action( 'pre_get_posts' , 'custom_order_post_list_order_sort' );