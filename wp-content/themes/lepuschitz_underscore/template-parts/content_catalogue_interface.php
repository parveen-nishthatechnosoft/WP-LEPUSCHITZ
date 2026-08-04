<?php
?>
<h1>Wählen Sie bitte <span class="bold">den Katalog</span> aus, den Sie hochladen wollen:</h1>
<select name="selectCatalog" id="selectCatalog">
    <option value="Select" selected disabled>-- Katalog auswählen --</option>

    <?php

    $args = array(
        'post_type' => 'catalog',
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    while ($query->have_posts()): $query->the_post(); ?>

        <option data-post="<?php the_ID(); ?>" data-type="<?php the_field('short_code');?>" value="<?php the_title(); ?>"><?php the_title(); ?></option>

    <?php endwhile; ?>

</select>

<br>

<div id="catalogs">
    <div id="catAnda" class="catalog">
        <p>Dieser Katalog braucht keine Hochladung. Die Dateien werden automatisch aktualisiert.</p>
        <button id="uploadAnda" class="btn btn-default">Jetzt akrualisieren</button>
    </div>

    <div id="catAnda1" class="catalog">
        <div class="andaFilesOrLinks">
            <div class="form-group">
                <label for="andaChoise">Verwenden Sie Dateien oder Links?</label>
                <input type="radio" class="anda-choice" name="andaChoice" value="Datei">Datei
                <input type="radio" class="anda-choice" name="andaChoice" value="Link">Link
            </div>
        </div>

        <div class="andaFiles">
            <div class="form-group">
                <label for="andaProducts">Datei mit den Produkten:</label>
                <input type="file" name="andaProducts" id="andaProducts">
            </div>
        </div>

        <div class="andaLinks">
            <div class="form-group">
                <label for="andaProducts">Datei mit den Produkten:</label>
                <input type="text" name="andaProducts" id="andaProducts">
            </div>
        </div>
    </div>
</div>

<div id="progress" style="display: none;">
    <div class="total"></div>
    <div class="finished"></div>
</div>
<div id="progressBar">
    <div class="done"></div>
    <p class="procent"><span>46</span>%</p>
</div>
