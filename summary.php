<?php
	require 'header.php';
	require 'php/summary_functions.php';
?>

<link rel="stylesheet" type="text/css" href="css/summary_style.css"/>

<div class="container_12 hidden" id="accounts_display">
	<div class="grid_12 push_1 alpha accform">
	<?php include "forms/accounts_form.php"; ?>
	</div>
</div>

<div class="container_12 hidden" id="support_display">
	<?php include "forms/support_form.php"; ?>
</div>

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
					<div class="col-md-3 text-right resize">
						<a id="expand" href="#"><span id="glyph_resize" class="btn btn-info btn-sm glyphicon glyphicon-fullscreen " aria-hidden="true"></span></a>
						<a id="close_modal" href="#"><span id="glyph_close" class="btn btn-danger btn-sm glyphicon glyphicon-remove " aria-hidden="true"></span></a>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<label>Subject:</label>
						<input type="text" class="form-control" id="tSubj" readonly>
					</div>
				</div>
				<div class="row">
					<div class="col-md-8">
						<label>From:</label>
						<input type="text" class="form-control" id="fromName"  readonly>
					</div>
					<div class="col-md-4">
						<label>Date:</label>
						<input type="text" class="form-control" id=""   readonly>
					</div>
				</div>
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
					<label class="col-md-12">Thread(s)</label>
					<div id="lbl_th" class="col-md-12"></div>
					<div id="magic_buttons" class="col-md-12"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="updateTicket" tabindex="-1" role="dialog">
	<div id="modal_cont" class="modal-dialog modal-lg">
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
						<div class="col-md-9">
							<label>Message</label>
							<textarea class="form-control" id="commit_msg" name="message" style="height: 300px;"></textarea>
						</div>

						<div class="col-md-3">

							 <label>Previous Conversation 
                                  
                                        &nbsp
                                <span title="hide content" id="hide" title="Hide Content"><i class="fa fa-angle-up" style="font-size: 18px;"></i></span>
                                        &nbsp
                                <span title="show content" id="show" title="Show Content"><i class="fa fa-angle-down" style="font-size: 18px;"></i></span>

                             </label>
                             
                             <section class="form-control" id="prev_conv" name="prev_conv" style="height: 300px; background-color: rgba(0, 0, 0, 0.1);">
                                       
                                    <div class="prev_convo">

                                        <button type="button" class="btn btn-default btn-sm prevconvbutton" style="width: 100%; text-align: left !important;" >
                                           <span class="glyphicon glyphicon-envelope"></span> &nbsp Few More Basics
                                       </button>

                                        <button type="button" class="btn btn-default btn-sm prevconvbutton" style="width: 100%; text-align: left !important;" >
                                            <span class="glyphicon glyphicon-envelope"></span> &nbsp Learning the basics
                                        </button>


                                    </div>

                              </section>




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
			<div class="modal-body"></div>
			<div id="attid"></div>
			<span id="attfn" style="text-align:center !important; margin-top: 0em !important; background-color: transparent; font-size: 15px; color: #fff;" ></span>
		</div>
	</div>
</div>

<div class="container_12 boxsummary hidden" style="left: 150px;" id="tickets_display">  
	<div class="full-width-div">        
		<div class="container_12" style="margin-top:0em;">
			<div id="boxesSum" class="row text-center">
				<div class="grid_2 push_1 alpha ticketbutton" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #A60800 2px;color:#A60800"><a href="#" onclick="return addTicket();"><strong>Ticket</strong></a></div>
				<div class="grid_2 push_1 omega twiliobutton" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #340570 2px;color:#340570"><a href="#" onclick="return showTwilio();"><strong>Twilio</strong></a></div>
			</div>
		</div>
	</div>
	<div class="container_12">
		<div id="sumArea" class="grid_5 alpha" style="overflow-y: scroll; overflow-x: hidden; height: 550px; ">
		<?php 
		foreach($arr_msgs as $a_m) { 
			$mID = $a_m['ticket_id'];
			$tNo = $a_m['no'];
			$sts = $a_m['status'];
			$sbj = $a_m['subject'];
			$frm = $a_m['from'];
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
							<div class="grid_1 alpha round-div">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
							<div class="grid_2 omega ticketlist">
								<a href="#" class="open-modal" data-cid="<?=$cID?>" data-id="<?=$mID?>" data-name="<?=$frm?>" data-no="<?=$tNo?>" data-status="<?=$sts?>" data-subject="<?=$sbj?>" data-mes="<?=$bdy?>" data-atturl="<?=$ats_title.$ats?>" data-threadmsg="<?=$th_arr_fin?>">
								<strong><?php echo $bn; ?></strong></a> <br>
								<?php
									echo $fn." ".$ln."<br>".
										$bp."<br>".
										$cID;
								?>
							</div>
						</div>
					</div><br/>
		<?php 
				}
				$em_cnt++;
			}
		} 
        ?>
		</div>
	</div>
</div>
<?php
    require "footer.php";
?>
<script type="text/javascript" src="scripts/summary_scripts.js"></script>
<script>
	$(document).ready(function() {
		summaryPageOnload();
	});
</script>