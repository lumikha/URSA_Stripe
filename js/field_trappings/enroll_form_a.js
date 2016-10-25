function checkFields_enroll1(){
	var checkError = new Array();
	var bizname = document.getElementById("biz-name").value,
		bizstreet = document.getElementById("biz-street").value,
		bizcity = document.getElementById("biz-city").value,
		bizstate = document.getElementById("biz-state").value,
		bizzip = document.getElementById("biz-zip").value,
		bizpnumber = document.getElementById("biz-pnumber").value,
		bizeadd = document.getElementById("biz-eadd").value,
		bizmnumber = document.getElementById("biz-mnumber").value;

	if (bizname) {
	    if(/^\s/.test(bizname)) {
	        $('#hido1').removeClass('hido');
	        $('#hido1').removeClass('hidob'); 
	        document.getElementById("error1").innerHTML = "Blank/White Spaces.";
	        checkError.push("1a"); }
	    else {
	        $('#hido1').addClass('hido'); } }
	else {
	    $('#hido1').removeClass('hido');
	    $('#hido1').removeClass('hidob'); 
	    document.getElementById("error1").innerHTML = "Required field.";
	    checkError.push("1"); }

	if (bizstreet) {
	    if(/^\s/.test(bizstreet)) {
	        $('#hido2').removeClass('hido');
	        $('#hido2').removeClass('hidob'); 
	        document.getElementById("error2").innerHTML = "Blank/White Spaces.";
	        checkError.push("2a"); }
	    else {
	        $('#hido2').addClass('hido'); } }
	else {
	    $('#hido2').removeClass('hido');
	    $('#hido2').removeClass('hidob'); 
	    document.getElementById("error2").innerHTML = "Required field.";
	    checkError.push("2"); }

	if (bizcity) {
	    if(/^\s/.test(bizcity)) {
	        $('#hido3').removeClass('hido');
	        $('#hido3').removeClass('hidob'); 
	        document.getElementById("error3").innerHTML = "Blank/White Spaces.";
	        checkError.push("3a"); }
	    else {
	        $('#hido3').addClass('hido'); } }
	else {
	    $('#hido3').removeClass('hido');
	    $('#hido3').removeClass('hidob'); 
	    document.getElementById("error3").innerHTML = "Required field.";
	    checkError.push("3"); }

	if (bizstate) {
	        $('#hido4-state').addClass('hido'); }
	else {
	    $('#hido4-state').removeClass('hido');
	    $('#hido4-state').removeClass('hidob'); 
	    document.getElementById("error4-state").innerHTML = "Required.";
	    checkError.push("4-state"); }

	if (bizzip) {
	        $('#hido4').addClass('hido'); }
	else {
	    $('#hido4').removeClass('hido');
	    $('#hido4').removeClass('hidob'); 
	    document.getElementById("error4").innerHTML = "Required field.";
	    checkError.push("4"); }

	if (bizpnumber) {
		if(bizpnumber.length == 10) {
	        $('#hido5').addClass('hido'); } 
	    else {
	    	$('#hido5').removeClass('hido');
	    	$('#hido5').removeClass('hidob'); 
	    	document.getElementById("error5").innerHTML = "Must contain 10 digit number.";
	    	checkError.push("5a"); } }
	else {
	    $('#hido5').removeClass('hido');
	    $('#hido5').removeClass('hidob'); 
	    document.getElementById("error5").innerHTML = "Required field.";
	    checkError.push("5"); }

	if (bizeadd) {
	    if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(bizeadd))
	    {
	    	$('#hido6').addClass('hido');
	    }
	    else
	    {
	        $('#hido6').removeClass('hido');
	        $('#hido6').removeClass('hidob'); 
	        document.getElementById("error6").innerHTML = "Incorrect email format.";
	        checkError.push("6a");
	    } } 
	else {
	    $('#hido6').removeClass('hido');
	    $('#hido6').removeClass('hidob'); 
	    document.getElementById("error6").innerHTML = "Required field.";
	    checkError.push("6"); }

	if(document.getElementById("allthetime_no").checked ==  true) {
		if (document.getElementById("spinner").value) {
	        $('#hido7').addClass('hido'); }
		else {
		    $('#hido7').removeClass('hido');
		    $('#hido7').removeClass('hidob'); 
		    document.getElementById("error7").innerHTML = "Required field, if No.";
		    checkError.push("7"); } }
	else {
		$('#hido7').addClass('hido'); }

	if (bizmnumber) {
		if(bizmnumber.length == 10) {
	        $('#hidomnum').addClass('hido'); } 
	    else {
	    	$('#hidomnum').removeClass('hido');
	    	$('#hidomnum').removeClass('hidob'); 
	    	document.getElementById("errormnum").innerHTML = "Must contain 10 digit number.";
	    	checkError.push("9"); } }
	else {
		$('#hidomnum').addClass('hido'); }

	if((document.getElementById("paymet_1").checked ==  true) || (document.getElementById("paymet_2").checked ==  true) ||
		(document.getElementById("paymet_3").checked ==  true) || (document.getElementById("paymet_4").checked ==  true) || 
		(document.getElementById("paymet_5").checked ==  true) || (document.getElementById("paymet_6").checked ==  true) ||
		(document.getElementById("paymet_7").checked ==  true)) {
		$('#hido8').addClass('hido'); }
	else {
		$('#hido8').removeClass('hido');
		$('#hido8').removeClass('hidob'); 
		document.getElementById("error8").innerHTML = "Please select atleast one.";
		checkError.push("8"); }

	if(checkError != "")
    {
        if(checkError[0] == "1" || checkError[0] == "1a") { $('#biz-name').focus(); }
        if(checkError[0] == "2" || checkError[0] == "2a") { $('#biz-street').focus(); }
        if(checkError[0] == "3" || checkError[0] == "3a") { $('#biz-city').focus(); }
        if(checkError[0] == "4-state") { $('#biz-state').focus(); }
        if(checkError[0] == "4") { $('#biz-zip').focus(); }
        if(checkError[0] == "5" || checkError[0] == "5a") { $('#biz-pnumber').focus(); }
        if(checkError[0] == "6" || checkError[0] == "6a" || checkError[0] == "6b") { $('#biz-eadd').focus(); }
        if(checkError[0] == "7") { $('#spinner').focus(); }
        if(checkError[0] == "9") { $('#biz-mnumber').focus(); }
        if(checkError[0] == "8") { $('#payment_label').focus(); }
        $('#error_check_all').removeClass('hido');
		$('#error_check_all').removeClass('hidob'); 
		document.getElementById("error_check_all").innerHTML = "Please complete all the required fields.";
        return false;
    }
    else
    {
    	return true;
    }
}

	function KeyPressBName(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido1').addClass('hidob');
	}

	function KeyPressBStreet(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido2').addClass('hidob');
	}

	function KeyPressBCity(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido3').addClass('hidob');
	}

	function ChangeState(){
		$('#hido4-state').addClass('hidob');
	}

	function KeyPressBZip(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido4').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido4').removeClass('hido');
            $('#hido4').removeClass('hidob'); 
            document.getElementById("error4").innerHTML = "Digits 0-9 only.";
            return false; }
        else {
            $('#hido4').addClass('hidob');
            return true; } 
	}

	function KeyPressBPNumber(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido5').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
			$('#hido5').removeClass('hido');
	        $('#hido5').removeClass('hidob'); 
	        document.getElementById("error5").innerHTML = "Digits 0-9 only.";
	        return false; }
	    else {
            $('#hido5').addClass('hidob');
            return true; } 
	}

	function KeyPressBEAdd(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido6').addClass('hidob');
	}

	function alltime() {
		$('#spinner').prop('hidden', true);
		$('#spinner').prop('disabled', true);
		$('#lbl_hrs').prop('hidden', true);
		$('#hido7').addClass('hidob');
	}

	function notalltime() {
		$('#spinner').prop('hidden', false);
		$('#spinner').prop('disabled', false);
		$('#lbl_hrs').prop('hidden', false);
	}

	function KeyPressMNumber(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hidomnum').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
			if(charCode == 45) {
				$('#hidomnum').addClass('hidob');
            	return true; }
            else {
	            $('#hidomnum').removeClass('hido');
	            $('#hidomnum').removeClass('hidob'); 
	            document.getElementById("errormnum").innerHTML = "Digits 0-9 only.";
	            return false; } }
	    else {
            $('#hidomnum').addClass('hidob');
            return true; } 
	}

	function payment() {
		$('#hido8').addClass('hidob');
	}

function clickField1(){$('#hido1').addClass('hidob');}
function clickField2(){$('#hido2').addClass('hidob');}
function clickField3(){$('#hido3').addClass('hidob');}
function clickField4(){$('#hido4').addClass('hidob');}
function clickField5(){$('#hido5').addClass('hidob');}
function clickField6(){$('#hido6').addClass('hidob');}
function clickField7(){$('#hido7').addClass('hidob');}
function clickFieldmnum(){$('#hidomnum').addClass('hidob');}