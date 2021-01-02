                            
</div>

<div><br/></div>

<h1>Issue Admin IOU</h1>
            
@include('includes.search_form', array('searchURL'=> URL::to('issue_admin_iou/search_customers') ))   


<p>This allows admins to Issue a customer an Admin IOU. IOU’s must be settled the next time the customer tops up. Please note that IOU’s work on a negative balance. If a customer has a €5 IOU they may reach a balance of -€5.</p>

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
            <td><a href="<?php echo URL::to('issue_admin_iou/issue_admin_iou_amount/'.$customer->id); ?>" class="btn btn-primary">Issue Credit</a></td>
        </tr>
        

    <?php endforeach; ?>
</table>
