</div>

<div><br/></div>
<h1>CREDIT SETTING</h1>

<?php 

    function generate_select($time, $name)
    {
        $hourmin = explode(':', $time);
        $hour = $hourmin[0];
        $min = $hourmin[1];

        $sel_hour = '<select name="'.$name.'Hour" style="width: 80px;">';
        for($i = 0; $i < 24; $i++){
            $a = ($i < 10)? 0:'';
            $b = ($hour == $i)? 'selected="selected"' : '';
            $sel_hour .= '<option value="'.$a.$i.'" '.$b.'>'.$a.$i.'</option>';
        }
        $sel_hour .= '</select>';

        $sel_min = '<select name="'.$name.'Min" style="width: 80px;">';
        for($i = 0; $i < 60; $i++){
            $a = ($i < 10)? 0:'';
            $b = ($min == $i)? 'selected="selected"' : '';
            $sel_min .= '<option value="'.$a.$i.'" '.$b.'>'.$a.$i.'</option>';
        }
        $sel_min .= '</select>';

        return $sel_hour .':'. $sel_min;
    }

    function generate_checkbox($active, $name)
    {
        $checked = ($active == 1) ? 'checked="checked"' : '';
        return '<input name="'.$name.'" type="checkbox" '.$checked.'>';
    }

?>



<?php $periods = json_decode($shutoff['shut_off_periods']); ?>
<?php //$periods = json_decode('{"Days":[{"Day":"Monday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Tuesday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Wednesday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Thursday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Friday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Saturday","Shut_Off_Start":"00:00","Shut_Off_End":"00:00","Active":0},{"Day":"Sunday","Shut_Off_Start":"00:00","Shut_Off_End":"00:00","Active":0}]}'); ?>

<form action="<?php echo URL::to('settings/credit_setting/change'); ?>" method="POST">
<table class="table table-bordered">

    <tr>
        <th>Day</th>
        <th>Shut off start</th>
        <th>Shut off end</th>
        <th>Active</th>
    </tr>

<?php foreach($periods as $k => $v): ?>
    <?php foreach($v as $r): ?>

    <tr>
        <td><?php echo $r->Day; ?></td>
        <td><?php echo generate_select($r->Shut_Off_Start, $r->Day.'Start'); ?></td>
        <td><?php echo generate_select($r->Shut_Off_End, $r->Day.'End'); ?></td>
        <td><?php echo generate_checkbox($r->Active, $r->Day.'Active'); ?></td>
    </tr>

<?php endforeach; ?>
<?php endforeach; ?>
</table>
<input type="submit" value="Change" class="btn btn-danger" style="float:right;">
</form>

<div class="cl">&nbsp;</div>
</div>