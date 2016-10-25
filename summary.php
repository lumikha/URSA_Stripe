<?php
    require 'header.php';
    require_once 'liveServer.php';
    require 'gmail_get_messages.php';
    //========Twilio Connection========

    require_once 'lib/twilio/Twilio/autoload.php'; // Loads the library
    use Twilio\Rest\Client;

    // Your Account Sid and Auth Token from twilio.com/user/account
    $sid = "AC36a61e9c2a4dd751bda6a1149632bece";
    $token = "6dc98a3bf47b6870ae285c529e8a6fca";
    $client = new Client($sid, $token);

    $phone_num = @$_POST['select'];
    @$text = @$_POST['textMessage'];
    $from = "+15005550006";
    //+12565988804

    /*
    $number = $client->incomingPhoneNumbers->create(
        array(
            "voiceUrl" => "http://demo.twilio.com/docs/voice.xml",
            "phoneNumber" => "+15005550006"
        )
    );

    echo $number->sid;
    */

    if(@$_POST['send']){
    $sms = $client->messages->create(
        "$phone_num",
        array(
            "from" => "$from",
            "body" => "$text"
        )
    );
    if($sms->sid){
        //echo "SID: ".$sms->sid;
        //echo "<br><h1><b><i>Message Sent.<i><b></h1>";
        ?>
            <script type="text/javascript">
                alert("Message Sent.");
            </script>
        <?php
    }else{
        ?>
            <script type="text/javascript">
                alert("Message Not Sent.");
            </script>
        <?php
    }
    }


    if(@$_POST['call']){
    $call = $client->calls->create(
        "$phone_num", "$from",
        array("url" => "http://demo.twilio.com/docs/voice.xml")
    );
    if($call->sid){
        ?>
            <script type="text/javascript">
                alert("Connected.");
            </script>
        <?php
    }else{
        ?>
            <script type="text/javascript">
                alert("Failed.");
            </script>
        <?php
    }
    }

    //=================================


    //==========SUPPORT 2=======

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
?>


<style type="text/css">

body
{
   margin-left: 10% !important;
}
  .modal.fade .modal-dialog {
            -webkit-transform: scale(0.1);
            -moz-transform: scale(0.1);
            -ms-transform: scale(0.1);
            transform: scale(0.1);
            top: 300px;
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .modal.fade .modal-dialog {
            -webkit-transform: scale(0.1);
            -moz-transform: scale(0.1);
            -ms-transform: scale(0.1);
            transform: scale(0.1);
            top: 300px;
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .modal.fade.in .modal-dialog {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            transform: scale(1);
            -webkit-transform: translate3d(0, -300px, 0);
            transform: translate3d(0, -300px, 0);
            opacity: 1;
        }
    #boxes
    {
    
        margin-top: -5.25em;

    }

    .ticketsummary2
    {
         /*background-color: rgba(0, 0, 0, 0.2);*/
         height: 500px;
         width: 92% ;
         margin-left: 0.5em;
         top: 1.25em;
    }

    .boxsupport
    {
        margin-left: 6.25em;
    }
  .folder_list_hide {
            display: none;
        }
  .table thead tr th{
            font-weight: bold;
        }
        .table tbody tr {
            cursor: pointer;
        }
        .table tbody tr:hover {
            background-color: #e6f1ff;
        }
        .table tr th:first-child() {
            width: 1px;
        }
        .table tr th:nth-child(2) {
            width: 100px;
        }
        .table tr th:nth-child(3) {
            width: 400px;
        }
        .table tr td:first-child() {
            width: 1px;
        }
        .table tr td:nth-child(2) {
            width: 100px;
        }
        .table tr td:nth-child(3) {
            width: 400px;
        }
        .table_email_content {
            min-height: 38px;
            height: 38px;
            overflow: hidden;
        }
        .table_email_content img {
            width: 30% !important;
            height: 30% !important;
        }
        .dataTables_filter {
            padding-right: 10px;
        }
        .dataTables_filter input {
            width: 200px !important;
        }
        table.dataTable thead th.sorting:after {
            content: "";
        }
        table.dataTable thead th.sorting_asc:after {
            content: "\27A4";
            transform: rotate(-90deg);
            margin-left: 10px !important;
        }
        table.dataTable thead th.sorting_desc:after {
            content: "\27A4";
            transform: rotate(90deg);
        }
        .dataTables_info {
            padding-left: 20px;
        }
        #list_unassigned .dataTables_empty, #list_mine .dataTables_empty, #list_assigned .dataTables_empty {
            height: 300px !important;
            background: url(img/chill_bear.png);
            background-size: 50%;
            background-position: center 30px; 
            background-repeat: no-repeat;
            cursor: default;
        }
        #list_spam .dataTables_empty {
            height: 300px !important;
            background: url(img/empty_garbage.png);
            background-size: 35%;
            background-position: center; 
            background-repeat: no-repeat;
            cursor: default;
        }
        #list_unassigned .dataTables_empty:hover,  #list_mine .dataTables_empty:hover, #list_assigned .dataTables_empty:hover, #list_spam .dataTables_empty:hover {
            background-color: #fff !important;
        }
        .sorting:focus, .sorting_asc:focus, .sorting_desc:focus {
            outline: none;
        }
        .pagination {
            padding-right: 20px;
        }
        .pagination li {
            display: inline-block;
            margin: 0;
        }
        .pagination li a {
            background-color: transparent;
            font-size: 20px;
            margin-top: -8px;
            color: #cccccc;
            border: none;
        }
        .pagination li a:hover {
            background-color: transparent;
            font-weight: bold;
        }
        .btngrpChckBxs {
            position: absolute;
            margin-top: 20px;
            margin-left: 200px;
            width: 200px;
            display: none;
        }
        .btngrpChckBxs li {
            display: inline-block;
            border: 1px solid #d4dce0;
            display: table-cell;
            position: relative;
            padding: 5px 10px 5px 10px;
            cursor: pointer;
            background-color: #fff;;
        }
        .btngrpChckBxs li:hover {
            background-color: #d6dde3;
        }
        .btngrpChckBxs .btnAssignTo {
            border-radius: 5px 0px 0px 5px;
        }
        .btngrpChckBxs .btnStatus {
            
        }
        .btngrpChckBxs .btnTag {
            border-radius: 0px 5px 5px 0px;
        }
        .ttAssignTo {
            background-color: black;
            position: absolute;
            border-radius: 5px;
            z-index: 1;
            color: #fff;
            padding: 5px 10px 5px 10px;
            margin-top: 10px;
            margin-left: -10px;
            display: none;
        }
        .ttAssignTo::after {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background-color: black;
            margin-top: -30px;
            margin-left: 9px;
            content: "";
            z-index: -1;
            -ms-transform: rotate(45deg); /* IE 9 */
            -webkit-transform: rotate(45deg); /* Chrome, Safari, Opera */
            transform: rotate(45deg);
        }
        .ttStatus {
            background-color: black;
            position: absolute;
            border-radius: 5px;
            z-index: 1;
            color: #fff;
            padding: 5px 10px 5px 10px;
            margin-top: 10px;
            margin-left: 24px;
            display: none;
        }
        .ttStatus::after {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background-color: black;
            margin-top: -30px;
            margin-left: 9px;
            content: "";
            z-index: -1;
            -ms-transform: rotate(45deg); /* IE 9 */
            -webkit-transform: rotate(45deg); /* Chrome, Safari, Opera */
            transform: rotate(45deg);
        }
        .ttTag {
            background-color: black;
            position: absolute;
            border-radius: 5px;
            z-index: 1;
            color: #fff;
            padding: 5px 10px 5px 10px;
            margin-top: 10px;
            margin-left: 69px;
            display: none;
        }
        .ttTag::after {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background-color: black;
            margin-top: -30px;
            margin-left: 1px;
            content: "";
            z-index: -1;
            -ms-transform: rotate(45deg); /* IE 9 */
            -webkit-transform: rotate(45deg); /* Chrome, Safari, Opera */
            transform: rotate(45deg);
        }
        /*** overwrite th width using scroll ***/
        .dataTables_scrollHeadInner table {
          width: 110% !important;
        }
