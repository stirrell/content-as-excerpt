<?php
/**
 * Plugin Name: Content as Excerpt
 * Description: Use the full content of a post as an excerpt
 * Plugin URI: https://www.second-cup-of-coffee.com
 * Author: Scott Tirrell
 * Author URI: https://www.second-cup-of-coffee.com
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: contentforexcerpt
 */

/**
 * Add a checkbox to the publish post area to override a post's excerpt with the full content
 * @return null
 */
function contentforexcerpt_addCheckbox() {
	$post_id = get_the_ID();

	if( get_post_type( $post_id ) != 'post' ) {
        return;
    }

    $value = get_post_meta( $post_id, '_content_for_excerpt', true );
    wp_nonce_field( 'contentforexcerpt_nonce_' . $post_id, 'contentforexcerpt_nonce' );

    ?>
    <div class="misc-pub-section misc-pub-section-last">
        <label for="_content_for_excerpt">
        	<input type="checkbox" value="1" <?php checked( $value, true, true ); ?> name="_content_for_excerpt" /><?php _e( 'Use Content For Excerpt', 'contentforexcerpt' ); ?>
        </label>
    </div>
<?php }
add_action( 'post_submitbox_misc_actions', 'contentforexcerpt_addCheckbox' );

/**
 * Save the selected value for our custom checkbox
 * @param  int $post_id The post ID
 * @return null
 */
function contentforexcerpt_saveCheckbox( $post_id ) {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }

    if (
        !isset( $_POST['contentforexcerpt_nonce'] ) ||
        !wp_verify_nonce( $_POST['contentforexcerpt_nonce'], 'contentforexcerpt_nonce_' . $post_id )
    ) {
        return;
    }
    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['_content_for_excerpt'] ) ) {
        update_post_meta( $post_id, '_content_for_excerpt', $_POST['_content_for_excerpt'] );
    } else {
        delete_post_meta( $post_id, '_content_for_excerpt' );
    }
}
add_action( 'save_post', 'contentforexcerpt_saveCheckbox' );

/**
 * Filter for the excerpt - should we override with the post's full content?
 * @param  string $excerpt The post's excerpt
 * @return string The filtered excerpt
 */
function contentforexcerpt_contentFilter( $excerpt ) {
	global $post;
	if( !is_admin() && ( is_front_page() || is_archive() ) ) {
		$value = get_post_meta( $post->ID, '_content_for_excerpt', true );
		if( 1 == $value ) {
			$excerpt = $post->post_content;
			return apply_filters( 'the_content', $excerpt );
		}
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'contentforexcerpt_contentFilter', 50 );
