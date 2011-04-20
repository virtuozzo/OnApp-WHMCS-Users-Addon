<?php

//error_reporting( E_ERROR );
//error_reporting( E_ALL );
//ini_set( 'display_errors', 'On' );

//var_dump( $_REQUEST );

include_once 'classes/Addon.php';

if( !strpos( dirname( __FILE__ ), 'modules/addons' ) ) {
    load_lang( );
    $vars[ '_lang' ] = $_LANG;
    onapp_users_output( $vars );
}

function onapp_users_output( $vars ) {
    include_once ROOTDIR . '/includes/smarty/Smarty.class.php';
    $smarty = new Smarty( );

    $module = new OnApp_Users_Addon( $smarty );

    $smarty->compile_dir = ROOTDIR . '/templates_c/';
    $smarty->template_dir = dirname( __FILE__ ) . '/templates/';
    $smarty->assign( 'LANG', $vars[ '_lang' ] );

    $smarty->assign( 'onapp_servers', $servers = $module->getServers( ) );

    if( !isset( $_GET[ 'server_id' ] ) ) {
        $server = array_shift( $servers );
        $_GET[ 'server_id' ] = $server[ 'id' ];
    }

    if( isset( $_POST[ 'filter' ] ) ) {
        $filter = 'filter' . $_POST[ 'filter' ];
        $data = $module->$filter( );
        $smarty->assign( 'whmcs_users', $data[ 'data' ] );
    }
    else {
        $data = $module->getUsersFromWHMCS( );
        $smarty->assign( 'whmcs_users', $data[ 'data' ] );

        if( isset( $_GET[ 'map' ] ) ) {
            $data = $module->getUsersFromOnApp( );
            $smarty->assign( 'onapp_users', $data[ 'data' ] );
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
    if( isset( $_GET[ 'server_id' ] ) ) {
        $smarty->assign( 'server_id', $_GET[ 'server_id' ] );
    }
    else {
        $server = current( $module->getServers( ) );
        $smarty->assign( 'server_id', $server[ 'id' ] );
    }

    $module->cleanParams( );

    echo $smarty->fetch( dirname( __FILE__ ) . '/templates/onapp_users.tpl' );
}

function onapp_users_config( ) {
    $configarray = array(
        'name' => 'OnApp Users Mapping',
        'version' => '1.0',
        'author' => 'OnApp',
        'description' => 'Description...',
        'language' => 'english'
    );

    return $configarray;
}

function onapp_users_activate( ) {
    $sql = 'CREATE TABLE IF NOT EXISTS `mod_onapp_users` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `onapp_user_id` int(11) NOT NULL,
			  `onapp_user_firstname` varchar(255) NOT NULL,
			  `onapp_user_lastname` varchar(255) NOT NULL,
			  `onapp_user_email` varchar(255) NOT NULL,
			  `onapp_user_server_id` int(11) NOT NULL,
			  `whmcs_user_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8';
    mysql_query( $sql );

    $module = new OnApp_Users_Addon( );
    $module->flushCache( );
}

function load_lang( ) {
    global $_LANG;

    if( isset( $_SESSION[ 'Language' ] ) ) {
        $language = $_SESSION[ 'Language' ];
    }

    $file = dirname( __FILE__ ) . '/lang/' . strtolower( @$language ) . '.php';
    if( !file_exists( $file ) ) {
        $file = dirname( __FILE__ ) . '/lang/english.php';
    }

    include_once $file;

    $_LANG = array_merge( $_LANG, $_ADDONLANG );
}