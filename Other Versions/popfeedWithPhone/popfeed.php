<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentPopFeed extends JPlugin {

  function plgContentPopFeed( &$subject, $config ) {
    parent::__construct( $subject, $config );
  }

  function onPrepareContent(&$row, &$params, $page=0) {
    if (is_object($row)) {
        $text = &$row->text;
    }
    else {
      $text = &$row;
    }
    global $mainframe;

    $plugin = & JPluginHelper::getPlugin('content', 'popfeed');
    $pluginParams = new JParameter($plugin->params);
    
    $auto_all = $pluginParams->def('auto_all', false);
    
    if (!$auto_all) {
      if (JString::strpos($text, '{popfeed}') === false) {
        return true;
      }
      
      if (JString::strpos($text, '{/popfeed}') === false) {
        return true;
      }        
    }
    else {
      $text = $text . '{popfeed}Leave your feedback!{/popfeed}';
    }
    
    // check for a valid recipient
    $recipient = $pluginParams->def('email_recipient', '');
    if ($recipient === "") {
      $myReplacement = '<span style="color: #f00;">No recipient specified</span>';
      $text = JString::str_ireplace('{popfeed}', $myReplacement, $text);
      $text = JString::str_ireplace('{/popfeed}', '', $text);
      return true;
    }
    
    if ($recipient === "email@email.com") {
      $myReplacement = '<span style="color: #f00;">Mail Recipient is specified as email@email.com.<br/>Please change it from the Module parameters.</span>';
      $text = JString::str_ireplace('{popfeed}', $myReplacement, $text);
      $text = JString::str_ireplace('{/popfeed}', '', $text);
      return true;
    }  

    if ($_POST["popfeedSecondForm"]) {
          
      $fromName = $pluginParams->def('from_name', 'PopFeed');
      $fromEmail = $pluginParams->def('from_email', 'popfeed@yoursite.com');     
      
      $afterText = $pluginParams->def('after_text', 'Thank you for your feedback.');
      $errorText = $pluginParams->def('error_text', 'Your feedback could not be submitted. Please try again.');    
    
      $invalidEmail = $pluginParams->def('invalid_email', 'Submitted email is invalid. Please try again.');
      
      if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $_POST["pf_email"])) {
        $myErrorMessage = '<div class="popfeed_error">' . $invalidEmail . '</div><br/>{popfeed}';
        $text = JString::str_ireplace('{popfeed}', $myErrorMessage, $text);
      }
      else {
        $mMessage = 'You received a message from ';
        if ($_POST["pf_name"]) {
          $mMessage = $mMessage . $_POST["pf_name"] . ' ';
        }
        $mMessage = $mMessage . '(' . $_POST["pf_email"] . ")\n";
        if ($_POST["pf_phone"]) {
          $mMessage = $mMessage . 'Phone: ' . $_POST["pf_phone"] . "\n";
        }
        $mMessage = $mMessage . "\n";
        $mMessage = $mMessage . $_POST["pf_message"];
        if (!JUtility::sendMail($fromEmail, $fromName, $recipient, $_POST["pf_subject"], $mMessage, false)) {
          $myErrorMessage = '<div class="popfeed_error">' . $errorText . '</div><br/>{popfeed}';
          $text = JString::str_ireplace('{popfeed}', $myErrorMessage, $text);
        }
        else {
          $myOKMessage = '<div class="popfeed_message">' . $afterText . '</div><br/>{popfeed}'; 
          $text = JString::str_ireplace('{popfeed}', $myOKMessage, $text);
        }        
      }
    }
    
    // get the url that the form will post
    // this should be the plugin's page url.   
    //
    // NOT ESSENTIAL IF POSTED..!
    //
    $exact_url = $pluginParams->def('exact_url', true);
    $disable_https = $pluginParams->def('disable_https', false);
    $fixed_url = $pluginParams->def('fixed_url', true);     

    if ($fixed_url) {
      $url = $pluginParams->def('fixed_url_address', "");
    }
    else {
      if (!$exact_url) {
        $url = JURI::current();
      }
      else {
        if (!$disable_https) {
          $url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        }
        else {
          $url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        }
      }
    }
    
    // get the rest of the parameters and build the form
    //
    $myNameLabel    = $pluginParams->def('name_label', 'Name:');
    $myEmailLabel   = $pluginParams->def('email_label', 'Email:'); 
    $mySubjectLabel = $pluginParams->def('subject_label', 'Subject:'); 
    $myPhoneLabel   = $pluginParams->def('phone_label', 'Phone:'); 
    $myMessageLabel = $pluginParams->def('message_label', 'Message:'); 
    $myPreText      = $pluginParams->def('pre_text', '<h2>Leave us Feedback</h2><br/>Use the form below to provide us feedback.<br/>Thank you<br/><br/>');
    $myButtonText   = $pluginParams->def('button_text', 'Submit Feedback');
    
    $autos = $pluginParams->def('auto_subject', true); 
    $auto_subject   = '';
    if ($autos) {
      if ((JRequest::getVar('option') == 'com_content') && (JRequest::getVar('view') == 'article')) {
        $db =& JFactory::getDBO();
        $myArticleId = JRequest::getVar( 'id' );
        $query = 'SELECT * FROM `#__content` WHERE `id` = "'.mysql_escape_string($myArticleId).'"';
        $db->setQuery($query);
        $myResult = $db->loadObject();
        $auto_subject = $myResult->title;
      }      
    }
    
    
    $myFormURL = JURI::base() . 'plugins/content/popfeed/form.php';
    
    $myScript = '<script>' . "\n" .
                'function sendForm() {' . "\n" .
                '  var w = window.open("","Popup_Window","width=400,height=500,menubar=no,resizable=yes");' . "\n" . 
                '  if (w.opener == null) w.opener = self;' . "\n" .
                '  if (w.opener.theForm == null) w.opener.theForm = document.popfeedSecondForm;' . "\n" .
                '  var a = window.setTimeout("document.popfeedFirstForm.submit();",500);' . "\n" .
                '  w.focus();' . "\n" .
                '  return true;' . "\n" .
                '}' . "\n" .
                '</script>' . "\n";
    
    $myForm = '<form name="popfeedFirstForm" action="'.$myFormURL.'" method="post" target="Popup_Window">' .
              '<input type="hidden" name="name_label" value="' . $myNameLabel . '"/>' .
              '<input type="hidden" name="email_label" value="' . $myEmailLabel . '"/>' .
              '<input type="hidden" name="subject_label" value="' . $mySubjectLabel . '"/>' .
              '<input type="hidden" name="phone_label" value="' . $myPhoneLabel . '"/>' .
              '<input type="hidden" name="message_label" value="' . $myMessageLabel . '"/>' .
              '<input type="hidden" name="pre_text" value="' . $myPreText . '"/>' .
              '<input type="hidden" name="button_text" value="' . $myButtonText . '"/>' .
              '<input type="hidden" name="auto_subject" value="' . $auto_subject . '"/>' .
              '<input type="hidden" name="formActive" value="true"/>' .
              '</form>' . "\n";
              
    $mySecondForm = '<form name="popfeedSecondForm" action="'.$url.'" method="post">' .
                    '<input type="hidden" name="pf_name"/>' .
                    '<input type="hidden" name="pf_email"/>' .
                    '<input type="hidden" name="pf_subject"/>' .
                    '<input type="hidden" name="pf_phone"/>' .
                    '<input type="hidden" name="pf_message"/>' .
                    '<input type="hidden" name="popfeedSecondForm" value="true"/>' .
                    '</form>' . "\n";              
    
    $myLinkStart = $myScript . $myForm . $mySecondForm .'<div class="popfeedLink"><a href="#" onClick="sendForm();">';

    $text = JString::str_ireplace('{popfeed}', $myLinkStart, $text);
    $text = JString::str_ireplace('{/popfeed}', '</a></div>', $text);
    
    
    return true;
  }

}