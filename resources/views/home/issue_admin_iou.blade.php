                            
</div>

<div><br/></div>

<h1>Issue Admin IOU</h1>
@include('includes.search_form', array('searchURL'=> URL::to('issue_admin_iou/search_customers') ))

<p>This allows admins to Issue a customer an Admin IOU. IOU’s must be settled the next time the customer tops up. Please note that IOU’s work on a negative balance. If a customer has a €5 IOU they may reach a balance of -€5.</p>
