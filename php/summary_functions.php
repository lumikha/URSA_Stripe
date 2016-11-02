<?php
require_once 'liveServer.php';
require 'gmail_get_messages.php';

$test = false;
$tbname = 'ursa-tickets';
$tbid = 'ticket_id';

$params_t_check = [
	'TableName' => $tbname,
	'ProjectionExpression' => $tbid.',ticket_number,ticket_gmail_id,ticket_name_from,ticket_email_subject,ticket_email_from,ticket_email_body,ticket_embedded_image,ticket_email_attachment,ticket_notes,ticket_status,ticket_updated_at'
];

try {
	while (true) {
		$ticket_check = $dynamodb->scan($params_t_check);

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

$arr_unassigned = array();
$arr_mine = array();
$arr_assigned = array();
$arr_closed = array();
$arr_spam = array();
$unassigned = 0;
$mine = 0;
$assigned = 0;
$closed = 0;
$spam = 0;

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
                         
		if(preg_match('/.php/',$att_file)) {
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

		if($obj2['ticket_status']['S'] == 'unassigned') {
			array_push($arr_unassigned, array(
				"ticket_id" => $obj2[$tbid]['S'],
				"no" => $obj2['ticket_number']['S'],
				"id" => $obj2['ticket_gmail_id']['S'],
				"status" => $obj2['ticket_status']['S'],
				"subject" => $obj2['ticket_email_subject']['S'],
				"body" => $bdy_image,
				"from" => $obj2['ticket_name_from']['S'],
				"email" => $obj2['ticket_email_from']['S'],
				"attachments" => $arr_att,
				"updated" => $obj2['ticket_updated_at']['S'],
				"notes" => $noteLists
			));
			$unassigned++;
		}

		if($obj2['ticket_status']['S'] == 'mine') {
			array_push($arr_mine, array(
				"ticket_id" => $obj2[$tbid]['S'],
				"no" => $obj2['ticket_number']['S'],
				"id" => $obj2['ticket_gmail_id']['S'],
				"status" => $obj2['ticket_status']['S'],
				"subject" => $obj2['ticket_email_subject']['S'],
				"body" => $bdy_image,
				"from" => $obj2['ticket_name_from']['S'],
				"email" => $obj2['ticket_email_from']['S'],
				"attachments" => $arr_att,
				"updated" => $obj2['ticket_updated_at']['S'],
				"notes" => $noteLists
			));
			$mine++;
		}

		if($obj2['ticket_status']['S'] == 'assigned') {
			array_push($arr_assigned, array(
				"ticket_id" => $obj2[$tbid]['S'],
				"no" => $obj2['ticket_number']['S'],
				"id" => $obj2['ticket_gmail_id']['S'],
				"status" => $obj2['ticket_status']['S'],
				"subject" => $obj2['ticket_email_subject']['S'],
				"body" => $bdy_image,
				"from" => $obj2['ticket_name_from']['S'],
				"email" => $obj2['ticket_email_from']['S'],
				"attachments" => $arr_att,
				"updated" => $obj2['ticket_updated_at']['S'],
				"assigned" => $obj2['ticket_assigned_to']['S'],
				"notes" => $noteLists
			));
			$assigned++;
		}

		if($obj2['ticket_status']['S'] == 'closed') {
			array_push($arr_closed, array(
				"ticket_id" => $obj2[$tbid]['S'],
				"no" => $obj2['ticket_number']['S'],
				"id" => $obj2['ticket_gmail_id']['S'],
				"status" => $obj2['ticket_status']['S'],
				"subject" => $obj2['ticket_email_subject']['S'],
				"body" => $bdy_image,
				"from" => $obj2['ticket_name_from']['S'],
				"email" => $obj2['ticket_email_from']['S'],
				"attachments" => $arr_att,
				"updated" => $obj2['ticket_updated_at']['S'],
				"notes" => $noteLists
			));
			$closed++;
		}

		if($obj2['ticket_status']['S'] == 'spam') {
			array_push($arr_spam, array(
				"ticket_id" => $obj2[$tbid]['S'],
				"no" => $obj2['ticket_number']['S'],
				"id" => $obj2['ticket_gmail_id']['S'],
				"status" => $obj2['ticket_status']['S'],
				"subject" => $obj2['ticket_email_subject']['S'],
				"body" => $bdy_image,
				"from" => $obj2['ticket_name_from']['S'],
				"email" => $obj2['ticket_email_from']['S'],
				"attachments" => $arr_att,
				"updated" => $obj2['ticket_updated_at']['S'],
				"notes" => $noteLists
			));
		$spam++;
		}
	}
	$cnt_tckts++;
}

$em_check = array();
foreach ($result_db_customers['Items'] as $obj) {
	if($obj['customer_email']['S']){
		array_push($em_check, array(
			"email" => $obj['customer_email']['S'],
			"id" => $obj['chargify_id']['S'],
			"bname" => $obj['business_name']['S'],
			"fname" => $obj['customer_first_name']['S'],
			"lname" => $obj['customer_last_name']['S'],
			"bphone" => $obj['business_phone_no']['S']
		));
	}
}

if(@$_POST['new_thread']){
	$new_note_id = GUID();
        
	if($_POST['status'] != $_POST['curr_status']) {
		if(empty($_POST['message'])) {
			$n_ctnt = $_POST['message']."<b>~</b> changed status from ";
		} else {
			$n_ctnt = $_POST['message']."<br/></br><b>~</b> changed status from ";
		}
		$n_ctnt .= "<b>".strtoupper($_POST['curr_status'])."</b> to <b>".strtoupper($_POST['status'])."</b>.";
	} else {
		$n_ctnt = $_POST['message'];
	}

	$updatedDateNow = date('Y/m/d H:i:s');

	//insert new ticket note in ursa-ticket-notes
	$new_note = $marshaler->marshalJson('
		{
			"ticket_note_id": "'.$new_note_id.'",
			"ticket_id": "'.@$_POST['cTID'].'",
			"note_content": "'.$n_ctnt.'",
			"note_created_at": "'.$updatedDateNow.'",
			"note_created_by": "'.$fname.'",
			"ticket_current_status": "'.$_POST['status'].'"
		}
	');

	$params_new_note = [
		'TableName' => 'ursa-ticket-notes',
		'Item' => $new_note
	];

	//get list of existing notes for the current ticket_id
	$get_notes = $marshaler->marshalJson('
		{
			"ticket_id": "'.$_POST['cTID'].'"
		}
	');

	$params_get_ticket = [
		'TableName' => 'ursa-tickets',
		'Key' => $get_notes
	];

	try {
		$dynamodb->putItem($params_new_note);
		$ticket = $dynamodb->getItem($params_get_ticket);
		json_encode($ticket["Item"]);
		$existing_notes = $ticket['Item']['ticket_notes']['S'];

		if($existing_notes == "(null)") {
			$upd_note_lists = $new_note_id;
		} else {
			$upd_note_lists = $existing_notes.",".$new_note_id;
		}

		$current_ticketID = $marshaler->marshalJson('
			{
				"ticket_id": "'.$_POST['cTID'].'"
			}
		');

		$toUpdate = $marshaler->marshalJson('
			{
				":ticket_notes": "'.$upd_note_lists.'",
				":ticket_status": "'.$_POST['status'].'",
				":ticket_updated_at": "'.$updatedDateNow.'"
			}
		');

		$params = [
			'TableName' => 'ursa-tickets',
			'Key' => $current_ticketID,
			'UpdateExpression' => 'set ticket_notes=:ticket_notes, ticket_status=:ticket_status, ticket_updated_at=:ticket_updated_at', 
			'ExpressionAttributeValues'=> $toUpdate,
			'ReturnValues' => 'UPDATED_NEW'
		];

		$dynamodb->updateItem($params);
		?><script>
			window.location.href = "summary";
		</script><?php
	} catch (DynamoDbException $e) {
		echo $e->getMessage() . "\n";
	}
}
?>