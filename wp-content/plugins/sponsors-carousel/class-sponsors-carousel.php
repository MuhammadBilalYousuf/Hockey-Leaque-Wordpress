<?php
/*  Copyright 2017  Sergey Panasenko  (email: sergey.panasenko@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class SponsorsCarousel {

	private $id;
	private $speed;
	private $scrollamount;
	private $autoscroll;
	private $width;
	private $height;
	private $totalwidth;
	private $marginright;
	private $showtitles;
	private $showcontrols;
	private $target;
	private $defaultlink;
	private $images;


	public function __construct( $id = 0 ) {
		$this->id           = $id;
		$this->speed        = 600;
		$this->scrollamount = 1;
		$this->autoscroll   = 0;
		$this->width        = 75;
		$this->height       = 75;
		$this->totalwidth   = 0;
		$this->marginright  = 0;
		$this->showtitles   = 1;
		$this->showcontrols = 1;
		$this->target       = 1;
		$this->defaultlink  = '';
		$this->images       = [];
	}

	public static function show_buttons( $current ) {
		$list = get_option( 'sponsors-carousels', '0' );
		$ids = explode( ',', $list );
		if ( !in_array( $current, $ids ) ) {
			$ids[] = $current;
			update_option( 'sponsors-carousels', implode( ',', $ids ) );
		}
		$out = '<div class="sponsors-carousels-list">';
		$url = admin_url( 'admin.php?page=sponsors_carousel' );
		foreach ( $ids as $id ) {
			$out .= '<a class="sponsors-carousels-list_el' . ( $current == $id ? ' selected' : '' ) . '" href="' . $url . ( $id ? ( '&id=' . $id ) : '' ) . '">' . '[sponsors_carousel' . ( $id ? ( ' id=' . $id ) : '' ) . ']' . '</a>';
		}
		$out .= '<a class="sponsors-carousels-list_el" href="' . $url . '&id=' .( max( $ids ) + 1 ) . '"><span class="dashicons dashicons-plus-alt"></span></a>';
		$out .= '</div>';
		return $out;
	}

	public static function delete( $id ) {
		if ( $id == 0 ) {
			return;
		}
		$list = get_option( 'sponsors-carousels' );
		if ( $list ) {
			$ids = explode( ',', $list );
			$key = array_search( $id, $ids );
			if ( $key !== false ) {
				unset( $ids[$key] );
				update_option( 'sponsors-carousels', implode( ',', $ids ) );
				delete_option( 'sponsors-carousel-' . $id );
			}
		}
		return '<script type="text/javascript">
				window.location.href = "'.admin_url( 'admin.php?page=sponsors_carousel' ) . '";
			</script>';
	}

	public function update( $data ) {
		$this->id           = intval( $data['id'] );
		$this->speed        = intval( $data['speed'] );
		$this->scrollamount = intval( $data['scrollamount'] );
		$this->autoscroll   = intval( $data['autoscroll'] );
		$this->width        = intval( $data['width'] );
		$this->height       = intval( $data['height'] );
		$this->totalwidth   = intval( $data['totalwidth'] );
		$this->marginright  = intval( $data['marginright'] );
		$this->showtitles   = intval( $data['showtitles'] );
		$this->showcontrols = intval( $data['showcontrols'] );
		$this->target       = intval( $data['target'] );
		$this->defaultlink  = $data['defaultlink'];
		$this->images       = array();
		foreach ( explode( ',',$data['images'] ) as $id ) {
			if ( intval($id) ) {
				$this->images[] = intval( $id );
			}
		}
		update_option( 'sponsors-carousel-' . $this->id, serialize( $this ) );
		return TRUE;
	}

	public function get_form() {

		$out = '
		<div class="sponsors-carousel_wrapper">
		<form id="sponsors-carousel"  method="post" novalidate="novalidate">
			<input name="save" value="save" type="hidden">
			<input name="id" value="' . $this->id . '" type="hidden">
			<input class="sponsors-carousel_images_field" name="images" value="' . implode( ',', $this->images ) . '" type="hidden">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">'. __( "Shortcode:", 'sponsors-carousel' ) . '</th>
						<td>
							[sponsors_carousel' . ( $this->id ? ( ' id=' . $this->id ) : '' ) . ']
							<div class="description">'. sprintf( __( 'Also you can use %1$s for show this carousel.', 'sponsors-carousel' ), "<b> < ? php echo sponsors_carousel( " . ( $this->id ? ( '' . $this->id) : '' ) . " ) ?></b>" ) . '</div>
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Speed:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="speed" id="speed" value="' . $this->speed .'" type="number">
							<div class="description">'. __( "The speed of the animation. 0 is instant, 10000 is very slow.", 'sponsors-carousel' ) . '</div>
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Scroll Amount:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="scrollamount" id="scrollamount" value="' . $this->scrollamount .'" type="number">
							<div class="description">'. __( "How many images should be scrolled in one step", 'sponsors-carousel' ) . '</div>
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Auto scroll:", 'sponsors-carousel' ) . '</th>
						<td>
							<select type="select" name="autoscroll" id="autoscroll">
								<option value="0" ' . ( 0 == $this->autoscroll ? 'selected="selected"' : '' ) . '>' . __( "Off", 'sponsors-carousel' ) . '</option>
								<option value="1" ' . ( 1 == $this->autoscroll ? 'selected="selected"' : '' ) . '>' . __( "Continuous", 'sponsors-carousel' ) . '</option>
								<option value="3" ' . ( 3 == $this->autoscroll ? 'selected="selected"' : '' ) . '>' . __( "Fast", 'sponsors-carousel' ) . '</option>
								<option value="6" ' . ( 6 == $this->autoscroll ? 'selected="selected"' : '' ) . '>' . __( "Medium", 'sponsors-carousel' ) . '</option>
								<option value="10" ' . ( 10 == $this->autoscroll ? 'selected="selected"' : '' ) . '>' . __( "Slow", 'sponsors-carousel' ) . '</option>
							</select>
						 </td>
					</tr>
					<tr>
						<th scope="row">'. __( "Width of Image:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="width" id="width" value="' . $this->width .'" type="number">
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Height of Image:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="height" id="height" value="' . $this->height .'" type="number">
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Width of Carousel:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="totalwidth" id="totalwidth" value="' . $this->totalwidth .'" type="number">
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Distance between images:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="marginright" id="marginright" value="' . $this->marginright .'" type="number">
						</td>
					</tr>
					<tr>
						<th scope="row">'. __( "Show titles for images:", 'sponsors-carousel' ) . '</th>
						<td>
							<select type="select" name="showtitles" id="showtitles">
								<option value="0" ' . ( 0 == $this->showtitles ? 'selected="selected"' : '' ) . '>' . __( "No", 'sponsors-carousel' ) . '</option>
								<option value="1" ' . ( 1 == $this->showtitles ? 'selected="selected"' : '' ) . '>' . __( "Yes", 'sponsors-carousel' ) . '</option>
							</select>
						 </td>
					</tr>
					<tr>
						<th scope="row">'. __( "Show controls:", 'sponsors-carousel' ) . '</th>
						<td>
							<select type="select" name="showcontrols" id="showcontrols">
								<option value="0" ' . ( 0 == $this->showcontrols ? 'selected="selected"' : '' ) . '>' . __( "No", 'sponsors-carousel' ) . '</option>
								<option value="1" ' . ( 1 == $this->showcontrols ? 'selected="selected"' : '' ) . '>' . __( "Yes", 'sponsors-carousel' ) . '</option>
							</select>
						 </td>
					</tr>
					<tr>
						<th scope="row">'. __( "Open in new window:", 'sponsors-carousel' ) . '</th>
						<td>
							<select type="select" name="target" id="target">
								<option value="0" ' . ( 0 == $this->target ? 'selected="selected"' : '' ) . '>' . __( "No, open in same window", 'sponsors-carousel' ) . '</option>
								<option value="1" ' . ( 1 == $this->target ? 'selected="selected"' : '' ) . '>' . __( "Yes, open in new window", 'sponsors-carousel' ) . '</option>
							</select>
						 </td>
					</tr>
					<tr>
						<th scope="row">'. __( "Default link:", 'sponsors-carousel' ) . '</th>
						<td>
							<input name="defaultlink" id="defaultlink" value="' . $this->defaultlink .'" type="url">
						</td>
					</tr>


				</tbody>
			</table>
			<p class="buttons">
				<input type="button" value="' .  __( 'Add images', 'sponsors-carousel' ) . '" onclick="return jQuery.sponsorsCarousel .selectImages()">
				<input type="submit" value="' .  __( 'Save Changes', 'sponsors-carousel' ) . '" >
		';
		if ( $this->id > 0 ) {
			$out .= '<input type="button" value="' .  __( 'Delete carousel', 'sponsors-carousel' ) . '" onclick="return jQuery.sponsorsCarousel .deleteCarousel(' . $this->id .',\'' .  __( 'Are you sure?','sponsors-carousel' ) . '\')">';
		}
		$out .= '
			</p>
		</form>
		<div class="sponsors_carousel_images"></div>

		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$.sponsorsCarouselImages = ' . json_encode( $this->get_images() ) . ';
				$.sponsorsCarouselI18    = ' . json_encode( $this->js_i18() ) . ';
				$.sponsorsCarousel.showImages();
			});
		</script>
		</div>
		';
		return $out;

	}

	public function get_images( $size = 'thumbnail', $title = false ) {
		$images = array();
		if ( !empty( $this->images ) ) {
			foreach( $this->images as $p ) {
				$post = get_post( $p );
				$arg = array();
				$img = array(
					'id'   => $p,
					'src'  => wp_get_attachment_image_url( $p, $size ),
					'link' => $post->post_excerpt,
				);
				if ( $title == 1 ) {
					$img['title'] = $post->post_title;
				}
				$images[] = $img;
			}
		}
		return $images;
	}

	public function show() {
		$size = array( $this->width, $this->height );
		$scwp_array = $this->get_images( $size, $this->showtitles );
		if ( empty( $scwp_array ) ) {
			return '';
		}
		$output = "
			<!-- Begin Sponsors Carousel -->";
		$output .= '
			<div class="sponsors_carousel_wrapper" id="sponsors_carousel_wrapper-' . $this->id . '">
				<div  id="sponsors_carousel-' . $this->id . '" class="sponsors_carousel"
					data-id="' . $this->id . '"
					data-speed="' . $this->speed . '"
					data-autoscroll="' . $this->autoscroll . '"
					data-scrollamount="' . $this->scrollamount . '"
					>
					<ul>';
		foreach ( $scwp_array as $img ) {
			if ( $img['link'] <> '' ) {
				$link = $img['link'];
			} else {
				$link = $this->defaultlink;
			}
			if ( $link  <> '' ) {
				$output .= '<li><a ' . ( $this->target ? ' target="_blank" ' : '' );
				$output .= ' href="' . $link . '" class="jcarousel-item">';
				$output .= '<img src="' . $img['src'] . '" ' . ( $this->showtitles ? ( ' title="' . $img['title'] . '"' ) : '' ) . '>';
				$output .= '</a></li>';
			} else {
				$output .= '<li><img src="' . $img['src'] . '" ' . ( $this->showtitles ? ( ' title="' . $img['title'] . '"' ) : '' ) . '></li>';
			}
		}
		$output .= '</ul>
			</div>';
		if ( $this->showcontrols ) {
			$output .= '
				<a href="#" onclick="return jQuery.sponsorsCarousel.move(' . $this->id . ', \'-=' . $this->scrollamount . '\')" class="sponsors_carousel-control sponsors_carousel-control-prev"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
				<a href="#" onclick="return jQuery.sponsorsCarousel.move(' . $this->id . ', \'+=' . $this->scrollamount . '\')" class="sponsors_carousel-control sponsors_carousel-control-next"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
			';
		}
		$output .= '</div>';

		$output .= "
			<style>
				#sponsors_carousel_wrapper-".$this->id.",
				#sponsors_carousel-" . $this->id . ",
				#sponsors_carousel-" . $this->id . " li,
				#sponsors_carousel-" . $this->id . " img {
					height:" . $this->height . "px;
				}
				#sponsors_carousel-" . $this->id . " img {
					width:" . $this->width . "px;
					margin-right:" . $this->marginright . "px;
				}
				#sponsors_carousel_wrapper-" . $this->id . " .sponsors_carousel-control {
					top:" . ( intval( $this->height / 2 ) - 12 ) . "px;
				}
			";
		if ( $this->totalwidth ) {
			$output .= "
			#sponsors_carousel_wrapper-".$this->id." {
				width: " . $this->totalwidth . "px;
			}
			";
		}
		$output .= "
			$( '.jcarousel' ).jcarouselAutoscroll( {
				interval: 1000
			} );

			</style>
			<!-- End Sponsors Carousel -->
		";
		return $output;
	}

	public function js_i18() {
		return array(
			"Choose Images"      => __( 'Choose Images', 'sponsors-carousel' ),
			"Insert in Carousel" => __( 'Insert in Carousel', 'sponsors-carousel' ),
			"Custom link:"       => __( 'Custom link:', 'sponsors-carousel' ),
		);
	}
}

