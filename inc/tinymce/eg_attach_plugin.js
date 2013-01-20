(function() {

	tinymce.PluginManager.requireLangPack('egattachments');

	tinymce.create('tinymce.plugins.EGAttachments', {

		init : function(ed, url) {

			ed.addCommand('mceEGAttachments', function() {
				var post_id = tinymce.DOM.get('post_ID').value;
				ed.windowManager.open({
					file : url + '/eg_attach_popup.php?post_id=' + post_id,
					width : 500, /*+ ed.getLang('EGAttachments.delta_width', 0), */
					height : 550,  /* + ed.getLang('EGAttachments.delta_height', 0), */
					inline : 1
				})
			});
            ed.addButton('EGAttachments', {
                title: 'egattachments.desc',
                image: url + '/egattachments.png',
                cmd: 'mceEGAttachments'
            });
		},
        createControl : function(n, cm){
            return null;
        },
		getInfo : function() {
			return {
					longname  : 'EGAttachments',
					author 	  : 'Emmanuel GEORJON',
					authorurl : 'http://www.emmanuelgeorjon.com',
					infourl   : 'http://www.emmanuelgeorjon.com',
					version   : '1.8.2'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('EGAttachments', tinymce.plugins.EGAttachments);
})();
