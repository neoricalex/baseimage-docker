// JavaScript Document
(function() {
    tinymce.PluginManager.add('mgmShortcodeBtn', function(editor, url) {
        editor.addCommand('mgmShortcodeCmd', function() {
            editor.windowManager.open({
                url : 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.downloads&method=shortcodes',
                width : 650 + editor.getLang('mgmShortcodeBtn.delta_width', 0),
                height : 400 + editor.getLang('mgmShortcodeBtn.delta_height', 0),
                inline : 1
            }, {
                plugin_url : url, // Plugin absolute URL
                someArg : 'mgmShortcodeBtn' // Custom argument
            });
        });
		
        editor.addButton('mgmShortcodeBtn', {
            title: 'MagicMembers Misc Tags',
            cmd: 'mgmShortcodeCmd',
            image: url + '/images/shortcodes.png',
            onPostRender: function() {
                var ctrl = this;
         
                editor.on('NodeChange', function(e) {
                    ctrl.active(e.element.nodeName == 'IMG');
                });
            }
        }); 
    });
        
    /*,
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : 'MagicMembers Misc Tags Editor Plugin',
                author : 'MagicMembers',
                authorurl : 'http://www.mgicmembers.com',
                infourl : 'http://www.mgicmembers.com/resources/mceplugins/mgmshortcode',
                version : '1.1'
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('mgmShortcodeBtn', tinymce.plugins.mgmShortcode);*/
})();