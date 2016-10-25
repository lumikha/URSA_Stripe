<style type="text/css">
  
a #unassigned:hover
{
  background-color: #26B68E !important;
  color: white !important;
}


a #mine:hover
{
  background-color: #EAE17F !important;
  color: white !important;
}


a #assigned:hover
{
  background-color: #348DC0 !important;
  color: white !important;
}


a #closed:hover
{
  background-color: #323232 !important;
  color: white !important;
}


a #spam:hover
{
  background-color: #C63C33 !important;
  color: white !important;
}

table thead
{
  background-color: rgba(52,141,192, 0.8);
  width: 100% !important;

}

.dataTables_wrapper
{
  margin-top: -1em !important;
  margin-top: -1em !important;
  background-color: rgba(0, 0, 0, 0);
}

.dataTables_scrollBody
{
    height: 300px !important;
}
</style>


<div class="full-width-div">
  <div class="container_12">
    <div class="grid_12 push_1 boxsupport">
      <div id="boxesSup" class="row text-center">
       
        <a href="#unassigned" onclick="openFolder(1, <?=$unassigned?>, '#datatable_unassigned')">
          <div class="grid_2 unassignedbutton" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #26B68E 2px;color:#26B68E;"  id="unassigned">
            <i class="glyphicon glyphicon-envelope"></i> &nbsp<strong>Unassigned</strong>
          </div>
        </a>
        <a href="#mine" onclick="openFolder(2, <?=$mine?>, '#datatable_mine')" >
          <div class="grid_2 minebutton" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #EAE17F 2px;color:#EAE17F" id="mine">
            <i class="glyphicon glyphicon-inbox"></i>&nbsp<strong>Mine</strong>
          </div>
        </a>
        <a href="#assigned"  onclick="openFolder(3, <?=$assigned?>, '#datatable_assigned')" >
          <div class="grid_2 assignedbutton" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #348DC0 2px;color:#348DC0" id="assigned">
            <i class="glyphicon glyphicon-user"></i>&nbsp<strong>Assigned</strong>
          </div>
        </a>
        <a href="#closed" >
          <div onclick="openFolder(4, <?=$closed?>, '#datatable_closed')" class="grid_2 closedbutton" style="padding: 1em;margin-right:2.75em; margin-bottom:1em;border:solid #323232 2px;color:#323232" id="closed">
            <i class="glyphicon glyphicon-trash"></i>&nbsp<strong>Closed</strong>
          </div>
        </a>
        <a href="#spam" >
          <div onclick="openFolder(5, <?=$spam?>, '#datatable_spam')" class="grid_2 spambutton" style="padding: 1em;margin-right:2.78em; margin-bottom:1em;border:solid #C63C33 2px;color:#C63C33" id="spam">
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
            <?php foreach($arr_unassigned as $aUN) { 
                $body2 = htmlentities($aUN['body']);
                if($aUN['notes']) {
                    $th_arr_fin2 = "";
                    $th_arr2 = array();
                    foreach($aUN['notes'] as $nl2) {
                        array_push($th_arr2, "<i><b>".$nl2['n_created_by']['S']."</b></i> added note||+||<span style='float: right;'>".$nl2['n_created_at']['S']."</span>||+||<p>".$nl2['n_content']['S']."</p>~^^^~");
                    }

                    $thArrCnt2 = 0;
                    while(!empty($th_arr2[$thArrCnt2])) {
                        $th_arr_fin2 .= $th_arr2[$thArrCnt2];
                        $thArrCnt2++;
                    }
                } else {
                    $th_arr_fin2 = "";
                }

                $ats_title2 = "";
                $ats2 = "";
                if($aUN['attachments']) {
                    $ats_title2 = "<br/><b>Attachments</b><br/><br/>";
                }
                foreach($aUN['attachments'] as $am_ats2) {
                    $ats2 .= htmlentities($am_ats2);
                }

                ?>

              <tr>
                <div data-cid="<?=$cID?>" data-id="<?=$mID?>" data-no="<?=$tNo?>" data-status="<?=$sts?>" data-subject="<?=$sbj?>" data-mes="<?=$bdy?>" data-atturl="<?=$ats_title.$ats?>" data-threadmsg="<?=$th_arr_fin?>"></div>
                <td><input type="checkbox" id="chckbxid<?=$aUN['no']?>" class="chckbx"></td>
                <td class="open-modal" data-cid='13867879' data-id='<?=$aUN["ticket_id"]?>' data-no='<?=$aUN['no']?>' data-status='<?=$aUN['status']?>' data-subject='<?=$aUN['subject']?>' data-mes='<?=$body2?>' data-atturl='<?=$ats_title2.$ats2?>' data-threadmsg='<?=$th_arr_fin2?>'><?=$aUN['from']?></td>
                <td class="open-modal" data-cid='13867879' data-id='<?=$aUN["ticket_id"]?>' data-no='<?=$aUN['no']?>' data-status='<?=$aUN['status']?>' data-subject='<?=$aUN['subject']?>' data-mes='<?=$body2?>' data-atturl='<?=$ats_title2.$ats2?>' data-threadmsg='<?=$th_arr_fin2?>'>
                                            <div class="table_email_content">
                                                <b><?=$aUN['subject']?></b><br/>
                                                <?=str_replace("<br>",'',$aUN['body'])?>
                                            </div>
                                        </td>
                                        <td class="open-modal" data-cid='13867879' data-id='<?=$aUN["ticket_id"]?>' data-no='<?=$aUN['no']?>' data-status='<?=$aUN['status']?>' data-subject='<?=$aUN['subject']?>' data-mes='<?=$body2?>' data-atturl='<?=$ats_title2.$ats2?>' data-threadmsg='<?=$th_arr_fin2?>'><?=$aUN['no']?></td>
                                        <td class="open-modal" data-cid='13867879' data-id='<?=$aUN["ticket_id"]?>' data-no='<?=$aUN['no']?>' data-status='<?=$aUN['status']?>' data-subject='<?=$aUN['subject']?>' data-mes='<?=$body2?>' data-atturl='<?=$ats_title2.$ats2?>' data-threadmsg='<?=$th_arr_fin2?>'><?=$aUN['updated']?></td>
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
    