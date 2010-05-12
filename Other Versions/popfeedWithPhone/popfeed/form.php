<?php

if ($_POST["formActive"]) {
  
  $myScript = '<script>' . "\n" .
              'function sendMyForm() {' . "\n" .
              '  opener.theForm.pf_name.value = document.popfeedForm.pf_name.value;' . "\n" .
              '  opener.theForm.pf_email.value = document.popfeedForm.pf_email.value;' . "\n" .
              '  opener.theForm.pf_phone.value = document.popfeedForm.pf_phone.value;' . "\n" .
              '  opener.theForm.pf_subject.value = document.popfeedForm.pf_subject.value;' . "\n" .
              '  opener.theForm.pf_message.value = document.popfeedForm.pf_message.value;' . "\n" .
              '  opener.setTimeout("document.popfeedSecondForm.submit();",500);' . "\n" .
              '  opener.focus();' . "\n" .
              '  self.close();' . "\n" . 
              '  return true;' . "\n" .
              '}' . "\n" .
              '</script>' . "\n";  
              
  print $myScript;
  
  print '<a href="javascript: void window.close()" style="position: absolute; right: 10px;">Close Window</a>';
  
  print $_POST["pre_text"];
  
  print '<form name="popfeedForm" action="" method="post"><table>' . "\n" .
        '<tr><td>' . $_POST["name_label"] . '</td><td><input type="text" name="pf_name" style="width: 200px;" /></td></tr>' . "\n" .
        '<tr><td>' . $_POST["email_label"] . '</td><td><input type="text" name="pf_email" style="width: 200px;" /></td></tr>' . "\n" .
        '<tr><td>' . $_POST["phone_label"] . '</td><td><input type="text" name="pf_phone" style="width: 200px;" /></td></tr>' . "\n" .
        '<tr><td>' . $_POST["subject_label"] . '</td><td><input type="text" name="pf_subject" style="width: 200px;" value="' . $_POST["auto_subject"] . '"/></td></tr>' . "\n" .
        '<tr><td valign="top">' . $_POST["message_label"] . '</td><td><textarea name="pf_message" rows="8" cols="15" style="width: 200px;"></textarea></td></tr>' . "\n" .
        '<tr><td></td><td><input type="button" value="' . $_POST["button_text"] . '" style="width: 200px;" onClick="sendMyForm();"/></td></tr>' . "\n" .
        '</form>';
  
}
else {
  die( 'Restricted access' );
}

?>