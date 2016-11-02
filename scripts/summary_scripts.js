function summaryPageOnload() {
	$("#supports_tab").removeClass('active');
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
	});

	$('#viewTicket').on('shown.bs.modal', function () {
		$('#tBody').scrollTop(0);
	});

	$('#previewAtt').on('hidden.bs.modal', function (e) {
		$("#attid").empty();
	});

	$('#updateTicket').on('hidden.bs.modal', function (e) {
		$("#commit_msg").empty();
	});

	/***** Initialize Support DataTables *****/
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
		}],
		"scrollY": "200px"
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
		}],
		"scrollY": "200px"
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
		}],
		"scrollY": "200px"
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
		}],
		"scrollY": "200px"
	});

	/***** Initialize Support DataTables *****/
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
		"scrollY": "300px",
	}); 

	$('.dataTables_scrollHeadInner').css('width', '900px');
}

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
	$.cookie('last_tab', '#account');
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
		fromName = _self.data('name');
	$("#tID").val(tID);
	$("#tNo").val(tNo);
	$("#tSubj").val(tSubj);
	$("#tMsg").html(tMsg);
	$("#tMsgAtt").html(tMsgAtt);
	$("#cID").val(cID);
	$("#commit_status").val(tSts);
	$("#curr_status").val(tSts);
	$("#fromName").val(fromName);

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
	if($('#modal_dialog').hasClass('modal-lg')) {
		img.style.width = '650px';
		img.style.height = '450px';
		$('#previewAtt').css('margin-top','-30px');
		$('#attfn').css('text-align','center');
	} else {
		img.style.width = '650px';
		img.style.height = '450px';
		$('#previewAtt').css('margin-top','-30px');
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

$('#expand').click(function() {
	if($('#glyph_resize').hasClass('glyphicon-fullscreen')) {
		$('#viewTicket').addClass('modal-fullscreen');
		$('#glyph_resize').removeClass('glyphicon-fullscreen');
		$('#glyph_resize').addClass('glyphicon-resize-small');
		$('#updateTicket').addClass('modal-fullscreen');
	} else {
		$('#viewTicket').removeClass('modal-fullscreen');
		$('#updateTicket').removeClass('modal-fullscreen');
		$('#glyph_resize').removeClass('glyphicon-resize-small');
		$('#glyph_resize').addClass('glyphicon-fullscreen');
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
	} else if(!$('#list_mine').hasClass("folder_list_hide")) {
		activeFolderNow = "#datatable_mine";
	} else if(!$('#list_assigned').hasClass("folder_list_hide")) {
		activeFolderNow = "#datatable_assigned";
	} else if(!$('#list_closed').hasClass("folder_list_hide")) {
		activeFolderNow = "#datatable_closed";
	} else {
		activeFolderNow = "#datatable_spam";
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
	//alert(arrOfchckbxs_chckd);
}

function getTicketData(id) {
	$('#viewTicket').modal('show'); 
}



/**show and hide buttons for previous conversation section **/

$(document).ready(function(){
    $("#show").click(function(){
        $(".prev_convo").show(1000);
    });

     $("#hide").click(function(){
        $(".prev_convo").hide(1000);
    });
});


