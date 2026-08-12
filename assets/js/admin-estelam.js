/**
 * جابه‌جایی ویرایشگر متن و فیلدهای استعلام در صفحه ویرایش نوشته
 *
 * وقتی تیک «این محتوا یک استعلام است» زده شود:
 *   ویرایشگر متن پنهان → باکس «جزئیات استعلام» باز
 * و برعکس. هیچ رفرشی لازم نیست.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.getElementById( 'dolatIsEstelam' );
		if ( ! toggle ) return;

		// ویرایشگر کلاسیک + هر چیزی که فقط برای نوشته عادی معنا دارد
		var editorIds  = [ 'postdivrich', 'postexcerpt', 'dolat_related_estelam_box' ];
		// باکس‌هایی که فقط برای استعلام معنا دارند
		var estelamIds = [ 'dolat_estelam_details', 'dolat_estelam_feedback', 'tagsdiv-estelam_tag' ];

		function boxes( ids ) {
			return ids
				.map( function ( id ) { return document.getElementById( id ); } )
				.filter( Boolean );
		}

		function show( el, on ) {
			el.style.display = on ? '' : 'none';
		}

		// یادداشت راهنما وقتی ویرایشگر پنهان می‌شود
		var note = document.createElement( 'div' );
		note.className = 'notice notice-info inline';
		note.style.margin = '0 0 14px';
		note.innerHTML = '<p>این نوشته یک <strong>استعلام</strong> است، پس ویرایشگر متن پنهان شده و محتوا از باکس «جزئیات استعلام» پایین‌تر ساخته می‌شود.</p>';

		var reloadNote = document.createElement( 'div' );
		reloadNote.className = 'notice notice-warning inline';
		reloadNote.style.margin = '8px 0 0';
		reloadNote.innerHTML = '<p>برای باز شدن فیلدهای استعلام، یک‌بار <strong>ذخیره پیش‌نویس</strong> بزنید.</p>';

		var classicEditor = document.getElementById( 'postdivrich' );

		function apply() {
			var on = toggle.checked;

			boxes( editorIds ).forEach( function ( el ) { show( el, ! on ); } );
			boxes( estelamIds ).forEach( function ( el ) { show( el, on ); } );

			document.body.classList.toggle( 'dolat-estelam-on', on );

			// ویرایشگر بلوکی را نمی‌شود همین‌جا پنهان کرد؛ کاربر باید یک‌بار ذخیره کند
			if ( on && ! classicEditor && ! reloadNote.parentNode ) {
				toggle.closest( '.inside' ).appendChild( reloadNote );
			} else if ( ! on && reloadNote.parentNode ) {
				reloadNote.parentNode.removeChild( reloadNote );
			}

			// یادداشت بالای فیلدهای استعلام
			var details = document.getElementById( 'dolat_estelam_details' );
			if ( on && details && classicEditor && ! note.parentNode ) {
				details.parentNode.insertBefore( note, details );
			} else if ( ( ! on || ! classicEditor ) && note.parentNode ) {
				note.parentNode.removeChild( note );
			}
		}

		toggle.addEventListener( 'change', apply );
		apply();
	} );
} )();
