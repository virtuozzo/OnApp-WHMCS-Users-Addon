<?php

include_once 'classes/Addon.php';

function onapp_users_output( $vars ) {
	global $templates_compiledir, $customadminpath;

	include_once ROOTDIR . '/includes/smarty/Smarty.class.php';
	$smarty = new Smarty( );
	$compile_dir = file_exists( $templates_compiledir ) ? $templates_compiledir : ROOTDIR . '/' . $templates_compiledir;
	$smarty->compile_dir = $compile_dir;
	$smarty->template_dir = ROOTDIR . '/' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/onapp_users_addon/';

	if( !file_exists( $smarty->template_dir ) ) {
		$msg = 'Copy folder ' . ROOTDIR . '/' . $customadminpath . '/templates/v4/onapp_users_addon to '
			   . ROOTDIR . '/' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/';
		exit( $msg );
	}

	$base_url = $_SERVER[ 'SCRIPT_NAME' ] . '?module=' . $_GET[ 'module' ];
	$vars[ '_lang' ][ 'JSMessages' ] = json_encode( $vars[ '_lang' ][ 'JSMessages' ] );
	$smarty->assign( 'LANG', $vars[ '_lang' ] );
	$smarty->assign( 'BASE_CSS', '../' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/onapp_users_addon' );
	$smarty->assign( 'BASE_JS', '../modules/addons/onapp_users/js' );
	$smarty->assign( 'BASE', $base_url );

	$module = new OnApp_Users_Addon( $smarty );

	if( isset( $_SESSION[ 'onapp_addon' ][ 'filter' ] ) && ( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'filter' ] == 'main' ) ) {
		$data = $module->filterMain( );
		$smarty->assign( 'whmcs_users', $data[ 'data' ] );
	}
	else {
		if( isset( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'info' ) ) {
			if( !isset( $_GET[ 'onapp_user_id' ] ) ) {
				$data = $module->getUsersFromWHMCS( $_GET[ 'whmcs_user_id' ] );
				$smarty->assign( 'whmcs_user', $data[ 'data' ] );

				$data = $module->getUsersFromOnApp( );
				$smarty->assign( 'onapp_users', $data[ 'data' ] );
			}
			else {
				$data = $module->getUserData( );
				$smarty->assign( 'whmcs_user', $data[ 'whmcs_user' ] );
				$smarty->assign( 'onapp_user', $data[ 'onapp_user' ] );
			}
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