</style>

<!-- SUPPORT MODAL
<div class="modal fade" id="viewTicket" tabindex="-1" role="dialog">
        <div id="modal_dialog" class="modal-dialog modal-md" >
            <div class="modal-content">
                <span>
                          
                </span>
                <div class="modal-body">

                    <div class="row">
                            <div class=" col-md-12  email_opt_top">
                                 
          
                             <a id="reply" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-pencil"></span> Reply
                            </a>

                             <a id="note" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-comment"></span> Add Note
                            </a>

                             <a id="assign" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-user"></span> Assign
                            </a>

                             <a id="status" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-flag"></span> Status
                            </a>

                             <a id="tag" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-tags"></span> Tag
                            </a>

                              <a id="tag" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-trash"></span> Delete
                            </a>


                            </div>
                       </div>



                    <input type="type" id="cID" hidden>
                    <div class="row">
                    <br>

                        <div class="col-md-6 col-md-offset-6">

                             <div class="input-group">
                                    <input type="text" class="form-control" placeholder="tNO"/ readonly="">
                                           <span class="input-group-addon">||</span>
                                    <input type="text" class="form-control" placeholder="tSTAT"/ readonly="">
                             </div>
                        </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <label>Subject:</label>
                            <input type="text" class="form-control tNO" value="" style="text-align: center; font-weight: bold;" readonly>
                           </div>


                        <div class="col-md-5">
                            <label style="display: none;">Ticket ID</label>
                            <input type="text" class="form-control" id="tID" value="" style="text-align: center; font-weight: bold; display: none;" readonly>
                        </div>



                        <div class="col-md-3 text-right">
                            <a id="expand" href="#"><span id="glyph_resize" class="btn btn-info btn-sm glyphicon glyphicon-resize-full " aria-hidden="true"></span></a>
                            <a id="close_modal" href="#"><span id="glyph_close" class="btn btn-danger btn-sm glyphicon glyphicon-remove " aria-hidden="true"></span></a>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <label>From:</label>
                            <input type="text" class="form-control" id="tSubj" name="ticket_subject" readonly>
                        </div>

                         <div class="col-md-4">
                            <label>Date:</label>
                            <input type="text" class="form-control" id="tSubj" name="ticket_subject" readonly>
                        </div>
                    </div>
                    <style>
                        #attID {
                            background-image: url('img/35.gif');
                            background-color: #f2f2f2;
                            background-repeat: no-repeat;
                            background-size: 50px;
                            background-position: center; 
                            border: 1 solid #fff2e6;
                            height: 200px;
                            box-shadow: 9px 9px 10px #818181;
                            -webkit-box-shadow: 9px 9px 10px #818181;
                            -moz-box-shadow: 9px 9px 10px #818181;
                            /*border-radius: 5px;*/
                            cursor: pointer;
                            transition: 0.3s;
                        }

                        #attID:hover {
                            -webkit-filter: brightness(50%);
                        }
                    </style>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Message</label>
                            <div id="tBody" class="form-control" readonly style="overflow:auto;height:300px; background-color: #fff;">
                                <span id="tMsg" style="height: auto;" readonly></span> 
                                <span id="tMsgAtt"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <center>
                            <a href="#" class="btn btn-danger open-modal-updTicket">Update Ticket</a>
                            <button class="btn btn-danger" onclick="gotoCustomerPage()">Go to Customer Page</button>
                        </center>
                    </div>
                    <div class="row">
                            <style>
                                #magic_buttons button {
                                    background-color: #e6e6e6;
                                    width: 100%;
                                    text-align: left;
                                }
                                #magic_buttons button:hover {
                                    background-color: #ffffff;
                                }
                                #magic_buttons p {
                                    background-color: #f2f2f2;
                                    padding: 5px;
                                }
                                #lbl_th span {
                                    background-color: #f2f2f2;
                                    padding: 5px;
                                }
                            </style>
                        <label class="col-md-12">Thread(s)</label>
                        <div id="lbl_th" class="col-md-12">
                        </div>
                        <div id="magic_buttons" class="col-md-12">
                        </div>
                    </div>
                </div>
        
            </div>
        </div>
    </div>
