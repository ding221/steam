<?php
namespace Lib;

class Mail//receive
{
    public $server='';
    public $username='';
    public $password='';
    public $marubox='';

    //function Mail($username, $password, $host='', $port='', $ssl = false) //Constructure
    function __construct($username, $password, $host='', $port='', $ssl = false, $type = '') //Constructure
    {
        if($port=='') {
            return false;
        }
        $strConnect ='{'.$host.':'.$port;
        if ($type) {
            $strConnect .= '/' . $type;
        }
        if ($ssl) {
            $strConnect .= '/ssl}INBOX';
        } else {
            $strConnect .= '}INBOX';
        }

        $this->server            =   $strConnect;
        $this->username          =   $username;
        $this->password          =   $password;
    }

    function connect() //Connect To the Mail Box  
    {
        $this->marubox=imap_open($this->server, $this->username, $this->password);

        if(!$this->marubox)
        {
            return false;
//          echo "Error: Connecting to mail server";  
//          exit;  
        }
        return true;
    }


    function getHeaders($mid) // Get Header info  
    {
        if(!$this->marubox)
            return false;

        $mail_header=imap_header($this->marubox, $mid);
        $sender=$mail_header->from[0];
        $sender_replyto=$mail_header->reply_to[0];
        if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster')
        {
            $subject=$this->decode_mime($mail_header->subject);

            $ccList=array();
            if (isset($mail_header->cc)){
                foreach ($mail_header->cc as $k => $v){
                    $ccList[]=$v->mailbox.'@'.$v->host;
                }
            }

            $toList=array();
            if (isset($mail_header->to)){
                foreach ($mail_header->to as $k => $v)
                {
                    $toList[]=$v->mailbox.'@'.$v->host;
                }
            }

            $ccList=implode(", ", $ccList);
            $toList=implode(", ", $toList);
            $mail_details=array(
                'fromBy'=>strtolower($sender->mailbox).'@'.$sender->host,
                'fromName'=>$this->decode_mime($sender->personal),
                'ccList'=>$ccList, //strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host,
                'toNameOth'=>$this->decode_mime($sender_replyto->personal),
                'subject'=>$subject,
                'mailDate'=>date("Y-m-d H:i:s", $mail_header->udate),
                'udate'=>$mail_header->udate,
                'toList'=>$toList//strtolower($mail_header->to[0]->mailbox).'@'.$mail_header->to[0]->host  
//                  'to'=>strtolower($mail_header->toaddress)  
            );
        }
        return $mail_details;
    }

