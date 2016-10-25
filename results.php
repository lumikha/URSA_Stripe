<?php
	$search = @$_GET['search'];
?>
<html>
<head>
	<title>LiLDip | Search</title>
</head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	 <!--960 grid stylesheet links-->

    <link href="css/960.css" rel="stylesheet"/>
    <link href="css/reset.css" rel="stylesheet"/>
    <link href="css/text.css" rel="stylesheet"/>
    <link rel="stylesheet" href="style.css"/>
   
   <!--media queries-->
    
   <!--media queries-->
   <link rel="stylesheet" type="text/css" href="css/ursa/mediaquery.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	<script type="text/javascript" src="js/angular.min.js"></script>


	<style type="text/css">
		[ng-cloak]
		{
		 display: none !important;
		}

	</style>

<script type="text/javascript">
	var myapp = angular.module("myapp",[]);
		myapp.controller("newController", function($scope,$http,$filter){
			$http.get("dynamoDB/search.php").success(function(response){
				$scope.users = response;
			});
			$scope.num = 10;
			//$scope.search = "<?php echo $search; ?>";

			$scope.currentPage = 0;
    		$scope.pageSize = 10;
    		$scope.search = '<?php echo $search; ?>';

    		$scope.getData = function () {
		      return $filter('filter')($scope.users, $scope.search)
		    }
		    $scope.numberOfPages=function(){
        		return Math.ceil($scope.getData().length/$scope.pageSize);                
    		}
 

		});
		myapp.filter('startFrom', function() {
		    return function(input, start) {
		        start = +start; //parse to int
		        return input.slice(start);
		    }
		});

	$(document).ready(function() {
	  // toggle advanced search
	  $("#toggleContainer").hide();
	  $("#trigger").click(function() {
	    $("#toggleContainer").toggle();
	  });
	});


	
	
</script>
<body>
		<div ng-app="myapp">
			<div ng-controller="newController">
			<div class="well-lg">
			<div class="container_12 results">
				<div class="grid_12 alpha">
					<form name="myForm">
						<input type="text" class="form-control" ng-keypress="currentPage=0" onkeyup="return check();" id="search" size="30" name="search" ng-model="search" placeholder="Search">
					</form>
					<span ng-hide="!search">
					<h4><strong>Results:</strong></h4><br>
					</span>
					<span ng-hide="search">
						<div class="panel panel-info">
							<div class="panel-body">
								<h3 class="text-info text-center">
									<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
									: Business Name, Customer Name, Email, Contacts, Chargify ID.
								</h3><br>
							</div>
						</div>
					</span>
					<span name="output" id="output" ng-cloak>
							<ul style="list-style: none;">
								<li ng-if="search" ng-repeat="user in result = (users | filter:search | startFrom:currentPage*pageSize | limitTo:pageSize)">
									<a href="customer?id={{ user.chargify_id.S }}" name="value"><h3>
									{{user.business_name.S}}</h3></a>
									<strong>Chargify ID:</strong> {{user.chargify_id.S}}, <strong>Customer:</strong> <a href="customer?id={{ user.chargify_id.S }}">{{user.customer_first_name.S}} {{user.customer_last_name.S}}</a>, <strong>Email:</strong> {{user.business_email.S}}
									<hr class="featurette-divider">
								</li>
								<span ng-hide="result.length || !search">
									<h6 class="text-info">
										<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
										: Business Name, Customer Name, Email, Contacts, Chargify ID.
									</h6>
									<h3><p>Opps, No Results Found ...</p></h3>
									<br>
								</span>
							</ul>
					</span> 
					<br>
						<span ng-hide="!search" ng-cloak>
						<button class="btn btn-info" ng-disabled="currentPage == 0" ng-click="currentPage=currentPage-1">
					        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
					    </button>
							<span> Page {{currentPage+1}} of {{numberOfPages()}} </span>
						<button class="btn btn-info" ng-disabled="currentPage >= getData().length/pageSize - 1" ng-click="currentPage=currentPage+1">
					        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
					    </button>
						</span>
				</div>
			</div>
			</div>
			</div>
		</div>

</body>
</html>