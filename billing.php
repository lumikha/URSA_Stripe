<?php
    require 'functions.php';
    require 'header.php';
?>

    <form id="billing_form">
        <div class="row">
            <div class="col-md-7">
                <input type="text" class="form-control" placeholder="Payment Processor ID ">
            </div>
            <div class="col-md-3">
                <select class="form-control">
                    <option>Trial</option>
                    <option>Active</option>
                    <option>Delinquent</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" placeholder="Successful Billing Cycles">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                 <select class="form-control">
                 <option>Product 1</option>
                 <option>Product 2</option>
                 <option>Product 3</option>
                 <option>Product 4</option>
                 </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Credit Card Masked Number">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Credit Card Expiration Month">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Credit Card Expiration Year">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Billing Address Street">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Billing City">
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" placeholder="State">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" placeholder="Billing Postcode">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" placeholder="Billing Country">
            </div>
        </div>
    </form>

<?php
    require "footer.php";
?>
