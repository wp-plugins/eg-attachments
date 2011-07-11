function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function insertEGAttachmentsShortCode() {

	var tagtext = "[attachments";

	var orderby   = document.getElementById('orderby').value;
	var sortorder = document.getElementById('sortorder').value;
	if (sortorder != 'ASC' || orderby != 'title' )
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

	fields_list = "";
	if (! document.getElementById('default_fields').checked) {
		var fields = document.getElementsByName('fields');
		for (var i=0; i < fields.length; i++) {
			if ( fields[i].checked ) {
				if (fields_list == "") fields_list = " fields=\"" + fields[i].value;
				else fields_list = fields_list + ',' + fields[i].value;
			}
		}
		if (fields_list != "") fields_list = fields_list + "\""
	}
	tagtext = tagtext + fields_list;

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

	var title = document.getElementById('title').value;
	if (title != 0 )
		tagtext = tagtext + " title=\"" + title + "\"";

	var titletag = document.getElementById('titletag').value;
	if (titletag != "h2" )
		tagtext = tagtext + " titletag=\"" + titletag + "\"";

	var force_saveas = parseInt(document.getElementById('force_saveas').value);
	if (force_saveas > 0 )
		tagtext = tagtext + " force_saveas=1";

	var logged_users = parseInt(document.getElementById('logged_users').value);
	if (logged_users > 0 )
		tagtext = tagtext + " logged_users=1";

	if (document.getElementById('nofollow').checked) {
		tagtext = tagtext + " nofollow=1";
	}

	tagtext = tagtext + "]";

	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
