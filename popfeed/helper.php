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
\defined( '_JEXEC' ) or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
//use \Joomla\CMS\Plugin\PluginHelper;

class PlgPopFeedHelper {

  public $params;
  public $article;
  public $hasRow;
  public $recipient;
  public $messages;
  public $include_external_libraries;
  public $popfeed_appearance;
  public $mailer;
  public $posted_values;
  private $context;
  public $popfeed_text;

  public function initialize($plg_popfeed, $params, $row, $context) {
    $this->params = $params;
    $this->messages = '';
    $this->context = $context;
    $this->hasRow = (is_object($row));
    if ($this->hasRow && ($this->context != 'mod_custom.content')) {
      $this->article = $row;
    } else if ($this->hasRow && ($this->context == 'mod_custom.content')) {
      $this->article = $row;
      $this->article->id = md5($this->article->text);
    } else {
      $this->article = '';
    }
    $this->include_external_libraries = $this->params->get('include_external_libraries', '0');
    $this->popfeed_appearance = $this->params->get('popfeed_appearance', '0');
    $this->popfeed_text = $this->i18n('LEAVE_YOUR_FEEDBACK', 'Leave your feedback!'); // Default

    $plg_popfeed->loadLanguage('plg_content_popfeed');
  }

  public function shouldBeHere() {
    return (
      $this->hasRow
      && (isset($this->article->text))
      && (isset($this->article->id))
      && (
        ($this->params->get('auto_all', false))
        || (strpos($this->article->text, '{popfeed}') !== false)
        || (strpos($this->article->text, 'id="popfeed_form_'.$this->article->id.'"'))
      )
      && (
        ($this->isNotExcluded())
        && ($this->isValidComponentView())
      )
    )
    || (
      $this->hasRow
      && (isset($this->article->text))
      && ($this->context == 'mod_custom.content')
      && ($this->params->get('allow_in_custom_modules', '0'))
      && (strpos($this->article->text, '{popfeed}') !== false) // By keeping this as a must in module context, we ensure that we never add PopFeed automatically.
    );
  }

  public function initializeArticleText() {
    if ($this->shouldBeHere()) {
      if (strpos($this->article->text, '{popfeed}') === false) {
         // Means we came here from auto_include
         $this->article->text .= '{popfeed}' . $this->i18n('LEAVE_YOUR_FEEDBACK', 'Leave your feedback!') . '{/popfeed}';
      }
      return true;
    }
    return false;
  }

