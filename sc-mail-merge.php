<?php
/*
Plugin Name: Mail Merger by SourceCode
Description: A simple mail merge plugin for WordPress with CSV upload support and batch email sending using WordPress Cron.
Version: 1.0
Author: SourceCodePlugins
Author URI: https://sourcecode.es
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sc-mail-merge
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MAILMERGER_SOURCECODE{

    // construct
    public function __construct(){
        add_action('admin_menu',[$this, 'create_admin_menu']);
        add_action('admin_enqueue_scripts',[$this,'enqueue_admin_assets']);
        add_action('admin_notices',[$this,'display_admin_notices']);
        add_action('admin_post_send_mail_merge', [$this, 'process_mail_merge']);
        add_action('mailmerger_send_batch',[$this,'send_email_batch'], 10, 2);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // admin menu

    public function create_admin_menu(){
        add_menu_page(
            'Mail Merger by SourceCode',
            'Mail Merger',
            'manage_options',
            'sc-mailmerger',
            [$this,'admin_page'],
            'dashicons-email-alt'
        );

        add_submenu_page(
            'sc-mailmerger',
            'Mail Merger Settings',
            'Settings',
            'manage_options',
            'sc-mailmerger-settings',
            [$this, 'settings_page']
        );
    }

    // admin assets

    public function enqueue_admin_assets($hook){
        if($hook !== 'toplevel_page_sc-mailmerger'){
            return;
        }

        wp_enqueue_editor();
        wp_enqueue_style(
            'bootstrap',
            plugin_dir_url(__FILE__).'assets/css/bootstrap.min.css',
            [],
            '5.3.0'

        );

        wp_enqueue_script(
            'bootstrap',
            plugin_dir_url(__FILE__).'assets/js/bootstrap.bundle.min.js',
            ['jquery'],
            '5.3.0',
            true
        );
    }

    // admin page content
    public function admin_page(){
        if(!current_user_can('manage_options')){
            return;
        }

        $templateDir = plugin_dir_path(__FILE__).'/templates/';
        if(file_exists($templateDir.'/admin-page.php')){
            // var_dump( get_option('mailmerger_batch_data'));
            include $templateDir.'/admin-page.php';
        }else{
            echo 'Admin Page Template Not Found';
        }

    }

    // Settings Page
    // Settings page content
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>Mail Merger Settings</h1>';
        echo '<form method="post" action="options.php">';

        settings_fields('mailmerger_settings');
        do_settings_sections('mailmerger-settings');
        submit_button();

        echo '</form>';
        echo '</div>';
    }

    
    // Register settings
    public function register_settings() {

        register_setting(
            'mailmerger_settings',
             'mailmerger_bcc_email',
             'sanitize_email'

        );

        add_settings_section(
            'mailmerger_settings_section',
            'General Settings',
            [$this, 'render_settings_section'],
            'mailmerger-settings'
        );

        add_settings_field(
            'mailmerger_bcc_email',
            'BCC Email Address',
            [$this, 'bcc_email_field'],
            'mailmerger-settings',
            'mailmerger_settings_section'
        );
    }

    public function render_settings_section() {
        echo '<p>Configure the general settings for Mail Merger.</p>';
    }

     // BCC email field
     public function bcc_email_field() {
        $bcc_email = get_option('mailmerger_bcc_email', '');
        echo '<input type="email" name="mailmerger_bcc_email" value="' . esc_attr($bcc_email) . '" class="regular-text">';
        echo '<p class="description">Add a BCC email address to include in all mail merge emails.</p>';
    }

    // process emails

    public function process_mail_merge(){
        
        if(!isset($_POST['mail_merge_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mail_merge_nonce'])),'mail_merge_nonce')){
            wp_die('Invalid Nonce!');
        }

        if(!current_user_can('manage_options')){
            wp_die('Unauthorised');
        }

        if(!isset($_FILES['csv'])){
            wp_die('No file');
        }
        if(isset($_FILES)){
            if (isset($_FILES['csv']) && !empty($_FILES['csv']['name'])) {
                // $uploaded_file = $_FILES['csv'];
                if(!isset($_FILES['csv']['tmp_name']) || empty($_FILES['csv']['tmp_name'])){
                    wp_die('Temporary file not found');
                }

                if(!isset($_FILES['csv']['type']) || empty($_FILES['csv']['type'])){
                    wp_die('File type not found. Your file is invalid.');
                }

                if(!empty($_FILES['csv']['error'])){
                    wp_die('Error with the file.');
                }

                if(empty($_FILES['csv']['size'])){
                    wp_die('File has no size. Possibly not uploaded correctly.');
                }
                
                
                $file_name = sanitize_file_name($_FILES['csv']['name']);
                $tmp_name = sanitize_text_field($_FILES['csv']['tmp_name']);
                $file_type = wp_check_filetype($file_name);

                // Validate file type
                $allowed_types = ['csv' => 'text/csv'];
                if (!array_key_exists($file_type['ext'], $allowed_types)) {
                    wp_die('Invalid file type. Only CSV files are allowed.');
                }

                // Prepare sanitized file data for upload
                $uploaded_file = [
                    'name' => $file_name,
                    'type' => sanitize_text_field($_FILES['csv']['type']),
                    'tmp_name' => $tmp_name,
                    'error' => intval($_FILES['csv']['error']),
                    'size' => intval($_FILES['csv']['size'])
                ];
            }
 
            $upload_overrides = array(
                'test_form'=>false
            );

            $movefile = wp_handle_upload($uploaded_file,$upload_overrides);
        }

        if ( $movefile && isset( $movefile['error'] ) ) {
            wp_die('File not uploaded');
        }

        $file_path = $movefile['file'];
        
        $csv_file = file($file_path);
        $csv_data = array_map(function($line) {
            // Detect delimiter dynamically: comma or semicolon
            $delimiter = (strpos($line, ';') !== false) ? ';' : ',';
            return str_getcsv($line, $delimiter);
        }, $csv_file);

        $csv_headers = array_map('strtolower',array_shift($csv_data));

        if(!in_array('first name',$csv_headers) || !in_array('email',$csv_headers)){
            wp_die('CSV file must include "First name" and "Email" columns');
        }
        

        if(!isset($_POST['subject'])){
            wp_die('No email subject entered');
        }
        $subject = sanitize_text_field(wp_unslash($_POST['subject']));
       
        if (isset($_POST['message'])) {
            $allowed_html = [
                'a' => [
                    'href' => [],
                    'title' => [],
                    'target' => [],
                ],
                'br' => [],
                'p' => [],
                'strong' => [],
                'em' => [],
                'ul' => [],
                'ol' => [],
                'li' => [],
                'span' => [
                    'style' => [], // Allow inline styles
                    'class' => []  // Allow classes
                ],
                'img' => [
                    'src' => [],
                    'alt' => [],
                    'title' => [],
                    'width' => [],
                    'height' => [],
                ],
                'h1' => [],
                'h2' => [],
                'h3' => [],
                'h4' => [],
                'h5' => [],
                'h6' => [],
                'div' => [
                    'style' => [],
                    'class' => [],
                ],
                'blockquote' => [],
                'code' => [],
                'pre' => [],
            ];
        
            $message = wp_kses(wp_unslash($_POST['message']), $allowed_html);
        }
       
        
        $message = preg_replace(
            [
                '/<\/?(o:p|w:[^>]+)>/i',
                '/style="[^"]*mso[^"]*"/i'
            ],
            '',
            $message
        );

        $bcc_email = get_option('mailmerger_bcc_email', '');

        // Generate unique batch key
        $batch_id = 'batch_'.time();

        $existing_batches = get_option('mailmerger_batches',[]);
        $existing_batches[$batch_id] = [
            'subject'=>$subject,
            'message_template'=>$message,
            'csv_data'=>$csv_data,
            'headers'=>$csv_headers,
            'bcc'=>$bcc_email
        ];

        update_option('mailmerger_batches',$existing_batches);
        $max_attempts = 5;
        $attempt = 0;
        $scheduled = false;
        
        while (!$scheduled && $attempt < $max_attempts) {
            if (!wp_next_scheduled('mailmerger_send_batch', [$batch_id])) {
                wp_schedule_single_event(time() + 20, 'mailmerger_send_batch', [$batch_id]);
                $scheduled = true;

                set_transient('mailmerger_success_message', 'Emails have been scheduled successfully and cron event created.', 30);
            }
            $attempt++;
            sleep(1); // Small delay before retrying
        }
        
        $max_attempts = 5;
        $attempt = 0;
        $scheduled = false;
        
        while (!$scheduled && $attempt < $max_attempts) {
            if (!wp_next_scheduled('mailmerger_send_batch', [$batch_id])) {
                wp_schedule_single_event(time() + 20, 'mailmerger_send_batch', [$batch_id]);
                $scheduled = true;
                error_log("Successfully scheduled mailmerger_send_batch on attempt $attempt");
                set_transient('mailmerger_success_message', 'Emails have been scheduled successfully and cron event created.', 30);
            }
            $attempt++;
            sleep(1); // Small delay before retrying
        }
        
        if (!$scheduled) {
            error_log("Failed to schedule mailmerger_send_batch after multiple attempts");
        }
       
        wp_safe_redirect(admin_url('admin.php?page=sc-mailmerger'));
        exit;
    }

    public function display_admin_notices(){
        if($message = get_transient('mailmerger_success_message')){
            echo '<div class="notice notice-success is-dismissible">'.esc_html($message).'</div>';
            delete_transient('mailmerger_success_message');
        }
    }

    // send emails
    public function send_email_batch($batch_id,$offset = 0){
        $batches = get_option('mailmerger_batches',[]);

        if(!$batches[$batch_id]){
            return;
        }

        $batch_data = $batches[$batch_id];

        $subject = $batch_data['subject'];
        $message_template = $batch_data['message_template'];
        $csv_data = $batch_data['csv_data'];
        $headers = $batch_data['headers'];


        $batch_size = 50;
        $next_offset = $offset + $batch_size;

        $batch = array_slice($csv_data,$offset,$batch_size);

        foreach($batch as $row){
            $data = array_combine($headers,$row);
            $message = $this->render_email_template('email-template.php',[
                'name' => $data['first name'],
                'subject' => $subject, 
                'message' => str_replace('{name}',$data['first name'],$message_template)
                ]
            );

            $email_headers = ['Content-Type: text/html; charset=UTF-8'];
            if (!empty($batch_data['bcc'])) {
                $email_headers[] = 'Bcc: ' . $batch_data['bcc'];
            }

            wp_mail($data['email'],$subject,$message,$email_headers);
        }

        if($next_offset < count($csv_data)){
            wp_schedule_single_event(time()+60*5,'mailmerger_send_batch', [$batch_id, $next_offset]);
        }else{
            unset($batches[$batch_id]);
            update_option('mailmerger_batches', $batches);
        }
        
    }

    private function render_email_template($template_file,$variables){

        $template_path = plugin_dir_path(__FILE__).'/templates/';
        if(!file_exists($template_path.$template_file)){
            return '';
        }

        extract($variables);
        ob_start();
        include $template_path.$template_file;
        return ob_get_clean();

    }
}

new MAILMERGER_SOURCECODE();