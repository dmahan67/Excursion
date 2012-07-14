<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */

$config['cart']['installed'] = true;

$cart['config'] = '<script>
						simpleCart({
							checkout: {
								type: "PayPal",
								email: "'.$config['plugin']['cart']['email'].'"
							},
							currency: "'.$config['plugin']['cart']['currency'].'",
							taxRate: '.$config['plugin']['cart']['tax'].',
							shippingFlatRate: '.$config['plugin']['cart']['shipping'].'
						});
					</script>';

$config['header_tags'] .= $excursion->createTags('css', 'cart', 'plugins/cart/css/cart.css', '');
$config['footer_tags'] .= $excursion->createTags('javascript', '', 'plugins/cart/js/simpleCart.js', '');
$config['footer_tags'] .= $cart['config'];
$config['footer_tags'] .= $excursion->createTags('javascript', '', 'plugins/cart/js/style.js', '');

?>