<?php
/*
PopFeed Plugin, developed by Christopher Mavros, Mavrosxristoforos.com
@Copyright Copyright (C) Christopher Mavros, since 2008.
@license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

?>
You received a message from <?php print $helper->posted_values['name']; ?> (<?php print $helper->posted_values['email']; ?>)
Regarding URL: <?php print "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\n"; ?>
Message:
<?php print $helper->posted_values['message']; ?>