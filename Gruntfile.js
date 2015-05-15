'use strict';

module.exports = function (grunt) {

	// Load grunt tasks
	grunt.loadNpmTasks('grunt-replace');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-bump');
	grunt.loadNpmTasks('grunt-svn-checkout');

	// Define the configuration for all the tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON('./package.json'),
		clean: {
			dist: {
				src: 'dist'
			}
		},
		svn_checkout: {
			dist: {
				repos: [
					{
						path: ['dist'],
	          			repo: 'http://plugins.svn.wordpress.org/wp-github-tools/'
					}
				]
			}
		},
		copy: {
			options:{
				mode: true
			},
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
						dest: 'dist/wp-github-tools/trunk'
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
		mkdir: {
			dist:{
				options:{
					create: ['dist']
				}
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

	grunt.registerTask('release', [
		'clean',
		'mkdir',
		'svn_checkout',
		'copy'
	]);

	grunt.registerTask('default', [
		'build',
		'release'
	]);
};