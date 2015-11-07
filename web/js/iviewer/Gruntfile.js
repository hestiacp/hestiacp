module.exports = function(grunt) {
  var fs = require('fs')
    , version = fs.readFileSync('Changelog', 'utf-8')
                  .split('\n')
                  .shift()
                  .split(/\s+/)
                  .pop();

  grunt.initConfig({
    version: {
      options: {
        release: version,
        pkg: 'package.json'
      },

      js: {
        options: {
          prefix: "Version:\\s+"
        },
        src: ['jquery.iviewer.js']
      },

      json: {
        src: ['package.json', 'iviewer.jquery.json', 'bower.json']
      }
    },

    uglify: {
      options: {
        report: "min",
        mangle: false,
        banner: 'set me later'
      },

      js: {
        files: {
          'jquery.iviewer.min.js': ['jquery.iviewer.js']
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-version');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('banner', function() {
    var banner = fs.readFileSync('jquery.iviewer.js', 'utf-8')
               .match(RegExp("^/\\*[^]*?\\*/"))[0] + '\n\n'

    grunt.config('uglify.options.banner', banner)
  });

  grunt.registerTask('changelog', function() {
    var date = new Date()
      , pad = function(len, symb) {
          if (symb.toString().length < len) {
            return '0' + symb;
          } else {
            return symb;
          }
        }
      , stamp = '(' + [
                      date.getFullYear(),
                      date.getMonth() + 1,
                      date.getDate()
                      ].map(pad.bind(null, 2))
                       .join('/') + ')\n'
      , file = grunt.file.read('Changelog')
                .replace(/(.*?)\n/, "$1 " + stamp);

    grunt.file.write('Changelog', file)
  })

  grunt.registerTask('release', ['version', 'banner', 'changelog', 'uglify'])
};