  public function initializeRecipient() {
    $this->recipient = $this->params->get('email_recipient', 'email@email.com');
    if ($this->params->get('auto_recipient', false)) {
      // Auto Recipient from article author.
      if ($this->hasRow && ($this->context != 'mod_custom.content')) {
        $user_tmp = Factory::getUser($this->article->created_by);
        $this->recipient = $user_tmp->email;
      }
    }
    if (($this->hasRow) && (isset($this->article->text))) {
      if (strpos($this->article->text, '{popfeed_mailrecipient}') !== false) {
        $this->recipient = $this->str_between('{popfeed_mailrecipient}', '{/popfeed_mailrecipient}', $this->article->text);
        $this->article->text = str_replace('{popfeed_mailrecipient}'.$this->recipient.'{/popfeed_mailrecipient}', '', $this->article->text);
      }
    }

    return ($this->recipient != '') && (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $this->recipient)) && ($this->recipient != 'email@email.com');
  }

  public function isNotExcluded() {
    // Only evoked when checking if this should be in an article. This function is not called in the module context.
    if ( (isset($this->article->id)) && ($this->article->id > 0) ) {
      if ( in_array($this->article->id, explode(',', $this->params->get('excluded_ids', ''))) ) {
        return false;
      }
      $catids = $this->params->get('catids', array());
      if (!is_array($catids)) {
        // Backwards compatibility fix, for saved options that have string instead of array.
        $catids = explode(',', $catids);
      }
      if (count($catids) > 0) {
        $db = Factory::getDBO();
        $db->setQuery('SELECT COUNT(*) FROM `#__content` '.
                      ' WHERE `id` = "'.$this->article->id.'" '.
                      ' AND `catid` IN ('.implode(',', $catids).')');
        return ($db->loadResult() > 0);
      }
    }
    return true;
  }

  public function isValidComponentView() {
    // Only evoked when checking if this should be in an article. This function is not called in the module context.
    $component_array = array('com_content');
    $view_array      = array('article');

    if ($this->params->get('show_in_frontpage', false)) {
      $view_array[] = 'frontpage';
      $view_array[] = 'featured';
    }
    if ($this->params->get('show_in_blog', false)) {
      $view_array[] = 'blog';
      $view_array[] = 'category';
    }

    $input = Factory::getApplication()->input;

    return ( (in_array($input->get('option', '', 'cmd'), $component_array)) && (in_array($input->get('view', '', 'cmd'), $view_array)) );
  }

  public function addMessage($key, $def, $msg_type) {
    $this->messages .= '<div class="popfeed_'.$msg_type.' '.$msg_type.'">'.$this->i18n($key, $def).'</div>';
  }

  public function hasCaptcha() {
    return (Factory::getConfig()->get('captcha') != '0');
    /*if ($this->params->get('use_captcha', '1')) {
      $db = Factory::getDBO();
      $db->setQuery('SELECT COUNT(`extension_id`) FROM `#__extensions` WHERE `type`="plugin" AND `folder`="captcha" AND `enabled`=1');
      return $db->loadResult();
    }
    return false;*/
  }

  public function filterItem($value) {
    return ($this->params->get('htmlentities_in_email', '0')) ? htmlentities($value, ENT_COMPAT, "UTF-8") : $value;
  }

  public function prepareEmail() {
    if ($this->hasRow) {
      $form_id = 'popfeed_form_'.$this->article->id;
      if (isset($_POST[$form_id.'_post'])) {
        $isValidPost = true;
        if ($this->hasCaptcha()) {
          $captcha = JCaptcha::getInstance(Factory::getConfig()->get('captcha'));
          /*PluginHelper::importPlugin('captcha');
          $d = JEventDispatcher::getInstance();
          $res = $d->trigger('onCheckAnswer', 'not_used');
          if( (!isset($res[0])) || (!$res[0]) ) {*/
          try {
            if (!$captcha->checkAnswer(Factory::getApplication()->input->get('popfeed_recaptcha_'.$form_id, null, 'string'))) {
              $this->addMessage('INVALID_CAPTCHA', 'Invalid Captcha', 'error');
              $isValidPost = false;
            }
          }
          catch(RuntimeException $e) {
            $this->addMessage('INVALID_CAPTCHA', 'Invalid Captcha', 'error');
            $isValidPost = false;
          }
        }

        if ($isValidPost) {
          // Determine if Rapid Contact Ex is installed.
          $this->posted_values = array();
          $this->posted_values['name'] = $this->filterItem($_POST[$form_id.'_name']);
          $this->posted_values['email'] = $this->filterItem($_POST[$form_id.'_email']);
          $this->posted_values['subject'] = $this->filterItem($_POST[$form_id.'_subject']);
          $this->posted_values['message'] = $this->filterItem($_POST[$form_id.'_message']);

          if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $this->posted_values['email'])) {
            $this->addMessage('INVALID_EMAIL', 'Submitted email is invalid. Please try again.', 'error');
          }
          else {
            $this->mailer = Factory::getMailer();
            $this->mailer->addRecipient($this->recipient); // One recipient is allowed when initializing.
            $app = Factory::getApplication();
            $this->mailer->setSender(array($app->getCfg('mailfrom'),$this->posted_values['name']));
            if(version_compare(JVERSION, '3.5', 'ge')) {
              $this->mailer->addReplyTo($this->posted_values['email'], $this->posted_values['name']);
            }
            else {
              $this->mailer->addReplyTo(array( $this->posted_values['email'], $this->posted_values['name'] ));
            }
            $this->mailer->setSubject($this->posted_values['subject']);
            return true; // means send.
          }
        }
      }
    }
    return false;
  }

  public function loadAssets() {
    $document = Factory::getDocument();
    if ($this->params->get('include_css', true)) {
      $document->addStyleSheet(Uri::base().'plugins/content/popfeed/assets/popfeed.css');
    }
    if (in_array($this->include_external_libraries, array(0,1))) {
      // Load jQuery
      HTMLHelper::_('jquery.framework');
    }
    if (in_array($this->include_external_libraries, array(0,2))) {
      // Load ColorBox
      $document->addStyleSheet(Uri::base().'plugins/content/popfeed/assets/colorbox.css');
      $document->addScript(Uri::base().'plugins/content/popfeed/assets/jquery.colorbox-min.js');
    }
  }

  public function replacePopFeedTag($replacement, $include_messages = false) {
    if ($this->hasRow) {
      $replacement = ($include_messages && ($this->messages != ''))
                   ? '<div class="popfeed_messages">'.$this->messages.'</div>'.$replacement : $replacement;
      $this->article->text = preg_replace('/{popfeed}.*{\/popfeed}/i', $replacement, $this->article->text);
      return true;
    }
    return false;
  }

  public function determinePopFeedText() {
    if ($this->hasRow) {
      $matches = array();
      preg_match('/{popfeed}(.*){\/popfeed}/i', $this->article->text, $matches);
      if (count($matches) > 1) {
        $this->popfeed_text = $matches[1];
      }
    }
  }

  public function i18nParam($param, $default) {
    $value = $this->params->get($param, $default);
    if ($value != $default) {
      $translated_value = Text::_($value, $default);
      if ($translated_value != $default) {
        $value = $translated_value;
      }
    }
    return $value;
  }

  public function i18n($key, $default) {
    return (Text::_($key) == $key) ? $default : Text::_($key);
  }

  function str_between($p1, $p2, $text) {
    $spos = strpos($text, $p1);
    if ($spos !== false) {
      $start_pos = $spos+strlen($p1);
      $end_pos = strpos($text, $p2);
      return substr($text, $start_pos, $end_pos-$start_pos);
    }
    return '';
  }

}