<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var string $id */
/** @var string $codes */
/** @var string $value */
/** @var string $doc_slug */
/** @var string $additional_classes */
?>
<div id="<?php echo esc_attr( $id ) ?>" class="connectpx_booking-ace-editor<?php if ( $additional_classes ) echo ' ' . esc_attr( $additional_classes ) ?>"<?php if ( $codes ) : ?> data-codes="<?php echo esc_attr( $codes ); ?>"<?php endif ?> data-value="<?php echo esc_attr( $value ); ?>"></div>
<small class="form-text text-muted"><?php echo __( 'Start typing "{" to see the available codes.', 'connectpx_booking' ) ?></small>