<?php
function write_log($message, $send_to_webhook = false, $warning = false)
{
    include_once $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/includes.php';
    include $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/config.php'; 

    $current_date = date("d.m.Y h:i");

    if ($send_to_webhook)
    {
        $log_message = $message;

        send_to_webhook($log_message, $current_date);
    }

    write_log_in_db($message, $current_date);

}

function write_log_in_db($message, $date)
{
    include_once $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/includes.php';
    include $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/config.php';

    $statement = $pdo->prepare("INSERT INTO logs (`message`, `date`) VALUES (?, ?);");
    $statement->execute(array($message, $date));
}

function send_to_webhook($message, $date)
{
    include_once $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/includes.php';
    include $_SERVER['DOCUMENT_ROOT'].'/rapid_auth_api_base64/config.php';

    $url = $webhook_url;
    $hookObject = json_encode([
        /*
         * The general "message" shown above your embeds
         */
        "content" => "",
        /*
         * The username shown in the message
         */
        "username" => "Rapid Auth Log System",
        /*
         * The image location for the senders image
         */
        "avatar_url" => "https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%3Fid%3DOIP.FnOmfyycgquBuzKDToZLdgHaFN%26pid%3DApi&f=1",
        /*
         * Whether or not to read the message in Text-to-speech
         */
        "tts" => false,
        /*
         * File contents to send to upload a file
         */
        // "file" => "",
        /*
         * An array of Embeds
         */
        "embeds" => [
            /*
             * Our first embed
             */
            [
                // Set the title for your embed
                "title" => "Log",
    
                // The type of your embed, will ALWAYS be "rich"
                "type" => "rich",
    
                // A description for your embed
                "description" => "",
    
                // The URL of where your title will be a link to
                "url" => "",
    
                // The integer color to be used on the left side of the embed
                "color" => hexdec( "FFFFFF" ),

    
                // Field array of objects
                "fields" => [
                    // Field 1
                    [
                        "name" => "Date: ",
                        "value" => $date,
                        "inline" => false
                    ],
                    // Field 2
                    [
                        "name" => "Log:",
                        "value" => $message,
                        "inline" => true
                    ]
                ]
            ]
        ]
    
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    
    $ch = curl_init();
    
    curl_setopt_array( $ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $hookObject,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ]
    ]);
    
    $response = curl_exec( $ch );
    curl_close( $ch );
}

?>