/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
App.Templates.html = {
    WEB: {
        hint: ['']
    }
};

// Internals
var Tpl = App.Templates;

var Templator = function()
{
    var init = function()
    {
        fb.info('Templator work');
        Templator.splitThemAll();
        Templator.freezeTplIndexes();
    };


    /**
     * Split the tpl strings into arrays
     */
    Templator.splitThemAll = function(){
        fb.info('splitting tpls');
        jQuery.each(App.Templates.html, function(o){
            //try{
            var tpls = App.Templates.html[o];
            jQuery.each(tpls, function(t){
                tpls[t] = tpls[t][0].split('~!');
            });
            //}catch(e){fb.error('%o %o', o, e);}
        });

    },

    /**
     * Iterates tpls
     */
    Templator.freezeTplIndexes = function(){
        fb.info('freezing tpl keys');
        jQuery.each(App.Templates.html, Templator.cacheTplIndexes);
    },

    /**
     * Grab the tpl group key and process it
     */
    Templator.cacheTplIndexes = function(key)
    {
        var tpls = App.Templates.html[key];

        jQuery.each(tpls, function(o)
        {
            var tpl = tpls[o];
            Templator.catchIndex(key, o, tpl);
        });
    },

    /**
     * Set the indexes
     */
    Templator.catchIndex = function(key, ref_key, tpl)
    {
        'undefined' == typeof App.Templates._indexes[key] ? App.Templates._indexes[key] = {} : false;
        'undefined' == typeof App.Templates._indexes[key][ref_key] ?
        App.Templates._indexes[key][ref_key] = {} : false;

        jQuery(tpl).each(function(index, o) {
            if (':' == o.charAt(0)) {
                App.Templates._indexes[key][ref_key][o.toString()] = index;
            }
        });
    }

    /**
     * Get concrete templates
     */
    init();
    return Templator;
};

Templator.getTemplate = function(ns, key){
    return [
        App.Templates._indexes[ns][key],
        App.Templates.html[ns][key].slice(0)
    ];
}
// init templator
Tpl.Templator = Templator();

Tpl.get = function(key, group){
    return Tpl.Templator.getTemplate(group, key);
}
