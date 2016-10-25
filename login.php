<?php
	require 'functions.php';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>URSA</title>
	<link rel="Shortcut icon" href="img/ursa_tab_logo.png"/>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/ursa/style-login.css">
	<link rel="stylesheet" type="text/css" href="js/field_trappings/error_msg.css">
	
<!--media query for login -->
	<link rel="stylesheet" type="text/css" href="css/ursa/style-login-xs.css" media="screen and (max-width:500px)">
	

	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/field_trappings/login.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	
	<!--disabling zoom in and out but only works on chrome-->
	<script> 
  			document.firstElementChild.style.zoom = "reset";
	</script>

<!--disabling zoom in and out works on firefox-->
	<script>

	$(document).ready(function(){
	$(document).keydown(function(event) {
	    	    if (event.ctrlKey==true && (event.which == '61' || event.which == '107' || event.which == '173' || event.which == '109'  || event.which == '187'  || event.which == '189'  ) ) {
         ; 
		event.preventDefault();
		// 107 Num Key  +
		//109 Num Key  -
		//173 Min Key  hyphen/underscor Hey
		// 61 Plus key  +/=
	     }
	});

	$(window).bind('mousewheel DOMMouseScroll', function (event) {
	       if (event.ctrlKey == true) {
           ; 
		   event.preventDefault();
	       }
	});
});
	</script>

