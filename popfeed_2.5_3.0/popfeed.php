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

jimport( 'joomla.plugin.plugin' );

class plgContentPopFeed extends JPlugin {

  public function onContentBeforeDisplay($context, &$row, &$params, $page = 0) {
    require_once(JPATH_SITE.'/plugins/content/popfeed/helper.php');
    $helper = new PlgPopFeedHelper();
    $helper->initialize($this, $this->params, $row);
    if (!$helper->shouldBeHere()) { return; }
    $helper->loadAssets();
    $form_id = 'popfeed_form_'.$helper->article->id;
    include JPluginHelper::getLayoutPath('content', 'popfeed', 'scripts_and_styles');
  }

  public function onContentPrepare($context, &$row, &$params, $page = 0) {
    require_once(JPATH_SITE.'/plugins/content/popfeed/helper.php');
    $helper = new PlgPopFeedHelper();
    $helper->initialize($this, $this->params, $row);

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
      // Comes here only if email should be sent.
      // Prepare email body.
      ob_start();
      include JPluginHelper::getLayoutPath('content', 'popfeed', 'email_body');
      $email_body = ob_get_clean();

      $helper->mailer->setBody($email_body);
      if ($helper->mailer->Send() !== true) {
        $helper->addMessage('YOUR_FEEDBACK_COULD_NOT_BE_SUBMITTED', 'Your feedback could not be submitted. Please try again.', 'error');
      }
      else {
        $helper->addMessage('THANK_YOU_FOR_YOUR_FEEDBACK', 'Thank you for your feedback.', 'message');
      }
    }

    // To find the text that the user has written.
    $helper->determinePopFeedText();

    // Show Form.
    ob_start();
    // Determine if Rapid Contact Ex is installed.
    include JPluginHelper::getLayoutPath('content', 'popfeed');
    $form = ob_get_clean();
    $helper->replacePopFeedTag($form, true); // True means to include any messages from the post process.
  }

}