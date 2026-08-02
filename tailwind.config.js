/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./*.php',
		'./inc/**/*.php',
		'./template-parts/**/*.php',
		'./assets/js/**/*.js',
	],
	darkMode: 'class',
	theme: {
		extend: {
			fontFamily: {
				sans: [ 'Vazirmatn', 'sans-serif' ],
			},
			colors: {
				dnavy: '#123c52',
				dgold: '#c39b45',
				dcream: '#f2efe6',
			},
		},
	},
	plugins: [],
};
