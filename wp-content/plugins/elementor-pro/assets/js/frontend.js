/*! elementor-pro - v1.0.3 - 13-12-2016 */
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var handlers = {
	form: require( 'modules/forms/assets/js/frontend/frontend' ),
	countdown: require( 'modules/countdown/assets/js/frontend/frontend' ),
	posts: require( 'modules/posts/assets/js/frontend/frontend' ),
	slides: require( 'modules/slides/assets/js/frontend/frontend' ),
	portfolio: require( 'modules/posts/assets/js/frontend/frontend' )
};

window.elementorProFrontend = {
	config: ElementorProFrontendConfig,
	modules: {}
};

jQuery( function( $ ) {
	$.each( handlers, function( moduleName ) {
		elementorProFrontend.modules[ moduleName ] = new this( $ );
	} );
} );

},{"modules/countdown/assets/js/frontend/frontend":2,"modules/forms/assets/js/frontend/frontend":4,"modules/posts/assets/js/frontend/frontend":7,"modules/slides/assets/js/frontend/frontend":10}],2:[function(require,module,exports){
module.exports = function() {
	elementorFrontend.hooks.addAction( 'frontend/element_ready/countdown.default', require( './handlers/countdown' ) );
};

},{"./handlers/countdown":3}],3:[function(require,module,exports){
var Countdown = function( $countdown, endTime, $ ) {
	var timeInterval,
		elements = {
			$daysSpan: $countdown.find( '.elementor-countdown-days' ),
			$hoursSpan: $countdown.find( '.elementor-countdown-hours' ),
			$minutesSpan: $countdown.find( '.elementor-countdown-minutes' ),
			$secondsSpan: $countdown.find( '.elementor-countdown-seconds' )
		};

	var updateClock = function() {
		var timeRemaining = Countdown.getTimeRemaining( endTime );

		$.each( timeRemaining.parts, function( timePart ) {
			var $element = elements[ '$' + timePart + 'Span' ],
				partValue = this.toString();

			if ( 1 === partValue.length ) {
				partValue = 0 + partValue;
			}

			if ( $element.length ) {
				$element.text( partValue );
			}
		} );

		if ( timeRemaining.total <= 0 ) {
			clearInterval( timeInterval );
		}
	};

	var initializeClock = function() {
		updateClock();

		timeInterval = setInterval( updateClock, 1000 );
	};

	initializeClock();
};

Countdown.getTimeRemaining = function( endTime ) {
	var timeRemaining = endTime - new Date(),
		seconds = Math.floor( ( timeRemaining / 1000 ) % 60 ),
		minutes = Math.floor( ( timeRemaining / 1000 / 60 ) % 60 ),
		hours = Math.floor( ( timeRemaining / ( 1000 * 60 * 60 ) ) % 24 ),
		days = Math.floor( timeRemaining / ( 1000 * 60 * 60 * 24 ) );

	if ( days < 0 || hours < 0 || minutes < 0 ) {
		seconds = minutes = hours = days = 0;
	}

	return {
		total: timeRemaining,
		parts: {
			days: days,
			hours: hours,
			minutes: minutes,
			seconds: seconds
		}
	};
};

module.exports = function( $scope, $ ) {
	var $element = $scope.find( '.elementor-countdown-wrapper' ),
		date = new Date( $element.data( 'date' ) * 1000 );

	new Countdown( $element, date, $ );
};

},{}],4:[function(require,module,exports){
module.exports = function() {
	elementorFrontend.hooks.addAction( 'frontend/element_ready/form.default', require( './handlers/form' ) );
	elementorFrontend.hooks.addAction( 'frontend/element_ready/form.default', require( './handlers/recaptcha' ) );
};

},{"./handlers/form":5,"./handlers/recaptcha":6}],5:[function(require,module,exports){
module.exports = function( $scope, $ ) {
	var $form = $scope.find( '.elementor-form' );

	$form.on( 'submit', function() {
		var $submitButton = $form.find( '[type="submit"]' );

		if ( $form.hasClass( 'elementor-form-waiting' ) ) {
			return false;
		}

		$form
			.animate( {
				opacity: '0.45'
			}, 500 )
			.addClass( 'elementor-form-waiting' );

		$submitButton
			.attr( 'disabled', 'disabled' )
			.html( '<i class="fa fa-spinner fa-spin"></i> ' + $submitButton.html() );

		$form
			.find( '.elementor-message' )
			.remove();

		$form
			.find( 'div.elementor-field-group' )
			.removeClass( 'error' )
			.find( 'span.elementor-form-help-inline' )
			.remove()
			.end()
			.find( ':input' ).attr( 'aria-invalid', 'false' );

		var formData = new FormData( $form[ 0 ] );
		formData.append( 'action', 'elementor_pro_forms_send_form' );
		formData.append( 'referrer', location.toString() );

		$.ajax( {
			url: elementorProFrontend.config.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: formData,
			processData: false,
			contentType: false,
			success: function( response, status ) {
				$submitButton
					.html( $submitButton.text() )
					.removeAttr( 'disabled' );

				$form
					.animate( {
						opacity: '1'
					}, 100 )
					.removeClass( 'elementor-form-waiting' );

				if ( ! response.success ) {
					if ( response.data.fields ) {
						$.each( response.data.fields, function( key, title ) {
							$form
								.find( 'div.elementor-field-group' ).eq( key )
								.addClass( 'elementor-error' )
								.append( '<span class="elementor-message elementor-message-danger elementor-help-inline elementor-form-help-inline" role="alert">' + title + '</span>' )
								.find( ':input' ).attr( 'aria-invalid', 'true' );
						} );
					}
					$form.append( '<div class="elementor-message elementor-message-danger" role="alert">' + response.data.message + '</div>' );
				} else {
					$form.trigger( 'reset' );

					if ( '' !== response.data.message ) {
						$form.append( '<div class="elementor-message elementor-message-success" role="alert">' + response.data.message + '</div>' );
					}
					if ( '' !== response.data.link ) {
						location.href = response.data.link;
					}
				}
			},

			error: function( xhr, desc ) {
				$form.append( '<div class="elementor-message elementor-message-danger" role="alert">' + desc + '</div>' );

				$submitButton
					.html( $submitButton.text() )
					.removeAttr( 'disabled' );

				$form
					.animate( {
						opacity: '1'
					}, 100 )
					.removeClass( 'elementor-form-waiting' );

				$form.trigger( 'error' );
			}
		} );

		return false;
	} );
};

},{}],6:[function(require,module,exports){
module.exports = function( $scope, $ ) {
	var $element = $scope.find( '.elementor-g-recaptcha:last' ),
		window;

	if ( ! $element.length ) {
		return;
	}

	var addRecaptcha = function( $element ) {
		var widgetId = window.grecaptcha.render( $element[0], $element.data() ),
			$form = $element.parents( 'form' );

		$element.data( 'widgetId', widgetId );

		$form.on( 'reset error', function() {
			window.grecaptcha.reset( $element.data( 'widgetId' ) );
		} );
	};

	var onRecaptchaApiReady = function( callback ) {
		window = elementorFrontend.getScopeWindow();
		if ( window.grecaptcha ) {
			callback();
		} else {
			// If not ready check again by timeout..
			setTimeout( function() {
				onRecaptchaApiReady( callback );
			}, 350 );
		}
	};

	onRecaptchaApiReady( function() {
		addRecaptcha( $element );
	} );
};

},{}],7:[function(require,module,exports){
module.exports = function() {
	var settings = {};

	var initSettings = function() {
		settings.classes = {
			fitHeight: 'elementor-fit-height'
		};

		settings.selectors = {
			postThumbnail: '.elementor-post__thumbnail'
		};
	};

	var initHandlers = function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/portfolio.default', require( './handlers/portfolio' ) );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/posts.classic', require( './handlers/posts' ) );
	};

	var init = function() {
		initSettings();

		initHandlers();
	};

	this.fitImage = function( $post, itemRatio ) {
		var $imageParent = $post.find( settings.selectors.postThumbnail ),
			$image = $imageParent.find( 'img' ),
			image = $image[0],
			imgRatio = image.naturalHeight / image.naturalWidth;

		$imageParent.toggleClass( settings.classes.fitHeight, imgRatio < itemRatio );
	};

	init();
};

},{"./handlers/portfolio":8,"./handlers/posts":9}],8:[function(require,module,exports){
var Portfolio = function( $element, $ ) {
	var elements = {},
		settings = {};

	var filterItems = function( term ) {
		if ( '__all' === term ) {
			elements.$items.removeClass( settings.classes.hide );

			return;
		}

		elements.$items.not( '.elementor-filter-' + term ).addClass( settings.classes.hide );

		elements.$items.filter( '.elementor-filter-' + term ).removeClass( settings.classes.hide );
	};

	var arrangeGrid = function() {
		var $activeItems = elements.$items.not( '.' + settings.classes.hide ),
			itemWidth = $activeItems.outerWidth(),
			itemHeight = itemWidth * settings.itemRatio;

		$activeItems.height( itemHeight );

		$activeItems.each( function( index ) {
			var $item = $( this );

			var leftPos = ( itemWidth + settings.itemGap ) * ( index % settings.colsCount ),
				top = ( itemHeight + settings.itemGap ) * Math.floor( index / settings.colsCount );

			if ( elementorFrontend.config.is_rtl ) {
				leftPos = -leftPos;
			}

			$item.css( 'transform', 'translate3d(' + leftPos + 'px, ' + top + 'px, 0)' );
		} );

		var containerHeight = ( itemHeight + settings.itemGap ) * Math.ceil( $activeItems.length / settings.colsCount );

		elements.$container.height( containerHeight );
	};

	var fitImages = function() {
		elements.$items.each( function() {
			elementorProFrontend.modules.posts.fitImage( $( this ), settings.itemRatio );
		} );
	};

	var setColsCountSettings = function() {
		var currentDeviceMode = getComputedStyle( elements.$container[0], ':after' ).content.replace( /"/g, '' );

		switch ( currentDeviceMode ) {
			case 'mobile':
				settings.colsCount = settings.colsMobile;
				break;
			case 'tablet':
				settings.colsCount = settings.colsTablet;
				break;
			default:
				settings.colsCount = settings.cols;
		}
	};

	var adjustResponsiveGrid = function() {
		setColsCountSettings();

		var gapWidthRemove = ( settings.itemGap * ( settings.colsCount - 1 ) ) / settings.colsCount;

		elements.$items.css( 'width', 'calc(' + ( 100 / settings.colsCount ).toFixed( 3 ) + '% - ' + gapWidthRemove + 'px)' );
	};

	var onFilterButtonClick = function() {
		var $button = $( this );

		elements.$filterButtons.removeClass( settings.classes.active );

		$button.addClass( settings.classes.active );

		filterItems( $button.data( 'filter' ) );

		arrangeGrid();
	};

	var onWindowResize = function() {
		adjustResponsiveGrid();

		arrangeGrid();
	};

	var initSettings = function() {
		settings = elements.$container.data( 'portfolio-options' );

		settings.itemGap = +settings.itemGap;

		settings.classes = {
			active: 'elementor-active',
			hide: 'elementor-hide',
			fitHeight: 'elementor-fit-height'
		};
	};

	var initElements = function() {
		elements.$container = $element.find( '.elementor-portfolio' );

		elements.$items = elements.$container.find( '.elementor-portfolio-item' );

		elements.$filterButtons = $element.find( '.elementor-portfolio__filter' );

		elements.$scopeWindow = $( elementorFrontend.getScopeWindow() );
	};

	var bindEvents = function() {
		elements.$filterButtons.on( 'click', onFilterButtonClick );

		elementorFrontend.elementsHandler.addExternalListener( $element, 'resize', onWindowResize );
	};

	var run = function() {
		adjustResponsiveGrid();

		arrangeGrid();

		// For slow browsers
		setTimeout( fitImages, 0 );
	};

	var init = function() {
		initElements();

		initSettings();

		bindEvents();

		run();
	};

	init();
};

module.exports = function( $scope, $ ) {
	if ( ! $scope.find( '.elementor-portfolio' ).length ) {
		return;
	}

	new Portfolio( $scope, $ );
};

},{}],9:[function(require,module,exports){
var ImageRatio = function() {
	var self = this,
		settings = {};

	var onImageRatioChange = function( model, view ) {
		if ( ! model.changed.classic_item_ratio ) {
			return;
		}

		view.$el.find( settings.selectors.postsContainer ).data( 'item-ratio', model.changed.classic_item_ratio.size );

		self.fitPostsImage( view.$el, jQuery );
	};

	var onPanelShow = function( panel, model, view ) {
		var settingsModel = model.get( 'settings' );

		settingsModel.on( 'change', function() {
			onImageRatioChange( settingsModel, view );
		} );
	};

	var initSettings = function() {
		settings.selectors = {
			postsContainer: '.elementor-posts',
			post: '.elementor-post',
			postThumbnailImage: '.elementor-post__thumbnail img'
		};
	};

	var addHooks = function() {
		if ( elementorFrontend.isEditMode() ) {
			elementor.hooks.addAction( 'panel/open_editor/widget/posts', onPanelShow );
		}
	};

	var init = function() {
		initSettings();

		addHooks();
	};

	this.fitPostsImage = function( $postsContainer, $ ) {
		var $posts = $postsContainer.find( settings.selectors.post ),
			itemRatio = $postsContainer.find( settings.selectors.postsContainer ).data( 'item-ratio' );

		$posts.each( function() {
			var $post = $( this ),
				$image = $post.find( settings.selectors.postThumbnailImage );

			elementorProFrontend.modules.posts.fitImage( $post, itemRatio );

			$image.on( 'load', function() {
				elementorProFrontend.modules.posts.fitImage( $post, itemRatio );
			} );
		} );
	};

	init();
};

var imageRatio = new ImageRatio();

module.exports = imageRatio.fitPostsImage;

},{}],10:[function(require,module,exports){
module.exports = function() {
	elementorFrontend.hooks.addAction( 'frontend/element_ready/slides.default', require( './handlers/slides' ) );
};

},{"./handlers/slides":11}],11:[function(require,module,exports){
module.exports = function( $scope, $ ) {
	var $slider = $scope.find( '.elementor-slides' );

	if ( ! $slider.length ) {
		return;
	}

	$slider.slick( $slider.data( 'slider_options' ) );

	// Add and remove animation classes to slide content, on slider change
	if ( '' === $slider.data( 'animation' ) ) {
		return;
	}

	$slider.on( {
		beforeChange: function() {
			var $sliderContent = $slider.find( '.elementor-slide-content' );

			$sliderContent.removeClass( 'animated ' + $slider.data( 'animation' ) ).hide();
		},

		afterChange: function( event, slick, currentSlide ) {
			var $currentSlide = $( slick.$slides.get( currentSlide ) ).find( '.elementor-slide-content' ),
				animationClass = $slider.data( 'animation' );

			$currentSlide
				.show()
				.addClass( 'animated ' + animationClass );
		}
	} );
};

},{}]},{},[1])
//# sourceMappingURL=frontend.js.map
