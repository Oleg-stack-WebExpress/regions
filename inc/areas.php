<?php
register_nav_menus([
    'top' => 'Верхнее меню'
]);

register_sidebar([
    'name' => 'Блог',
    'id' => 'blog-sidebar',
    'before_widget' => '<div id="%1$s" class="blog-widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '</div class="blog-widget_title">',
    'after_title' => '</div>',
]);

