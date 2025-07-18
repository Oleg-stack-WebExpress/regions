<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-12 col-md-8">
            <h2>Лавка лучника</h2>

            <?php
            $query = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 10
            ]);
            if ($query->have_posts()) {
                while ($query->have_posts()) {

                    $query->the_post(); ?>

                    <h3><?php the_title(); ?></h3>
                    <?php the_content(); ?>
                    <hr>

                    <?php
                }

            } else {
                echo '<p>Постов нет</p>';
            } ?>

            <?php if ($query->have_posts())
                ; ?>

        </div>
        <div class="col-12 col-md-4">
            <?php dynamic_sidebar('blog-sidebar'); ?>
        </div>
    </div>
</div>



<?php get_footer(); ?>