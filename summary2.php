<?php
    require 'header.php';
    require 'gmail_get_messages.php';
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
        #output {
            display: none;
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

            <div class="grid_12 push_1 alpha" style="height: 500px; width: 93%; position: relative; margin-left: 20px; margin-top: -25px;">
                <?php
                    include "forms/accounts_form.php";
                ?>
            </div>
        </div>
    <!--</div>-->

<!--<div class="tab-pane fade" id="account">-->

    <div class="container_12 boxsummary" style="left: 150px;" id="tickets_display">  
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
                            <input type="text" class="form-control" id="tNo" value="" style="text-align: center; font-weight: bold;" readonly>
                        </div>
                        <div class="col-md-5">
                            <label style="display: none;">Ticket ID</label>
                            <input type="text" class="form-control" id="tID" value="" style="text-align: center; font-weight: bold; display: none;" readonly>
                        </div>



                        <div class="col-md-3 text-right" style="top:-20px">
                            <a id="expand" href="#"><span id="glyph_resize" class="btn btn-info btn-sm glyphicon glyphicon-resize-full " aria-hidden="true"></span></a>
                            <a id="close_modal" href="#"><span id="glyph_close" class="btn btn-danger btn-sm glyphicon glyphicon-remove " aria-hidden="true"></span></a>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Subject</label>
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

    <div class="modal fade" id="updateTicket" tabindex="-1" role="dialog">
        <div id="modal_cont" class="modal-dialog modal-md">
            <div class="modal-content">
                                    
                <div class="modal-body">
                    <form method="POST">
                        <input type="type" id="cID_new_thread" name="cTID" hidden>
                        <input type="type" id="curr_status" name="curr_status" hidden>
                        <div class="row">
                            <div class="col-md-6">
                                <label>New Thread Type</label>
                                <div class="radio" style="margin-left: 30px;">
                                    <label><input type="radio" id="rad1" name="type" value="note" onclick="tType(1);" checked>Note</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <!--<label><input type="radio" id="rad2" name="type" value="message" onclick="tType(2);">Message</label>-->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>New Status</label>
                                <select class="form-control" id="commit_status" name="status">
                                    <optgroup label="Status">
                                        <option value="unassigned">Unassigned</option>
                                        <option value="pending">Pending</option>
                                        <option value="closed">Closed</option>
                                        <option value="spam">Spam</option>
                                    </optgroup>
                                </select>
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
        <div class="container_12" style="margin-top:-1em;">
            <div id="boxes" class="row text-center">
                <div class="grid_2 push_1 alpha" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #A60800 2px;color:#A60800"><a href="#" onclick="return addTicket();"><strong>Ticket</strong></a></div>
                <div class="grid_2 push_1 omega" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #340570 2px;color:#340570"><strong>Talkdesk</strong></div>
        
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
                           
                            <div class="ticketsummary">
                                <div class="grid_1 alpha round-div">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <div class="grid_2 omega">
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
        $.cookie('last_tab', '#account');
        $('#viewTicket').on('hidden.bs.modal', function (e) {
            $("#magic_buttons").empty();
            $("#lbl_th").empty();
            $("#id_you_like_div_none").empty();
        })

        $('#datatable_accounts').DataTable({
            "bDestroy": true,
           "bPaginate": true,
                "pagingType": "full_numbers",
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
                
        });

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
