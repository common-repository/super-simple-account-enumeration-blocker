<?php
/*
Plugin Name: Super Simple Account Enumeration Blocker
Plugin URI: http://www.gilzow.com/ssaeb/ss
Description: A very simple account enumeration blocker
Version: 1.0.0
Author: gilzow
Author URI: http://www.gilzow.com
License: GPL2 or later
*/

/**********************************
 * username anti-enumeration stuff
 *********************************/
/**
 * Blocks remote attackers from enumerating user names via pretty URL redirects
 *
 * When using pretty permalinks, if a query-based URL is requested (e.g. http://domain.com/?author=1) WordPress will
 * redirect the request to the pretty version.  Attackers can utilize this to quickly enumerate account names as the
 * account name is usually the same as the author slug in the pretty link.  The following code looks to see if the
 * request is for an author, and if so, instructs wordpress not to redirect the request.
 *
 * @param $strRedirectionURL
 * @param $strRequestedURL
 * @return mixed
 * @see https://developer.wordpress.org/reference/hooks/redirect_canonical/
 */
add_filter('redirect_canonical',function($strRedirectionURL, $strRequestedURL){
    if (1 === preg_match('/\?author=([\d]*)/', $strRequestedURL)) {
        $strRedirectionURL = false;
    }
    return $strRedirectionURL;
}, 10,2);

/**
 * Changes author permalink to use author=# instead of username
 *
 * When using pretty permalinks, the authors permanent link includes the author slug which, in most cases, is the same
 * as the author's account name.  The following code converts the author's permalink to use a parameterized URL, thereby
 * not revealing the author slug.
 *
 * @param $strLink   string  Prepared link to Author's archive page
 * @param $intID     integer Author User ID
 *
 * @return string    Link to Author's archive page
 */
add_filter('author_link',function($strLink,$intID){
    return home_url('/') . '?author=' . $intID;
},10,2);

/**
 * Corrects author feed link after filtering author_link
 *
 * The author's feed link is based on the author_link. When we convert the author_link to not using the pretty
 * permalink structure, we need to adjust the feed link so it continues to function
 *
 * @param $strLink   string  Prepared link to author feed
 * @param $strFeed   string  Feed type
 *
 * @return string Prepared link to author feed
 */
add_filter('author_feed_link',function ($strLink,$strFeed){
    if(1 == preg_match('/^\/([^\/]+)/',$strLink,$aryMatch)){
        return $aryMatch[0] . '&feed=' . $strFeed;
    }
},10,2);

/**
 * Removes username from the body class list.  Why does wordpress include the user name in the body class?  So you can
 * add per-user custom classes, but that seems like a very fringe case vs giving hackers all of your user names. The
 * code checks the array of classes to see if there is an author class.  It then retrieves all classes that are named with
 * 'author-<slug>' and removes them, leaving the 'author-#' class so a specific author page can still be targeted.
 *
 * @param $aryClasses array of classes to include in the body element
 * @return array filtered list of classes
 */
add_filter('body_class',function($aryClasses){
    if(is_author() && in_array('author',$aryClasses)){
        /**
         * match all classes of 'author-<username>' but not 'author-id'
         *
         * match: author-admin
         * match: author-gilzowp
         * NO match: author-5
         *
         */
        $aryUserNames = preg_grep('/^author-(?!\d+$).+$/',$aryClasses);
        if(count($aryUserNames) > 0){
            $aryClasses = array_diff($aryClasses,$aryUserNames);
        }
    }
    return $aryClasses;
},100,1);

/**
 * Remove slug property from response to user query
 *
 * As of WordPress 4.7, the REST API was included with core. This API also includes the users endpoint which, unfortunately,
 * includes the slug property in the response.  In most cases, the user slug is the same as the author's account name.
 * The code simply removes the slug property from a user response object.
 *
 * @param $objResponse   WP_REST_Response    Prepared response object to REST API call
 * @param $objUser       WP_User             User object used to creare response
 * @param $objRequest    WP_REST_Request     Requested object
 *
 * @return WP_REST_Response
 */
add_filter('rest_prepare_user',function($objResponse,$objUser,$objRequest){
    if(isset($objResponse->data['slug']) && '' !== $objResponse->data['slug']){
        unset($objResponse->data['slug']);
    }
    return $objResponse;
},10,3);

/**
 * Removes the error message indicating an invalid user, or incorrect password for a specific user
 *
 * When entering in an incorrect password, WordPress, by default, will gladly inform you that the password is incorrect.
 * Or if you enter in an account name that doesn't exist, WordPress, by default, will gladly tell you that the account
 * doesn't exist.  This can be used to determine valid account names.  The following code checks to see if authentication
 * failed due to incorrect password, invalid username, or invalid email address, and if no, resets the error message to
 * "Invalud username, email address or incorrect password"
 *
 * @param $objUser WP_User|WP_Error
 * @return WP_Error|WP_User|null
 */
add_filter('authenticate',function($objUser){
    if(is_wp_error($objUser)){
        if(
            isset($objUser->errors['incorrect_password'])
            ||  isset($objUser->errors['invalid_username'])
            ||  isset($objUser->errors['invalid_email'])
        ){
            $objUser = null;;
        }
    }
    return $objUser;
},99,1);