</head>
<body>
	<input type="text" id="resetPassError" value="<?php echo $reset_pass_error; ?>" style="display:none;">
	<input type="text" id="resetPassSuccess" value="<?php echo $reset_pass_success; ?>" style="display:none;">

	<script>
		$(document).ready(function () {
		    if(document.getElementById("resetPassError").value == 1) {
		    	document.getElementById("reset_error").style.display = "block";
		    	forgot_pass();
		    }

		    if(document.getElementById("resetPassSuccess").value == 1) {
		    	forgot_pass();
		    }
		});
	</script>

	<div id="login_box" class="login_container">
		<div class="login_title">
			<!--<span style="color: #FFB30F;">BIG</span><span>LOCO</span>-->
			<!--<span>Welcome to Little Dipper</span>-->
			<img src="img/big_yellow_ursa_logo.png">
		</div>
		<div class="login_inner">
			<!--<div class="col-1">
				<div class="row">
					<h2>Log In</h3>
				</div>
			</div>-->
		<?php
			if($found==false) {
				echo '<div class="col-1 error_all">
						<p>Email/Password incorrect.</p>
					  </div>';
			}
		?>
			<div class="col-1">
				<form action="" method="POST" onsubmit="return check_login_fields();" name="login_form">
					<div class="row">
						<!--<label>Email</label>&nbsp;&nbsp;-->
						<label>&nbsp;</label>
						<label class="error" for="email" generated="true"></label>
						<input type="text" class="form-control" name="email" id="email" value="<?php if(@$_POST) { echo @$email; } ?>" onkeypress="hideMsgF1()" placeholder="Email">
					</div>
					<div class="row">
						<!--<label>Password</label>&nbsp;&nbsp;-->
						<label>&nbsp;</label>
						<label class="error" for="password" generated="true"></label>
						<input type="password" class="form-control" name="password" id="password" onkeypress="hideMsgF2()" placeholder="Password">
					</div>
					<div class="row">
						<div class="remember_me">
					    	<input type="checkbox" name="remember" value="remember_yes" title="URSA will remember your face for 1hour">
					    </div>
					    <label class="lbl_rmmbr_me">Remember Me</label>
				  	</div>
					<div class="row" >
						<div class="login_frgt_pass">
							<a href="#" onclick="forgot_pass();">Forgot your Password?</a>
						</div>
						<input class="login_sbmt" type="submit" name="login_btn" value="Log In">
					</div>
				</form>
			</div>
		</div>
	</div>

	<style>
		.flexContainer {
			display: flex;
		}

		#resetpass_newpass {
			flex: 1;
			padding-right: 50px;
		}

		#eye {
			background: transparent;
			border: none;
			margin-left: -26px;
			padding-top: -10px;
		}

		#eye:focus {
			outline:0;
		}

		#reset_error {
			width: 100%;
			height: 40px;
			background-color: #ffb3b3;
			border: solid 2px #ff6666;
			border-radius: 10px;
			text-align: center;
			padding-top: 8px;
			display: none;
		}
	</style>

	<div id="lostpass_box" class="login_container" hidden>
		<!--<div class="login_title">

		</div>-->
		<div class="login_inner">
			<div class="col-1">
				<div class="row">
					<?php if($reset_pass_success == 0) { ?>
						<h2>Reset Your Password</h3>
						<p>In order to reset your password, we must send you a link to your email to verify your account. What email address did you sign up with?</p>
					<?php } else { ?>
						<h2>New Password Created</h3>
						<p>An email was sent to your email address containing a link to verify your password reset request.</p>
					<?php } ?>
				</div>

				<?php if($reset_pass_success == 0) { ?>
				<div id="reset_error">
					<span>Email not registered.</span>
				</div>

				<form action="" method="POST" onsubmit="return check_resetpass_fields();" name="reset_pass_form">
					<div class="row">
						<label>&nbsp;</label>
						<label class="error" for="resetpass_email" generated="true"></label>
						<input type="text" class="form-control" name="resetpass_email" id="resetpass_email" onkeypress="hideMsgF3()" placeholder="Email">
					</div>
					<div class="row">
						<label>&nbsp;</label>
						<label class="error" for="resetpass_newpass" generated="true"></label>
						<div class="flexContainer">
							<input type="password" class="form-control" name="resetpass_newpass" id="resetpass_newpass" onkeypress="hideMsgF4()" placeholder="Desired New Password">
							<!--<button type="button" id="eye">
								<img src="img/show_password.png" alt="Show">
							</button>-->
							<button type="button" id="eye" class="glyphicon glyphicon-eye-open" title="Show Password"></button>
						</div>
					</div>
					<div class="row">
						<button class="go_back_login" onclick="go_back_login();">&#8592;Go Back</button>
						<input class="login_sbmt reset_pass_btn" name="reset_btn" type="submit" value="Reset Password">
					</div>
				</form>
				<?php } else { ?>
					<div class="row">
						<button class="go_back_login" onclick="go_back_login();">&#8592;Go Log In</button>
					</div>
				<?php } ?>	

			</div>
		</div>
	</div>


	

	<script>
	$(function() {
		$("form[name='login_form']").validate({
        // Specify validation rules
        rules: {
          // The key name on the left side is the name attribute
          // of an input field. Validation rules are defined
          // on the right side
          email: {
            // Specify that email should be validated
            // by the built-in "email" rule
            required : true,
            email: true
          },
          password: {
          	required: true,
            minlength: 0,
            maxlength: 32
          }
        },
        // Specify validation error messages
        messages: {
          password: {
            required: "Please provide a password",
            minlength: "At least 8 characters long",
            maxlength: "Whoah, not too much bruh"
          },
          email: "Please enter a valid email address"
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
          form.submit();
        }
      });
		//reset form
		$("form[name='reset_pass_form']").validate({
        // Specify validation rules
        rules: {
          // The key name on the left side is the name attribute
          // of an input field. Validation rules are defined
          // on the right side
          resetpass_email: {
            // Specify that email should be validated
            // by the built-in "email" rule
            required : true,
            email: true
          },
          resetpass_newpass: {
          	required: true,
            minlength: 0,
            maxlength: 32
          }
        },
        // Specify validation error messages
        messages: {
          resetpass_newpass: {
            required: "Please provide a new password",
            minlength: "At least 8 characters long",
            maxlength: "Whoah, not too much bruh"
          },
          resetpass_email: "Please enter a valid email address"
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
          form.submit();
        }
      });

	});

		document.getElementById("eye").addEventListener("click", function(e){
	        var pwd = document.getElementById("resetpass_newpass");
	        if(pwd.getAttribute("type")=="password"){
	            pwd.setAttribute("type","text");
	            $('#eye').removeClass('glyphicon-eye-open');
	            $('#eye').addClass('glyphicon-eye-close');
	            $('#eye').attr('title',"Hide Password");
	        } else {
	            pwd.setAttribute("type","password");
	            $('#eye').removeClass('glyphicon-eye-close');
	            $('#eye').addClass('glyphicon-eye-open');
	            $('#eye').attr('title',"Show Password");
	        }
	    });
	</script>
</body>
</html>
