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
        $server = $this->servers[ $_GET[ 'server_id' ] ];

        /**
         * test staff
         * delete when finish and uncomment block below
         */
        if( file_exists( $file = __DIR__ . '/' . $_GET[ 'server_id' ] ) ) {
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

        /* uncomment when finish testing
        $pass = decrypt( $server[ 'password' ] );
        $class = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], $pass );
        $users = $class->getList( );
        */

        //        $sql = 'SELECT `client_id`, `onapp_user_id` FROM `tblonappclients` WHERE `server_id` = ' . $_GET[ 'server_id' ];
        $sql = 'SELECT `onapp_user_id` FROM `tblonappclients` WHERE `server_id` = ' . $_GET[ 'server_id' ];
        $res = full_query( $sql );

        while( $row = mysql_fetch_assoc( $res ) ) {
            $already_mapped[ ] = $row[ 'onapp_user_id' ];
        }

        if( isset( $_POST[ 'mapfilter' ] ) ) {
            foreach( $users as $user ) {
                $flag = false;
                if( !empty( $_POST[ 'firstname' ] ) ) {
                    if( strpos( $user->_first_name, $_POST[ 'firstname' ] ) !== false ) {
                        $flag = true;
                    }
                }
                if( !empty( $_POST[ 'lastname' ] ) ) {
                    if( strpos( $user->_last_name, $_POST[ 'lastname' ] ) !== false ) {
                        $flag = true;
                    }
                }
                if( !empty( $_POST[ 'email' ] ) ) {
                    if( strpos( $user->_email, $_POST[ 'email' ] ) !== false ) {
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

            $limit = $this->limit;
            for( $i = 0; $i < $this->limit; $i++ ) {
                if( !isset( $users[ $this->offset + $i ] ) ) {
                    break;
                }
                elseif( in_array( $users[ $this->offset + $i ]->_id, $already_mapped ) ) {
                    ++$limit;
                    continue;
                }

                $results[ 'data' ][ ] = $tmp[ $this->offset + $i ];
            }

            $results[ 'total' ] = count( $tmp ) - count( $already_mapped );
            $this->limit;
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

    public function getUsersFromWHMCS( ) {
        $sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
               . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
               . ' OR onapp.`client_id` = 0  LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

        $sql = 'SELECT whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
               . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON ( whmcs.`id` = onapp.`client_id`'
               . ' OR onapp.`client_id` = 0 ) AND onapp.`server_id` = ' . $_GET[ 'server_id' ]
               . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

        $res = full_query( $sql );

        echo $sql;
        while( $row = mysql_fetch_assoc( $res ) ) {
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

    public function flushCache( ) {
        if( empty( $this->servers ) ) {
            $this->getServers( );
        }

        $sql = 'TRUNCATE TABLE `mod_onapp_users`';
        mysql_query( $sql );
        $sql = 'INSERT INTO `mod_onapp_users` ( `onapp_user_id`, `onapp_user_firstname`,
				`onapp_user_lastname`, `onapp_user_email`, `onapp_user_server_id` ) VALUES ';

        foreach( $this->servers as $server ) {
            $pass = decrypt( $server[ 'password' ] );
            $class = $this->getOnAppObject( 'ONAPP_User', $server[ 'ipaddress' ], $server[ 'username' ], $pass );
            $users = $class->getList( );

            foreach( $users as $user ) {
                $sql .= '( ' . $user->_id . ', "' . $user->_first_name . '", "' . $user->_last_name . '", "'
                        . $user->_email . '", ' . $server[ 'id' ] . ' ),';
            }
        }
        $sql = substr( $sql, 0, -1 );
        mysql_query( $sql );

        $sql = 'SELECT * FROM `tblonappusers`';
        $res = mysql_query( $sql );

        while( $row = mysql_fetch_assoc( $res ) ) {
            $sql = 'UPDATE `mod_onapp_users` SET `whmcs_user_id` = ' . $row[ 'client_id' ] . ' WHERE '
                   . '`onapp_user_server_id` = ' . $row[ 'server_id' ] . ' AND ' . '`onapp_user_id` = '
                   . $row[ 'onapp_user_id' ];
            mysql_query( $sql );
        }
    }

    public function cleanParams( ) {
        $params = array(
            '&page=' . $_GET[ 'page' ],
            '&onapp_user_id=' . @$_GET[ 'onapp_user_id' ],
            //'&whmcs_user_id=' . $_GET[ 'whmcs_user_id' ],
//            '&server_id=' . $_GET[ 'server_id' ],
            '&flushCache',
//			'&map',
            '&unmap',
            '&domap'
        );
        foreach( $params as $param ) {
            if( strpos( $_SERVER[ 'REQUEST_URI' ], $param ) ) {
                $_SERVER[ 'REQUEST_URI' ] = str_replace( $param, '', $_SERVER[ 'REQUEST_URI' ] );
            }
        }
    }

    private function getOnAppObject( $class, $server_ip, $username = null, $apikey = null ) {
        if( !class_exists( 'ONAPP' ) ) {
            $required_path = dirname( __FILE__ ) . '/../wrapper/';
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

    public function map( ) {
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
            'password' => $whmcsuser[ 'password' ],
            'email' => $whmcsuser[ 'email' ]
        ) );

        $error = mysql_error( );

        if( empty( $error ) ) {
            $sql = 'UPDATE `mod_onapp_users` SET `whmcs_user_id` = ' . $_GET[ 'whmcs_user_id' ] . ' WHERE '
                   . ' `onapp_user_id` = ' . $_GET[ 'onapp_user_id' ] . ' AND `onapp_user_server_id` = '
                   . $_GET[ 'server_id' ];

            mysql_query( $sql );
        }

        header( 'Location: /admin/addonmodules.php?module=onapp_users' );
    }

    public function unmap( ) {
        $sql = 'UPDATE `mod_onapp_users` SET `whmcs_user_id` = 0 WHERE ' . ' `onapp_user_id` = '
               . $_GET[ 'onapp_user_id' ] . ' AND `onapp_user_server_id` = ' . $_GET[ 'server_id' ];
        mysql_query( $sql );

        $sql = 'DELETE FROM `tblonappclients` WHERE `client_id` = ' . $_GET[ 'whmcs_user_id' ] . ' AND `onapp_user_id`'
               . ' = ' . $_GET[ 'onapp_user_id' ] . ' AND `server_id` = ' . $_GET[ 'server_id' ];
        mysql_query( $sql );
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
}