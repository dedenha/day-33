<?php
function _retriever($url, $data = null, $headers = null, $method = 'GET')
{
    $cookie_file_temp = dirname(__FILE__) . '/cookie/name.txt';
    $datas['http_code'] = 0;

    // Check if URL is empty
    if ($url == "") {
        return $datas;
    }

    // Prepare data for POST or GET
    $data_string = "";
    if ($data != null) {
        foreach ($data as $key => $value) {
            $data_string .= $key . '=' . urlencode($value) . '&';
        }
        $data_string = rtrim($data_string, '&');
    }

    // Initialize cURL
    $ch = curl_init();

    // Set request method
    if (strtoupper($method) == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    } else if (strtoupper($method) == "GET" && $data != null) {
        $url = $url . '?' . $data_string;
    }

    // Set headers if provided
    if ($headers != null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }


    // Set other cURL options
    curl_setopt($ch, CURLOPT_HEADER, false); // Exclude the header in the output
    curl_setopt($ch, CURLOPT_NOBODY, false); // include the body in the output
    curl_setopt($ch, CURLOPT_URL, $url); // Set the URL to fetch
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Ignore host SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore peer SSL verification
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return output as string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any "Location: "header
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_temp); // Save cookies
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_temp); // Send cookies


    // Excecute cURL request
    $response = curl_exec($ch);
    $datas['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $datas['result'] = $response; // Get response content

    // Check for cURL errors
    if (curl_errno($ch)) {
        $datas['error'] = curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);
    return $datas;
}

//$html = file_get_contents('https://www.kapanlagi.com/showbiz/index.html');
$html = _retriever('https://www.kapanlagi.com/showbiz/index.html');
print_r($html['result']);

$t_start = strpos($html['result'], '<li class="artikel-kl col-md-4 col-sm-6">');
$t_html = substr($html['result'], $t_start);
//print_r($t_html);

// LINK
$t_link_start = strpos($t_html, '<a href=') + 9;
$t_link_end = strpos($t_html, 'html">') + 4;
$t_link_length = $t_link_end - $t_link_start;
$link = substr($t_html, $t_link_start, $t_link_length);
print_r($link . '<br>');

//IMG
$t_img_start = strpos($t_html, '<img src="') + 10;
$t_img_end = strpos($t_html, '"alt"');
$t_img_len = $t_img_end - $t_img_start;
$img = substr($t_html, $t_img_start, $t_img_len);
// print_r($img);

// TITLE
$t_title_start = strpos($t_html, 'alt="') + 5;
$t_title_end = strpos($t_html, '" loading="auto" />');
$t_title_len = $t_title_end - $t_title_start;
$title = substr($t_html, $t_title_start, $t_title_len);
// print_r($title  . '<br>')

$data[$counter]['img'] = $img;
$data[$counter]['title'] = $title;

// DETAIL
$html = _retriever($link);
$decode = gzdecode($html['result']);
// print_r($decode);

$script_start = strpos($decode, '<script type="application/ld+json">');
$temp_html =  substr($decode, $script_start);
$script_end = strpos($temp_html, '</script>');
$json = substr($temp_html, 35, $script_end - 35);
$arr_data = json_decode($json);
// print_r($arr_data);

$data[$counter]['publish_date'] = $arr_data[2]->datePublished;
$data[$counter]['article_body'] = $arr_data[2]->articleBody;

$keyword = '';
foreach ($arr_data[2]->keywords as $k => $v) {
    # <code...
    $keyword .= $v . ';;';
}
$data[$counter]['keywords'] = $keywords;
print_r($data);
