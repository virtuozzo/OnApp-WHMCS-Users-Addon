$( document ).ready( function() {
	// bind actions
	$( '.unmap' ).bind( 'click', function() {
		if( confirm( 'Do you really want to unmap user?' ) ) {
			return true;
		}
		else {
			return false;
		}
	} );
	$( '.mapserver' ).bind( 'change', function() {
        var go = document.location.href.replace( /&server_id=\d+/, '' );
        go += '&server_id=' + this.value;
        console.log( go );

        document.location = go;
	});


	$( '#tab0' ).bind( 'click', function() {
		$( this ).toggleClass( 'tabselected' );
		$( '#tab0box' ).slideToggle( 300 );
		return false;
	} );
	$( '#clients' ).bind( 'change', function() {
		$( '#servers' ).toggle();
	} );

	$( "select[class$='whmcs_user']" ).bind( 'change', function() {
		$( this ).parents( 'tr:first' ).find( 'select' ).val( $( this ).val() );
	} );
	$( 'select#page' ).bind( 'change', function() {
		var el = $( 'select#page' );
		var url = el.parents( 'form' ).attr( 'action' );
		url += '&' + el.attr( 'name' ) + '=' + el.val();
		document.location.href = url;
	} );
	$( '#page-go' ).bind( 'click', function() {
		var el = $( 'select#page' );
		var url = el.parents( 'form' ).attr( 'action' );
		url += '&' + el.attr( 'name' ) + '=' + el.val();
		document.location.href = url;
	} );
	$( 'a.map' ).bind( 'click', function() {
		var href = $( this ).attr( 'href' );
		href += '&whmcs_user_id=' + $( this ).parents( 'tr' ).find( 'select:first' ).val();
		$( this ).attr( 'href', href );
	} );
	$( '#servers' ).hide();

	// set default values
	$( 'span.connected' ).each( function( i, el ) {
		$( el ).parents( 'tr' ).find( 'select' ).val( $( el ).text() );
		$( el ).parents( 'tr' ).find( 'select' ).attr( 'disabled', 'disabled' );
	} );

	var pcre = /server_id=(\d+)/;
	pcre = pcre.exec( document.location.search );
	if( pcre ) {
		$( '.mapserver' ).val( pcre[1] );
	}

	return;
} );


function getServerUser( id ) {
	console.log( id );
	$( '#user-onapp-text' ).val( $( '#onappuser' + id ).text() );
	$( '#user-onapp' ).val( id );
}

function getWHMCSUser( id ) {
	console.log( id );
	$( '#user-whmcs-text' ).val( $( '#whmcsuser' + id ).text() );
	$( '#user-whmcs' ).val( id );
}