-->
    <?php
    //==============END SUPPORT============
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
    <style>

        .modal.fade .modal-dialog {
            -webkit-transform: scale(0.1);
            -moz-transform: scale(0.1);
            -ms-transform: scale(0.1);
            transform: scale(0.1);
            top: 300px;
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .modal.fade .modal-dialog {
            -webkit-transform: scale(0.1);
            -moz-transform: scale(0.1);
            -ms-transform: scale(0.1);
            transform: scale(0.1);
            top: 300px;
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .modal.fade.in .modal-dialog {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            transform: scale(1);
            -webkit-transform: translate3d(0, -300px, 0);
            transform: translate3d(0, -300px, 0);
            opacity: 1;
        }

        input[readonly], span[readonly] {
            background-color: #fff !important;
        }

        .mc_loading {
            position: absolute;
            min-height: 500px;
            max-height: 750px;
            height: 100%;
            width: 100%;
            margin:0;
            margin-bottom: 20px;
            z-index: 1;
            display: none;
        }
        .mc_loading .bg {
            position: absolute;
            background-color: #FFF;
            opacity: .6;
            height: 100%;
            width: 100%;
        }
        .mc_loading img {
            width: 200px;
            position: absolute;
            top: 120px;
            left: 320px;
        }
        .cs_loading {
            position: absolute;
            min-height: 500px;
            max-height: 750px;
            height: 100%;
            width: 100%;
            margin:0;
            margin-bottom: 20px;
            z-index: 1;
            display: none;
        }
        .cs_loading img {
            width: 200px;
            position: absolute;
            top: 55px;
            left: 170px;
        }
        #datatable_accounts_filter{
            display: none;
        }
        #datatable_accounts_filter thead{
            display: none;
        }
        .pagination {
            padding-right: 20px;
        }
        .pagination li {
            display: inline-block;
            margin: 0;
        }
        .pagination li a {
            background-color: transparent;
            font-size: 20px;
            margin-top: -8px;
            color: #cccccc;
            border: none;
        }
        .pagination li a:hover {
            background-color: transparent;
            font-weight: bold;
        }
    </style>
    <!--<div class="tab-content">-->
    <!--<div class="tab-pane fade" id="accounts">-->
        <div class="container_12 hidden" id="accounts_display">

            <style>
                .table tbody tr {
                cursor: pointer;
                }
                .table tbody tr:hover {
                    background-color: #e6f1ff;
                }
                #accountsArea::-webkit-scrollbar { 
                    display: none; 
                }
                .glyphicon.glyphicon-briefcase {
                    font-size: 15px;
                }
            </style>
           

            <div class="grid_12 push_1 alpha accform" style="height: 500px; width: 93%; position: relative; margin-left: 20px; margin-top: -25px;">
                <?php
                    include "forms/accounts_form.php";
                ?>
            </div>
        </div>
        <div class="container_12 hidden" id="support_display">
          <style>
        #sumArea::-webkit-scrollbar { 
            display: none; 
        }
        #datatable_accounts_wrapper{ 
            width: 110%
        }
        #datatable_accounts_paginate{ 
            position: relative;
            right:100px;
        }
        </style>
            <?php
                    include "forms/support_form.php";
                ?>
        </div>
       
    <!--</div>-->

<!--<div class="tab-pane fade" id="account">-->

    <div class="modal fade" id="viewTicket" tabindex="-1" role="dialog">
        <div id="modal_dialog" class="modal-dialog modal-md" >
            <div class="modal-content">
                <span>
                          
                </span>
                <div class="modal-body">
                    <input type="type" id="cID" hidden>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Ticket Entry No.</label>
                            <input type="text" class="form-control tNO" id="tNo" value="" style="text-align: center; font-weight: bold;" readonly>
                        </div>
                        <div class="col-md-3 col-md-offset-3">
                            <label>Status:</label><br>
                            <span type="text" id="tSTAT" class="tSTAT" name="status" value="" style="text-align: center; font-weight: bold; color: green;" readonly> &nbsp &nbspACTIVE <br></span>
                        </div>
                        <div class="col-md-5">
                            <label style="display: none;">Ticket ID</label>
                            <input type="text" class="form-control" id="tID" value="" style="text-align: center; font-weight: bold; display: none;" readonly>
                        </div>


<style type="text/css">
    .text-right
    {
        margin-top: -4em;
    }
