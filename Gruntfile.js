module.exports = function(grunt) {
	'use strict';

	require('time-grunt')(grunt);
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// phplint: A simple wrapper around the php -l <filename> command.
		phplint:{
			plugin: [
				'src/**/*.php',
				'assets/views/**/*.php'
			]
		},

		// sass: Compile Sass to CSS.
		sass: {
			options: {
				outputStyle: 'compressed'
			},
			plugin: {
				files: {
					'assets/styles/dist/global.css': 'assets/styles/src/global.scss'
				}
			}
		},

		// autoprefixer: Parse CSS and add vendor prefixes to CSS rules using values from the Can I Use website
		autoprefixer: {
			options: {
				browsers: ['last 2 versions', '> 1%', 'ie >= 8'],
				expand: true,
				flatten: true
			},
			plugin: {
				src : 'assets/styles/dist/*.css'
			}
		},

		// cssmin: Compress CSS files
		cssmin: {
			plugin: {
				files: {
					'assets/styles/dist/*.min.css': 'assets/styles/src/*.css'
				}
			}
		},

		// jshint: Validate javascript files with JSHint
		jshint:{
			gruntfile:{
				src:[
					// Self-test
					'Gruntfile.js'
				]
			},
			plugin: {
				src:[
					'assets/scripts/src/**/*.js'
				]
			}
		},

		// concat: Concatenate files
		concat: {
			plugin: {
				src: [
					'assets/scripts/src/chimplet/utilities.js',
					'assets/scripts/src/chimplet/conditional-display.js',
					'assets/scripts/src/chimplet/toggle-checkboxes.js'
				],
				dest: 'assets/scripts/dist/common.js'
			}
		},

		// uglify: Minify (javascript)files with UglifyJS
		uglify: {
			plugin: {
				files: {
					'assets/scripts/dist/common.min.js': 'assets/scripts/dist/common.js'
				}
			}
		},

		// watch: Run tasks whenever watched files change
		watch: {
			styles: {
				files: [
					'assets/styles/src/**/*.scss'
				],
				tasks: ['sass', 'autoprefixer', 'notify:sass'],
				options: {
					spawn: false,
					livereload: false
				}
			},
			scripts: {
				files: [
					'assets/scripts/src/**/*.js'
				],
				tasks: ['concat', 'uglify', 'notify:concat']
			}
		},

		// notify: Automatic Notifications when Grunt tasks fail (or succeed)
		notify: {
			watch: {
				options: {
					// title: '<%= pkg.title %>',
					message: 'Keeping an eye out, Chief!'
				}
			},
			sass: {
				options: {
					// title: '<%= pkg.title %>',
					message: 'Sass compiled to CSS.'
				}
			},
			concat: {
				options: {
					// title: '<%= pkg.title %>',
					message: 'JavaScript is now concatenated'
				}
			}

		}

	});

	grunt.registerTask('default', ['notify:watch', 'watch']);

};
