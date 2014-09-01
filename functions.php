<?php


function wpcf7_update_email_resources($wpcf7_data)
{
    $formData = $_POST;

    if( ! isset($formData['res-id']) )
    {
        return $wpcf7_data;
    }

    $fileId = (int) $formData['res-id'];
    $message = $wpcf7_data->mail['body'];
    $subject = $wpcf7_data->mail['subject'];
    $sender  = $wpcf7_data->mail['sender'];
    $attachments = $wpcf7_data->mail['attachments'];
    $additional_headers = $wpcf7_data->mail['additional_headers'];
    $additional_settings = $wpcf7_data->prop( 'additional_settings' );

    $dlmFile = new DLM_Download($fileId);
    $formData['filename'] = $dlmFile->get_the_title();
    $formData['file_url'] = $dlmFile->get_the_download_link();
    $wpcf7_data->posted_data = $formData;

    $wpcf7_data->posted_data['your-email']  = $formData['email'];
    $wpcf7_data->posted_data['your-name']   = $formData['name'];
    $wpcf7_data->posted_data['your-subject']= "Your requested The Debs Planner Free download ({$wpcf7_data->posted_data['filename']})";

    $message = str_replace("[filename]", $wpcf7_data->posted_data['filename'], $message);
    $message = str_replace("[file_url]", $wpcf7_data->posted_data['file_url'], $message);
    $message = str_replace("[name]", $wpcf7_data->posted_data['your-name'], $message);
    $subject = str_replace("[filename]", $wpcf7_data->posted_data['filename'], $subject);

    /*
    add_filter( 'wp_mail_content_type', 'set_html_content_type' );
    @wp_mail( $wpcf7_data->posted_data['your-email'], $subject , nl2br($message), 'From:'.$sender. "\r\n", $attachments);
    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
    */

    $wpcf7_data->set_properties( array(
        'mail' => array(
            'body'    => $message,
            'subject' => $subject,
            'sender'  => $sender,
            'recipient' => $wpcf7_data->posted_data['your-email'],
            'use_html'  => TRUE,
            'attachments' => $attachments,
            'additional_headers' => $additional_headers
        ))
    );

    $additional_settings .= "\n". "on_sent_ok: \"document.getElementById('dlm-$fileId').style.display = 'block'; document.getElementById('dlm-$fileId').innerHTML='The download link to the file has been emailed to you.'; \"";
    $wpcf7_data->set_properties( array( 'additional_settings' => $additional_settings ) );

    return $wpcf7_data;
}

add_action("wpcf7_before_send_mail", "wpcf7_update_email_resources", 2);

function wpcf7_update_dlm_widgets_resources($wpcf7_data)
{
    $formData = $_POST;

    if( ! isset($formData['sidebar-resources']) )
    {
        return $wpcf7_data;
    }

    $message = $wpcf7_data->mail['body'];
    $subject = $wpcf7_data->mail['subject'];
    $sender  = $wpcf7_data->mail['sender'];
    $attachments = $wpcf7_data->mail['attachments'];
    $additional_headers = $wpcf7_data->mail['additional_headers'];
    $additional_settings = $wpcf7_data->prop( 'additional_settings' );
    $messageContent = '';

    $headerMsg = 'Hi '.$formData['your-name'].',' . "\n\n". 'Your requested e-book are listed below.' . "\n\n";
    $wpcf7_data->posted_data = $formData;

    if( !empty($formData['dresid']) AND is_array($formData['dresid']) )
    {
        $body = 'File        : <strong>[filename]</strong>'. "\n";
        $body.= 'Download URL: <strong><a href="[file_url]" target="_new">[file_url]</a></strong>'. "\n\n";

        foreach (array_unique($formData['dresid']) as $id)
        {
            $parseMsg = $body;
            $dlmFile = new DLM_Download($id);

            $parseMsg = str_replace("[filename]", $dlmFile->get_the_title(), $parseMsg);
            $parseMsg = str_replace("[file_url]", $dlmFile->get_the_download_link(), $parseMsg);

            $formData[]['filename'] = $dlmFile->get_the_title();
            $formData[]['file_url'] = $dlmFile->get_the_download_link();

            $messageContent .= $parseMsg;
            unset($dlmFile);
        }
    }

    $wpcf7_data->posted_data['your-subject']= "Your requested The Debs Planner free download resources";

    unset($formData['dresid'], $formData['sidebar-resources'], $wpcf7_data->posted_data['dresid']);

    $message = str_replace("[your-email]", $wpcf7_data->posted_data['your-email'], $message);
    $message = str_replace("[your-name]", $wpcf7_data->posted_data['your-name'], $message);
    $subject = str_replace("[your-name]", $wpcf7_data->posted_data['your-name'], $subject);

    $wpcf7_data->set_properties( array(
        'mail' => array(
            'body'    => $headerMsg . $messageContent . $message,
            'subject' => $subject,
            'sender'  => $sender,
            'recipient' => $wpcf7_data->posted_data['your-email'],
            'use_html'  => TRUE,
            'attachments' => $attachments,
            'additional_headers' => $additional_headers
        ))
    );

    $additional_settings .= "\n". "on_sent_ok: \"document.getElementById('dlm-831').style.display = 'block'; document.getElementById('dlm-831').innerHTML='The download details has been emailed to you.'; \"";
    $wpcf7_data->set_properties( array( 'additional_settings' => $additional_settings ) );

    return $wpcf7_data;
}

add_action("wpcf7_before_send_mail", "wpcf7_update_dlm_widgets_resources", 1);