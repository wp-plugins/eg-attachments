function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function insertEGAttachmentsShortCode() {
	
	var tagtext = "[attachments";

	var orderby   = document.getElementById('orderby').value;
	var sortorder = document.getElementById('sortorder').value;
	if (sortorder != 0 && orderby != 0 )
		tagtext = tagtext + " orderby=\"" + orderby + " " + sortorder + "\"";

	var listsize = document.getElementById('listsize').value;
	if (listsize != 0 )
		tagtext = tagtext + " size=" + listsize;
	
	var doctype = document.getElementById('doctype').value;
	if (doctype != 0 )
		tagtext = tagtext + " doctype=" + doctype;

	var doclist = document.getElementById('doclist').options;
	id_list = ""
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

	tagtext = tagtext + "]";
		
	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
