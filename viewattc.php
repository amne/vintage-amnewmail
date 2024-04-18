<?php
 $blh->open_mailbox($pnum);
 $msgpart=$blh->getmsgpart($pnum,$blh->msgs[$mnum-1]);
 header("Content-type: ".$mime_types[$msgpart->type]."/".strtolower($msgpart->subtype));
 if(isset($msgpart->name)) {
  header('Content-Disposition: inline; filename="'.$msgpart->name.'"');
  }
 print $msgpart->mparts;
 $blh->close_mailbox();
?>