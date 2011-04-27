<?php

if( !function_exists( 'json_encode' ) ) {
    include_once 'JSON.php';
}

class OnApp_Users_Addon {
    private $smarty = null;
    private $servers = array( );
    private $limit = 10;
    private $offset = 0;

    public function __construct( &$smarty = null ) {
        $this->smarty = $smarty;

        if( isset( $_GET[ 'map' ] ) ) {
            $smarty->assign( 'map', true );
        }
        elseif( isset( $_GET[ 'info' ] ) ) {
            $smarty->assign( 'info', true );
        }
        elseif( isset( $_GET[ 'domap' ] ) ) {
            $this->map( );
        }
        elseif( isset( $_GET[ 'unmap' ] ) ) {
            $this->unmap( );
        }
        elseif( isset( $_GET[ 'activate' ] ) ) {
            $this->activate( );
        }
        elseif( isset( $_GET[ 'syncdata' ] ) ) {
            $this->syncData( );
        }
        elseif( isset( $_GET[ 'syncauth' ] ) ) {
            $this->syncAuth( );
        }
        elseif( isset( $_GET[ 'suspend' ] ) ) {
            $this->suspend( );
        }
        elseif( isset( $_GET[ 'signin' ] ) ) {
            $this->signin( );
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
            $this->servers[ $row[ 'id' ] ] = $row;
        }

        return $this->servers;
    }

