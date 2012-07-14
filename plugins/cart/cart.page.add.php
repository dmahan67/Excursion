<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=page.add.tags
[END_PLUGIN]
==================== */

$xtpl->assign('FORM_CART_PRICE', $excursion->inputbox('text', 'cart_price', $insert['cart_price'], array('size' => '64', 'maxlength' => '255')));

?>