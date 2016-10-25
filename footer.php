</div>
</body>
</html>

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.mousewheel.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>
<script src="js/dataTables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script src="js/dataTables/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/jquery.tagsinput.js"></script>

<?php if(basename($_SERVER['PHP_SELF']) == "customer.php") { ?>
<script type="text/javascript">
      $(document).ready(function(){
        checkIfCancel();
        
        document.getElementById('search_result_view').style.display = "none";
        var lastTab = $.cookie('last_tab');
        if(lastTab != "#admin"){
            document.getElementById('boxes').style.display = "block";
            document.getElementById('search_result_view').style.display = "block";
        }

        $('#btn_admin').click(function(){
            document.getElementById('boxes').style.display = "none";
            document.getElementById('search_result_view').style.display = "none";
        });
        $('#btn_account,#btn_provisioning,#btn_support,#customer,#btn_dashboard,#btn_center').click(function(){
            document.getElementById('boxes').style.display = "block";
            document.getElementById('search_result_view').style.display = "block";
        });

        $("#users").dataTable();
      });
</script>
<?php } ?>

<script type="text/javascript">

		function onAddTag(tag) {
			alert("Added a tag: " + tag);
		}
		function onRemoveTag(tag) {
			alert("Removed a tag: " + tag);
		}

		function onChangeTag(input,tag) {
			alert("Changed a tag: " + tag);
		}

    function checkIfCancel() {
        if(document.getElementById("cancel_no").checked == true) {
            cancelNo();
        }
    }

    function cancelYes() {
        $('#cancel_reason').prop('disabled', false);
    }

    function cancelNo() {
        $('#cancel_reason').prop('disabled', true);
    }

		$(function() {

			$('#k-words').tagsInput({width:'auto'});

		});

</script>
<!--js for shrinking text and div-->

<script>
$( '.box' ).each(function ( i, box ) {

   var width = $( box ).width(),
       html = '<span style="white-space:nowrap">',
       line = $( box ).wrapInner( html ).children()[ 0 ],
       n = 100;
   
   $( box ).css( 'font-size', n );

   while ( $( line ).width() > width ) {
       $( box ).css( 'font-size', --n );
   }

   $( box ).text( $( line ).text() );

});

</script>

<!--draggable scrollbar-->


