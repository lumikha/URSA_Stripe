<?php
    require 'header.php';

    if(isset($_POST['submit_ticket'])) {
        echo $_POST['bname']."<br/>";
        echo $_POST['tbc_date']."<br/>";
        echo $_POST['sctr']."<br/>";
        echo $_POST['sagnt']."<br/>";
        echo $_POST['sagnt']."<br/>";
    }
?>

    <form id="sales_form" action="" method="POST" style="margin-top: -60px;">
        <div class="row">
            <div class="col-md-1" style="float: right;">
                <button class="btn btn-danger" type="submit" name="submit_ticket">Ticket</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" name="bname" placeholder="Business Name" value="<?php echo $business_name; ?>">
            </div>
            <div class="col-md-2">
                <p><?php echo $sales_date; ?></p>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="tbc_date" placeholder="Trial/Bill/Cancel Date" value="<?php echo $cust_search_state; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" name="sctr" placeholder="Sales Center" value="<?php echo $sales_center; ?>" readonly>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="sagnt" placeholder="Sales Agent" value="<?php echo $sales_agent; ?>" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <select class="form-control">
                    <optgroup label="Current">
                    <?php
                        echo "<option value='".$result_customer_id[0]->product->handle."'>".$result_customer_id[0]->product->name."</option>";
                    ?>
                    </optgroup>
                    <optgroup label="Available Plans">
                        <option value="prod_001">Basic Plan</option>
                        <option value="plan_002">Start-up Plan</option>
                        <option value="plan_005">Upgrade to Start-up Plan</option>
                        <option value="plan_003">Business Plan</option>
                        <option value="plan_006">Upgrade to Business Plan</option>
                        <option value="plan_004">Enterprise Plan</option>
                        <option value="plan_007">Upgrade Enterprise Plan</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-control">
                    <optgroup label="Current">
                    <?php
                        if(empty($result_customer_id[0]->components)) { 
                            echo "<option value=''>None</option>";
                        } else {
                            echo "<option value='".$result_customer_id[0]->components->name."'>".$result_customer_id[0]->components->id."</option>";
                        }
                    ?>    
                    </optgroup>
                    <optgroup label="Available Components">
                        <option value="196368">Custom Company Domain</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-control">
                    <optgroup label="Current">
                    <?php
                        if(empty($result_customer_id[0]->coupon_code)) { 
                            echo "<option value=''>None</option>";
                        } else {
                            echo "<option value='".$result_customer_id[0]->coupon_code."'>".$result_customer_id[0]->coupon_code->name."</option>"; 
                        }
                    ?>    
                    </optgroup>
                    <optgroup label="Available Coupons">
                        <option value="SAVE50">Discount Coupon</option>
                        <option value="FREDOM">Domain Coupon</option>
                        <option value="REFER">Referral Coupon</option>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="dropdown col-md-4">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    Cancellation Reason
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Refund Amount">
            </div>
        </div>
    </form>

<?php
    require "footer.php";
?>