<?php

class CURL {
	private $ch;

	private $defaultOptions = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_HEADER => false,
		CURLOPT_NOBODY => false,
		CURLOPT_USERAGENT => 'CURL',
	);

	private $customOptions = array( );

	public function __construct( ) {
		$cookiesFile = tempnam( '/tmp', 'CURL_' );
		$this->defaultOptions[ CURLOPT_COOKIEFILE ] = $cookiesFile;
		$this->defaultOptions[ CURLOPT_COOKIEJAR ] = $cookiesFile;
		$this->ch = curl_init( );
	}

	public function addOption( $name, $value ) {
		$this->customOptions[ $name ] = $value;
	}

	public function setLog( ) {
		$log = fopen( dirname( __FILE__ ) . '/CURL.log', 'a' );
		if( $log ) {
			fwrite( $log, str_repeat( '=', 80 ) . PHP_EOL );
			$this->addOption( CURLOPT_STDERR, $log );
			$this->addOption( CURLOPT_VERBOSE, true );
		}
	}

	public function put( $url = null ) {
		return $this->send( 'PUT', $url );
	}

	public function get( $url = null ) {
		return $this->send( 'GET', $url );
	}

	public function post( $url = null ) {
		return $this->send( 'POST', $url );
	}

	public function head( $url = null ) {
		return $this->send( 'HEAD', $url );
	}

	private function send( $method, $url ) {
		if( $url === null ) {
			if( !isset( $this->customOptions[ CURLOPT_URL ] ) || empty( $this->customOptions[ CURLOPT_URL ] ) ) {
				exit( 'empty url' );
			}
		}
		$this->addOption( CURLOPT_CUSTOMREQUEST, $method );
		$this->addOption( CURLOPT_URL, $url );
		return $this->exec( );
	}

	private function setOptions( ) {
		$options = $this->customOptions + $this->defaultOptions;
		//		$options = $this->defaultOptions + $this->customOptions;
		//		$options = array_merge( $this->defaultOptions, $this->customOptions );
		//		var_dump( $this->defaultOptions + $this->customOptions );
		//		var_dump( $this->defaultOptions );
		//		var_dump( $this->customOptions );
		//		var_dump( $options );
		curl_setopt_array( $this->ch, $options );
	}

	private function exec( ) {
		$this->setOptions( );
		$result = curl_exec( $this->ch );
		//		$this->destroy( );
		return $result;
	}
}

class MyCurl {
	public $getHeaders = true; //headers will be added to output
	public $getContent = true; //contens will be added to output
	public $followRedirects = true; //should the class go to another URL, if the current is "HTTP/1.1 302 Moved Temporarily"

	private $fCookieFile;
	private $fSocket;

	public function __construct( ) {
		$this->fCookieFile = tempnam( '/tmp', 'g_' );
		$this->init( );
	}

	public function init( ) {
		return $this->fSocket = curl_init( );
	}

	public function setopt( $opt, $value ) {
		return curl_setopt( $this->fSocket, $opt, $value );
	}

	public function load_defaults( ) {
		$this->setopt( CURLOPT_RETURNTRANSFER, true );
		//$this->setopt( CURLOPT_FOLLOWLOCATION, $this->followRedirects );
		//$this->setopt( CURLOPT_REFERER, "http://google.com" );
		//$this->setopt( CURLOPT_VERBOSE, false );
		$this->setopt( CURLOPT_SSL_VERIFYPEER, false );
		$this->setopt( CURLOPT_SSL_VERIFYHOST, false );
		$this->setopt( CURLOPT_HEADER, $this->getHeaders );
		$this->setopt( CURLOPT_NOBODY, !$this->getContent );
		$this->setopt( CURLOPT_COOKIEJAR, $this->fCookieFile );
		$this->setopt( CURLOPT_COOKIEFILE, $this->fCookieFile );
		$this->setopt( CURLOPT_USERAGENT, 'MyCurl' );
		//		$this->setopt( CURLOPT_POST, 1 );
		//		$this->setopt( CURLOPT_CUSTOMREQUEST, 'POST' );
		/*
		$fp = fopen( dirname( __FILE__ ) . '/CURL.log', 'a' );
		if( $fp ) {
			$this->setopt( CURLOPT_STDERR, $fp );
		}
		*/
	}

