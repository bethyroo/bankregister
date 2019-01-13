<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page list.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <style type="text/css">
            body{
                background-color: #009999
            }
            td {
                vertical-align: top;
            }
            table table td {
                border-width: 0;
                border-style: none;
                border-color: gray;
                
            }
            table table {
                border-width: 6px;
                border-style: outset;
                border-color: gray;
                border-collapse: collapse;
            }
            th, tr.form {
                border-style: none;
                background-color: #ffff99;
                border-width: 0;
                border-color: #009900;
            }
            .odd {
                background-color: white;
            }
            .even {
                background-color: #ccccff;
                
            }
            .outstanding.even {
                background-color: #5eb;
            }
            .outstanding.odd {
                background-color: #77ffdd;
            }
            .negative {
                
            }
            .warning {
                
            }
            .fixed {
                display:block;
                width:100%;
            }
            .scroll {
                display: block;
                max-height:75vh;
                overflow: auto;
            }
            .cell1 {
                width: 8em;
            }
            .cell2 {
                width: 20em;
                overflow: hidden;
            }
            .cell3 {
                width: 8em;
            }
            .cell4 {
                width: 8em;
            }
            .cell5 {
                width: 12em;
            }
            
        </style>
        <script>
            function toggleTransfer(visible) {
                if(visible) {
                    document.getElementById('description').style.display = 'none';
                    document.getElementById('to').style.display = '';
                    document.getElementById('how').style.display = '';
                    
                } else {
                    document.getElementById('description').style.display = '';
                    document.getElementById('to').style.display = 'none';
                    document.getElementById('how').style.display = 'none';
                }
            }
        </script>
    </head>
    <body>
    <!--<img src="monkey.gif" alt="monkey" style="width:90px; height:90px;">-->
        <table>
            <tr>
                <td colspan="2">
                    <button type="button" onclick="window.location.href = '?page=logout'">Logout</button>
                    <button type="button" onclick="window.location.href = '?page=users'">Manage Users</button>
                    <button type="button" onclick="window.location.href = '?page=import'">Import Transactions</button>
                    <?php echo $message; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <form method="get" name="account_form" id="account_form">
                        <?php if(!count($accounts_array)) { ?>
                            No accounts.
                        <?php } else { ?>
                            <strong>Accounts</strong><br>
                            <?php foreach ($accounts_array as $account) { $sum += $account['total']; ?>
                            <input type="radio" name="aID" value="<?php echo $account['id']; ?>"
                                   <?php if($account['id'] == $aID) echo 'checked="checked"'; ?> 
                                   onclick="document.getElementById('account_form').submit()"/>
                                <label><?php echo $account['name'].' ('.$account['total'].')'; ?></label>
                                <br>
                            <?php } ?>
                                <label>Total all accounts: <?php echo $sum; ?></label>
                                <br>
                        <?php } ?>
                        <button type="button" onclick="window.location.href='?page=account'">Manage Accounts</button>
                    </form>
                </td>
                <td>
                    <form method="post" action="index.php">
                        <input type="hidden" name="aID" value="<?php echo $aID; ?>">
                        <table>
                        <thead class="fixed">
                            <th class="cell1">Date</th>
                            <th class="cell2">description</th>
                            <th class="cell3">Amount</th>
                            <th class="cell4">Balance</th>
                            <th class="cell5"></th>
                        </thead>
                        <tbody class="scroll" id="transactions">
                        <?php 
                        $total = 0;
                        foreach($transactions as $row) {
                            $total += $row['value'];
                            $row_last = $row['id'];
                            $odd = !$odd; 
                            $class = $odd?'odd':'even';
                            if($row['outstanding']) $class .= ' outstanding';
                            if($row['value'] < 0) $class .= ' negative';
                            if($total < 0 && $account_info['type'] == 'bank') $class .= ' warning';
                            ?>
                        <tr class="<?php echo $class; ?>" id="row_<?php echo $row['id']; ?>">
                            <td class="cell1"><?php echo $row['tran_date']; ?></td>
                            <td class="cell2"><?php echo $row['description']; ?></td>
                            <td class="cell3"><?php echo money_format('%#10n', $row['value']); ?></td>
                            <td class="cell4"><?php echo money_format('%#10n', $total); ?></td>
                            <td class="cell5"><button type="button" onclick="window.location.href='?action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">Edit</button></td>
                        </tr>
                        <?php } ?> 
                        </tbody>
                        <tbody class="fixed">
                            <tr class="form">
                                <td class="cell1">
                                    <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                    <input type="date" name="tran_date" value="<?php echo (isset($transaction['tran_date'])?$transaction['tran_date']:date('Y-m-d')); ?>">
                                </td>
                                <td class="cell2">
                                    <input type="text" id="description" name="description" value="<?php echo $transaction['description']; ?>" size="30" placeholder="Description">
                                    <!-- Transfer form -->
                                    <select name="how" id="how" style="display:none;">
                                        <option value="1" selected="selected">To</option>
                                        <option value="0">From</option>
                                    </select>
                                    <select name="to" id="to" style="display:none;">
                                        <?php foreach($accounts_array as $account) { 
                                            echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <!-- End transfer form -->
                                    <?php if($transaction['id'] && !$transaction['link']) { ?>
                                    <select name="account">
                                        <?php foreach($accounts_array as $account) { 
                                            echo '<option value="'.$account['id'].'"'.($account['id']==$aID?' selected="selected"':'').'>'.$account['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php } else { ?>
                                    <input type="hidden" name="account" value="<?php echo $aID; ?>">
                                    <?php } ?>
                                </td>
                                <td class="cell3"><input type="text" name="value" value="<?php echo $transaction['value']; ?>" placeholder="$ 0.00" size="8"></td>
                                <td class="cell4"><input type="checkbox" name="outstanding" value="1"<?php echo ($transaction['outstanding']?'checked=checked':''); ?>" size="8"><label>Outstanding</label></td>
                                <td class="cell5">
                                    <input type="submit" name="action" value="<?php echo $transaction['id']?'save':'add';?>">
                                    <?php if($transaction['id']) { ?>
                                    <input type="submit" name="action" value="delete">
                                    <?php } else { ?>
                                    <input type="checkbox" name="transfer" id="transfer" value="1" onchange="toggleTransfer(this.checked)">
                                    <label>Transfer</label>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </form>
              </td>
            </tr>
        </table>
        <script>
            document.getElementById('transactions').scrollTop = 1000000;
        </script>
    </body>
</html>