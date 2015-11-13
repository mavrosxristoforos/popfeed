<?php
/*
PopFeed Plugin, developed by Christopher Mavros, Mavrosxristoforos.com
@Copyright Copyright (C) Christopher Mavros, since 2008.
@license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentPopFeed extends JPlugin {

  function plgContentPopFeed( &$subject, $config ) {
    parent::__construct( $subject, $config );
  }

  public function onContentPrepare($context, &$row, &$params, $page = 0) {
    require_once(JPATH_SITE.'/plugins/content/popfeed/helper.php');
    $helper = new PlgPopFeedHelper();
    $helper->initialize($this->params, $row);

    if (!$helper->initializeArticleText()) {
      $helper->replacePopFeedTag(''); // This is not an error message. It just removes the popfeed tag.
      return true;
      // We have an article here on. (hasArticle == true)
    }

    if (!$helper->initializeRecipient()) {
      $helper->replacePopFeedTag('PopFeed: '.$helper->i18n('INVALID_RECIPIENT', 'The email recipient specified is either empty or invalid. Please check the plugin options.'));
      return true;
    }

    // Handle Posts
    if ($helper->prepareEmail()) {
      //
      ob_start();
      include JPluginHelper::getLayoutPath('content', 'popfeed', 'email_body');
      $form = ob_get_clean();
    }

    // Show Form.
    ob_start();
    // Determine if Rapid Contact Ex is installed.
    include JPluginHelper::getLayoutPath('content', 'popfeed');
    $form = ob_get_clean();
    //$helper->replacePopFeedTag();

    // show form.

    /*if (isset($row->id)) {

      if (isset($_POST["popfeedSecondForm" . $row->id])) {

        $fromName = $this->params->get('from_name', 'PopFeed');
        $fromEmail = $this->params->get('from_email', 'popfeed@yoursite.com');

        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $_POST["pf_email"])) {
          $myErrorMessage = '<div class="popfeed_error">'.$helper->i18n('INVALID_EMAIL', 'Submitted email is invalid. Please try again.').'</div><br/>{popfeed}';
          $text = str_replace('{popfeed}', $myErrorMessage, $text);
        }
        else {
          $mMessage = 'You received a message from ';
          if ($_POST["pf_name"]) {
            $mMessage .= $_POST["pf_name"] . ' ';
          }
          $mMessage .= '(' . $_POST["pf_email"] . ")\n\n";
          $mMessage .= "Article: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\n\n";
          $mMessage .= $_POST["pf_message"];

          $mailSender = JFactory::getMailer();
          $mailSender->addRecipient($helper->recipient);
          if ($this->params->get('from_email', 'popfeed@yoursite.com') != 'popfeed@yoursite.com') {
            $mailSender->setSender(array($fromEmail,$fromName));
          }
          else {
            $mailSender->setSender(array($_POST["pf_email"],$_POST["pf_name"]));
          }
          $mailSender->setSubject($_POST["pf_subject"]);
          $mailSender->setBody($mMessage);

          if ($mailSender->Send() !== true) {
            $myErrorMessage = '<div class="popfeed_error">'.
                                $helper->i18n('YOUR_FEEDBACK_COULD_NOT_BE_SUBMITTED', 'Your feedback could not be submitted. Please try again.').
                              '</div><br/>{popfeed}';
            $text = str_replace('{popfeed}', $myErrorMessage, $text);
          }
          else {
            $myOKMessage = '<div class="popfeed_message">'.
                             $helper->i18n('THANK_YOU_FOR_YOUR_FEEDBACK', 'Thank you for your feedback.').
                           '</div><br/>{popfeed}';
            $text = str_replace('{popfeed}', $myOKMessage, $text);
          }
        }
      }

      $url = '';
      if ($this->params->get('fixed_url', true)) {
        $url = $this->params->get('fixed_url_address', "");
      }

      // get the rest of the parameters and build the form
      //
      $myNameLabel    = $this->params->get('name_label', 'Name:');
      $myEmailLabel   = $this->params->get('email_label', 'Email:');
      $mySubjectLabel = $this->params->get('subject_label', 'Subject:');
      $myMessageLabel = $this->params->get('message_label', 'Message:');
      $myPreText      = $this->params->get('pre_text', '');
      $myButtonText   = $this->params->get('button_text', 'Submit Feedback');

      $auto_subject   = '';
      if ($this->params->get('auto_subject', true)) {
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

      // Remember to switch the $row->text to text.


      return true;
    }*/
  }

}