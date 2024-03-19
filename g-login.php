<?php

/**
 * Plugin Name: Login with Google
 * Description: This plugin will add a login with google button
 * Author: Hiten
 * Version: 0.0.1
 */

//Dont access this file directly
defined('ABSPATH') or die();

// Include Google API client library
require_once plugin_dir_path(__FILE__) . 'google-api/vendor/autoload.php';

// Function to add Google login button to login form
function add_google_login_button()
{
    global $gClient;

    // Check if the button is enabled
    $button_enabled = get_option('google_login_button_enabled', true);
    if (!$button_enabled) {
        return;
    }
    $login_url = $gClient->createAuthUrl();
?>
    <style>
        .login-with-google .button {
            background-color: #fff;
            color: #757575;
            border: 1px solid #757575;
            /* Add padding to create space for the icon */
            padding-left: 30px;
            /* Adjust as needed */
            padding-right: 10px;
            /* Adjust as needed */
        }

        .login-with-google .button:hover {
            background-color: #f1f1f1;
        }

        .google-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            /* background: url('/wp-content/plugins/G-Login/media/google.jpg') no-repeat center center; */
            background-size: cover;
            margin-right: 5px;
            /* Adjust spacing between icon and text */
            margin-left: -25px;
            /* Adjust to properly align the icon */
        }
    </style>
    <p class="login-with-google">
        <a class="button button-large" href="<?php echo esc_url($login_url); ?>">
            <span class="google-icon"></span> Continue with Google
        </a>
    </p>
<?php
}
add_action('login_form', 'add_google_login_button');

//Add in admin sidebar
function add_google_login_menu()
{
    add_menu_page(
        'Login with Google Settings',
        'Login with Google',
        'manage_options',
        'google_login_setting',
        'google_login_settings_page',  // Callback function to display page content
        'dashicons-admin-users',
        99

    );
}


add_action('admin_menu', 'add_google_login_menu');

// Callback function to display plugin settings page
function google_login_settings_page()
{
    // Check if form is submitted
    if (isset($_POST['google_login_settings_submit'])) {
        // Update option based on checkbox status
        update_option('google_login_button_enabled', isset($_POST['google_login_button_enabled']));
    }

    // Get current option value
    $button_enabled = get_option('google_login_button_enabled', true);
?>
    <div class="wrap">
        <h1>Login with Google Settings</h1>
        <form method="post" action="">
            <label for="google_login_button_enabled">
                <input type="checkbox" id="google_login_button_enabled" name="google_login_button_enabled" <?php echo $button_enabled ? 'checked' : ''; ?>>
                Enable "Continue with Google" button
            </label>
            <br><br>
            <input type="submit" name="google_login_settings_submit" class="button button-primary" value="Save Settings">
        </form>
    </div>
<?php
}


$gClient = new Google_Client();
$gClient->setClientId("931518911732-ap6us2rp864crksqhve3erat47armt1k.apps.googleusercontent.com");
$gClient->setClientSecret("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");
$gClient->setApplicationName("G-Login");
$gClient->setRedirectUri("https://localhost/g-login-wordpress/wp-admin/admin-ajax.php?action=vm_login_google");
// Update the scopes
$scopes = [
    "https://www.googleapis.com/auth/userinfo.email",
];

$gClient->setScopes($scopes);

//Login URL
$login_url = $gClient->createAuthUrl();

// var_dump($login_url);

//Generate button shortcode
add_shortcode('google-login', 'login_with_google');

function login_with_google()
{
    global $login_url;

    $btnContent = ' <style>
    .googleBtn{
        display: table;
        margin: 0 auto;
        background: #4285F4;
        padding: 15px;
        border-radius: 3px;
        color: #fff;

    }
    </style>';

    if (!is_user_logged_in()) {
        //Check registration is closed

        // if (!get_option('users_can_register')) {
        //     return ('Registration is closed');
        // } else {
        return $btnContent . '<a class="googleBtn" href="' . $login_url . '">Login With Google </a>';
        // }
    } else {
        $current_user = wp_get_current_user();
        return $btnContent . '<div class="googleBtn">Hi, ' . $current_user->first_name . '! - <a href="/wp-login.php?action=logout">LogOut</a></div>';
    }
}

//Add Ajax 
add_action('wp_ajax_vm_login_google', 'vm_login_google');
function vm_login_google()
{
    global $gClient;
    // checking for google code
    if (isset($_GET['code'])) {
        $token = $gClient->fetchAccessTokenWithAuthCode($_GET['code']);
        if (!isset($token["error"])) {
            // get data from google
            $oAuth = new Google_Service_Oauth2($gClient);
            $userData = $oAuth->userinfo_v2_me->get();
        }

        // check if user email already registered
        if (!email_exists($userData['email'])) {
            // generate password
            $bytes = openssl_random_pseudo_bytes(2);
            $password = md5(bin2hex($bytes));
            $user_login = $userData['id'];


            $new_user_id = wp_insert_user(
                array(
                    'user_login'        => $user_login,
                    'user_pass'             => $password,
                    'user_email'        => $userData['email'],
                    'first_name'        => $userData['givenName'],
                    'last_name'            => $userData['familyName'],
                    'user_registered'    => date('Y-m-d H:i:s'),
                    'role'                => 'subscriber'
                )
            );
            if ($new_user_id) {
                // send an email to the admin
                wp_new_user_notification($new_user_id);

                // log the new user in
                do_action('wp_login', $user_login, $userData['email']);
                wp_set_current_user($new_user_id);
                wp_set_auth_cookie($new_user_id, true);

                // send the newly created user to the home page after login
                wp_redirect(home_url());
                exit;
            }
        } else {
            //if user already registered than we are just loggin in the user
            $user = get_user_by('email', $userData['email']);
            do_action('wp_login', $user->user_login, $user->user_email);
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);
            wp_redirect(home_url());
            exit;
        }


        var_dump($userData);
    } else {
        wp_redirect(home_url());
        exit();
    }
}


//ALLOW LOGOUT USER TO ACCESS ADMIN-AJAX.PHP
function add_google_ajax_actions()
{
    add_action('wp_ajax_nopriv_vm_login_google', 'vm_login_google');
}

add_action('admin_init', 'add_google_ajax_actions');
