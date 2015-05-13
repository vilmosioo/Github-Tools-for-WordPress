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
		},
		bump: {
			options: {
				files: ['package.json', 'bower.json'],
				updateConfigs: ['pck'],
				commit: true,
				commitMessage: 'Release v%VERSION% [skip ci]',
				commitFiles: ['-a'],
				createTag: true,
				tagName: 'v%VERSION%',
				tagMessage: 'Version %VERSION%',
				push: true,
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