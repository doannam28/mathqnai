<?php $setting = App\Helpers\Utility::setting();?>
<!-- Footer -->
<footer>
    <div id="div-nq">
        <a href="/noi-qui-va-dieu-khoan">Nội quy & chính sách</a>
    </div>
    <div class="div-qc">
        <h3 class="h3-telegram">Liên hệ QC: Telegram: {{$setting->telegram}}</h3>
        <p class="p-email">Email: {{$setting->email}}</p>
        <div class="txt-copy">{!! $setting->copyright !!}</div>
    </div>
   <div class="div-tag">{!! $setting->texttag !!}</div>
</footer>
