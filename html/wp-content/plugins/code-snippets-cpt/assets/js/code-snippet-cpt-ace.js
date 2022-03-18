/**
 * Code Snippets CPT - v2.1.0 - 2017-04-05
 * http://dsgnwrks.pro
 *
 * Copyright (c) 2017;
 * Licensed GPLv2+
 */
window.snippetcpt = window.snippetcpt || {};

/* eslint-disable max-params, no-shadow-restricted-names, no-undefined, no-unused-vars */
( function( window, document, $, ace, cpt, undefined ) {
	/* eslint-enable max-params, no-shadow-restricted-names, no-undefined, no-unused-vars */
	'use strict';

	$.extend( cpt, window.snippetcptAce );

	var renderTimeout = null;

	cpt.viewers = [];
	cpt.aceInitComplete = false;
	cpt.aceAfterRenderComplete = false;

	cpt.newViewer = function( currentEl ) {
		var viewer = {
			toolbarDelay : 500,
			isCollapsed  : null,
			editor       : null
		};

		viewer.cache = function() {
			viewer.$snippet = $( currentEl );
			viewer.$wrap    = viewer.$snippet.parent();
			viewer.data     = viewer.$snippet.data( 'config' );
		};

		viewer.init = function() {
			viewer.cache();

			viewer.editor = ace.edit( currentEl );

			viewer.editor.setOptions( {
				readOnly: true,
				showPrintMargin: false,
				highlightActiveLine: false,
				maxLines: 'auto' === viewer.data.max_lines ? Infinity : viewer.data.max_lines,
				minLines: 1
			} );

			var editSession = viewer.editor.getSession();

			viewer.editor.setTheme( cpt.theme || 'ace/theme/chrome' );

			if ( viewer.data.lang ) {
				if ( 'php' === viewer.data.lang && 0 !== editSession.getValue().trim().indexOf( '<?php' ) ) {
					editSession.setMode( { path: 'ace/mode/php', inline: true } );
				} else {
					editSession.setMode( 'ace/mode/' + viewer.data.lang );
				}
			} else {
				editSession.setMode( cpt.language || 'ace/mode/text' );
			}

			if ( ! viewer.data.line_nums ) {
				viewer.editor.renderer.setShowGutter( false );
			}

			viewer.editor.renderer.on( 'afterRender', viewer.triggerRender );

			viewer.$wrap.on( 'click', '.line-numbers', viewer.toggleLineNumbers );
			if ( 'auto' === viewer.data.max_lines ) {
				viewer.$wrap.removeClass( 'scrollable' );
			}

			if ( cpt.features.collapsible ) {
				viewer.$wrap.on( 'click', '.collapse', viewer.toggleCollapse );
			}

			viewer.$snippet.trigger( 'snippetcpt-ace-init' );
		};

		viewer.triggerRender = function() {
			if ( renderTimeout ) {
				window.clearTimeout( renderTimeout );
			}

			renderTimeout = setTimeout( function() {
				cpt.aceAfterRenderComplete = true;
				$( document.body ).trigger( 'snippetcpt-afterRender' );

				viewer.editor.renderer.off( 'resize', viewer.triggerRender );
			}, 500 );
		};

		viewer.toggleLineNumbers = function( evt ) {
			evt.preventDefault();

			if ( viewer.data.lineNums ) {
				viewer.editor.renderer.setShowGutter( false );
				viewer.data.lineNums = false;
				viewer.$wrap.find( '.snippet-buttons .line-numbers' ).removeClass( 'has-line-numbers' );
			} else {
				viewer.editor.renderer.setShowGutter( true );
				viewer.data.lineNums = true;
				viewer.$wrap.find( '.snippet-buttons .line-numbers' ).addClass( 'has-line-numbers' );
			}
		};

		viewer.toggleCollapse = function( evt ) {
			evt.preventDefault();

			if ( viewer.isCollapsed ) {
				viewer.isCollapsed = false;
				viewer.$snippet.slideDown();
				viewer.$snippet.parent().removeClass( 'snippetcpt-hidden' );
				$( this )
				.addClass( 'dashicons-hidden' )
				.removeClass( 'dashicons-visibility' );
			} else {
				viewer.isCollapsed = true;
				// viewer.$toolbar.slideDown( 150 );
				viewer.$snippet.slideUp();
				viewer.$snippet.parent().addClass( 'snippetcpt-hidden' );
				$( this )
				.addClass( 'dashicons-visibility' )
				.removeClass( 'dashicons-hidden' );
			}
		};

		cpt.viewers.push( viewer );
		viewer.init();
	};

	cpt.init = function() {
		$( '.snippetcpt-ace-viewer' ).each( function() {
			cpt.newViewer( this );
		} );

		cpt.aceInitComplete = true;
		$( document.body ).trigger( 'snippetcpt-ace-init-complete' );
	};

	$( cpt.init );

} )( window, document, jQuery, window.ace, window.snippetcpt );

