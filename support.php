<?php
    require 'header.php';
    require_once 'liveServer.php';

    $test = false;
    if($test) {
        $unassigned = 0;
        $mine = 0;
        $assigned = 0;
        $closed = 0;
        $spam = 0;

        $ticket_id = "20161011152852722141";
        $customer_name = "John Doe";
        $email_subject = "Lorem Ipsum";
        $email_body = "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).";
        $ticket_number = 26191;
        $ticket_updated_at = "10/29/2016";
        $assigned_to = " Agent";
        $attach = "[attachments_here]";
    } else {
        require_once 'dynamoDB/dbConnect.php';

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
    }
?>
   <style>
        .left_container {
            height: 500px; 
            width: 200px !important;
            margin: 0; 
            background-color: #f3f3f3;
        }
        .middle_container {
            min-height: 500px;
            max-height: 750px;
            height: auto;
            width: 700px !important;
            margin:0;
            margin-bottom: 20px;
            background-color: #fafafa;
            display: block;
        }
        .middle_container_2 {
            min-height: 500px;
            height: auto;
            width: 500px !important;
            margin:0;
            background-color: #fafafa;
            display: none;
        }
        .right_container {
            min-height: 100px;
            max-height: 500px;
            height: 500px; 
            width: 200px !important; 
            margin:0; 
            background-color: #e6e6e6;
            display: none;
        }
        .folders {
            padding-top: 20px;
        }
        .folders a {
            text-decoration: none;
            width: 100%;
            font-size: 15px;
        }
        .folders button {
            background: none;
            border: none;
            text-align: left;
            padding-top: 5px;
            padding-bottom: 5px;
            margin-bottom: 2px;
            width: 100%;
        }
        .folders button:hover {
            background-color: #01295F;
            color: #fff;
        }
        .folders button:focus {
            background-color: #01295F;
            color: #fff;
            outline: none;
        }
        .folders_button_active {
            background-color: #01295F !important;
            color: #fff !important;
            font-weight: bold;
        }
        .folders button i {
            padding-right: 10px;
        }
        .folders button span {
            float: right;
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
            background-color: #fafafa;
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
            /*
            animation:mymove .5s ease-out forwards;
            -webkit-animation:mymove .5s;
            */
        }
        /*
        @keyframes mymove
        { 
            from { top:180px; }
            to { top:190px; }
        }
        */
        /* Safari and Chrome */
        /*
        @-webkit-keyframes mymove 
        {
            from { top:180px; }
            to { top:190px; }
        }
        */
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
        .mc_loading {
            position: absolute;
            min-height: 500px;
            max-height: 750px;
            height: 100%;
            width: 700px;
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
            width: 100px;
            position: absolute;
            top: 180px;
            left: 290px;
        }
        .email_opt_top {
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 10px;
            height: 40px;
            border-bottom: 1px solid black;
        }
        .mail_subj {
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 10px;
            border-bottom: 1px solid black;
        }
        .mail_subj .msubj {
            font-weight: bold;
            font-size: 20px;
            width: 70%;
            word-wrap: break-word; 
            display: inline-block;
        }
        .mail_subj .right_side {
            padding-right: 10px;
            display: inline-block;
            height: 30px;
            margin-left: 40px;
            top: 0;
        }
        .mail_subj .tNO {
            font-weight: bold;
            font-size: 18px;
            width: 10%;
            display: block;
        }
        .mail_subj .tSTAT {
            font-size: 15px;
            width: 10%;
            display: block;
        }
        .mail_body {
            min-height: 100px;
            height: auto;
            padding-top: 15px;
            max-height:380px;
            overflow-y: auto; 
            overflow-x:hidden;
        }
        .mail_body .mfrom {
            width: 60%;
            padding-left: 10px;
            font-size: 15px;
            font-weight: bold;
        }
        .mail_body .mdate {
            width: 20%;
            float: right;
            padding-right: 10px;
            color: #a5b2bd;
        }
        .mbody {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 20px;
        }
        .mattach {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 20px;
        }
        .prevConvo {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 10px;
        }
        .prevConvo span {
            color: #000;
            font-weight: bold;
            font-size: 15px;
        }

        /* attachments preview */
        .imgatt1
        {
            position: relative; 
            display: inline-block;
            padding: 0.5em;

        }
        .imgatt1 button {
            position: absolute; 
            width: 20px; 
            height: 20px; 
            top: 70%; 
            left: 75%; 
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
            width: 100px; 
            height: 100px; 
            display: none; 
            color: #ffffff ; 
            font-weight: bold; 
            padding: 5px; 
            word-wrap: break-word; 
            cursor: zoom-in;
        }
        .imgatt2
        {
            width: 100px; 
            height: 100px; 
            margin-bottom:25px;
       }




  
    </style> 

