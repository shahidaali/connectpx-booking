<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var $helper BooklyInvoices\Backend\Modules\Settings\Lib\Helper */
use ConnectpxBooking\Lib;
?>
<div>
    <table cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <!-- Header Row -->
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td style="width: 30%; vertical-align: top;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td style="font-size: large; font-weight: bold; vertical-align: top; color: #2e75b5;">
                                                    <?php echo $company_details['company_name']; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top;">
                                                    <?php echo $company_details['company_address']; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top;">
                                                    <?php echo __('Phone:', 'connectpx_booking') ?> <?php echo $company_details['company_phone']; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 10px;"></td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top; background-color: #2e75b5; color: #fff;">
                                                    <?php echo __('BILL TO:', 'connectpx_booking') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top;">
                                                    <?php echo $customer['full_name']; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top;">
                                                    <?php echo $customer['address']; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align: top;">
                                                    <?php echo $customer['phone']; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 40%; vertical-align: middle; text-align: center;" align="center" valign="center">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td style="height: 10px;"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <?php if(!empty($company_details['company_logo'])): ?>
                                                        <img src="<?php echo $company_details['company_logo']; ?>" style="width: 150px; height: auto;">
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 30%; vertical-align: top;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td style="font-size: large; font-weight: bold; vertical-align: top; color: #2e75b5; text-align: right;" align="right">
                                                    <?php echo __('INVOICE', 'connectpx_booking') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 40px;"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td style="font-weight: bold; vertical-align: top; background-color: #2e75b5; color: #fff;">
                                                                    <?php echo __('INVOICE #', 'connectpx_booking') ?>
                                                                </td>
                                                                <td style="font-weight: bold; vertical-align: top; background-color: #2e75b5; color: #fff;">
                                                                    <?php echo __('DATE', 'connectpx_booking') ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-weight: bold; vertical-align: top;">
                                                                    <?php echo $invoice['id']; ?>
                                                                </td>
                                                                <td style="font-weight: bold; vertical-align: top;">
                                                                    <?php echo $invoice['created_date']; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-weight: bold; vertical-align: top; background-color: #2e75b5; color: #fff;">
                                                                    <?php echo __('CUSTOMER ID', 'connectpx_booking') ?>
                                                                </td>
                                                                <td style="font-weight: bold; vertical-align: top; background-color: #2e75b5; color: #fff;">
                                                                    <?php echo __('TERMS', 'connectpx_booking') ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-weight: bold; vertical-align: top;">
                                                                    <?php echo $customer['id']; ?>
                                                                </td>
                                                                <td style="font-weight: bold; vertical-align: top;">
                                                                    <?php echo $due_text; ?>
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
                </td>
            </tr> <!-- # Header Row -->
            <tr>
                <td style="height: 20px;"></td>
            </tr>
            <!-- Appointments -->
            <tr>
                <td>
                    <table cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #000;">
                        <tbody>
                            <tr>
                                <?php foreach ($datatables['settings']['columns'] as $name => $show) { ?>
                                    <td style="vertical-align: middle; background-color: #2e75b5; color: #fff; border-right-width: 0.1px; border-bottom-width: 0.1; text-align: center;" valign="middle" align="center"><?php echo $datatables['titles'][$name]; ?></td>
                                <?php } ?>
                            </tr>
                            <?php foreach ($appointments as $appointment) { ?>
                                <tr>
                                    <?php foreach ($datatables['settings']['columns'] as $name => $show) { ?>
                                        <td style="vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; text-align: center;" valign="middle" align="center"><?php echo $appointment[$name]; ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <!-- # Appointments -->
            <!-- Totals -->
            <tr>
                <td align="right">
                    <table cellpadding="5" cellspacing="0" width="30%" style="border-collapse: collapse; border: 1px solid #000; background-color: #d8d8d8;">
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; color: #2e75b5;" valign="middle">
                                    <?php echo $thank_you_text; ?>
                                </td>
                                <td style="font-weight: bold; vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; color: #2e75b5;" valign="middle">
                                    <?php echo __('Total', 'connectpx_booking'); ?>
                                </td>
                                <td style="font-weight: bold; vertical-align: middle; border-right-width: 0.1px; border-bottom-width: 0.1; color: #2e75b5;" valign="middle">
                                    <?php echo $invoice['total']; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <!-- # Totals -->
        </tbody>
    </table>
</div>