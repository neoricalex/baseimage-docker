// JavaScript Document
(function() {
    tinymce.PluginManager.add('mgmDownloadBtn', function(editor, url) {
        // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
        editor.addCommand('mgmDownloadCmd', function() {
            editor.windowManager.open({
                url : 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.downloads&method=editor',
                width : 650 + editor.getLang('mgmDownloadBtn.delta_width', 0),
                height : 400 + editor.getLang('mgmDownloadBtn.delta_height', 0),
                inline : 1
            }, {
                plugin_url : url, // Plugin absolute URL
                someArg : 'mgmDownloadBtn' // Custom argument
            });
        });
	
        editor.addButton('mgmDownloadBtn', {
            title: 'MagicMembers Download Tags',
            cmd: 'mgmDownloadCmd',
            image: url + '/images/download.png',
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
                longname: 'MagicMembers Download Tags Editor Plugin',
                author: 'MagicMembers',
                authorurl: 'http://www.mgicmembers.com',
                infourl: 'http://www.mgicmembers.com/resources/mceplugins/mgmdownload',
                version: '1.1'
            };
        }
    });
    tinymce.PluginManager.add('mgmDownloadBtn', tinymce.plugins.mgmDownload);*/
})();