<?php
/**
 * Create and endpoint to get notices related to the EE24 repository
 *
 * @return void
 */
function ee24_notices()
{
    $namespace = 'api-eea24/v1';
    $route = 'repository-status';
    register_rest_route(
        $namespace,
        $route,
        array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => 'get_repository_request_status',
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        )
    );
}

add_action('rest_api_init', 'ee24_notices');

add_action('admin_footer-post.php', 'repository_status_script');
add_action('admin_footer-post-new.php', 'repository_status_script');


function repository_status_script()
{
    ?>
    <script type="text/javascript">

        const {subscribe, select} = wp.data;
        const {isSavingPost} = select('core/editor');
        var checked = true;
        subscribe(() => {
            if (isSavingPost()) {
                checked = false;
            } else {
                if (!checked) {
                    checkNotificationAfterPublish();
                    checked = true;
                }

            }
        });

        function checkNotificationAfterPublish() {
            const postId = wp.data.select("core/editor").getCurrentPostId();
            const url = wp.url.addQueryArgs(
                '/wp-json/api-eea24/v1/repository-status',
                {id: postId},
            );
            wp.apiFetch({
                url,
            }).then(
                function (response) {
                    if (response.message) {
                        wp.data.dispatch("core/notices").createNotice(
                            response.type,
                            response.message,
                            {
                                id: 'repository_status_notice',
                                isDismissible: true
                            }
                        );
                    }
                }
            );
        };
    </script>
    <?php
}

function get_repository_request_status()
{
    if (isset($_GET['id'])) {

        $id = sanitize_text_field(
            wp_unslash($_GET['id'])
        );

        $errorTransient = get_transient("ee24_error");
        $successTransient = get_transient("ee24_success");

        if ($errorTransient) {
            delete_transient('ee24_error');

            return new \WP_REST_Response(
                array(
                    'type' => 'error',
                    'message' => wp_unslash("Error exporting to the EE24 Repository: " . $errorTransient),
                )
            );
        }

        if($successTransient) {
            delete_transient('ee24_success');
            return new \WP_REST_Response(
                array(
                    'type' => 'success',
                    'message' => wp_unslash("The EE24 Repository has been updated. " . $successTransient),
                )
            );
        }
    }

    return null;
}
