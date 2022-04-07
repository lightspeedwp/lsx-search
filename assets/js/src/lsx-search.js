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
    lsx_search.currentForm = false;

    lsx_search.init = function() {
        lsx_search.currentForm = jQuery('.lsx-search-form');
        //console.log( lsx_search.currentForm );

        if (undefined != lsx_search.currentForm) {
            lsx_search.watchSubmit();

            if (undefined != lsx_search.currentForm.find('.search-field')) {
                lsx_search.watchSearchInput();
            }

            if (undefined != lsx_search.currentForm.find('.btn-dropdown')) {
                lsx_search.watchDropdown();
            }

            if (undefined != lsx_search.currentForm.find('.datepicker')) {
                lsx_search.watchDatePickerButton();
            }
        }
    };

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

			$( '.lsx-search-title-results .clear-facets' ).addClass( 'hidden' );
			if ( $.isFunction( FWP.buildQueryString() ) && '' !== FWP.buildQueryString() ) {
				$( '.lsx-search-title-results .clear-facets' ).removeClass( 'hidden' );
			}

			$.each( FWP.settings, function( key, val ) {
				if ( 'product_price' === key ) {
					var $parent = $( '.facetwp-facet-' + key ).closest( '.facetwp-item' );
					( val.range.min === val.range.max ) ? $parent.addClass( 'hidden' ) : $parent.removeClass( 'hidden' );
				}
			});

			$.each( FWP.settings.num_choices, function( key, val ) {
				var $parent = $('.parent-facetwp-facet-' + key).closest('.facetwp-item');
				(0 === val) ? $parent.hide() : $parent.show();
			});

			if ( false === lsx_search.facet_wp_load_first_time ) {
				lsx_search.first_facet_collaped();
				lsx_search.onChangeTab_Map();
				
				// Load the layout switcher.
				lsx_search.initLayoutSwitcher();
				if ('' === jQuery('.lsx-map-preview').html()) {
					lsx_search.reloadMap();
				}
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

	lsx_search.onChangeTab_Map = function() {
		$('a[data-toggle="tab"][href="#to-search-map"]').on('shown.bs.tab', lsx_search.reloadMap() );
	};

	lsx_search.reloadMap = function() {
		/*if (typeof LSX_TO_Maps !== 'undefined') {
			LSX_TO_Maps.initThis();
		}*/
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

			if ( undefined !== FWP ) {
                FWP.auto_refresh = false;
			}

			$( '.ssm-close-btn' ).on( 'click', function() {
				FWP.is_refresh = true;
				lsx_search.document.trigger( 'facetwp-refresh' );
				FWP.fetchData();
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
	 *
	 *
	 * @package    lsx-search
	 * @subpackage scripts
	 */
	lsx_search.input_search = function() {
		lsx_search.document.on( 'click', '.search-submit.facetwp-btn', function() {
			FWP.refresh();
		} );
	};


    lsx_search.watchDropdown = function() {
        var $this = this;

        //console.log('dropdown fix');
        jQuery(lsx_search.currentForm).find('.dropdown-toggle').each( function() {
        	jQuery(this).attr('data-toggle','dropdown');
		});

        jQuery(lsx_search.currentForm).find('.dropdown-menu').on('click','a',function(event) {
            event.preventDefault();

            jQuery(this).parents('.dropdown').find('.btn-dropdown').attr('data-selection',jQuery(this).attr('data-value'));
            jQuery(this).parents('.dropdown').find('.btn-dropdown').html(jQuery(this).html()+' <span class="caret"></span>');

            if (jQuery(this).hasClass('default')) {
                jQuery(this).parent('li').hide();
            } else {
                jQuery(this).parents('ul').find('.default').parent('li').show();
            }

            if (jQuery(this).parents('.field').hasClass('combination-dropdown')) {
                $this.switchDropDown(jQuery(this).parents('.dropdown'));
            }

            if (jQuery(this).parents('.field').hasClass('engine-select')) {
                $this.switchEngine(jQuery(this).parents('.dropdown'));
            }
        });
    };

    lsx_search.watchSubmit = function() {
        var currentForm = lsx_search.currentForm;

        jQuery(lsx_search.currentForm).on('submit',function(event) {
            var has_facets = false;

            if (undefined != jQuery(this).find('.btn-dropdown:not(.btn-combination)')) {
                has_facets = true;

                jQuery(this).find('.btn-dropdown:not(.btn-combination)').each(function() {
                    var value = jQuery(this).attr('data-selection');

                    if (0 != value || '0' != value) {
                        var input = jQuery("<input>")
                            .attr("type", "hidden")
                            .attr("name", jQuery(this).attr('id'))
                            .val(value);

                        jQuery(currentForm).append(jQuery(input));
                    }
                });
            }

            //Make sure all empty imput do not send a name in the url if they are empty


            //Check if there is a keyword.
            jQuery(this).find('input[type="text"]').each(function() {
                //console.log(jQuery( this ));

            	if ( 's' != jQuery( this ).attr('name') && '' == jQuery( this ).val() ) {
                    jQuery( this ).attr('name','');

				}
			});
            /*if (false == has_facets && undefined != jQuery(this).find('.search-field') && '' == jQuery(this).find('.search-field').val()) {
                jQuery(this).find('.search-field').addClass('error');
                event.preventDefault();
            }*/
            //return false;
        });
    };

    lsx_search.watchSearchInput = function() {
        jQuery(lsx_search.currentForm).find('.search-field').on('keyup',function(event) {
            if (jQuery(this).hasClass('error')) {
                jQuery(this).removeClass('error');
            }
        });
    };

    lsx_search.watchDatePickerButton = function() {
        jQuery(lsx_search.currentForm).find('.datepicker .datepicker-value').each(function(event) {
            jQuery( this ).datepicker({
                dateFormat: "yy-mm-dd"
			});

        });
    };

    lsx_search.switchDropDown = function(dropdown) {
        var id = dropdown.find('button').attr('data-selection');

        if (dropdown.parents('form').find('.combination-toggle.selected').length > 0) {
            dropdown.parents('form').find('.combination-toggle.selected button').attr('data-selection','0');
            var default_title = dropdown.parents('form').find('.combination-toggle.selected a.default').html();
            dropdown.parents('form').find('.combination-toggle.selected button').html(default_title+' <span class="caret"></span>');
            dropdown.parents('form').find('.combination-toggle.selected').removeClass('selected').addClass('hidden');
        }

        dropdown.parents('form').find('#'+id).parents('.combination-toggle').removeClass('hidden').addClass('selected');
    };

    lsx_search.switchEngine = function(dropdown) {
        var id = dropdown.find('button').attr('data-selection');

        if (dropdown.parents('form').find('.combination-toggle.selected').length > 0) {
            dropdown.parents('form').find('.combination-toggle.selected button').attr('data-selection','0');
            var default_title = dropdown.parents('form').find('.combination-toggle.selected a.default').html();
            dropdown.parents('form').find('.combination-toggle.selected button').html(default_title+' <span class="caret"></span>');
            dropdown.parents('form').find('.combination-toggle.selected').removeClass('selected').addClass('hidden');
        }

        dropdown.parents('form').attr('engine');
    };

    lsx_search.clearFacets = function( ) {
        FWP.reset();
	};

	lsx_search.first_facet_collaped = function( ) {
		var first_visible_facet = $('.lsx-search-filer-area .facetwp-item-wrap .row:not(.hidden-sm) .facetwp-item:visible:first');
        $(first_visible_facet).find('button.facetwp-collapse').trigger("click");
	};

	lsx_search.initLayoutSwitcher = function() {
		jQuery(document).on('click', '.lsx-layout-switcher-option', function(e) {
			e.preventDefault();
			console.log(jQuery('body').hasClass('lsx-search-enabled'));
			if ( jQuery('body').hasClass('lsx-search-enabled') ) {
				FWP.refresh();
			}
		});
	};

	/**
	 * On document ready.
	 *
	 * @package    lsx-search
	 * @subpackage scripts
	 */
	lsx_search.document.ready( function() {
		lsx_search.on_facet_wp_load();
		lsx_search.input_search();

		if ( lsx_search.window_width < 768 ) {
			lsx_search.mobile_filters();
		}
		lsx_search.init();
		//lsx_search.first_facet_collaped();


	} );

} )( jQuery, window, document );
