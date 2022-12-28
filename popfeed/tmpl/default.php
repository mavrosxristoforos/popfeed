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

$form_id = 'popfeed_form_'.$helper->article->id;
$a_href = ($helper->popfeed_appearance == '0') ? '#'.$form_id : 'javascript:void(0);';

if ($helper->hasCaptcha()) {
  // Initialize Captcha
  $captcha_field = (JFactory::getConfig()->get('captcha') != '0') ? JCaptcha::getInstance(JFactory::getConfig()->get('captcha'))->display('popfeed_recaptcha_'.$form_id, 'popfeed_recaptcha_'.$form_id, 'g-recaptcha') : '';
}

?>
<?php if ($helper->popfeed_appearance != '4') { ?>
<div class="popfeed_link_div <?php print $this->params->get('link_position'); ?> <?php print $this->params->get('link_vertical'); ?> <?php print $this->params->get('link_horizontal'); ?>">
  <a href="<?php print $a_href; ?>" onClick="showPopFeed('<?php print $form_id; ?>')"
     class="popfeedLink popfeed_link <?php print $this->params->get('link_class', ''); ?>" id="<?php print $form_id; ?>_link"><?php print $helper->popfeed_text; ?></a>
</div>
<?php } ?>
<?php if ($helper->popfeed_appearance == '1') { ?><div class="popfeed_form_box" id="<?php print $form_id; ?>_box"> </div><?php } ?>
<div class="popfeed_form_wrapper" id="<?php print $form_id; ?>_wrapper">
<?php if ($helper->popfeed_appearance == '1') { ?>
  <a href="javascript:void(0);" onClick="hidePopFeed('<?php print $form_id; ?>')" class="close_link"><?php
    print $helper->i18n('CLOSE_FORM', 'Close Form');
  ?></a>
<?php } ?>
<div class="popfeed_form <?php print $this->params->get('form_class', ''); ?>" id="<?php print $form_id; ?>">
<?php if ($helper->i18nParam('pre_text', "<h3>What do you think?</h3>\n<p>Send us feedback!</p>") != '') { ?>
<div class="popfeed_form_pre_text"><?php print $helper->i18nParam('pre_text', "<h3>What do you think?</h3>\n<p>Send us feedback!</p>"); ?></div>
<?php } ?>
<form id="<?php print $form_id; ?>_innerform" name="popfeed_form_innerform" method="post" <?php
  if ( ($this->params->get('fixed_url', true)) && ($this->params->get('fixed_url_address', '') != '') ) {
    print 'action="'.$this->params->get('fixed_url_address', "").'"';
  }
?>>
  <div class="popfeed_form_field_group field_group">
    <div class="popfeed_field">
      <input type="text" id="<?php print $form_id; ?>_name" class="popfeedinputbox inputbox form-control" name="<?php print $form_id; ?>_name" placeholder="<?php print $helper->i18n('PLG_POPFEED_NAME', 'Name'); ?>"/>
    </div>
    <div class="popfeed_field">
      <input type="email" id="<?php print $form_id; ?>_email" class="popfeedinputbox inputbox form-control" name="<?php print $form_id; ?>_email" placeholder="<?php print $helper->i18n('PLG_POPFEED_EMAIL', 'email&#64;site.com'); ?>"/>
    </div>
    <div class="popfeed_field">
      <input type="text" id="<?php print $form_id; ?>_subject" class="popfeedinputbox inputbox form-control" name="<?php print $form_id; ?>_subject" placeholder="<?php print $helper->i18n('PLG_POPFEED_SUBJECT', 'Message Subject'); ?>"
             <?php if ($helper->params->get('auto_subject', true) && isset($helper->article->title)) {
               print 'value="'.sprintf($helper->i18nParam('auto_subject_pattern', 'Regarding: %s'), $helper->article->title).'"';
             } ?>/>
    </div>
    <div class="popfeed_field">
      <textarea id="<?php print $form_id; ?>_message" class="popfeedtextarea textarea form-control" name="<?php print $form_id; ?>_message" placeholder="<?php print $helper->i18n('PLG_POPFEED_MESSAGE', 'Your Feedback Message'); ?>"></textarea>
    </div>
<?php if ($helper->hasCaptcha()) { ?>
    <div class="popfeed_field captcha_field">
      <?php print $captcha_field; ?>
    </div>
<?php } ?>
    <div class="popfeed_field popfeed_button">
      <input type="submit" id="<?php print $form_id; ?>_submit" class="popfeedbutton button btn btn-primary" name="<?php print $form_id; ?>_submit" value="<?php print $helper->i18n('PLG_POPFEED_SUBMIT', 'Submit Feedback'); ?>"/>
    </div>
  </div>
  <input type="hidden" name="<?php print $form_id; ?>_post" value="<?php print uniqid(); ?>"/>
</form>
</div>
</div>