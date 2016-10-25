<div class="tab-pane fade" id="provisioning">
<form id="cust_provisioning_form" action="" method="POST" style="margin-top: -2.75em;">
     
     
        <div class="grid_9">
            <div class="grid_9 alpha">
                <label class="<?php echo $check; ?>">Business Name<label class="error rem-bor" for="bname" generated="true"></label></label>
                <input type="text" class="form-control" name="bname" placeholder="Business Name" value="<?php echo $business_name; ?>">
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_4 alpha">
                <label class="<?php echo $check; ?>">Sales Center</label>
                <input type="text" class="form-control" name="sctr" placeholder="Sales Center" value="<?php echo $sales_center; ?>" readonly>
            </div>
            <div class="grid_1">
                <center><label style="margin-top:.5em;"> Sale Date </label></center>
                <p style="text-align: center;"><b><?php echo $sales_date; ?></b></p>
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Sales Agent</label>
                <input type="text" class="form-control" name="sagnt" placeholder="Sales Agent" value="<?php echo $sales_agent; ?>" readonly>
            </div>
        </div>
        <div class="grid_9">
            <div class="dropdown grid_2 alpha">
                <label>Cancelled?</label><br/>
                <?php if($cancelled == "yes") { ?>
                    <label class="radio-inline"><input type="radio" name="cancel" id="cancel_yes" value="yes" checked="checked" onclick="cancelYes()">Yes</label>
                    <label class="radio-inline"><input type="radio" name="cancel" id="cancel_no" value="no" onclick="cancelNo()">No</label>
                <?php } else { ?>
                    <label class="radio-inline"><input type="radio" name="cancel" id="cancel_yes" value="yes" onclick="cancelYes()">Yes</label>
                    <label class="radio-inline"><input type="radio" name="cancel" id="cancel_no" value="no" checked="checked" onclick="cancelNo()">No</label>
                <?php } ?>
            </div>
            <div class="grid_5">
                <label class="<?php echo $check; ?>">Cancellation Reason</label>
                <textarea class="form-control" rows="5" name="cancel_reason" id="cancel_reason" placeholder="Cancel Reason" style="resize: vertical;" value="<?php echo $cancel_reason; ?>"><?php echo $cancel_reason; ?></textarea>
            </div>
            <div class="grid_2 omega">
                <label class="<?php echo $check; ?>">Refund Amount</label>
                <input type="text" class="form-control check-fill" placeholder="Refund Amount">
            </div>
        </div>

        

        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Business Category</label>
                <select class="form-control" name="b-category">
                <?php 
                if(isset($_GET['id'])) {
                    echo "<optgroup label='Current'>";
                    if(empty($business_category)) { 
                        echo "<option value=''>None</option>";
                    } else {
                        echo "<option value='".$business_category."'>".$business_category."</option>"; 
                    }
                    echo "</optgroup>";
                ?>
                <optgroup label="Categories">
                    <option value="Automotive Services">Automotive Services</option>
                    <option value="Business Services">Business Services</option>
                    <option value="Food &amp; Beverage">Food &amp; Beverage</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Household Services">Household Services</option>
                    <option value="Lawn &amp; Garden Services">Lawn &amp; Garden Services</option>
                    <option value="Mobile Services">Mobile Services</option>
                    <option value="Personal Services">Personal Services</option>
                    <option value="Retail Establishment">Retail Establishment</option>
                </optgroup>
                <?php 
                } else {
                    echo "<option value='' disabled selected>Business Category</option>";
                }
                ?>
                </select>
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Existing Website</label>
                <input type="text" class="form-control check-fill" name="b-site" placeholder="Existing Website" value="<?php echo $business_website; ?>">
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Primary Email<label class="error rem-bor" for="b_email" generated="true"></label></label>
                <input type="text" class="form-control check-fill" name="b_email" placeholder="Primary Email" value="<?php echo $business_email; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Gmail Account</label>
                <input type="text" class="form-control check-fill" name="b-gmail" placeholder="Gmail Account" value="<?php echo $gmail_acc; ?>">
            </div>
        </div>
        
        <div class="grid_9">
            <div class="grid_9 alpha">
                <div class="form-group">
                  <label for="comment">Keywords:</label></span>
                  <input type="text" class="tags" name="k-words" id="k-words" value="<?php echo $keywords; ?>">
                </div>
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_4 alpha">
                <label class="<?php echo $check; ?>">Office Address 1</label>
                <input type="text" class="form-control check-fill" name="b-address1" placeholder="Office Address 1" value="<?php echo $business_address; ?>">
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Office Address 2</label>
                <input type="text" class="form-control check-fill" name="b-address2" placeholder="Office Address 2" value="<?php echo $business_address_2; ?>">
            </div>
            <div class="grid_2 omega">
            <label class="<?php echo $check; ?>">Post Address?</label>
                <select class="form-control" name="b-post-address">
                    <?php if(isset($_GET['id'])) { 
                        echo "<optgroup label='Show Address?'>";
                        if($business_post_address == 'yes') { 
                            echo "<option value='".$business_post_address."'>Yes</option>
                                <option value='no'>No</option>";
                        } else { 
                            echo "<option value='".$business_post_address."'>No</option>
                                <option value='yes'>Yes</option>";
                        }
                        echo "</optgroup>"; 
                    } else {
                        echo "<option value='' disabled selected>Show Address?</option>";
                    }?>
                 </select>
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Office City</label>
                <input type="text" class="form-control check-fill" name="b-city" placeholder="Office City" value="<?php echo $business_city; ?>">
            </div>
            <div class="grid_2">
                <label class="<?php echo $check; ?>">Office State</label>
                <select class="form-control" name="b-state">
                <?php if(isset($_GET['id'])) {
                    echo "<optgroup label='Current'>
                        <option value='".$business_state."'>".$business_state."</option>
                        </optgroup>";
                ?>
                    <optgroup label='States'>
                        <option value="AL">AL</option> 
                        <option value="AK">AK</option>
                        <option value="AZ">AZ</option> 
                        <option value="AR">AR</option> 
                        <option value="CA">CA</option> 
                        <option value="CO">CO</option> 
                        <option value="CT">CT</option> 
                        <option value="DE">DE</option> 
                        <option value="DC">DC</option> 
                        <option value="FL">FL</option> 
                        <option value="GA">GA</option> 
                        <option value="HI">HI</option> 
                        <option value="ID">ID</option> 
                        <option value="IL">IL</option> 
                        <option value="IN">IN</option> 
                        <option value="IA">IA</option> 
                        <option value="KS">KS</option> 
                        <option value="KY">KY</option> 
                        <option value="LA">LA</option> 
                        <option value="ME">ME</option> 
                        <option value="MD">MD</option> 
                        <option value="MA">MA</option> 
                        <option value="MI">MI</option> 
                        <option value="MN">MN</option> 
                        <option value="MS">MS</option> 
                        <option value="MO">MO</option> 
                        <option value="MT">MT</option> 
                        <option value="NE">NE</option> 
                        <option value="NV">NV</option> 
                        <option value="NH">NH</option> 
                        <option value="NJ">NJ</option> 
                        <option value="NM">NM</option> 
                        <option value="NY">NY</option> 
                        <option value="NC">NC</option> 
                        <option value="ND">ND</option> 
                        <option value="OH">OH</option> 
                        <option value="OK">OK</option> 
                        <option value="OR">OR</option> 
                        <option value="PA">PA</option> 
                        <option value="RI">RI</option> 
                        <option value="SC">SC</option> 
                        <option value="SD">SD</option> 
                        <option value="TN">TN</option> 
                        <option value="TX">TX</option> 
                        <option value="UT">UT</option> 
                        <option value="VT">VT</option> 
                        <option value="VA">VA</option> 
                        <option value="WA">WA</option> 
                        <option value="WV">WV</option> 
                        <option value="WI">WI</option> 
                        <option value="WY">WY</option>
                    </optgroup>
                <?php } else {
                    echo "<option value='' disabled selected>Office State</option>";
                } ?>
                </select>
            </div>
            <div class="grid_2">
                <label class="<?php echo $check; ?>">Office Zip Code</label>
                <input type="text" class="form-control check-fill" name="b-zip" placeholder="Office Zip Code" value="<?php echo $business_zip; ?>">
            </div>
            <div class="grid_2 omega">
                <label class="<?php echo $check; ?>">Office Country</label>
                <input type="text" class="form-control check-fill" name="b-country" placeholder="Office Country" value="<?php echo $business_country; ?>">
            </div>
        </div>

            
        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Hours of Operation</label>
                <input type="text" class="form-control check-fill" name="b-hours" placeholder="Hours Of Operation" value="<?php echo $business_hours; ?>">
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Payment Accepted</label>
                <input type="text" class="form-control check-fill" name="payment" placeholder="Payment Accepted" value="<?php echo $payment_method; ?>">
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Special Request</label>
                <input type="text" class="form-control check-fill" name="request" placeholder="Special Request" value="<?php echo $sp_request; ?>">
            </div>
        </div>

        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Office/Business Phone</label>
                <input type="text" class="form-control check-fill" name="b-phone" placeholder="Office/Business Phone" value="<?php echo $business_phone; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Alternate Phone</label>
                <input type="text" class="form-control check-fill" name="b-alt-phone" placeholder="Alternate Phone" value="<?php echo $business_alt_phone; ?>">
            </div>
        </div>

        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Existing Social 1</label>
                <input type="text" class="form-control check-fill" name="b-social1" placeholder="Existing Social 1" value="<?php echo $social1; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Existing Social 2</label>
                <input type="text" class="form-control check-fill" name="b-social2" placeholder="Existing Social 2" value="<?php echo $social2; ?>">
            </div>
        </div>

        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Biglo Website</label>
                <input type="text" class="form-control check-fill" name="biglo-site" placeholder="BigLo Website" value="<?php echo $biglo_site; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Analytical Address</label>
                <input type="text" class="form-control check-fill" name="analyt-add" placeholder="Analytical Address" value="<?php echo $analytical_address; ?>">
            </div>
        </div>

        
        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Google Plus</label>
                <input type="text" class="form-control check-fill" name="gplus" placeholder="Google +" value="<?php echo $google_plus; ?>">
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Google Maps</label>
                <input type="text" class="form-control check-fill" name="gmap" placeholder="Google Maps" value="<?php echo $google_maps; ?>">
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Facebook</label>
                <input type="text" class="form-control check-fill" name="fb" placeholder="Facebook" value="<?php echo $facebook; ?>">
            </div>
        </div>

        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Four Square</label>
                <input type="text" class="form-control check-fill" name="foursq" placeholder="Four Square" value="<?php echo $foursquare; ?>">
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Twitter</label>
                <input type="text" class="form-control check-fill" name="twit" placeholder="Twitter" value="<?php echo $twitter; ?>">
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">LinkedIn</label>
                <input type="text" class="form-control check-fill" name="linkedin" placeholder="LinkedIn" value="<?php echo $linkedin; ?>">
            </div>
            <!--hidden ticket button for medium to large screens-->
             
        </div>
        
        <!--
        <div class="row">
            <div class="col-md-12">
                <div class="progress">
                    <div class="progress-bar progress-bar-success" style="width: 35%">
                        <span class="sr-only">35% Complete (success)</span>
                    </div>
                    <div class="progress-bar progress-bar-warning" style="width: 20%">
                        <span class="sr-only">20% Complete (warning)</span>
                    </div>
                    <div class="progress-bar progress-bar-danger" style="width: 10%">
                        <span class="sr-only">10% Complete (danger)</span>
                    </div>
                </div>
            </div>
        </div>
        -->
        <br><br>
        <div id="myProgressbar" class="progress">
            <div id="bar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                <span class="count"></span>
            </div>
        </div>

        <div class="grid_9">
            <div class="grid_9 alpha">
                <button class="btn btn-danger" type="submit" name="upd_prov">Update</button>
            </div>
        </div>
    </form>
</div>