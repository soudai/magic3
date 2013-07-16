<?php
defined('_JEXEC') or die;

/**
 * Template for Joomla! CMS, created with Artisteer.
 * See readme.txt for more details on how to use the template.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

// Create alias for $this object reference:
$document = $this;

// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . $document->template;

Artx::load("Artx_Page");

// Initialize $view:
$view = $this->artx = new ArtxPage($this);

// Decorate component with Artisteer style:
$view->componentWrapper();

JHtml::_('behavior.framework', true);

?>
<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $document->language; ?>">
<head>
    <jdoc:include type="head" />
    <link rel="stylesheet" href="<?php echo $document->baseurl; ?>/templates/system/css/system.css" />
    <link rel="stylesheet" href="<?php echo $document->baseurl; ?>/templates/system/css/general.css" />

    <!-- Created by Artisteer v4.1.0.59861 -->
    
    
    <meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width">

    <!--[if lt IE 9]><script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.css" media="screen">
    <!--[if lte IE 7]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.ie7.css" media="screen" /><![endif]-->
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.responsive.css" media="all">
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Serif&amp;subset=latin">


    <script>if ('undefined' != typeof jQuery) document._artxJQueryBackup = jQuery;</script>
    <script src="<?php echo $templateUrl; ?>/jquery.js"></script>
    <script>jQuery.noConflict();</script>

    <script src="<?php echo $templateUrl; ?>/script.js"></script>
    <?php $view->includeInlineScripts() ?>
    <script>if (document._artxJQueryBackup) jQuery = document._artxJQueryBackup;</script>
    <script src="<?php echo $templateUrl; ?>/script.responsive.js"></script>
</head>
<body>

<div id="art-main">
<header class="art-header"><?php echo $view->position('header', 'art-nostyle'); ?>


    <div class="art-shapes">
<div class="art-textblock art-object1703320768" data-left="0%">
        <div class="art-object1703320768-text-container">
        <div class="art-object1703320768-text"><p style="color: #31341E; font-size:24px;font-family:'Droid Serif', Arial, 'Arial Unicode MS', Helvetica, Sans-Serif;font-weight:normal;font-style:normal;text-decoration:none">My Blog</p></div>
    </div>
    
</div>
            </div>



<div class="art-textblock art-object55016022" data-left="99.54%">
    <form class="art-search" name="Search" action="<?php echo $document->baseurl; ?>/index.php" method="post">
    <input type="text" value="" name="searchword">
        <input type="hidden" name="task" value="search">
<input type="hidden" name="option" value="com_search">
<input type="submit" value="" name="search" class="art-search-button">
        </form>
</div>
                
                    
</header>
<?php if ($view->containsModules('user3', 'extra1', 'extra2')) : ?>
<nav class="art-nav">
    
<?php if ($view->containsModules('extra1')) : ?>
<div class="art-hmenu-extra1"><?php echo $view->position('extra1'); ?></div>
<?php endif; ?>
<?php if ($view->containsModules('extra2')) : ?>
<div class="art-hmenu-extra2"><?php echo $view->position('extra2'); ?></div>
<?php endif; ?>
<?php echo $view->position('user3'); ?>
 
    </nav>
<?php endif; ?>
<div class="art-sheet clearfix">
            <?php echo $view->position('banner1', 'art-nostyle'); ?>
<?php echo $view->positions(array('top1' => 33, 'top2' => 33, 'top3' => 34), 'art-block'); ?>
<div class="art-layout-wrapper">
                <div class="art-content-layout">
                    <div class="art-content-layout-row">
                        <?php if ($view->containsModules('left')) : ?>
<div class="art-layout-cell art-sidebar1">
<?php echo $view->position('left', 'art-block'); ?>



                        </div>
<?php endif; ?>
                        <div class="art-layout-cell art-content">
<?php
  echo $view->position('banner2', 'art-nostyle');
  if ($view->containsModules('breadcrumb'))
    echo artxPost($view->position('breadcrumb'));
  echo $view->positions(array('user1' => 50, 'user2' => 50), 'art-article');
  echo $view->position('banner3', 'art-nostyle');
  echo artxPost(array('content' => '<jdoc:include type="message" />', 'classes' => ' art-messages'));
  echo '<jdoc:include type="component" />';
  echo $view->position('banner4', 'art-nostyle');
  echo $view->positions(array('user4' => 50, 'user5' => 50), 'art-article');
  echo $view->position('banner5', 'art-nostyle');
?>



                        </div>
                        <?php if ($view->containsModules('right')) : ?>
<div class="art-layout-cell art-sidebar2">
<?php echo $view->position('right', 'art-block'); ?>


                        </div>
<?php endif; ?>
                    </div>
                </div>
            </div>
<?php echo $view->positions(array('bottom1' => 33, 'bottom2' => 33, 'bottom3' => 34), 'art-block'); ?>
<?php echo $view->position('banner6', 'art-nostyle'); ?>

<footer class="art-footer">
<?php if ($view->containsModules('copyright')) : ?>
    <?php echo $view->position('copyright', 'art-nostyle'); ?>
<?php else: ?>
<p><a href="#">Link1</a> | <a href="#">Link2</a> | <a href="#">Link3</a></p>
<p>Copyright © 2013. All Rights Reserved.</p>
<?php endif; ?>
</footer>

    </div>
    <p class="art-page-footer">
        <span id="art-footnote-links"><a href="http://www.artisteer.com/?p=joomla_templates" target="_blank">Joomla template</a> created with Artisteer.</span>
    </p>
</div>



<?php echo $view->position('debug'); ?>
</body>
</html>