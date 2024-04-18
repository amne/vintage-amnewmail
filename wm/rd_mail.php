<?php
/**************************************************************
i can't believe i wrote these recursives.
i never knew what they were all about. started
learning php few weeks ago (`bout 2 weeks i think)
it's not finished of course. i don't even know what IMAP is yet!
i wrote these cuz i needed 'em to write my own webmail interface
(those webmails i found on the net ... shitty ... don't work
in windows pretty well ... i don't like`em

i tested this one on my own PC.
used Mercury SMTP,POP3,IMAP to serve mail services
used Open WinDNS to serve DNS services
used Outlook to send shitty mails with attachements and mails inside mails (recursiveness?)
used PHPEditor to edit php scripts
used Apache1.3.33/PHP4.3.0-dev to serve HTTP
used WinAMP 5.093 to listen my fav music while brainstorming this class.

simple usage:
include(rd_mail.php);
$test=new ai_map();
$test->init("server","user","pass",$extra);
$test->open_mailbox($whattofetch);
$test->close_mailbox();

$extra syntax:   ":port[/servertype[/ssl]]
$whattofetch syntax:  - if $whattofetch is null then the parser
                      will not fetch anything from the server!
                      - $whattofetch=="all" then everything will be
                      fetched (not recommended for large mailboxes)
                      - $whattofetch=="part[.subpart[.subsubpart[....]]..]]"
                      that subpart will be fetched from the server.
                      to get it you will have to call
                       $part=$test->getmsgpart("part[.subpart[.subsubpart[....]]..]]");
                       $part is of type "tmsg_m";
                       $part->type - the type of the content (see $mime_types array)
                       $part->subtype - the subtype (PLAIN,HTML,IMAGE,etc.)
                       $part->partnum - you know what this is ("part[.subpart........")
                       $part->nparts = "1" if it is an attachement or a mail body if
                                           it's not 1 then u tricked me and will have
                                           to figure out yourself the substructure of
                                           this "subpart"
                       $part->mparts - is filled with the part body (or a substructure, look above)

enjoy this one cuz i am.
***************************************************************/


$mime_types=array(
 0 => 'text',
 1 => 'multipart',
 2 => 'message',
 3 => 'application',
 4 => 'audio',
 5 => 'image',
 6 => 'video',
 7 => 'other');

/*
todo: build an array with elems of this type so you can
      find attachements the easy way  :D*/
$mboxes="";
class tmsg_attach
{
var $mime;
var $name;
var $partnum;
}
class tmsg_text
{
var $mime;
var $charset;
var $partnum;
}
/*4*/

class tmsg_m
{
var $sublevel;
var $partnum;
var $type;
var $subtype;
var $header;
var $struc;
var $nparts;
var $mparts;
var $msg_texts;
var $msg_attachs;
}


class ai_map
{
var $srvxtra;
var $server;
var $port;
var $mailbox;
var $uname;
var $upass;
var $mlbox;
var $nummsgs;
var $checkmbox;
var $mboverview;
var $msgs;
var $mboxstring;


 function init($server,$uname,$upass,$srv_extra="",$mbbox="")
 {
  $this->server=$server;
  $this->uname=$uname;
  $this->upass=$upass;
  $this->srvxtra=$srv_extra;
  $this->mlbox=$mbbox;
 }

