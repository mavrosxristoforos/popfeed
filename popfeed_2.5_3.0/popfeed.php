<?php
//**
//* @Copyright Copyright (C) 2011 Christopher Mavros
//* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
//******/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentPopFeed extends JPlugin {

  function plgContentPopFeed( &$subject, $config ) {
    parent::__construct( $subject, $config );
  }

  public function onContentPrepare($context, &$row, &$params, $page = 0) {
    if (is_object($row)) {
        $text = &$row->text;
    }
    else {
      // We are probably in a module - we do not want to be here.
      $text = &$row;
      //$row = new stdClass();
      //$row->id = 0;
      return true;
    }

    if (isset($row->id)) {
      $auto_all = $this->params->get('auto_all', false);
      $auto_all_text = $this->params->get('auto_all_text', 'Leave your feedback!');
      $show_in_frontpage = $this->params->get('show_in_frontpage', false);
      $show_in_blog = $this->params->get('show_in_blog', false);
      $catids = $this->params->get('catids', '');
      $excluded_ids = $this->params->get('excluded_ids', '');

      $component_array = array();
      $component_array[] = 'com_content';

      $view_array = array();
      $view_array[] = 'article';

      if ($show_in_frontpage) {
        $view_array[] = 'frontpage';
      }
      if ($show_in_blog) {
        $view_array[] = 'blog';
      }

      if (!$auto_all) {
        if (JString::strpos($text, '{popfeed}') === false) {
          return true;
        }

        if (JString::strpos($text, '{/popfeed}') === false) {
          return true;
        }
      }
      else {
        $current_component = JRequest::getVar('option');
        $current_view = JRequest::getVar('view');

        $excluded = explode(',', $excluded_ids);

        if (in_array($current_component, $component_array)) {
          if (in_array($current_view, $view_array)) {
            $aid = $row->id;

            $includeme = true;

            if ($aid > 0) {
              if (in_array($aid, $excluded)) {
                $includeme = false;
              }
              if ($catids != '') {
                $db = JFactory::getDBO();
                $db->setQuery('SELECT COUNT(*) FROM `#__content` WHERE `id` = "'.$aid.'" AND `catid` IN ('.$catids.')');
                $acount = $db->loadResult();
                if ($acount == '0') {
                  $includeme = false;
                }
              }
            }
            if ($includeme == true) {
              $text = $text . '{popfeed}' . $auto_all_text . '{/popfeed}';
            }
          }
        }
      }

      // check for a valid recipient
      $recipient = $this->params->get('email_recipient', '');
      $autoem = $this->params->get('auto_recipient', '0');
      if ($autoem == '1') {
        //if (is_object($row)) {
        //  $db = JFactory::getDBO();
        //  $myArticleId = $row->id;
        //  $query = 'SELECT * FROM `#__content` WHERE `id` = "'.mysql_escape_string($myArticleId).'"';
        //  $db->setQuery($query);
        //  $myResult = $db->loadObject();
  //
        //  $query = 'SELECT * FROM `#__users` WHERE `id` = "'.mysql_escape_string($myResult->created_by).'"';
        //  $db->setQuery($query);
        //  $myAuthor = $db->loadObject();
  //
        //  $recipient = $myAuthor->email;
        //}
        if (is_object($row)) {
          $user_tmp = JFactory::getuser($row->created_by);
          $recipient = $user_tmp->email;
        }
      }

      // if set differently, set the new recipient
      $mposition = JString::strpos($text, '{popfeed_mailrecipient}');
      if ($mposition !== false) {
        $lposition = JString::strpos($text, '{/popfeed_mailrecipient}');
        $mlength = $lposition - ($mposition + 23);
        $recipient = JString::substr($text, $mposition + 23, $mlength);
        $text = str_replace('{popfeed_mailrecipient}'.$recipient.'{/popfeed_mailrecipient}', '', $text);
      }

      if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $recipient)) {
        $myReplacement = '<span style="color: #f00;">Invalid or No recipient specified</span>';
        $text = str_replace('{popfeed}', $myReplacement, $text);
        $text = str_replace('{/popfeed}', '', $text);
        return true;
      }

      if ($recipient === "email@email.com") {
        $myReplacement = '<span style="color: #f00;">Mail Recipient is specified as email@email.com.<br/>Please change it from the Module parameters.</span>';
        $text = str_replace('{popfeed}', $myReplacement, $text);
        $text = str_replace('{/popfeed}', '', $text);
        return true;
      }

      if (isset($_POST["popfeedSecondForm" . $row->id])) {

        $fromName = $this->params->get('from_name', 'PopFeed');
        $fromEmail = $this->params->get('from_email', 'popfeed@yoursite.com');

        $afterText = $this->params->get('after_text', 'Thank you for your feedback.');
        $errorText = $this->params->get('error_text', 'Your feedback could not be submitted. Please try again.');

        $invalidEmail = $this->params->get('invalid_email', 'Submitted email is invalid. Please try again.');

        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $_POST["pf_email"])) {
          $myErrorMessage = '<div class="popfeed_error">' . $invalidEmail . '</div><br/>{popfeed}';
          $text = str_replace('{popfeed}', $myErrorMessage, $text);
        }
        else {
          $mMessage = 'You received a message from ';
          if ($_POST["pf_name"]) {
            $mMessage = $mMessage . $_POST["pf_name"] . ' ';
          }
          $mMessage = $mMessage . '(' . $_POST["pf_email"] . ")\n\n";
          $mMessage = $mMessage . "Article: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\n\n";
          $mMessage = $mMessage . $_POST["pf_message"];

          $mailSender = JFactory::getMailer();
          $mailSender->addRecipient($recipient);
          if ($this->params->get('from_email', 'popfeed@yoursite.com') != 'popfeed@yoursite.com') {
            $mailSender->setSender(array($fromEmail,$fromName));
          }
          else {
            $mailSender->setSender(array($_POST["pf_email"],$_POST["pf_name"]));
          }
          $mailSender->setSubject($_POST["pf_subject"]);
          $mailSender->setBody($mMessage);

          if ($mailSender->Send() !== true) {
            $myErrorMessage = '<div class="popfeed_error">' . $errorText . '</div><br/>{popfeed}';
            $text = str_replace('{popfeed}', $myErrorMessage, $text);
          }
          else {
            $myOKMessage = '<div class="popfeed_message">' . $afterText . '</div><br/>{popfeed}';
            $text = str_replace('{popfeed}', $myOKMessage, $text);
          }
        }
      }

      // get the url that the form will post
      // this should be the plugin's page url.
      //
      // NOT ESSENTIAL IF POSTED..!
      //
      $exact_url = $this->params->get('exact_url', true);
      $disable_https = $this->params->get('disable_https', false);
      $fixed_url = $this->params->get('fixed_url', true);

      if ($fixed_url) {
        $url = $this->params->get('fixed_url_address', "");
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
      $myNameLabel    = $this->params->get('name_label', 'Name:');
      $myEmailLabel   = $this->params->get('email_label', 'Email:');
      $mySubjectLabel = $this->params->get('subject_label', 'Subject:');
      $myMessageLabel = $this->params->get('message_label', 'Message:');
      $myPreText      = $this->params->get('pre_text', '');
      $myButtonText   = $this->params->get('button_text', 'Submit Feedback');

      $autos = $this->params->get('auto_subject', true);
      $auto_subject   = '';
      if ($autos) {
        //$db = JFactory::getDBO();
        //$myArticleId = $row->id;
        //$query = 'SELECT * FROM `#__content` WHERE `id` = "'.mysql_escape_string($myArticleId).'"';
        //$db->setQuery($query);
        //$myResult = $db->loadObject();
        if (is_object($row)) {
          $auto_subject = $row->title;
        }
      }

      $myFormURL = JURI::base() . 'plugins/content/popfeed/popfeed/form.php';

      $myScript = '<script>' . "\n" .
                  'function sendForm' . $row->id . '() {' . "\n" .
                  '  var w = window.open("","Popup_Window","width=400,height=500,menubar=no,resizable=yes");' . "\n" .
                  '  if (w.opener == null) w.opener = self;' . "\n" .
                  '  if (w.opener.theForm == null) w.opener.theForm = document.popfeedSecondForm' . $row->id . ';' . "\n" .
                  '  var a = window.setTimeout("document.popfeedFirstForm' . $row->id . '.submit();",500);' . "\n" .
                  '  w.focus();' . "\n" .
                  '  return false;' . "\n" .
                  '}' . "\n" .
                  '</script>' . "\n";

      $myForm = '<form name="popfeedFirstForm' . $row->id . '" action="'.$myFormURL.'" method="post" target="Popup_Window">' .
                '<input type="hidden" name="name_label" value="' . $myNameLabel . '"/>' .
                '<input type="hidden" name="email_label" value="' . $myEmailLabel . '"/>' .
                '<input type="hidden" name="subject_label" value="' . $mySubjectLabel . '"/>' .
                '<input type="hidden" name="message_label" value="' . $myMessageLabel . '"/>' .
                '<input type="hidden" name="pre_text" value="' . $myPreText . '"/>' .
                '<input type="hidden" name="button_text" value="' . $myButtonText . '"/>' .
                '<input type="hidden" name="auto_subject" value="' . $auto_subject . '"/>' .
                '<input type="hidden" name="formActive" value="true"/>' .
                '<input type="hidden" name="rowID" value="' . $row->id . '"/>' .
                '</form>' . "\n";

      $mySecondForm = '<form name="popfeedSecondForm' . $row->id . '" action="'.$url.'" method="post">' .
                      '<input type="hidden" name="pf_name"/>' .
                      '<input type="hidden" name="pf_email"/>' .
                      '<input type="hidden" name="pf_subject"/>' .
                      '<input type="hidden" name="pf_message"/>' .
                      '<input type="hidden" name="popfeedSecondForm' . $row->id . '" value="true"/>' .
                      '</form>' . "\n";

      $myLinkStart = $myScript . $myForm . $mySecondForm .'<div class="popfeedLink"><a href="#" onClick="sendForm' . $row->id . '();">';

      $text = str_replace('{popfeed}', $myLinkStart, $text);
      $text = str_replace('{/popfeed}', '</a></div>', $text);


      return true;
    }
  }

}