</style>


                        <div class="col-md-3 text-right">
                            <a id="expand" href="#"><span id="glyph_resize" class="btn btn-info btn-sm glyphicon glyphicon-resize-full " aria-hidden="true"></span></a>
                            <a id="close_modal" href="#"><span id="glyph_close" class="btn btn-danger btn-sm glyphicon glyphicon-remove " aria-hidden="true"></span></a>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Subject:</label>
                            <input type="text" class="form-control"  readonly>
                        </div>
                    </div>

                     <div class="row">
                        <div class="col-md-8">
                            <label>From:</label>
                            <input type="text" class="form-control mfrom"  readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Date:</label>
                            <input type="text" class="form-control mfrom"  readonly>
                        </div>
                    </div>
                    <style>
                        #attID {
                            background-image: url('img/35.gif');
                            background-color: #f2f2f2;
                            background-repeat: no-repeat;
                            background-size: 50px;
                            background-position: center; 
                            border: 1 solid #fff2e6;
                            height: 200px;
                            box-shadow: 9px 9px 10px #818181;
                            -webkit-box-shadow: 9px 9px 10px #818181;
                            -moz-box-shadow: 9px 9px 10px #818181;
                            /*border-radius: 5px;*/
                            cursor: pointer;
                            transition: 0.3s;
                        }

                        #attID:hover {
                            -webkit-filter: brightness(50%);
                        }
                    </style>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Message</label>
                            <div id="tBody" class="form-control" readonly style="overflow:auto;height:300px; background-color: #fff;">
                                <span id="tMsg" style="height: auto;" readonly></span> 
                                <span id="tMsgAtt"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <center>
                            <a href="#" class="btn btn-danger open-modal-updTicket">Update Ticket</a>
                            <button class="btn btn-danger" onclick="gotoCustomerPage()">Go to Customer Page</button>
                        </center>
                    </div>
                    <div class="row">
                            <style>
                                #magic_buttons button {
                                    background-color: #e6e6e6;
                                    width: 100%;
                                    text-align: left;
                                }
                                #magic_buttons button:hover {
                                    background-color: #ffffff;
                                }
                                #magic_buttons p {
                                    background-color: #f2f2f2;
                                    padding: 5px;
                                }
                                #lbl_th span {
                                    background-color: #f2f2f2;
                                    padding: 5px;
                                }
                            </style>

                            <style type="text/css">
                                
                                .btn-lg
                                {
                                    background-color: rgba(0,0,0,1);
                                }
                            </style>

                        <label class="col-md-12">Thread(s)</label>
                        <div id="lbl_th" class="col-md-12">
                        </div>
                        <div id="magic_buttons" class="col-md-12">
                        </div>
                    </div>
                </div>
        
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateTicket" tabindex="-1" role="dialog">
        <div id="modal_cont" class="modal-dialog modal-md">
            <div class="modal-content">
                                    
                <div class="modal-body">
                    <form method="POST">
                        <input type="type" id="cID_new_thread" name="cTID" hidden>
                        <input type="type" id="curr_status" name="curr_status" hidden>
                      

                        <div class="row">
                            <div class=" col-md-12  email_opt_top">
                                 
          
                             <a id="reply" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-pencil"></span> Reply
                            </a>

                             <a id="note" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-comment"></span> Add Note
                            </a>

                             <a id="assign" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-user"></span> Assign
                            </a>

                             <a id="status" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-flag"></span> Status
                            </a>

                             <a id="tag" href="#" class="btn btn-primary btn-md">
                                <span class="glyphicon glyphicon-tags"></span> Tag
                            </a>

                              <a id="tag" href="#" class="btn btn-primary btn-md" style="display: none;">
                                <span class="glyphicon glyphicon-trash"></span> Delete
                            </a>


                            </div>
                       </div>


                        <div class="row">
                            <div class="col-md-12">
                                <label>Message</label>
                                <textarea class="form-control" id="commit_msg" name="message" style="height: 300px;"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <center>
                                <input type="Submit" class="btn btn-danger" name="new_thread" value="Create Thread">
                            </center>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewAtt" tabindex="-1" role="dialog" style="margin-left: -5em;">
        <div id="modal_cont" class="modal-dialog">
            <div class="modal-content" style="position: absolute; margin-left: auto; margin-right: auto; width: 0; height: 0; border: none; padding: none;">
                                    
                <div class="modal-body">

                   
                </div>
                    <div id="attid"></div>
                     <span id="attfn" style="text-align:center !important; margin-top: 0em !important; background-color: transparent; font-size: 15px; color: #fff;" ></span>
                    

        
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewTwilio" tabindex="-1" role="dialog">
        <div id="modal_cont" class="modal-dialog">
            <div class="modal-content" style="padding:2em">
                <div class="cs_loading">
                    <img src="img/loader1.gif">
                </div>       
                <div class="modal-body">
                    <form method='POST'>
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control" onChange="setTemplate(this);" id='template' name='template'>
                                    <option disabled="disabled" selected="true">Template</option>
                                    <option value="1">Say Hi</option>
                                    <option value="2">Say Thank you</option>
                                    <option value="3">How are you?</option>
                                    <option value="0">--clear--</option>
                            </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <textarea class="form-control" id="textMessage" name="textMessage" style="background-color:#F6F9F4;height:10em"></textarea>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                            <select class="form-control" id='select' name='select'>
                                <option value='+639262386282'>Jas</option>
                                <option value='+639262386282'>Jes</option>
                                <option value='+639262386282'>Gem</option>
                                <option value='+639262386282'>Beth</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="submit" class="form-control btn btn-success" name="send" value="send" onclick="callNtext();">
                            <!--<button type="submit" class="btn btn-success" name="send"><span class="glyphicon glyphicon-send" aria-hidden="true"></span> Send</button>-->
                        </div>
                        <div class="col-md-3">
                            <input type="submit" class="form-control btn btn-info" name="call" value="call" onclick="callNtext();">
                            <!--<button type="submit" class="btn btn-info" name="call"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span> Call</button>-->
                        </div>
                        </div>
                    </form>
                   
                </div>
                    

        
            </div>
        </div>
    </div>

    <div class="modal fade" id="success_modal" tabindex="-1" role="dialog">
        <div id="modal_cont" class="modal-dialog">
            <div class="modal-content" style="padding:2em">
                     
                <div class="modal-body">
                    Success !
                   
                </div>
                    

        
            </div>
        </div>
    </div>
    <div class="container_12 boxsummary hidden" style="left: 150px;" id="tickets_display">  

