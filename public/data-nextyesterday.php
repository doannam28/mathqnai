<?php
// Bao gồm thư viện Simple HTML DOM
include('simple_html_dom.php');

// Lấy ngày từ tham số URL
$date = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');
$trendlimit = isset($_GET['trendlimit']) ? $_GET['trendlimit'] : 20;

// Tính toán ngày hôm qua
$nextyesterdayDate = date('Y-m-d', strtotime($date . ' -2 day'));

// URL bạn muốn lấy dữ liệu
$url = "https://rongbachkim.com/trend.php?list&alone&day=$nextyesterdayDate&trendlimit=$trendlimit";

// Sử dụng file_get_contents để lấy toàn bộ nội dung từ URL
$html_content = file_get_contents($url);

if ($html_content !== false) {
    // Tạo đối tượng DOM từ nội dung HTML lấy được
    $html = str_get_html($html_content);

    if ($html) {
        $count = 0;
        // Lấy tất cả các thẻ <a> trong HTML
            foreach ($html->find('a') as $a) {
        // Thay thế thẻ <a> bằng thẻ <span> và thêm class "top2-trend"
        $span_class = 'top2-trend';
        if ($count < 5) {
            $span_class .= ' top2-34';
        } elseif ($count >= 5 && $count < 16) {
            $span_class .= ' top2-31';
        } elseif ($count >= 16 && $count < 41) {
            $span_class .= ' top2-28';
        } elseif ($count >= 41 && $count < 71) {
            $span_class .= ' top2-24';
        } else {
            $span_class .= ' top2-19';
        }

        // Kiểm tra xem <a> có chứa thẻ <span> con hay không
        if ($a->find('span', 0)) {
            $span_class .= ' father';
        }

        // Tạo thẻ <span> mới với class đã được xác định
        $span = '<span class="' . $span_class . '">' . $a->innertext . '</span>';

        $a->outertext = $span;
        $count++;
    }

        // Tạo một chuỗi để lưu trữ kết quả
        $result = '';

        // Lấy tất cả các phần tử có class "contentbox"
        foreach ($html->find('.contentbox') as $contentbox) {
            // Tìm và loại bỏ các div có style "display:block; padding-top:5px"
            foreach ($contentbox->find('div[style="display:block; padding-top:5px"]') as $div) {
                $div->outertext = '';
            }



            // Thêm nội dung của mỗi phần tử "contentbox" vào chuỗi kết quả
            $result .= $contentbox->outertext;
        }

        // Giải phóng bộ nhớ
        $html->clear();
        unset($html);

        // Trả về kết quả
        echo $result;
    } else {
        echo "Không thể tạo đối tượng DOM từ nội dung HTML.";
    }
} else {
    echo "Không thể lấy nội dung từ URL.";
}
?>
