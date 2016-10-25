function checkFields_enroll2(){
	var checkError = new Array();
	var salut = document.getElementById("salut").value,
		bfname = document.getElementById("bfname").value,
		blname = document.getElementById("blname").value,
		ceadd = document.getElementById("c-eadd").value,
		cphone = document.getElementById("c-phone").value,
		cstreet = document.getElementById("c-street").value,
		ccity = document.getElementById("c-city").value,
		cstate = document.getElementById("c-state").value,
		czip = document.getElementById("c-zip").value,
		cardnum = document.getElementById("card-number").value,
		cvc = document.getElementById("card-cvc").value,
		cardexpirymonth = document.getElementById("card-expiry-month").value,
		cardexpiryyear = document.getElementById("card-expiry-year").value,
		sagent = document.getElementById("sales-agent").value;

	if (salut) {
	    $('#hido-sal').addClass('hido'); }
	else {
	    $('#hido-sal').removeClass('hido');
	    $('#hido-sal').removeClass('hidob'); 
	    document.getElementById("error-sal").innerHTML = "Required field.";
	    checkError.push("1sal"); }

	if (bfname) {
	    if(/^\s/.test(bfname)) {
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

	if (blname) {
	    if(/^\s/.test(blname)) {
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

	if (ceadd) {
	    if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(ceadd))
	    {
	    	$('#hido3').addClass('hido');
	    }
	    else
	    {
	        $('#hido3').removeClass('hido');
	        $('#hido3').removeClass('hidob'); 
	        document.getElementById("error3").innerHTML = "Incorrect email format.";
	        checkError.push("3a");
	    } } 
	else {
	    $('#hido3').removeClass('hido');
	    $('#hido3').removeClass('hidob'); 
	    document.getElementById("error3").innerHTML = "Required field.";
	    checkError.push("3"); }

	if (cphone) {
		if(cphone.length == 10) {
	        $('#hido4').addClass('hido'); } 
	    else {
	    	$('#hido4').removeClass('hido');
	    	$('#hido4').removeClass('hidob'); 
	    	document.getElementById("error4").innerHTML = "Must contain 10 digit number.";
	    	checkError.push("4a"); } }
	else {
	    $('#hido4').removeClass('hido');
	    $('#hido4').removeClass('hidob'); 
	    document.getElementById("error4").innerHTML = "Required field.";
	    checkError.push("4"); }

	if (cstreet) {
	    if(/^\s/.test(cstreet)) {
	        $('#hido5').removeClass('hido');
	        $('#hido5').removeClass('hidob'); 
	        document.getElementById("error5").innerHTML = "Blank/White Spaces.";
	        checkError.push("5a"); }
	    else {
	        $('#hido5').addClass('hido'); } }
	else {
	    $('#hido5').removeClass('hido');
	    $('#hido5').removeClass('hidob'); 
	    document.getElementById("error5").innerHTML = "Required field.";
	    checkError.push("5"); }

	if (ccity) {
	    if(/^\s/.test(ccity)) {
	        $('#hido6').removeClass('hido');
	        $('#hido6').removeClass('hidob'); 
	        document.getElementById("error6").innerHTML = "Blank/White Spaces.";
	        checkError.push("6a"); }
	    else {
	        $('#hido6').addClass('hido'); } }
	else {
	    $('#hido6').removeClass('hido');
	    $('#hido6').removeClass('hidob'); 
	    document.getElementById("error6").innerHTML = "Required field.";
	    checkError.push("6"); }

	if (cstate) {
	        $('#hido7-state').addClass('hido'); }
	else {
	    $('#hido7-state').removeClass('hido');
	    $('#hido7-state').removeClass('hidob'); 
	    document.getElementById("error7-state").innerHTML = "Required.";
	    checkError.push("7state"); }

	if (czip) {
	        $('#hido7').addClass('hido'); }
	else {
	    $('#hido7').removeClass('hido');
	    $('#hido7').removeClass('hidob'); 
	    document.getElementById("error7").innerHTML = "Required field.";
	    checkError.push("7"); }

	if (cardnum) {
	        $('#hido8').addClass('hido'); }
	else {
	    $('#hido8').removeClass('hido');
	    $('#hido8').removeClass('hidob'); 
	    document.getElementById("error8").innerHTML = "Required field.";
	    checkError.push("8"); }

	if (cvc) {
	        $('#hido9').addClass('hido'); }
	else {
	    $('#hido9').removeClass('hido');
	    $('#hido9').removeClass('hidob'); 
	    document.getElementById("error9").innerHTML = "CVC Required field.";
	    checkError.push("9"); }

	if (cardexpirymonth) {
	        $('#hido10').addClass('hido'); }
	else {
	    $('#hido10').removeClass('hido');
	    $('#hido10').removeClass('hidob'); 
	    document.getElementById("error10").innerHTML = "MM Required field.";
	    checkError.push("10"); }

	if (cardexpiryyear) {
	    $('#hido11').addClass('hido'); }
	else {
	    $('#hido11').removeClass('hido');
	    $('#hido11').removeClass('hidob'); 
	    document.getElementById("error11").innerHTML = "YY Required field.";
	    checkError.push("11"); }

	if (sagent) {
	    $('#hido12').addClass('hido'); }
	else {
	    $('#hido12').removeClass('hido');
	    $('#hido12').removeClass('hidob'); 
	    document.getElementById("error12").innerHTML = "Required field.";
	    checkError.push("12"); }

	if(checkError != "")
    {
    	if(checkError[0] == "1sal") { $('#salut').focus(); }
        if(checkError[0] == "1" || checkError[0] == "1a") { $('#bfname').focus(); }
        if(checkError[0] == "2" || checkError[0] == "2a") { $('#blname').focus(); }
        if(checkError[0] == "3" || checkError[0] == "3a" || checkError[0] == "3b" || checkError[0] == "3c") { $('#c-eadd').focus(); }
        if(checkError[0] == "4") { $('#c-phone').focus(); }
        if(checkError[0] == "5" || checkError[0] == "5a") { $('#c-street').focus(); }
        if(checkError[0] == "6" || checkError[0] == "6a") { $('#c-city').focus(); }
        if(checkError[0] == "7state") { $('#c-state').focus(); }
        if(checkError[0] == "7") { $('#c-zip').focus(); }
        if(checkError[0] == "8") { $('#card-number').focus(); }
        if(checkError[0] == "9") { $('#card-cvc').focus(); }
        if(checkError[0] == "10") { $('#card-expiry-month').focus(); }
        if(checkError[0] == "11") { $('#card-expiry-year').focus(); }
        if(checkError[0] == "12") { $('#sales-agent').focus(); }
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

	function ChangeSal(){
		$('#hido-sal').addClass('hidob');
	}

	function KeyPressFName(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido1').addClass('hidob');
	}

	function KeyPressLName(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido2').addClass('hidob');
	}

	function KeyPressPhone(evt){
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

	function KeyPressStreet(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido5').addClass('hidob');
	}

	function KeyPressCity(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido6').addClass('hidob');
	}

	function ChangeState(){
		$('#hido7-state').addClass('hidob');
	}

	function KeyPressZip(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido7').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido7').removeClass('hido');
            $('#hido7').removeClass('hidob'); 
            document.getElementById("error7").innerHTML = "Digits 0-9 only.";
            return false; }
        else {
            $('#hido7').addClass('hidob');
            return true; } 
	}

	function KeyPressCCNumber(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido8').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido8').removeClass('hido');
            $('#hido8').removeClass('hidob'); 
            document.getElementById("error8").innerHTML = "Digits 0-9 only.";
            return false; }
        else {
            $('#hido8').addClass('hidob');
            return true; }
	}

	function KeyPressCVC(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido9').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido9').removeClass('hido');
            $('#hido9').removeClass('hidob'); 
            document.getElementById("error9").innerHTML = "CVC Digits 0-9 only.";
            return false; }
        else {
            $('#hido9').addClass('hidob');
            return true; }
	}

	function KeyPressCCExpiryMM(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido10').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido10').removeClass('hido');
            $('#hido10').removeClass('hidob'); 
            document.getElementById("error10").innerHTML = "MM Digits 0-9 only.";
            return false; }
        else {
            $('#hido10').addClass('hidob');
            return true; }
	}

	function KeyPressCCExpiryYY(evt){
		var charCode = (evt.which) ? evt.which : event.keyCode
		$('#hido11').addClass('hidob');
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            $('#hido11').removeClass('hido');
            $('#hido11').removeClass('hidob'); 
            document.getElementById("error11").innerHTML = "YY Digits 0-9 only.";
            return false; }
        else {
            $('#hido11').addClass('hidob');
            return true; }
	}

	function ChangeAgent(){
		$('#hido12').addClass('hidob');
	}

function clickField1(){$('#hido1').addClass('hidob');}
function clickField2(){$('#hido2').addClass('hidob');}
function clickField4(){$('#hido4').addClass('hidob');}
function clickField5(){$('#hido5').addClass('hidob');}
function clickField6(){$('#hido6').addClass('hidob');}
function clickField7(){$('#hido7').addClass('hidob');}
function clickField8(){$('#hido8').addClass('hidob');}	
function clickField9(){$('#hido9').addClass('hidob');}	
function clickField10(){$('#hido10').addClass('hidob');}	
function clickField11(){$('#hido11').addClass('hidob');}		