<div class="full-width-div">
    <div class="container_12 boxsummary2" style="left: 150px;">
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
    

              
            <div class="container_12">
                <div class="grid_4 alpha left_container">
                    <div class="folders">
                        <a href="#unassigned">
                            <button id="btn_fldr_1" onclick="openFolder(1, <?=$unassigned?>, '#datatable_unassigned')"><i class="glyphicon glyphicon-envelope"></i>
                                Unassigned<span><?=$unassigned?></span>
                            </button>
                        </a>
                        <a href="#mine">
                            <button id="btn_fldr_2" onclick="openFolder(2, <?=$mine?>, '#datatable_mine')"><i class="glyphicon glyphicon-inbox"></i>
                                Mine<span><?=$mine?></span>
                            </button>
                        </a>
                        <a href="#assigned">
                            <button id="btn_fldr_3" onclick="openFolder(3, <?=$assigned?>, '#datatable_assigned')"><i class="glyphicon glyphicon-user"></i>
                                Assigned<span><?=$assigned?></span>
                            </button>
                        </a>
                        <a href="#closed">
                            <button id="btn_fldr_4" onclick="openFolder(4, <?=$closed?>, '#datatable_closed')"><i class="glyphicon glyphicon-trash"></i>
                                Closed<span><?=$closed?></span>
                            </button>
                        </a>
                        <a href="#spam">
                            <button id="btn_fldr_5" onclick="openFolder(5, <?=$spam?>, '#datatable_spam')"><i class="glyphicon glyphicon-ban-circle"></i>
                            Spam<span><?=$spam?></span></button>
                        </a>
                    </div>
                </div>
                <div class="grid_4  middle_container">
                    <div class="mc_loading">
                        <div class="bg"></div>
                        <img src="img/loader1.gif">
                    </div>
                    <?php if($test) { ?>
                        <div id="list_unassigned" class="folder_list_hide">
                            <?php divChckBxs(); ?>
                            <table id="datatable_unassigned" class="table">
                                <!--<input type="text" id="chckdTNos">
                                <span id="chckdTNos_disp"></span>
                                <br><button onclick="checkedboxes()"></button>-->
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
                                    <?php $i=0; while($i < $unassigned) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$i?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$customer_name."U".$i?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')">
                                            <div class="table_email_content">
                                                <b><?=$email_subject?></b><br/>
                                                <?=$email_body?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_number+$i?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_updated_at?></td>
                                    </tr>
                                    <?php $i++; } ?>
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
                                    <?php $j=0; while($j < $mine) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$j?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$customer_name."M"?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')">
                                            <div class="table_email_content">
                                                <b><?=$email_subject?></b><br/>
                                                <?=$email_body?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_number.$j?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_updated_at?></td>
                                    </tr>
                                    <?php $j++; } ?>
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
                                    <?php $k=0; while($k < $assigned) { ?>
                                    <?php 
                                        $test_arr_agents = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                                        $ran_agents = array_rand($test_arr_agents);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$k?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$customer_name."A"?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')">
                                            <div class="table_email_content">
                                                <b><?=$email_subject?></b><br/>
                                                <?=$email_body?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$test_arr_agents[$ran_agents].$assigned_to?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_number.$k?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_updated_at?></td>
                                    </tr>
                                    <?php $k++; } ?>
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
                                    <?php $l=0; while($l < $closed) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$l?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$customer_name."C"?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')">
                                            <div class="table_email_content">
                                                <b><?=$email_subject?></b><br/>
                                                <?=$email_body?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_number.$l?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_updated_at?></td>
                                    </tr>
                                    <?php $l++; } ?>
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
                                    <?php $m=0; while($m < $spam) { ?>
                                    <tr>
                                        <td><input type="checkbox" id="chckbxid<?=$m?>" class="chckbx"></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$customer_name."S"?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')">
                                            <div class="table_email_content">
                                                <b><?=$email_subject?></b><br/>
                                                <?=$email_body?>
                                            </div>
                                        </td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_number.$m?></td>
                                        <td onclick="getTicketData('<?=$ticket_id?>')"><?=$ticket_updated_at?></td>
                                    </tr>
                                    <?php $m++; } ?>
                                </tbody>
                            </table>
                        </div>
