'use strict';

module.exports = function (grunt) {

	// Load grunt tasks
	require('load-grunt-tasks')(grunt);

	// Define the configuration for all the tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON('./package.json'),
		clean: {
			dist: {
				src: ['dist', 'build']
			}
		},
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("yyyy-mm-dd") %> */'
			},
			dist: {
				files: [
					{
						expand: true,
						cwd: '.',
						src: [
							'**/*.js',
							'!{node_modules,build,dist,.git,ci}/**'
						],
						dest: 'build'
					}
				]
			}
		},
		compress: {
			build: {
				options: {
					archive: 'wp-github-tools.zip'
				},
				files: [
					{
						expand: true,
						cwd: 'build/',
						src: ['**/*'],
						dest: '.'
					}
				]
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
			},
		},
		push_svn: {
			options: {
				remove: true,
				username: 'vilmosioo',
				password: process.env.WP_PASS,
				message: 'Release v<%= pkg.version %>'
			},
			main: {
				src: 'dist/wp-github-tools',
				dest: 'http://plugins.svn.wordpress.org/wp-github-tools/',
				tmp: 'dist/.tmp'
			}
		},
		copy: {
			options:{
				mode: true
			},
			build: {
				files: [
					{
						expand: true,
						cwd: '.',
						src: [
							'**/*.{css,php,txt,md}',
							'!{node_modules,build,dist,.git,ci}/**'
						],
						dest: 'build'
					}
				]
			},
			trunk: {
				files: [
					{
						expand: true,
						cwd: 'build',
						src: [
							'**/*',
						],
						dest: 'dist/wp-github-tools/trunk'
					}
				]
			},
			tag: {
				files: [
					{
						expand: true,
						cwd: 'build',
						src: [
							'**/*'
						],
						dest: 'dist/wp-github-tools/tags/<%= pkg.version %>'
					}
				]
			}
		},
		replace: {
			build: {
				options: {
					variables: {
						'version' : '<%= pkg.version %>'
					}
				},
				files: [
					{
						expand: true,
						dot: true,
						cwd: 'build',
						dest: 'build',
						src: [
							'**/*.{txt,md,js,css,php}'
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

	grunt.registerTask('build', [
		'clean',
		'copy:build',
		'uglify',
		'replace',
		'compress'
	]);

	grunt.registerTask('release', [
		'mkdir',
		'svn_checkout',
		'copy:trunk',
		'copy:tag',
		'push_svn'
	]);

	grunt.registerTask('default', ['build']);
};