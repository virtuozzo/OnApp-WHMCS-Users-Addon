$( document ).ready( function() {
	// bind actions
	$( '.unmap' ).bind( 'click', function() {
		if( confirm( LANG.UnmapAlert ) ) {
			return true;
		}
		else {
			return false;
		}
	} );
	$( '.mapserver' ).bind( 'change', function() {
        var go = document.location.href.replace( /&server_id=\d+/, '' );
        go += '&server_id=' + this.value;
        document.location = go;
	});
	$( '#tab0' ).bind( 'click', function() {
		$( this ).toggleClass( 'tabselected' );
		$( '#tab0box' ).slideToggle( 300 );
		return false;
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

	// set default values
	var pcre = /server_id=(\d+)/;
	pcre = pcre.exec( document.location.search );
	if( pcre ) {
		$( '.mapserver' ).val( pcre[1] );
	}
} );