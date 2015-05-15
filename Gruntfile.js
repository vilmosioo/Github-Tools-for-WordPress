'use strict';

module.exports = function (grunt) {

	// Load grunt tasks
	grunt.loadNpmTasks('grunt-replace');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-bump');

	// Define the configuration for all the tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON('./package.json'),
		clean: {
			dist: {
				src: 'dist'
			}
		},
		copy: {
			dist: {
				files: [
					{
						expand: true,
						cwd: '.',
						src: [
							'**/*',
							'!{node_modules,dist,.git,ci}/**',
							'!Gruntfile.js',
							'!package.json',
							'!.gitignore',
							'!.gitmodules'
						],
						dest: 'dist'
					}
				]
			}
		},
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
						cwd: 'dist',
						dest: 'dist',
						src: [
							'**/*'
						]
					}
				]
			}
		},
		bump: {
			options: {
				files: ['package.json'],
				updateConfigs: ['pkg'],
				commit: true,
				commitMessage: 'Release v%VERSION% [skip ci]',
				commitFiles: ['package.json'],
				createTag: true,
				tagName: 'v%VERSION%',
				tagMessage: 'Version %VERSION%',
				push: false,
				pushTo: 'origin',
				gitDescribeOptions: '--tags --always --abbrev=1 --dirty=-d',
				globalReplace: false
			}
		}
	});

	grunt.registerTask('default', [
		'replace'
	]);
};