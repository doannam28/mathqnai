<style>
    .top2-trend {
        color: blue;
    }
</style>
<?php
// Bao gồm thư viện Simple HTML DOM
include('simple_html_dom.php');
// Lấy ngày từ tham số URL
$date = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');
$trendlimit = isset($_GET['trendlimit']) ? $_GET['trendlimit'] : 20;

// URL bạn muốn lấy dữ liệu
$url = "https://rongbachkim.com/trend.php?list&alone&day=$date&trendlimit=$trendlimit";
// Sử dụng file_get_contents để lấy toàn bộ nội dung từ URL
$html_content = file_get_contents($url);
if ($html_content !== false) {
    // Tạo đối tượng DOM từ nội dung HTML lấy được
    $html = str_get_html($html_content);

    if ($html) {
        // Biến đếm để đếm số lần đã thêm class "top2-up"
        $count = 0;

        foreach ($html->find('a') as $a) {
            // Thay thế thẻ <a> bằng thẻ <span> và thêm class "top2-trend"
            $span = '<span class="top2-trend">' . $a->innertext . '</span>';

            // Thêm class "top2-up" vào 5 span đầu tiên
            if ($count < 5) {
                $span = '<span class="top2-trend top2-34">' . $a->innertext . '</span>';
                $count++;
            }

            // Thêm class "top2-7" vào các span từ vị trí thứ 6 trở đi
            if ($count >= 6 && $count <= 15) {
                $span = '<span class="top2-trend top2-31">' . $a->innertext . '</span>';
            }

            // Thêm class "top2-7" vào các span từ vị trí thứ 6 trở đi
            if ($count >= 16 && $count <= 40) {
                $span = '<span class="top2-trend top2-28">' . $a->innertext . '</span>';
            }

            if ($count >= 41 && $count <= 70) {
                $span = '<span class="top2-trend top2-24">' . $a->innertext . '</span>';
            }

            if ($count >= 71) {
                $span = '<span class="top2-trend top2-19">' . $a->innertext . '</span>';
            }

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
