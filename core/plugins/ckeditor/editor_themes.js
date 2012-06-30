CKEDITOR.replace( 'minieditor',
	{
		removePlugins : 'bidi,button,dialogadvtab,div,filebrowser,flash,format,forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,stylescombo,table,tabletools,templates',
		disableObjectResizing : true,
		toolbar :
		[
			['Bold', 'Italic','Underline'],
			['TextColor'],
			['NumberedList','BulletedList','-','Blockquote'],
		]
} );