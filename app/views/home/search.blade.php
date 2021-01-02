  </div>

<div><br/></div>

  <h1>Result ({{ count($customers) }})
  @include('includes.search_form', array('searchURL'=> URL::to('search') ))
  </h1>

  <div class="admin">
      <?php
      if($customers==""){
        echo "There is no customer data to show";
    }else{ ?>
    <table class="table table-bordered">
        <th>Barcode</th>
        <th>Username</th>
        <th>Email Address</th>
        <th>Mobile Number</th>
        <th>First Name</th>
        <th>Surname</th>

        <th>Address</th>

        <th>Nominated Mobile Phone</th>
        <th>View Profile</th>
        <?php
        foreach ($customers as $type){ ?>
        <tr>
            <td><?php echo $type['barcode']?></td>
            <td><?php echo $type['username']?></td>
            <td><?php echo $type['email_address']?></td>
            <td><?php echo $type['mobile_number']?></td>
            <td><?php echo $type['first_name']?></td>
            <td><?php echo $type['surname']?></td>
            
            <td><?php echo $type['house_number_name'] . ', ' .$type['street1'] . ', ' .$type['street2'] . ', ' .$type['town'] . ', ' .$type['county']; ?></td>

            <td><?php echo $type['nominated_telephone']?></td>
            <td><a  class="btn btn-info" type="button" href="<?php echo URL::to('customer_tabview_controller/show/'.$type['id']) ?>">View</a></td>
        </tr>
        <?php } ?>
    </table>

    <?php } ?>
</div>