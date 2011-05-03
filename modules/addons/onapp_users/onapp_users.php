<?php

include_once 'classes/Addon.php';

function onapp_users_output( $vars ) {
    include_once ROOTDIR . '/includes/smarty/Smarty.class.php';
    $smarty = new Smarty( );
    $smarty->compile_dir = ROOTDIR . '/templates_c/';
    $smarty->template_dir = ROOTDIR . '/admin/templates/' . $GLOBALS['aInt' ]->adminTemplate . '/onapp_users_addon/';
    $vars[ '_lang' ]['JSMessages'] = json_encode($vars[ '_lang' ]['JSMessages']);
    $smarty->assign( 'LANG', $vars[ '_lang' ] );
    $smarty->assign( 'BASE_CSS', '/admin/templates/' . $GLOBALS['aInt' ]->adminTemplate . '/onapp_users_addon' );
    $smarty->assign( 'BASE_JS', '/modules/addons/onapp_users/js' );

    $module = new OnApp_Users_Addon( $smarty );
    $smarty->assign( 'onapp_servers', $servers = $module->getServers( ) );
    if( isset( $_GET[ 'server_id' ] ) ) {
        $smarty->assign( 'server_id', $_GET[ 'server_id' ] );
    }
    else {
        $server = current( $servers );
        $smarty->assign( 'server_id', $server[ 'id' ] );
        $_GET[ 'server_id' ] = $server[ 'id' ];
    }

    if( isset( $_POST[ 'filter' ] ) ) {
        $filter = 'filter' . $_POST[ 'filter' ];
        $data = $module->$filter( );
        $smarty->assign( 'whmcs_users', $data[ 'data' ] );
    }
    else {
        if( isset( $_GET[ 'map' ] ) ) {
            $data = $module->getUsersFromWHMCS( $_GET[ 'whmcs_user_id' ] );
            $smarty->assign( 'whmcs_user', $data[ 'data' ] );

            $data = $module->getUsersFromOnApp( );
            $smarty->assign( 'onapp_users', $data[ 'data' ] );
        }
        elseif( isset( $_GET[ 'info' ] ) ) {
            $data = $module->getUserData( );
            $smarty->assign( 'whmcs_user', $data[ 'whmcs_user' ] );
            $smarty->assign( 'onapp_user', $data[ 'onapp_user' ] );
        }
        else {
            $data = $module->getUsersFromWHMCS( );
            $smarty->assign( 'whmcs_users', $data[ 'data' ] );
        }
    }

    $smarty->assign( 'pages', $data[ 'pages' ] );
    $smarty->assign( 'current', $data[ 'current' ] );

    if( isset( $data[ 'prev' ] ) ) {
        $smarty->assign( 'prev', $data[ 'prev' ] );
    }
    if( isset( $data[ 'next' ] ) ) {
        $smarty->assign( 'next', $data[ 'next' ] );
    }

    $smarty->assign( 'total', $data[ 'total' ] );
    $smarty->assign( 'server_id', $_GET[ 'server_id' ] );

    $module->cleanParams( );
    echo $smarty->fetch( $smarty->template_dir . 'onapp_users.tpl' );
}

function onapp_users_config( ) {
    $config = array(
        'name' => 'OnApp Users Addon',
        'version' => '1.0',
        'author' => 'OnApp',
        'description' => 'This module allows you to map existing OnApp user to WHMCS user and some other useful actions.',
        'language' => 'english'
    );

    return $config;
}