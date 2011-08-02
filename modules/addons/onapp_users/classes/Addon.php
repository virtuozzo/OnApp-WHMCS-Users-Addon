<?php

class OnApp_Users_Addon {
	private $smarty = null;
	private $lang = null;
	private $servers = array( );
	private $limit = 10;
	private $offset = 0;

	public function __construct( &$smarty = null ) {
		$this->smarty = $smarty;
		$this->lang = $smarty->get_template_vars( 'LANG' );

		$this->smarty->assign( 'onapp_servers', $this->getServers( ) );
		if( isset( $_GET[ 'server_id' ] ) ) {
			$this->smarty->assign( 'server_id', $_GET[ 'server_id' ] );
		}
		else {
			$server = current( $this->servers );
			$smarty->assign( 'server_id', $_GET[ 'server_id' ] = $server[ 'id' ] );
		}

		if( isset( $_POST[ 'blockops' ] ) && isset( $_POST[ 'selection' ] ) ) {
			$this->blockOperations( );
		}

		if( isset( $_GET[ 'filterreset' ] ) ) {
			$this->resetFilter( );
		}
		else {
			if( isset( $_POST[ 'filter' ] ) ) {
				$this->setFilter( );
			}
			elseif( isset( $_SESSION[ 'onapp_addon' ][ 'filter' ] ) ) {
				$this->smarty->assign( 'filter', $_SESSION[ 'onapp_addon' ][ 'filter' ] );
			}
			else {
				$this->smarty->assign( 'filter', null );
			}
		}

		switch( $_GET[ 'action' ] ) {
			case 'info':
				if( !isset( $_GET[ 'onapp_user_id' ] ) ) {
					$smarty->assign( 'map', true );
				}
				else {
					$smarty->assign( 'info', true );
				}
				break;

			case 'domap':
				$this->map( );
				break;

			case 'unmap':
				$this->unmap( );
				break;

			case 'activate':
				$this->activate( );
				break;

			case 'syncdata':
				$this->syncData( );
				break;

			case 'syncauth':
				$this->syncAuth( );
				break;

			case 'suspend':
				$this->suspend( );
				break;
		}

		if( !isset( $_GET[ 'page' ] ) ) {
			$_GET[ 'page' ] = 1;
		}

		$this->offset = $this->limit * ( $_GET[ 'page' ] - 1 );
	}

	public function getServers( ) {
		$sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
			   . ' FROM `tblservers` WHERE `type` = "onapp" OR `type` = "onappusers"';
		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			$this->servers[ $row[ 'id' ] ] = $this->getServerData( $row );
		}