<style>
       .imgatt1
       {
           position: relative; 
           display: inline-block;
           padding: 0.5em;
       }
      .imgatt1 button {
          position: absolute; 
          width: 35px; 
          height: 35px; 
          top: 75%; 
          left: 78%; 
          background: transparent; 
          background-image: url(img/down_icon.png); 
          background-size: 100%; 
         border: none;
       }
       .att_title
       {
           position: absolute; 
           background: rgb(0, 0, 0); 
           background: rgba(0, 0, 0, 0.7); 
           width: 200px; 
           height: 200px; 
           display: none; 
           color: #ffffff ; 
           font-weight: bold; 
           padding: 5px; 
           word-wrap: break-word; 
           cursor: zoom-in;
       }
       .imgatt2
       {
           width: 200px; 
           height: 200px; 
           margin-bottom:25px;

       }
  </style>

    <!--
    <div id="attModal" class="preview-modal">
        <span class="preview-close">×</span>
        <img class="preview-modal-content" id="img01">
        <div id="caption"></div>
    </div>
    -->
    <div class="full-width-div">        
        <div class="container_12" style="margin-top:0em;">
            <div id="boxesSum" class="row text-center">
               
                <div class="grid_2 push_1 alpha ticketbutton" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #A60800 2px;color:#A60800"><a href="#" onclick="return addTicket();"><strong>Ticket</strong></a></div>
                <div class="grid_2 push_1 omega twiliobutton" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #340570 2px;color:#340570"><a href="#" onclick="return showTwilio();"><strong>Twilio</strong></a></div>
        
            </div>
        </div>
    </div>
    <div class="container_12">

        <style>
        #sumArea::-webkit-scrollbar { 
            display: none; 
        }
        </style>

        <div id="sumArea" class="grid_5 alpha" style="overflow-y: scroll; overflow-x: hidden; height: 550px; ">

        <?php 
            foreach($arr_msgs as $a_m) { 
                $mID = $a_m['ticket_id'];
                $tNo = $a_m['no'];
                $sts = $a_m['status'];
                $sbj = $a_m['subject'];
                $bdy = htmlentities($a_m['body']);

                if($a_m['notes']) {
                    $th_arr_fin = "";
                    $th_arr = array();
                    foreach($a_m['notes'] as $nl) {
                        array_push($th_arr, "<i><b>".$nl['n_created_by']['S']."</b></i> added note||+||<span style='float: right;'>".$nl['n_created_at']['S']."</span>||+||<p>".$nl['n_content']['S']."</p>~^^^~");
                    }

                    $thArrCnt = 0;
                    while(!empty($th_arr[$thArrCnt])) {
                        $th_arr_fin .= $th_arr[$thArrCnt];
                        $thArrCnt++;
                    }
                } else {
                    $th_arr_fin = "";
                }

                $ats_title = "";
                $ats = "";
                if($a_m['attachments']) {
                    $ats_title = "<br/><b>Attachments</b><br/><br/>";
                }
                foreach($a_m['attachments'] as $am_ats) {
                    $ats .= htmlentities($am_ats);
                }
                $em_cnt=0;
                while(!empty($em_check[$em_cnt])) {
                    if($a_m['email'] == $em_check[$em_cnt]['email']) {
                        $cID = $em_check[$em_cnt]['id'];
                        $bn = $em_check[$em_cnt]['bname'];
                        $fn = $em_check[$em_cnt]['fname'];
                        $ln = $em_check[$em_cnt]['lname'];
                        $bp = $em_check[$em_cnt]['bphone'];
        ?>
                        <div class="container_12">
                           
                            <div class="grid_5 ticketsummary">
                                <div class="grid_1 alpha round-div">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <div class="grid_2 omega ticketlist">
                                    <a href="#" class="open-modal" data-cid="<?=$cID?>" data-id="<?=$mID?>" data-no="<?=$tNo?>" data-status="<?=$sts?>" data-subject="<?=$sbj?>" data-mes="<?=$bdy?>" data-atturl="<?=$ats_title.$ats?>" data-threadmsg="<?=$th_arr_fin?>">
                                    <strong><?php echo $bn; ?></strong></a> <br>
                                    <?php
                                        echo $fn." ".$ln."<br>".
                                             $bp."<br>".
                                             $cID;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <br/>
        <?php 
                    }
                    $em_cnt++;
                }
            } 
        ?>

        </div>
    </div>
   </div>
   <!--<div> <!-- tickets end -->
<!--</div> <!-- tab-content end -->
<?php
    require "footer.php";
?>