    public function getUsersFromOnApp( ) {
        $server = $this->servers[ $_GET[ 'server_id' ] ];

        $pass = decrypt( $server[ 'password' ] );
        $class = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], $pass );
        $users = $class->getList( );

        $sql = 'SELECT `onapp_user_id` FROM `tblonappclients` WHERE `server_id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );

        while( $row = mysql_fetch_assoc( $res ) ) {
            $already_mapped[ ] = $row[ 'onapp_user_id' ];
        }

        if( isset( $_POST[ 'mapfilter' ] ) ) {
            foreach( $users as $user ) {
                $flag = false;
                if( !empty( $_POST[ 'firstname' ] ) ) {
                    if( strpos( strtolower( $user->_first_name ), strtolower( $_POST[ 'firstname' ] ) ) !== false ) {
                        $flag = true;
                    }
                }
                if( !empty( $_POST[ 'lastname' ] ) ) {
                    if( strpos( strtolower( $user->_last_name ), strtolower( $_POST[ 'lastname' ] ) ) !== false ) {
                        $flag = true;
                    }
                }
                if( !empty( $_POST[ 'email' ] ) ) {
                    if( strpos( strtolower( $user->_email ), strtolower( $_POST[ 'email' ] ) ) !== false ) {
                        $flag = true;
                    }
                }
                if( empty( $_POST[ 'firstname' ] ) && empty( $_POST[ 'lastname' ] ) && empty( $_POST[ 'email' ] ) ) {
                    $flag = true;
                }

                if( $flag ) {
                    $tmp[ ] = $user;
                }
            }

            $results[ 'data' ] = $tmp;
            $results[ 'total' ] = count( $results[ 'data' ] );
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
        $user = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], decrypt( $server[ 'password' ] ) );
        $user->_loger->setDebug( true );
        $user->load( $result[ 'whmcs_user' ][ 'onapp_user_id' ] );
        $result[ 'onapp_user' ] = (array)$user->_obj;

        return $result;
    }

    public function filterMain( ) {
        $where = '';
        $rules = array( );
        if( !empty( $_POST[ 'firstname' ] ) ) {
            $rules[ ] = '`firstname` LIKE "%' . $_POST[ 'firstname' ] . '%"';
        }
        if( !empty( $_POST[ 'lastname' ] ) ) {
            $rules[ ] = '`lastname` LIKE "%' . $_POST[ 'lastname' ] . '%"';
        }
        if( !empty( $_POST[ 'email' ] ) ) {
            $rules[ ] = 'whmcs.`email` LIKE "%' . $_POST[ 'email' ] . '%"';
        }

        if( count( $rules ) ) {
            $where = ' WHERE ' . implode( ' AND ', $rules );
        }

        $sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
               . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
               . ' OR onapp.`client_id` = 0 ' . $where;

        $res = mysql_query( $sql );

        $results = array( );
        while( $row = mysql_fetch_assoc( $res ) ) {
            $results[ 'data' ][ ] = $row;
        }

        $sql = 'SELECT count(*) FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
               . ' OR onapp.`client_id` = 0 ' . $where;
        $res = mysql_query( $sql );
        $results[ 'total' ] = mysql_result( $res, 0 );

        return $results;
    }

    public function cleanParams( ) {
        $params = array(
            '&page=' . $_GET[ 'page' ],
            '&onapp_user_id=' . @$_GET[ 'onapp_user_id' ],
            '&whmcs_user_id=' . $_GET[ 'whmcs_user_id' ],
            '&server_id=' . $_GET[ 'server_id' ],
            '&flushCache',
            '&map',
            '&unmap',
            '&domap',
            '&activate',
            '&syncdata',
            '&syncauth',
            '&info',
        );
        foreach( $params as $param ) {
            if( strpos( $_SERVER[ 'REQUEST_URI' ], $param ) ) {
                $_SERVER[ 'REQUEST_URI' ] = str_replace( $param, '', $_SERVER[ 'REQUEST_URI' ] );
            }
        }
    }

    private function map( ) {
        $sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
               . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
        $res = full_query( $sql );
        $whmcsuser = mysql_fetch_assoc( $res );

        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );
        $pass = decrypt( $server[ 'password' ] );

        $data[ 'password' ] = $whmcsuser[ 'password' ];
        $data[ 'password_confirmation' ] = $whmcsuser[ 'password' ];
        $data = json_encode( array( 'user' => $data ) );

        $headers = array( 'Accept: application/json', 'Content-type: application/json', 'Content-Length: ' . strlen( $data ) );

        include_once 'CURL.php';
        $curl = new CURL( );
        $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );
        $curl->addOption( CURLOPT_POSTFIELDS, $data );
        $curl->addOption( CURLOPT_HEADER, true );

        $content = $curl->put( 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json' );

        $this->smarty->assign( 'msg', true );
        if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'MapedSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );

            insert_query( 'tblonappclients', array(
                'server_id' => $_GET[ 'server_id' ],
                'client_id' => $_GET[ 'whmcs_user_id' ],
                'onapp_user_id' => $_GET[ 'onapp_user_id' ],
                'password' => encrypt( $whmcsuser[ 'password' ] ),
                'email' => $whmcsuser[ 'email' ]
            ) );
        }
        else {
            if( is_null( $tmp = json_decode( $content ) ) ) {
                $msg = $content;
            }
            else {
                $msg = $tmp->error;
            }

            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'MapedError' ] . $msg );
            $this->smarty->assign( 'msg_ok', false );
        }
    }

    private function unmap( ) {
        $sql = 'DELETE FROM `tblonappclients` WHERE `client_id` = ' . $_GET[ 'whmcs_user_id' ] . ' AND `onapp_user_id`'
               . ' = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ];
        mysql_query( $sql );

        $error = mysql_error( );
        $this->smarty->assign( 'msg', true );
        if( empty( $error ) ) {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'UnmapedSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );
        }
        else {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'UnmapedError' ] . $error );
            $this->smarty->assign( 'msg_ok', false );

            $this->cleanParams( );
            $_GET[ 'info' ] = true;
            $this->smarty->assign( 'info', true );
        }
    }

    private function activate( ) {
        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );
        $pass = decrypt( $server[ 'password' ] );

        $headers = array( 'Accept: application/json', 'Content-type: application/json' );

        include_once 'CURL.php';
        $curl = new CURL( );
        $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );
        $curl->addOption( CURLOPT_HEADER, true );

        $url = 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '/activate_user.json';
        $content = $curl->get( $url );

        $this->smarty->assign( 'msg', true );
        if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'ActivatedSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );
        }
        else {
            if( is_null( $tmp = json_decode( $content ) ) ) {
                $msg = $content;
            }
            else {
                $msg = $tmp->error;
            }

            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'ActivatedError' ] . $msg );
            $this->smarty->assign( 'msg_ok', false );
        }

        $this->cleanParams( );
        $_GET[ 'info' ] = true;
        $this->smarty->assign( 'info', true );
    }

    private function suspend( ) {
        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );
        $pass = decrypt( $server[ 'password' ] );

        $headers = array( 'Accept: application/json', 'Content-type: application/json' );

        include_once 'CURL.php';
        $curl = new CURL( );
        $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );
        $curl->addOption( CURLOPT_HEADER, true );

        $url = 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '/suspend.json';
        $content = $curl->get( $url );

        $this->smarty->assign( 'msg', true );
        if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'SuspendSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );
        }
        else {
            if( is_null( $tmp = json_decode( $content ) ) ) {
                $msg = $content;
            }
            else {
                $msg = $tmp->error;
            }

            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'SuspendError' ] . $msg );
            $this->smarty->assign( 'msg_ok', false );
        }

        $this->cleanParams( );
        $_GET[ 'info' ] = true;
        $this->smarty->assign( 'info', true );
    }

    private function syncData( ) {
        $sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
               . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
        $res = full_query( $sql );
        $whmcsuser = mysql_fetch_assoc( $res );

        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );
        $pass = decrypt( $server[ 'password' ] );

        $data[ 'first_name' ] = $whmcsuser[ 'firstname' ];
        $data[ 'last_name' ] = $whmcsuser[ 'lastname' ];
        $data[ 'email' ] = $whmcsuser[ 'email' ];

        $data = json_encode( array( 'user' => $data ) );
        $headers = array( 'Accept: application/json', 'Content-type: application/json', 'Content-Length: ' . strlen( $data ) );

        include_once 'CURL.php';
        $curl = new CURL( );
        $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );
        $curl->addOption( CURLOPT_POSTFIELDS, $data );
        $curl->addOption( CURLOPT_HEADER, true );

        $url = 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json';
        $content = $curl->put( $url );

        $this->smarty->assign( 'msg', true );
        if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'DataSyncedSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );
        }
        else {
            if( is_null( $tmp = json_decode( $content ) ) ) {
                $msg = $content;
            }
            else {
                $msg = '';
                foreach( $tmp as $k => $v ) {
                    $msg .= $k . ' ' . $v . '<br/>';
                }
            }

            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'DataSyncedError' ] . $msg );
            $this->smarty->assign( 'msg_ok', false );
        }

        $this->cleanParams( );
        $_GET[ 'info' ] = true;
        $this->smarty->assign( 'info', true );
    }

    private function signin( ) {
        $sql = 'SELECT `password`, `email` FROM tblonappclients '
               . 'WHERE `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ]
               . ' AND `client_id` = ' . $_GET[ 'whmcs_user_id' ];
        $res = full_query( $sql );
        $onapp_user = mysql_fetch_assoc( $res );

        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );

        $url = urlencode($onapp_user[ 'email' ]) . ':' . decrypt( $onapp_user[ 'password' ] )
               . '@' . $server[ 'ipaddress' ] . '/users/';
        header( 'Location: http://' . $url );
    }

    private function syncAuth( ) {
        $sql = 'SELECT `password`, `email` FROM tblonappclients '
               . 'WHERE `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ]
               . ' AND `client_id` = ' . $_GET[ 'whmcs_user_id' ];
        $res = full_query( $sql );
        $onapp_user = mysql_fetch_assoc( $res );

        $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
               . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );
        $server = mysql_fetch_assoc( $res );
        $pass = decrypt( $server[ 'password' ] );

        $headers = array( 'Accept: application/json', 'Content-type: application/json' );

        include_once 'CURL.php';
        $curl = new CURL( );
        $curl->addOption( CURLOPT_USERPWD, $onapp_user[ 'email' ] . ':' . decrypt( $onapp_user[ 'password' ] ) );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );
        $curl->addOption( CURLOPT_HEADER, true );

        $url = 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json';
        $content = $curl->get( $url );

        $this->smarty->assign( 'msg', true );
        if( $curl->getRequestInfo( 'http_code' ) != 200 ) {
            $sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
                   . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
            $res = full_query( $sql );
            $server = mysql_fetch_assoc( $res );
            $pass = decrypt( $server[ 'password' ] );

            $curl = new CURL( );
            $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
            $curl->addOption( CURLOPT_HTTPHEADER, $headers );
            $curl->addOption( CURLOPT_HEADER, true );

            $url = 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json';
            $content = $curl->get( $url );

            if( $curl->getRequestInfo( 'http_code' ) == 200 ) {
                $content = json_decode( $content );
                $user = $content->user;

                $sql = 'SELECT `id`, `firstname`, `lastname`, `email`, `password` FROM `tblclients`'
                       . ' WHERE `id` = ' . $_GET[ 'whmcs_user_id' ];
                $res = full_query( $sql );
                $whmcsuser = mysql_fetch_assoc( $res );

                $sql = 'UPDATE tblonappclients SET `email` = "' . $user->login . '", '
                       . '`password` = "' . encrypt( $whmcsuser[ 'password' ] ) . '"';

                mysql_query( $sql );

                $data[ 'password' ] = $whmcsuser[ 'password' ];
                $data[ 'password_confirmation' ] = $whmcsuser[ 'password' ];
                $data = json_encode( array( 'user' => $data ) );

                $headers = array( 'Accept: application/json', 'Content-type: application/json', 'Content-Length: ' . strlen( $data ) );

                include_once 'CURL.php';
                $curl = new CURL( );
                $curl->addOption( CURLOPT_USERPWD, $server[ 'username' ] . ':' . $pass );
                $curl->addOption( CURLOPT_HTTPHEADER, $headers );
                $curl->addOption( CURLOPT_POSTFIELDS, $data );
                $curl->addOption( CURLOPT_HEADER, true );

                $content = $curl->put( 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json' );
            }
            else {
                $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'AuthSyncedError' ] );
                $this->smarty->assign( 'msg_ok', false );
            }
        }
        else {
            $this->smarty->assign( 'msg_text', $GLOBALS[ '_LANG' ][ 'AuthSyncedSuccessfully' ] );
            $this->smarty->assign( 'msg_ok', true );
        }

        $this->cleanParams( );
        $_GET[ 'info' ] = true;
        $this->smarty->assign( 'info', true );
    }

    private function getOnAppObject( $class, $server_ip, $username = null, $apikey = null ) {
        $required_path = dirname( __FILE__ ) . '/../wrapper/';
        if( !class_exists( 'ONAPP' ) ) {
            require_once $required_path . 'ONAPP.php';
        }

        $required_file = str_replace( 'ONAPP_', '', $class ) . '.php';
        require_once $required_path . $required_file;

        $obj = new $class;
        $obj->auth( $server_ip, $username, $apikey );

        return $obj;
    }

    private function checkUser( &$row ) {
        $server = $this->servers[ $row[ 'server_id' ] ];
        $user = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], decrypt( $server[ 'password' ] ) );
        $user->load( $row[ 'onapp_user_id' ] );
        $user = $user->_obj;

        if( is_null( $user ) ) {
            $row[ 'not_exist' ] = true;
        }
        elseif( $user->_status == 'deleted' ) {
            $row[ 'deleted' ] = true;
        }
    }
}