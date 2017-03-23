<?php global $wpdb ?>
<div class="wrap">
    <h2><?php _e('Migrate from Mytory Markdown Plain to Mytory Markdown for Dropbox', 'mm4d') ?></h2>

    <?php
    if (!empty($message)) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p><strong><?= $message ?></strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Close.',
                        'mytory-markdown') ?></span></button>
        </div>
        <?php
        exit;
    } ?>

    <?php
    // help paragraph
    if (!function_exists('Markdown')) {
        include_once 'markdown.php';
    }
    $help_file_path = dirname(__FILE__) . '/help/url-batch-replace-' . get_user_locale() . '.md';
    if (file_exists($help_file_path)) {
        $md_content = file_get_contents($help_file_path);
    } else {
        $md_content = file_get_contents(dirname(__FILE__) . '/help/url-batch-replace-en_US.md');
    }
    echo Markdown($md_content);

    // extract common substring in md path
    $results = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'mytory_md_path' AND meta_value != ''");
    $mytory_md_path_list = wp_list_pluck($results, 'meta_value');
    $mytory_md_path_list_raw = $mytory_md_path_list;

    foreach ($mytory_md_path_list as $i => $path) {
        $mytory_md_path_list[$i] = str_replace(array('http://', 'https://'), array('', ''), $path);
    }

    include_once 'extract-common-substring.php';
    $recommend_change_from = strCommonPrefixByStr($mytory_md_path_list);
    ?>

    <form method="post" class="js-form">
        <p>Fill URL corresponding to Public folder in below field. </p>
        <p>
            e.g. If your URL is
            <code>https://dl.dropboxusercontent.com/u/15546257/md/a.md</code>
            and path in Dropbox is <code>/Public/md/a.md</code>, URL corresponding to Public
            folder is <code>dl.dropboxusercontent.com/u/15546257/</code>.
        </p>
        <p>
            Do not include <code>http://</code> or <code>https://</code>. <code>/</code> Is required at the end.
        </p>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('URL corresponding to Public folder') ?></th>
                <td>
                    <input class="large-text" type="text" name="change_from" value="<?= $recommend_change_from ?>"
                           title="<?php esc_attr_e(__('Change from')) ?>"/>
                    <?php if ($recommend_change_from) { ?>
                        <p>
                            <?php _e('Above is a string that extracted common substring from markdown paths.',
                                'mytory-markdown') ?>
                            <?php _e('Please edit appropriately and convert.', 'mm4d') ?>
                        </p>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary"
                   value="<?php _e('Convert', 'mytory-markdown') ?>">
            <?php
            $wp_query = new WP_Query(array(
                'meta_query' => array(
                    array(
                        'key' => 'mytory_md_path_old',
                        'value' => '',
                        'compare' => '!=',
                    ),
                ),
            ));
            if ($wp_query->post_count > 0) { ?>
                <a title="<?php _e('Only one step can be undo.', 'mytory-markdown') ?>" style="float: right;"
                   onclick="return confirm('<?php _e('Really?', 'mytory-markdown') ?>');"
                   class="trash"
                   href="options-general.php?page=mytory-markdown-batch-update&action=undo"><?php _e('Undo',
                        'mytory-markdown') ?></a>
            <?php } ?>
        </p>
    </form>

    <div class="card" style="max-width: 100%;">
        <h3><?php _e('Reference: Your Markdown URL List', 'mytory-markdown') ?></h3>
        <ul>
            <?php foreach ($mytory_md_path_list_raw as $path) { ?>
                <li><code><?= $path ?></code></li>
            <?php } ?>
        </ul>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        $('.js-form').submit(function () {
            var $change_from = $('[name="change_from"]');
            if ($change_from.val().substr(-1) !== '/') {
                $change_from.val($change_from.val() + '/');
            }
            if ($change_from.val().substr(0, 7) === 'http://') {
                $change_from.val($change_from.val().substr(7));
            }
            if ($change_from.val().substr(0, 8) === 'https://') {
                $change_from.val($change_from.val().substr(8));
            }
        });
    });
</script>