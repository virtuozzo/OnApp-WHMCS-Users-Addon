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

        if( isset( $_GET[ 'flushCache' ] ) ) {
            $this->flushCache( );
        }
        elseif( isset( $_GET[ 'domap' ] ) ) {
            $this->map( );
        }
        elseif( isset( $_GET[ 'unmap' ] ) ) {
            $this->unmap( );
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
        var_dump( __METHOD__ );

        $server = $this->servers[ $_GET[ 'server_id' ] ];

        /**
         * test staff
         * delete when finish and uncomment block below
         */
        if( file_exists( $file = __DIR__ . '/' . $_GET[ 'server_id' ] . '.srv' ) ) {
            var_dump( 'CACHE' );
            $required_path = __DIR__ . '/../wrapper/';
            require_once $required_path . 'User.php';
            $users = unserialize( file_get_contents( $file ) );
        }
        else {
            $pass = decrypt( $server[ 'password' ] );
            $class = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], $pass );
            $users = $class->getList( );

            file_put_contents( $file, serialize( $users ) );
        }

        // todo uncomment block below & remove block above
        /* uncomment when finish testing
        $pass = decrypt( $server[ 'password' ] );
        $class = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], $pass );
        $users = $class->getList( );
        */

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
        var_dump( __METHOD__ );

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
        var_dump( __METHOD__ );

        $sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
               . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
               . ' WHERE onapp.`server_id` = ' . $_GET[ 'server_id' ] . ' AND whmcs.`id` = ' . $_GET[ 'whmcs_user_id' ]
               . ' LIMIT 1';

        $result[ 'whmcs_user' ] = mysql_fetch_assoc( full_query( $sql ) );

        $server = $this->servers[ $result[ 'whmcs_user' ][ 'server_id' ] ];
        $user = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], decrypt( $server[ 'password' ] ) );
        $user->_loger->setDebug( true );
        $user->load( $result[ 'whmcs_user' ][ 'onapp_user_id' ] );
        $result[ 'onapp_user' ] = $user->_obj;

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
            '&info'
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
        $curl->addOption( CURLOPT_POSTFIELDS, $data );
        $curl->addOption( CURLOPT_HTTPHEADER, $headers );

        $contents = $curl->put( 'http://' . $server[ 'ipaddress' ] . '/users/' . $_GET[ 'onapp_user_id' ] . '.json' );

        $res = insert_query( 'tblonappclients', array(
            'server_id' => $_GET[ 'server_id' ],
            'client_id' => $_GET[ 'whmcs_user_id' ],
            'onapp_user_id' => $_GET[ 'onapp_user_id' ],
            'password' => encrypt( $whmcsuser[ 'password' ] ),
            'email' => $whmcsuser[ 'email' ]
        ) );

        $error = mysql_error( );

        if( empty( $error ) ) {
            $sql = 'UPDATE `mod_onapp_users` SET `whmcs_user_id` = ' . $_GET[ 'whmcs_user_id' ] . ' WHERE '
                   . ' `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `onapp_user_server_id` = '
                   . $_GET[ 'server_id' ];

            mysql_query( $sql );
        }
    }

    private function unmap( ) {
        var_dump( __METHOD__ );

        $sql = 'DELETE FROM `tblonappclients` WHERE `client_id` = ' . $_GET[ 'whmcs_user_id' ] . ' AND `onapp_user_id`'
               . ' = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ];
        mysql_query( $sql );
    }

    private function getOnAppObject( $class, $server_ip, $username = null, $apikey = null ) {
        $required_path = dirname( __FILE__ ) . '/../wrapper/';
        if( !class_exists( 'ONAPP' ) ) {
            require_once $required_path . 'ONAPP.php';
        }

        var_dump( "$class, $server_ip, $username, $apikey" );

        $required_file = str_replace( 'ONAPP_', '', $class ) . '.php';
        require_once $required_path . $required_file;

        $obj = new $class;
        $obj->auth( $server_ip, $username, $apikey );
        $obj->_loger->setDebug( true );

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