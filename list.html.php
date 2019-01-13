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
            #transactions{
                height: 90vh;
                overflow: scroll;
            }
            
        </style>
    </head>
    <img src="monkey.gif" alt="monkey" style="width:90px; height:90px;">
    <body>
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
                            <?php foreach ($accounts_array as $account) { ?>
                            <input type="radio" name="aID" value="<?php echo $account['id']; ?>"
                                   <?php if($account['id'] == $aID) echo 'checked="checked"'; ?> 
                                   onclick="document.getElementById('account_form').submit()"/>
                                <label><?php echo $account['name']; ?></label>
                                <br>
                            <?php } ?>
                        <?php } ?>
                        <button type="button" onclick="window.location.href='?page=account'">Manage Accounts</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                    <table>
                        <thead>
                        <th>Date</th>
                        <th>description</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th></th>
                        </thead>
                        <tbody id="transactions">
                        <?php 
                        $total = 0;
                        foreach($transactions as $row) {
                            $total += $row['value'];
                            $odd = !$odd; 
                            $class = $odd?'odd':'even';
                            if($row['outstanding']) $class .= ' outstanding';
                            if($row['value'] < 0) $class .= ' negative';
                            if($total < 0 && $account_info['type'] == 'bank') $class .= ' warning';
                            ?>
                        <tr class="<?php echo $class; ?>">
                            <td><?php echo $row['tran_date']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo money_format('%#10n', $row['value']); ?></td>
                            <td><?php echo money_format('%#10n', $total); ?></td>
                            <td><button type="button" onclick="window.location.href='?action=edit&id=<?php echo $row['id']; ?>'">Edit</button></td>
                        </tr>
                        <?php } ?> 
                        </tbody>
                            <tr class="form">
                                <td>
                                    <input type="hidden" name="account" value="<?php echo $aID; ?>">
                                    <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                    <input type="date" name="tran_date" value="<?php echo (isset($transaction['tran_date'])?$transaction['tran_date']:date('Y-m-d')); ?>">
                                </td>
                                <td><input type="text" name="description" value="<?php echo $transaction['description']; ?>" size="20"></td>
                                <td><input type="text" name="value" value="<?php echo $transaction['value']; ?>" size="8"></td>
                                <td><input type="checkbox" name="outstanding" value="1"<?php echo ($transaction['outstanding']?'checked=checked':''); ?>" size="8"></td>
                                <td><input type="submit" name="action" value="<?php echo $transaction['id']?'save':'add';?>"></td>
                            </tr>
                    </table>
                    </form>
              </td>
            </tr>
        </table>
    </body>
</html>