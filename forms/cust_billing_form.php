<div class="tab-pane fade" id="billing">
<form id="cust_billing_form" action="" method="POST" style="margin-top: -60px;">
        <div class="row">
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">Payment Processor ID</label>
                <input type="text" name="ppID" id="ppID" class="form-control" value="<?php echo $chargifyID; ?>" placeholder="Payment Processor ID ">
            </div>
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">Billing Status</label>
                <select class="form-control" name="bill_stat">
                <?php if(isset($_GET['id'])) { ?>
                    <optgroup label="Current"> 
                        <option id="bill_stat"></option>
                    </optgroup>
                    <optgroup label="Status"> 
                        <option value="trialing">Trialing</option>
                        <option value="active">Active</option>
                        <option value="past_due">Past Due</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="canceled">Canceled</option>
                        <option value="delinquent">Delinquent</option>
                    </optgroup>
                <?php } else {
                    echo "<option value='' disabled selected>Status</option>";
                } ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="<?php echo $check; ?>">Billing Cycles</label>
                <input type="number" name="bill_cycle" class="form-control" placeholder="Successful Billing Cycles">
            </div>
            <div class="col-md-1">
                <span><?php echo $cust_search_state; ?></span>
            </div>
            <div class="col lg-1 col-md-1 col-sm-3 col-xs-3">
                <label class="<?php echo $check; ?>">MM</label>
                <input type="text" class="form-control" name="bill-d1" style="width:50px; margin-left: -15px; margin-right: 30px;" value="<?php echo $state_date[1]; ?>" >
            </div>
            <div class="col-lg-1 col-md-1 col-sm-3 col-xs-3">
                <label class="<?php echo $check; ?>">DD</label>
                <input type="text" class="form-control" name="bill-d2" value="<?php echo $state_date[2]; ?>" style="margin-left:-15px; width:50px;">
            </div>
            <div class="col-lg-1 col-md-1 col-sm-3 col-xs-3">
                <label class="<?php echo $check; ?>">YYYY</label>
                <input type="text" class="form-control" name="bill-d3" value="<?php echo $state_date[0]; ?>" style="margin-left: -15px; width: 70px;">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="<?php echo $check; ?>">Product</label>
                <select class="form-control" name="product">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>
                            <option value='".$product_handle."'>".$product_name."</option>
                        </optgroup>";
                    ?>
                    <optgroup label="Available Plans">
                        <option value="prod_001">Basic Plan</option>
                        <option value="plan_002">Start-up Plan</option>
                        <option value="plan_005">Upgrade to Start-up Plan</option>
                        <option value="plan_003">Business Plan</option>
                        <option value="plan_006">Upgrade to Business Plan</option>
                        <option value="plan_004">Enterprise Plan</option>
                        <option value="plan_007">Upgrade Enterprise Plan</option>
                    </optgroup>
                    <?php
                    } else {
                        echo "<option value='' disabled selected>Product</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="<?php echo $check; ?>">Component Quantity(Custom Domains)</label>
                <select class="form-control" name="comp_quantity">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>";
                        if(empty($product_component_quantity)) { 
                            echo "<option value=''>None</option>";
                        } else {
                            echo "<option value='".$product_component_quantity."'>".$product_component_quantity."</option>";
                        }
                        echo "</optgroup>";
                    ?>
                    <optgroup label="Quantity">
                        <?php
                            $count_comp=0;
                            while($count_comp != 10) {
                                echo "<option value='".$count_comp."'>".$count_comp."</option>";
                                $count_comp++;
                            }
                        ?>
                    </optgroup>
                    <?php
                    } else {
                        echo "<option value='' disabled selected>Component</option>";
                    }
                    ?>  
                </select>
            </div>
            <div class="col-md-4">
                <label class="<?php echo $check; ?>">Coupon</label>
                <select class="form-control" name="coupon">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>";
                        if(empty($product_coupon_code)) { 
                            echo "<option value=''>None</option>";
                        } else {
                            echo "<option value='".$product_coupon_code."'>".$product_coupon_name."</option>"; 
                        }
                         echo "</optgroup>";
                    ?>    
                    </optgroup>
                    <optgroup label="Available Coupons">
                        <option value="SAVE50">Discount Coupon</option>
                        <option value="FREDOM">Domain Coupon</option>
                        <option value="REFER">Referral Coupon</option>
                    </optgroup>
                     <?php
                    } else {
                        echo "<option value='' disabled selected>Coupon</option>";
                    }
                    ?>  
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="<?php echo $check; ?>">CC Masked Number</label>
                <input type="text" name="ccnumber" class="form-control" placeholder="Credit Card Masked Number" value="<?php echo $cc_last_four; ?>">
            </div>
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">CC Exp Month</label>
                <input type="text" name="ccexpm" class="form-control" placeholder="CC Expiration Month" value="<?php echo $cc_exp_mm; ?>">
            </div>
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">CC Exp Year</label>
                <input type="text" name="ccexpy" class="form-control" placeholder="CC Expiration Year" value="<?php echo $cc_exp_yy; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">Billing Address</label>
                <input type="text" name="bill_address" class="form-control" placeholder="Billing Address Street" value="<?php echo $bill_address; ?>">
            </div>
            <div class="col-md-3">
                <label class="<?php echo $check; ?>">Billing City</label>
                <input type="text" name="bill_city" class="form-control" placeholder="Billing City" value="<?php echo $bill_city; ?>">
            </div>
            <div class="col-md-2">
                <label class="<?php echo $check; ?>">Billing State</label>
                <input type="text" name="bill_state" class="form-control" placeholder="State" value="<?php echo $bill_state; ?>">
            </div>
            <div class="col-md-2">
                <label class="<?php echo $check; ?>">Billing Zipcode</label>
                <input type="text" name="bill_zip" class="form-control" placeholder="Billing Postcode" value="<?php echo $bill_zip; ?>">
            </div>
            <div class="col-md-2">
                <label class="<?php echo $check; ?>">Billing Country</label>
                <input type="text" name="bill_country" class="form-control" placeholder="Billing Country" value="<?php echo $bill_country; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 col-md-offset-5 col-sm-1 col-sm-offset-5 col-xs-1 col-xs-offset-4">
                <button class="btn btn-danger" type="submit" name="upd_bill">Update</button>
            </div>
        </div>
    </form>
    </div>