<script>
    $(document).ready(function() {
        if($.cookie('last_tab') == '#accountsFromSup') {
            $("#tickets_display").addClass('hidden');
            $("#accounts_display").removeClass('hidden');
            $.cookie('last_tab', '#account');
            $('#accounts_tab').addClass('active');
        } else {
            $("#tickets_display").removeClass('hidden');
            $('#accounts_tab').removeClass('active');
            $.cookie('last_tab', '#');
        }
        
        $('#viewTicket').on('hidden.bs.modal', function (e) {
            $("#magic_buttons").empty();
            $("#lbl_th").empty();
            $("#id_you_like_div_none").empty();
        })

        $('#viewTicket').on('shown.bs.modal', function () {
            $('#tBody').scrollTop(0);
        })

        $('#previewAtt').on('hidden.bs.modal', function (e) {
            $("#attid").empty();
        })

        $('#updateTicket').on('hidden.bs.modal', function (e) {
            $('#rad1').removeAttr('checked');
            $('#rad2').removeAttr('checked');
            document.getElementById("commit_status").value = "";
            $('#commit_subj').prop('disabled', false);
            document.getElementById("commit_subj").value = "";
            document.getElementById("commit_msg").value = "";
        })


        //=====support======
        $('#datatable_unassigned').DataTable({
      "bPaginate": true,
      "pagingType": "full",
      "language": {
        "paginate": {
          "first": "&lt;&lt;",
          "previous": "&lt;",
          "next": "&gt;",
          "last": "&gt;&gt;"
        },
        "sInfo": "_TOTAL_ total <b>unassigned</b> tickets | Viewing <b>_START_</b> - <b>_END_</b>",
        "sEmptyTable": "Amazing! All tickets were all handled, let's wait for new ones."
      },
      "lengthChange": false,
      "bFilter": true, 
      "bInfo": true,
      "order": [3, 'asc'],
      "columnDefs": [ {
        "targets"  : [0,2],
        "orderable": false,
      }],
      "scrollY": "200px"
    });
    $('#datatable_mine').DataTable({
      "bPaginate": true,
      "pagingType": "full",
      "language": {
        "paginate": {
          "first": "&lt;&lt;",
          "previous": "&lt;",
          "next": "&gt;",
          "last": "&gt;&gt;"
        },
        "sInfo": "_TOTAL_ total <b>mine</b> tickets | Viewing <b>_START_</b> - <b>_END_</b>",
        "sEmptyTable": "Chill out and relax. No tickets assigned to you."
      },
      "lengthChange": false,
      "bFilter": true, 
      "bInfo": true,
      "order": [3, 'asc'],
      "columnDefs": [ {
        "targets"  : [0,2],
        "orderable": false,
      }]
    });
    $('#datatable_assigned').DataTable({
      "bPaginate": true,
      "pagingType": "full",
      "language": {
        "paginate": {
          "first": "&lt;&lt;",
          "previous": "&lt;",
          "next": "&gt;",
          "last": "&gt;&gt;"
        },
        "sInfo": "_TOTAL_ total <b>assigned</b> tickets | Viewing <b>_START_</b> - <b>_END_</b>",
        "sEmptyTable": "Hurray! The team resolved all tickets that were assigned to them."
      },
      "lengthChange": false,
      "bFilter": true, 
      "bInfo": true,
      "order": [4, 'asc'],
      "columnDefs": [ {
        "targets"  : [0,2],
        "orderable": false,
      }]
    });
    $('#datatable_closed').DataTable({
      "bPaginate": true,
      "pagingType": "full",
      "language": {
        "paginate": {
          "first": "&lt;&lt;",
          "previous": "&lt;",
          "next": "&gt;",
          "last": "&gt;&gt;"
        },
        "sInfo": "_TOTAL_ total <b>closed</b> tickets | Viewing <b>_START_</b> - <b>_END_</b>"
      },
      "lengthChange": false,
      "bFilter": true, 
      "bInfo": true,
      "order": [3, 'desc'],
      "columnDefs": [ {
        "targets"  : [0,2],
        "orderable": false,
      }]
    });
    $('#datatable_spam').DataTable({
      "bPaginate": true,
      "pagingType": "full",
      "language": {
        "paginate": {
          "first": "&lt;&lt;",
          "previous": "&lt;",
          "next": "&gt;",
          "last": "&gt;&gt;"
        },
        "sInfo": "_TOTAL_ total <b>spam</b> tickets | Viewing <b>_START_</b> - <b>_END_</b>",
        "sEmptyTable": "<b>URSA</b> don't like spam emails! <b>URSA</b> thrown it all."
      },
      "lengthChange": false,
      "bFilter": true, 
      "bInfo": true,
      "order": [3, 'desc'],
      "columnDefs": [ {
        "targets"  : [0,2],
        "orderable": false,
      }]
    });


        $('#datatable_accounts').DataTable({
            "bDestroy": true,
           "bPaginate": true,
                "pagingType": "full",
                "language": {
                    "paginate": {
                        "first": "&lt;&lt;",
                        "previous": "&lt;",
                        "next": "&gt;",
                        "last": "&gt;&gt;"
                    },
                    "sInfo": "_TOTAL_ total <b>account(s)</b> | Viewing <b>_START_</b> - <b>_END_</b>"
                },
                "lengthChange": false,
                "bFilter": true, 
                "bInfo": true,
                "bSort": false,
                "scrollY": "300px",
        }); 

        $('.dataTables_scrollHeadInner').css('width', '900px');
    });
    $('#search').on( 'keyup', function () {
        var table = $('#datatable_accounts').DataTable();
        table.search( this.value ).draw();
    });
    function showTitle(x) {
        var targetDiv = x.getElementsByClassName("att_title")[0];
        targetDiv.style.display = "block";
    }

    function hideTitle(x) {
        var targetDiv = x.getElementsByClassName("att_title")[0];
        targetDiv.style.display = "none";
    }
    
    function testClick(val) {
        if(document.getElementById('id_you_like_div_'+val).style.display == "block") {
            document.getElementById('id_you_like_div_'+val).style.display = "none";
        } else {
            document.getElementById('id_you_like_div_'+val).style.display = "block";
        }
    }

    $(document).on("click", ".open-modal", function (e) {
        e.preventDefault();
        var _self = $(this);
            tID = _self.data('id'),
            tNo = _self.data('no'),
            tSts = _self.data('status'),
            tSubj = _self.data('subject'),
            tMsg = _self.data('mes'),
            tMsgAtt = _self.data('atturl'),
            cID = _self.data('cid'),
            threads = _self.data('threadmsg');
        $("#tID").val(tID);
        $("#tNo").val(tNo);
        $("#tSubj").val(tSubj);
        $("#tMsg").html(tMsg);
        $("#tMsgAtt").html(tMsgAtt);
        $("#cID").val(cID);
        $("#commit_status").val(tSts);
        $("#curr_status").val(tSts);

        if(threads) {
            fields = threads.split("~^^^~");

            var i = 0;
            while(fields[i]) {
                field_type = fields[i].split("||+||");
                var btn = document.createElement("BUTTON");
                btn.setAttribute("id", "id_you_like_"+i);
                btn.setAttribute("class", "form-control");
                btn.setAttribute("onclick", "testClick("+i+")");
                document.getElementById('magic_buttons').appendChild(btn);
                document.getElementById('id_you_like_'+i).innerHTML = field_type[0] + field_type[1];
                var current = document.getElementById('id_you_like_'+i);
                var el = document.createElement("SPAN");
                el.setAttribute("id", "id_you_like_div_"+i);
                el.setAttribute("style", "display: none");
                insertAfter(current, el);
                document.getElementById('id_you_like_div_'+i).innerHTML = field_type[2];
                var element = document.getElementById("magic_buttons");
                i++;
            }
        } else {
            var no = document.createElement("SPAN");
                no.setAttribute("id", "id_you_like_div_none");
                no.setAttribute("class", "col-md-12");
                document.getElementById('lbl_th').appendChild(no);
                document.getElementById('id_you_like_div_none').innerHTML = "<span>No thread(s) found.</span";
        }

        $("#viewTicket").modal('show');
    });

   
   $(document).on("click", ".open-modal-previewAtt", function (e) {
      var _self = $(this);
            src = _self.data('src'),
            fn = _self.data('fn');
        var img = document.createElement("img");
        img.src= src;
        document.getElementById('attid').appendChild(img);
        document.getElementById('attfn').innerHTML = fn;
        $("#previewAtt").modal('show');
         
         if($('#modal_dialog').hasClass('modal-lg')){
        
         img.style.width = '650px';
         img.style.height = '450px';
         $('#previewAtt').css('margin-top','-30px');
         //$('#previewAtt').css('margin-left','-350px');
         $('#attfn').css('text-align','center');


     }

        else{
         img.style.width = '650px';
         img.style.height = '450px';
         $('#previewAtt').css('margin-top','-30px');
         //$('#previewAtt').css('margin-left','-50px');
         $('#attfn').css('text-align','center');
     }

    });




    $(document).on("click", ".open-modal-updTicket", function (e) {
        document.getElementById('cID_new_thread').value = document.getElementById('tID').value;
        document.getElementById('cID_new_thread').value = document.getElementById('tID').value;
        $("#updateTicket").modal('show');
    });

    function gotoCustomerPage() {
        var cID = document.getElementById('cID').value;
        var tID = document.getElementById('tID').value;
        window.open('customer?id='+cID+'&ticket_id='+tID);
    }

    function tType(tVal) {
        if(tVal == 1) {
            $('#commit_subj').prop('disabled', true);
        } else {
            $('#commit_subj').prop('disabled', false);
        }
    }

    function insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }
    $('#expand').click(function(){


        if($('#modal_dialog').hasClass('modal-md')){

            $('#glyph_resize').removeClass('glyphicon-resize-full');
            $('#glyph_resize').addClass('glyphicon-resize-small');
            $('#modal_dialog').removeClass('modal-md');
            $('#modal_cont').removeClass('modal-md');
            $('#modal_dialog').addClass('modal-lg');
            $('#modal_cont').addClass('modal-lg');
           
            $('.imgatt2').removeClass('modal-md'); 
            $('.imgatt2').css({'background-color': 'transparent', 'width': '250px', 'height': '250px', 'margin': '1em', 'display': 'inline-block'}).addClass('modal-lg'); 
            $('.att_title').removeClass('modal-md'); 
            $('.att_title').css({ 'width': '250px', 'height': '250px'}).addClass('modal-lg'); 
            $('a button').removeClass('modal-md'); 
            $('a button').css({ 'width': '50px', 'height': '50px'}).addClass('modal-lg'); 
           //$('.imgatt1').css({ 'width': '250px', 'height': '250px'}).addClass('modal-lg'); 


        }else{
            $('#glyph_resize').removeClass('glyphicon-resize-small');
            $('#glyph_resize').addClass('glyphicon-resize-full');
            $('#modal_dialog').removeClass('modal-lg');
            $('#modal_cont').removeClass('modal-lg');
            $('#modal_dialog').addClass('modal-md');
            $('#modal_cont').addClass('modal-md');
            
            $('.imgatt2').removeClass('modal-lg'); 
            $('.imgatt2').css({'background-color': 'transparent', 'width': '200px', 'height': '200px', 'margin': '1em', 'display': 'inline-block'}).addClass('modal-md'); 
            $('.att_title').removeClass('modal-lg'); 
            $('.att_title').css({ 'width': '200px', 'height': '200px'}).addClass('modal-md'); 
            $('a button').removeClass('modal-lg'); 
            $('a button').css({ 'width': '35px', 'height': '35px'}).addClass('modal-md'); 
         }
    });

