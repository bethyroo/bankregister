<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page account_list.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<span id="title"><?php 
switch($_REQUEST['page']) {
    case 'account':
        echo "Edit Accounts";
        break;
    case 'recurring':
        echo "Recurring Transactions";
        break;
    case 'statement':
        echo "Statement";
        break;
    case 'transaction':
    default:
        echo "Transactions";
        break;
}
?>
</span>
<div id="accounts_list">
    <img src="monkey.gif" style="width:180px;" id="monkey">
    <ul id="list_item">
        <?php if(count($accounts_array)) foreach ($accounts_array as $account) { $sum += $account['total']; ?>
        <li<?php if($account['id'] == $aID) echo ' class="selected"'; ?>>
            <a href="<?php echo query_string(array('aID'), array('aID'=>$account['id'])); ?>">
                <?php echo $account['name']; ?>
                <br>
                <?php if($_REQUEST['page']!= 'recurring') { ?>
                <span class="balance">Total: $<?php echo $account['total']; ?></span>
                <span class="available">Available: $<?php echo $account['available']; ?></span>
                <?php } else { ?>
                <span class="available"><?php echo $account['recurring']; ?></span>
                <?php } ?>
                <img src="arrow.png">
            </a>
        </li>
        <?php } ?>
        <li class="empty">Total all accounts: <?php echo $sum; ?></li>
        <li>
            <a href='?page=account&action=new'>Add Account<img src="arrow.png"></a>
        </li>
    </ul>
</div>
