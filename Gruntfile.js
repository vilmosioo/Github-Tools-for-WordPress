'use strict';

module.exports = function (grunt) {

	// Load grunt tasks
	grunt.loadNpmTasks('grunt-replace');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-bump');
	grunt.loadNpmTasks('grunt-svn-checkout');
	grunt.loadNpmTasks('grunt-push-svn');

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
			},
		},
		push_svn: {
			options: {
				remove: true,
				username: 'vilmosioo',
				password: process.env.WP_PASS
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
			trunk: {
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
			},
			tag: {
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
						dest: 'dist/wp-github-tools/tags/<%= pkg.version %>'
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
						cwd: 'dist/wp-github-tools',
						dest: 'dist/wp-github-tools',
						src: [
							'trunk/**/*',
							'tags/<%= pkg.version %>/**/*'
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
		'copy',
		'replace',
		'push_svn'
	]);

	grunt.registerTask('default', []);
};