<script type="text/javascript">
    $(function() { 
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
            //save the latest tab using a cookie:
            $.cookie('last_tab', $(e.target).attr('href'));
        });
        //activate latest tab, if it exists:
        var lastTab = $.cookie('last_tab');
        if (lastTab) {
            $('a[href=' + lastTab + ']').tab('show');
        }
        else
        {
            // Set the first tab if cookie do not exist
            $('a[data-toggle="tab"]:first').tab('show');
        }

        //--------
        $("form[name='add_user']").validate({
        // Specify validation rules
        rules: {
          // The key name on the left side is the name attribute
          // of an input field. Validation rules are defined
          // on the right side
          fname: { required:true },
          lname: { required:true },
          email: {
            // Specify that email should be validated
            // by the built-in "email" rule
            required : true,
            email: true
          },
          pass: {
            maxlength: 32
          }
        },
        // Specify validation error messages
        messages: {
          pass: {
            required: "Please provide a password",
            maxlength: "Whoah, not too much"
          },
          email: "Please enter a valid email address",
          fname: "First Name is required",
          lname: "Last Name is required"
        },
        errorPlacement: function (error, element) {
            element.attr("placeholder", error[0].outerText);
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
          form.submit();
        }
      });

      $("#noid_cust_account_form").validate({
        ignore: "",
        rules: {
            cID: { 
                required: true 
            }
        },
        messages: {
            cID: {
                required: false
            }
        },
        focusInvalid: false,
        errorPlacement: function(){
           return false;
        },
        submitHandler: function(form) {
            form.submit();
        },
        showErrors: function(errorMap, errorList) {
            $(".form-errors").html("No customer selected");
        }
    });

    $("#cust_account_form").validate({
        rules: {
            acc_b_name: { 
                required:true 
            },
            acc_fname: { 
                required:true 
            },
            acc_lname: { 
                required:true 
            },
            acc_phone: {
                required:true
            },
            acc_email: {
                required:true
            },
            acc_bill_add_1: {
                required:true
            },
            acc_bill_city: {
                required:true
            },
            acc_bill_state: {
                required:true
            },
            acc_bill_zip: {
                required:true
            }
        },
        messages: {
            acc_b_name: "*",
            acc_fname: "*",
            acc_lname: "*",
            acc_phone: "*",
            acc_email: "*",
            acc_bill_add_1: "*",
            acc_bill_city: "*",
            acc_bill_state: "*",
            acc_bill_zip: "*"
        },
        focusInvalid: false,
        invalidHandler: function() {
            $(this).find(":input.error:first").focus();
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
    
    $("#cust_provisioning_form").validate({
        rules: {
            bname: { 
                required:true 
            },
            b_email: { 
                required:true 
            }
        },
        messages: {
            bname: "*",
            b_email: "*"
        },
        focusInvalid: false,
        invalidHandler: function() {
            $(this).find(":input.error:first").focus();
        },
        submitHandler: function(form) {
            form.submit();
        }
    });


     $("#email_box").mousewheel(function(event, delta) {

        this.scrollLeft -= (delta * 30);
      
        event.preventDefault();

     });



    });

    $(document).ready(function () {
        check();
    });

	var myapp = angular.module("myapp",[]);
		myapp.controller("newController", function($scope,$http){
			$http.get("dynamoDB/search.php").success(function(response){
				$scope.users = response;
			});
			$scope.num = 5;
		});

	function check(){
		var search = document.forms["myForm"]["search"].value;
		if(search) {
			document.getElementById('output').style.visibility = "visible";
			document.getElementById("search_result_view").style.zIndex = "10";
		} else {
			document.getElementById('output').style.visibility = "hidden";
			document.getElementById("search_result_view").style.zIndex = "0";
		}
	}
		
	function getSearch(){
		var search = document.forms["myForm"]["search"].value;
		window.location.href = "results.php?search="+search;
	}
</script>

<?php
if(isset($_SESSION['user_now_db_customer_id'])) {
	$char_state = $result_customer_id[0]->state;
	$char_state_exp = explode('_', $char_state);
	$count=0;
	$fin_char_state = "";
	while(!empty($char_state_exp[$count])) {
		$fin_char_state .= ucfirst($char_state_exp[$count])."&nbsp;";
		$count++;
	}
	echo "<input type='text' id='char_state' value='".$fin_char_state."' hidden>";

	?><script>
		document.getElementById("cust_id").title = document.getElementById("char_state").value;
	</script>
	<?php
}
?>

<script>
	function CapCom(e) {
		var keyCom = window.event? event : e
		if (keyCom.ctrlKey && keyCom.keyCode == 88){ //combination is ctrl + q
			window.location = "logout.php";
		}
	}
	document.onkeydown = CapCom;
</script>

<script type="text/javascript">

!function ($) {

    "use strict";

    var Progressbar = function (element) {
        this.$element = $(element);
    }

    Progressbar.prototype.update = function (value) {
        var $div = this.$element.find('div');
        var $span = $div.find('span');
        $div.attr('aria-valuenow', value);
        $div.css('width', value + '%');
        $span.text(value + '% Complete');
    }

    Progressbar.prototype.finish = function () {
        this.update(100);
    }

    Progressbar.prototype.reset = function () {
        this.update(0);
    }

    $.fn.progressbar = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('jbl.progressbar');

            if (!data) $this.data('jbl.progressbar', (data = new Progressbar(this)));
            if (typeof option == 'string') data[option]();
            if (typeof option == 'number') data.update(option);
        })
    };


    $(document).ready(function(){ 
    	XX();
    $(".check-fill").keyup(XX); 
    function XX() {
        var $fields = $(".check-fill");
        var count = 0;
        $fields.each(function(){
             if($(this).val().length > 0)
                  count++;
        });
        
        
         var percentage = Math.floor(count * 100 / $fields.length);

    $(".progress-bar").css("width", percentage + "%");
    $(".count").text(percentage+"% Complete");

}
     
});

}

(window.jQuery);
</script>
<!--
<script type="text/javascript" src="js/session_timeout.js"></script>

<script>
jQuery(document).ready(function() {       
    checkIdle();
});
</script>-->