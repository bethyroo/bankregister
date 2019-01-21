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
        <link rel="stylesheet" type="text/css" href="main.css">
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
            function toggleHighlight(item) {
                item.classList.toggle('highlight');
            }
        </script>
    </head>
    <body id="list">
    
        <table>
            <tr>
                <td colspan="2">
                    <div class="nav">
                    <button type="button" onclick="window.location.href = '?page=logout'">Logout</button>
                    <button type="button" onclick="window.location.href = '?page=users'">Manage Users</button>
                    <button type="button" onclick="window.location.href = '?page=import'">Import Transactions</button>
                    <button type="button" onclick="window.location.href = '?page=recurring'">Recurring Transactions</button>
                    </div>
                    <?php echo $message; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <img src="monkey.gif" style="width:180px;">
                    <form method="get" name="account_form" id="account_form">
                        <?php if(!count($accounts_array)) { ?>
                        <h2>No accounts.</h2>
                        <?php } else { ?>
                            <h2>Accounts</h2>
                            <?php foreach ($accounts_array as $account) { $sum += $account['total']; ?>
                            <button type="submit" name="aID" value="<?php echo $account['id']; ?>"
                                   <?php if($account['id'] == $aID) echo 'class="selected"'; ?> 
                                   onclick="document.getElementById('account_form').submit()">
                                <?php echo $account['name'].' ('.$account['total'].')'.' <span class="available">'.$account['available'].'</span>'; ?>
                            </button>
                            <?php } ?>
                                <h2>Total all accounts: <?php echo $sum; ?></h2>
                        <?php } ?>
                        <button type="button" onclick="window.location.href='?page=account'">Manage Accounts</button>
                    </form>
                </td>
                <td>
                    <?php if($recurring) { ?>
                    <span id="recurring">
                        There are <?php echo $recurring; ?> recurring transactions. 
                        <button type="button" onclick="window.location.href='?page=recurring&action=perform'">Perform</button>
                    </span>
                    <br>
                    <?php } ?>
                    <form method="post" action="index.php">
                        <input type="hidden" name="aID" value="<?php echo $aID; ?>">
                        <table>
                        <thead class="fixed">
                            <th class="cell1">Date</th>
                            <th class="cell2">description</th>
                            <th class="cell3">Amount</th>
                            <th class="cell4">Balance</th>
                            <th class="cell5">Statement</th>
                        </thead>
                        <tbody class="scroll" id="transactions">
                        <?php 
                        $total = 0;
                        foreach($transactions as $row) {
                            // track total
                            if($row['statement']=='0') $total += $row['value'];
                            // track outstanding
                            if($row['outstanding']) $outstanding += $row['value'];
                            // imbalance amount
                            if($row['statement'] && $total-$outstanding != $row['value']) $imbalance = $total-$outstanding-$row['value'];
                            // class odd/even
                            $odd = !$odd; 
                            $class = $odd?'odd':'even';
                            // special classes
                            if($row['outstanding']) $class .= ' outstanding';
                            if($row['value'] < 0) $class .= ' negative';
                            if($total < 0 && $account_info['type'] == 'bank') $class .= ' warning';// overdrawn
                            if($row['id'] == $transaction['id']) $class .= ' edit';// line being edited
                            if($row['statement']) {
                                $class .= ' statement';
                                if($total-$outstanding != $row['value']) $class .= ' imbalance';
                            }
                            ?>
                            <tr class="<?php echo $class; ?>" id="row_<?php echo $row['id']; ?>" onclick="toggleHighlight(this)" ondblclick="window.location.href='?action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">
                            <td class="cell1"><?php echo $row['tran_date']; ?></td>
                            <td class="cell2"><?php echo $row['statement']?'Outstanding:'.money_format('%#10n',$outstanding):$row['description']; ?></td>
                            <td class="cell3"><?php echo $row['statement']?$imbalance:money_format('%#10n', $row['value']); ?></td>
                            <td class="cell4"><?php echo money_format('%#10n', $row['statement']?$row['value']:$total); ?></td>
                            <td class="cell5">
                                <button type="button" onclick="window.location.href='?action=edit&aID=<?php echo $aID; ?>&id=<?php echo $row['id']; ?>'">Edit</button>
                                <?php if($row['statement']&&$total-$outstanding != $row['value']) echo 'Imbalance'; ?>
                            </td>
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
                                    <br>
                                    <input type="checkbox" name="statement" value="1" <?php if($transaction['statement']) echo 'checked="checked"';?>>
                                    <label>Statement</label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </form>
              </td>
            </tr>
        </table>
        <script>
            // scroll to selected or last row
            document.getElementById('row_<?php echo $transaction['id']?$transaction['id']:$row['id']; ?>').scrollIntoView();
            // select description fields
            document.getElementById('description').focus();
        </script>
    </body>
</html>