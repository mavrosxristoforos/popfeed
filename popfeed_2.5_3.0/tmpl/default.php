<?php
/*
PopFeed Plugin, developed by Christopher Mavros, Mavrosxristoforos.com
@Copyright Copyright (C) Christopher Mavros, since 2008.
@license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
//$document->addStyleSheet('');

// Include Lightbox of some form.

?>
<div class="popfeed_form" id="popfeed_form_<?php print $helper->article->id; ?>">
<?php if ($helper->params->get('pre_text', '') != '') { ?>
<div class="popfeed_form_pre_text"><?php print $helper->params->get('pre_text', ''); ?></div>
<?php } ?>
<form id="popfeed_form_<?php print $helper->article->id; ?>_innerform" name="popfeed_form_innerform" method="post">
  <div class="popfeed_form_field_group field_group">
    <div class="popfeed_field">
      <input type="text" id="popfeed_form_<?php print $helper->article->id; ?>_name" class="popfeedinputbox inputbox form-control"
             name="popfeed_form_<?php print $helper->article->id; ?>_name"
             placeholder="<?php print $helper->i18n('PLG_POPFEED_NAME', 'Name'); ?>"/>
    </div>
    <div class="popfeed_field">
      <input type="text" id="popfeed_form_<?php print $helper->article->id; ?>_email" class="popfeedinputbox inputbox form-control"
             name="popfeed_form_<?php print $helper->article->id; ?>_email"
             placeholder="<?php print $helper->i18n('PLG_POPFEED_EMAIL', 'email@site.com'); ?>"/>
    </div>
    <div class="popfeed_field">
      <input type="text" id="popfeed_form_<?php print $helper->article->id; ?>_subject" class="popfeedinputbox inputbox form-control"
             name="popfeed_form_<?php print $helper->article->id; ?>_subject"
             placeholder="<?php print $helper->i18n('PLG_POPFEED_SUBJECT', 'Message Subject'); ?>"
             <?php if ($helper->params->get('auto_subject', true)) {
               print 'value="'.$helper->article->title.'"'
             } ?>/>
    </div>
    <div class="popfeed_field">
      <textarea id="popfeed_form_<?php print $helper->article->id; ?>_message" class="popfeedtextarea textarea form-control"
                name="popfeed_form_<?php print $helper->article->id; ?>_message"
                placeholder="<?php print $helper->i18n('PLG_POPFEED_MESSAGE', 'Your Feedback Message'); ?>"></textarea>
    </div>
    <div class="popfeed_field popfeed_button">
      <input type="submit"
      <textarea id="popfeed_form_<?php print $helper->article->id; ?>_submit" class="popfeedbutton button btn btn-primary"
             name="popfeed_form_<?php print $helper->article->id; ?>_submit"
             value="<?php print $helper->i18n('PLG_POPFEED_SUBMIT', 'Submit Feedback'); ?>"/>
    </div>
  </div>
</form>
</div>