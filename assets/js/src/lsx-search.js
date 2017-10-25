/**
 * LSX Search scripts.
 *
 * @package    lsx-search
 * @subpackage scripts
 */

var lsx_search = Object.create( null );

;( function( $, window, document, undefined ) {

	'use strict';

	lsx_search.document = $( document );
	lsx_search.window = $( window );
	lsx_search.window_height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	lsx_search.window_width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	lsx_search.facet_wp_load_first_time = false;

	/**
	 *
	 *
	 * @package    lsx-search
	 * @subpackage scripts
	 */
	lsx_search.on_facet_wp_load = function() {
		lsx_search.facet_wp_load_first_time = false;

		lsx_search.document.on( 'facetwp-loaded', function() {
			$( 'body' ).removeClass( 'facetwp-loading-body' );

			$( '#secondary, #primary' ).css( {
				'opacity': '',
				'pointer-events': ''
			} );

			if ( '' == FWP.build_query_string() ) {
				$( '.facetwp-results-clear-btn' ).addClass( 'hidden' );
			} else {
				$( '.facetwp-results-clear-btn' ).removeClass( 'hidden' );
			}

			$.each( FWP.settings.num_choices, function( key, val ) {
				var $parent = $( '.facetwp-facet-' + key ).closest( '.facetwp-item' );
				( 0 === val ) ? $parent.addClass( 'hidden' ) : $parent.removeClass( 'hidden' );
			});

			if ( false === lsx_search.facet_wp_load_first_time ) {
				lsx_search.facet_wp_load_first_time = true;
				return;
			}

			var scroll_top = $( '.facetwp-facet' ).length > 0 ? $( '.facetwp-facet' ).offset().top : $( '.facetwp-template' ).offset().top;
			scroll_top -= 250;
			$( 'html, body' ).animate( { scrollTop: scroll_top }, 400 );
		} );

		lsx_search.document.on( 'facetwp-refresh', function() {
			$( 'body' ).addClass( 'facetwp-loading-body' );

			$( '#secondary, #primary' ).css( {
				'opacity': 0.5,
				'pointer-events': 'none'
			} );
		} );
	};

	/**
	 *
	 *
	 * @package    lsx-search
	 * @subpackage scripts
	 */
	lsx_search.mobile_filters = function() {
		if ( $( '.facetwp-template' ).length > 0 ) {
			$( '.facetwp-filters-wrap' ).slideAndSwipe();

			FWP.auto_refresh = false;

			$( '.ssm-close-btn' ).on( 'click', function() {
				FWP.is_refresh = true;
				lsx_search.document.trigger( 'facetwp-refresh' );
				FWP.fetch_data();
				FWP.is_refresh = false;
			} );

			$( '.ssm-apply-btn' ).on( 'click', function() {
				FWP.refresh();
			} );

			lsx_search.document.on( 'facetwp-refresh', function() {
				$( '.facetwp-filters-wrap' ).each( function() {
					$( this ).data( 'plugin_slideAndSwipe' ).hideNavigation();
				} );
			} );
		}
	};

	/**
	 * On document ready.
	 *
	 * @package    lsx-search
	 * @subpackage scripts
	 */
	lsx_search.document.ready( function() {
		lsx_search.on_facet_wp_load();

		if ( lsx_search.window_width < 768 ) {
			lsx_search.mobile_filters();
		}
	} );

} )( jQuery, window, document );
