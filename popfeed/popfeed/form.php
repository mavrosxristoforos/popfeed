<?php

if ($_POST["formActive"]) {

  $specialID = $_POST["rowID"];

  $myScript = '<script type="text/javascript">' . "\n" .
              'function sendMyForm() {' . "\n" .
              '  opener.theForm.pf_name.value = document.popfeedForm.pf_name.value;' . "\n" .
              '  opener.theForm.pf_email.value = document.popfeedForm.pf_email.value;' . "\n" .
              '  opener.theForm.pf_subject.value = document.popfeedForm.pf_subject.value;' . "\n" .
              '  opener.theForm.pf_message.value = document.popfeedForm.pf_message.value;' . "\n" .
              '  opener.setTimeout("document.popfeedSecondForm' . $specialID . '.submit();",500);' . "\n" .
              '  opener.focus();' . "\n" .
              '  self.close();' . "\n" .
              '  return true;' . "\n" .
              '}' . "\n" .
              '</script>' . "\n";

  print '<html><head>';
  print $myScript;

  print '<link rel="stylesheet" href="popfeed.css" type="text/css">';

  print '</head><body>';

  print '<a href="javascript: void window.close()" id="popfeedcloselink" class="popfeedlink" style="position: absolute; right: 10px;">Close Window</a>';

  print $_POST["pre_text"];

  print '<form id="popfeedForm" name="popfeedForm" action="" method="post"><table>' . "\n" .
        '<tr><td>' . $_POST["name_label"] . '</td><td><input type="text" id="popfeedname" class="popfeedinputbox inputbox" name="pf_name" style="width: 200px;" /></td></tr>' . "\n" .
        '<tr><td>' . $_POST["email_label"] . '</td><td><input type="text" id="popfeedemail" class="popfeedinputbox inputbox" name="pf_email" style="width: 200px;" /></td></tr>' . "\n" .
        '<tr><td>' . $_POST["subject_label"] . '</td><td><input type="text" id="popfeedsubject" class="popfeedinputbox inputbox" name="pf_subject" style="width: 200px;" value="' . $_POST["auto_subject"] . '"/></td></tr>' . "\n" .
        '<tr><td valign="top">' . $_POST["message_label"] . '</td><td><textarea id="popfeedmessage" class="popfeedtextarea textarea" name="pf_message" rows="8" cols="15" style="width: 200px;"></textarea></td></tr>' . "\n" .
        '<tr><td></td><td><input type="button" id="popfeedsubmit" class="popfeedbutton button" value="' . $_POST["button_text"] . '" style="width: 200px;" onClick="sendMyForm();"/></td></tr>' . "\n" .
        '</form>';

  print '</body>';

}
else {
  die( 'Restricted access' );
}

?>