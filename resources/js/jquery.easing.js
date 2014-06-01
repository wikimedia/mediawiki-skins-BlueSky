( function ( $ ) {
	$.extend( $.easing, {
		easeInOutQuad: function( x, t, b, c, d ) {
			if ( ( t /= d / 2 ) < 1 ) {
				return c / 2 * t * t + b;
			}
			return -c / 2 * ( ( --t ) * ( t - 2 ) - 1 ) + b;
		},
		easeInOutQuint: function( x, t, b, c, d ) {
			if ( ( t /= d / 2 ) < 1 ) {
				return c / 2 * t * t * t * t * t + b;
			}
			return c / 2 * ( ( t -= 2 ) * t * t * t * t + 2 ) + b;
		},
		easeInOutQuart: function( x, t, b, c, d ) {
			if ( ( t /= d / 2 ) < 1 ) {
				return c / 2 * t * t * t * t + b;
			}
			return -c / 2 * ( ( t -= 2 ) * t * t * t - 2 ) + b;
		},
		easeInOutExpo: function( x, t, b, c, d ) {
			if ( t === 0 ) {
				return b;
			}
			if ( t == d ) {
				return b + c;
			}
			if ( ( t /= d / 2 ) < 1 ) {
				return c / 2 * Math.pow( 2, 10 * ( t - 1 ) ) + b;
			}
			return c / 2 * ( -Math.pow( 2, -10 * --t ) + 2 ) + b;
		},
		easeInOutBack: function( x, t, b, c, d, s ) {
			if ( s === undefined ) {
				s = 1.70158;
			}
			if ( ( t /= d / 2 ) < 1 ) {
				return c / 2 * ( t * t * ( ( ( s *= ( 1.525 ) ) + 1 ) * t - s ) ) + b;
			}
			return c / 2 * ( ( t -= 2 ) * t * ( ( ( s *= ( 1.525 ) ) + 1 ) * t + s ) + 2 ) + b;
		}
	} );
}( jQuery ) );