 function open_mailbox($ftchbody="")
  {
  $this->mboxstring="{".$this->server.$this->srvxtra."}INBOX".$this->mlbox;
  $this->mailbox = imap_open($this->mboxstring, $this->uname, $this->upass);
  $this->check_mailbox($ftchbody);
  }
 function parse_mail_structure($mnum,$deep=0,$ftchbody,&$smsg,$deepirch)
  {
  global $mime_types;
  if(!$smsg) {return -1;}
   $smsg->nparts=1;
   //print "part $deepirch ".$smsg->struc->type."<BR>";
   //print "part $deepirch ".$smsg->struc->subtype."<BR>";
   if(isset($smsg->struc->parts))
    {
    //print "part nparts $deepirch: ".count($smsg->struc->parts)."<BR>";
    $smsg->nparts=count($smsg->struc->parts);
    $smsg->type=$smsg->struc->type;
    $smsg->subtype=$smsg->struc->subtype;
    $smsg->sublevel=$deep;
    $smsg->partnum=$deepirch;
    for($kpm=0;$kpm<$smsg->nparts;$kpm++)
     {
      $smsg->mparts[$kpm]=new tmsg_m();
      $smsg->mparts[$kpm]->struc=$smsg->struc->parts[$kpm];
      $nextierarch=$kpm+1;
      if($deepirch){$nextierarch=$deepirch.".".$nextierarch;}
      $this->parse_mail_structure($mnum,$deep++,$ftchbody,$smsg->mparts[$kpm],$nextierarch);
     }
    } else
    {
    if(!$deepirch) // this is just a message? that's strange huh!!!
     {
      $deepirch=1;
     }
    $smsg->type=$smsg->struc->type;
    $smsg->subtype=$smsg->struc->subtype;
    $smsg->sublevel=$deep;
    $smsg->partnum=$deepirch;
    $smsg->nparts=1;
    switch($smsg->type)
     {
     case 0:
     if(!$smsg->struc->ifdparameters)
      {
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]=new tmsg_text();
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]->mime=$mime_types[$smsg->type]."/".strtolower($smsg->subtype);
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]->partnum=$smsg->partnum;
      }
      break;
     case 2:
     if(!$smsg->struc->ifdparameters)
      {
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]=new tmsg_text();
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]->mime="text/plain";
      $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]->partnum=$smsg->partnum;
      }
      break;
     case 3:
     case 4:
     case 5:
     case 6:
      $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]=new tmsg_attach();
      $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->mime=$mime_types[$smsg->type]."/".strtolower($smsg->subtype);
      $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->partnum=$smsg->partnum;
      break;
     default: print "there's a new MIME type on the market! let me know at khrysnukem@yahoo.com<BR>";
     }
    if($smsg->struc->ifdparameters)
     {
     for($pii=0;$pii<count($smsg->struc>parameters);$pii++)
      {
      if($smsg->struc->parameters[$pii]=="FILENAME")
       {
       $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->name=$smsg->struc->parameters[$pii]->value;
       $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->partnum=$smsg->partnum;
       $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->mime=$mime_types[$smsg->type]."/".strtolower($smsg->subtype);
       }
      }
     }
    if($smsg->struc->ifparameters)
     {
     for($pii=0;$pii<count($smsg->struc->parameters);$pii++)
      {
       if($smsg->struc->parameters[$pii]->attribute=="CHARSET")
        {
        $this->msgs[$mnum-1]->msg_texts[$smsg->partnum]->charset=$smsg->struc->parameters[$pii]->value;
        }
       if($smsg->struc->parameters[$pii]->attribute=="NAME")
        {
        $this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->name=$smsg->struc->parameters[$pii]->value;
	$this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->partnum=$smsg->partnum;
	$this->msgs[$mnum-1]->msg_attachs[$smsg->partnum]->mime=$mime_types[$smsg->type]."/".strtolower($smsg->subtype);
        }
      }
     }
    if(($ftchbody==$deepirch) or ($ftchbody=="all")){
    $tmp_body=imap_fetchbody($this->mailbox,$mnum,$deepirch);
    switch($smsg->struc->encoding)
     {
     case 0: break;
     case 1: break;
     case 2: break;
     case 3: $tmp_body=imap_base64($tmp_body); break;
     case 4: $tmp_body=imap_qprint($tmp_body); break;
     case 5: break;
     default: break;
     }
    $smsg->mparts=$tmp_body;
    }
    }
  }
 function check_mailbox($ftch_body="")
  {
  $this->checkmbox=imap_check($this->mailbox);
  $this->nummsgs=imap_num_msg($this->mailbox);
  $this->mboverview=imap_fetch_overview($this->mailbox,"1:".$this->nummsgs,0);
  if(is_array($this->mboverview)) {reset($this->mboverview);}
  for ($ii=0;$ii<$this->nummsgs;$ii++)
   {
    $msg_num=$this->mboverview[$ii]->msgno;
    $this->msgs[$ii]=new tmsg_m();
    $this->msgs[$ii]->header=$this->mboverview[$ii];
    $this->msgs[$ii]->struc=imap_fetchstructure($this->mailbox,$msg_num);
    $this->parse_mail_structure($msg_num,0,$ftch_body,$this->msgs[$ii],"");
    }
  }

 function getmsgpart($partnum="0",&$strc)
  {
  $partarr=explode(".",$partnum);
  $tmppartarr=$partarr;
  $nextpart=array_shift($tmppartarr);
 /* print "getmsgpart :: partnum -- ".$partnum."<BR>";
  print "getmsgpart :: nextpart -- ".$nextpart."<BR>";
  print "getmsgpart :: strc->nparts -- ".$strc->nparts."<BR>";
  print "getmsgpart :: strc->partnum -- ".$strc->partnum."<BR>";
  for debuggin purposes */
  $partnum=implode(".",$tmppartarr);
  if($nextpart)
   {
    if(isset($strc->struc->parts))
    {return $this->getmsgpart($partnum,$strc->mparts[$nextpart-1]);}
    else
    {
    return $strc;
    }
   } else
   {
   return $strc;
   }
  // return imap_fetchbody($this->mailbox,$msgnum,$partnum);
  }

 function close_mailbox()
  {
  imap_close($this->mailbox);
  }

}
?>
