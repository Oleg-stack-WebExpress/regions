<?php get_header(); ?>


<div class="container">
    <h1><span class="badge text-bg-secondary"><?php the_title(); ?> </span></h1>


    <div class="shadow-lg p-3 mb-5 bg-body-tertiary rounded">
        <?php the_content(); ?>
    </div>

</div>
<?php get_footer(); ?>