<!--TEST END HERE--><?php } else { ?>
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
                    <?php } ?>
                </div>
                <div class="grid_4 middle_container_2">
                    <div class="email_opt_top">
                        <span>[action_buttons here]</span>
                    </div>
                    <div class="mail_subj">
                        <span class="msubj"></span>
                        <div class="right_side">
                            <span class="tNO"></span>
                            <span class="tSTAT"></span>
                        </div>
                    </div>
                    <div class="mail_body">
                        <div class="mdetails">
                            <span class="mfrom"></span>
                            <span class="mdate"></span>
                        </div>
                        <div class="mbody"></div>
                        <div class="mattach">
                        <?php 
                            if(!empty($attach) && $test == true) {
                                echo "<br/><br/><b>Attachments: </b><br/>";
                            }
                        ?>
                        </div>
                    </div>
                </div>

                <div class="grid_4 omega right_container">
                    <div class="prevConvo">
                        <span>Previous Conversations</span>
                    </div>
                </div>

            </div>
        </div> 
    </div>

   

<?php
    require "footer.php";
?>


    <script>
        $(document).ready(function() {
            $.cookie('last_tab', '#support');
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
            $('.middle_container').css("display", "block");
            $('.middle_container_2').css("display", "none");
            $('.right_container').css("display", "none");
            $('#list_unassigned').addClass('folder_list_hide');
            $('#list_mine').addClass('folder_list_hide');
            $('#list_assigned').addClass('folder_list_hide');
            $('#list_closed').addClass('folder_list_hide');
            $('#list_spam').addClass('folder_list_hide');
            $('#btn_fldr_1').removeClass('folders_button_active');
            $('#btn_fldr_2').removeClass('folders_button_active');
            $('#btn_fldr_3').removeClass('folders_button_active');
            $('#btn_fldr_4').removeClass('folders_button_active');
            $('#btn_fldr_5').removeClass('folders_button_active');

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
                $('#btn_fldr_1').addClass('folders_button_active');
                current_folder_list = "#list_unassigned";
                current_datatable = "#datatable_unassigned";
            } else if(folder == 2) {
                $('#list_mine').removeClass('folder_list_hide');
                $('#btn_fldr_2').addClass('folders_button_active');
                current_folder_list = "#list_mine";
                current_datatable = "#datatable_mine";
            } else if(folder == 3) {
                $('#list_assigned').removeClass('folder_list_hide');
                $('#btn_fldr_3').addClass('folders_button_active');
                current_folder_list = "#list_assigned";
                current_datatable = "#datatable_assigned";
            } else if(folder == 4) {
                $('#list_closed').removeClass('folder_list_hide');
                $('#btn_fldr_4').addClass('folders_button_active');
                current_folder_list = "#list_closed";
                current_datatable = "#datatable_closed";
            } else {
                $('#list_spam').removeClass('folder_list_hide');
                $('#btn_fldr_5').addClass('folders_button_active');
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

        function openOneTicket() {
            $('.middle_container').css("display", "none");
            $('.middle_container_2').css("display", "block");
            $('.right_container').css("display", "block");
        }

        function getTicketData(id) {
            $('.mc_loading').css("display", "block");
            $('.msubj').empty();
            $('.tNO').empty();
            $('.tSTAT').empty();
            $('.mfrom').empty();
            $('.mdate').empty();
            $('.mbody').empty();
            $('.mattach').empty();
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    var res = String(xmlhttp.responseText);
                    field = res.split("||+||");
                    $('.msubj').text(field[0]);
                    $('.tNO').text("#"+field[1]);
                    $('.tSTAT').text(field[2]);
                    $('.mfrom').text("FROM: "+field[3]);
                    $('.mdate').text(field[4]);
                    $('.mbody').append(field[5]);
                    $('.mattach').append(field[6]);
                    
                    if(res) {
                        $('.mc_loading').css("display", "none");
                        openOneTicket();
                    }
                }
            }
            xmlhttp.open("GET", "support_get_one?tid="+id, true);
            xmlhttp.send(null);   
        }






//fixed position for right and left containers when a ticket is clicked
           /** $(function() {
            var offset = $(".left_container, .right_container").offset();
            var topPadding = 15;
            $(window).scroll(function() {

                if($(window).width() >= 959)
                if ($(window).scrollTop() > offset.top) 
                 
                {
                    $(".left_container, .right_container").stop().animate({
                        marginTop: $(window).scrollTop() - offset.top + topPadding
                    });
                } else {
                    $(".left_container, .right_container").stop().animate({
                        marginTop: 0
                    });
                };
            });
        });


**/


</script>


