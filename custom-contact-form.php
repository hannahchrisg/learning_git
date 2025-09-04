<?php
/**
 * Plugin Name: Custom Contact Form with ACF
 * Description: A simple contact form that saves submissions to the "Contact Page" custom post type using ACF fields.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */
function custom_contact_form_enqueue_styles() {
    wp_enqueue_style(
        'custom-contact-form-style',
        plugin_dir_url(__FILE__) . 'style.css',
        array(),
        '1.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'custom_contact_form_enqueue_styles');

function render_contact_form() {
    global $message;
if (!empty($message)) {
    echo $message;
}
    ob_start();
    ?>
    <form method="POST" action="" class="formstyle">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required style="color:black;">

        <label for="second_name">Second Name:</label>
        <input type="text" id="second_name" name="second_name" style="color:black;">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" style="color:black;" required>

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" style="color:black;" required>

        <label for="address">Address:</label><br>
        <textarea id="address" name="address" style="color:black;" required></textarea><br><br>

        <input type="submit" name="submit_contact" value="Send Message">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('contact_form', 'render_contact_form');

function handle_contact_form_submission() {
    global $message;
    if (isset($_POST['submit_contact'])) {

       
        $first_name   = sanitize_text_field($_POST['first_name']);
        $second_name  = sanitize_text_field($_POST['second_name']);
        $email        = sanitize_email($_POST['email']);
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $address      = sanitize_textarea_field($_POST['address']);

        if (empty($first_name) || empty($email) || empty($phone_number) || empty($address)) {
            $message =  '<p style="color:white;font-weight:bold;font-size:20px;">Please fill in all required fields.</p>';
            return;
        }
       if (!(is_email($email))) {
    $message =  '<p style="color:white;font-weight:bold;font-size:20px;">Please enter a valid email address.</p>';
    return;
}
        
        if( !preg_match('/^[0-9]{10}$/', $phone_number)){
            $message = '<p style="color:white;font-weight:bold;font-size:20px;">Please enter a valid 10-digit phone number.</p>';
            return;
        }


        

       
        $post_id = wp_insert_post(array(
            'post_type'   => 'contact_page', 
            'post_status' => 'publish',
        ));

       
        if ($post_id) {
            update_field('first_name', $first_name, $post_id);
            update_field('second_name', $second_name, $post_id);
            update_field('email', $email, $post_id);
            update_field('phone_number', $phone_number, $post_id);
            update_field('address', $address, $post_id);

            $message =  '<p style="color:white;font-weight:bold;font-size:20px;">Thank you! Your message has been submitted.</p>';
        } else {
             $message = '<p style="color:white;font-weight:bold;font-size:20px;">Something went wrong. Please try again.</p>';
        }
         $log_entry = "First Name: $first_name\nSecond Name: $second_name\nEmail: $email\nPhone Number: $phone_number\nAddress: $address\n\n";
        file_put_contents(WP_CONTENT_DIR . '/contact-log.txt', $log_entry, FILE_APPEND);
        // Attempt email
        $to = 'hannah@olivetech.com'; // Replace with your email
        $subject = 'New Contact Form Submission';
        $body = $log_entry;
        $headers = array('From: ' . $email);
        if (wp_mail($to, $subject, $body, $headers)) {
             $message='<p style="color:white;font-weight:bold;font-size:20px;">Message sent successfully!</p>';
        } else {
             $message='<p style="color:white;font-weight:bold;font-size:20px;">Email failed. Check log at wp-content/contact-log.txt.</p>';
        }
    }
}
add_action('wp', 'handle_contact_form_submission');

