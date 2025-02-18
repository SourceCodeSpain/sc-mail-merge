<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center">
            <h1>Mail Merger</h1>
            <h2>by <a href="https://sourcecode.es">SourceCode</a></h2>
        </div>
        <div class="col-12">
            <p>Complete the form below to send a mailmerge out. Please note the CSV file must contain First Name and Email columns.</p>
        </div>
    </div>
    <div class="row">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')) ?>" enctype="multipart/form-data">
            <!--

                # Subject - text
                # Message - wysiwyg? / textarea
                # CSV - file (first name, email)
                # Nonce - hidden
                # BCC - text
                # Priority - check
                # Schedule to send emails - select?
                # Add action to options for cron - hidden form field?
            -->
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Subject">
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <p>Placeholders - <code>{name}</code>.</p>
                    <?php 
                        echo wp_kses_post(
                            wp_editor(
                            '',
                            'message',
                            [
                                        'media_buttons'=>false,
                                        'teeny'=>true
                                    ],
                            )
                        );
                    ?>
            </div>
            <div class="mb-3">
                <label for="csv" class="form-label">CSV (Must be have fields: First Name & Email)</label>
                <input type="file" name="csv" class="form-control" accept=".csv">
            </div>
            <div class="mb-3">
                <input type="hidden" name="action" value="send_mail_merge">
                <?php wp_nonce_field('mail_merge_nonce','mail_merge_nonce'); ?>
                <input type="submit" value="Send Emails" class="button button-primary">
            </div>
        </form>
    </div>    
</div>