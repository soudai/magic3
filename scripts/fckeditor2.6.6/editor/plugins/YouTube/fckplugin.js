// Register the related commands.
FCKCommands.RegisterCommand( 'YouTube', new FCKDialogCommand( FCKLang['DlgYouTubeTitle'], FCKLang['DlgYouTubeTitle'], FCKConfig.PluginsPath + 'YouTube/youtube.html', 450, 350 ) ) ;

// Create the "YouTube" toolbar button.
var oFindItem		= new FCKToolbarButton( 'YouTube', FCKLang['YouTubeTip'] ) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'YouTube/youtube.gif' ;

FCKToolbarItems.RegisterItem( 'YouTube', oFindItem ) ;			// 'YouTube' is the name used in the Toolbar config.
