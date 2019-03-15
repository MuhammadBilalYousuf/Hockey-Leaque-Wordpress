jQuery(document).ready( function( $ ) {
	$.sponsorsCarousel = {
		uploader:false,
		editor:false,
	};

	jQuery.sponsorsCarousel.selectImages = function() {
		if ( $.sponsorsCarousel.uploader ) {
			$.sponsorsCarousel.uploader.open();
			return;
		}
		$.sponsorsCarousel.uploader = wp.media( {
			title: $.sponsorsCarousel.i18( "Choose Images" ),
			button: {
				text: $.sponsorsCarousel.i18( "Insert in Carousel" )
			},
			library: { type: 'image' },
			multiple: true
		});
		$.sponsorsCarousel.uploader.on( 'select', function() {
			$.sponsorsCarousel.addImages( $.sponsorsCarousel.uploader.state().get( 'selection' ).toJSON() );
		});
		$.sponsorsCarousel.uploader.open();
		return false;
	};

	$.sponsorsCarousel.addImages = function( images ) {
		$.each( images, function( i, e ) {
			var id = e.id + '';
			if ( typeof e.sizes.thumbnail != 'undefined' ) {
				$.sponsorsCarouselImages.push( {id:id, link:e.caption, src:e.sizes.thumbnail.url} );
			} else {
				$.sponsorsCarouselImages.push( {id:id, link:e.caption, src:e.sizes.full.url} );
			}
		});
		$.sponsorsCarousel.showImages();
	};

	$.sponsorsCarousel.showImages = function() {
		var out = '';
		if ( $.sponsorsCarouselImages.length == 0 ) {
			return;
		}
		$.each( $.sponsorsCarouselImages, function( i, e ) {
			out += '<div class="sponsors_carousel_image" data-id="' + e.id + '">';
			out += '<img src="' + e.src + '">';
			out += '<div class="sponsors_carousel_link_edit">';
			out += '<div class="sponsors_carousel_link_edit_label">' + $.sponsorsCarousel.i18( "Custom link:" ) + '</div>';
			out += '<input onchange="jQuery.sponsorsCarousel.changeLink( ' + e.id + ', jQuery( this ).val() )" value="' + e.link + '">';
			out += '</div>';
			out += '<div class="sponsors_carousel_image_delete dashicons dashicons-dismiss" onclick="jQuery.sponsorsCarousel .deleteImg( this )"></div>';
			out += '</div>';
		});
		$('.sponsors_carousel_images').html( out );
		$.sponsorsCarousel.imagesToField();
		$( ".sponsors_carousel_images" ).sortable({
			stop: function( event, ui ) {
				$.sponsorsCarousel.imagesToField();
			}
		});
		$( ".sponsors_carousel_images" ).disableSelection();
	};

	$.sponsorsCarousel.changeLink = function( id, link ) {
		var data = {
			action: 'sponsors_carousel_change_link',
			image_id: id,
			link:link
		};
		jQuery.post( ajaxurl, data, function( response ) {

		});
	};

	$.sponsorsCarousel.imagesToField = function() {
		var ids = [];
		var old = JSON.parse( JSON.stringify( $.sponsorsCarouselImages ) );
		$.sponsorsCarouselImages = [];
		$( ".sponsors_carousel_image" ).each( function( i, e ) {
			var id = $( e ).data( 'id' );
			if ( id ) {
				ids.push( id );
				var data = {};
				$.each( old, function( i, e ) {
					if ( e.id == id ) {
						data = e;
					}
				});
				$.sponsorsCarouselImages.push( data );
			}
		});
		$('.sponsors-carousel_images_field').val( ids.join( ',' ) );
	};

	$.sponsorsCarousel.deleteImg = function( obj ) {
		$( obj ).parent().remove();
		$.sponsorsCarousel.imagesToField();
	};

	$.sponsorsCarousel.deleteCarousel = function( id, question ) {
		if ( id > 0 && confirm( question ) ) {
			window.location.href = window.location.href + '&delete=' + id;
		}
		return false;
	};

	$.sponsorsCarousel.i18 = function( s ) {
		return ( typeof $.sponsorsCarouselI18[s] != 'undefined' ) ? $.sponsorsCarouselI18[ s ] : s
	};

});
