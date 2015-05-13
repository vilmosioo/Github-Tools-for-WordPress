'use strict';

module.exports = function (grunt) {

	// Load grunt tasks
	grunt.loadNpmTasks('grunt-replace');

	// Define the configuration for all the tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON('./package.json'),
		replace: {
			dist: {
				options: {
					variables: {
						'version' : '<%= pkg.version %>'
					}
				},
				files: [
					{
						expand: true,
						dot: true,
						cwd: '.',
						dest: '.',
						src: [
							'**/*.{js,css,txt,md}',
							'!node_modules/**/*',
							'!.git/**/*',
							'!includes/**/*'
						]
					}
				]
			}
		}
	});

	grunt.registerTask('default', [
		'replace'
	]);
};