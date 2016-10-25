<?php
    require_once 'dynamoDB/dbConnect.php';
    require_once realpath(dirname(__FILE__) . '/lib/google/apiclient/src/Google/autoload.php');
    require_once 'liveServer.php';

    $tbname = 'ursa-tickets';
    $tbid = 'ticket_id';

    $params_t_check = [
    'TableName' => $tbname,
    'ProjectionExpression' => $tbid.',ticket_number,ticket_gmail_id,ticket_name_from,ticket_email_subject,ticket_email_from,ticket_email_body,ticket_embedded_image,ticket_email_attachment,ticket_notes,ticket_status'
    ];

    try {
        while (true) {
            $ticket_check = $dynamodb->scan($params_t_check);

            foreach ($ticket_check['Items'] as $i) {
                $movie = $marshaler->unmarshalItem($i);
            }

            if (isset($ticket_check['LastEvaluatedKey'])) {
                $params_t_check['ExclusiveStartKey'] = $ticket_check['LastEvaluatedKey'];
                $ticket_check = $dynamodb->scan($params_t_check);
            } else {
                break;
            }
        }
    } catch (DynamoDbException $e) {
        echo "Unable to scan USERS:\n";
        echo $e->getMessage() . "\n";
    }

    $test = array();
    foreach ($ticket_check['Items'] as $obj) {
        if($obj['ticket_gmail_id']['S']){
            array_push($test, $obj['ticket_gmail_id']['S']);
        }
    }

    $arr_msgs = array();
    $cnt_tckts = 0;
    foreach ($ticket_check['Items'] as $obj2) {
        if($obj2['ticket_gmail_id']['S'] && $obj2['ticket_status']['S'] != "closed"){
            $bdy_image = decodeBody(@$obj2['ticket_email_body']['S']);

            $arr_emb = explode(',', $obj2['ticket_embedded_image']['S']);

            preg_match_all('/src="cid:(.*)"/Uims', $bdy_image, $matches);
            if(count($matches)) {
                $cnt_emb = 0;
                foreach($matches[1] as $match) {
                    $search = "src=\"cid:$match\"";
                    $replace = "src=".$att_path.$obj2['ticket_gmail_id']['S']."/".$arr_emb[$cnt_emb];
                    $bdy_image = str_replace($search, $replace, $bdy_image);  
                    $cnt_emb++;       
                }
            }
            $arr_att = array();
            $att_file = $obj2['ticket_email_attachment']['S'];
            $arr = explode(',', $att_file);
                     
            if(preg_match('/.php/',$att_file)){
                foreach ($arr as $key) {
                    @$attmts = file_get_contents($att_path.$obj2['ticket_gmail_id']['S'].'/attachments/'.$key);
                    array_push($arr_att, $attmts);
                }
            }
            
            if($obj2['ticket_notes']['S'] == "(null)") {
                $noteLists = null;
            } else {
                $arr_notes = array();
                $nExp = explode(',', $obj2['ticket_notes']['S']);
                foreach($nExp as $tn) {
                    $get_notes = $marshaler->marshalJson('
                        {
                            "ticket_note_id": "'.$tn.'"
                        }
                    ');
                    $params_get_notes = [
                        'TableName' => 'ursa-ticket-notes',
                        'Key' => $get_notes
                    ];

                    try {
                        $data_tNote = $dynamodb->getItem($params_get_notes);
                        array_push($arr_notes, array(
                            "n_id" => $data_tNote['Item']['ticket_note_id'],
                            "n_created_at" => $data_tNote['Item']['note_created_at'],
                            "n_created_by" => $data_tNote['Item']['note_created_by'],
                            "n_content" => $data_tNote['Item']['note_content']
                        ));
                    } catch (DynamoDbException $e) {
                        echo $e->getMessage() . "\n";
                    }
                }
                $noteLists = $arr_notes;
            }

            if($live_server) {
                $locpath = "../URSA_att";
                $replace_livepath = "attachments";
                $arr_att = str_replace($locpath, $replace_livepath, $arr_att);
            }

            array_push($arr_msgs, array(
                $tbid => $obj2[$tbid]['S'],
                "no" => $obj2['ticket_number']['S'],
                "id" => $obj2['ticket_gmail_id']['S'],
                "status" => $obj2['ticket_status']['S'],
                "subject" => $obj2['ticket_email_subject']['S'],
                "body" => $bdy_image,
                "from" => $obj2['ticket_name_from']['S'],
                "email" => $obj2['ticket_email_from']['S'],
                "attachments" => $arr_att,
                "notes" => $noteLists
            ));
        }
        $cnt_tckts++;
    }

    define('APPLICATION_NAME', 'Gmail API PHP Quickstart');
    define('CLIENT_SECRET_PATH', 'lib/secret_biglo/client_secret.json');
    define('SCOPES', Google_Service_Gmail::GMAIL_READONLY);
    define('CREDENTIALS_PATH', 'lib/secret_biglo/gmail-php-quickstart.json');

    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfigFile(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');
    
    $accessToken = json_decode(file_get_contents(CREDENTIALS_PATH), true);          
    $client->setAccessToken($accessToken);
/*
 * Decode the body.
 * @param : encoded body  - or null
 * @return : the body if found, else FALSE;
 */
function decodeBody($body) {
    $rawData = $body;
    $sanitizedData = strtr($rawData,'-_', '+/');
    $decodedMessage = base64_decode($sanitizedData);
    if(!$decodedMessage){
        $decodedMessage = FALSE;
    }
    return $decodedMessage;
}

function UID() {
    date_default_timezone_set("Asia/Manila");
    $t = microtime(true);
    $micro = sprintf("%06d",($t - floor($t)) * 1000000);
    $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

    return $d->format("YmdHisu");
}

function createFile($data64, $message_id, $filename) {
    global $att_path;
    $file_decoded = base64_decode ($data64);
    $file = fopen ($att_path.$message_id.'/attachments/'.$filename ,'w');
    fwrite ($file, $file_decoded);
    fclose ($file);
}

function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

$gmail = new Google_Service_Gmail($client);

$list = $gmail->users_messages->listUsersMessages('me', ['maxResults' => 1000]);

try{
    $arr_fetched_emails = array();
    $email_num =1;
    while ($list->getMessages() != null) {
        foreach ($list->getMessages() as $mlist) {
            $message_id = $mlist->id;
            $optParamsGet2['format'] = 'full';
            $single_message = $gmail->users_messages->get('me', $message_id, $optParamsGet2);
            $payload = $single_message->getPayload();
            $parts = $payload->getParts();
            // With no attachment, the payload might be directly in the body, encoded.
            $header = $payload->getHeaders();
            $body = $payload->getBody();

            $from_email = "";
            foreach($header as $head) {
                if($head['name'] == 'Date') {
                    $date = $head['value'];
                }
                if($head['name'] == 'Subject') {
                    $subject = $head['value'];
                }
                if($head['name'] == 'From') {
                    $from = $head['value'];
                }
                if($head['name'] == 'Authentication-Results') {
                    $get_email = explode("smtp.mailfrom=", $head['value']); 
                    if(strpos($get_email[1], ";")) {
                        $if_yahoo = explode(";", $get_email[1]);
                        $from_email = $if_yahoo[0];
                    } else {
                        $from_email = $get_email[1];
                    }
                }
            }

            if( in_array( $message_id ,$test)) { 
                break;
            } else if($from_email!="") {
                array_push($arr_fetched_emails, array(
                    "f_msgID" => $message_id,
                    "f_parts" => $parts,
                    "f_header" => $header,
                    "f_body" => $body,
                    "f_from_email" => $from_email,
                    "f_date" => $date,
                    "f_subject" => $subject,
                    "f_from_name" => $from
                ));
            } else {}
        }

        if ($list->getNextPageToken() != null) {
            $pageToken = $list->getNextPageToken();
            $list = $gmail->users_messages->listUsersMessages('me', ['pageToken' => $pageToken, 'maxResults' => 1000]);
        } else {
            break;
        }
    }

    foreach(array_reverse($arr_fetched_emails, true) as $fem) {
        $message_id = $fem['f_msgID'];
        $parts = $fem['f_parts'];
        $header = $fem['f_header'];
        $body = $fem['f_body'];
        $from_email = $fem['f_from_email'];
        $date = $fem['f_date'];
        $subject = $fem['f_subject'];
        $from = $fem['f_from_name'];

        $cnt_tckts++;
        $FOUND_BODY = FALSE;
        // If we didn't find a body, let's look for the parts
        if(!$FOUND_BODY) {
            foreach ($parts  as $part) {
                if($part['parts'] && !$FOUND_BODY) {
                    foreach ($part['parts'] as $p) {
                        if($p['parts'] && count($p['parts']) > 0){
                            foreach ($p['parts'] as $y) {
                                if(($y['mimeType'] === 'text/html') && $y['body']) {
                                    $FOUND_BODY = decodeBody($y['body']->data);
                                    $body_msg = $y['body']->data;
                                    break;
                                }
                            }
                        } else if(($p['mimeType'] === 'text/html') && $p['body']) {
                            $FOUND_BODY = decodeBody($p['body']->data);
                            $body_msg = $p['body']->data;
                            break;
                        }
                    }
                }
                if($FOUND_BODY) {
                    break;
                }
            }
        }

        $uniqueFilename = "(no embedded image)";
        $arr_emb_imgs = array();
        // let's save all the images linked to the mail's body:
        if($FOUND_BODY && count($parts) > 1){
            $images_linked = array();
            foreach ($parts  as $part) {
                if($part['filename']){
                    array_push($images_linked, $part);
                } else {
                if($part['parts']) {
                    foreach ($part['parts'] as $p) {
                        if($p['parts'] && count($p['parts']) > 0){
                            foreach ($p['parts'] as $y) {
                                if(($y['mimeType'] === 'text/html') && $y['body']) {
                                    array_push($images_linked, $y);
                                }
                            }
                        } else if(($p['mimeType'] !== 'text/html') && $p['body']) {
                            array_push($images_linked, $p);
                        }
                    }
                }
            }
        }
        // special case for the wdcid...
        preg_match_all('/wdcid(.*)"/Uims', $FOUND_BODY, $wdmatches);
        if(count($wdmatches)) {
            $z = 0;
            foreach($wdmatches[0] as $match) {
                $z++;
                if($z > 9){
                    $FOUND_BODY = str_replace($match, 'image0' . $z . '@', $FOUND_BODY);
                } else {
                    $FOUND_BODY = str_replace($match, 'image00' . $z . '@', $FOUND_BODY);
                }
            }
        }
        preg_match_all('/src="cid:(.*)"/Uims', $FOUND_BODY, $matches);
        if(count($matches)) {
            mkdir($att_path.$message_id, 0755, true);
            $search = array();
            $replace = array();
            // let's trasnform the CIDs as base64 attachements 
            foreach($matches[1] as $match) {
                foreach($images_linked as $img_linked) {
                    foreach($img_linked['headers'] as $img_lnk) {
                        if( $img_lnk['name'] === 'Content-ID' || $img_lnk['name'] === 'Content-Id' || $img_lnk['name'] === 'X-Attachment-Id'){
                            if ($match === str_replace('>', '', str_replace('<', '', $img_lnk->value)) || explode("@",$match)[0] === explode(".", $img_linked->filename)[0] || explode("@",$match)[0] === $img_linked->filename) {
                                $search = "src=\"cid:$match\"";
                                $mimetype = $img_linked->mimeType;
                                $mime_exp = explode('/', $mimetype);
                                $attachment = $gmail->users_messages_attachments->get('me', $mlist->id, $img_linked['body']->attachmentId);
                                $data64 = strtr($attachment->getData(), array('-' => '+', '_' => '/'));
                                $replace = "src=\"data:" . $mimetype . ";base64," . $data64 . "\"";
                                $FOUND_BODY = str_replace($search, $replace, $FOUND_BODY);
                            }
                        }
                    }
                }
                $uniqueFilename = UID();
                file_put_contents($att_path.$message_id."/".$uniqueFilename.".".$mime_exp[1], decodeBody($attachment['data']));
                $uniqueFilename = $uniqueFilename.".".$mime_exp[1];
                array_push($arr_emb_imgs, $uniqueFilename);
            }
        }
    }

    if($arr_emb_imgs != null) {
        $uniqueFilename = "";
        $cnt_aei = 0;
        foreach($arr_emb_imgs as $aei) {
            if($cnt_aei != 0) {
                $uniqueFilename .= ",".$aei;
            } else {
                $uniqueFilename .= $aei;
            }
            $cnt_aei++;
        }
    }

    // If we didn't find the body in the last parts, 
    // let's loop for the first parts (text-html only)
    if(!$FOUND_BODY) {
        foreach ($parts  as $part) {
            if($part['body'] && $part['mimeType'] === 'text/html') {
                $FOUND_BODY = decodeBody($part['body']->data);
                $body_msg = $part['body']->data;
                break;
            }
        }
    }
    // With no attachment, the payload might be directly in the body, encoded.
    if(!$FOUND_BODY) {
        $FOUND_BODY = decodeBody($body['data']);
        $body = decodeBody($body['data']);
    }
    // Last try: if we didn't find the body in the last parts, 
    // let's loop for the first parts (text-plain only)
    if(!$FOUND_BODY) {
        foreach ($parts  as $part) {
            if($part['body']) {
                $FOUND_BODY = decodeBody($part['body']->data);
                $body_msg = $part['body']->data;
                break;
            }
        }
    }
    if(!$FOUND_BODY) {
        $FOUND_BODY = '(No message)';
        $body_msg = '(No message)';
    }

    $uniqueFilename2 = "";
    $attachmentName = array();
    $arr_att = array();
    $cnt_att = 0;
    foreach($parts as $ptest) {
        if($ptest['body']['attachmentId']) {
            if (!file_exists($att_path.$message_id.'/attachments')) {
                mkdir($att_path.$message_id.'/attachments', 0755, true);
            }
            $uniqueFilename2 = UID().".php";
            $attachment = $gmail->users_messages_attachments->get('me', $message_id, $ptest['body']['attachmentId']);
            $data64 = strtr($attachment->getData(), array('-' => '+', '_' => '/'));
            //$replace = "src=\"data:" . $ptest['mimeType'] . ";base64," . $data64 . "\"";
                        
            if($ptest['mimeType'] == 'image/gif' || $ptest['mimeType'] == 'image/png' || $ptest['mimeType'] == 'image/jpeg') {
                $att = "
                    <div class='imgatt1' onmouseover='showTitle(this)' onmouseout='hideTitle(this)' >
                        <a href='#' class='open-modal-previewAtt' data-src='".$att_path.$message_id."/attachments/".$ptest['filename']."' data-fn='".$ptest['filename']."'>
                            <div class='att_title' style='cursor: zoom-in;'>
                                <span>".$ptest['filename']."</span>
                            </div>
                        </a>
                        <img class='imgatt2'  src='".$att_path.$message_id."/attachments/".$ptest['filename']."'>
                        <a href='".$att_path.$message_id."/attachments/".$ptest['filename']."' download='".$ptest['filename']."' style=''>
                            <button></button>
                        </a>
                    </div>";
                array_push($arr_att, $att);
                array_push($attachmentName, $uniqueFilename2);
                file_put_contents($att_path.$message_id."/attachments/$uniqueFilename2", $att);
                file_put_contents($att_path.$message_id."/attachments/".$ptest['filename'], decodeBody($attachment['data']));
            } else if($ptest['mimeType'] == 'application/pdf') {
                $att = "  
                    <div class='imgatt1' onmouseover='showTitle(this)' onmouseout='hideTitle(this)'>
                        <div class='att_title'>
                            <span>".$ptest['filename']."</span>
                        </div>
                        <img class='imgatt2'  src='img/pdf.png'>
                        <a href='".$att_path.$message_id."/attachments/".$ptest['filename']."' download='".$ptest['filename']."' style=''>
                            <button></button>
                        </a>
                    </div>";
                    array_push($arr_att, $att);
                    array_push($attachmentName, $uniqueFilename2);
                    file_put_contents($att_path.$message_id."/attachments/$uniqueFilename2", $att);
                    createFile($data64, $message_id, $ptest['filename']);
                } else if($ptest['mimeType'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                    $att = "  
                        <div class='imgatt1' onmouseover='showTitle(this)' onmouseout='hideTitle(this)'>
                            <div class='att_title'>
                                <span>".$ptest['filename']."</span>
                            </div>
                            <img class='imgatt2'  src='img/docx.png'></a>
                            <a href='".$att_path.$message_id."/attachments/".$ptest['filename']."' download='".$ptest['filename']."' style=''>
                                <button></button>
                            </a>
                        </div>";
                    array_push($arr_att, $att);
                    array_push($attachmentName, $uniqueFilename2);
                    file_put_contents($att_path.$message_id."/attachments/$uniqueFilename2", $att);
                    createFile($data64, $message_id, $ptest['filename']);
                } else if($ptest['mimeType'] == 'application/msword') {
                    $att = "  
                        <div class='imgatt1' onmouseover='showTitle(this)' onmouseout='hideTitle(this)'>
                            <div class='att_title'>
                                <span>".$ptest['filename']."</span>
                            </div>
                            <img class='imgatt2'  src='img/doc.png'></a>
                            <a href='".$att_path.$message_id."/attachments/".$ptest['filename']."' download='".$ptest['filename']."' style=''>
                                <button></button>
                            </a>
                        </div>";
                    array_push($arr_att, $att);
                    array_push($attachmentName, $uniqueFilename2);
                    file_put_contents($att_path.$message_id."/attachments/$uniqueFilename2", $att);
                    createFile($data64, $message_id, $ptest['filename']);
                } else {
                    $att = "  
                        <div class='imgatt1' onmouseover='showTitle(this)' onmouseout='hideTitle(this)'>
                            <div class='att_title'>
                                <span>".$ptest['filename']."</span>
                            </div>
                            <img class='imgatt2'  src='img/unknown.png'></a>
                            <a href='".$att_path.$message_id."/attachments/".$ptest['filename']."' download='".$ptest['filename']."' style=''>
                                <button></button>
                            </a>
                        </div>";
                    array_push($arr_att, $att);
                    array_push($attachmentName, $uniqueFilename2);
                    file_put_contents($att_path.$message_id."/attachments/$uniqueFilename2", $att);
                    createFile($data64, $message_id, $ptest['filename']);
                }
            }
            $cnt_att++;
        }

        $comma_separated = "(no attachment)";
        if($uniqueFilename2 != ""){
            $comma_separated = implode(",", $attachmentName);
        }
        $t_id = UID();
        if(!empty($FOUND_BODY)){
            $item_t_add = $marshaler->marshalJson('
                {
                    "'.$tbid.'": "'.$t_id.'",
                    "ticket_number": "'.$cnt_tckts.'",
                    "ticket_notes": "(null)",
                    "ticket_status": "unassigned",
                    "ticket_gmail_id": "'.$message_id.'",
                    "ticket_email_from": "'.$from_email.'",
                    "ticket_name_from": "'.$from.'",
                    "ticket_email_subject": "'.$subject.'",
                    "ticket_email_date": "'.$date.'",
                    "ticket_email_body": "'.$body_msg.'",
                    "ticket_embedded_image": "'.$uniqueFilename.'",
                    "ticket_email_attachment": "'.$comma_separated.'",
                    "ticket_updated_at": "(null)"
                }
            ');

            $params_t_add = [
                'TableName' => $tbname,
                'Item' => $item_t_add
            ];

            try {
                $result = $dynamodb->putItem($params_t_add);
            } catch (DynamoDbException $e) {
                echo "Unable to add item:\n";
                echo $e->getMessage() . "\n";
            }

            array_unshift($arr_msgs, array(
                $tbid => $t_id,
                "no" => $cnt_tckts,
                "id" => $message_id,
                "status" => "unassigned",
                "subject" => $subject,
                "body" => $FOUND_BODY,
                "from" => $from,
                "email" => $from_email,
                "attachments" => $arr_att,
                "notes" => null
            ));
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

array_sort_by_column($arr_msgs, $tbid);
?>