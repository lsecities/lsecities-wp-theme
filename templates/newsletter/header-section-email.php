                         
        <div>
          <a href="" target="_blank">
            <?php echo get_the_post_thumbnail($section->ID, array(600,294)); ?>
          </a>
          <div>
            <h1 class="headerTitle">
              <a href="" target="_blank">
                <span><?php echo get_the_title($section->ID); ?></span>
              </a>
            </h1>
            <ul>
              <?php foreach($section['featured_items'] as $item): ?>
              <li>
                <a href="#<?php echo $item->ID; ?>" target="_blank"><?php echo get_the_title($item->ID); ?></a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
