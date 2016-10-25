<?php
  require 'header.php';
  require_once 'liveServer.php';
  require_once 'dynamoDB/dbConnect.php';
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

  function decodeBody($body) {
    $rawData = $body;
    $sanitizedData = strtr($rawData,'-_', '+/');
    $decodedMessage = base64_decode($sanitizedData);
    if(!$decodedMessage){
      $decodedMessage = FALSE;
    }
    return $decodedMessage;
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
</style>

<style type="text/css">
  
  .text-right
  {
    top: -12em !important;
    left: 15em !important;
  }
</style>

<div class="modal fade" id="viewTicket" tabindex="-1" role="dialog">
        <div id="modal_dialog" class="modal-dialog modal-md" >
            <div class="modal-content">
                <span>
                          
                </span>
                <div class="modal-body">

<!--action buttons-->
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


<style type="text/css">
  
a #unassigned:hover
{
  background-color: #26B68E;
  color: white !important;
}


a #mine:hover
{
  background-color: #EAE17F;
  color: white !important;
}


a #assigned:hover
{
  background-color: #348DC0;
  color: white !important;
}


a #closed:hover
{
  background-color: #323232;
  color: white !important;
}


a #spam:hover
{
  background-color: #C63C33;
  color: white !important;
}

table thead
{
  background-color: rgba(52,141,192, 0.8);
}

.dataTables_wrapper
{
  margin-top: -1em !important;
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

<div class="full-width-div">
  <div class="container_12">
    <div class="grid_12 push_1 boxsupport">
      <div id="boxesSup" class="row text-center">
        <a href="#unassigned" onclick="openFolder(1, <?=$unassigned?>, '#datatable_unassigned')">
          <div class="grid_2" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #26B68E 2px;color:#26B68E;">
            <i class="glyphicon glyphicon-envelope"></i> &nbsp<strong>Unassigned</strong>
          </div>
        </a>
        <a href="#mine" onclick="openFolder(2, <?=$mine?>, '#datatable_mine')">
          <div class="grid_2" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #EAE17F 2px;color:#EAE17F">
            <i class="glyphicon glyphicon-inbox"></i>&nbsp<strong>Mine</strong>
          </div>
        </a>
        <a href="#assigned"  onclick="openFolder(3, <?=$assigned?>, '#datatable_assigned')">
          <div class="grid_2" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #348DC0 2px;color:#348DC0">
            <i class="glyphicon glyphicon-user"></i>&nbsp<strong>Assigned</strong>
          </div>
        </a>
        <a href="#closed">
          <div onclick="openFolder(4, <?=$closed?>, '#datatable_closed')" class="grid_2" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #323232 2px;color:#323232">
            <i class="glyphicon glyphicon-trash"></i>&nbsp<strong>Closed</strong>
          </div>
        </a>
        <a href="#spam">
          <div onclick="openFolder(5, <?=$spam?>, '#datatable_spam')" class="grid_2" style="padding: 1em;margin-right:2.78em; margin-bottom:1em;border:solid #C63C33 2px;color:#C63C33">
           <i class="glyphicon glyphicon-ban-circle"></i>&nbsp <strong>Spam</strong>
          </div>
        </a>
      </div>
    </div>
  </div>

<!--for the tickets-->

    <div class="container_12">

    	<div class="grid_1 push_2 alpha ticketsummary2">
        <?php 
        function divChckBxs() {
            ?>
                <ul class="btngrpChckBxs">
                    <li class='btnAssignTo' onclick="checkedboxes()"><i class="glyphicon glyphicon-user"></i></li>
                    <li class='btnStatus' onclick="checkedboxes()"><i class="glyphicon glyphicon-flag"></i></li>
                    <li class='btnTag' onclick="checkedboxes()"><i class="glyphicon glyphicon-tag"></i></li>
                    <span class="ttAssignTo">Assign</span>
                    <span class="ttStatus">Status</span>
                    <span class="ttTag">Tag</span>
                </ul>
            <?php
        }
    ?>

    	 <div id="list_unassigned" class="folder_list_hide">
          <?php divChckBxs(); ?>             
          <table id="datatable_unassigned" class="table">
            <thead>
              <tr>
                <th><input type="checkbox" class="chckbx_all"></th>
                <th title="Sort">Customer</th>
                <th>Conversation</th>
                <th title="Sort">Number</th>
                <th title="Sort">Last Update</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($arr_unassigned as $aUN) { ?>
              <tr>
                <td><input type="checkbox" id="chckbxid<?=$aUN['no']?>" class="chckbx"></td>
                <td onclick="getTicketData('<?=$aUN["ticket_id"]?>')"><?=$aUN['from']?></td>
                <td onclick="getTicketData('<?=$aUN["ticket_id"]?>')">
                                            <div class="table_email_content">
                                                <b><?=$aUN['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aUN['body'])?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$aUN["ticket_id"]?>')"><?=$aUN['no']?></td>
                                        <td onclick="getTicketData('<?=$aUN["ticket_id"]?>')"><?=$aUN['updated']?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="list_mine" class="folder_list_hide">
                            <?php divChckBxs(); ?>
                            <table id="datatable_mine" class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="chckbx_all"></th>
                                        <th title="Sort">Customer</th>
                                        <th>Conversation</th>
                                        <th title="Sort">Number</th>
                                        <th title="Sort">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($arr_mine as $aM) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$aM['no']?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$aM["ticket_id"]?>')"><?=$aM['from']?></td>
                                        <td onclick="getTicketData('<?=$aM["ticket_id"]?>')">
                                            <div class="table_email_content">
                                                <b><?=$aM['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aM['body'])?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$aM["ticket_id"]?>')"><?=$aM['no']?></td>
                                        <td onclick="getTicketData('<?=$aM["ticket_id"]?>')"><?=$aM['updated']?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="list_assigned" class="folder_list_hide">
                            <?php divChckBxs(); ?>
                            <table id="datatable_assigned" class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="chckbx_all"></th>
                                        <th title="Sort">Customer</th>
                                        <th>Conversation</th>
                                        <th title="Sort">Assigned To</th>
                                        <th title="Sort">Number</th>
                                        <th title="Sort">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($arr_assigned as $aAS) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$aAS['no']?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$aAS["ticket_id"]?>')"><?=$aAS['from']?></td>
                                        <td onclick="getTicketData('<?=$aAS["ticket_id"]?>')">
                                            <div class="table_email_content">
                                                <b><?=$aAS['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aAS['body'])?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$aAS["ticket_id"]?>')"><?=$aAS['assigned']?></td>
                                        <td onclick="getTicketData('<?=$aAS["ticket_id"]?>')"><?=$aAS['no']?></td>
                                        <td onclick="getTicketData('<?=$aAS["ticket_id"]?>')"><?=$aAS['updated']?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="list_closed" class="folder_list_hide">
                            <?php divChckBxs(); ?>
                            <table id="datatable_closed" class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="chckbx_all"></th>
                                        <th title="Sort">Customer</th>
                                        <th>Conversation</th>
                                        <th title="Sort">Number</th>
                                        <th title="Sort">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($arr_closed as $aC) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$aC['no']?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$aC["ticket_id"]?>')"><?=$aC['from']?></td>
                                        <td onclick="getTicketData('<?=$aC["ticket_id"]?>')">
                                            <div class="table_email_content">
                                                <b><?=$aC['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aC['body'])?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$aC["ticket_id"]?>')"><?=$aC['no']?></td>
                                        <td onclick="getTicketData('<?=$aC["ticket_id"]?>')"><?=$aC['updated']?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="list_spam" class="folder_list_hide">
                            <?php divChckBxs(); ?>
                            <table id="datatable_spam" class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="chckbx_all"></th>
                                        <th title="Sort">Customer</th>
                                        <th>Conversation</th>
                                        <th title="Sort">Number</th>
                                        <th title="Sort">Last Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($arr_spam as $aS) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$aS['no']?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$aS["ticket_id"]?>')"><?=$aS['from']?></td>
                                        <td onclick="getTicketData('<?=$aS["ticket_id"]?>')">
                                            <div class="table_email_content">
                                                <b><?=$aS['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aS['body'])?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$aS["ticket_id"]?>')"><?=$aS['no']?></td>
                                        <td onclick="getTicketData('<?=$aS["ticket_id"]?>')"><?=$aS['updated']?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>


    	</div>


    </div>




</div>


<?php
  require "footer.php";
?>

<script>
  $(document).ready(function() {
    $('#datatable_unassigned').DataTable({
      "bPaginate": true,
      "pagingType": "full_numbers",
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
      }]
    });
    $('#datatable_mine').DataTable({
      "bPaginate": true,
      "pagingType": "full_numbers",
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
      "pagingType": "full_numbers",
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
      "pagingType": "full_numbers",
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
      "pagingType": "full_numbers",
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
  });

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
</script>
