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

?>
You received a message from <?php print $helper->posted_values['name']; ?> (<?php print $helper->posted_values['email']; ?>)
Regarding URL: <?php print "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\n"; ?>
Message:
<?php print $helper->posted_values['message']; ?>