$('#close_modal').click(function() {
    $('#viewTicket').modal('hide');
});

function oneAccount(accountid) {
    $('.mc_loading').css("display", "block");
    $.cookie('last_tab', '#account');
    window.location.href = 'customer?id=' + accountid;
}
</script>
<!-- SUPPORT2 -->
<script>


var current_folder_list = "";
        var current_datatable = "";
        $(document).on('change', current_datatable+' .chckbx_all', function() {
            if($(current_datatable+' .chckbx_all').is(':checked')) {
                $(current_datatable+' .chckbx').prop('checked', true);
                $('.btngrpChckBxs').css("display", "block");
            } else {
                $(current_datatable+' .chckbx').prop('checked', false);
                $('.btngrpChckBxs').css("display", "none");
            }
        });

        $(document).on('change', current_datatable+' .chckbx', function() {
            if($(current_datatable+' .chckbx').is(':checked')) {
                $('.btngrpChckBxs').css("display", "block");
            } else {
                $('.btngrpChckBxs').css("display", "none");
            }
        });

        $(current_folder_list+' .btnAssignTo').on({
            mouseenter: function () {
                $(current_folder_list+' .ttAssignTo').fadeIn(100);
                $(current_folder_list+' .ttAssignTo').css("display", "block");

            },
            mouseleave: function () {
                $(current_folder_list+' .ttAssignTo').css("display", "none");
            }
        });

        $(current_folder_list+' .btnStatus').on({
            mouseenter: function () {
                $(current_folder_list+' .ttStatus').fadeIn(100);
                $(current_folder_list+' .ttStatus').css("display", "block");
            },
            mouseleave: function () {
                $(current_folder_list+' .ttStatus').css("display", "none");
            }
        });

        $(current_folder_list+' .btnTag').on({
            mouseenter: function () {
                $(current_folder_list+' .ttTag').fadeIn(100);
                $(current_folder_list+' .ttTag').css("display", "block");
            },
            mouseleave: function () {
                $(current_folder_list+' .ttTag').css("display", "none");
            }
        });

  $(document).on('click', '.sorting', function () {
            activeFolder();
        });

        $(document).on('click', '.sorting_asc', function () {
            activeFolder();
        });

        $(document).on('click', '.sorting_desc', function () {
            activeFolder();
        });

        $(document).on('click', '.paginate_button', function () {
            activeFolder();
        });

  function openFolder(folder, tickets, table) {
    $('.chckbx').prop('checked', false);
            $('.chckbx_all').prop('checked', false);
            $('.btngrpChckBxs').css("display", "none");
    $('#list_unassigned').addClass('folder_list_hide');
    $('#list_mine').addClass('folder_list_hide');
    $('#list_assigned').addClass('folder_list_hide');
    $('#list_closed').addClass('folder_list_hide');
    $('#list_spam').addClass('folder_list_hide');

    $(table+'_filter input').val(null);
            $(table+'_filter input').trigger("keyup");

            $('.paginate_button').css("display", "none");
            if($(table+'_first').hasClass("disabled")) {
                $(table+'_first').css("display", "none");
                $(table+'_previous').css("display", "none");
            } else {
                $(table+'_first').css("display", "inline-block");
                $(table+'_previous').css("display", "inline-block");
            }

            if($(table+'_next').hasClass("disabled")) {
                $(table+'_next').css("display", "none");
                $(table+'_last').css("display", "none");
            } else {
                $(table+'_next').css("display", "inline-block");
                $(table+'_last').css("display", "inline-block");
            }

            var rows  = tickets;

            if(rows == 0) {
                $(table+'_filter input').attr("readonly","true");
                $(table+'_filter input').focus( function() {
                   $(this).css("border","1px solid #ccc");
                   $(this).css("outline","none");
                   $(this).css("box-shadow","none");
                });
                $(table+' .sorting').off();
                $(table+' .sorting_asc').off();
                $(table+' .sorting_desc').off();
                $(table+' .chckbx_all').attr("disabled", true);
            } else {
                $(table+' .sorting').on();
                $(table+' .sorting_asc').on();
                $(table+' .sorting_desc').on();
            }

            if(rows > 10) {
                $(table+'_paginate').css("display", "block");
                $(table+'_info').css("display", "block");
            } else {
                $(table+'_paginate').css("display", "none");
                $(table+'_info').css("display", "none");
            }

    if(folder == 1) {
      $('#list_unassigned').removeClass('folder_list_hide');
      current_folder_list = "#list_unassigned";
      current_datatable = "#datatable_unassigned";
    } else if(folder == 2) {
      $('#list_mine').removeClass('folder_list_hide');
      current_folder_list = "#list_mine";
      current_datatable = "#datatable_mine";
    } else if(folder == 3) {
      $('#list_assigned').removeClass('folder_list_hide');
      current_folder_list = "#list_assigned";
      current_datatable = "#datatable_assigned";
    } else if(folder == 4) {
      $('#list_closed').removeClass('folder_list_hide');
      current_folder_list = "#list_closed";
      current_datatable = "#datatable_closed";
    } else {
      $('#list_spam').removeClass('folder_list_hide');
      current_folder_list = "#list_spam";
      current_datatable = "#datatable_spam";
    }
  }

  function activeFolder() {
            var activeFolderNow = "";
            if(!$('#list_unassigned').hasClass("folder_list_hide")) {
                activeFolderNow = "#datatable_unassigned";
            }else if(!$('#list_mine').hasClass("folder_list_hide")) {
                activeFolderNow = "#datatable_mine";
            }else if(!$('#list_assigned').hasClass("folder_list_hide")) {
                activeFolderNow = "#datatable_assigned";
            }else if(!$('#list_closed').hasClass("folder_list_hide")) {
                activeFolderNow = "#datatable_closed";
            }else{
                activeFolderNow = "#datatable_spam";
            }

            $('.paginate_button').css("display", "none");
            if($(activeFolderNow+'_first').hasClass("disabled")) {
                $(activeFolderNow+'_first').css("display", "none");
                $(activeFolderNow+'_previous').css("display", "none");
            } else {
                $(activeFolderNow+'_first').css("display", "inline-block");
                $(activeFolderNow+'_previous').css("display", "inline-block");
            }

            if($(activeFolderNow+'_next').hasClass("disabled")) {
                $(activeFolderNow+'_next').css("display", "none");
                $(activeFolderNow+'_last').css("display", "none");
            } else {
                $(activeFolderNow+'_next').css("display", "inline-block");
                $(activeFolderNow+'_last').css("display", "inline-block");
            }

            if($(activeFolderNow).dataTable().fnSettings().aoData.length == 0) {
                $(activeFolderNow+' .sorting').off();
                $(activeFolderNow+' .sorting_asc').off();
                $(activeFolderNow+' .sorting_desc').off();
                
            } else {
                $(activeFolderNow+' .sorting').on();
                $(activeFolderNow+' .sorting_asc').on();
                $(activeFolderNow+' .sorting_desc').on();
            }
        }

  function checkedboxes() {
            var arrOfchckbxs_chckd = [];
            $(current_datatable+" .chckbx").each(function(){
                var id = $(this).attr('id');
                var $this = $(this);

                if($this.is(':checked')) {
                    arrOfchckbxs_chckd.push($this.attr("id"))
                }
            });
            alert(arrOfchckbxs_chckd);
        }

  function getTicketData(id) {
    $('#viewTicket').modal('show'); 
  }

//=============Twilio SMS

            test0 = "";
            test1 = "Hi there";
            test2 = "Thanks mate";
            test3 = "How you doin' buddy?"
            function callNtext() {
                $('.cs_loading').css("display", "block");
            }
            function showTwilio(){
                $("#viewTwilio").modal('show');
            }
            function setTemplate(t) {
                var otionValue = t.value;
                if (otionValue == "0") {
                  document.getElementById('textMessage').innerHTML = test0;
                } else if (otionValue == "1")
                  document.getElementById('textMessage').innerHTML = test1;
                  else if (otionValue == "2")
              document.getElementById('textMessage').innerHTML = test2;
          else if (otionValue == "3")
              document.getElementById('textMessage').innerHTML = test3;
            }; 

</script>