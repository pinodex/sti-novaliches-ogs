module.exports = function(grunt){

    grunt.initConfig({
        sass: {
            dist: {
                options: {
                    style: 'expanded'
                },
                
                files: [{
                    expand: true,
                    cwd:    'public/assets/sass',
                    dest:   'public/assets/css',
                    src:    ['**/*.scss'],
                    ext:    '.css'
                }]
            }
        },

        watch: {
            files: 'public/assets/sass/*.scss',
            
            tasks: [
                'sass'
            ]
        },

        cssmin: {
            options: {
                keepSpecialComments: 0
            },
            target: {
                files: [{
                    expand: true,
                    cwd:    'public/assets/css',
                    dest:   'public/assets/css',
                    ext:    '.min.css',
                    src:    ['*.css', '!*.min.css']
                }]
            }
        },

        uglify: {
            target: {
                files: {
                    'public/assets/js/app.min.js': [
                        'public/assets/js/app.js'
                    ]
                }
            }
        },

        concat: {
            options: {
                banner: '/*! (c) 2016 Raphael Marco */\n',
            },

            js: {
                src: [
                    'public/assets/js/vue.min.js',
                    'public/assets/js/app.min.js'
                ],

                dest: 'public/assets/js/compiled.js'
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');

    grunt.registerTask('default', [
        'watch'
    ]);

    grunt.registerTask('compile', [
        'sass',
        'cssmin',
        'uglify',
        'concat'
    ]);

};