		return $this->servers;
	}

	public function getUsersFromOnApp( ) {
		$server = $this->servers[ $_GET[ 'server_id' ] ];

		$class = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$users = $class->getList( );

		$sql = 'SELECT `onapp_user_id` FROM `tblonappclients` WHERE `server_id` = ' . $_GET[ 'server_id' ];
		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			$already_mapped[ ] = $row[ 'onapp_user_id' ];
		}

		if( isset( $_SESSION[ 'onapp_addon' ][ 'filter' ] ) && ( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'filter' ] == 'map' ) ) {
			foreach( $users as $user ) {
				$flag = false;
				if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'firstname' ] ) ) {
					if( strpos( strtolower( $user->_first_name ), strtolower( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'firstname' ] ) ) !== false ) {
						$flag = true;
					}
				}
				if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'lastname' ] ) ) {
					if( strpos( strtolower( $user->_last_name ), strtolower( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'lastname' ] ) ) !== false ) {
						$flag = true;
					}
				}
				if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'email' ] ) ) {
					if( strpos( strtolower( $user->_email ), strtolower( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'email' ] ) ) !== false ) {
						$flag = true;
					}
				}

				if( $flag ) {
					$tmp[ ] = $user;
				}
			}

			$limit = $this->limit;
			for( $i = 0; $i < $limit; $i++ ) {
				if( !isset( $tmp[ $this->offset + $i ] ) ) {
					break;
				}
				elseif( in_array( $tmp[ $this->offset + $i ]->_id, $already_mapped ) ) {
					++$limit;
					continue;
				}

				$results[ 'data' ][ ] = $tmp[ $this->offset + $i ];
			}

			$results[ 'total' ] = count( $tmp );
			$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
			$results[ 'current' ] = $_GET[ 'page' ];

			if( $_GET[ 'page' ] > 1 ) {
				$results[ 'prev' ] = $_GET[ 'page' ] - 1;
			}

			if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
				$results[ 'next' ] = $_GET[ 'page' ] + 1;
			}
		}
		else {
			$limit = $this->limit;
			for( $i = 0; $i < $limit; $i++ ) {
				if( !isset( $users[ $this->offset + $i ] ) ) {
					break;
				}
				elseif( in_array( $users[ $this->offset + $i ]->_id, $already_mapped ) ) {
					++$limit;
					continue;
				}

				$results[ 'data' ][ ] = $users[ $this->offset + $i ];
			}

			$results[ 'total' ] = count( $users ) - count( $already_mapped );
			$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
			$results[ 'current' ] = $_GET[ 'page' ];

			if( $_GET[ 'page' ] > 1 ) {
				$results[ 'prev' ] = $_GET[ 'page' ] - 1;
			}

			if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
				$results[ 'next' ] = $_GET[ 'page' ] + 1;
			}
		}

		return $results;
	}

	public function getUsersFromWHMCS( $id = false ) {
		if( $id ) {
			$sql = 'SELECT whmcs.* FROM `tblclients` AS whmcs WHERE whmcs.`id` = ' . $id . ' LIMIT 1';

			$result[ 'data' ] = mysql_fetch_assoc( full_query( $sql ) );
			return $result;
		}

		$sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
			   . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON ( whmcs.`id` = onapp.`client_id`'
			   . ' OR onapp.`client_id` = 0 ) AND onapp.`server_id` = ' . $_GET[ 'server_id' ]
			   . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			if( !is_null( $row[ 'server_id' ] ) ) {
				$this->checkUser( $row );
				$row[ 'mapped' ] = true;
			}
			$results[ 'data' ][ $row[ 'id' ] ] = $row;
		}

		$sql = 'SELECT count(*) FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
			   . ' OR onapp.`client_id` = 0';
		$res = mysql_query( $sql );
		$results[ 'total' ] = mysql_result( $res, 0 );

		$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
		$results[ 'current' ] = $_GET[ 'page' ];

		if( $_GET[ 'page' ] > 1 ) {
			$results[ 'prev' ] = $_GET[ 'page' ] - 1;
		}

		if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
			$results[ 'next' ] = $_GET[ 'page' ] + 1;
		}

		return $results;
	}

	public function getUserData( ) {
		$sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
			   . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
			   . ' WHERE onapp.`server_id` = ' . $_GET[ 'server_id' ] . ' AND whmcs.`id` = ' . $_GET[ 'whmcs_user_id' ]
			   . ' LIMIT 1';

		$result[ 'whmcs_user' ] = mysql_fetch_assoc( full_query( $sql ) );

		$server = $this->servers[ $result[ 'whmcs_user' ][ 'server_id' ] ];
		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $result[ 'whmcs_user' ][ 'onapp_user_id' ] );
		$result[ 'onapp_user' ] = $user->_obj;

		return $result;
	}

	public function filterMain( ) {
		$where = '';
		$rules = array( );

		if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'userid' ] ) ) {
			$rules[ ] = '`id` LIKE "%' . $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'userid' ] . '%"';
		}
		if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'firstname' ] ) ) {
			$rules[ ] = '`firstname` LIKE "%' . $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'firstname' ] . '%"';
		}
		if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'lastname' ] ) ) {
			$rules[ ] = '`lastname` LIKE "%' . $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'lastname' ] . '%"';
		}
		if( !empty( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'email' ] ) ) {
			$rules[ ] = 'whmcs.`email` LIKE "%' . $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'email' ] . '%"';
		}
		if( isset( $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'filtermapped' ] ) ) {
			$rules[ ] = 'onapp.`server_id` = ' . $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'server_id' ];
		}

		if( count( $rules ) ) {
			$where = ' WHERE ' . implode( ' AND ', $rules );
		}

		$sql = 'SELECT SQL_CALC_FOUND_ROWS whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
			   . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
			   . ' OR onapp.`client_id` = 0 ' . $where . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

		$res = mysql_query( $sql );

		$results = array( );
		while( $row = mysql_fetch_assoc( $res ) ) {
			if( !is_null( $row[ 'server_id' ] ) ) {
				$this->checkUser( $row );
				if( $row[ 'server_id' ] == $_SESSION[ 'onapp_addon' ][ 'filter' ][ 'server_id' ] ) {
					$row[ 'mapped' ] = true;
				}
			}
			$results[ 'data' ][ ] = $row;
		}

		$results[ 'total' ] = mysql_result( mysql_query( 'SELECT FOUND_ROWS( )' ), 0 );

		$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
		$results[ 'current' ] = $_GET[ 'page' ];

		if( $_GET[ 'page' ] > 1 ) {
			$results[ 'prev' ] = $_GET[ 'page' ] - 1;
		}

		if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
			$results[ 'next' ] = $_GET[ 'page' ] + 1;
		}

		return $results;
	}

	private function map( ) {
		$sql = 'SELECT COUNT(*) FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
			   . ' AND client_id = ' . $_GET[ 'whmcs_user_id' ] . ' AND onapp_user_id = ' . $_GET[ 'onapp_user_id' ];
		$cnt = mysql_result( mysql_query( $sql ), 0 );

		if( $cnt > 0 ) {
			$this->smarty->assign( 'msg', true );
			$this->smarty->assign( 'msg_text', $this->lang[ 'MapedError' ] . $this->lang[ 'MapedErrorExists' ] );
			$this->smarty->assign( 'msg_ok', false );

			return;
		}

		$sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
			   . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
		$res = full_query( $sql );
		$whmcsuser = mysql_fetch_assoc( $res );

		$server = $this->getServerData( );

		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $_GET[ 'onapp_user_id' ] );
		$user->_password = $user->_password_confirmation = $whmcsuser[ 'password' ];
		$user->save( );

		$this->smarty->assign( 'msg', true );
		if( is_null( $user->error ) ) {
			$this->smarty->assign( 'msg_text', $this->lang[ 'MapedSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );

			insert_query( 'tblonappclients', array(
					'server_id' => $_GET[ 'server_id' ],
					'client_id' => $_GET[ 'whmcs_user_id' ],
					'onapp_user_id' => $_GET[ 'onapp_user_id' ],
					'password' => encrypt( $whmcsuser[ 'password' ] ),
					'email' => $user->_obj->_login
				) );
		}
		else {
			$msg = $user->error;

			$this->smarty->assign( 'msg_text', $this->lang[ 'MapedError' ] . $msg );
			$this->smarty->assign( 'msg_ok', false );
		}
	}

	private function unmap( $id = null ) {
		if( !is_null( $id ) ) {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $id . ' LIMIT 1';

			$_GET[ 'onapp_user_id' ] = mysql_result( mysql_query( $sql ), 0 );
			$_GET[ 'whmcs_user_id' ] = $id;

			$blockops = true;
		}
		else {
			$blockops = false;
		}

		$sql = 'DELETE FROM `tblonappclients` WHERE `client_id` = ' . $_GET[ 'whmcs_user_id' ] . ' AND `onapp_user_id`'
			   . ' = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ];
		mysql_query( $sql );

		$error = mysql_error( );
		$this->smarty->assign( 'msg', true );
		if( empty( $error ) ) {
			if( $blockops ) {
				return true;
			}

			$this->smarty->assign( 'msg_text', $this->lang[ 'UnmapedSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}
		else {
			if( $blockops ) {
				return false;
			}

			$this->smarty->assign( 'msg_text', $this->lang[ 'UnmapedError' ] . $error );
			$this->smarty->assign( 'msg_ok', false );

			$_GET[ 'action' ] = 'info';
			$this->smarty->assign( 'info', true );
		}
	}

	private function activate( $id = null ) {
		if( !is_null( $id ) ) {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $id . ' LIMIT 1';

			$_GET[ 'onapp_user_id' ] = mysql_result( mysql_query( $sql ), 0 );

			$blockops = true;
		}
		else {
			$blockops = false;
		}

		$server = $this->getServerData( );

		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $_GET[ 'onapp_user_id' ] );

		$user->activate_user( );

		$this->smarty->assign( 'msg', true );
		if( is_null( $user->error ) ) {
			if( $blockops ) {
				return true;
			}
			$this->smarty->assign( 'msg_text', $this->lang[ 'ActivatedSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}
		else {
			if( $blockops ) {
				return false;
			}
			$msg = $user->error;
			$this->smarty->assign( 'msg_text', $this->lang[ 'ActivatedError' ] . $msg );
			$this->smarty->assign( 'msg_ok', false );
		}

		$_GET[ 'action' ] = 'info';
		$this->smarty->assign( 'info', true );
	}

	private function suspend( $id = null ) {
		if( !is_null( $id ) ) {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $id . ' LIMIT 1';

			$_GET[ 'onapp_user_id' ] = mysql_result( mysql_query( $sql ), 0 );

			$blockops = true;
		}
		else {
			$blockops = false;
		}

		$server = $this->getServerData( );

		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $_GET[ 'onapp_user_id' ] );
		$user->suspend( );

		$this->smarty->assign( 'msg', true );
		if( is_null( $user->error ) ) {
			if( $blockops ) {
				return true;
			}

			$this->smarty->assign( 'msg_text', $this->lang[ 'SuspendSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}
		else {
			if( $blockops ) {
				return false;
			}

			$msg = $user->error;

			$this->smarty->assign( 'msg_text', $this->lang[ 'SuspendError' ] . $msg );
			$this->smarty->assign( 'msg_ok', false );
		}

		$_GET[ 'action' ] = 'info';
		$this->smarty->assign( 'info', true );
	}

	private function syncData( $id = null ) {
		if( !is_null( $id ) ) {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $id . ' LIMIT 1';

			$_GET[ 'onapp_user_id' ] = mysql_result( mysql_query( $sql ), 0 );
			$_GET[ 'whmcs_user_id' ] = $id;

			$blockops = true;
		}
		else {
			$blockops = false;
		}

		$sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
			   . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
		$res = full_query( $sql );
		$whmcsuser = mysql_fetch_assoc( $res );

		$server = $this->getServerData( );

		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $_GET[ 'onapp_user_id' ] );
		$user->_first_name = $whmcsuser[ 'firstname' ];
		$user->_last_name = $whmcsuser[ 'lastname' ];
		$user->_email = $whmcsuser[ 'email' ];
		$user->save( );

		$this->smarty->assign( 'msg', true );
		if( is_null( $user->error ) ) {
			if( $blockops ) {
				return true;
			}

			$this->smarty->assign( 'msg_text', $this->lang[ 'DataSyncedSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}
		else {
			if( $blockops ) {
				return false;
			}

			$msg = $user->error;
			$this->smarty->assign( 'msg_text', $this->lang[ 'DataSyncedError' ] . $msg );
			$this->smarty->assign( 'msg_ok', false );
		}

		$_GET[ 'action' ] = 'info';
		$this->smarty->assign( 'info', true );
	}

	private function syncAuth( $id = null ) {
		if( !is_null( $id ) ) {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $id . ' LIMIT 1';

			$_GET[ 'onapp_user_id' ] = mysql_result( mysql_query( $sql ), 0 );
			$_GET[ 'whmcs_user_id' ] = $id;

			$sql = 'SELECT `password`, `email` FROM tblonappclients '
				   . 'WHERE `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ]
				   . ' AND `client_id` = ' . $_GET[ 'whmcs_user_id' ];
			$res = full_query( $sql );
			$onapp_user = mysql_fetch_assoc( $res );

			$blockops = true;
		}
		else {
			$sql = 'SELECT onapp_user_id FROM tblonappclients WHERE server_id = ' . $_GET[ 'server_id' ]
				   . ' AND client_id = ' . $_GET[ 'whmcs_user_id' ] . ' LIMIT 1';

			$sql = 'SELECT `password`, `email` FROM tblonappclients '
				   . 'WHERE `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ]
				   . ' AND `client_id` = ' . $_GET[ 'whmcs_user_id' ];
			$res = full_query( $sql );
			$onapp_user = mysql_fetch_assoc( $res );

			$blockops = false;
		}

		$server = $this->getServerData( );

		$headers = array( 'Accept: application/json', 'Content-type: application/json' );

		include_once 'CURL.php';
		$curl = new CURL( );
		$curl->addOption( CURLOPT_USERPWD, $onapp_user[ 'email' ] . ':' . decrypt( $onapp_user[ 'password' ] ) );
		$curl->addOption( CURLOPT_HTTPHEADER, $headers );
		$curl->addOption( CURLOPT_HEADER, true );

		$url = $server[ 'address' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json';

		$content = $curl->get( $url );

		$this->smarty->assign( 'msg', true );
		if( $curl->getRequestInfo( 'http_code' ) != 200 ) {
			$server = $this->getServerData( );

			$curl = new CURL( );
			$curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $server[ 'password' ] );
			$curl->addOption( CURLOPT_HTTPHEADER, $headers );
			$curl->addOption( CURLOPT_HEADER, true );

			$url = $server[ 'address' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json';
			$content = $curl->get( $url );

			if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
				$content = json_decode( $content );
				$user = $content->user;

				$sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
					   . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
				$res = full_query( $sql );
				$whmcsuser = mysql_fetch_assoc( $res );

				$sql = 'UPDATE `tblonappclients` SET `email` = "' . $user->login . '", '
					   . '`password` = "' . encrypt( $whmcsuser[ 'password' ] ) . '"  WHERE `server_id` = '
					   . $_GET[ 'server_id' ] . ' AND `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ];

				mysql_query( $sql );

				$data[ 'password' ] = $whmcsuser[ 'password' ];
				$data[ 'password_confirmation' ] = $whmcsuser[ 'password' ];
				$data = json_encode( array( 'user' => $data ) );

				$headers = array( 'Accept: application/json', 'Content-type: application/json', 'Content-Length: ' . strlen( $data ) );

				include_once 'CURL.php';
				$curl = new CURL( );
				$curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $server[ 'password' ] );
				$curl->addOption( CURLOPT_HTTPHEADER, $headers );
				$curl->addOption( CURLOPT_POSTFIELDS, $data );
				$curl->addOption( CURLOPT_HEADER, true );

				$content = $curl->put( $server[ 'address' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json' );
				if( $blockops ) {
					return true;
				}
				$this->smarty->assign( 'msg_text', $this->lang[ 'AuthSyncedSuccessfully' ] );
				$this->smarty->assign( 'msg_ok', true );
			}
			else {
				if( $blockops ) {
					return false;
				}
				$this->smarty->assign( 'msg_text', $this->lang[ 'AuthSyncedError' ] );
				$this->smarty->assign( 'msg_ok', false );
			}
		}
		else {
			if( $blockops ) {
				return true;
			}
			$this->smarty->assign( 'msg_text', $this->lang[ 'AuthSyncedSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}

		if( $blockops ) {
			return true;
		}

		$_GET[ 'action' ] = 'info';
		$this->smarty->assign( 'info', true );
	}

	private function getOnAppObject( $class, $server_ip, $username = null, $apikey = null ) {
		$obj = new $class;
		$obj->auth( $server_ip, $username, $apikey );

		return $obj;
	}

	private function checkUser( &$row ) {
		$server = $this->servers[ $row[ 'server_id' ] ];

		$user = $this->getOnAppObject( 'ONAPP_User', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$user->load( $row[ 'onapp_user_id' ] );
		$user = $user->_obj;

		if( is_null( $user ) ) {
			$row[ 'not_exist' ] = true;
		}
		elseif( $user->_status == 'deleted' ) {
			$row[ 'deleted' ] = true;
		}
	}

	private function getServerData( $server = null ) {
		if( is_null( $server ) ) {
			$sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
				   . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
			$res = full_query( $sql );
			$server = mysql_fetch_assoc( $res );
		}
		$server[ 'password' ] = decrypt( $server[ 'password' ] );

		if( !empty( $server[ 'ipaddress' ] ) ) {
			$server[ 'address' ] = $server[ 'ipaddress' ];
		}
		else {
			$server[ 'address' ] = $server[ 'hostname' ];
		}

		return $server;
	}

	private function setFilter( ) {
		foreach( $_POST as $name => $value ) {
			$_SESSION[ 'onapp_addon' ][ 'filter' ][ $name ] = $value;
		}
		$this->smarty->assign( 'filter', $_SESSION[ 'onapp_addon' ][ 'filter' ] );
	}

	private function resetFilter( ) {
		unset( $_SESSION[ 'onapp_addon' ][ 'filter' ] );
		header( 'Location: ' . str_replace( '&filterreset', '', $_SERVER[ 'REQUEST_URI' ] ) );
	}

	private function blockOperations( ) {
		set_time_limit( 0 );

		$result = true;
		foreach( $_POST[ 'selection' ] as $id ) {
			$action = $_POST[ 'blockops' ];
			$result = $result && $this->$action( $id );
		}

		if( $result ) {
			$this->smarty->assign( 'msg_text', $this->lang[ 'BlockOpsSuccessfully' ] );
			$this->smarty->assign( 'msg_ok', true );
		}
		else {
			$this->smarty->assign( 'msg_text', $this->lang[ 'BlockOpsError' ] );
			$this->smarty->assign( 'msg_ok', false );
		}
	}
}