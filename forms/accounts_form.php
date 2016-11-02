<h2 style="font-weight: bold; margin-left: 10px;">Business Accounts</h2>
<div id="accountsArea" style="width: 100%; height: 100%; margin-top: -20px; backgroun-color: black;overflow:hidden">
    <table id="datatable_accounts" class="table" style="width: 100% !important;">
        <div class="mc_loading">
            <div class="bg"></div>
            <img src="img/loader1.gif">
        </div>
        <thead>
            <tr class="hidden">
                <td></td>
            </tr>
        </thead>
        <tbody>
        <?php   
            foreach ($result_db_customers['Items'] as $i) {
                $cust = $marshaler->unmarshalItem($i); ?>
                <tr style="margin-left: 10px;"><td class="oneAcc" onclick="oneAccount('<?=$cust["chargify_id"]?>')">
                <?php
                    echo '<span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> &nbsp;&nbsp;&nbsp;<strong style="font-size: 20px; color: #31708f;">'.$cust['business_name'].'</strong><br>
                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;'.$cust['customer_first_name'].' '.$cust['customer_last_name'].'&nbsp;&nbsp;&nbsp; 
                        <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp; '.$cust['business_email'].'';
                    echo '</td></tr>';
            } ?>
        </tbody>
    </table>
</div>
