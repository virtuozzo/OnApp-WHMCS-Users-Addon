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
    } );
    $( '#tab0' ).bind( 'click', function() {
        $( this ).toggleClass( 'tabselected' );
        $( '#tab0box' ).slideToggle( 300 );
        return false;
    } );
    $( 'select#page' ).bind( 'change', function() {
        var go = document.location.href.replace( /&page=\d+/, '' );
        go += '&page=' + this.value;
        document.location.href = go;
    } );
    $( '#map-filter' ).change( function() {
        var checked = $( this ).attr( 'checked' );
        if( checked ) {
            var go = document.location.href.replace( /&filtermapped/, '' );
            go += '&filtermapped';
        }
        else {
            var go = document.location.href.replace( /&filtermapped/, '' );
        }
        document.location.href = go;
    } );

    // set default values
    var pcre = /server_id=(\d+)/;
    pcre = pcre.exec( document.location.search );
    if( pcre ) {
        $( '.mapserver' ).val( pcre[1] );
    }
    $( '#resetfilter' ).bind( 'click', function() {
        console.log( 'reset' );
        console.log( document.location );
        document.location.href = document.location.href;

        return;
        var go = document.location.href.replace( /&page=\d+/, '' );
        go += '&page=' + this.value;
        document.location.href = go;
    } );

    var pcre = /filtermapped/;
    pcre = pcre.exec( document.location.search );
    if( pcre ) {
        $( '#map-filter' ).attr( 'checked', 'checked' );
    }
} );