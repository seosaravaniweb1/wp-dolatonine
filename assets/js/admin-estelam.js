/**
 * جابه‌جایی ویرایشگر متن و فیلدهای استعلام در صفحه درج/ویرایش نوشته
 *
 * پرسش «آیا این محتوا استعلام است؟» دقیقا زیر ویرایشگر متن است:
 *   بله → ویرایشگر متن جمع می‌شود و باکس «جزئیات استعلام» باز می‌شود
 *   خیر → برعکس
 * بدون ذخیره کردن و بدون رفرش.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var radios = document.querySelectorAll( 'input.dolat-is-estelam' );
		if ( ! radios.length ) return;

		// فقط برای نوشته عادی معنا دارد
		var postIds = [ 'postdivrich', 'postexcerpt', 'dolat_related_estelam_box' ];
		// فقط برای استعلام معنا دارد
		var estelamIds = [ 'dolat_estelam_details', 'dolat_estelam_feedback' ];

		function boxes( ids ) {
			return ids
				.map( function ( id ) { return document.getElementById( id ); } )
				.filter( Boolean );
		}

		function isOn() {
			for ( var i = 0; i < radios.length; i++ ) {
				if ( radios[ i ].checked ) return '1' === radios[ i ].value;
			}
			return false;
		}

		// یادداشتی که جای ویرایشگر می‌نشیند تا صفحه خالی به‌نظر نرسد
		var note = document.createElement( 'div' );
		note.className = 'notice notice-info inline';
		note.style.margin = '0 0 12px';
		note.innerHTML = '<p>این نوشته یک <strong>استعلام</strong> است، پس ویرایشگر متن جمع شده و محتوا از باکس «جزئیات استعلام» پایین‌تر ساخته می‌شود.</p>';

		function apply() {
			var on = isOn();

			boxes( postIds ).forEach( function ( el ) { el.style.display = on ? 'none' : ''; } );
			boxes( estelamIds ).forEach( function ( el ) { el.style.display = on ? '' : 'none'; } );

			document.body.classList.toggle( 'dolat-estelam-on', on );

			var host = document.getElementById( 'dolat_estelam_toggle' );
			if ( on && host && ! note.parentNode ) {
				host.parentNode.insertBefore( note, host );
			} else if ( ! on && note.parentNode ) {
				note.parentNode.removeChild( note );
			}
		}

		Array.prototype.forEach.call( radios, function ( r ) {
			r.addEventListener( 'change', apply );
		} );
		apply();
	} );
} )();
