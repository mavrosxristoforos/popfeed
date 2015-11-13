<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class PlgPopFeedHelper {

  public $params;
  public $article;
  public $hasArticle;
  public $recipient;

  public function initialize($params, $row) {
    $this->params = $params;
    $this->hasArticle = (is_object($row));
    $this->article = ($this->hasArticle) ? $row : '';
  }

  public function initializeArticleText() {
    if ($this->hasArticle) {
      // Do not include PopFeed in modules.
      if (isset($this->article->text)) {
        if ( ($this->params->get('auto_all', false)) || (strpos($this->article->text, '{popfeed}') !== false) ) {
          // Include PopFeed only in desired locations.
          if ( ($this->isNotExcluded()) && ($this->isValidComponentView()) ) {
            if (strpos($this->article->text, '{popfeed}') === false) {
              // Means we came here from auto_include
              $this->article->text .= '{popfeed}' . $this->i18n('LEAVE_YOUR_FEEDBACK', 'Leave your feedback!') . '{/popfeed}';
            }
            return true;
          }
        }
      }
    }
    return false;
  }

  public function initializeRecipient() {
    $this->recipient = $this->params->get('email_recipient', 'email@email.com');
    if ($this->params->get('auto_recipient', false)) {
      // Auto Recipient from article author.
      if ($this->hasArticle) {
        $user_tmp = JFactory::getuser($this->article->created_by);
        $this->recipient = $user_tmp->email;
      }
    }
    if (($this->hasArticle) && (isset($this->article->text))) {
      if (strpos($this->article->text, '{popfeed_mailrecipient}') !== false) {
        $this->recipient = $this->str_between('{popfeed_mailrecipient}', '{/popfeed_mailrecipient}', $this->article->text);
        $this->article->text = str_replace('{popfeed_mailrecipient}'.$this->recipient.'{/popfeed_mailrecipient}', '', $this->article->text);
      }
    }

    return ($this->recipient != '') && (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $this->recipient)) && ($this->recipient != 'email@email.com');
  }

  public function isNotExcluded() {
    if ( (isset($this->article->id)) && ($this->article->id > 0) ) {
      if ( in_array($this->article->id, explode(',', $this->params->get('excluded_ids', ''))) ) {
        return false;
      }
      if ($this->params->get('catids', '') != '') {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT COUNT(*) FROM `#__content` '.
                      ' WHERE `id` = "'.$aid.'" '.
                      ' AND `catid` IN ('.$this->params->get('catids', '').')');
        return ($db->loadResult() > 0);
      }
    }
    return true;
  }

  public function isValidComponentView() {
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

		return ( (in_array(JRequest::getVar('option'), $component_array))
		      && (in_array(JRequest::getVar('view'), $view_array)) );
  }

  public function prepareEmail() {
    if ($this->hasArticle) {
      if (isset($_POST['popfeed_form_'.$this->article->id])) {
        // posted data =
        return true;
      }
    }
    return false;
  }

  public function replacePopFeedTag($replacement) {
    if ($this->hasArticle) {
      $this->article->text = preg_replace('/{popfeed}.*{\/popfeed}/i', $replacement, $this->article->text);
      return true;
    }
    return false;
  }

  public function i18n($key, $default) {
    return (JText::_($key) == $key) ? $default : JText::_($key);
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