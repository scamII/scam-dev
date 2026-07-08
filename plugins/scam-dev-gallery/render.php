<?php
$cols = $attributes['cols'] ?? 3;
echo do_shortcode( '[scamdev_gallery cols="' . intval( $cols ) . '"]' );
