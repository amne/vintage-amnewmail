<?php
 $blh->open_mailbox();
//$blh->check_mailbox();
print "<hr>";
print "<hr>";
$ii=0;
print "<BR>You have ".$blh->checkmbox->Nmsgs." messages in your mailbox and ".$blh->checkmbox->Recent." new messages. <BR>";
print '<table border="0" width="100%">';
print '<tr><th align="center" valign="top" width="20%">Mailboxes</th><th valign="top" align="center">Mails</th></tr><tr>';
print '<td valign="top">';
//print "Mail listing for ".$blh->mboxstring."<BR>";
$mboxes=imap_getmailboxes($blh->mailbox,"{".$blh->server."}INBOX","%");
foreach($mboxes as $mmmbx)
 {
 $mboxname=$mmmbx->name;
 $mboxsubname=explode("{".$blh->server."}",$mboxname);
 if($mboxsubname[1]=="INBOX")
  {
   $mboxnamelink="";
   $mboxname="Inbox";
  } else
  {
   $mboxname=explode(".",$mboxsubname[1]);
   $mboxnamelink=".".$mboxname[1];
   $mboxname=$mboxname[1];
  }
 print '<a href="'.$PHP_SELF.'?viewattach=listmsgs&mailbbox='.$mboxnamelink.'">'.$mboxname.'</a><br>';
 }
print '</td><td valign="top">';
print '<table border="1">';
print '<tr>';
print '<th></th><th>From</th><th>Subject:</th><th>Attachements</th>';
print '</tr>';
print '<form action="index.php?viewattach=listmsgs" method="POST">';
for($ii=0;$ii<$blh->nummsgs;$ii++)
 {
 if($blh->mboverview[$ii]->seen)
  {
          print "<b>";
  }
 print '<tr><td><input type="checkbox" name="cbmsg_['.$ii.']" value="c'.$ii.'"></td><td>';
 reset($blh->msgs[$ii]->msg_texts);
foreach($blh->msgs[$ii]->msg_texts as $thismsg)
 {
 print "\n".'<a href="'.$PHP_SELF.'?viewattach=view&mnum='.($ii+1).'&pnum='.$thismsg->partnum.'" target="_blank">';
 print htmlspecialchars($blh->mboverview[$ii]->from);
 print "( type: ".$thismsg->mime .")";
 print "<BR>";
 }
 print '</td>';
 print '<td>'.$blh->mboverview[$ii]->subject.'</td>';
 print '<td>';
 foreach($blh->msgs[$ii]->msg_attachs as $thisattach)
  {
  print '<a href="'.$PHP_SELF.'?viewattach=view&mnum='.($ii+1).'&pnum='.$thisattach->partnum.'" target="_blank">'.$thisattach->name.'</a><BR>';
  }
 print '</td></tr>';
 if($blh->mboverview[$ii]->seen)
  {
          print "</b>";
  }
 }
print '<tr><td><input type="submit" name="Delete" value="Delete"></td>';
print '<td><input type="submit" name="Move" value="Move"></td>';
print '<td><input type="submit" name="Mark" value="Mark"></td>';

print '</form>';
print '</table>';
print '</th></tr></table>';
reset($blh->msgs);
//imap_createmailbox($blh->mailbox,"{".$blh->server.$blh->srvxtra."}INBOX.Trash");
$blh->close_mailbox();
print "\n".'<BR><BR><a href="index.php?logout=1">Logout</a><BR>';
?>