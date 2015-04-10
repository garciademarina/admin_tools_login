<?php if(!defined('ABS_PATH')) exit();
/*
Plugin Name: Admin Tools Login
Description: Take access of a user account if you are Administrator or Moderator user.
Version: 1.0.2
Author: garciademarina
Author URI: http://www.garciademarina.com/
Short Name: admin_tools_login
Plugin update URI: admin-tools-login
*/

// Login Route
osc_add_route('admin_tools_login_login-route', 'admin/login/(\d+)', 'admin/login/{user_id}', osc_plugin_folder(__FILE__) . 'index.php');

// Logout Route
osc_add_route('admin_tools_login_logout-route', 'admin/logout/(\d+)', 'admin/logout/{user_id}', osc_plugin_folder(__FILE__) . 'index.php');

// Admin Login/Logout Bar
function admin_tools_login() {
    if( osc_is_admin_user_logged_in() && (osc_is_public_profile() || osc_is_ad_page()) || (osc_is_admin_user_logged_in() && osc_is_web_user_logged_in()) ) {
        if (osc_logged_user_id()!='' || osc_user_id()!='' && !osc_is_web_user_logged_in()) { ?>
            <style><?php require_once 'css/style.css'; ?></style>
            <div id="admin_tools_login_footer">
                <p>
                        <?php if (!osc_is_web_user_logged_in()) { ?>
                            <a class="btn-custom" href="<?php echo osc_route_url('admin_tools_login_login-route', array('user_id' => osc_user_id())); ?>"><?php _e('Admin Login', 'admin_tools_login'); ?> [ <?php echo osc_user_name(); ?> ]</a>
                        <?php } else { ?>
                            <a class="btn-custom" href="<?php echo osc_route_url('admin_tools_login_logout-route', array('user_id' => osc_logged_user_id())); ?>"><?php _e('Admin Logout', 'admin_tools_login'); ?></a>
                        <?php } ?>
                    <?php } ?>
                </p>
            </div>
    <?php }
}
osc_add_hook('header', 'admin_tools_login');

// Admin Tools Login/Logout Action
function admin_tools_login_init() {
    if( osc_is_admin_user_logged_in() && Params::getParam('route')=='admin_tools_login_login-route' ) {

        if(Params::getParam('user_id')!=='') {
            $user = User::newInstance()->findByPrimaryKey( Params::getParam('user_id') );

            if( !$user ) {
                return 0;
            }

            //we are logged in... let's go!
            Session::newInstance()->_set('userId', $user['pk_i_id']);
            Session::newInstance()->_set('userName', $user['s_name']);
            Session::newInstance()->_set('userEmail', $user['s_email']);
            $phone = ($user['s_phone_mobile']) ? $user['s_phone_mobile'] : $user['s_phone_land'];
            Session::newInstance()->_set('userPhone', $phone);

            osc_redirect_to(osc_user_dashboard_url());
            return 3;
        } else {
            osc_redirect_to(osc_base_url());
        }

    } elseif( osc_is_admin_user_logged_in() && Params::getParam('route')=='admin_tools_login_logout-route' ) {

        //destroying session
        Session::newInstance()->_drop('userId');
        Session::newInstance()->_drop('userName');
        Session::newInstance()->_drop('userEmail');
        Session::newInstance()->_drop('userPhone');

        Cookie::newInstance()->pop('oc_userId');
        Cookie::newInstance()->pop('oc_userSecret');
        Cookie::newInstance()->set();

        osc_redirect_to(osc_base_url());

    }
}
osc_add_hook('init', 'admin_tools_login_init');