window.snippetcpt = window.snippetcpt || {};

/* eslint-disable max-params, no-shadow-restricted-names, no-undefined, no-unused-vars */
( function( window, document, $, cpt, undefined ) {
	/* eslint-enable max-params, no-shadow-restricted-names, no-undefined, no-unused-vars */
	'use strict';

	$.extend( cpt, window.snippetcptl10n );

	cpt.$ = cpt.$ || {};

	var $c = cpt.$;
	var ESCAPE = 27;
	var iconSet = [];
	var btnTemplate = '<span class="snippet-button dashicons {{ data.class }}" title="{{ data.title }}"></span>';
	var linkTemplate = '<a href="{{ data.link }}" class="snippet-button dashicons {{ data.class }}" title="{{ data.title }}"></a>';
	var iconsConfig = {
		copy : {
			class: 'dashicons-editor-code',
			title: cpt.l10n.copy
		},
		fullscreen : {
			class: 'dashicons-editor-expand',
			title: cpt.l10n.fullscreen
		},
		close : {
			class: 'dashicons-no',
			title: cpt.l10n.close
		},
		edit : {
			class: 'dashicons-edit',
			title: cpt.l10n.edit
		},
		collapse : {
			class: 'dashicons-hidden collapse',
			title: cpt.l10n.collapse
		},
		numbers : {
			class: 'dashicons-editor-ol line-numbers',
			title: cpt.l10n.numbers
		}
	};

	cpt.init = function() {
		$c.wrap = $( '.snippetcpt-wrap' );
		$c.body = $( document.body );

		$( document ).on( 'prettify-loaded', cpt.prettifyLoaded );

		if ( cpt.features.edit ) {
			iconSet.push( 'edit' );
		}

		if ( cpt.features.do_click_to_copy ) {
			cpt.clickToCopyInit();
		}

		if ( cpt.features.enable_full_screen_view ) {
			cpt.fullScreenInit();
		}

		if ( cpt.features.enable_ace ) {
			iconSet.push( 'numbers' );

			if ( cpt.features.collapsible ) {
				iconSet.push( 'collapse' );
			}
		}

		if ( cpt.addIcons( iconSet ) ) {
			$c.body.trigger( 'snippet-icons-added' );
		}

		if ( cpt.features.enable_full_screen_view && cpt.fullscreen ) {
			$c.body.one( 'snippetcpt-afterRender', function() {
				$c.body.find( '.snippet-button.dashicons-editor-expand' ).first()
					.trigger( 'click' );
			} );
		}
	};

	cpt.prettifyLoaded = function() {
		$c.wrap.each( function() {
			var $this = $( this );
			var rows  = $this.find( '.linenums li' ).length;

			if ( rows > 1000 ) {
				$this.addClass( 'gt1000' );
			}
			if ( rows > 100 ) {
				$this.addClass( 'gt100' );
			}
			if ( rows > 10 ) {
				$this.addClass( 'gt10' );
			}
		} );
	};

	cpt.addIcons = function( icons ) {
		var added = false;

		$c.wrap.each( function() {
			var $this = $( this );
			var html = '';

			for ( var i = 0; i < icons.length; i++ ) {
				if ( 'fullscreen' === icons[i] && $this.parent( '.snippetcpt-footer' ).length ) {
					icons[i] = 'close';
				}

				html += cpt.getIcon( icons[i], $this.data( icons[i] ) );
			}

			if ( html ) {
				added = true;
				if ( ! $this.find( '.snippet-buttons' ).length ) {
					$this.append( '<div class="snippet-buttons"></div>' );
				}
			}

			$( this ).find( '.snippet-buttons' ).append( html );
		} );

		return added;
	};

	cpt.getIcon = function( icon, link ) {
		var html = '';

		switch ( icon ) {
			case 'close':
			case 'collapse':
			case 'numbers':
				html = cpt.template( iconsConfig[ icon ], btnTemplate );
				break;
			default:
				if ( link ) {
					html = cpt.template(
						$.extend( iconsConfig[ icon ], { link: link } ),
						linkTemplate
					);
				}
				break;
		}

		return html;
	};

	cpt.template = function( data, template ) {
		$.each( data, function( key, value ) {
			template = template.replace( new RegExp( '{{ data.' + key + ' }}', 'gi' ), value );
		} );
		return template;
	};


	/*
	 * Feature: Click to copy
	 */

	cpt.clickToCopyInit = function() {
		iconSet.push( 'copy' );

		$c.body
			.on( 'click', '.snippet-button.dashicons-editor-code', cpt.browserCopy )
			.on( 'snippet-icons-added', function() {
				setTimeout( cpt.cacheCopyable, 500 );
			} );
	};

	// Cache a copyable version of the snippet in a hidden div.
	cpt.cacheCopyable = function() {
		$( '.snippetcpt-wrap' ).each( function() {
			var $snippet = $( this );
			var copyId   = $snippet.attr( 'id' ) + '-copy';
			var url      = $snippet.data( 'copy' ) + '&json=1';

			var failedCb = function() {
				if ( cpt.debug ) {
					console.log( 'get fail', arguments );
				}
			};

			$.get( url )
				.success( function( response ) {
					if ( response.success ) {
						var $div = $( '<div style="display:none" id="' + copyId + '"></div>' );

						$div.text( response.data );
						$c.body.append( $div );
						$snippet.data( 'copyId', copyId );
					} else {
						failedCb( response );
					}
				} )
				.fail( failedCb );
		} );
	};

	/*
	 * Feature: Browser copy using execCommand().
	 */

	cpt.browserCopy = function( evt ) {
		var $btn     = $( this );
		var $snippet = $btn.parents( '.snippetcpt-wrap' );

		if ( ! $snippet.data( 'copyId' ) ) {

			// Fallback to window.open function if ajax snppet-getting failed.
			return cpt.snippetCopyWindow( $btn.attr( 'href' ), evt );
		}

		if ( ! $snippet.find( '.copy-success-msg' ).length ) {
			$snippet.find( '.snippet-buttons' ).append( '<div class="copy-success-msg">' + cpt.l10n.copied + '</div>' );
		}

		var copied = cpt.copyText( $( document.getElementById( $snippet.data( 'copyId' ) ) ).text() );

		if ( window.wp && window.wp.a11y ) {
			window.wp.a11y.speak( copied ? cpt.l10n.copied : cpt.l10n.copyError, 'assertive' );
		}

		if ( copied ) {
			evt.preventDefault();

			$btn.removeClass( 'dashicons-editor-code' ).addClass( 'dashicons-thumbs-up' );
			$snippet.addClass( 'snippet-copied-success' );
			setTimeout( function() {
				$btn.removeClass( 'dashicons-thumbs-up' ).addClass( 'dashicons-editor-code' );
				$snippet.removeClass( 'snippet-copied-success' );
			}, 1500 );

		} else {

			// Fallback to window.open function on failure.
			return cpt.snippetCopyWindow( $btn.attr( 'href' ), evt );
		}

		return true;
	};

	cpt.copyText = function( text ) {
		var success = false;

		var createNode = function( content ) {
			var node = document.createElement( 'pre' );

			node.style.width = '1px';
			node.style.height = '1px';
			node.style.position = 'fixed';
			node.style.top = '5px';
			node.textContent = content;

			return node;
		};

		var copyNode = function( node ) {
			var selection = getSelection();

			selection.removeAllRanges();

			var range = document.createRange();

			range.selectNodeContents( node );
			selection.addRange( range );

			try {
				success = document.execCommand( 'copy' );
			} catch ( err ) {
				if ( cpt.debug ) {
					console.log( cpt.l10n.copyError );
					console.log( err );
				}
			}

			selection.removeAllRanges();
		};

		var node = createNode( text );

		document.body.appendChild( node );
		copyNode( node );
		document.body.removeChild( node );

		return success;
	};

	/*
	 * Feature: Open window to copy snippet
	 */

	cpt.snippetCopyWindow = function( url, evt ) {
		var w = 925; var h = 950;
		var left = ( window.innerWidth / 2 ) - ( w / 2 );
		var top = ( window.innerHeight / 2 ) - ( h / 2 );

		// Pop open a window.
		if ( window.open(
			url,
			cpt.l10n.copy_code,
			'toolbar=no,resizable=yes,width=' + w + ',height=' + h + ',top=' + top + ',left=' + left
		) ) {
			// (else fall through to opening link in same window)
			evt.preventDefault();
		}
	};

	/*
	 * Feature: Expand snippet view to full screen
	 */

	cpt.fullScreenInit = function() {
		iconSet.push( 'fullscreen' );

		$c.footer = $( '.snippetcpt-footer' );
		if ( !$c.footer.length ) {
			$c.footer = $( '<div class="snippetcpt-footer snippet-hidden"></div>' )
				.appendTo( $c.body );
		}

		$c.body
			.on( 'click', '.snippet-button.dashicons-no', cpt.closeSnippet )
			.on( 'click', '.snippet-button.dashicons-editor-expand', cpt.openSnippet );

		$( document ).on( 'keyup', function( evt ) {
			if ( ESCAPE === evt.keyCode ) {
				cpt.closeSnippet();
			}
		} );

		if ( cpt.isSnippet ) {
			cpt.url = window.location.pathname;
			cpt.isFull = false;

			window.onpopstate = function( evt ) {
				var fallback = cpt.fullscreen ? 'closed' : 'open';
				var goTo = evt.state && evt.state.was ? evt.state.was : fallback;

				cpt.isSnippet = false;
				if ( 'closed' === goTo ) {
					$c.body.find( '.snippet-button.dashicons-editor-expand' ).first()
						.trigger( 'click' );
				} else {
					$c.body.find( '.snippet-button.dashicons-no' ).first()
						.trigger( 'click' );
				}
				cpt.isSnippet = true;
			};
		}
	};

	cpt.closeSnippet = function() {
		cpt.isFull = false;

		if ( cpt.isSnippet ) {
			window.history.pushState( { was: 'open' }, '', cpt.url );
		}

		$c.body.removeClass( 'snippet-full-screen' ).removeClass( 'snippet-scrollable' );
		$c.footer.html( '' ).addClass( 'snippet-hidden' );
	};

	cpt.openSnippet = function( evt ) {
		evt.preventDefault();
		cpt.isFull = true;

		if ( cpt.isSnippet ) {
			window.history.pushState( { was: 'closed' }, '', '?full-screen' );
		}

		var $snippet = $( this ).parents( '.snippetcpt-wrap' ).clone( true );
		var $pre = $snippet.find( 'pre' );

		$c.body.addClass( 'snippet-full-screen' );
		$snippet.find( '.dashicons-editor-expand' )
			.replaceWith( cpt.template( iconsConfig.close, btnTemplate ) );
		$pre.show();
		$c.footer.html( $snippet ).removeClass( 'snippet-hidden' );

		if ( $pre.outerHeight() > $( window ).height() || $pre.find( '>' ).outerHeight() > $( window ).height() ) {
			$c.body.addClass( 'snippet-scrollable' );
		}

		$c.body.trigger( 'snippet-full-screen' );
	};

	$( cpt.init );

} )( window, document, jQuery, window.snippetcpt );