    function get_mime_type(&$structure) //Get Mime type Internal Private Use  
    {
        $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if($structure->subtype && $structure->subtype!="PNG") {
            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    function getBody($mid, &$path = '', $imageList = '') // Get Message Body
    {
        if(!$this->marubox)
            return false;

        $body = $this->get_part($this->marubox, $mid, "TEXT/HTML");
        if ($body == "")
            $body = $this->get_part($this->marubox, $mid, "TEXT/PLAIN");

        if ($path && $imageList) {
            //处理图片
            $body=$this->embed_images($body, $path, $imageList);
        }
        return $body;
    }

    function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) //Get Part Of Message Internal Private Use
    {

        if(!$structure) {
            $structure = imap_fetchstructure($stream, $msg_number);
        }

        if($structure) {
            if($mime_type == $this->get_mime_type($structure))
            {
                if(!$part_number) {
                    $part_number = "1";
                }
                $text = imap_fetchbody($stream, $msg_number, $part_number);

                if($structure->encoding == 3) {
                    return imap_base64($text);
                  //if ($structure->parameters[0]->value!="utf-8")
                  //{
                  //    return imap_base64($text);
                  //}
                  //else
                  //{
                  //    return imap_base64($text);
                  //}
                } else if($structure->encoding == 4) {
                    //return iconv('gb2312', 'utf8', imap_qprint($text);
                    return imap_qprint($text);
                } else {
                    //return iconv('gb2312', 'utf8', $text);
                    return $text;
                }
            }
            if($structure->type == 1) /* multipart */
            {
                while(list($index, $sub_structure) = each($structure->parts))
                {
                    $prefix = '';
                    if($part_number) {
                        $prefix = $part_number . '.';
                    }
                    $data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
                    if($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    function getTotalMails() //Gets the number of messages in the current mailbox
    {
        if(!$this->marubox)
            return false;

//      return imap_headers($this->marubox);  
        return imap_num_msg($this->marubox);
    }

    function GetAttach($mid, $path) // Get Atteced File from Mail
    {
        if(!$this->marubox)
            return false;

        $struckture = imap_fetchstructure($this->marubox, $mid);

        $files=array();
        if(isset($struckture->parts) && $struckture->parts)
        {
            foreach($struckture->parts as $key => $value)
            {
                $enc=$struckture->parts[$key]->encoding;

                //取邮件附件  
                if($struckture->parts[$key]->ifdparameters)
                {
                    //命名附件, 转码
                    $name=$this->decode_mime($struckture->parts[$key]->dparameters[0]->value);
                    $extend =explode("." , $name);
                    $file['extension'] = $extend[count($extend)-1];
                    $file['pathname']  = $this->setPathName($key, $file['extension']);
                    $file['title']     = !empty($name) ? htmlspecialchars($name) : str_replace('.' . $file['extension'], '', $name);
                    $file['size']      = isset($struckture->parts[$key]->dparameters[1]->value) ? $struckture->parts[$key]->dparameters[1]->value : '';
//                  $file['tmpname']   = $struckture->parts[$key]->dparameters[0]->value;  
                    if(@$struckture->parts[$key]->disposition=="ATTACHMENT")
                    {
                        $file['type']      = 1;
                    }
                    else
                    {
                        $file['type']      = 0;
                    }
                    $files[] = $file;

                    $message = imap_fetchbody($this->marubox, $mid, $key+1);
                    if ($enc == 0)
                        $message = imap_8bit($message);
                    if ($enc == 1)
                        $message = imap_8bit ($message);
                    if ($enc == 2)
                        $message = imap_binary ($message);
                    if ($enc == 3)//图片  
                        $message = imap_base64 ($message);
                    if ($enc == 4)
                        $message = quoted_printable_decode($message);
                    if ($enc == 5)
                        $message = $message;
                    $fp=fopen($path.$file['pathname'], "w");
                    fwrite($fp, $message);
                    fclose($fp);

                }
                // 处理内容中包含图片的部分  
                if(isset($struckture->parts[$key]->parts) && $struckture->parts[$key]->parts)
                {
                    foreach($struckture->parts[$key]->parts as $keyb => $valueb)
                    {
                        $enc=$struckture->parts[$key]->parts[$keyb]->encoding;
                        if($struckture->parts[$key]->parts[$keyb]->ifdparameters)
                        {
                            //命名图片  
                            $name=$this->decode_mime($struckture->parts[$key]->parts[$keyb]->dparameters[0]->value);
                            $extend =explode("." , $name);
                            $file['extension'] = $extend[count($extend)-1];
                            $file['pathname']  = $this->setPathName($key, $file['extension']);
                            $file['title']     = !empty($name) ? htmlspecialchars($name) : str_replace('.' . $file['extension'], '', $name);
                            $file['size']      = $struckture->parts[$key]->parts[$keyb]->dparameters[1]->value;
//                          $file['tmpname']   = $struckture->parts[$key]->dparameters[0]->value;  
                            $file['type']      = 0;
                            $files[] = $file;

                            $partnro = ($key+1).".".($keyb+1);

                            $message = imap_fetchbody($this->marubox, $mid, $partnro);
                            if ($enc == 0)
                                $message = imap_8bit($message);
                            if ($enc == 1)
                                $message = imap_8bit ($message);
                            if ($enc == 2)
                                $message = imap_binary ($message);
                            if ($enc == 3)
                                $message = imap_base64 ($message);
                            if ($enc == 4)
                                $message = quoted_printable_decode($message);
                            if ($enc == 5)
                                $message = $message;
                            $fp=fopen($path.$file['pathname'], "w");
                            fwrite($fp, $message);
                            fclose($fp);
                        }
                    }
                }
            }
        }
        //move mail to taskMailBox  
        $this->move_mails($mid, $this->marubox);

        return $files;
    }

    function embed_images(&$body, &$path, $imageList)
    {
        // get all img tags  
        preg_match_all('/<img.*?>/', $body, $matches);
        if (!isset($matches[0])) return;

        foreach ($matches[0] as $img)
        {
            // replace image web path with local path  
            preg_match('/src="(.*?)"/', $img, $m);
            if (!isset($m[1])) continue;
            $arr = parse_url($m[1]);
            if (!isset($arr['scheme']) || !isset($arr['path']))continue;

//          if (!isset($arr['host']) || !isset($arr['path']))continue;  
            if ($arr['scheme']!="http")
            {
                $filename=explode("@", $arr['path']);
                $body = str_replace($img, '<img alt="" src="'.$path.$imageList[$filename[0]].'" style="border: none;" />', $body);
            }
        }
        return $body;
    }

    function deleteMails($mid) // Delete That Mail  
    {
        if(!$this->marubox)
            return false;

        imap_delete($this->marubox, $mid);
    }
    function close_mailbox() //Close Mail Box  
    {
        if(!$this->marubox)
            return false;

        imap_close($this->marubox, CL_EXPUNGE);
    }

    //移动邮件到指定分组  
    function move_mails($msglist, $mailbox)
    {
        if(!$this->marubox)
            return false;

        @imap_mail_move($this->marubox, $msglist, $mailbox);
    }

    function creat_mailbox($mailbox)
    {
        if(!$this->marubox)
            return false;

        //imap_renamemailbox($imap_stream, $old_mbox, $new_mbox);
        imap_create($this->marubox, $mailbox);
    }

    /* 
     * decode_mime()转换邮件标题的字符编码, 处理乱码
     */
    function decode_mime($str){
        $str=imap_mime_header_decode($str);
        return $str[0]->text;
        echo "str";print_r($str);
        if ($str[0]->charset!="default")
        {echo "==".$str[0]->text;
            return iconv($str[0]->charset, 'utf8', $str[0]->text);
        }
        else
        {
            return $str[0]->text;
        }
    }

    /**
     * Set path name of the uploaded file to be saved.
     *
     * @param  int    $fileID
     * @param  string $extension
     * @access public
     * @return string
     */
    public function setPathName($fileID, $extension)
    {
        return date('Ym/dHis', time()) . $fileID . mt_rand(0, 10000) . '.' . $extension;
    }

}  