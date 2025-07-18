<?php get_header(); ?>
<div class="container">
    <h1>О нас</h1>
    <img src="<?= get_assets_dir('images/Emojione_BW_1F3F9.svg.png'); ?>" alt="logo">
    <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Описание
            </button>
            <ul class="dropdown-menu">
                <li>
                    <p><?php bloginfo('description'); ?></p>
                </li>
            </ul>
        </div>
</div>
<?php get_footer(); ?>