function forgot_pass() {
	$('#hido3').addClass('hido');
	$('#hido4').addClass('hido');
	$('#login_box').prop('hidden', true);
	$('#lostpass_box').prop('hidden', false);
}
function go_back_login() {
	$('#hido1').addClass('hido');
	$('#hido2').addClass('hido');
	$('#hido3').addClass('hido');
	$('#hido4').addClass('hido');
	$('#login_box').prop('hidden', false);
	$('#lostpass_box').prop('hidden', true);
}

function check_login_fields(){
	var checkError = new Array();
	var email = document.getElementById("email").value,
		password = document.getElementById("password").value;

	if (email) {
	    if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
	    	$('#hido1').addClass('hido');
	    } else {
	        $('#hido1').removeClass('hido');
	        $('#hido1').removeClass('hidob'); 
	        document.getElementById("error1").innerHTML = "Incorrect email format.";
	        checkError.push("1a");
	    } } 
	else {
	    $('#hido1').removeClass('hido');
	    $('#hido1').removeClass('hidob'); 
	    document.getElementById("error1").innerHTML = "Required field.";
	    checkError.push("1"); }

	if (password) {
	    if(/^\s/.test(password)) {
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

	if(checkError != "")
    {
        if(checkError[0] == "1" || checkError[0] == "1a") { $('#email').focus(); }
        if(checkError[0] == "2" || checkError[0] == "2a") { $('#password').focus(); }
        return false;
    }
    else
    {
    	return true;
    }
}

function hideMsgF1(){$('#hido1').addClass('hidob');}
function hideMsgF2(){$('#hido2').addClass('hidob');}

function check_resetpass_fields(){
	var checkError = new Array();
	var resetpass_email = document.getElementById("resetpass_email").value,
		resetpass_newpass = document.getElementById("resetpass_newpass").value;

	if (resetpass_email) {
	    if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(resetpass_email)) {
	    	$('#hido3').addClass('hido');
	    } else {
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

	if (resetpass_newpass) {
	    $('#hido4').addClass('hido'); } 
	else {
	    $('#hido4').removeClass('hido');
	    $('#hido4').removeClass('hidob'); 
	    document.getElementById("error4").innerHTML = "Required field.";
	    checkError.push("4"); }

	if(checkError != "")
    {
        if(checkError[0] == "3" || checkError[0] == "3a") { $('#resetpass_email').focus(); }
        if(checkError[0] == "4") { $('#resetpass_newpass').focus(); }
        return false;
    }
    else
    {
    	return true;
    }
}

function hideMsgF3(){$('#hido3').addClass('hidob');}
function hideMsgF4(){$('#hido4').addClass('hidob');}