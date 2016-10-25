<?php
	require_once 'dynamoDB/dbConnect.php';
	require_once 'liveServer.php';

	$id = $_REQUEST["tid"];

	$tbname = 'ursa-tickets';
    $tbid = 'ticket_id';

    $key = $marshaler->marshalJson('
	    {
	        "ticket_id": "'.$id.'"
	    }
	');
	$params = [
	    'TableName' => 'ursa-tickets',
	    'Key' => $key
	];
	try {
	    $result = $dynamodb->getItem($params);
	} catch (DynamoDbException $e) {
	    echo "Unable to get item:\n";
	    echo $e->getMessage() . "\n";
	}

	function decodeBody($body) {
        $rawData = $body;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);
        if(!$decodedMessage){
            $decodedMessage = FALSE;
        }
        return $decodedMessage;
    }

	$bdy_image = decodeBody(@$result['Item']['ticket_email_body']['S']);

	$arr_emb = explode(',', $result['Item']['ticket_embedded_image']['S']);

	preg_match_all('/src="cid:(.*)"/Uims', $bdy_image, $matches);
	if(count($matches)) {
		$cnt_emb = 0;
		foreach($matches[1] as $match) {
			$search = "src=\"cid:$match\"";
			$replace = "src=".$att_path.$result['Item']['ticket_gmail_id']['S']."/".$arr_emb[$cnt_emb];
			$bdy_image = str_replace($search, $replace, $bdy_image);  
			$cnt_emb++;       
		}
	}
	$arr_att = array();
	$att_file = $result['Item']['ticket_email_attachment']['S'];
	$arr = explode(',', $att_file);
                         
	if(preg_match('/.php/',$att_file)){
		foreach ($arr as $key) {
			@$attmts = file_get_contents($att_path.$result['Item']['ticket_gmail_id']['S'].'/attachments/'.$key);
				array_push($arr_att, $attmts);
			}
		}
                
		if($result['Item']['ticket_notes']['S'] == "(null)") {
			$noteLists = null;
		} else {
			$arr_notes = array();
			$nExp = explode(',', $result['Item']['ticket_notes']['S']);
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

	echo $result['Item']['ticket_email_subject']['S']."||+||".$result['Item']['ticket_number']['S']."||+||".$result['Item']['ticket_status']['S']."||+||".$result['Item']['ticket_name_from']['S']."||+||".$result['Item']['ticket_updated_at']['S']."||+||".$bdy_image/*."||+||".$arr_att*/;
	
	if(!empty($arr_att)) {
		echo "||+||<br><br><b>Attachments: <b><br>";
		foreach($arr_att as $at) {
			echo $at;
		}
	}

	

?>