<?php
/*------------------------------------------------------------------------
# popfeed - PopFeed
# ------------------------------------------------------------------------
# author    Christopher Mavros - Mavrosxristoforos.com
# copyright Copyright (C) 2008 Mavrosxristoforos.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: https://mavrosxristoforos.com
# Technical Support:  Forum - https://mavrosxristoforos.com/support/forum
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();

// Initialize Form Scripts & Styles
$style = '';
$script = '';
switch($helper->popfeed_appearance) {
  case '0':
    $style = '
#%form_id%_wrapper { display: none; }
#%form_id% { padding: 10px; }
    ';
    $script = '
function showPopFeed(form_id) {
  return true;
}
if (window.jQuery) {
  jQuery(document).ready(function(){jQuery("#%form_id%_link").colorbox({inline:true});});
}
';
    break;
  case '1':
    $style = '
#%form_id%_box { display: none; width: 100%; height: 100%; position: fixed; background: #000; opacity: 0.9; top: 0; left: 0; }
#%form_id%_wrapper { display: none; padding: 10px; position: fixed; width: 40%; height: 70%; top: 10%; left: 30%; overflow: scroll; background: #FFF; }
.close_link { float: right; }
    ';
    $script = '
function showPopFeed(form_id) {
  document.getElementById(form_id+"_box").setAttribute("style", "display: block;");
  document.getElementById(form_id+"_wrapper").setAttribute("style", "display: block;");
}
function hidePopFeed(form_id) {
  document.getElementById(form_id+"_box").removeAttribute("style");
  document.getElementById(form_id+"_wrapper").removeAttribute("style");
}
    ';
    break;
  case '2':
    $style= '
#%form_id%_wrapper { display: none; }
    ';
    $script = '
function showPopFeed(form_id) {
  jQuery("#"+form_id+"_wrapper").slideToggle(400, function() {
    element = document.getElementById(form_id+"_wrapper");
    if (element.offsetWidth > 0 || element.offsetHeight > 0) {
      document.getElementById(form_id+"_link").innerHTML = "'.$helper->i18n('CLOSE_FORM', 'Close Form').'";
    }
    else {
      document.getElementById(form_id+"_link").innerHTML = "'.$helper->i18n('LEAVE_YOUR_FEEDBACK', 'Leave your feedback!').'";
    }
  });
}
    ';
    break;
  case '3':
    $style= '
#%form_id%_wrapper { display: none; }
    ';
    $script = '
      var showing_form_%form_id% = false;
function showPopFeed(form_id) {
  element = document.getElementById(form_id+"_wrapper");
  showing_form_%form_id% = !showing_form_%form_id%;
  if (showing_form_%form_id%) {
    element.setAttribute("style", "display: block;");
    document.getElementById(form_id+"_link").innerHTML = "'.$helper->i18n('CLOSE_FORM', 'Close Form').'";
  }
  else {
    element.removeAttribute("style");
    document.getElementById(form_id+"_link").innerHTML = "'.$helper->i18n('LEAVE_YOUR_FEEDBACK', 'Leave your feedback!').'";
  }
}
    ';
    break;
}
$document->addStyleDeclaration(str_replace('%form_id%', $form_id, $style));
$document->addScriptDeclaration(str_replace('%form_id%', $form_id, $script));

?>