	public function destroy( ) {
		return curl_close( $this->fSocket );
	}

	public function head( $url ) {
		if( $this->fSocket ) {
			$this->getHeaders = true;
			$this->getContent = false;
			$this->load_defaults( );
			$this->setopt( CURLOPT_POST, 0 );
			$this->setopt( CURLOPT_CUSTOMREQUEST, 'HEAD' );
			$this->setopt( CURLOPT_URL, $url );

			$result = curl_exec( $this->fSocket );
			$this->destroy( );
			return $result;
		}
		return 0;
	}

	public function put( $url ) {
		if( $this->fSocket ) {
			//			$this->getHeaders = true;
			//			$this->getContent = false;
			$this->load_defaults( );
			//			$this->setopt( CURLOPT_POST, 0 );
			$this->setopt( CURLOPT_CUSTOMREQUEST, 'PUT' );
			$this->setopt( CURLOPT_URL, $url );
			//$this->setopt( CURLOPT_POSTFIELDS, '{"user":{"password":"123321","password_confirmation":"123321"}}' );

			$result = curl_exec( $this->fSocket );
			var_dump( curl_error( $this->fSocket ) );
			$this->destroy( );

			return $result;
		}
		return 0;
	}

	public function get( $url ) {
		if( $this->fSocket ) {
			$this->load_defaults( );
			$this->setopt( CURLOPT_POST, 0 );
			$this->setopt( CURLOPT_CUSTOMREQUEST, 'GET' );
			$this->setopt( CURLOPT_URL, $url );
			$result = curl_exec( $this->fSocket );
			$this->destroy( );
			return $result;
		}
		return 0;
	}

	public function post( $url, $post_data, $arr_headers = array( ), &$http_code ) {
		if( $this->fSocket ) {
			$post_data = $this->compile_post_data( $post_data );
			$this->load_defaults( );
			if( !empty( $post_data ) ) {
				$this->setopt( CURLOPT_POSTFIELDS, $post_data );
			}

			if( !empty( $arr_headers ) ) {
				$this->setopt( CURLOPT_HTTPHEADER, $arr_headers );
			}

			$this->setopt( CURLOPT_URL, $url );

			$result = curl_exec( $this->fSocket );
			$http_code = curl_getinfo( $this->fSocket, CURLINFO_HTTP_CODE );
			$this->destroy( );
			return $result;
		}
		return 0;
	}

	public function compile_post_data( $post_data ) {
		$o = "";
		if( !empty( $post_data ) ) {
			foreach( $post_data as $k => $v ) $o .= $k . "=" . urlencode( $v ) . "&";
		}
		return substr( $o, 0, -1 );
	}

	public function get_parsed( $result, $bef, $aft = "" ) {
		$line = 1;
		$len = strlen( $bef );
		$pos_bef = strpos( $result, $bef );
		if( $pos_bef === false ) {
			return "";
		}
		$pos_bef += $len;

		if( empty( $aft ) ) { //try to search up to the end of line
			$pos_aft = strpos( $result, "\n", $pos_bef );
			if( $pos_aft === false ) {
				$pos_aft = strpos( $result, "\r\n", $pos_bef );
			}
		}
		else {
			$pos_aft = strpos( $result, $aft, $pos_bef );
		}

		if( $pos_aft !== false ) {
			$rez = substr( $result, $pos_bef, $pos_aft - $pos_bef );
		}
		else {
			$rez = substr( $result, $pos_bef );
		}

		return $rez;
	}
}