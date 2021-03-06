<?php
/*
Plugin Name: Admin tools login
Description: Take access of a user account if you are Administrator or Moderator user.
Version: 1.0.1
Author: garciademarina
Author URI: http://www.garciademarina.com/
Short Name: admin_tools_login
Plugin update URI: admin-tools-login
*/

osc_add_route('admin_tools_login_as', 'admin_tools_login_as/(\d+)', 'admin_tools_login_as/{user_id}', osc_plugin_folder(__FILE__) . 'empty.php');

function admin_tools_login_footer() {
    if( osc_is_admin_user_logged_in() &&
        /* Public user profile url */
        (osc_is_public_profile() ||
        /* Listing detail url */
        osc_is_ad_page() )
        || (osc_is_admin_user_logged_in() && osc_is_web_user_logged_in() )
        ) {
    ?>
        <style>
            <?php require_once 'css/style.css'; ?>
        </style>
        <div id="admin_tools_login_footer">
            <p>
                <?php if (!osc_is_web_user_logged_in()) { ?>
                    <a class="btn-custom" href="<?php echo osc_route_url('admin_tools_login_as', array('user_id' => osc_user_id())); ?>"><?php _e('Login as', 'admin_tools_login'); ?> [<?php echo osc_user_name(); ?>]</a>
                <?php } else { ?>
                    <a class="btn-custom" href="<?php echo osc_route_url('admin_tools_login_as', array('logout' => osc_user_id())); ?>"><?php _e('Logout as', 'admin_tools_login'); ?> [<?php echo osc_user_name(); ?>]</a>
                <?php } ?>
            </p>
        </div>
    <?php
        }
}
osc_add_hook('footer', 'admin_tools_login_footer');

function admin_tools_login_init() {
    if( osc_is_admin_user_logged_in() && Params::getParam('route')=='admin_tools_login_as' ) {
        if(Params::getParam('user_id')!='') {
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
            /* logout */
            if(Params::getParam('logout')!='') {
                error_log( 'logout in' );
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
    }
}
osc_add_hook('init', 'admin_tools_login_init');