<?php
	
?>
<html>
<head>
	<title></title>
</head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	<script type="text/javascript" src="js/angular.min.js"></script>
	<script type="text/javascript">
		var myapp = angular.module("myapp",[]);
			myapp.controller("newController", function($scope,$http){
				$http.get("couchDB/search.php").success(function(response){
					$scope.users = response;
				});
				$scope.num = 5;
			});

		function check(){
			var search = document.forms["myForm"]["search"].value;
			if(search == null || search=="" || search==" "){	
				document.getElementById('output').style.visibility = "hidden";
				//jQuery("#output").fadeOut();
			}
			else{
				document.getElementById('output').style.visibility = "visible";
				//jQuery("#output").fadeIn();
			}
		}

		
		
		function getSearch(){
			var search = document.forms["myForm"]["search"].value;
			window.location.href = "results.php?search="+search;
		}

	</script>
<body ng-app="myapp" onload="check();">
		<!--SEARCH ENGINE -->
		<div ng-controller="newController" style="position:fixed;z-index: 10;">
		<div class="well-lg">
		<a href="" id="trigger" ><span class="btn btn-primary btn-md glyphicon glyphicon-search" aria-hidden="true"></span></a>
		&nbsp;&nbsp;
		<div id="toggleContainer" style="float:right">
				<form name="myForm">
					<input type="text" class="form-control" onkeyup="return check();" id="search" size="30" name="search" ng-model="search" placeholder="Search">
				</form>
				<span name="output" id="output">
						<div class="list-group">
							<a class="list-group-item" ng-repeat="user in result = ( users | filter:search | limitTo:num)" href="?id={{ user._id }}">
								<span style="font-size:130%" class="text-info"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> {{user.business_name}}</span><br>
								<span style="font-size:75%"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> {{user.customer_first_name}} {{user.customer_last_name}}, 
									<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>  {{user.business_email}}
								</span>
							</a>
							<a class="list-group-item" ng-hide="result.length">Opps, No Results Found ...</a>
							<a class="list-group-item text-right" href="#" onclick="getSearch();" style="font-size:90%">View More Results <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>	
						</div>
				</span> 
			</div>
		</div>
	</div>
	<!-- END SEARCH -->



	<!--SAMPLE  -->
	<div class="row">
		<div class="col-md-offset-1 col-md-10">
		<h1 style="text-align:right">Hey Jasper!</h1>

		</div>
	</div>
	<div class="row">
			<div class="col-md-offset-1 col-md-10">

	<ul class="nav nav-tabs">
	  <li class="active"><a data-toggle="tab" href="#home">Summary</a></li>
	  <li><a data-toggle="tab" href="#menu1">Provisioning</a></li>
	  <li><a data-toggle="tab" href="#menu2">Billing</a></li>
	  <li><a data-toggle="tab" href="#menu3">Dashboard</a></li>
	  <li><a data-toggle="tab" href="#menu4">Admin</a></li>
	</ul>

	<div class="tab-content">
	  <div id="home" class="tab-pane fade in active">
	    <h3>Summary</h3>
	    <p>Some content.</p>
	  </div>
	  <div id="menu1" class="tab-pane fade">
	    <h3>Provisioning</h3>
	    <p>Some content in provisioning.</p>
	  </div>
	  <div id="menu2" class="tab-pane fade">
	    <h3>Billing</h3>
	    <p>Some content in Billing.</p>
	  </div>
	  <div id="menu3" class="tab-pane fade">
	    <h3>Dashboard</h3>
	    <p>Some content in Dashboard.</p>
	  </div>
	  <div id="menu4" class="tab-pane fade">
	    <h3>Admin</h3>
	    <p>Some content in Admin.</p>
	  </div>
	</div>		</div>
	</div>


</body>
</html>