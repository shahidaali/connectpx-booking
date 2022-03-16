<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var $helper BooklyInvoices\Backend\Modules\Settings\Lib\Helper */
use ConnectpxBooking\Lib;
?>
<div>
    <table cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td>
                    <table cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #000;" id="invoice-appointments" class="table">
                        <tbody>
                            <tr>
                                <?php foreach ($datatables['settings']['columns'] as $name => $show) { ?>
                                    <td style="vertical-align: middle; background-color: #2e75b5; color: #fff; border-right-width: 0.1px; border-bottom-width: 0.1; text-align: center;" valign="middle" align="center"><?php echo $datatables['titles'][$name]; ?></td>
                                <?php } ?>
                            </tr>
                            <?php foreach ($appointments as $appointment) { ?>
                                <tr>
                                    <?php foreach ($datatables['settings']['columns'] as $name => $show) { ?>
                                        <td style="vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; text-align: center;" valign="middle" align="center">
                                            <?php
                                                echo $appointment[$name]; 
                                            ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td style="width: 80%;"></td>
                                <td style="width: 20%;">
                                    <table cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #000; background-color: #d8d8d8;">
                                        <tbody>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; color: #2e75b5; width: 20%;" valign="middle">
                                                    <?php echo __('Total', 'connectpx_booking'); ?>
                                                </td>
                                                <td style="font-weight: bold; vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; color: #2e75b5;  width: 20%; text-align: right;" valign="middle" align="right">
                                                    <?php echo $total_amount; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>