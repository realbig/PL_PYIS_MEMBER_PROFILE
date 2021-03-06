<?php
/**
 * Members JSON Output
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Since we are not a real Page and have no context of where we came from, we need to grab the User from the URL

$url = str_replace( '?json', '', $_SERVER['REQUEST_URI'] );

$url = rtrim( $url, '/' ); // If there's a trailing slash, remove it so we can ensure we grab the User Name

$user_name = explode( '/', $url );
$user_name = $user_name[ count( $user_name ) - 1 ];

$user = get_user_by( 'login', $user_name );
$user_id = $user->data->ID;

if ( ( ( $user->roles[0] !== 'subscriber' ) && ( ( $user->roles[0] !== 'administrator' ) || ( strtolower( $user->user_login ) !== 'adrian' ) ) ) || ( ! $user ) ) {
    
    // No checking JSON for non-subscribers or non-admins that aren't Adrian
    
    header( 'Content-Type: text/html' );
    
    global $wp_query;

    // Tricking WP Core functions into thinking we're a real Page.
    $wp_query->is_404 = false;
    $wp_query->is_page = true;
    
    get_header();
    
    include( PyisMemberProfile::pyis_locate_template( 'member-not-found.php' ) );
    
    get_footer();
    
    die();
    
}

// Now that we have queried the User from the URL, we can access a lot more data
$pyis_user_data = get_userdata( $user_id );
$course_id = get_theme_mod( 'pyis_course', 0 );

$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
$course_progress = ( $course_progress[ $course_id ]['completed'] / $course_progress[ $course_id ]['total'] ) * 100;

// If due to a LearnDash bug they have over 100% completion, reset to 100%
if ( $course_progress > 100 ) $course_progress = 100;

$data = array(
    'last_name' => trim( $pyis_user_data->last_name ),
    'first_name' => trim( $pyis_user_data->first_name ),
    'course_progress' => $course_progress,
    'course_completed' => learndash_course_completed( $user_id, $course_id ),
    'url' => get_bloginfo( 'url' ) . $url,
);

die( json_encode( $data ) );