<?php

function remove_unwanted_profile_fields($contactmethods) {
    $current_user = wp_get_current_user();
    
    if (in_array('gianhang', $current_user->roles)) {
        // Loại bỏ trường Admin Color Scheme
        remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');

        // Loại bỏ trường Locale
        global $locale;
        remove_action('personal_options', array($locale, 'locale'));
    }

    return $contactmethods;
}
add_filter('user_contactmethods', 'remove_unwanted_profile_fields');

function remove_personal_options($subject) {
    $current_user = wp_get_current_user();
    
    if (in_array('gianhang', $current_user->roles)) {
        $subject = preg_replace('#<tr class="user-admin-color-wrap"[\s\S]*?</tr>#', '', $subject, 1);
        $subject = preg_replace('#<tr class="user-language-wrap"[\s\S]*?</tr>#', '', $subject, 1);
        $subject = preg_replace('#<tr class="user-first-name-wrap"[\s\S]*?</tr>#', '', $subject, 1);
        $subject = preg_replace('#<tr class="user-last-name-wrap"[\s\S]*?</tr>#', '', $subject, 1);
        $subject = preg_replace('#<tr class="user-display-name-wrap"[\s\S]*?</tr>#', '', $subject, 1);
    }

    return $subject;
}

function profile_subject_start() {
    $current_user = wp_get_current_user();
    
    if (in_array('gianhang', $current_user->roles)) {
        ob_start('remove_personal_options');
    }
}

function profile_subject_end() {
    $current_user = wp_get_current_user();
    
    if (in_array('gianhang', $current_user->roles)) {
        ob_end_flush();
    }
}

add_action('admin_head-profile.php', 'profile_subject_start');
add_action('admin_footer-profile.php', 'profile_subject_end');

// Thêm trường phone vào dưới user-email-wrap
function add_phone_field($profile_user) {
    $current_user = wp_get_current_user();
    if (in_array('gianhang', $current_user->roles)):
    ?>
    <table id="table_user_meta_phone" class="form-table" role="presentation">
        <tr class="user-phone-wrap">
            <th><label for="user_meta_phone"><?php _e( 'Phone' ); ?></label></th>
            <td>
                <input type="text" name="phone" id="user_meta_phone" required="true" value="<?php echo esc_attr( get_the_author_meta('phone', $profile_user->ID) ); ?>" class="regular-text code" />
                <p class="description"><?php _e('Please enter your phone number.'); ?></p>
            </td>
        </tr>
    </table>
    <h2><?php esc_html_e( 'Profile Picture'); ?></h2>

    <table class="form-table">
        <tr>
            <th><label for="avatar"><?php esc_html_e( 'Avatar'); ?></label></th>
            <td>
                <input type="file" id="avatar" name="avatar" accept="image/*">

                <?php echo get_avatar( get_current_user_id(), 150, '', 'Avatar', array( 'url' => get_the_author_meta('avatar_url', $profile_user->ID) ) ); ?>

                <?php // if(get_the_author_meta('avatar_url', $profile_user->ID) ):?>
                    <!-- <img src="<?php // echo esc_attr( get_the_author_meta('avatar_url', $profile_user->ID) ); ?>" alt="" width="400px"> -->
                <?php // endif; ?>

                <p class="description"><?php esc_html_e( 'Upload a new profile picture.'); ?></p>
            </td>
        </tr>
    </table>
    <?php
    endif;
}
add_action('show_user_profile', 'add_phone_field');
add_action('edit_user_profile', 'add_phone_field');


function add_script_profile_admin()
{
    $current_user = wp_get_current_user();
    if (in_array('gianhang', $current_user->roles)):
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#your-profile').attr("enctype", "multipart/form-data");

        var forcus = $('#your-profile').find('.form-table')[2];
        var move = $('table#table_user_meta_phone');

        $(forcus).before(move[0].outerHTML);

        move.remove();
    });
</script>
<?php
endif;
}

add_action('admin_footer', 'add_script_profile_admin');

// Lưu trữ dữ liệu trường phone khi cập nhật hồ sơ người dùng
function save_phone_field($user_id) {
    $current_user = wp_get_current_user();
    if (current_user_can('edit_user', $user_id) && in_array('gianhang', $current_user->roles) && isset($_POST['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));

        // Xử lý khi form được submit
        if ( isset( $_FILES['avatar'] ) && ! empty( $_FILES['avatar']['tmp_name'] ) ) {
            // Thực hiện xử lý tải lên ảnh avatar
            $upload_overrides = array( 'test_form' => false );
            $uploaded_file = wp_handle_upload( $_FILES['avatar'], $upload_overrides );

            if ( isset( $uploaded_file['url'] ) ) {
                // Lưu đường dẫn avatar vào trường meta của người dùng
                $avatar_url = $uploaded_file['url'];
                update_user_meta( get_current_user_id(), 'avatar_url', $avatar_url );
            }
        }
    }
}
add_action('personal_options_update', 'save_phone_field');
add_action('edit_user_profile_update', 'save_phone_field');








function remove_all_posts_view($views) {
    $current_user = wp_get_current_user();
    // Kiểm tra nếu tab "Tất cả" tồn tại trong danh sách và xóa nó
    if (isset($views['all']) && in_array('gianhang', $current_user->roles)) {
        unset($views['all']);
    }
    return $views;
}
add_filter('views_edit-bien-so', 'remove_all_posts_view');

function custom_pre_get_posts($query) {
    $current_user = wp_get_current_user();

    // if (in_array('gianhang', $current_user->roles)) {
    //     $query->set('author', $current_user->ID);
    // }
    if (is_admin() && $query->is_main_query() && $query->get('post_type') == 'bien-so') {
        
        if (in_array('gianhang', $current_user->roles)) {
            $query->set('author', $current_user->ID);
        }
    }
}
add_action('pre_get_posts', 'custom_pre_get_posts');