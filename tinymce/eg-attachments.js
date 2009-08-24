function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function insertEGAttachmentsShortCode() {
	
	var tagtext = "[attachments";

	var orderby   = document.getElementById('orderby').value;
	var sortorder = document.getElementById('sortorder').value;
	if (sortorder != 0 && orderby != 0 )
		tagtext = tagtext + " orderby=\"" + orderby + " " + sortorder + "\"";

	var listsize = document.getElementById('size').value;
	if (listsize != 0 )
		tagtext = tagtext + " size=" + listsize;

	var doclabel = document.getElementById('label').value;
	if (doclabel != 0 )
		tagtext = tagtext + " label=" + doclabel;

	var doctype = document.getElementById('doctype').value;
	if (doctype != 0 )
		tagtext = tagtext + " doctype=" + doctype;

	var doclist = document.getElementById('doclist').options;
	id_list = "";
	if ( doclist.length > 0 ) {
		for (var i=0; i<doclist.length; i++) {
			if (doclist[i].selected) {
				if (id_list == "") id_list = " docid=" + doclist[i].value;
				else id_list = id_list + ',' + doclist[i].value;
			}
		}		
	}
	tagtext = tagtext + id_list;

	var field_list = '';
	var field_caption = document.getElementById('field_caption');
	if (field_caption.checked) field_list = 'caption';
	
	var field_description = document.getElementById('field_description');
	if (field_description.checked) field_list = field_list  + ',description';

	if (field_list == '') field_list = 'none';
	if (field_list == 'caption') field_list = '';
	
	if (field_list != '')
		tagtext = tagtext + ' fields=' + field_list;

	var title = document.getElementById('title').value;
	if (title != 0 )
		tagtext = tagtext + " title=\"" + title + "\"";

	var titletag = document.getElementById('titletag').value;
	if (titletag != "h2" )
		tagtext = tagtext + " titletag=\"" + titletag + "\"";

	var force_saveas = document.getElementById('force_saveas').value;
	if (force_saveas != "-1" )
		tagtext = tagtext + " force_saveas=\"" + force_saveas + "\"";

	var logged_users = document.getElementById('logged_users').value;
	if (logged_users != "-1" )
		tagtext = tagtext + " logged_users=\"" + logged_users + "\"";

	tagtext = tagtext + "]";
		
	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
