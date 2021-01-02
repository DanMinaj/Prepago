                            
</div>

<div><br/></div>

<h1>Issue Top-Up Arrears</h1>
@include('includes.search_form', array('searchURL'=> URL::to('issue_topup_arrears/search_customers') ))

<p>This allows admins to issue credit to a customer whereby the customer can pay the credit back over a period of time. For example if the admin credits €100 to the account at a rate of €1 per day it will take 100 days to pay the credit back.</p>

<table class="table table-bordered">
    <tr>
        <th>Name</th>
        <th>Barcode</th>
        <th>Email</th>
        <th>Mobile</th>
        <th><br></th>
    </tr>
    <?php foreach ($customers as $customer): ?>
        <tr style="text-align: center;">
            <td><?php echo $customer->first_name . " " . $customer->surname; ?></td>
            <td><?php echo $customer->barcode; ?></td>
            <td><?php echo $customer->email_address; ?></td>
            <td><?php echo $customer->mobile_number; ?></td>
            <td><a href="<?php echo URL::to('issue_topup_arrears/issue_topup_arrears_amount/'.$customer->id); ?>" class="btn btn-primary">Issue Credit</a></td>
        </tr>
        

    <?php endforeach; ?>
</table>