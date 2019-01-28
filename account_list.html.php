<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page account_list.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<div id="accounts_list">
    <img src="monkey.gif" style="width:180px;" id="monkey">
    <ul id="list_item">
        <?php if(count($accounts_array)) foreach ($accounts_array as $account) { $sum += $account['total']; ?>
        <li<?php if($account['id'] == $aID) echo ' class="selected"'; ?>>
            <a href="<?php echo query_string(array('aID'), array('aID'=>$account['id'])); ?>">
                <?php echo $account['name']; ?>
                <br>
                <span class="balance">Total: $<?php echo $account['total']; ?></span>
                <span class="available">Available: $<?php echo $account['available']; ?></span>
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
